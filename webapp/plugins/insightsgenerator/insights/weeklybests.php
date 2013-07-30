<?php
/*
 Plugin Name: Weekly bests
 Description: Your most popular posts from last week. (Monday)
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/weeklybests.php
 *
 * Copyright (c) 2013 Nilaksh Das, Gina Trapani
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
 * @copyright 2013 Nilaksh Das, Gina Trapani
 * @author Nilaksh Das <nilakshdas [at] gmail [dot] com>
 */

class WeeklyBestsInsight extends InsightPluginParent implements InsightPlugin {
    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $in_test_mode =  ((isset($_SESSION["MODE"]) && $_SESSION["MODE"] == "TESTS") || getenv("MODE")=="TESTS");
        //Only insert this insight if it's Monday or if we're testing
        if ((date('w') == 1 || $in_test_mode) && count($last_week_of_posts)) {
            $most_popular_post = null;
            $best_popularity_params = array('index' => 0, 'replies' => 0, 'retweets' => 0, 'favs' => 0);

            foreach ($last_week_of_posts as $post) {
                $reply_count = $post->reply_count_cache;
                $retweet_count = $post->retweet_count_cache;
                $fav_count = $post->favlike_count_cache;

                $popularity_index = (5 * $reply_count) + (3 * $retweet_count) + (2 * $fav_count);

                if ($popularity_index > $best_popularity_params['index']) {
                    $best_popularity_params['index'] = $popularity_index;
                    $best_popularity_params['replies'] = $reply_count;
                    $best_popularity_params['retweets'] = $retweet_count;
                    $best_popularity_params['favs'] = $fav_count;

                    $most_popular_post = $post;
                }
            }

            if (isset($most_popular_post)) {
                $insight_text = $this->username."'s most popular post from last week got ";
                foreach ($best_popularity_params as $key => $value) {
                    if ($value) {
                        switch ($key) {
                            case 'replies':
                                if ($instance->network == 'twitter') {
                                    $insight_text .= $value." ".($value > 1 ? "replies, " : "reply, ");
                                } else {
                                    $insight_text .= $value." comment".($value > 1 ? "s, " : ", ");
                                }
                                break;
                            
                            case 'retweets':
                                $insight_text .= $value." retweet".($value > 1 ? "s, " : ", ");
                                break;

                            case 'favs':
                                if ($instance->network == 'twitter') {
                                    $insight_text .= $value." favourite".($value > 1 ? "s, " : ", ");
                                } elseif ($instance->network == 'googleplus') {
                                    $insight_text .= $value." +1".($value > 1 ? "s, " : ", ");
                                } else {
                                    $insight_text .= $value." like".($value > 1 ? "s, " : ", ");
                                }
                                break;
                        }
                    }
                }

                $insight_text = rtrim($insight_text, ", ");
                $insight_text .= '.';
                if (!(strpos($insight_text, ',') === false)) {
                    $insight_text = substr_replace($insight_text, " and",
                    strpos($insight_text, strrchr($insight_text, ',')), 1);
                }

                $this->insight_dao->insertInsight("weekly_best", $instance->id, $this->insight_date, "Weekly best:",
                $insight_text, basename(__FILE__, ".php"), Insight::EMPHASIS_LOW, serialize($most_popular_post));
            }
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('WeeklyBestsInsight');
