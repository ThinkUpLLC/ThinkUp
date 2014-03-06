<?php
/**
 *
 * ThinkUp/webapp/plugins/instagram/controller/class.InstagramPluginConfigurationController.php
 *
 * Copyright (c) 2009-2013 Dimosthenis Nikoudis
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
 * Instagram Plugin Configuration Controller
 *
 * @author Dimosthenis Nikoudis <dnna[at]dnna[dot]gr>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Dimosthenis Nikoudis
 */
class InstagramPluginConfigurationController extends PluginConfigurationController {
    /**
     *
     * @var Owner An owner object for the user adding this plugin to his / her account
     */
    var $owner;
    /**
     * Constructor
     * @param Owner $owner
     * @return InstagramPluginConfigurationController
     */
    public function __construct($owner) {
        parent::__construct($owner, 'instagram');
        $this->disableCaching();
        $this->owner = $owner;
    }

    public function authControl() {
        $config = Config::getInstance();
        Loader::definePathConstants();
        $this->setViewTemplate(THINKUP_WEBAPP_PATH.'plugins/instagram/view/instagram.account.index.tpl');
        $this->view_mgr->addHelp('instagram', 'userguide/settings/plugins/instagram');

        /** set option fields **/

        // Application ID text field
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, array('name'=>'instagram_app_id',
        'label'=>'Client ID', 'size' => 18)); // add element
        // set a special required message
        $this->addPluginOptionRequiredMessage('instagram_app_id',
        'The instagram plugin requires a valid Client ID.');

        // Application Secret text field
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, array('name'=>'instagram_api_secret',
        'label'=>'Client Secret', 'size' => 37)); // add element
        // set a special required message
        $this->addPluginOptionRequiredMessage('instagram_api_secret',
        'The instagram plugin requires a valid Client Secret.');

        $max_crawl_time_label = 'Max crawl time in minutes';
        $max_crawl_time = array('name' => 'max_crawl_time', 'label' => $max_crawl_time_label,
        'default_value' => '20', 'advanced'=>true, 'size' => 3);
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $max_crawl_time);

        $this->addToView('thinkup_site_url', Utils::getApplicationURL());

        $instagram_plugin = new InstagramPlugin();
        if ($instagram_plugin->isConfigured()) {
            $this->setUpInstagramInteractions($instagram_plugin->getOptionsHash());
            $this->addToView('is_configured', true);
        } else {
            $this->addInfoMessage('Please complete plugin setup to start using it.', 'setup');
            $this->addToView('is_configured', false);
        }
        return $this->generateView();
    }

    /**
     * Populate view manager with instagram interaction UI, like the instagram Add User button and page dropdown.
     * @param array $options 'instagram_app_id' and 'instagram_api_secret'
     */
    protected function setUpInstagramInteractions($options) {
        // Create our instagram Application instance
        $redirect_uri = Utils::getApplicationURL().'account/?p=instagram';
        $scope = array( 'likes', 'comments', 'relationships' );

        $instagram = new Instagram\Auth(array(
            'client_id'  => $options['instagram_app_id']->option_value,
            'client_secret' => $options['instagram_api_secret']->option_value,
            'redirect_uri' => $redirect_uri,
        ));

        $instagramconnect_link = sprintf(
            'https://api.instagram.com/oauth/authorize/?client_id=%s&redirect_uri=%s&response_type=code&scope=%s',
            $options['instagram_app_id']->option_value,
            $redirect_uri,
            implode( '+', $scope )
        );
        $this->addToView('instaconnect_link', $instagramconnect_link);

        self::processPageActions($options, $instagram);

        $logger = Logger::getInstance();
        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $instances = $instance_dao->getByOwnerAndNetwork($this->owner, 'instagram');

        $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
        foreach ($instances as $instance) {
            $tokens = $owner_instance_dao->getOAuthTokens($instance->id);
            if (isset($tokens['auth_error']) && $tokens['auth_error'] != '') {
                $instance->auth_error = $tokens['auth_error'];
            }
        }
        $this->addToView('instances', $instances);
    }
    /**
     * Process actions based on $_GET parameters. Authorize Instagram user.
     * @param arr $options instagram plugin options
     * @param instagram $instagramAuth instagram object
     */
    protected function processPageActions($options, Instagram\Auth $instagramAuth) {
        //authorize user
        if (isset($_GET["code"])) {
            $access_token = $instagramAuth->getAccessToken($_GET["code"]);
            if (isset($access_token)) {
                $instagram = new Instagram\Instagram($access_token);
                $instagram_user_profile = $instagram->getCurrentUser()->getData();
                $instagram_username = $instagram_user_profile->username;
                $instagram_user_id = $instagram_user_profile->id;
                $this->saveAccessToken($instagram_user_id, $access_token, $instagram_username);
            } else {
                $error_msg = "Problem authorizing your instagram account! Please correct your plugin settings.";
                $this->addErrorMessage($error_msg, 'authorization', true);
            }
        }
    }
    /**
     * Save newly-acquired OAuth access token
     * @param int $instagram_user_id
     * @param str $instagram_access_token
     * @param str $instagram_username
     * @return void
     */
    protected function saveAccessToken($instagram_user_id, $instagram_access_token, $instagram_username) {
        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
        $user_dao = DAOFactory::getDAO('UserDAO');

        $instance = $instance_dao->getByUserIdOnNetwork($instagram_user_id, 'instagram');
        if (isset($instance)) {
            $owner_instance = $owner_instance_dao->get($this->owner->id, $instance->id);
            if ($owner_instance == null) { //Instance already exists, owner instance doesn't
                //Add owner instance with session key
                $owner_instance_dao->insert($this->owner->id, $instance->id, $instagram_access_token);
                $this->addSuccessMessage("Success! Your instagram account has been added to ThinkUp.", 'user_add');
            } else {
                $owner_instance_dao->updateTokens($this->owner->id, $instance->id, $instagram_access_token, '');
                $this->addSuccessMessage("Success! You've reconnected your Instagram account. To connect ".
                "a different account, log  out of Instagram in a different browser tab and try again.", 'user_add');
            }
            //set auth error to empty string
            $owner_instance_dao->setAuthErrorByTokens($instance->id, $instagram_access_token, '');
        } else { //Instance does not exist
            $instance_dao->insert($instagram_user_id, $instagram_username, 'instagram');
            $instance = $instance_dao->getByUserIdOnNetwork($instagram_user_id, 'instagram');
            $owner_instance_dao->insert($this->owner->id, $instance->id, $instagram_access_token);
            $this->addSuccessMessage("Success! Your instagram account has been added to ThinkUp.", 'user_add');
        }

        if (!$user_dao->isUserInDB($instagram_user_id, 'instagram')) {
            $r = array('user_id'=>$instagram_user_id, 'user_name'=>$instagram_username,'full_name'=>$instagram_username,
            'avatar'=>'',
            'location'=>'', 'description'=>'', 'url'=>'', 'is_protected'=>'',  'follower_count'=>0,
            'friend_count'=>0, 'post_count'=>0, 'last_updated'=>'', 'last_post'=>'', 'joined'=>'',
            'last_post_id'=>'', 'network'=>'instagram' );
            $u = new User($r, 'Owner info');
            $user_dao->updateUser($u);
        }
        $this->view_mgr->clear_all_cache();
    }
}
