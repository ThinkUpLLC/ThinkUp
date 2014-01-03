<?php
/*
 Plugin Name: Video View Minutes
 Description: Highlights videos that have been viewed for a larger number of minutes than your average videos.
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/minutesviewed.php
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
 * MinutesViewed Insight
 *
 * Copyright (c) 2013 Aaron Kalair
 *
 * @author Aaron Kalair aaronkalair@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Aaron Kalair
 */

class MinutesViewedInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $video_dao = DAOFactory::getDAO('VideoDAO');
        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        self::generateBaselines($instance);

        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
        $filename = basename(__FILE__, ".php");

        foreach ($last_week_of_posts as $post) {
            $simplified_post_date = date('Y-m-d', strtotime($post->pub_date));

            if($post->network == 'youtube') {
                $video = $video_dao->getVideoByID($post->post_id, 'youtube');
                $average_mins_viewed_month = $baseline_dao->getInsightBaseline('avg_minutes_viewed_month',
                $instance->id, date('Y-m-d') );
                $average_mins_viewed_90 = $baseline_dao->getInsightBaseline('avg_minutes_viewed_90', $instance->id,
                date('Y-m-d') );
                $average_mins_viewed_all_time = $baseline_dao->getInsightBaseline('avg_minutes_viewed_all_time',
                $instance->id, date('Y-m-d') );
                $max_mins_viewed = $baseline_dao->getInsightBaseline('all_time_mins_viewed_high', $instance->id,
                date('Y-m-d') );
                $year_mins_viewed = $baseline_dao->getInsightBaseline('year_mins_viewed_high', $instance->id,
                date('Y-m-d') );
                $ninety_mins_viewed = $baseline_dao->getInsightBaseline('90_mins_viewed_high', $instance->id,
                date('Y-m-d') );
                $hot_videos = $video_dao->getHotVideos($instance->network_username, 'youtube', 10, 'minutes_watched',
                'Minutes Watched');
                $chart = VideoMySQLDAO::getHotVideosVisualizationData($hot_videos, 'Minutes Watched');
            } else {
                break;
            }

            $headline = "Viewers watched ".$video->post_text . " ";
            $headline .= 'for a total of ';
            $headline .= InsightTerms::getSyntacticTimeDifference($video->minutes_watched*60).'.';
            $insight_text = "<a href=http://plus.google.com/$instance->network_user_id>".$instance->network_username."</a>'s ";
            $insight_text .= "video <a href=http://www.youtube.com/watch?v=$video->post_id>".$video->post_text."</a> ";
            $insight_text .= "really left an impression. ";
            $can_insert = false;

            // Higher than averages
            if($video->minutes_watched >= $average_mins_viewed_all_time->value * 10 &&
            $average_mins_viewed_all_time->value != 0) {
                $multiplier = $this->terms->getMultiplierAdverb(round($video->minutes_watched/
                $average_mins_viewed_all_time->value,2),'multiplier');
                $insight_text .=  "That's <strong>" . $multiplier."</strong> the all-time average.";
                $emphasis = Insight::EMPHASIS_HIGH;
                $can_insert = true;
            } elseif($video->minutes_watched >= $average_mins_viewed_90->value * 10 &&
            $average_mins_viewed_90->value != 0) {
                $multiplier = $this->terms->getMultiplierAdverb(round($video->minutes_watched/
                $average_mins_viewed_90->value,2),'multiplier');
                $insight_text .=  "That's <strong>" . $multiplier."</strong> the 90-day average.";
                $emphasis = Insight::EMPHASIS_HIGH;
                $can_insert = true;
            } elseif($video->minutes_watched >= $average_mins_viewed_month->value * 10 &&
            $average_mins_viewed_month->value != 0) {
                $multiplier = $this->terms->getMultiplierAdverb(round($video->minutes_watched/
                $average_mins_viewed_month->value,2),'multiplier');
                $insight_text .=  "That's <strong>" . $multiplier."</strong> the 30-day average.";
                $emphasis = Insight::EMPHASIS_HIGH;
                $can_insert = true;
            } elseif($video->minutes_watched >= $average_mins_viewed_all_time->value * 5 &&
            $average_mins_viewed_all_time->value != 0) {
                $multiplier = $this->terms->getMultiplierAdverb(round($video->minutes_watched/
                $average_mins_viewed_all_time->value,2),'multiplier');
                $insight_text .=  "That's <strong>" . $multiplier."</strong> the all-time average.";
                $emphasis = Insight::EMPHASIS_MED;
                $can_insert = true;
            } elseif($video->minutes_watched >= $average_mins_viewed_90->value * 5 &&
            $average_mins_viewed_90->value != 0) {
                $multiplier = $this->terms->getMultiplierAdverb(round($video->minutes_watched/
                $average_mins_viewed_90->value,2),'multiplier');
                $insight_text .=  "That's <strong>" . multiplier."</strong> the 90-day average.";
                $emphasis = Insight::EMPHASIS_MED;
                $can_insert = true;
            } elseif($video->minutes_watched >= $average_mins_viewed_month->value * 5 &&
            $average_mins_viewed_month->value != 0) {
                $multiplier = $this->terms->getMultiplierAdverb(round($video->minutes_watched/
                $average_mins_viewed_month->value,2),'multiplier');
                $insight_text .=  "That's <strong>" . $multiplier."</strong> the 30-day average.";
                $emphasis = Insight::EMPHASIS_MED;
                $can_insert = true;
            } elseif($video->minutes_watched >= $average_mins_viewed_all_time->value * 2 &&
            $average_mins_viewed_all_time->value != 0) {
                $multiplier = $this->terms->getMultiplierAdverb(round($video->minutes_watched/
                $average_mins_viewed_all_time->value,2),'multiplier');
                $insight_text .=  "That's <strong>" . $multiplier."</strong> the all-time average.";
                $emphasis = Insight::EMPHASIS_LOW;
                $can_insert = true;
            } elseif($video->minutes_watched >= $average_mins_viewed_90->value * 2 &&
            $average_mins_viewed_90->value != 0) {
                $multiplier = $this->terms->getMultiplierAdverb(round($video->minutes_watched/
                $average_mins_viewed_90->value,2),'multiplier');
                $insight_text .=  "That's <strong>" . $multiplier."</strong> the 90-day average.";
                $emphasis = Insight::EMPHASIS_LOW;
                $can_insert = true;
            } elseif($video->minutes_watched >= $average_mins_viewed_month->value * 2 &&
            $average_mins_viewed_month->value != 0) {
                $multiplier = $this->terms->getMultiplierAdverb(round($video->minutes_watched/
                $average_mins_viewed_month->value,2),'multiplier');
                $insight_text .=  "That's <strong>" . $multiplier."</strong> the 30-day average.";
                $emphasis = Insight::EMPHASIS_LOW;
                $can_insert = true;
            }

            if($can_insert) {
                $this->insight_dao->insertInsightDeprecated('minutes_viewed'.$video->id, $instance->id,
                $simplified_post_date, $headline, $insight_text, $filename, $emphasis, serialize(array($video, $chart)));
            }

            $headline = "Viewers watched ".$video->post_text . " ";
            $headline .= 'for a total of ';
            $headline .= InsightTerms::getSyntacticTimeDifference($video->minutes_watched*60).'.';
            $insight_text = "<a href=http://plus.google.com/$instance->network_user_id>".$instance->network_username."</a>'s ";
            $insight_text .= "video <a href=http://www.youtube.com/watch?v=$video->post_id>".$video->post_text."</a> ";
            $insight_text .= "really left an impression. ";

            // All time highs
            if($video->minutes_watched >= $max_mins_viewed->value && $max_mins_viewed->value != 0) {
                $insight_text .= "set a new all-time high!";
                $emphasis = Insight::EMPHASIS_HIGH;
                $can_insert = true;
            } elseif($video->minutes_watched >= $year_mins_viewed->value && $year_mins_viewed->value != 0) {
                $insight_text .= "set a new 365-day high!";
                $emphasis = Insight::EMPHASIS_MED;
                $can_insert = true;
            } elseif($video->minutes_watched >= $ninety_mins_viewed->value && $ninety_mins_viewed->value != 0) {
                $insight_text .= "set a new 90-day high!";
                $emphasis = Insight::EMPHASIS_LOW;
                $can_insert = true;
            }

            if($can_insert) {
                $this->insight_dao->insertInsightDeprecated('minutes_viewed_high'.$video->id, $instance->id,
                $simplified_post_date, $headline, $insight_text, $filename, $emphasis, serialize(array($video,$chart)));
            }
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

    private function generateBaselines($instance) {
        $video_dao = DAOFactory::getDAO('VideoDAO');
        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $username = $instance->network_username;

        $average_mins_viewed_all_time = $video_dao->getAverageMinutesViewed($username, 'youtube');
        $baseline_dao->insertInsightBaseline('avg_minutes_viewed_all_time', $instance->id,
        $average_mins_viewed_all_time, date('Y-m-d'));

        $average_mins_viewed_90 = $video_dao->getAverageMinutesViewed($username, 'youtube', 90);
        $baseline_dao->insertInsightBaseline('avg_minutes_viewed_90', $instance->id, $average_mins_viewed_90,
        date('Y-m-d'));

        $average_mins_viewed_month = $video_dao->getAverageMinutesViewed($username, 'youtube', 30);
        $baseline_dao->insertInsightBaseline('avg_minutes_viewed_month', $instance->id, $average_mins_viewed_month,
        date('Y-m-d'));

        $all_time_mins_viewed = $video_dao->getHighestMinutesViewed($username, 'youtube', null);
        $baseline_dao->insertInsightBaseline('all_time_mins_viewed_high', $instance->id, $all_time_mins_viewed,
        date('Y-m-d'));

        $year_mins_viewed = $video_dao->getHighestMinutesViewed($username, 'youtube', 365);
        $baseline_dao->insertInsightBaseline('year_mins_viewed_high', $instance->id, $year_mins_viewed,
        date('Y-m-d'));

        $ninety_mins_viewed = $video_dao->getHighestMinutesViewed($username, 'youtube', 90);
        $baseline_dao->insertInsightBaseline('90_mins_viewed_high', $instance->id, $ninety_mins_viewed,
        date('Y-m-d'));
    }

}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('MinutesViewedInsight');
