<?php

/*
 Plugin Name: Reply Spike
 Description: Reply spikes and high insights for the past 7, 30, and 365 days.
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/replyspike.php
 *
 * Copyright (c) 2012-2013 Aaron Kalair
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
 * @copyright 2012-2013 Aaron Kalair
 * @author Aaron Kalair <aaronkalair [at] gmail [dot] com>
 */

class ReplySpikeInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        self::generateInsightBaselines($instance, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $filename = basename(__FILE__, ".php");
        $insight_text = '';
        $insight_slug = '';

        $simplified_post_date = "";

        foreach ($last_week_of_posts as $post) {
            if ($post->reply_count_cache > 2) { // Only show insight for more than 2 replies
                // First get spike/high 7/30/365 day baselines
                if ($simplified_post_date != date('Y-m-d', strtotime($post->pub_date))) {
                    $simplified_post_date = date('Y-m-d', strtotime($post->pub_date));

                    $average_reply_count_7_days =
                    $insight_baseline_dao->getInsightBaseline('avg_reply_count_last_7_days', $instance->id,
                    $simplified_post_date);

                    $average_reply_count_30_days =
                    $insight_baseline_dao->getInsightBaseline('avg_reply_count_last_30_days', $instance->id,
                    $simplified_post_date);

                    $high_reply_count_7_days =
                    $insight_baseline_dao->getInsightBaseline('high_reply_count_last_7_days', $instance->id,
                    $simplified_post_date);

                    $high_reply_count_30_days =
                    $insight_baseline_dao->getInsightBaseline('high_reply_count_last_30_days', $instance->id,
                    $simplified_post_date);

                    $high_reply_count_365_days =
                    $insight_baseline_dao->getInsightBaseline('high_reply_count_last_365_days', $instance->id,
                    $simplified_post_date);
                }
                // Next compare post reply counts to baselines and store insights where there's a spike or high
                if (isset($high_reply_count_365_days->value)
                && $post->reply_count_cache >= $high_reply_count_365_days->value
                && isset($it_is_not_launch_day)) {

                    $hot_posts_data = $this->insight_dao->getPreCachedInsightData('PostMySQLDAO::getHotPosts',
                    $instance->id, $simplified_post_date);
                    if (isset($hot_posts_data)) {
                        $insight_slug = 'reply_high_365_day_'.$post->id;
                        $headline = "That ".$this->terms->getNoun('post'). " got <strong>" .
                            number_format($post->reply_count_cache) . " " .
                            $this->terms->getNoun('reply', InsightTerms::PLURAL) .
                            "</strong> &mdash; your 365-day high!";
                        $insight_text = "Why do you think $this->username's ".$this->terms->getNoun('post').
                            " did so well?";
                        $emphasis = Insight::EMPHASIS_HIGH;
                        $my_insight_posts = array($post, $hot_posts_data);

                        $this->insight_dao->deleteInsight('reply_high_30_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                        $this->insight_dao->deleteInsight('reply_high_7_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                        $this->insight_dao->deleteInsight('reply_spike_30_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                        $this->insight_dao->deleteInsight('reply_spike_7_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                    }
                } elseif (isset($high_reply_count_30_days->value)
                && $post->reply_count_cache >= $high_reply_count_30_days->value
                && isset($it_is_not_launch_day)) {

                    $hot_posts_data = $this->insight_dao->getPreCachedInsightData('PostMySQLDAO::getHotPosts',
                    $instance->id, $simplified_post_date);

                    if (isset($hot_posts_data)) {
                        $insight_slug = 'reply_high_30_day_'.$post->id;

                        $headline = "This ".$this->terms->getNoun('post'). " got " .
                            $this->terms->getNoun('reply', InsightTerms::PLURAL) .
                            " from <strong>" . number_format($post->reply_count_cache) .
                            " people</strong>.";
                        $insight_text = "That sets a new 30-day record for $this->username.";

                        $emphasis = Insight::EMPHASIS_HIGH;
                        $my_insight_posts = array($post, $hot_posts_data);

                        $this->insight_dao->deleteInsight('reply_high_7_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                        $this->insight_dao->deleteInsight('reply_spike_30_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                        $this->insight_dao->deleteInsight('reply_spike_7_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                    }
                } elseif (isset($high_reply_count_7_days->value)
                && $post->reply_count_cache >= $high_reply_count_7_days->value) {

                    $hot_posts_data = $this->insight_dao->getPreCachedInsightData('PostMySQLDAO::getHotPosts',
                    $instance->id, $simplified_post_date);

                    if (isset($hot_posts_data)) {
                        $insight_slug = 'reply_high_7_day_'.$post->id;
                        $headline = "<strong>".number_format($post->reply_count_cache).
                            " people</strong> replied to $this->username's ".$this->terms->getNoun('post').".";
                        $insight_text = "That's a new 7-day record.";
                        $emphasis = Insight::EMPHASIS_HIGH;
                        $my_insight_posts = array($post, $hot_posts_data);

                        $this->insight_dao->deleteInsight('reply_high_30_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                        $this->insight_dao->deleteInsight('reply_spike_30_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                        $this->insight_dao->deleteInsight('reply_spike_7_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                    }
                } elseif (isset($average_reply_count_30_days->value)
                && $post->reply_count_cache > ($average_reply_count_30_days->value*2)) {

                    $hot_posts_data = $this->insight_dao->getPreCachedInsightData('PostMySQLDAO::getHotPosts',
                    $instance->id, $simplified_post_date);

                    if (isset($hot_posts_data)) {
                        $multiplier = floor($post->reply_count_cache/$average_reply_count_30_days->value);
                        $insight_slug = 'reply_spike_30_day_'.$post->id;
                        $headline = "<strong>".number_format($post->reply_count_cache).
                            " people</strong> replied to " . $this->username . "'s ".$this->terms->getNoun('post'). '.';
                        $insight_text = "That's more than <strong>".$this->terms->getMultiplierAdverb($multiplier).
                            "</strong> " . $this->username . "'s 30-day average.";
                        $emphasis = Insight::EMPHASIS_LOW;
                        $my_insight_posts = array($post, $hot_posts_data);

                        $this->insight_dao->deleteInsight('reply_high_30_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                        $this->insight_dao->deleteInsight('reply_high_7_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                        $this->insight_dao->deleteInsight('reply_spike_7_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                    }
                } elseif (isset($average_reply_count_7_days->value)
                && $post->reply_count_cache > ($average_reply_count_7_days->value*2)) {

                    $hot_posts_data = $this->insight_dao->getPreCachedInsightData('PostMySQLDAO::getHotPosts',
                    $instance->id, $simplified_post_date);

                    if (isset($hot_posts_data)) {
                        $multiplier = floor($post->reply_count_cache/$average_reply_count_7_days->value);
                        $insight_slug = 'reply_spike_7_day_'.$post->id;
                        $headline = "<strong>".number_format($post->reply_count_cache).
                            " people</strong> replied to $this->username's ".$this->terms->getNoun('post'). '.';
                        $insight_text = "That's more than "."<strong>" .$this->terms->getMultiplierAdverb($multiplier).
                            "</strong> $this->username's 7-day average.";
                        $emphasis = Insight::EMPHASIS_LOW;
                        $my_insight_posts = array($post, $hot_posts_data);

                        $this->insight_dao->deleteInsight('reply_high_30_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                        $this->insight_dao->deleteInsight('reply_high_7_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                        $this->insight_dao->deleteInsight('reply_spike_30_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                    }
                }

                if (isset($headline) && isset($insight_slug)) {
                    $my_insight = new Insight();

                    //REQUIRED: Set the insight's required attributes
                    $my_insight->instance_id = $instance->id;
                    $my_insight->slug = $insight_slug; //slug to label this insight's content
                    $my_insight->date = $simplified_post_date; //date of the data this insight applies to
                    $my_insight->headline = $headline; // or just set a string like 'Ohai';
                    $my_insight->text = $insight_text; // or just set a strong like "Greetings humans";
                    $my_insight->header_image = '';
                    $my_insight->emphasis = $emphasis; //Set emphasis optionally, default is Insight::EMPHASIS_LOW
                    $my_insight->filename = basename(__FILE__, ".php"); //Same for every insight, must be set exactly this way
                    $my_insight->setPosts($my_insight_posts);

                    $this->insight_dao->insertInsight($my_insight);
                }
                //reset vars
                $headline = null;
                $insight_slug = null;
                $insight_text = null;
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

    /**
     * Calculate and store insight baselines for a specified number of days.
     * @param Instance $instance
     * @param int $number_days Number of days to backfill
     * @return void
     */
    private function generateInsightBaselines($instance, $number_days=3) {
        $post_dao = DAOFactory::getDAO('PostDAO');
        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');

        $days_ago = 0;
        // Generate baseline post insights for the last 7 days
        while ($days_ago < $number_days) {
            $since_date = date("Y-m-d", strtotime("-".$days_ago." day"));

            if ($post_dao->doesUserHavePostsWithRepliesSinceDate($instance->network_username, $instance->network, 7,
            $since_date)) {
                //Save average replies over past 7 days
                $average_reply_count_7_days = null;
                $average_reply_count_7_days = $post_dao->getAverageReplyCount($instance->network_username,
                $instance->network, 7, $since_date);
                if ($average_reply_count_7_days != null ) {
                    $insight_baseline_dao->insertInsightBaseline('avg_reply_count_last_7_days', $instance->id,
                    $average_reply_count_7_days, $since_date);
                    $this->logger->logSuccess("Averaged $average_reply_count_7_days Replies in the 7 days before ".
                    $since_date, __METHOD__.','.__LINE__);
                }

                //Save reply high for last 7 days
                $high_reply_count_7_days = $post_dao->getAllPostsByUsernameOrderedBy($instance->network_username,
                $network=$instance->network, 1, 'reply_count_cache', 7, $iterator = false, $is_public = false,
                $since=$since_date);
                if ($high_reply_count_7_days != null ) {
                    $high_reply_count_7_days = $high_reply_count_7_days[0]->reply_count_cache;
                    $insight_baseline_dao->insertInsightBaseline('high_reply_count_last_7_days', $instance->id,
                    $high_reply_count_7_days, $since_date);
                    $this->logger->logSuccess("High of $high_reply_count_7_days replies in the 7 days before ".
                    $since_date, __METHOD__.','.__LINE__);
                }
            }

            if ($post_dao->doesUserHavePostsWithRepliesSinceDate($instance->network_username, $instance->network, 30,
            $since_date)) {
                //Save average replies over past 30 days
                $average_reply_count_30_days = null;
                $average_reply_count_30_days = $post_dao->getAverageReplyCount($instance->network_username,
                $instance->network, 30, $since_date);
                if ($average_reply_count_30_days != null ) {
                    $insight_baseline_dao->insertInsightBaseline('avg_reply_count_last_30_days', $instance->id,
                    $average_reply_count_30_days, $since_date);
                    $this->logger->logSuccess("Averaged $average_reply_count_30_days replies in the 30 days before ".
                    $since_date, __METHOD__.','.__LINE__);
                }

                //Save reply high for last 30 days
                $high_reply_count_30_days = $post_dao->getAllPostsByUsernameOrderedBy($instance->network_username,
                $network=$instance->network, 1, 'reply_count_cache', 30, $iterator = false, $is_public = false,
                $since=$since_date);
                if ($high_reply_count_30_days != null ) {
                    $high_reply_count_30_days = $high_reply_count_30_days[0]->reply_count_cache;
                    $insight_baseline_dao->insertInsightBaseline('high_reply_count_last_30_days', $instance->id,
                    $high_reply_count_30_days, $since_date);
                    $this->logger->logSuccess("High of $high_reply_count_30_days replies in the 30 days before ".
                    $since_date, __METHOD__.','.__LINE__);
                }
            }

            if ($post_dao->doesUserHavePostsWithRepliesSinceDate($instance->network_username, $instance->network, 365,
            $since_date)) {
                //Save reply high for last 365 days
                $high_reply_count_365_days = $post_dao->getAllPostsByUsernameOrderedBy($instance->network_username,
                $network=$instance->network, 1, 'reply_count_cache', 365, $iterator = false, $is_public = false,
                $since=$since_date);
                if ($high_reply_count_365_days != null ) {
                    $high_reply_count_365_days = $high_reply_count_365_days[0]->reply_count_cache;
                    $insight_baseline_dao->insertInsightBaseline('high_reply_count_last_365_days', $instance->id,
                    $high_reply_count_365_days, $since_date);
                    $this->logger->logSuccess("High of $high_reply_count_365_days replies in the 365 days before ".
                    $since_date, __METHOD__.','.__LINE__);
                }
            }
            $days_ago++;
        }
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('ReplySpikeInsight');
