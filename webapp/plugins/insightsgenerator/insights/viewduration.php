<?php
/*
 Plugin Name: Percentage Viewed Duration
 Description: Videos which have a higher or lower than average percentage viewed duration.
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/viewduration.php
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
 * View Duration Insight
 *
 * Highlights videos which have a higher or lower than average percentage viewed duration
 *
 * Copyright (c) 2013 Aaron Kalair
 *
 * @author Aaron Kalair aaronkalair@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Aaron Kalair
 */

class ViewDurationInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_weeks_posts, $number_days) {
        parent::generateInsight($instance, $last_weeks_posts, $number_days);
        self::generateBaselines($instance);

        $video_dao = DAOFactory::getDAO('VideoDAO');
        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');

        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
        $filename = basename(__FILE__, ".php");
        $insight_text = '';

        foreach ($last_weeks_posts as $post) {
            // YouTube users can post replies to their videos and these get passed to us here also and have no
            // associated videos so check we actually got a video post.
            if($post->network == 'youtube' && $post->in_reply_to_post_id == null) {
                $video = $video_dao->getVideoByID($post->post_id, 'youtube');
                $simplified_post_date = date('Y-m-d', strtotime($post->pub_date));
                $average_month = $baseline_dao->getInsightBaseline('avg_view_percentage_month', $instance->id,
                date('Y-m-d'));
                $average_all_time = $baseline_dao->getInsightBaseline('avg_view_percentage_all_time', $instance->id,
                date('Y-m-d'));
                $average_90 = $baseline_dao->getInsightBaseline('avg_view_percentage_90', $instance->id,
                date('Y-m-d'));
                $high = $baseline_dao->getInsightBaseline('view_percentage_all_time_high', $instance->id,
                date('Y-m-d'));
                $high_365 = $baseline_dao->getInsightBaseline('view_percentage_year_high',
                $instance->id, date('Y-m-d'));
                $high_90 = $baseline_dao->getInsightBaseline('view_percentage_90_high', $instance->id, date('Y-m-d'));
                $low = $baseline_dao->getInsightBaseline('view_percentage_all_time_low', $instance->id, date('Y-m-d'));
                $low_365 = $baseline_dao->getInsightBaseline('view_percentage_year_low', $instance->id, date('Y-m-d'));
                $low_90 = $baseline_dao->getInsightBaseline('view_percentage_90_low', $instance->id, date('Y-m-d'));
                $hot_videos = $video_dao->getHotVideos($instance->network_username, 'youtube', 10,
                'average_view_percentage', 'Average View Percentage');
                $chart = VideoMySQLDAO::getHotVideosVisualizationData($hot_videos, 'Average View Percentage');
            } else {
                break;
            }

            $view_duration = round($video->average_view_percentage, 0);
            // The analytics data isn't always available for new videos so check this value isn't 0
            if($view_duration == 0){
                break;
            }
            $change_month = round($view_duration - $average_month->value, 0);
            $change_all_time = round($view_duration - $average_all_time->value, 0);
            $change_90 = round($view_duration - $average_90->value, 0);
            $headline = "On average, viewers watched "; // <a href=http://plus.google.com/".$instance->network_user_id.">
            $headline .= $instance->network_username."'s video"; //</a>
            $headline .= " ".$video->post_text." "; //<a href=http://www.youtube.com/watch?v=".$video->post_id."></a>
            $headline .= "<strong>".$view_duration."%</strong> of the way through.";
            $can_insert = false;

            // Increases or decreases compared to the average
            if( abs($change_all_time) >=30 ) {
                $can_insert = true;
                $emphasis = Insight::EMPHASIS_HIGH;
                if($change_all_time >=30) {
                    $insight_text = "That's <strong>" . $change_all_time . "%</strong> longer than "
                                    ."<a href=\"http://plus.google.com/" . $instance->network_user_id . "\">"
                                    .$instance->network_username . "</a>'s all-time average.";
                } else {
                    $insight_text .= "That's <strong>" . abs($change_all_time) . "%</strong> less than "
                                    . "<a href=\"http://plus.google.com/". $instance->network_user_id . "\">"
                                    . $instance->network_username . "</a>'s all-time average.";
                }
            } elseif( abs($change_90) >= 30 ) {
                $can_insert = true;
                $emphasis = Insight::EMPHASIS_HIGH;
                if($change_90 >=30) {
                    $insight_text = "That's <strong>" . $change_90 . "%</strong> longer than "
                                    . "<a href=\"http://plus.google.com/". $instance->network_user_id . "\">"
                                    . $instance->network_username . "</a>'s 90-day average.";
                } else {
                    $insight_text = "That's <strong>" . abs($change_90) . "%</strong> less than "
                                    . "<a href=\"http://plus.google.com/" . $instance->network_user_id . "\">"
                                    . $instance->network_username . "</a>'s 90-day average.";
                }
            } elseif( abs($change_month) >=30 ) {
                $can_insert = true;
                $emphasis = Insight::EMPHASIS_HIGH;
                if($change_month >=30) {
                    $insight_text = "That's <strong>" . $change_month."%</strong> longer than "
                                    . "<a href=\"http://plus.google.com/" . $instance->network_user_id . "\">"
                                    . $instance->network_username . "</a>'s 30-day average.";
                } else {
                    $insight_text = "That's <strong>" . abs($change_month)."%</strong> less than "
                                    . "<a href=\"http://plus.google.com/" . $instance->network_user_id . "\">"
                                    . $instance->network_username . "</a>'s 30-day average.";
                }
            } elseif( abs($change_all_time) >=15 ) {
                $can_insert = true;
                $emphasis = Insight::EMPHASIS_MED;
                if($change_all_time >=15) {
                    $insight_text = "That's <strong>" . $change_all_time."%</strong> longer than "
                                    . "<a href=\"http://plus.google.com/" . $instance->network_user_id . "\">"
                                    . $instance->network_username . "</a>'s all-time average.";
                } else {
                    $insight_text = "That's <strong>" . abs($change_all_time)."%</strong> less than "
                                    . "<a href=\"http://plus.google.com/" . $instance->network_user_id . "\">"
                                    . $instance->network_username . "</a>'s all-time average.";
                }
            } elseif( abs($change_90) >= 15 ) {
                $can_insert = true;
                $emphasis = Insight::EMPHASIS_MED;
                if($change_90 >=15) {
                    $insight_text = "That's <strong>" . $change_90."%</strong> longer than "
                                    . "<a href=\"http://plus.google.com/" . $instance->network_user_id . "\">"
                                    . $instance->network_username . "</a>'s 90-day average.";
                } else {
                    $insight_text = "That's <strong>" . abs($change_90)."%</strong> less than "
                                    . "<a href=\"http://plus.google.com/" . $instance->network_user_id . "\">"
                                    . $instance->network_username . "</a>'s 90-day average.";
                }
            } elseif( abs($change_month) >=15 ) {
                $can_insert = true;
                $emphasis = Insight::EMPHASIS_MED;
                if($change_month >=15) {
                    $insight_text = "That's <strong>" . $change_month."%</strong> longer than "
                                    . "<a href=\"http://plus.google.com/" . $instance->network_user_id . "\">"
                                    . $instance->network_username . "</a>'s 30-day average.";
                } else {
                    $insight_text = "That's <strong>" . abs($change_month)."%</strong> less than "
                                    . "<a href=\"http://plus.google.com/" . $instance->network_user_id . "\">"
                                    . $instance->network_username . "</a>'s 30-day average.";
                }
            } elseif( abs($change_all_time) >=5 ) {
                $can_insert = true;
                $emphasis = Insight::EMPHASIS_LOW;
                if($change_all_time >=5) {
                    $insight_text = "That's <strong>" . $change_all_time."%</strong> longer than "
                                    . "<a href=\"http://plus.google.com/" . $instance->network_user_id . "\">"
                                    . $instance->network_username . "</a>'s all-time average.";
                } else {
                    $insight_text = "That's <strong>" . abs($change_all_time)."%</strong> less than "
                                    . "<a href=\"http://plus.google.com/" . $instance->network_user_id . "\">"
                                    . $instance->network_username . "</a>'s all-time average.";
                }
            } elseif( abs($change_90) >=5 ) {
                $can_insert = true;
                $emphasis = Insight::EMPHASIS_LOW;
                if($change_90 >=5) {
                    $insight_text = "That's <strong>" . $change_90."%</strong> longer than "
                                    . "<a href=\"http://plus.google.com/" . $instance->network_user_id . "\">"
                                    . $instance->network_username . "</a>'s 90-day average.";
                } else {
                    $insight_text = "That's <strong>" . abs($change_90)."%</strong> less than "
                                    . "<a href=\"http://plus.google.com/" . $instance->network_user_id . "\">"
                                    . $instance->network_username . "</a>'s 90-day average.";
                }
            } elseif( abs($change_month) >=5 ) {
                $can_insert = true;
                $emphasis = Insight::EMPHASIS_LOW;
                if($change_month >=5) {
                    $insight_text = "That's <strong>" . $change_month."%</strong> longer than "
                                    . "<a href=\"http://plus.google.com/" . $instance->network_user_id . "\">"
                                    . $instance->network_username . "</a>'s 30-day average.";
                } else {
                    $insight_text = "That's <strong>" . abs($change_month)."%</strong> less than "
                                    . "<a href=\"http://plus.google.com/" . $instance->network_user_id . "\">"
                                    . $instance->network_username . "</a>'s 30-day average.";
                }
            }

            if($can_insert) {
                $this->insight_dao->insertInsightDeprecated('view_duration', $instance->id, $simplified_post_date,
                $headline, $insight_text, $filename, $emphasis, serialize(array($chart,$video)));
            }

            // New highs and lows
            $can_insert = false;
            // $insight_text = "<a href=\"http://plus.google.com/$instance->network_user_id\">$instance->network_username</a>'s ";
            // $insight_text .= "video <a href=\"http://www.youtube.com/watch?v=$video->post_id\">$video->post_text</a> was ";
            // $insight_text .= "viewed <strong>".$view_duration."%</strong> of the way through on average.";
            $headline = "$instance->network_username's ";
            $headline .= "video $video->post_text was ";
            $headline .= "viewed <strong>".$view_duration."%</strong> of the way through on average.";

            if($view_duration >= $high->value && $high->value != 0) {
                $insight_text = "That's <a href=\"http://plus.google.com/$instance->network_user_id\">$instance->network_username</a>'s new all-time high!";
                $emphasis = Insight::EMPHASIS_HIGH;
                $can_insert = true;
            } elseif($view_duration <= $low->value && $low->value != 0) {
                // $insight_text = "That's <a href=\"http://plus.google.com/$instance->network_user_id\">$instance->network_username</a>'s new all-time low.";
                // find a nicer way to say this.
                $emphasis = Insight::EMPHASIS_HIGH;
                $can_insert = true;
            } elseif($view_duration >= $high_365->value && $high_365->value != 0) {
                $insight_text = "That's <a href=\"http://plus.google.com/$instance->network_user_id\">$instance->network_username</a>'s new 365-day record!";
                $emphasis = Insight::EMPHASIS_MED;
                $can_insert = true;
            } elseif($view_duration <= $low_365->value && $low_365->value != 0) {
                // $insight_text = "That's <a href=\"http://plus.google.com/$instance->network_user_id\">$instance->network_username</a>'s new 365-day low.";
                // this one is too depressing.
                $emphasis = Insight::EMPHASIS_MED;
                $can_insert = true;
            }elseif($view_duration >= $high_90->value && $high_90->value != 0) {
                $insight_text = "That's <a href=\"http://plus.google.com/$instance->network_user_id\">$instance->network_username</a>'s new 90-day record.";
                $emphasis = Insight::EMPHASIS_LOW;
                $can_insert = true;
            } elseif($view_duration <= $low_90->value && $low_90->value != 0) {
                // $insight_text = "That's <a href=\"http://plus.google.com/$instance->network_user_id\">$instance->network_username</a>'s new 90-day low.";
                // too much of a bummer.
                $emphasis = Insight::EMPHASIS_LOW;
                $can_insert = true;
            }

            if($can_insert) {
                $this->insight_dao->insertInsightDeprecated('view_duration_record', $instance->id,
                $simplified_post_date, $headline, $insight_text, $filename, $emphasis, serialize(array($chart,$video)));
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

    private function generateBaselines($instance) {
        $video_dao = DAOFactory::getDAO('VideoDAO');
        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');

        $month_view_percent_average = $video_dao->getAverageOfAverageViewPercentage($instance->network_username,
        'youtube', 30);
        $ninety_view_percent_average = $video_dao->getAverageOfAverageViewPercentage($instance->network_username,
        'youtube', 90);
        $all_time_view_percent_average = $video_dao->getAverageOfAverageViewPercentage($instance->network_username,
        'youtube');
        $all_time_high = $video_dao->getAverageViewPercentageHigh($instance->network_username, 'youtube');
        $all_time_low = $video_dao->getAverageViewPercentageLow($instance->network_username, 'youtube');
        $year_high = $video_dao->getAverageViewPercentageHigh($instance->network_username, 'youtube', 365);
        $year_low = $video_dao->getAverageViewPercentageLow($instance->network_username, 'youtube', 365);
        $ninety_high = $video_dao->getAverageViewPercentageHigh($instance->network_username, 'youtube', 90);
        $ninety_low = $video_dao->getAverageViewPercentageLow($instance->network_username, 'youtube', 90);

        $baseline_dao->insertInsightBaseline('avg_view_percentage_month', $instance->id,
        $month_view_percent_average, date('Y-m-d'));
        $baseline_dao->insertInsightBaseline('avg_view_percentage_90', $instance->id,
        $ninety_view_percent_average, date('Y-m-d'));
        $baseline_dao->insertInsightBaseline('avg_view_percentage_all_time', $instance->id,
        $all_time_view_percent_average, date('Y-m-d'));
        $baseline_dao->insertInsightBaseline('view_percentage_all_time_high', $instance->id,
        $all_time_high, date('Y-m-d'));
        $baseline_dao->insertInsightBaseline('view_percentage_all_time_low', $instance->id,
        $all_time_low, date('Y-m-d'));
        $baseline_dao->insertInsightBaseline('view_percentage_year_high', $instance->id,
        $year_high, date('Y-m-d'));
        $baseline_dao->insertInsightBaseline('view_percentage_year_low', $instance->id,
        $year_low, date('Y-m-d'));
        $baseline_dao->insertInsightBaseline('view_percentage_90_high', $instance->id,
        $ninety_high, date('Y-m-d'));
        $baseline_dao->insertInsightBaseline('view_percentage_90_low', $instance->id,
        $ninety_low, date('Y-m-d'));
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('ViewDurationInsight');

