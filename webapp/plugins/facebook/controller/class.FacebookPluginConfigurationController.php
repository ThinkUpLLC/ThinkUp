<?php
/**
 *
 * ThinkUp/webapp/plugins/facebook/controller/class.FacebookPluginConfigurationController.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Guillaume Boudreau, Mark Wilkie
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
 * @copyright 2009-2010 Gina Trapani, Guillaume Boudreau, Mark Wilkie
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
        // API Key text field
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, array('name'=>'facebook_api_key',
        'label'=>'Your Facebook API Key')); // add element
        $this->addPluginOptionHeader('facebook_api_key',
        'Facebook Configuration');
        // set a special required message
        $this->addPluginOptionRequiredMessage('facebook_api_key',
        'The Facebook plugin requires a valid API Key.');

        // Application Secret text field
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, array('name'=>'facebook_api_secret',
        'label'=>'Your Facebook Application Secret')); // add element
        // set a special required message
        $this->addPluginOptionRequiredMessage('facebook_api_secret',
        'The Facebook plugin requires a valid Application Secret.');

        // Application ID text field
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, array('name'=>'facebook_app_id',
        'label'=>'Your Facebook Application ID')); // add element
        // set a special required message
        $this->addPluginOptionRequiredMessage('facebook_app_id',
        'The Facebook plugin requires a valid Application ID.');

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
        'secret' => $options['facebook_api_secret']->option_value,
        'cookie' => false,
        ));

        //check status of current FB user
        $session = $facebook->getSession();
        $fb_user = null;
        if ($session) {
            $fb_user_id = $facebook->getUser();
            $fb_user = $facebook->api('/me');
        }

        // login or logout url will be needed depending on current user state.
        if (isset($fb_user)) {
            $logoutUrl = $facebook->getLogoutUrl();
            $fbconnect_link = '<img src="https://graph.facebook.com/'. $fb_user_id .'/picture" style="float:left;">'.
            $fb_user['name'].'<br /><a href="'. $logoutUrl .'">
            <img src="http://static.ak.fbcdn.net/rsrc.php/z2Y31/hash/cxrz4k7j.gif"></a>';
        } else {
            $redirect_uri = urlencode('http://'.$_SERVER['SERVER_NAME'].THINKUP_BASE_URL.'account/?p=facebook');
            $params = array('req_perms'=>'offline_access', 'redirect_uri'=>$redirect_uri);
            $loginUrl = $facebook->getLoginUrl($params);

            $fbconnect_link =  '<a href="'. $loginUrl .
            '"><img src="http://static.ak.fbcdn.net/rsrc.php/zB6N8/hash/4li2k73z.gif"></a>';
        }

        $this->addToView('fbconnect_link', $fbconnect_link);

        $status = self::processPageActions($fb_user, $facebook->getAccessToken());
        $this->addToView("info", $status["info"]);
        $this->addToView("error", $status["error"]);
        $this->addToView("success", $status["success"]);

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
                if ($pages->data) {
                    $user_pages[$instance->network_user_id] = $pages->data;
                }
            }
        }
        //print_r($user_pages);
        $this->addToView('user_pages', $user_pages);

        $owner_instance_pages = $instance_dao->getByOwnerAndNetwork($this->owner, 'facebook page');
        $this->addToView('owner_instance_pages', $owner_instance_pages);

        $this->addToView('owner_instances', $owner_instances);
        if (isset($options['facebook_api_key'])) {
            $this->addToView('fb_api_key', $options['facebook_api_key']->option_value);
        }
    }

    protected function processPageActions($fb_user, $access_token) {
        $messages = array("error"=>'', "success"=>'', "info"=>'');

        //authorize user
        if (isset($_GET["session"]) ) {
            $session_data = json_decode(str_replace("\\", "", $_GET["session"]));
            $messages['info'] = $this->saveAccessToken($session_data->uid, $session_data->access_token,
            $fb_user['name']);
        }

        //insert pages
        if (isset($_GET["action"]) && $_GET["action"] == "add page" && isset($_GET["facebook_page_id"])
        && isset($_GET["viewer_id"]) && isset($_GET["owner_id"]) && isset($_GET["instance_id"])) {
            //$page_data = json_decode(str_replace("\\", "", $_GET["facebook_page_id"]));
            $page_data = FacebookGraphAPIAccessor::apiRequest('/'.$_GET["facebook_page_id"], $access_token);
            $messages = self::insertPage($page_data->id, $_GET["viewer_id"], $_GET["instance_id"],
            $page_data->name, $page_data->picture, $messages);
        }
        return $messages;
    }

    protected function saveAccessToken($fb_user_id, $fb_access_token, $fb_username) {
        $msg = '';
        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $oid = DAOFactory::getDAO('OwnerInstanceDAO');
        $user_dao = DAOFactory::getDAO('UserDAO');

        $instance = $instance_dao->getByUserIdOnNetwork($fb_user_id, 'facebook');
        if (isset($instance)) {
            $msg .= "Instance exists<br />";
            $oi = $oid->get($this->owner->id, $instance->id);
            if ($oi == null) { //Instance already exists, owner instance doesn't
                $oid->insert($this->owner->id, $instance->id, $fb_access_token); //Add owner instance with session key
                $msg .= "Created owner instance.<br />";
            }
        } else { //Instance does not exist
            $msg .= "Instance does not exist<br />";

            $instance_dao->insert($fb_user_id, $fb_username, 'facebook');
            $msg .= "Created instance";

            $instance = $instance_dao->getByUserIdOnNetwork($fb_user_id, 'facebook');
            $oid->insert($this->owner->id, $instance->id, $fb_access_token);
            $msg .= "Created owner instance.<br />";
        }

        if (!$user_dao->isUserInDB($fb_user_id, 'facebook')) {
            $r = array('user_id'=>$fb_user_id, 'user_name'=>$fb_username,'full_name'=>$fb_username, 'avatar'=>'',
                'location'=>'', 'description'=>'', 'url'=>'', 'is_protected'=>'',  'follower_count'=>0, 
                'friend_count'=>0, 'post_count'=>0, 'last_updated'=>'', 'last_post'=>'', 'joined'=>'', 
                'last_post_id'=>'', 'network'=>'facebook' );
            $u = new User($r, 'Owner info');
            $user_dao->updateUser($u);
        }
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
                    $messages["success"] .= "Instance ID ".$instance_id.
                " created successfully for Facebook page ID $fb_page_id.";
                }
                $tokens = $owner_instance_dao->getOAuthTokens($existing_instance_id);
                $session_key = $tokens['oauth_access_token'];
                $owner_instance_dao->insert($this->owner->id, $instance_id, $session_key);
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
            $messages["info"] .= "Instance ".$fb_page_id.", facebook exists.";
            $instance_id = $i->id;
        }
        return $messages;
    }

}