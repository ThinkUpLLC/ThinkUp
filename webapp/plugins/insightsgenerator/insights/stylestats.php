<?php
/*
 Plugin Name: Style Stats
 Description: Stats on different types of posts in the past week.
 When: Saturdays
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/StyleStat.php
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani [at] gmail [dot] com>
 */

class StyleStatsInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        if (self::shouldGenerateInsight('style_stats', $instance, $insight_date='today',
        $regenerate_existing_insight=false, $day_of_week=6, count($last_week_of_posts),
        $excluded_networks=array('foursquare'))) {
            $total_posts = array("questions" => 0, "quotations" => 0, "links" => 0, "photos" => 0);
            $total_replies = array("all" => 0, "questions" => 0, "quotations" => 0, "links" => 0, "photos" => 0);
            $average_replies = array("all" => 0, "questions" => 0, "quotations" => 0, "links" => 0, "photos" => 0);
            $total_reshares = array("all" => 0, "questions" => 0, "quotations" => 0, "links" => 0, "photos" => 0);
            $average_reshares = array("all" => 0, "questions" => 0, "quotations" => 0, "links" => 0, "photos" => 0);
            if (sizeof( $last_week_of_posts) > 5) {
                $this->logger->logSuccess("Calculating style stats ", __METHOD__.','.__LINE__);
                foreach ($last_week_of_posts as $post) {
                    $total_replies["all"] += $post->reply_count_cache;
                    $total_reshares["all"] += $post->all_retweets;
                    if ((strpos($post->post_text, '? ') !== false) || self::endsWith($post->post_text, '?') ) {
                        $total_posts["questions"]++;
                        $total_replies["questions"] += $post->reply_count_cache;
                        $total_reshares["questions"] += $post->all_retweets;
                    }
                    if (strpos($post->post_text, '"') !== false || self::startsWith($post->post_text, 'OH') ) {
                        $total_posts["quotations"]++;
                        $total_replies["quotations"] += $post->reply_count_cache;
                        $total_reshares["quotations"] += $post->all_retweets;
                    }
                    if (sizeof($post->links) > 0 ) {
                        foreach ($post->links as $link) {
                            if ($link->image_src != null) {
                                $total_posts["photos"]++;
                                $total_replies["photos"] += $post->reply_count_cache;
                                $total_reshares["photos"] += $post->all_retweets;
                            } else {
                                $total_posts["links"]++;
                                $total_replies["links"] += $post->reply_count_cache;
                                $total_reshares["links"] += $post->all_retweets;

                            }
                        }
                    }
                }
                if (sizeof($last_week_of_posts) > 0) {
                    $average_replies["all"] = round($total_replies["all"] / (sizeof($last_week_of_posts)) );
                    $average_reshares["all"] = round($total_reshares["all"] / (sizeof($last_week_of_posts)) );
                } else {
                    $average_replies["all"] = 0;
                    $average_reshares["all"] = 0;
                }

                if ($total_posts["questions"] > 0 ) {
                    $average_replies["questions"] = round($total_replies["questions"] / $total_posts["questions"]);
                    $average_reshares["questions"] = round($total_reshares["questions"] / $total_posts["questions"]);
                } else {
                    $average_replies["questions"] = 0;
                    $average_reshares["questions"] = 0;
                }

                if ($total_posts["quotations"] > 0) {
                    $average_replies["quotations"] = round($total_replies["quotations"] / $total_posts["quotations"]);
                    $average_reshares["quotations"] = round($total_reshares["quotations"] / $total_posts["quotations"]);
                } else {
                    $average_replies["quotations"] = 0;
                    $average_reshares["quotations"] = 0;
                }

                if ($total_posts["links"] > 0) {
                    $average_replies["links"] = round($total_replies["links"] / $total_posts["links"]);
                    $average_reshares["links"] = round($total_reshares["links"] / $total_posts["links"]);
                } else {
                    $average_replies["links"] = 0;
                    $average_reshares["links"] = 0;
                }

                if ($total_posts["photos"] > 0) {
                    $average_replies["photos"] = round($total_replies["photos"] / $total_posts["photos"]);
                    $average_reshares["photos"] = round($total_reshares["photos"] / $total_posts["photos"]);
                } else {
                    $average_replies["photos"] = 0;
                    $average_reshares["photos"] = 0;
                }

                $insight_text = '';
                arsort($total_posts);
                $keys = array_keys($total_posts);
                $last_type = end($keys);
                foreach ($total_posts as $type => $total) {
                    if ($type == $last_type) { //last item in list
                        $insight_text .= "and ";
                    }
                    if ($insight_text == '') { //first item
                        $insight_text .= (($total == 0)?"None":$total)." of $this->username's posts this week ".
                        (($total == 1)?"was a":"were")." ".(($total == 1)?substr($type, 0, -1):$type);
                    } else {
                        $insight_text .= (($total == 0)?"none":$total)." ".(($total == 1)?"was a":"were")." ".
                        (($total == 1)?substr($type, 0, -1):$type);
                    }
                    if ($type == $last_type) {  //last item in list
                        $insight_text .= ".";
                    } else {
                        $insight_text .= ", ";
                    }
                }

                arsort($average_replies);
                $terminology = ($post->network == "twitter")?"retweets":"reshares";
                foreach ($average_replies as $type => $average) {
                    $sentence = "";
                    $has_replies_multiplier = false;
                    if ($average > $average_replies["all"] && $average_replies["all"] > 0) {
                        $multiplier_replies = floor($average/$average_replies["all"]);
                        if ($multiplier_replies > 1) {
                            $sentence .= " <strong>".ucfirst($type)."</strong> got <strong>".$multiplier_replies.
                            "x</strong> more replies ";
                            $has_replies_multiplier = true;
                        }
                        $multiplier_reshares = 0;
                        if ($average_reshares[$type] > $average_reshares["all"]) {
                            $multiplier_reshares = floor($average_reshares[$type]/$average_reshares["all"]);
                            if ($multiplier_reshares > 1) {
                                if ($has_replies_multiplier) {
                                    $sentence .= "and <strong>".$multiplier_reshares. "x</strong> more $terminology ";
                                } else {
                                    $sentence .= " <strong>".ucfirst($type)."</strong> got <strong>".
                                    $multiplier_reshares. "x</strong> more $terminology ";
                                }
                            }
                        }
                        if ($multiplier_replies > 1 || $multiplier_reshares > 1) {
                            $sentence .= "than average.";
                        }
                    } else {
                        if ($average_reshares[$type] > $average_reshares["all"] && $average_reshares["all"] > 0) {
                            $multiplier = floor($average_reshares[$type]/$average_reshares["all"]);
                            if ($multiplier > 1) {
                                $sentence .= " <strong>".ucfirst($type)."</strong> got <strong>".$multiplier.
                                "x</strong> more $terminology than average.";
                            }
                        }
                    }
                    $insight_text .= $sentence;
                }
                //TODO: Stop using the cached dashboard data and generate fresh here
                $hot_posts_data = $this->insight_dao->getPreCachedInsightData('PostMySQLDAO::getHotPosts',
                $instance->id, date('Y-m-d'));

                if (isset($hot_posts_data)) {
                    $this->insight_dao->insertInsight('style_stats', $instance->id, date('Y-m-d'),
                    "Post style:", $insight_text, basename(__FILE__, ".php"), Insight::EMPHASIS_LOW,
                    serialize($hot_posts_data));
                }
            } else {
                $this->logger->logSuccess("Only ".sizeof( $last_week_of_posts).
                " posts last week, not enough to calculate style stats ", __METHOD__.','.__LINE__);
            }
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
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
