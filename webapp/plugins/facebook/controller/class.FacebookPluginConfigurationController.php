<?php
/**
 *
 * ThinkUp/webapp/plugins/facebook/controller/class.FacebookPluginConfigurationController.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Guillaume Boudreau, Mark Wilkie
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkup.com).
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
 * @copyright 2009-2013 Gina Trapani, Guillaume Boudreau, Mark Wilkie
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class FacebookPluginConfigurationController extends PluginConfigurationController {

    public function __construct($owner) {
        parent::__construct($owner, 'facebook');
        $this->disableCaching();
    }

    public function authControl() {
        $config = Config::getInstance();
        Loader::definePathConstants();
        $this->setViewTemplate(THINKUP_WEBAPP_PATH.'plugins/facebook/view/facebook.account.index.tpl');
        $this->view_mgr->addHelp('facebook', 'userguide/settings/plugins/facebook');

        /** set option fields **/

        // Application ID text field
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, array('name'=>'facebook_app_id',
        'label'=>'App ID', 'size' => 18)); // add element
        // set a special required message
        $this->addPluginOptionRequiredMessage('facebook_app_id',
        'The Facebook plugin requires a valid App ID.');

        // Application Secret text field
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, array('name'=>'facebook_api_secret',
        'label'=>'App Secret', 'size' => 37)); // add element
        // set a special required message
        $this->addPluginOptionRequiredMessage('facebook_api_secret',
        'The Facebook plugin requires a valid App Secret.');

        $max_crawl_time_label = 'Max crawl time in minutes';
        $max_crawl_time = array('name' => 'max_crawl_time', 'label' => $max_crawl_time_label,
        'default_value' => '20', 'advanced'=>true, 'size' => 3);
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $max_crawl_time);

        $this->addToView('thinkup_site_url', Utils::getApplicationURL());

        $facebook_plugin = new FacebookPlugin();
        if ($facebook_plugin->isConfigured()) {
            $this->setUpFacebookInteractions($facebook_plugin->getOptionsHash());
            $this->addToView('is_configured', true);
        } else {
            $this->addInfoMessage('Please complete plugin setup to start using it.', 'setup');
            $this->addToView('is_configured', false);
        }
        return $this->generateView();
    }
    /**
     * Return whether or not a Facebook account ID is a page.
     * @param str $account_id
     * @param str $access_token
     * @return bool
     */
    protected function isAccountPage($account_id, $access_token) {
        $account = FacebookGraphAPIAccessor::apiRequest('/' . $account_id . '?metadata=true', $access_token);
        return !empty($account)
        && ((!empty($account->type)  && (strcmp($account->type, 'page')==0))
        || (!empty($account->metadata->type) && (strcmp($account->metadata->type, 'page')==0)));
    }
    /**
     * Populate view manager with Facebook interaction UI, like the Facebook Add User button and page dropdown.
     * @param array $options 'facebook_app_id' and 'facebook_api_secret'
     */
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
        // Plant unique token for CSRF protection during auth per https://developers.facebook.com/docs/authentication/
        if (SessionCache::get('facebook_auth_csrf') == null) {
            SessionCache::put('facebook_auth_csrf', md5(uniqid(rand(), true)));
        }

        if (isset($this->owner) && $this->owner->isMemberAtAnyLevel()) {
            if ($this->owner->isMemberLevel()) {
                $instance_dao = DAOFactory::getDAO('InstanceDAO');
                $owner_instances = $instance_dao->getByOwnerAndNetwork($this->owner, 'facebook');
                if (sizeof($owner_instances) > 0) {
                    $this->do_show_add_button = false;
                    $this->addInfoMessage("To connect another Facebook account to ThinkUp, upgrade your membership.",
                    'membership_cap');
                }
            }
        }

        if ($this->do_show_add_button) {
            $params = array('scope'=>'read_stream,user_birthday,user_likes,user_location,user_website,'.
            'read_friendlists,friends_birthday,friends_location,manage_pages,read_insights,manage_pages',
            'state'=>SessionCache::get('facebook_auth_csrf'),
            'redirect_uri'=> (Utils::getApplicationURL(). 'account/?p=facebook')
            );

            $fbconnect_link = $facebook->getLoginUrl($params);
            $this->addToView('fbconnect_link', $fbconnect_link);
        }

        self::processPageActions($options, $facebook);

        $logger = Logger::getInstance();
        $user_pages = array();
        $user_admin_pages = array();
        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $instances = $instance_dao->getByOwnerAndNetwork($this->owner, 'facebook');

        if ($this->do_show_add_button) {
            $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
            foreach ($instances as $instance) {
                // TODO: figure out if the scope has changed since this instance last got its tokens,
                // and we need to get re-request permission with the new scope
                $tokens = $owner_instance_dao->getOAuthTokens($instance->id);
                $access_token = $tokens['oauth_access_token'];
                if ($instance->network == 'facebook') { //not a page
                    $pages = FacebookGraphAPIAccessor::apiRequest('/'.$instance->network_user_id.'/likes',
                    $access_token);
                    if (@$pages->data) {
                        $user_pages[$instance->network_user_id] = $pages->data;
                    }

                    $sub_accounts = FacebookGraphAPIAccessor::apiRequest('/'.$instance->network_user_id.'/accounts',
                    $access_token);
                    if (!empty($sub_accounts->data)) {
                        $user_admin_pages[$instance->network_user_id] = array();
                        foreach ($sub_accounts->data as $act) {
                            if (self::isAccountPage($act->id, $access_token)) {
                                $user_admin_pages[$instance->network_user_id][] = $act;
                            }
                        }
                    }
                }
                if (isset($tokens['auth_error']) && $tokens['auth_error'] != '') {
                    $instance->auth_error = $tokens['auth_error'];
                }
            }
            $this->addToView('user_pages', $user_pages);
            $this->addToView('user_admin_pages', $user_admin_pages);
        }

        $owner_instance_pages = $instance_dao->getByOwnerAndNetwork($this->owner, 'facebook page');
        if (count($owner_instance_pages) > 0) {
            $this->addToView('owner_instance_pages', $owner_instance_pages);
        }
        $this->addToView('instances', $instances);
    }
    /**
     * Process actions based on $_GET parameters. Authorize FB user or add FB page.
     * @param arr $options Facebook plugin options
     * @param Facebook $facebook Facebook object
     */
    protected function processPageActions($options, Facebook $facebook) {
        //authorize user
        if (isset($_GET["code"]) && isset($_GET["state"])) {
            //validate state to avoid CSRF attacks
            if ($_GET["state"] == SessionCache::get('facebook_auth_csrf')) {
                //Prepare API request
                //First, prep redirect URI
                $redirect_uri = urlencode(Utils::getApplicationURL(). 'account/?p=facebook');

                //Build API request URL
                $api_req = 'https://graph.facebook.com/oauth/access_token?client_id='.
                $options['facebook_app_id']->option_value.'&client_secret='.
                $options['facebook_api_secret']->option_value. '&redirect_uri='.$redirect_uri.'&state='.
                SessionCache::get('facebook_auth_csrf').'&code='.$_GET["code"];

                $access_token_response = FacebookGraphAPIAccessor::rawApiRequest($api_req, false);
                parse_str($access_token_response);
                if (isset($access_token)) {
                    /**
                     * Swap in short-term token for long-lived token as per
                     * https://developers.facebook.com/docs/facebook-login/access-tokens/#extending
                     */
                    $api_req = 'https://graph.facebook.com/oauth/access_token?grant_type=fb_exchange_token&client_id='.
                    $options['facebook_app_id']->option_value.'&client_secret='.
                    $options['facebook_api_secret']->option_value. '&fb_exchange_token='.$access_token;

                    $access_token_response = FacebookGraphAPIAccessor::rawApiRequest($api_req, false);
                    parse_str($access_token_response);

                    $facebook->setAccessToken($access_token);
                    $fb_user_profile = $facebook->api('/me');
                    $fb_username = $fb_user_profile['name'];
                    $fb_user_id = $fb_user_profile['id'];

                    if (empty($fb_username)) {
                        $error = 'Sorry, ThinkUp does not support business accounts.';
                        $this->addErrorMessage($error, 'authorization');
                    } else {
                        $this->addSuccessMessage($this->saveAccessToken($fb_user_id, $access_token, $fb_username),
                            'authorization');
                    }
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
                    $this->addErrorMessage($error_msg, 'authorization', true);
                }
            } else {
                $this->addErrorMessage("Could not authenticate Facebook account due to invalid CSRF token.",
                'authorization');
            }
        }

        //insert pages
        if (isset($_GET["action"]) && $_GET["action"] == "add page" && isset($_GET["facebook_page_id"])
        && isset($_GET["viewer_id"]) && isset($_GET["owner_id"]) && isset($_GET["instance_id"])) {
            //get access token
            $oid = DAOFactory::getDAO('OwnerInstanceDAO');
            $tokens = $oid->getOAuthTokens($_GET["instance_id"]);
            $access_token = $tokens['oauth_access_token'];

            $page_data = FacebookGraphAPIAccessor::apiRequest('/'.$_GET["facebook_page_id"], $access_token,
            "id,name,picture");
            self::insertPage($page_data->id, $_GET["viewer_id"], $_GET["instance_id"], $page_data->name,
            $page_data->picture->data->url);
        }
    }
    /**
     * Save newly-acquired OAuth access token
     * @param int $fb_user_id
     * @param str $fb_access_token
     * @param str $fb_username
     * @return void
     */
    protected function saveAccessToken($fb_user_id, $fb_access_token, $fb_username) {
        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
        $user_dao = DAOFactory::getDAO('UserDAO');

        $instance = $instance_dao->getByUserIdOnNetwork($fb_user_id, 'facebook');
        if (isset($instance)) {
            $owner_instance = $owner_instance_dao->get($this->owner->id, $instance->id);
            if ($owner_instance == null) { //Instance already exists, owner instance doesn't
                //Add owner instance with session key
                $owner_instance_dao->insert($this->owner->id, $instance->id, $fb_access_token);
                $this->addSuccessMessage("Success! Your Facebook account has been added to ThinkUp.", 'user_add');
            } else {
                $owner_instance_dao->updateTokens($this->owner->id, $instance->id, $fb_access_token, '');
                $this->addSuccessMessage("Success! You've reconnected your Facebook account. To connect a ".
                "different account, log  out of Facebook in a different browser tab and try again.", 'user_add');
            }
            //set auth error to empty string
            $owner_instance_dao->setAuthErrorByTokens($instance->id, $fb_access_token, '');
        } else { //Instance does not exist
            $instance_dao->insert($fb_user_id, $fb_username, 'facebook');
            $instance = $instance_dao->getByUserIdOnNetwork($fb_user_id, 'facebook');
            $owner_instance_dao->insert($this->owner->id, $instance->id, $fb_access_token);
            $this->addSuccessMessage("Success! Your Facebook account has been added to ThinkUp.", 'user_add');
        }

        if (!$user_dao->isUserInDB($fb_user_id, 'facebook')) {
            $r = array('user_id'=>$fb_user_id, 'user_name'=>$fb_username,'full_name'=>$fb_username, 'avatar'=>'',
            'location'=>'', 'description'=>'', 'url'=>'', 'is_verified'=>'', 'is_protected'=>'',  'follower_count'=>0,
            'friend_count'=>0, 'post_count'=>0, 'last_updated'=>'', 'last_post'=>'', 'joined'=>'',
            'last_post_id'=>'', 'network'=>'facebook' );
            $u = new User($r, 'Owner info');
            $user_dao->updateUser($u);
        }
        $this->view_mgr->clear_all_cache();
    }
    /**
     * Insert Facebook page instance into the data store
     * @param str $fb_page_id
     * @param str $viewer_id
     * @param int $existing_instance_id
     * @param str $fb_page_name
     * @param str $fb_page_avatar
     * @return void
     */
    protected function insertPage($fb_page_id, $viewer_id, $existing_instance_id, $fb_page_name,
    $fb_page_avatar) {
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
                    $this->addSuccessMessage("Success! Your Facebook page has been added.", 'page_add');
                }
                $tokens = $owner_instance_dao->getOAuthTokens($existing_instance_id);
                $session_key = $tokens['oauth_access_token'];
                $owner_instance_dao->insert($this->owner->id, $instance_id, $session_key);
            } else {
                $this->addInfoMessage("This Facebook Page is already in ThinkUp.", 'page_add');
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
                $val['is_verified'] = false;
                $val['is_protected'] = false;
                $val['follower_count'] = 0;
                $val['post_count'] = 0;
                $val['joined'] = 0;
                $val['network'] = 'facebook page';
                $user = new User($val);
                $result = $user_dao->updateUser($user);
            }
        } else {
            $this->addInfoMessage("This Facebook Page is already in ThinkUp.", 'page_add');
            $instance_id = $i->id;
        }
    }
}
