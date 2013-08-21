<?php
/*
 Plugin Name: Interaction Graph
 Description: People and topics you posted about in the last week.
 When: Wednesdays
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/interactiongraph.php
 *
 * Copyright (c) 2012-2013 Nilaksh Das, Gina Trapani
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Nilaksh Das, Gina Trapani
 * @author Nilaksh Das <nilakshdas [at] gmail [dot] com>
 */

require_once dirname(__FILE__).'/../../twitter/extlib/twitter-text-php/lib/Twitter/Extractor.php';

class InteractionGraphInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $in_test_mode =  ((isset($_SESSION["MODE"]) && $_SESSION["MODE"] == "TESTS") || getenv("MODE")=="TESTS");
        //Only insert this insight if it's Wednesday or if we're testing
        if (date('w') == 3 || $in_test_mode ) {
            $user_dao = DAOFactory::getDAO('UserDAO');

            $hashtags_of_last_week = array();
            $mentions_of_last_week = array();
            $link_hashtags_mentions = array();
            $insight_data = array('user' => $user_dao->getDetails($instance->network_user_id,$instance->network),
            'hashtags' => array(), 'mentions' => array());
            $insight_text = '';
            foreach ($last_week_of_posts as $post) {
                $post_text = $post->post_text;

                // Extract hashtags and mentions from post text
                $text_parser = new Twitter_Extractor($post_text);
                $elements = $text_parser->extract();

                $hashtags_in_post = $elements['hashtags'];
                foreach ($hashtags_in_post as $hashtag_in_post) {
                    $hashtag_in_post = '#'.$hashtag_in_post;

                    // Update hashtag count
                    if (array_key_exists($hashtag_in_post, $hashtags_of_last_week)) {
                        $hashtags_of_last_week[$hashtag_in_post]++;
                    } else {
                        $hashtags_of_last_week[$hashtag_in_post] = 1;
                    }
                }

                if ($instance->network == 'twitter') {
                    $mentions_in_post = $elements['mentions'];
                    $mentioned_users = array();
                    foreach ($mentions_in_post as $mention_in_post) {
                        if ($mention_in_post == $instance->network_username) {
                            // Don't count metweets
                            continue;
                        } else {
                            $mentioned_users[$mention_in_post] = $user_dao->getUserByName($mention_in_post,
                            $instance->network);
                            if (isset($mentioned_users[$mention_in_post])) {
                                $mention_in_post = '@'.$mentioned_users[$mention_in_post]->username;
                            } else {
                                $mention_in_post = '@'.$mention_in_post;
                            }

                            // Update mention count
                            if (array_key_exists($mention_in_post, $mentions_of_last_week)) {
                                $mentions_of_last_week[$mention_in_post]++;
                            } else {
                                $mentions_of_last_week[$mention_in_post] = 1;
                            }

                            // Link mention with hashtags
                            foreach ($hashtags_in_post as $hashtag_in_post) {
                                $hashtag_in_post = '#'.$hashtag_in_post;
                                $link_hashtags_mentions[$hashtag_in_post][] = $mention_in_post;
                            }
                        }
                    }
                }
            }

            $most_used_hashtag = false;
            $most_mentioned_user = false;
            if (count($hashtags_of_last_week)) {
                // Get most talked about hashtag
                arsort($hashtags_of_last_week);
                $most_used_hashtag = each($hashtags_of_last_week);

                // Add hashtags to dataset
                foreach ($hashtags_of_last_week as $hashtag => $count) {
                    $hashtag_info['hashtag'] = $hashtag;
                    $hashtag_info['count'] = $count;
                    $hashtag_info['url'] = self::getHashtagSearchURL($hashtag,$instance->network);
                    $hashtag_info['related_mentions'] = count($link_hashtags_mentions[$hashtag])
                    ? $link_hashtags_mentions[$hashtag] : array();
                    $insight_data['hashtags'][] = $hashtag_info;
                }
            }
            if (count($mentions_of_last_week)) {
                // Get most mentioned user
                arsort($mentions_of_last_week);
                $most_mentioned_user = each($mentions_of_last_week);

                // Add mentions to dataset
                foreach ($mentions_of_last_week as $mention => $count) {
                    $mention_info['mention'] = $mention;
                    $mention_info['count'] = $count;
                    $mention_info['user'] = $mentioned_users[ltrim($mention,'@')];
                    $insight_data['mentions'][] = $mention_info;
                }
            }

            $insight_text .= $most_used_hashtag ?
            "talked about <a href=".self::getHashtagSearchURL($most_used_hashtag['key'],$instance->network)
            ." target=\"_blank\">".$most_used_hashtag['key']."</a> <strong>".$most_used_hashtag['value']
            .(($most_used_hashtag['value'] > 1) ? " times</strong> " : " time</strong> "):'';
            $insight_text .= ($insight_text && $most_mentioned_user) ? "and ":'';
            $insight_text .= $most_mentioned_user?
            "mentioned ".$most_mentioned_user['key']." <strong>".$most_mentioned_user['value']
            .(($most_mentioned_user['value'] > 1) ? " times</strong> " : " time</strong> "):'';

            if ($insight_text) {
                $insight_text = $this->username." ".$insight_text."last week.";
                $this->insight_dao->insertInsight("interaction_graph",
                $instance->id, $this->insight_date, "Interactions:", $insight_text,
                basename(__FILE__, ".php"), Insight::EMPHASIS_LOW, serialize($insight_data));
            }
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

    /**
     * Get the service URL of the hashtag search page on the respective network.
     * @param str $network
     * @param str $hashtag
     * @return str Hashtag search url
     */
    public static function getHashtagSearchURL($hashtag, $network) {
        switch ($network) {
            case 'twitter':
                return "https://twitter.com/search?q=%23".ltrim($hashtag,'#')."&src=hash";

            case 'facebook':
                return "https://www.facebook.com/hashtag/".ltrim($hashtag,'#');

            case 'google+':
                return "https://plus.google.com/u/0/s/%23".ltrim($hashtag,'#');

            default:
                return NULL;
        }
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('InteractionGraphInsight');
