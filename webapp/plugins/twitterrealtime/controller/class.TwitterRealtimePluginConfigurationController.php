<?php
/**
 *
 * ThinkUp/webapp/plugins/twitterrealltime/controller/class.TwitterRealtimePluginConfigurationController.php
 *
 * Copyright (c) 2011-2013 Mark Wilkie, Amy Unruh
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
 * Twitter Realtime Plugin Configuration Controller
 *
 * Handles plugin configuration requests.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @author Amy Unruh
 */
class TwitterRealtimePluginConfigurationController extends PluginConfigurationController {
    /**
     *
     * @var Owner
     */
    var $owner;
    /**
     * @var $int php major version num required for redis
     */
    var $php_major_version_for_redis = 5;
    /**
     * @var $int php minor version num required for redis
     */
    var $php_minor_version_for_redis = 3;
    /**
     * @return str
     */
    public function authControl() {
        $config = Config::getInstance();
        Loader::definePathConstants();
        $this->setViewTemplate(THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/view/twitterrealtime.account.index.tpl');
        $this->view_mgr->addHelp('twitterrealtime', 'userguide/settings/plugins/twitterrealtime');

        $id = DAOFactory::getDAO('InstanceDAO');
        $od = DAOFactory::getDAO('OwnerDAO');

        // get plugin option values if defined...
        $plugin_options = $this->getPluginOptions();

        // get oauth option values from twitter plugin.
        // @TODO -- what is the right way to do this?
        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $twitter_options = $plugin_option_dao->getOptionsHash('twitter', true);
        $oauth_consumer_key = null;
        if (isset($twitter_options['oauth_consumer_key'])) {
            $oauth_consumer_key = $twitter_options['oauth_consumer_key']->option_value;
        }
        $oauth_consumer_secret = null;
        if (isset($twitter_options['oauth_consumer_secret'])) {
            $oauth_consumer_secret = $twitter_options['oauth_consumer_secret']->option_value;
        }

        // @TODO - get any other option values as necessary
        // $archive_limit = $this->getPluginOption('archive_limit');

        $auth_from_twitter = '';
        if (isset($oauth_consumer_key) && isset($oauth_consumer_secret)) {
            $to = new TwitterOAuth($oauth_consumer_key, $oauth_consumer_secret);
            /* Request tokens from twitter */
            $tok = $to->getRequestToken();
            if (isset($tok['oauth_token'])) {
                $token = $tok['oauth_token'];
                $_SESSION['oauth_request_token_secret'] = $tok['oauth_token_secret'];

                /* Build the authorization URL */
                $oauthorize_link = $to->getAuthorizeURL($token);
                // create indication that auth from twitter plugin is okay
                $auth_from_twitter = "Using the Twitter Consumer key and secret as set in " .
                                     "the <a href=\"./?p=twitter\">Twitter plugin</a>.";
            } else {
                //set error message here
                $this->addErrorMessage(
                "Unable to obtain OAuth token. Check your Twitter plugin consumer key and secret configuration.");
                $oauthorize_link = '';
            }
        } else {
            $this->addErrorMessage(
                "Missing required settings! Please configure the Twitter plugin.");
            $oauthorize_link = '';
        }
        $owner_instances = $id->getByOwnerAndNetwork($this->owner, 'twitter');

        $this->addToView('owner_instances', $owner_instances);
        $this->addToView('oauthorize_link', $oauthorize_link);
        $this->addToView('auth_from_twitter', $auth_from_twitter);

        // add plugin options from
        $this->addOptionForm();
        $plugin = new TwitterRealtimePlugin();
        $this->addToView('is_configured', $plugin->isConfigured());

        return $this->generateView();
    }

    /**
     * Set plugin option fields for admin/plugin form
     */
    private function addOptionForm() {

        // for now at least, require consumer auth info to be the same as the twitter plugin, since the
        // accounts are all auth'd against that key.  So, we don't have fields for those options here.
        // When the Twitter Realtime plugin is started up, it will exit
        // without doing its stuff if the auth info is not set in the Twitter plugin.

        $php_path_label = 'Path to the PHP interpreter to use';
        $php_path = array('name' => 'php_path', 'label' => $php_path_label,
        // @TODO - should this have a default set?
        //'default_value' => '/usr/bin/php'
        );
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $php_path);

        $has_redis = $this->isRedisSupported();
        $use_redis_label = "Use Redis"; // @TODO -- more information
        if ($has_redis) {
            $use_redis = array('name' => 'use_redis', 'label' => $use_redis_label,
            'values' => array('True' => 'true', 'False' => 'false'), 'default_value' => 'false');
            $this->addPluginOption(self::FORM_RADIO_ELEMENT, $use_redis);
        }
    }

    /**
     * Do we have redis support?
     * @returns boolean
     */
    private function isRedisSupported() {
        $version = explode('.', PHP_VERSION);
        // check whether predis is supported. first part of this check is overkill-
        // if major v. is less than 5, we should not even be running
        if (!($version[0] >= $this->php_major_version_for_redis &&
        $version[1] >= $this->php_minor_version_for_redis)) {
            return false;
        }
        // can i ping a redis server?
        $redis_status = false;
        $redis = null;
        require_once THINKUP_WEBAPP_PATH . 'plugins/twitterrealtime/extlib/predis/lib/Predis.php';
        eval('$redis = new Predis\Client();'); //for php less than 5.3
        if (!is_null($redis)) {
            try {
                $resp = $redis->ping();
                $redis_status = true;
            }
            catch (Exception $e) {
                error_log("Exception: " . $e->getMessage() . ". Check that the Redis server is running.");
            }
        }
        return $redis_status;
    }
}
