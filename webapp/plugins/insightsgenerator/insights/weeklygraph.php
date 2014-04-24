<?php
/*
 Plugin Name: Weekly Graph
 Description: Summarize and display a simple chart of responses to last week's posts.
 When: Wednesdays for Twitter, Saturday otherwise
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/weeklygraph.php
 *
 * Copyright (c) 2013-2014 Nilaksh Das, Gina Trapani
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
 * @copyright 2013-2014 Nilaksh Das, Gina Trapani
 * @author Nilaksh Das <nilakshdas [at] gmail [dot] com>
 */

class WeeklyGraphInsight extends InsightPluginParent implements InsightPlugin {
    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        if ($instance->network == 'twitter') {
            $day_of_week = 3;
        } else {
            $day_of_week = 6;
        }
        $should_generate_insight = self::shouldGenerateWeeklyInsight('weekly_graph', $instance, $insight_date='today',
            $regenerate_existing_insight=false, $day_of_week=$day_of_week, count($last_week_of_posts));

        if ($should_generate_insight) {
            $most_popular_post = null;
            $best_popularity_params = array('index' => 0, 'reply' => 0, 'retweet' => 0, 'like' => 0);
            $total_replies = 0;
            $total_retweets = 0;
            $total_likes = 0;

            $engaged_posts = array();
            foreach ($last_week_of_posts as $post) {
                $reply_count = $post->reply_count_cache;
                $retweet_count = $post->retweet_count_cache;
                $fav_count = $post->favlike_count_cache;

                $total_replies += $reply_count;
                $total_retweets += $retweet_count;
                $total_likes += $fav_count;

                $popularity_index = (5 * $reply_count) + (3 * $retweet_count) + (2 * $fav_count);

                if ($popularity_index > $best_popularity_params['index']) {
                    $best_popularity_params['index'] = $popularity_index;
                    $best_popularity_params['reply'] = $reply_count;
                    $best_popularity_params['retweet'] = $retweet_count;
                    $best_popularity_params['like'] = $fav_count;

                    $most_popular_post = $post;
                }

                if ($popularity_index > 0) {
                    $post->popularity_index = $popularity_index;
                    $engaged_posts[] = $post;
                }
            }

            if (isset($most_popular_post)) {
                usort($engaged_posts, array($this, 'compareEngagedPosts'));
                $posts = array_slice($engaged_posts, 0, 10);

                if ($total_replies >= $total_likes && $total_replies >= $total_retweets) {
                    $insight_text = $this->username." really inspired conversations in the past week";
                    $lower = array();
                    if ($total_replies > $total_likes) {
                        $lower[] = $this->terms->getNoun('like', InsightTerms::PLURAL);
                    }
                    if ($total_replies > $total_retweets) {
                        $lower[] = $this->terms->getNoun('retweet', InsightTerms::PLURAL);
                    }
                    if (count($lower) == 0) {
                        $insight_text .= ', getting more '.
                            $this->terms->getNoun('reply', InsightTerms::PLURAL)." than anything else.";
                    } else {
                        $insight_text .= ' &mdash; '. $this->terms->getNoun('reply', InsightTerms::PLURAL).
                            ' outnumbered '.join(' or ', $lower).'.';
                    }
                } else if ($total_likes >= $total_replies && $total_likes >= $total_retweets) {
                    $insight_text = "Whatever ".$this->username." said in the past week must have been memorable";
                    $insight_text .= ' &mdash; there were '.number_format($total_likes).' '.
                        $this->terms->getNoun('like', InsightTerms::PLURAL);
                    $lower = array();
                    if ($total_likes > $total_replies && $total_replies > 0) {
                        $plural = $total_replies==1?InsightTerms::SINGULAR : InsightTerms::PLURAL;
                        $lower[] = number_format($total_replies).' '.  $this->terms->getNoun('reply', $plural);
                    }
                    if ($total_likes > $total_retweets && $total_retweets > 0) {
                        $plural = $total_retweets==1?InsightTerms::SINGULAR : InsightTerms::PLURAL;
                        $lower[] = number_format($total_retweets).' '. $this->terms->getNoun('retweet', $plural);
                    }
                    if (count($lower) == 0) {
                        $insight_text .= '.';
                    } else {
                        $insight_text .= ', beating out '.join(' and ', $lower).'.';
                    }
                } else {
                    $insight_text = $this->username.
                        " shared a lot of things people wanted to amplify in the past week.";
                    $lower = array();
                    if ($total_retweets > $total_replies) {
                        $lower[] = $this->terms->getNoun('reply', InsightTerms::PLURAL) . ' by '
                            .number_format($total_retweets - $total_replies);
                    }
                    if ($total_retweets > $total_likes) {
                        $lower[] = $this->terms->getNoun('like', InsightTerms::PLURAL) . ' by '
                            .number_format($total_retweets - $total_likes);
                    }
                    if (count($lower) > 0) {
                        $insight_text .= ' '.ucfirst($this->terms->getNoun('retweet', InsightTerms::PLURAL))
                            .' outnumbered '.join(' and ', $lower). '.';
                    }
                }

                $headline = $this->getVariableCopy(array(
                    "What's going on with %username's %posts.",
                    "What's up with %username's %posts.",
                    "What's happening with %username's %posts?",
                    "Here's the deal with %username's %posts.",
                    "Last week in %username's %posts&hellip;"
                ));

                $my_insight = new Insight();
                $my_insight->slug = 'weekly_graph';
                $my_insight->instance_id = $instance->id;
                $my_insight->date = $this->insight_date;
                $my_insight->headline = $headline;
                $my_insight->text = $insight_text;
                $my_insight->header_image = $header_image;
                $my_insight->filename = basename(__FILE__, ".php");
                $my_insight->emphasis = Insight::EMPHASIS_LOW;
                if (count($posts) > 3) {
                    $formatted_posts =array(ChartHelper::getPostActivityVisualizationData($posts, $instance->network));
                    $my_insight->setPosts($formatted_posts);
                }
                $this->insight_dao->insertInsight($my_insight);
            }
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

    /**
     * Compare two posts by popularity - for sorting callback
     *
     * @param Post $a First post
     * @param Post $b Second post
     * @return int Sort value
     */
    private function compareEngagedPosts($a,$b) {
        return $b->popularity_index - $a->popularity_index;
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('WeeklyGraphInsight');
