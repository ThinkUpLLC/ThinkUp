<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.InsightsGenerator.php
 *
 * Copyright (c) 2012 Gina Trapani
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
 * Insights Generator
 *
 * Generate and store insights for faster dashboard and view rendering.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class InsightsGenerator {
    /**
     *
     * @var Instance
     */
    var $instance;
    public function __construct(Instance $instance) {
        $this->instance = $instance;
    }
    /**
     * Generate insights and store in the insights storage.
     */
    public function generateInsights() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $simplified_date = date('Y-m-d');

        //Cache FollowMySQLDAO::getLeastLikelyFollowersThisWeek
        $follow_dao = DAOFactory::getDAO('FollowDAO');
        $results = $follow_dao->getLeastLikelyFollowersThisWeek($this->instance->network_user_id,
        $this->instance->network, 13, 1);
        if (isset($results)) {
            $insight_dao->insertInsight("FollowMySQLDAO::getLeastLikelyFollowersThisWeek", $this->instance->id,
            $simplified_date, '', Insight::EMPHASIS_LOW, serialize($results));
        }

        //Cache PostMySQLDAO::getHotPosts
        $post_dao = DAOFactory::getDAO('PostDAO');
        $hot_posts = $post_dao->getHotPosts($this->instance->network_user_id, $this->instance->network, 10);
        if (sizeof($hot_posts) > 3) {
            $hot_posts_data = self::getHotPostVisualizationData($hot_posts, $this->instance->network);
            $insight_dao->insertInsight("PostMySQLDAO::getHotPosts", $this->instance->id,
            $simplified_date, '', Insight::EMPHASIS_LOW, serialize($hot_posts_data));
        }

        //Cache ShortLinkMySQLDAO::getRecentClickStats
        $short_link_dao = DAOFactory::getDAO('ShortLinkDAO');
        $click_stats = $short_link_dao->getRecentClickStats($this->instance, 10);
        if (sizeof($click_stats) > 3) {
            $click_stats_data = self::getClickStatsVisualizationData($click_stats);
            $insight_dao->insertInsight("ShortLinkMySQLDAO::getRecentClickStats", $this->instance->id,
            $simplified_date, '', Insight::EMPHASIS_LOW, serialize($click_stats_data));
        }

        //Cache PostMySQLDAO::getAllPostsByUsernameOrderedBy // getMostRepliedToPostsInLastWeek
        $most_replied_to_1wk = $post_dao->getMostRepliedToPostsInLastWeek($this->instance->network_username,
        $this->instance->network, 5);
        if (sizeof($most_replied_to_1wk) > 1) {
            $insight_dao->insertInsight("PostMySQLDAO::getMostRepliedToPostsInLastWeek", $this->instance->id,
            $simplified_date, '', Insight::EMPHASIS_LOW, serialize($most_replied_to_1wk));
        }

        //Cache PostMySQLDAO::getAllPostsByUsernameOrderedBy // getMostRetweetedPostsInLastWeek
        $most_retweeted_1wk = $post_dao->getMostRetweetedPostsInLastWeek($this->instance->network_username,
        $this->instance->network, 5);
        if (sizeof($most_retweeted_1wk) > 1) {
            $insight_dao->insertInsight("PostMySQLDAO::getMostRetweetedPostsInLastWeek", $this->instance->id,
            $simplified_date, '', Insight::EMPHASIS_LOW, serialize($most_retweeted_1wk));
        }

        //Cache PostMySQLDAO::getClientsUsedByUserOnNetwork
        $clients_usage = $post_dao->getClientsUsedByUserOnNetwork($this->instance->network_user_id,
        $this->instance->network);
        $insight_dao->insertInsight("PostMySQLDAO::getClientsUsedByUserOnNetwork", $this->instance->id,
        $simplified_date, '', Insight::EMPHASIS_LOW, serialize($clients_usage));
    }

    /**
     * Convert Hot Posts data to JSON for use with Google Charts
     * @param array $hot_posts Array returned from PostDAO::getHotPosts
     * @return string JSON
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

            $result_set[] = array('c' => array(
            array('v' => substr($post_text_label, 0, 100) . '...'),
            array('v' => intval($post->reply_count_cache)),
            array('v' => intval($post->all_retweets)),
            array('v' => intval($post->favlike_count_cache)),
            ));
        }
        return json_encode(array('rows' => $result_set, 'cols' => $metadata));
    }

    /**
     * Convert click stats data to JSON for Google Charts
     * @param array $click_stats Array returned from ShortLinkDAO::getRecentClickStats
     * @return string JSON
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
     * @param array $client_usage Array returned from PostDAO::getClientsUsedByUserOnNetwork
     * @return string JSON
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