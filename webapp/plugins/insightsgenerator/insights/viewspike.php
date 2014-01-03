<?php
/*
 Plugin Name: View Spike Insight
 Description: Video view spike and high insights for the past 7, 30, and 365 days.
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/viewspike.php
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
 * ViewSpike Insight
 *
 * Copyright (c) 2013 Aaron Kalair
 *
 * @author Aaron Kalair aaronkalair@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Aaron Kalair
 */

class ViewSpikeInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        self::generateInsightBaselines($instance, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $filename = basename(__FILE__, ".php");
        $video_dao = DAOFactory::getDAO('VideoDAO');
        $insight_text = '';

        $simplified_post_date = "";

        foreach ($last_week_of_posts as $post) {
            if($post->network == 'youtube') {
                $video = $video_dao->getVideoByID($post->post_id, 'youtube');
            }
            else {
                break;
            }
            if ($video->views > 2) { //Only show insight for more than 2 views
                // First get spike/high 30/90/365 day baselines
                if ($simplified_post_date != date('Y-m-d', strtotime($post->pub_date))) {
                    $simplified_post_date = date('Y-m-d', strtotime($post->pub_date));

                    $average_view_count_30_days =
                    $insight_baseline_dao->getInsightBaseline('avg_view_count_last_30_days', $instance->id,
                    $simplified_post_date);

                    $average_view_count_90_days =
                    $insight_baseline_dao->getInsightBaseline('avg_view_count_last_90_days', $instance->id,
                    $simplified_post_date);

                    $high_view_count_30_days =
                    $insight_baseline_dao->getInsightBaseline('high_view_count_last_30_days', $instance->id,
                    $simplified_post_date);

                    $high_view_count_90_days =
                    $insight_baseline_dao->getInsightBaseline('high_view_count_last_90_days', $instance->id,
                    $simplified_post_date);

                    $high_view_count_365_days =
                    $insight_baseline_dao->getInsightBaseline('high_view_count_last_365_days', $instance->id,
                    $simplified_post_date);

                    $hot_videos_data = $video_dao->getHotVideos($instance->network_username, 'youtube', 10, 'views',
                    'Views');
                    $chart_data = VideoMySQLDAO::getHotVideosVisualizationData($hot_videos_data, 'Views');
                }
                // Next compare post view counts to baselines and store insights where there's a spike or high
                if (isset($high_view_count_365_days->value)
                && $video->views >= $high_view_count_365_days->value) {
                    if (isset($chart_data)) {
                        $headline = "<strong>".number_format($video->views) . " people</strong> viewed ";
                        $headline .= "$instance->network_username's video $video->post_text.";
                        $insight_text = "That makes ";
                        $insight_text .= "<a href=\"http://www.youtube.com/watch?v=$video->post_id\">$video->post_text</a>";
                        $insight_text .= " a new 365-day record for ";
                        $insight_text .= "<a href=\"http://plus.google.com/$instance->network_user_id\">";
                        $insight_text .= "$instance->network_username</a>!";

                        $this->insight_dao->insertInsightDeprecated('view_high_365_day_'.$video->id, $instance->id,
                        $simplified_post_date, $headline, $insight_text, $filename, Insight::EMPHASIS_HIGH,
                        serialize(array($video, $chart_data)));

                        $this->insight_dao->deleteInsight('view_high_90_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                        $this->insight_dao->deleteInsight('view_high_90_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                        $this->insight_dao->deleteInsight('view_spike_30_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                        $this->insight_dao->deleteInsight('view_spike_30_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                    }
                } elseif (isset($high_view_count_90_days->value)
                && $video->views >= $high_view_count_90_days->value) {
                    if (isset($chart_data)) {
                        $headline = "<strong>".number_format($video->views) . " people</strong> viewed ";
                        $headline .= "$instance->network_username's video $video->post_text.";
                        $insight_text = "That makes ";
                        $insight_text .= "<a href=\"http://www.youtube.com/watch?v=$video->post_id\">$video->post_text</a>";
                        $insight_text .= " a new 90-day record for ";
                        $insight_text .= "<a href=\"http://plus.google.com/$instance->network_user_id\">";
                        $insight_text .= "$instance->network_username</a>.";

                        $this->insight_dao->insertInsightDeprecated('view_high_90_day_'.$video->id, $instance->id,
                        $simplified_post_date, $headline, $insight_text, $filename, Insight::EMPHASIS_HIGH,
                        serialize(array($video, $chart_data)));

                        $this->insight_dao->deleteInsight('view_high_30_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                        $this->insight_dao->deleteInsight('view_spike_30_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                    }
                } elseif (isset($high_view_count_30_days->value)
                && $video->views >= $high_view_count_30_days->value) {
                    if (isset($chart_data)) {
                        $headline = "<strong>".number_format($video->views) . " people</strong> viewed ";
                        $headline .= "$instance->network_username's video $video->post_text.";
                        $insight_text = "That makes ";
                        $insight_text .= "<a href=\"http://www.youtube.com/watch?v=$video->post_id\">$video->post_text</a>";
                        $insight_text .= " a new 30-day record for ";
                        $insight_text .= "<a href=\"http://plus.google.com/$instance->network_user_id\">";
                        $insight_text .= "$instance->network_username</a>.";

                        $this->insight_dao->insertInsightDeprecated('view_high_30_day_'.$video->id, $instance->id,
                        $simplified_post_date, $headline, $insight_text, $filename, Insight::EMPHASIS_HIGH,
                        serialize(array($video, $chart_data)));
                    }
                }

                if (isset($average_view_count_90_days->value)
                && $video->views > ($average_view_count_90_days->value*2)) {
                    if (isset($chart_data)) {
                        $multiplier = floor($video->views/$average_view_count_30_days->value);
                        $multiplier = $this->terms->getMultiplierAdverb($multiplier);
                        $headline = "<strong>".number_format($video->views)." people</strong> viewed ";
                        $headline .= "$video->post_text &mdash; looks like it's going viral.";
                        $insight_text = "<a href=\"http://plus.google.com/$instance->network_user_id\">";
                        $insight_text .= "$instance->network_username</a>'s video <a href=\"http://www.youtube.com/watch?v=";
                        $insight_text .= "$video->post_id\">$video->post_text</a> got more than <strong>".$multiplier."</strong> ";
                        $insight_text .= "the 90-day average of views.";

                        $this->insight_dao->insertInsightDeprecated('view_spike_90_day_'.$post->id, $instance->id,
                        $simplified_post_date, $headline, $insight_text, $filename, Insight::EMPHASIS_LOW,
                        serialize(array($video, $chart_data)));

                        $this->insight_dao->deleteInsight('view_spike_30_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                    }
                } elseif (isset($average_view_count_30_days->value)
                && $video->views > ($average_view_count_30_days->value*2)) {
                    if (isset($chart_data)) {
                        $multiplier = floor($video->views/$average_view_count_30_days->value);
                        $multiplier = $this->terms->getMultiplierAdverb($multiplier);
                        $headline = "<strong>".number_format($video->views)." people</strong> viewed ";
                        $headline .= "$video->post_text &mdash; looks like it's doing pretty well.";
                        $insight_text = "<a href=\"http://plus.google.com/$instance->network_user_id\">";
                        $insight_text .= "$instance->network_username</a>'s video <a href=\"http://www.youtube.com/watch?v=";
                        $insight_text .= "$video->post_id\">$video->post_text</a> got more than <strong>".$multiplier."</strong> ";
                        $insight_text .= "the 30-day average of views.";

                        $this->insight_dao->insertInsightDeprecated('view_spike_30_day_'.$post->id, $instance->id,
                        $simplified_post_date, $headline, $insight_text, $filename, Insight::EMPHASIS_LOW,
                        serialize(array($video, $chart_data)));
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
        $video_dao = DAOFactory::getDAO('VideoDAO');
        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');

        $days_ago = 0;
        // Generate baseline post insights for the last 7 days
        while ($days_ago < $number_days) {
            $since_date = date("Y-m-d", strtotime("-".$days_ago." day"));

            if ($video_dao->doesUserHaveVideosWithViewsSinceDate($instance->network_username, $instance->network, 30,
            $since_date)) {
                //Save average views over past 30 days
                $average_view_count_30_days = null;
                $average_view_count_30_days = $video_dao->getAverageViews($instance->network_username,
                $instance->network, 30, $since_date);
                if ($average_view_count_30_days != null ) {
                    $insight_baseline_dao->insertInsightBaseline('avg_view_count_last_30_days', $instance->id,
                    $average_view_count_30_days, $since_date);
                    $this->logger->logSuccess("Averaged $average_view_count_30_days views in the 30 days before ".
                    $since_date, __METHOD__.','.__LINE__);
                }

                //Save view high for last 30 days
                $high_view_count_30_days = $video_dao->getHighestViews($instance->network_username, $instance->network,
                30, $since_date);
                if ($high_view_count_30_days != null ) {
                    $insight_baseline_dao->insertInsightBaseline('high_view_count_last_30_days', $instance->id,
                    $high_view_count_30_days, $since_date);
                    $this->logger->logSuccess("High of $high_view_count_30_days views in the 30 days before ".
                    $since_date, __METHOD__.','.__LINE__);
                }
            }

            if ($video_dao->doesUserHaveVideosWithViewsSinceDate($instance->network_username, $instance->network, 90,
            $since_date)) {
                //Save average views over past 90 days
                $average_view_count_90_days = null;
                $average_view_count_90_days = $video_dao->getAverageViews($instance->network_username,
                $instance->network, 90, $since_date);
                if ($average_view_count_90_days != null ) {
                    $insight_baseline_dao->insertInsightBaseline('avg_view_count_last_90_days', $instance->id,
                    $average_view_count_90_days, $since_date);
                    $this->logger->logSuccess("Averaged $average_view_count_90_days views in the 90 days before ".
                    $since_date, __METHOD__.','.__LINE__);
                }

                //Save view high for last 90 days
                $high_view_count_90_days = $video_dao->getHighestViews($instance->network_username, $instance->network,
                90, $since_date);
                if ($high_view_count_90_days != null ) {
                    $insight_baseline_dao->insertInsightBaseline('high_view_count_last_90_days', $instance->id,
                    $high_view_count_90_days, $since_date);
                    $this->logger->logSuccess("High of $high_view_count_90_days views in the 90 days before ".
                    $since_date, __METHOD__.','.__LINE__);
                }
            }

            if ($video_dao->doesUserHaveVideosWithViewsSinceDate($instance->network_username, $instance->network, 365,
            $since_date)) {
                //Save view high for last 365 days
                $high_view_count_365_days = $video_dao->getHighestViews($instance->network_username, $instance->network,
                365, $since_date);
                if ($high_view_count_365_days != null ) {
                    $insight_baseline_dao->insertInsightBaseline('high_view_count_last_365_days', $instance->id,
                    $high_view_count_365_days, $since_date);
                    $this->logger->logSuccess("High of $high_view_count_365_days views in the 365 days before ".
                    $since_date, __METHOD__.','.__LINE__);
                }
            }
            $days_ago++;
        }
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('ViewSpikeInsight');
