<?php
/**
 *
 * ThinkUp/webapp/_lib/class.DashboardModuleCacher.php
 *
 * Copyright (c) 2012-2013 Gina Trapani
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
 * Dashboard Module Cacher
 *
 * Generate and store dashboard module query results for faster rendering
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class DashboardModuleCacher {
    /**
     *
     * @var Instance
     */
    var $instance;
    public function __construct(Instance $instance) {
        $this->instance = $instance;
        $this->logger = Logger::getInstance();
        $this->logger->setUsername($instance->network_username);
    }
    /**
     * Pre-fetch dashboard module data and store.
     */
    public function cacheDashboardModules() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $simplified_date = date('Y-m-d');

        //Cache FollowMySQLDAO::getLeastLikelyFollowersThisWeek
        $follow_dao = DAOFactory::getDAO('FollowDAO');
        $results = $follow_dao->getLeastLikelyFollowersThisWeek($this->instance->network_user_id,
        $this->instance->network, 13, 1);
        if (isset($results)) {
            //delete existing
            $insight_dao->deleteInsightsBySlug("FollowMySQLDAO::getLeastLikelyFollowersThisWeek", $this->instance->id);
            //insert new
            $insight_dao->insertInsightDeprecated("FollowMySQLDAO::getLeastLikelyFollowersThisWeek",
            $this->instance->id, $simplified_date, '', '', 'dashboard', Insight::EMPHASIS_LOW, serialize($results));
        }

        //Cache PostMySQLDAO::getHotPosts
        $post_dao = DAOFactory::getDAO('PostDAO');
        $hot_posts = $post_dao->getHotPosts($this->instance->network_user_id, $this->instance->network, 10);
        if (sizeof($hot_posts) > 3) {
            $hot_posts_data = self::getHotPostVisualizationData($hot_posts, $this->instance->network);
            //delete existing
            //TODO Go back to deleting this existing data once insights stream doesn't reference it
            //$insight_dao->deleteInsightsBySlug("PostMySQLDAO::getHotPosts", $this->instance->id);
            //insert new
            $insight_dao->insertInsightDeprecated("PostMySQLDAO::getHotPosts", $this->instance->id,
            $simplified_date, '', '', 'dashboard', Insight::EMPHASIS_LOW, serialize($hot_posts_data));
        }

        //Cache ShortLinkMySQLDAO::getRecentClickStats
        $short_link_dao = DAOFactory::getDAO('ShortLinkDAO');
        $click_stats = $short_link_dao->getRecentClickStats($this->instance, 10);
        if (sizeof($click_stats) > 3) {
            $click_stats_data = self::getClickStatsVisualizationData($click_stats);
            //delete existing
            //TODO Go back to deleting this existing data once insights stream doesn't reference it
            //$insight_dao->deleteInsightsBySlug("ShortLinkMySQLDAO::getRecentClickStats", $this->instance->id);
            //insert new
            $insight_dao->insertInsightDeprecated("ShortLinkMySQLDAO::getRecentClickStats", $this->instance->id,
            $simplified_date, '', '', 'dashboard', Insight::EMPHASIS_LOW, serialize($click_stats_data));
        }

        //Cache PostMySQLDAO::getAllPostsByUsernameOrderedBy // getMostRepliedToPostsInLastWeek
        $most_replied_to_1wk = $post_dao->getMostRepliedToPostsInLastWeek($this->instance->network_username,
        $this->instance->network, 5);
        if (sizeof($most_replied_to_1wk) > 1) {
            //delete existing
            $insight_dao->deleteInsightsBySlug("PostMySQLDAO::getMostRepliedToPostsInLastWeek", $this->instance->id);
            //insert new
            $insight_dao->insertInsightDeprecated("PostMySQLDAO::getMostRepliedToPostsInLastWeek", $this->instance->id,
            $simplified_date, '', '', 'dashboard', Insight::EMPHASIS_LOW, serialize($most_replied_to_1wk));
        }

        //Cache PostMySQLDAO::getAllPostsByUsernameOrderedBy // getMostRetweetedPostsInLastWeek
        $most_retweeted_1wk = $post_dao->getMostRetweetedPostsInLastWeek($this->instance->network_username,
        $this->instance->network, 5);
        if (sizeof($most_retweeted_1wk) > 1) {
            //delete existing
            $insight_dao->deleteInsightsBySlug("PostMySQLDAO::getMostRetweetedPostsInLastWeek", $this->instance->id);
            //insert new
            $insight_dao->insertInsightDeprecated("PostMySQLDAO::getMostRetweetedPostsInLastWeek", $this->instance->id,
            $simplified_date, '', '', 'dashboard', Insight::EMPHASIS_LOW, serialize($most_retweeted_1wk));
        }

        //Cache PostMySQLDAO::getClientsUsedByUserOnNetwork
        $clients_usage = $post_dao->getClientsUsedByUserOnNetwork($this->instance->network_user_id,
        $this->instance->network);
        //delete existing
        $insight_dao->deleteInsightsBySlug("PostMySQLDAO::getClientsUsedByUserOnNetwork", $this->instance->id);
        //insert new
        $insight_dao->insertInsightDeprecated("PostMySQLDAO::getClientsUsedByUserOnNetwork", $this->instance->id,
        $simplified_date, '', '', 'dashboard', Insight::EMPHASIS_LOW, serialize($clients_usage));

        //Cache PostMySQLDAO::getOnThisDayFlashbackPosts
        $posts_flashback = $post_dao->getOnThisDayFlashbackPosts($this->instance->network_user_id,
        $this->instance->network);
        //delete existing
        $insight_dao->deleteInsightsBySlug("PostMySQLDAO::getOnThisDayFlashbackPosts", $this->instance->id);
        //insert new
        $insight_dao->insertInsightDeprecated("PostMySQLDAO::getOnThisDayFlashbackPosts", $this->instance->id,
        $simplified_date, '', '', 'dashboard', Insight::EMPHASIS_LOW, serialize($posts_flashback));

        //if it's December or January, cache PostMySQLDAO::getMostPopularPostsOfTheYear
        if (date('n') == 12 || date('n') == 1) {
            if (date('n') == 12) {
                $year = date('Y');
            } else {
                $year = intval(date('Y'))-1;
            }
            //Cache PostMySQLDAO::getMostPopularPostsOfTheYear
            $posts_yearly_popular = $post_dao->getMostPopularPostsOfTheYear($this->instance->network_user_id,
            $this->instance->network, $year, 5);
            //delete existing
            $insight_dao->deleteInsightsBySlug("PostMySQLDAO::getMostPopularPostsOfTheYear", $this->instance->id);
            //insert new
            $insight_dao->insertInsightDeprecated("PostMySQLDAO::getMostPopularPostsOfTheYear", $this->instance->id,
            $simplified_date, '', '', 'dashboard', Insight::EMPHASIS_LOW, serialize($posts_yearly_popular));
        }

        if ($this->instance->network == 'foursquare') {
            // Cache PostMySQLDAO::countCheckinsToPlaceTypesLastWeek
            $checkins_count = $post_dao->countCheckinsToPlaceTypesLastWeek($this->instance->network_user_id,
            $this->instance->network);
            //delete existing
            $insight_dao->deleteInsightsBySlug("PostMySQLDAO::countCheckinsToPlaceTypesLastWeek", $this->instance->id);
            //insert new
            $insight_dao->insertInsightDeprecated("PostMySQLDAO::countCheckinsToPlaceTypesLastWeek",
            $this->instance->id, $simplified_date, '', '', 'dashboard', Insight::EMPHASIS_LOW,
            serialize($checkins_count));

            // Cache PostMySQLDAO::countCheckinsToPlaceTypes
            $checkins_all_time_count = $post_dao->countCheckinsToPlaceTypes($this->instance->network_user_id,
            $this->instance->network);
            //delete existing
            $insight_dao->deleteInsightsBySlug("PostMySQLDAO::countCheckinsToPlaceTypes", $this->instance->id);
            //insert new
            $insight_dao->insertInsightDeprecated("PostMySQLDAO::countCheckinsToPlaceTypes", $this->instance->id,
            $simplified_date, '', '', 'dashboard', Insight::EMPHASIS_LOW, serialize($checkins_all_time_count));

            // Cache PostMySQLDAO::getPostsPerHourDataVis
            $hourly_checkin_datavis = $post_dao->getPostsPerHourDataVis($this->instance->network_user_id,
            $this->instance->network);
            //delete existing
            $insight_dao->deleteInsightsBySlug("PostMySQLDAO::getPostsPerHourDataVis", $this->instance->id);
            //insert new
            $insight_dao->insertInsightDeprecated("PostMySQLDAO::getPostsPerHourDataVis", $this->instance->id,
            $simplified_date, '', '', 'dashboard', Insight::EMPHASIS_LOW, serialize($hourly_checkin_datavis));

            // Cache PostMySQLDAO::getAllCheckinsInLastWeekAsGoogleMap
            $checkins_map = $post_dao->getAllCheckinsInLastWeekAsGoogleMap($this->instance->network_user_id,
            $this->instance->network);
            //delete existing
            $insight_dao->deleteInsightsBySlug("PostMySQLDAO::getAllCheckinsInLastWeekAsGoogleMap",
            $this->instance->id);
            //insert new
            $insight_dao->insertInsightDeprecated("PostMySQLDAO::getAllCheckinsInLastWeekAsGoogleMap",
            $this->instance->id, $simplified_date, '', '', 'dashboard', Insight::EMPHASIS_LOW,
            serialize($checkins_map));
        }
    }

    /**
     * Convert Hot Posts data to JSON for use with Google Charts
     * @param arr $hot_posts Array returned from PostDAO::getHotPosts
     * @return str JSON
     */
    public static function getHotPostVisualizationData($hot_posts, $network) {
        switch ($network) {
            case 'twitter':
                $post_label = 'Tweet';
                $approval_label = 'Favorites';
                $share_label = 'Retweets';
                $reply_label = 'Replies';
                break;
            case 'facebook':
            case 'facebook page':
                $post_label = 'Post';
                $approval_label = 'Likes';
                $share_label = 'Shares';
                $reply_label = 'Comments';
                break;
            case 'google+':
                $post_label = 'Post';
                $approval_label = "+1s";
                $share_label = 'Shares';
                $reply_label = 'Comments';
                break;
            default:
                $post_label = 'Post';
                $approval_label = 'Favorites';
                $share_label = 'Shares';
                $reply_label = 'Comments';
                break;
        }
        $metadata = array(
        array('type' => 'string', 'label' => $post_label),
        array('type' => 'number', 'label' => $reply_label),
        array('type' => 'number', 'label' => $share_label),
        array('type' => 'number', 'label' => $approval_label),
        );
        $result_set = array();
        foreach ($hot_posts as $post) {
            if (isset($post->post_text) && $post->post_text != '') {
                $post_text_label = htmlspecialchars_decode(strip_tags($post->post_text), ENT_QUOTES);
            } elseif (isset($post->link->title) && $post->link->title != '') {
                $post_text_label = str_replace('|','', $post->link->title);
            } elseif (isset($post->link->url) && $post->link->url != "") {
                $post_text_label = str_replace('|','', $post->link->url);
            } else {
                $post_text_label = date("M j",  date_format (date_create($post->pub_date), 'U' ));
            }

            // Concat text and clean up any encoding snags
            $text_shortened = substr($post_text_label, 0, 100) . '...';
            // Doesn't work as expected on PHP 5.4
            //$text_clean = iconv("UTF-8", "ISO-8859-1//IGNORE", $text_shortened);
            $text_clean= mb_convert_encoding($text_shortened, 'UTF-8', 'UTF-8');

            $result_set[] = array('c' => array(
            array('v' => $text_clean),
            array('v' => intval($post->reply_count_cache)),
            array('v' => intval($post->all_retweets)),
            array('v' => intval($post->favlike_count_cache)),
            ));
        }
        return json_encode(array('rows' => $result_set, 'cols' => $metadata));
    }

    /**
     * Convert click stats data to JSON for Google Charts
     * @param arr $click_stats Array returned from ShortLinkDAO::getRecentClickStats
     * @return str JSON
     */
    public static function getClickStatsVisualizationData($click_stats) {
        $metadata = array(
        array('type' => 'string', 'label' => 'Link'),
        array('type' => 'number', 'label' => 'Clicks'),
        );
        $result_set = array();
        foreach ($click_stats as $link_stat) {
            $post_text_label = htmlspecialchars_decode(strip_tags($link_stat['post_text']), ENT_QUOTES);
            $result_set[] = array('c' => array(
            array('v' => substr($post_text_label, 0, 100) . '...'),
            array('v' => intval($link_stat['click_count'])),
            ));
        }
        return json_encode(array('rows' => $result_set, 'cols' => $metadata));
    }

    /**
     * Convert client usage data to JSON for Google Charts
     * @param arr $client_usage Array returned from PostDAO::getClientsUsedByUserOnNetwork
     * @return str JSON
     */
    public static function getClientUsageVisualizationData($client_usage) {
        $metadata = array(
        array('type' => 'string', 'label' => 'Client'),
        array('type' => 'number', 'label' => 'Posts'),
        );
        $result_set = array();
        foreach ($client_usage as $client => $posts) {
            $result_set[] = array('c' => array(
            array('v' => $client, 'f' => $client),
            array('v' => intval($posts)),
            ));
        }
        return json_encode(array('rows' => $result_set, 'cols' => $metadata));
    }
}