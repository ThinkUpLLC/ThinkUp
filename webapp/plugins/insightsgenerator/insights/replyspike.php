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
                && $post->reply_count_cache >= $high_reply_count_365_days->value) {

                    $hot_posts_data = $this->insight_dao->getPreCachedInsightData('PostMySQLDAO::getHotPosts',
                    $instance->id, $simplified_post_date);
                    if (isset($hot_posts_data)) {
                        $this->insight_dao->insertInsight('reply_high_365_day_'.$post->id, $instance->id,
                        $simplified_post_date, "New 365-day record!","<strong>".number_format($post->reply_count_cache).
                        " people</strong> replied to $this->username's post.", $filename, Insight::EMPHASIS_HIGH,
                        serialize(array($post, $hot_posts_data)));

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
                && $post->reply_count_cache >= $high_reply_count_30_days->value) {

                    $hot_posts_data = $this->insight_dao->getPreCachedInsightData('PostMySQLDAO::getHotPosts',
                    $instance->id, $simplified_post_date);

                    if (isset($hot_posts_data)) {
                        $this->insight_dao->insertInsight('reply_high_30_day_'.$post->id, $instance->id,
                        $simplified_post_date, "New 30-day record!", "<strong>".number_format($post->reply_count_cache).
                        " people</strong> replied to $this->username's post.", $filename, Insight::EMPHASIS_HIGH,
                        serialize(array($post, $hot_posts_data)));

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
                        $this->insight_dao->insertInsight('reply_high_7_day_'.$post->id, $instance->id,
                        $simplified_post_date, "New 7-day record!", "<strong>".number_format($post->reply_count_cache).
                        " people</strong> replied to $this->username's post.", $filename, Insight::EMPHASIS_HIGH,
                        serialize(array($post, $hot_posts_data)));

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
                        $this->insight_dao->insertInsight('reply_spike_30_day_'.$post->id, $instance->id,
                        $simplified_post_date, "Conversation starter:",
                        "<strong>".number_format($post->reply_count_cache).
                        " people</strong> replied to $this->username's post, more than <strong>".$multiplier.
                        "x</strong> $this->username's 30-day average.", $filename,
                        Insight::EMPHASIS_LOW, serialize(array($post, $hot_posts_data)));

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
                        $this->insight_dao->insertInsight('reply_spike_7_day_'.$post->id, $instance->id,
                        $simplified_post_date, "Conversation starter:",
                        "<strong>".number_format($post->reply_count_cache).
                        " people</strong> replied to $this->username's post, more than <strong>" .$multiplier.
                        "x</strong> $this->username's 7-day average.", $filename, Insight::EMPHASIS_LOW,
                        serialize(array($post, $hot_posts_data)));

                        $this->insight_dao->deleteInsight('reply_high_30_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                        $this->insight_dao->deleteInsight('reply_high_7_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                        $this->insight_dao->deleteInsight('reply_spike_30_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                    }
                }
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
