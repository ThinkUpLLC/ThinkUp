<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/controller/class.TwitterPluginConfigurationController.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Mark Wilkie
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
 * Twitter Plugin Configuration Controller
 *
 * Handles plugin configuration requests.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Mark Wilkie
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TwitterPluginConfigurationController extends PluginConfigurationController {

    public function authControl() {
        $config = Config::getInstance();
        Loader::definePathConstants();
        $this->setViewTemplate(THINKUP_WEBAPP_PATH.'plugins/twitter/view/twitter.account.index.tpl');
        $this->view_mgr->addHelp('twitter', 'userguide/settings/plugins/twitter/index');

        $instance_dao = DAOFactory::getDAO('InstanceDAO');

        // get plugin option values if defined...
        $plugin_options = $this->getPluginOptions();
        $oauth_consumer_key = $this->getPluginOption('oauth_consumer_key');
        $oauth_consumer_secret = $this->getPluginOption('oauth_consumer_secret');
        $archive_limit = $this->getPluginOption('archive_limit');
        $num_twitter_errors = $this->getPluginOption('num_twitter_errors');

        $this->addToView('twitter_app_name', "ThinkUp ". $_SERVER['SERVER_NAME']);
        $this->addToView('thinkup_site_url', Utils::getApplicationURL(true));

        $plugin = new TwitterPlugin();
        if ($plugin->isConfigured()) {
            $this->addToView('is_configured', true);
            $owner_instances = $instance_dao->getByOwnerAndNetwork($this->owner, 'twitter');
            $this->addToView('owner_instances', $owner_instances);
            if (isset($this->owner) && $this->owner->isMemberAtAnyLevel()) {
                if ($this->owner->isMemberLevel()) {
                    if (sizeof($owner_instances) > 0) {
                        $this->do_show_add_button = false;
                        $this->addInfoMessage("To connect another Twitter account to ThinkUp, upgrade your membership.",
                        'membership_cap');
                    }
                }
            }
            if (isset($_GET['oauth_token']) || $this->do_show_add_button ) {
                $twitter_oauth = new TwitterOAuth($oauth_consumer_key, $oauth_consumer_secret);
                /* Request tokens from twitter */
                $token_array = $twitter_oauth->getRequestToken(Utils::getApplicationURL(true)."account/?p=twitter");

                if (isset($token_array['oauth_token']) || Utils::isTest()) {
                    $token = $token_array['oauth_token'];
                    SessionCache::put('oauth_request_token_secret', $token_array['oauth_token_secret']);

                    if (isset($_GET['oauth_token'])) {
                        self::addAuthorizedUser($oauth_consumer_key, $oauth_consumer_secret, $num_twitter_errors);
                    }

                    if ($this->do_show_add_button) {
                        /* Build the authorization URL */
                        $oauthorize_link = $twitter_oauth->getAuthorizeURL($token);
                        $this->addToView('oauthorize_link', $oauthorize_link);
                    }
                } else {
                    //set error message here
                    $this->addErrorMessage(
                    "Unable to obtain OAuth tokens from Twitter. Please double-check the consumer key and secret ".
                    "are correct.", "setup");
                    $oauthorize_link = '';
                    $this->addToView('is_configured', false);
                }
            }
        } else {
            $this->addInfoMessage('Please complete plugin setup to start using it.', 'setup');
            $this->addToView('is_configured', false);
        }
        // add plugin options from
        $this->addOptionForm();

        return $this->generateView();
    }

    /**
     * Set plugin option fields for admin/plugin form
     */
    private function addOptionForm() {
        $oauth_consumer_key = array('name' => 'oauth_consumer_key', 'label' => 'Consumer key', 'size' => 27);
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $oauth_consumer_key);

        $oauth_consumer_secret = array('name' => 'oauth_consumer_secret', 'label' => 'Consumer secret', 'size' => 50);
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $oauth_consumer_secret);
        $archive_limit_label = 'Pagination Limit <span style="font-size: 10px;">' .
        '[<a href="http://dev.twitter.com/pages/every_developer" title="Twitter still maintains a database '.
        'of all the tweets sent by a user. However, to ensure performance of the site, this artificial limit of '.
        '3,200 posts is temporarily in place." target="_blank">?</a>]</span>';
        $archive_limit = array('name' => 'archive_limit','label' => $archive_limit_label, 'default_value' => '3200',
        'advanced'=> true);
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $archive_limit);

        $num_twitter_errors_label = 'Total API Errors to Tolerate';
        $num_twitter_errors = array('name' => 'num_twitter_errors', 'label' => $num_twitter_errors_label,
        'default_value' => '5', 'advanced'=>true, 'size'=>3);
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $num_twitter_errors);

        $tweet_count_per_call_label = 'Tweet Count to Return Per API Call';
        $tweet_count_per_call = array('name' => 'tweet_count_per_call', 'label' => $tweet_count_per_call_label,
        'default_value' => '100', 'advanced'=> true, 'size'=>3);
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $tweet_count_per_call);
    }

    /**
     * Add user who just returned from Twitter.com OAuth authorization and populate view with error/success messages.
     * @param str $oauth_consumer_key
     * @param str $oauth_consumer_secret
     * @param str $num_twitter_errors
     * @return void
     */
    private function addAuthorizedUser($oauth_consumer_key, $oauth_consumer_secret, $num_twitter_errors) {
        $request_token = $_GET['oauth_token'];
        $request_token_secret = SessionCache::get('oauth_request_token_secret');

        $twitter_oauth = new TwitterOAuth($oauth_consumer_key, $oauth_consumer_secret, $request_token,
        $request_token_secret);
        if (isset($_GET['oauth_verifier'])) {
            $token_array = $twitter_oauth->getAccessToken($_GET['oauth_verifier']);
        } else {
            $token_array = null;
        }

        if (isset($token_array['oauth_token']) && isset($token_array['oauth_token_secret'])) {
            $api = new TwitterAPIAccessorOAuth($token_array['oauth_token'], $token_array['oauth_token_secret'],
                $oauth_consumer_key, $oauth_consumer_secret, $num_twitter_errors, false);

            $authed_twitter_user = $api->verifyCredentials();
            //                echo "User ID: ". $authed_twitter_user['user_id']."<br>";
            //                echo "User name: ". $authed_twitter_user['user_name']."<br>";

            if ( isset($authed_twitter_user) && isset($authed_twitter_user['user_name'])
            && isset($authed_twitter_user['user_id'])) {
                $instance_dao = DAOFactory::getDAO('TwitterInstanceDAO');
                $instance = $instance_dao->getByUsername($authed_twitter_user['user_name'], 'twitter');
                $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
                if (isset($instance)) {
                    $owner_instance = $owner_instance_dao->get($this->owner->id, $instance->id);
                    if ($owner_instance != null) {
                        $owner_instance_dao->updateTokens($this->owner->id, $instance->id, 
                        $token_array['oauth_token'], $token_array['oauth_token_secret']);
                        $this->addSuccessMessage($authed_twitter_user['user_name'].
                        " on Twitter is already set up in ThinkUp! To add a different Twitter account, ".
                        "log out of Twitter.com in your browser and authorize ThinkUp again.", 'user_add');
                    } else {
                        if ($owner_instance_dao->insert($this->owner->id, $instance->id,
                        $token_array['oauth_token'], $token_array['oauth_token_secret'])) {
                            $this->addSuccessMessage("Success! ".$authed_twitter_user['user_name'].
                            " on Twitter has been added to ThinkUp!", "user_add");
                        } else {
                            $this->addErrorMessage("Error: Could not create an owner instance.", "user_add");
                        }
                    }
                } else {
                    $instance_dao->insert($authed_twitter_user['user_id'], $authed_twitter_user['user_name']);
                    $instance = $instance_dao->getByUsername($authed_twitter_user['user_name']);
                    if ($owner_instance_dao->insert( $this->owner->id, $instance->id, $token_array['oauth_token'],
                    $token_array['oauth_token_secret'])) {
                        $this->addSuccessMessage("Success! ".$authed_twitter_user['user_name'].
                        " on Twitter has been added to ThinkUp!", "user_add");
                    } else {
                        $this->addErrorMessage("Error: Could not create an owner instance.", "user_add");
                    }
                }
            }
        } else {
            $msg = "Error: Twitter authorization did not complete successfully. Check if your account already ".
            " exists. If not, please try again.";
            $this->addErrorMessage($msg, "user_add");
        }
        $this->view_mgr->clear_all_cache();
    }
}
