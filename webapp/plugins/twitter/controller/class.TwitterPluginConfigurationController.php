<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/controller/class.TwitterPluginConfigurationController.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Mark Wilkie
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
 * Twitter Plugin Configuration Controller
 *
 * Handles plugin configuration requests.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Mark Wilkie
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TwitterPluginConfigurationController extends PluginConfigurationController {
    /**
     *
     * @var Owner
     */
    var $owner;

    public function authControl() {
        $config = Config::getInstance();
        Utils::defineConstants();
        $this->setViewTemplate(THINKUP_WEBAPP_PATH.'plugins/twitter/view/twitter.account.index.tpl');

        $id = DAOFactory::getDAO('InstanceDAO');
        $od = DAOFactory::getDAO('OwnerDAO');

        // get plugin option values if defined...
        $plugin_options = $this->getPluginOptions();
        $oauth_consumer_key = $this->getPluginOption('oauth_consumer_key');
        $oauth_consumer_secret = $this->getPluginOption('oauth_consumer_secret');
        $archive_limit = $this->getPluginOption('archive_limit');
        $num_twitter_errors = $this->getPluginOption('num_twitter_errors');
        $max_api_calls_per_crawl = $this->getPluginOption('max_api_calls_per_crawl');
        //Add public user instance
        if (isset($_GET['twitter_username'])) { // if form was submitted
            $logger = Logger::getInstance();

            $api = new TwitterAPIAccessorOAuth('NOAUTH', 'NOAUTH', $oauth_consumer_key, $oauth_consumer_secret,
            $num_twitter_errors, $max_api_calls_per_crawl);
            $api_call = str_replace("[id]", $_GET['twitter_username'], $api->cURL_source['show_user']);
            list($cURL_status, $data) = $api->apiRequestFromWebapp($api_call);
            if ($cURL_status == 200) {
                $thisFeed = array();
                try {
                    $xml = $api->createParserFromString(utf8_encode($data));
                    $user = array('user_id'=>$xml->id, 'user_name'=>$xml->screen_name, 'is_protected'=>$xml->protected);
                } catch(Exception $e) {
                    $this->addErrorMessage($e->getMessage());
                }
                if (isset($user) && $user["is_protected"] == 'false') {
                    // if so, add to instances table and owners table

                    $i = $id->getByUsernameOnNetwork($_GET['twitter_username'], 'twitter');
                    $oid = DAOFactory::getDAO('OwnerInstanceDAO');;

                    $msg = '';
                    if (isset($i)) { //Instance exists
                        $oi = $oid->get($this->owner->id, $i->id);
                        if ($oi == null) { //Owner_instance doesn't exist
                            $oid->insert($this->owner->id, $i->id, '', '');
                        }
                    } else { //Instance does not exist
                        $id->insert($user["user_id"], $user["user_name"]);

                        $i = $id->getByUsernameOnNetwork($user["user_name"], 'twitter');
                        $oid->insert($this->owner->id, $i->id, '', '');
                    }
                    $this->addSuccessMessage($_GET['twitter_username']." has been added to ThinkUp.");

                    $this->addSuccessMessage("Added ".$_GET['twitter_username']." to ThinkUp.");
                } else { // if not, return error
                    $this->addErrorMessage($_GET['twitter_username'].
                    " is a private Twitter account; ThinkUp cannot track it without authorization.");
                }
            } else {
                $this->addErrorMessage($_GET['twitter_username']." is not a valid Twitter username.");
            }
        }

        if (isset($oauth_consumer_key) && isset($oauth_consumer_secret)) {
            $to = new TwitterOAuth($oauth_consumer_key, $oauth_consumer_secret);
            /* Request tokens from twitter */
            $tok = $to->getRequestToken();
            if (isset($tok['oauth_token'])) {
                $token = $tok['oauth_token'];
                $_SESSION['oauth_request_token_secret'] = $tok['oauth_token_secret'];

                /* Build the authorization URL */
                $oauthorize_link = $to->getAuthorizeURL($token);
            } else {
                //set error message here
                $this->addErrorMessage(
                "Unable to obtain OAuth token. Check your Twitter consumer key and secret configuration.");
                $oauthorize_link = '';
            }
        } else {
            $this->addErrorMessage(
                "Missing required settings! Please configure the Twitter plugin below.");
            $oauthorize_link = '';
        }
        $owner_instances = $id->getByOwnerAndNetwork($this->owner, 'twitter');

        $this->addToView('owner_instances', $owner_instances);
        $this->addToView('oauthorize_link', $oauthorize_link);

        // add plugin options from
        $this->addOptionForm();

        return $this->generateView();
    }

    /**
     * Set plugin option fields for admin/plugin form
     */
    private function addOptionForm() {

        $oauth_consumer_key = array('name' => 'oauth_consumer_key', 'label' => 'Consumer key');
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $oauth_consumer_key);

        $oauth_consumer_secret = array('name' => 'oauth_consumer_secret', 'label' => 'Consumer secret');
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
        'default_value' => '5', 'advanced'=>true);
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $num_twitter_errors);

        $max_api_calls_per_crawl_label = 'Max API Calls Per Crawl';
        $max_api_calls_per_crawl = array('name' => 'max_api_calls_per_crawl', 'label' => $max_api_calls_per_crawl_label,
        'default_value' => '350', 'advanced'=> true);
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $max_api_calls_per_crawl);
    }
}
