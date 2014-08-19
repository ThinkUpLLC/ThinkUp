<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/model/class.TwitterPlugin.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
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
 * Twitter Plugin
 *
 * Twitter crawler and webapp plugin retrieves data from Twitter and displays it.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TwitterPlugin extends Plugin implements CrawlerPlugin {

    public function __construct($vals = null) {
        parent::__construct($vals);
        $this->folder_name = 'twitter';
        $this->addRequiredSetting('oauth_consumer_key');
        $this->addRequiredSetting('oauth_consumer_secret');
    }

    public function activate() {
    }

    public function deactivate() {
        //Pause all active Twitter instances
        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $twitter_instances = $instance_dao->getAllInstances("DESC", true, "twitter");
        foreach ($twitter_instances as $ti) {
            $instance_dao->setActive($ti->id, false);
        }
    }

    public function crawl() {
        $config = Config::getInstance();
        $logger = Logger::getInstance();
        $instance_dao = DAOFactory::getDAO('TwitterInstanceDAO');
        $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $instance_hashtag_dao = DAOFactory::getDAO('InstanceHashtagDAO');

        // get oauth values
        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash('twitter', true);

        $current_owner = $owner_dao->getByEmail(Session::getLoggedInUser());

        $instances = $instance_dao->getActiveInstancesStalestFirstForOwnerByNetworkNoAuthError($current_owner,
        'twitter');
        foreach ($instances as $instance) {
            $logger->setUsername($instance->network_username);
            $logger->logUserSuccess("Starting to collect data for ".$instance->network_username." on Twitter.",
            __METHOD__.','.__LINE__);

            $tokens = $owner_instance_dao->getOAuthTokens($instance->id);

            $num_twitter_errors =
            isset($options['num_twitter_errors']) ? $options['num_twitter_errors']->option_value : null;

            $dashboard_module_cacher = new DashboardModuleCacher($instance);

            try {
                if (isset($tokens['oauth_access_token']) && $tokens['oauth_access_token'] != ''
                && isset($tokens['oauth_access_token_secret']) && $tokens['oauth_access_token_secret'] != '') {
                    $archive_limit = isset($options['archive_limit']->option_value)?
                    $options['archive_limit']->option_value:3200;
                    $api = new CrawlerTwitterAPIAccessorOAuth($tokens['oauth_access_token'],
                    $tokens['oauth_access_token_secret'], $options['oauth_consumer_key']->option_value,
                    $options['oauth_consumer_secret']->option_value, $archive_limit,
                    $num_twitter_errors);

                    $twitter_crawler = new TwitterCrawler($instance, $api);

                    $instance_dao->updateLastRun($instance->id);

                    $twitter_crawler->fetchInstanceUserTweets();
                    $twitter_crawler->fetchInstanceUserMentions();
                    $twitter_crawler->fetchInstanceUserFriends();
                    $twitter_crawler->fetchInstanceUserFollowers();
                    $twitter_crawler->fetchInstanceUserGroups();
                    $twitter_crawler->fetchRetweetsOfInstanceUser();
                    $twitter_crawler->fetchInstanceUserFavorites();
                    $twitter_crawler->updateStaleGroupMemberships();
                    $twitter_crawler->fetchStrayRepliedToTweets();
                    $twitter_crawler->fetchUnloadedFollowerDetails();
                    $twitter_crawler->cleanUpFollows();

                    //Retrieve search results for saved keyword/hashtags
                    $instances_hashtags = $instance_hashtag_dao->getByInstance($instance->id);
                    foreach ($instances_hashtags as $instance_hashtag) {
                        $twitter_crawler->fetchInstanceHashtagTweets($instance_hashtag);
                    }
                } else {
                    throw new Exception('Missing Twitter OAuth tokens.');
                }
            } catch (Exception $e) {
                $logger->logUserError(get_class($e) ." while crawling ".$instance->network_username." on Twitter: ".
                $e->getMessage(), __METHOD__.','.__LINE__);
            }
            $dashboard_module_cacher->cacheDashboardModules();

            // Save instance
            if (isset($twitter_crawler->user)) {
                $instance_dao->save($instance, $twitter_crawler->user->post_count, $logger);
            }
            Reporter::reportVersion($instance);

            $logger->logUserSuccess("Finished collecting data for ".$instance->network_username.
            " on Twitter.", __METHOD__.','.__LINE__);
        }
    }

    public function renderConfiguration($owner) {
        $controller = new TwitterPluginConfigurationController($owner, 'twitter');
        return $controller->go();
    }

    public function renderInstanceConfiguration($owner, $instance_username, $instance_network) {
        $controller = new TwitterPluginHashtagConfigurationController($owner, 'twitter', $instance_username);
        return $controller->go();
    }

    /**
     * Defines the ordering of replies in the post page (/post/?t=...)
     *
     * @param $order_by Order by distance ('location') or not ('default')
     * @return string Ordering, to be used in a SQL 'ORDER BY' statement
     */
    public static function repliesOrdering($order_by) {
        if ($order_by == 'location') {
            return "geo_status, reply_retweet_distance, is_reply_by_friend DESC, follower_count DESC";
        } else {
            return "is_reply_by_friend DESC, follower_count DESC";
        }
    }
}
