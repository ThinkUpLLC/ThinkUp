<?php
/*
 Plugin Name: Style Stats
 Description: Stats on different types of posts in the past week.
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/StyleStat.php
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012 Gina Trapani
 * @author Gina Trapani <ginatrapani [at] gmail [dot] com>
 */

class StyleStatsInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);

        /**
         * Style stats: This week, X% of your posts were questions, Y% were quotations, Z% had links and Q% had photos.
         *  Links/Quotations got the most retweets and photos/questions got the most replies.
         */
        $total_posts_by_type = array("questions" => 0, "quotations" => 0, "links" => 0, "photos" => 0);
        $total_responses_by_type = array("questions" => 0, "quotations" => 0, "links" => 0, "photos" => 0);
        $total_average_responses_by_type = array("questions" => 0, "quotations" => 0, "links" => 0, "photos" => 0);
        $total_responses = 0;
        if ( sizeof( $last_week_of_posts) > 5  && $instance->network != 'foursquare') {
            $this->logger->logSuccess("Calculating style stats ", __METHOD__.','.__LINE__);
            foreach ($last_week_of_posts as $post) {
                $total_responses += $post->all_retweets + $post->reply_count_cache;
                if ((strpos($post->post_text, '? ') !== false) || self::endsWith($post->post_text, '?') ) {
                    $total_posts_by_type["questions"]++;
                    $total_responses_by_type["questions"] += $post->all_retweets + $post->reply_count_cache;
                }
                if (strpos($post->post_text, '"') !== false || self::startsWith($post->post_text, 'OH') ) {
                    $total_posts_by_type["quotations"]++;
                    $total_responses_by_type["quotations"] += $post->all_retweets + $post->reply_count_cache;
                }
                if (sizeof($post->links) > 0 ) {
                    $total_posts_by_type["links"]++;
                    $total_responses_by_type["links"] += $post->all_retweets + $post->reply_count_cache;
                    foreach ($post->links as $link) {
                        if ($link->image_src != null) {
                            $total_posts_by_type["photos"]++;
                            $total_responses_by_type["photos"] += $post->all_retweets + $post->reply_count_cache;
                        }
                    }
                }
            }
            $overall_average_responses = round($total_responses / (sizeof($last_week_of_posts)) );

            $total_average_responses_by_type["questions"] =
            round($total_responses_by_type["questions"] / $total_posts_by_type["questions"]);
            $total_average_responses_by_type["quotations"] =
            round($total_responses_by_type["quotations"] / $total_posts_by_type["quotations"]);
            $total_average_responses_by_type["links"] =
            round($total_responses_by_type["links"] / $total_posts_by_type["links"]);
            $total_average_responses_by_type["photos"] =
            round($total_responses_by_type["photos"] / $total_posts_by_type["photos"]);

            $insight_text = '';
            arsort($total_posts_by_type);
            $keys = array_keys($total_posts_by_type);
            $last_type = end($keys);
            foreach ($total_posts_by_type as $type => $total) {
                if ($type == $last_type) { //last item in list
                    $insight_text .= "and ";
                }
                if ($insight_text == '') { //first item
                    $insight_text .= (($total == 0)?"None":$total)." of your posts this week were $type";
                } else {
                    $insight_text .= (($total == 0)?"none":$total)." were $type";
                }
                if ($type == $last_type) {  //last item in list
                    $insight_text .= ".";
                } else {
                    $insight_text .= ", ";
                }
            }

            arsort($total_average_responses_by_type);
            foreach ($total_average_responses_by_type as $type => $average) {
                if ($average > $overall_average_responses) {
                    $percent = round(($average * 100)/$overall_average_responses);
                    $insight_text .= " <strong>".ucfirst($type)."</strong> got <strong>".$percent.
                    "%</strong> more responses than average.";
                }
            }

            $this->insight_dao->insertInsight('style_stats', $instance->id, date('Y-m-d'),
            "Style stats:", $insight_text, basename(__FILE__, ".php"), Insight::EMPHASIS_LOW);
        } else {
            $this->logger->logSuccess("Only ".sizeof( $last_week_of_posts).
            " posts last week, not enough to calculate style stats ", __METHOD__.','.__LINE__);
        }
    }

    private function endsWith($str, $end_str) {
        $full_str_end = substr($str, strlen($str) - (strlen($end_str)));
        return $full_str_end == $end_str;
    }

    private function startsWith($str, $start_str) {
        $full_str_start = substr($str, 0, strlen($str) - (strlen($start_str)));
        return $full_str_start == $start_str;
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('StyleStatsInsight');
