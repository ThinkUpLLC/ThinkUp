<?php
/**
 *
 * ThinkUp/webapp/plugins/GooglePlus/controller/class.GooglePlusPluginConfigurationController.php
 *
 * Copyright (c) 2011 Gina Trapani
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
 * GooglePlus Plugin Configuration Controller
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

class GooglePlusPluginConfigurationController extends PluginConfigurationController {
    /**
     *
     * @var Owner
     */
    var $owner;
    /**
     * Constructor
     * @param Owner $owner
     * @return GooglePlusPluginConfigurationController
     */
    public function __construct($owner) {
        parent::__construct($owner, 'googleplus');
        $this->disableCaching();
        $this->owner = $owner;
    }

    public function authControl() {
        $config = Config::getInstance();
        Utils::defineConstants();
        $this->setViewTemplate( THINKUP_WEBAPP_PATH.'plugins/googleplus/view/googleplus.account.index.tpl');

        /** set option fields **/
        // client ID text field
        $name_field = array('name' => 'google_plus_client_id', 'label' => 'Client ID', 'size'=>50);
        $name_field['default_value'] = ''; // set default value
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $name_field); // add element
        // set a special required message
        $this->addPluginOptionRequiredMessage('google_plus_client_id', 'A client ID is required to use Google+.');

        // client secret text field
        $name_field = array('name' => 'google_plus_client_secret', 'label' => 'Client secret', 'size'=>40);
        $name_field['default_value'] = ''; // set default value
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $name_field); // add element
        // set a special required message
        $this->addPluginOptionRequiredMessage('google_plus_client_secret',
        'A client secret is required to use Google+.');

        $plugin_option_dao = DAOFactory::getDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash('googleplus', true); //get cached

        if (isset($options['google_plus_client_id']->option_value)
        && isset($options['google_plus_client_secret']->option_value)) {
            $this->setUpGPlusInteractions($options);
        } else {
            $this->addErrorMessage('Please set your Google+ client ID and secret.');
        }

        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $owner_instances = $instance_dao->getByOwnerAndNetwork($this->owner, 'google+');
        $this->addToView('owner_instances', $owner_instances);

        return $this->generateView();
    }

    /**
     * Add user auth link or process incoming auth requests.
     * @param array $options Plugin options array
     */
    protected function setUpGPlusInteractions(array $options) {
        //get options
        $client_id = $options['google_plus_client_id']->option_value;
        $client_secret = $options['google_plus_client_secret']->option_value;

        //prep redirect URI
        $config = Config::getInstance();
        $site_root_path = $config->getValue('site_root_path');
        $ssl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != '')?'s':'');
        $redirect_uri = urlencode('http'.$ssl.'://'.$_SERVER['SERVER_NAME']. $site_root_path.'account/?p=google%2B');

        //create OAuth link
        $oauth_link = "https://accounts.google.com/o/oauth2/auth?client_id=".$client_id.
        "&redirect_uri=".$redirect_uri."&scope=https://www.googleapis.com/auth/plus.me&response_type=code";
        $this->addToView('oauth_link', $oauth_link);

        // Google provided a code to get an access token
        if (isset($_GET['code'])) {
            $code = $_GET['code'];

            $crawler = new GooglePlusCrawler(null, null);
            $tokens = $crawler->getOAuthTokens($client_id, $client_secret, $code, 'authorization_code',
            $redirect_uri);
            if (isset($tokens->error)) {
                $this->addErrorMessage("Oops! Something went wrong while obtaining OAuth tokens.<br>Google says \"".
                $tokens->error.".\" Please double-check your settings and try again.");
            } else {
                $gplus_api_accessor = new GooglePlusAPIAccessor();
                $gplus_user = $gplus_api_accessor->apiRequest('people/me', $tokens->access_token, null);
                $gplus_user_id = $gplus_user->id;
                $gplus_username = $gplus_user->displayName;

                $this->saveAccessTokens($gplus_user_id, $gplus_username, $tokens->access_token,
                $tokens->refresh_token);
            }
        }
    }

    /**
     * Save newly-acquired OAuth access tokens to application options.
     * @param str $gplus_user_id
     * @param str $gplus_username
     * @param str $access_token
     * @param str $refresh_token
     * @return str Success message
     */
    protected function saveAccessTokens($gplus_user_id, $gplus_username, $access_token, $refresh_token) {
        $msg = '';
        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
        $user_dao = DAOFactory::getDAO('UserDAO');

        $instance = $instance_dao->getByUserIdOnNetwork($gplus_user_id, 'google+');
        if (isset($instance)) {
            $owner_instance = $owner_instance_dao->get($this->owner->id, $instance->id);
            if ($owner_instance == null) { //Instance already exists, owner instance doesn't
                //Add owner instance with session key
                $owner_instance_dao->insert($this->owner->id, $instance->id, $access_token, $refresh_token);
                $msg .= "Success! Your Google+ account has been added to ThinkUp.";
            } else {
                $owner_instance_dao->updateTokens($this->owner->id, $instance->id, $access_token, $refresh_token);
                $msg .= "Success! You've reconnected your Google+ account. To connect a different account, log ".
                "out of Google in a different browser tab and try again.";
            }
        } else { //Instance does not exist
            $instance_dao->insert($gplus_user_id, $gplus_username, 'google+');
            $instance = $instance_dao->getByUserIdOnNetwork($gplus_user_id, 'google+');
            $owner_instance_dao->insert(
            $this->owner->id,
            $instance->id, $access_token, $refresh_token);
            $msg .= "Success! Your Google+ account has been added to ThinkUp.";
        }

        if (!$user_dao->isUserInDB($gplus_user_id, 'google+')) {
            $r = array('user_id'=>$gplus_user_id, 'user_name'=>$gplus_username,'full_name'=>$gplus_username, 'avatar'=>'',
            'location'=>'', 'description'=>'', 'url'=>'', 'is_protected'=>'',  'follower_count'=>0,
            'friend_count'=>0, 'post_count'=>0, 'last_updated'=>'', 'last_post'=>'', 'joined'=>'',
            'last_post_id'=>'', 'network'=>'facebook' );
            $u = new User($r, 'Owner info');
            $user_dao->updateUser($u);
        }

        if ($msg != '') {
            $this->addSuccessMessage($msg);
        }

        $this->view_mgr->clear_all_cache();

        return $msg;
    }
}
