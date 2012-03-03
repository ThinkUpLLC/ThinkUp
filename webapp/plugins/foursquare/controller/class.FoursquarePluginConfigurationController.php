<?php
/**
 * ThinkUp/webapp/plugins/foursquare/controller/class.FoursquarePluginConfigurationController.php
 *
 * Copyright (c) 2012 Aaron Kalair
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
 * foursquare plugin configuration controller
 * This class gets the OAuth tokens from Foursquare and saves them in the database
 *
 * @author Aaron Kalair <aaronkalair[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012 Aaron Kalair
 */

class FoursquarePluginConfigurationController extends PluginConfigurationController {

    /**
     *
     * @var Owner An owner object for the user adding this plugin to his / her account
     */
    var $owner;

    /**
     * Constructor
     * @param Owner $owner
     * @return FoursquarePluginConfigurationController
     */
    public function __construct($owner) {
        /* Call the parents class constructor with the ID of the TU user who is adding this plugin to there dashboard
         * and the folder the plugin lives in
         */
        parent::__construct($owner, 'foursquare');
        // Disable caching
        $this->disableCaching();
        // Set the global owner variable to the value passed into the constructor
        $this->owner = $owner;
    }

    public function authControl() {
        // Get an instance
        $config = Config::getInstance();
        // Set up some constants
        Loader::definePathConstants();
        // Set the view to the account index page for this plugin
        $this->setViewTemplate(THINKUP_WEBAPP_PATH.'plugins/foursquare/view/foursquare.account.index.tpl' );
        // URL to the help page for this plugin
        $this->view_mgr->addHelp('foursquare', 'userguide/settings/plugins/foursquare');

        // Set some option fields on the template page

        // Set the client ID text field
        $name_field = array('name' => 'foursquare_client_id', 'label' => 'Client ID', 'size' => 48);
        // Set the default value to be blank
        $name_field['default_value'] = '';
        // Add the element to the page
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $name_field);
        // Set a message saying this field is required
        $this->addPluginOptionRequiredMessage('foursquare_client_id', 'A client id is requried to use foursquare');

        // Set the client secret field
        $name_field = array('name' => 'foursquare_client_secret', 'label' => 'Client Secret', 'size' => 48);
        // Set the default value to be blank
        $name_field['default_value'] = '';
        // Add the element to the page
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $name_field);
        // Set a message saying this field is required
        $this->addPluginOptionRequiredMessage('foursquare_client_secret',
        'A client secret is requried to use foursquare');

        // Get a data access object so we can get the options for the plugin from the database
        $plugin_option_dao = DAOFactory::getDAO('PluginOptionDAO');
        // Get a cached hash of the options from the database
        $options = $plugin_option_dao->getOptionsHash('foursquare', true);

        // Create a new plugin object
        $plugin = new FoursquarePlugin();

        // If the plugin is configured
        if ($plugin->isConfigured()) {
            // Set up the interactions
            $this->setUpFoursquareInteractions($options);
            // Indicate on the view that this plugin is configured
            $this->addToView('is_configured', true);
        } else {
            // If the plugin isn't configured
            // Tell the user that this plugin needs configuring
            $this->addInfoMessage('Please complete plugin setup to start using it', 'setup');
            // Indicate on the view that this plugin is not configured
            $this->addToView('is_configured', false);
        }

        $this->addToView('thinkup_site_url', Utils::getApplicationURL());

        // Display the foursquare account index page
        return $this->generateView();
    }

    /**
     * Add user auth link or process incoming auth requests.
     * @param array $options Plugin options array
     */
    protected function setUpFoursquareInteractions(array $options) {
        // Get the client ID and secret
        $client_id = $options['foursquare_client_id']->option_value;
        $client_secret = $options['foursquare_client_secret']->option_value;

        // Set up the redirect URL
        // Get a new configuration instance
        $config = Config::getInstance();
        // Get the root path of our install
        $site_root_path = $config->getValue('site_root_path');
        // If the server supports ssl add an s to our URL path
        $ssl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != '')?'s':'');
        // Generate the redirect URL
        $redirect_uri = urlencode('http'.$ssl.'://'.$_SERVER['SERVER_NAME'].
        $site_root_path.'account/?p=foursquare');

        // Create the OAuth link based on foursquares instructions here: https://developer.foursquare.com/overview/auth
        $oauth_link = "https://foursquare.com/oauth2/authenticate?client_id=".$client_id."&response_type=code";
        $oauth_link .= "&redirect_uri=".$redirect_uri;
        // Add the link for the user to click to the page
        $this->addToView('oauth_link', $oauth_link);

        // If we are here because they have been redirect back by foursquare with a OAuth token
        if (isset($_GET['code'])) {
            // Get the code foursquare provided from the URL
            $code = $_GET['code'];
            // Create a new crawler, as this class contains the method for retriving our tokens
            $crawler = new FoursquareCrawler(null, null);
            // Get the OAuth Tokens
            $tokens = $crawler->getOAuthTokens($client_id, $client_secret, $redirect_uri, $code);
            // If foursquare return an error
            if (isset($tokens->error)) {
                // Tell the user something went wrong
                $this->addErrorMessage("Oops! Something went wrong while obtaining OAuth tokens. foursquare says \"".
                $tokens->error.".\" Please double-check your settings and try again.", 'authorization');
            } else {
                // If we got some OAuth tokens back, check they are valid
                $foursquare_api_accessor = new FoursquareAPIAccessor();
                // Make a query for the users details on foursquare
                $foursquare_user = $foursquare_api_accessor->apiRequest('users/self', $tokens->access_token);
                // If foursquare returned an error after that request
                if (isset($foursquare_user->error) || !isset($foursquare_user->response->user->id)
                || !isset($foursquare_user->response->user->contact->email)) {
                    $this->addErrorMessage("Oops! Something went wrong querying the foursquare API.
                         foursquare says \"". Utils::varDumpToString($foursquare_user).
                        ".\" Please double-check your settings and try again.", 'authorization');
                } else {
                    // Everything went fine so store the details in the database
                    // Set the user ID and username based on details returned by foursquare
                    $foursquare_user_id = $foursquare_user->response->user->id;
                    $foursquare_username = $foursquare_user->response->user->contact->email;
                    // Save the tokens in the database
                    $this->saveAccessTokens($foursquare_user_id, $foursquare_username, $tokens->access_token);
                }
            }
        }

        // Create a new instance DAO
        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        // Get the owner of this instance
        $owner_instances = $instance_dao->getByOwnerAndNetwork($this->owner, 'foursquare');
        // Add all owners of foursquare instances to the view
        $this->addToView('owner_instances', $owner_instances);
    }

    /**
     * Save newly-acquired OAuth access tokens to application options.
     * @param str $foursquare_user_id
     * @param str $foursquare_username
     * @param str $access_token
     * @return void
     */
    protected function saveAccessTokens($foursquare_user_id, $foursquare_username, $access_token) {
        // Create a new instance DAO
        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        // Create a new owner instance DAO
        $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
        // Create a new user DAO
        $user_dao = DAOFactory::getDAO('UserDAO');
        // Get the owner of this instance
        $instance = $instance_dao->getByUserIdOnNetwork($foursquare_user_id, 'foursquare');

        // If the instance is set
        if (isset($instance)) {
            // Get the instance
            $owner_instance = $owner_instance_dao->get($this->owner->id, $instance->id);

            //Instance already exists, owner instance doesn't
            if ($owner_instance == null) {
                //Add owner instance (foursquare doesn't support a refresh token)
                $owner_instance_dao->insert($this->owner->id, $instance->id, $access_token, '');
                $this->addSuccessMessage("Success! Your foursquare account has been added to ThinkUp.", 'user_add');
            } else {
                // Update this users tokens  (foursquare doesn't support a refresh token)
                $owner_instance_dao->updateTokens($this->owner->id, $instance->id, $access_token, '');
                $this->addSuccessMessage("Success! You've reconnected your foursquare account. To connect a different ".
                "account, log out of foursquare in a different browser tab and try again.", 'user_add');
            }
        } else { //Instance does not exist
            // Create an instance of a foursquare user
            $instance_dao->insert($foursquare_user_id, $foursquare_username, 'foursquare');
            // Get the owner of this instance
            $instance = $instance_dao->getByUserIdOnNetwork($foursquare_user_id, 'foursquare');
            // Insert an owner instance
            $owner_instance_dao->insert( $this->owner->id, $instance->id, $access_token, '');
            // Tell the user all is well
            $this->addSuccessMessage("Success! Your foursquare account has been added to ThinkUp.", 'user_add');
        }

        // If the user is not in the database
        if (!$user_dao->isUserInDB($foursquare_user_id, 'foursquare')) {
            // Create an array with this users information
            $r = array('user_id'=>$foursquare_user_id, 'user_name'=>$foursquare_username,
            'full_name'=>'', 'avatar'=>'', 'location'=>'', 'description'=>'',
            'url'=>'http://www.foursquare.com/user/'.$foursquare_user_id, 'is_protected'=>'0',
            'follower_count'=>null, 'friend_count'=>null, 'post_count'=>null, 'last_updated'=>'', 'last_post'=>null,
            'joined'=>'', 'last_post_id'=>'', 'network'=>'foursquare' );
            // Create a new user with this information
            $u = new User($r, 'Owner info');
            // Insert them into the database
            $user_dao->updateUser($u);
        }
        // Clear the cache
        $this->view_mgr->clear_all_cache();
    }
}
