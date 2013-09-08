<?php
/**
 *
 * ThinkUp/webapp/plugins/GooglePlus/controller/class.GooglePlusPluginConfigurationController.php
 *
 * Copyright (c) 2011-2013 Gina Trapani
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
 * GooglePlus Plugin Configuration Controller
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Gina Trapani
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
        Loader::definePathConstants();
        $this->setViewTemplate( THINKUP_WEBAPP_PATH.'plugins/googleplus/view/googleplus.account.index.tpl');
        $this->view_mgr->addHelp('googleplus', 'userguide/settings/plugins/googleplus');

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

        $plugin = new GooglePlusPlugin();
        if ($plugin->isConfigured()) {
            $this->setUpGPlusInteractions($options);
            $this->addToView('is_configured', true);
        } else {
            $this->addInfoMessage('Please complete plugin setup to start using it.', 'setup');
            $this->addToView('is_configured', false);
        }

        $this->addToView('thinkup_site_url', Utils::getApplicationURL());
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
        $redirect_uri = urlencode(Utils::getApplicationURL() .'account/?p=google%2B');

        //create OAuth link
        $oauth_link = "https://accounts.google.com/o/oauth2/auth?client_id=".$client_id.
        "&redirect_uri=".$redirect_uri.
        "&scope=https://www.googleapis.com/auth/plus.me&response_type=code&access_type=offline&approval_prompt=force";
        $this->addToView('oauth_link', $oauth_link);

        // Google provided a code to get an access token
        if (isset($_GET['code'])) {
            $code = $_GET['code'];

            $crawler_plugin_registrar = new GooglePlusCrawler(null, null);
            $tokens = $crawler_plugin_registrar->getOAuthTokens($client_id, $client_secret, $code, 'authorization_code',
            $redirect_uri);
            if (isset($tokens->error)) {
                $this->addErrorMessage("Oops! Something went wrong while obtaining OAuth tokens.<br>Google says \"".
                $tokens->error.".\" Please double-check your settings and try again.", 'authorization');
            } else {
                if (isset($tokens->access_token) && isset($tokens->access_token)) {
                    //Get user data
                    $gplus_api_accessor = new GooglePlusAPIAccessor();
                    $gplus_user = $gplus_api_accessor->apiRequest('people/me', $tokens->access_token, null);
                    if (isset($gplus_user->error)) {
                        if ($gplus_user->error->code == "403"
                        && $gplus_user->error->message == 'Access Not Configured') {
                            $this->addErrorMessage("Oops! Looks like Google+ API access isn't turned on. ".
                            "<a href=\"http://code.google.com/apis/console#access\">In the Google APIs console</a>, ".
                            "in Services, flip the Google+ API Status switch to 'On' and try again.", 'authorization');
                        } else {
                            $this->addErrorMessage("Oops! Something went wrong querying the Google+ API.<br>".
                            "Google says \"". $gplus_user->error->code.": ".$gplus_user->error->message.
                            ".\" Please double-check your settings and try again.", 'authorization');
                        }
                    } else {
                        if (isset($gplus_user->id) && isset($gplus_user->displayName)) {
                            $gplus_user_id = $gplus_user->id;
                            $gplus_username = $gplus_user->displayName;
                            //Process tokens
                            $this->saveAccessTokens($gplus_user_id, $gplus_username, $tokens->access_token,
                            $tokens->refresh_token);
                        } else {
                            $this->addErrorMessage("Oops! Something went wrong querying the Google+ API.<br>".
                            "Google says \"". Utils::varDumpToString($gplus_user).
                            ".\" Please double-check your settings and try again.", 'authorization');
                        }
                    }
                } else {
                    $this->addErrorMessage("Oops! Something went wrong while obtaining OAuth tokens.<br>Google says \"".
                    Utils::varDumpToString($tokens).".\" Please double-check your settings and try again.",
                    'authorization');
                }
            }
        }
        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $owner_instances = $instance_dao->getByOwnerAndNetwork($this->owner, 'google+');
        $this->addToView('owner_instances', $owner_instances);
    }

    /**
     * Save newly-acquired OAuth access tokens to application options.
     * @param str $gplus_user_id
     * @param str $gplus_username
     * @param str $access_token
     * @param str $refresh_token
     * @return void
     */
    protected function saveAccessTokens($gplus_user_id, $gplus_username, $access_token, $refresh_token) {
        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
        $user_dao = DAOFactory::getDAO('UserDAO');

        $instance = $instance_dao->getByUserIdOnNetwork($gplus_user_id, 'google+');
        if (isset($instance)) {
            $owner_instance = $owner_instance_dao->get($this->owner->id, $instance->id);
            if ($owner_instance == null) { //Instance already exists, owner instance doesn't
                //Add owner instance with session key
                $owner_instance_dao->insert($this->owner->id, $instance->id, $access_token, $refresh_token);
                $this->addSuccessMessage("Success! Your Google+ account has been added to ThinkUp.", 'user_add');
            } else {
                $owner_instance_dao->updateTokens($this->owner->id, $instance->id, $access_token, $refresh_token);
                $this->addSuccessMessage("Success! You've reconnected your Google+ account. To connect a different ".
                "account, log out of Google in a different browser tab and try again.", 'user_add');
            }
        } else { //Instance does not exist
            $instance_dao->insert($gplus_user_id, $gplus_username, 'google+');
            $instance = $instance_dao->getByUserIdOnNetwork($gplus_user_id, 'google+');
            $owner_instance_dao->insert(
            $this->owner->id,
            $instance->id, $access_token, $refresh_token);
            $this->addSuccessMessage("Success! Your Google+ account has been added to ThinkUp.", 'user_add');
        }

        if (!$user_dao->isUserInDB($gplus_user_id, 'google+')) {
            $r = array('user_id'=>$gplus_user_id, 'user_name'=>$gplus_username,'full_name'=>$gplus_username,
            'avatar'=>'', 'location'=>'', 'description'=>'', 'url'=>'', 'is_verified'=>'', 'is_protected'=>'',
            'follower_count'=>0, 'friend_count'=>0, 'post_count'=>0, 'last_updated'=>'', 'last_post'=>'',
            'joined'=>'0000-00-00 00:00:00', 'last_post_id'=>'', 'network'=>'google+' );
            $u = new User($r, 'Owner info');
            $user_dao->updateUser($u);
        }
        $this->view_mgr->clear_all_cache();
    }
}
