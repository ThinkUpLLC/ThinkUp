<?php
/**
 *
 * ThinkUp/webapp/plugins/facebook/controller/class.FacebookPluginConfigurationController.php
 *
 * Copyright (c) 2009-2011 Gina Trapani, Guillaume Boudreau, Mark Wilkie
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 *
 * Facebook Plugin Configuration controller
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Gina Trapani, Guillaume Boudreau, Mark Wilkie
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class FacebookPluginConfigurationController extends PluginConfigurationController {
    /**
     *
     * @var Owner
     */
    var $owner;
    /**
     * Constructor
     * @param Owner $owner
     * @return FacebookPluginConfigurationController
     */
    public function __construct($owner) {
        parent::__construct($owner, 'facebook');
        $this->disableCaching();
        $this->owner = $owner;
    }

    public function authControl() {
        $config = Config::getInstance();
        Utils::defineConstants();
        $this->setViewTemplate(THINKUP_WEBAPP_PATH.'plugins/facebook/view/facebook.account.index.tpl');

        /** set option fields **/

        // Application ID text field
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, array('name'=>'facebook_app_id',
        'label'=>'Application ID', 'size' => 18)); // add element
        // set a special required message
        $this->addPluginOptionRequiredMessage('facebook_app_id',
        'The Facebook plugin requires a valid Application ID.');

        // API Key text field
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, array('name'=>'facebook_api_key',
        'label'=>'API Key', 'size' => 18)); // add element
        // set a special required message
        $this->addPluginOptionRequiredMessage('facebook_api_key',
        'The Facebook plugin requires a valid API Key.');

        // Application Secret text field
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, array('name'=>'facebook_api_secret',
        'label'=>'Application Secret', 'size' => 37)); // add element
        // set a special required message
        $this->addPluginOptionRequiredMessage('facebook_api_secret',
        'The Facebook plugin requires a valid Application Secret.');

        $max_crawl_time_label = 'Max crawl time in minutes';
        $max_crawl_time = array('name' => 'max_crawl_time', 'label' => $max_crawl_time_label,
        'default_value' => '20', 'advanced'=>true);
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $max_crawl_time);

        $plugin_option_dao = DAOFactory::getDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash('facebook', true); //get cached

        if (isset($options['facebook_app_id']->option_value) && isset($options['facebook_api_secret']->option_value)) {
            $this->setUpFacebookInteractions($options);
        } else {
            $this->addErrorMessage('Please set your Facebook API key, application ID and secret.');
        }
        return $this->generateView();
    }

    protected function setUpFacebookInteractions($options) {
        // Create our Facebook Application instance
        $facebook = new Facebook(array(
        'appId'  => $options['facebook_app_id']->option_value,
        'secret' => $options['facebook_api_secret']->option_value
        ));

        $fb_user = $facebook->getUser();
        if ($fb_user) {
            try {
                $fb_user_profile = $facebook->api('/me');
            } catch (FacebookApiException $e) {
                error_log($e);
                $fb_user = null;
                $fb_user_profile = null;
            }
        }
        //Plant unique token for CSRF protection during auth per https://developers.facebook.com/docs/authentication/
        if (SessionCache::get('facebook_auth_csrf') == null) {
            SessionCache::put('facebook_auth_csrf', md5(uniqid(rand(), true)));
        }

        $params = array('scope'=>
        'offline_access,read_stream,user_likes,user_location,user_website,read_friendlists,friends_location',
        'state'=>SessionCache::get('facebook_auth_csrf'));

        $fbconnect_link = $facebook->getLoginUrl($params);
        $this->addToView('fbconnect_link', $fbconnect_link);

        $status = self::processPageActions($options, $facebook);
        $this->addInfoMessage($status["info"]);
        $this->addErrorMessage($status["error"]);
        $this->addSuccessMessage($status["success"]);

        $logger = Logger::getInstance();
        $user_pages = array();
        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $owner_instances = $instance_dao->getByOwnerAndNetwork($this->owner, 'facebook');

        $ownerinstance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
        foreach ($owner_instances as $instance) {
            $tokens = $ownerinstance_dao->getOAuthTokens($instance->id);
            $access_token = $tokens['oauth_access_token'];
            if ($instance->network == 'facebook') { //not a page
                $pages = FacebookGraphAPIAccessor::apiRequest('/'.$instance->network_user_id.'/likes', $access_token);
                if (@$pages->data) {
                    $user_pages[$instance->network_user_id] = $pages->data;
                }
            }
        }
        $this->addToView('user_pages', $user_pages);

        $owner_instance_pages = $instance_dao->getByOwnerAndNetwork($this->owner, 'facebook page');
        if(count($owner_instance_pages) > 0) {
            $this->addToView('owner_instance_pages', $owner_instance_pages);
        }

        $this->addToView('owner_instances', $owner_instances);
        if (isset($options['facebook_api_key'])) {
            $this->addToView('fb_api_key', $options['facebook_api_key']->option_value);
        }
    }

    /**
     * Process actions based on $_GET parameters. Authorize FB user or add FB page.
     * @param arr $options Facebook plugin options
     * @param Facebook $facebook Facebook object
     * @return arr Array of success and error messages
     */
    protected function processPageActions($options, Facebook $facebook) {
        $messages = array("error"=>'', "success"=>'', "info"=>'');

        //authorize user
        if (isset($_GET["code"]) && isset($_GET["state"])) {
            //validate state to avoid CSRF attacks
            if ($_GET["state"] == SessionCache::get('facebook_auth_csrf')) {
                //Prepare API request
                //First, prep redirect URI
                $config = Config::getInstance();
                $site_root_path = $config->getValue('site_root_path');
                $redirect_uri = urlencode('http://'.$_SERVER['SERVER_NAME'].$site_root_path.'account/?p=facebook');

                //Build API request URL
                $api_req = 'https://graph.facebook.com/oauth/access_token?client_id='.
                $options['facebook_app_id']->option_value.'&client_secret='.
                $options['facebook_api_secret']->option_value. '&redirect_uri='.$redirect_uri.'&state='.
                SessionCache::get('facebook_auth_csrf').'&code='.$_GET["code"];

                $access_token_response = FacebookGraphAPIAccessor::rawApiRequest($api_req, false);
                parse_str($access_token_response);
                if (isset($access_token)) {
                    $facebook->setAccessToken($access_token);
                    $fb_user_profile = $facebook->api('/me');
                    $fb_username = $fb_user_profile['name'];
                    $fb_user_id = $fb_user_profile['id'];
                    $messages['success'] = $this->saveAccessToken($fb_user_id, $access_token, $fb_username);
                } else {
                    $error_msg = "Problem authorizing your Facebook account! Please correct your plugin settings.";
                    $error_object = json_decode($access_token_response);
                    if (isset($error_object) && isset($error_object->error->type)
                    && isset($error_object->error->message)) {
                        $error_msg = $error_msg."<br>Facebook says: \"".$error_object->error->type.": "
                        .$error_object->error->message. "\"";
                    } else {
                        $error_msg = $error_msg."<br>Facebook's response: \"".$access_token_response. "\"";
                    }
                    $messages['error'] = $error_msg;
                }
            } else {
                $messages['error'] = "Could not authenticate Facebook account due to invalid CSRF token.";
            }
        }

        //insert pages
        if (isset($_GET["action"]) && $_GET["action"] == "add page" && isset($_GET["facebook_page_id"])
        && isset($_GET["viewer_id"]) && isset($_GET["owner_id"]) && isset($_GET["instance_id"])) {
            //get access token
            $oid = DAOFactory::getDAO('OwnerInstanceDAO');
            $tokens = $oid->getOAuthTokens($_GET["instance_id"]);
            $access_token = $tokens['oauth_access_token'];

            $page_data = FacebookGraphAPIAccessor::apiRequest('/'.$_GET["facebook_page_id"], $access_token);
            $messages = self::insertPage($page_data->id, $_GET["viewer_id"], $_GET["instance_id"],
            $page_data->name, $page_data->picture, $messages);
        }
        return $messages;
    }

    /**
     * Save newly-acquired OAuth access token
     * @param int $fb_user_id
     * @param str $fb_access_token
     * @param str $fb_username
     * @return str Success message
     */
    protected function saveAccessToken($fb_user_id, $fb_access_token, $fb_username) {
        $msg = '';
        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
        $user_dao = DAOFactory::getDAO('UserDAO');

        $instance = $instance_dao->getByUserIdOnNetwork($fb_user_id, 'facebook');
        if (isset($instance)) {
            $owner_instance = $owner_instance_dao->get($this->owner->id, $instance->id);
            if ($owner_instance == null) { //Instance already exists, owner instance doesn't
                //Add owner instance with session key
                $owner_instance_dao->insert($this->owner->id, $instance->id, $fb_access_token);
                $msg .= "Success! Your Facebook account has been added to ThinkUp.";
            } else {
                $owner_instance_dao->updateTokens($this->owner->id, $instance->id, $fb_access_token, '');
                $msg .= "Success! You've reconnected your Facebook account. To connect a different account, log ".
                "out of Facebook in a different browser tab and try again.";
            }
        } else { //Instance does not exist
            $instance_dao->insert($fb_user_id, $fb_username, 'facebook');
            $instance = $instance_dao->getByUserIdOnNetwork($fb_user_id, 'facebook');
            $owner_instance_dao->insert($this->owner->id, $instance->id, $fb_access_token);
            $msg .= "Success! Your Facebook account has been added to ThinkUp.";
        }

        if (!$user_dao->isUserInDB($fb_user_id, 'facebook')) {
            $r = array('user_id'=>$fb_user_id, 'user_name'=>$fb_username,'full_name'=>$fb_username, 'avatar'=>'',
            'location'=>'', 'description'=>'', 'url'=>'', 'is_protected'=>'',  'follower_count'=>0, 
            'friend_count'=>0, 'post_count'=>0, 'last_updated'=>'', 'last_post'=>'', 'joined'=>'', 
            'last_post_id'=>'', 'network'=>'facebook' );
            $u = new User($r, 'Owner info');
            $user_dao->updateUser($u);
        }
        $this->view_mgr->clear_all_cache();

        return $msg;
    }

    protected function insertPage($fb_page_id, $viewer_id, $existing_instance_id, $fb_page_name,
    $fb_page_avatar, $messages) {
        //check if instance exists
        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
        $i = $instance_dao->getByUserAndViewerId($fb_page_id, $viewer_id, 'facebook page');
        $user_dao = DAOFactory::getDAO('UserDAO');
        $user_in_db = $user_dao->isUserInDB($fb_page_name, "facebook page");
        if ($i == null || !$user_in_db) {
            if ($i == null ) {
                $instance_id = $instance_dao->insert($fb_page_id, $fb_page_name, "facebook page", $viewer_id);
                if ($instance_id) {
                    $messages["success"] .= "Success! Your Facebook page has been added.";
                }
                $tokens = $owner_instance_dao->getOAuthTokens($existing_instance_id);
                $session_key = $tokens['oauth_access_token'];
                $owner_instance_dao->insert($this->owner->id, $instance_id, $session_key);
            } else {
                $messages["error"] .= "This Facebook Page is already in ThinkUp.";
            }
            if (!$user_in_db) {
                $val = array();
                $val['user_name'] = $fb_page_name;
                $val['full_name'] = $fb_page_name;
                $val['user_id'] = $fb_page_id;
                $val['avatar'] = $fb_page_avatar;
                $val['location'] = '';
                $val['description'] = '';
                $val['url'] = '';
                $val['is_protected'] = false;
                $val['follower_count'] = 0;
                $val['post_count'] = 0;
                $val['joined'] = 0;
                $val['network'] = 'facebook page';
                $user = new User($val);
                $result = $user_dao->updateUser($user);
            }
        } else {
            $messages["error"] .= "This Facebook Page is already in ThinkUp.";
            $instance_id = $i->id;
        }
        return $messages;
    }

}