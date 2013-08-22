<?php
/*
 Plugin Name:  Strong Opinions
 Description: Videos with a increase in the percentage of likes or dislikes
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/strongopinions.php
 *
 * Copyright (c) 2013 Aaron Kalair
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
 * @copyright 2013 Aaron Kalair
 */

class StrongOpinionsInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
        self::generateInsightBaselines($instance);

        $video_dao = DAOFactory::getDAO('VideoDAO');
        $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');

        $all_time_likes = $baseline_dao->getInsightBaseline('avg_like_percentage_all_time',
        $instance->id, date('Y-m-d'));
        $all_time_dislikes = $baseline_dao->getInsightBaseline('avg_dislike_percentage_all_time', $instance->id,
        date('Y-m-d'));
        $month_likes = $baseline_dao->getInsightBaseline('avg_like_percentage_1_month', $instance->id, date('Y-m-d'));
        $month_dislikes = $baseline_dao->getInsightBaseline('avg_dislike_percentage_1_month', $instance->id,
        date('Y-m-d'));
        $all_time_like_high = $baseline_dao->getInsightBaseline('high_like_percentage_all_time', $instance->id,
        date('Y-m-d'));
        $all_time_dislike_high = $baseline_dao->getInsightBaseline('high_dislike_percentage_all_time', $instance->id,
        date('Y-m-d'));
        $filename = basename(__FILE__, ".php");

        foreach ($last_week_of_posts as $post) {

            if($post->network == 'youtube') {
                $video = $video_dao->getVideoByID($post->post_id, 'youtube');
                $simplified_post_date = date('Y-m-d', strtotime($video->pub_date));
            } else {
                break;
            }

            // Get the average baselines
            $total_likes_and_dislikes = $video->likes + $video->dislikes;
            $percent_likes = ($video->likes / $total_likes_and_dislikes ) * 100;
            $percent_dislikes = ($video->dislikes / $total_likes_and_dislikes ) * 100;
            $percent_like_change_year = round($percent_likes - $all_time_likes->value, 2);
            $percent_like_change_month = round($percent_likes - $month_likes->value, 2);
            $percent_dislike_change_year = round($percent_dislikes - $all_time_dislikes->value, 2);
            $percent_dislike_change_month = round($percent_dislikes - $month_dislikes->value, 2);
            $emphasis = null;
            $prefix = 'Your fans have spoken:';
            $text = $video->post_text." got ";
            $can_insert = false;

            // Increases in like percentages
            if($percent_like_change_month >= 50 || $percent_like_change_year >= 50)  {
                if($percent_like_change_year >= 50) {
                    $text .= $percent_like_change_year."% more likes than your all time average";
                } else {
                    $text .= $percent_like_change_month."% more likes than your monthly average";
                }
                $emphasis = Insight::EMPHASIS_HIGH;
                $can_insert = true;
            } elseif($percent_like_change_month >= 25 || $percent_like_change_year >= 25) {
                if($percent_like_change_year >= 25) {
                    $text .= $percent_like_change_year."% more likes than your all time average";
                } else {
                    $text .= $percent_like_change_month."% more likes than your monthly average";
                }
                $emphasis = Insight::EMPHASIS_MED;
                $can_insert = true;
            } elseif($percent_like_change_month >= 10 || $percent_like_change_year >= 10) {
                if($percent_like_change_year >= 10) {
                    $text .= $percent_like_change_year."% more likes than your all time average";
                } else {
                    $text .= $percent_like_change_month."% more likes than your monthly average";
                }
                $can_insert = true;
                $emphasis = Insight::EMPHASIS_LOW;
            }

            // Increases in dislike percentages
            if($percent_dislike_change_month >= 50 || $percent_dislike_change_year >= 50)  {
                if($percent_dislike_change_year >= 50) {
                    $text .= $percent_dislike_change_year."% more dislikes than your all time average";
                } else {
                    $text .= $percent_dislike_change_month."% more dislikes than your monthly average";
                }
                $emphasis = Insight::EMPHASIS_HIGH;
                $can_insert = true;
            } elseif($percent_dislike_change_month >= 25 || $percent_dislike_change_year >= 25) {
                if($percent_dislike_change_year >= 25) {
                    $text .= $percent_dislike_change_year."% more dislikes than your all time average";
                } else {
                    $text .= $percent_dislike_change_month."% more dislikes than your monthly average";
                }
                $emphasis = Insight::EMPHASIS_MED;
                $can_insert = true;
            } elseif($percent_dislike_change_month >= 10 || $percent_dislike_change_year >= 10) {
                if($percent_dislike_change_year >= 10) {
                    $text .= $percent_dislike_change_year."% more dislikes than your all time average";
                } else {
                    $text .= $percent_dislike_change_month."% more dislikes than your monthly average";
                }
                $can_insert = true;
                $emphasis = Insight::EMPHASIS_LOW;
            }

            if($can_insert) {
                $this->insight_dao->insertInsight("strong_opinions".$video->id, $instance->id, $simplified_post_date,
                $prefix, $text, $filename, $emphasis, serialize($video));
            }

            $text = null;
            // All time highs and lows
            if($percent_likes >= $all_time_like_high->value) {
                $text = $percent_likes."% of people liked ".$video->post_text." a new all time high";
                $emphasis = Insight::EMPHASIS_HIGH;
            } elseif($percent_dislikes >= $all_time_dislike_high->value) {
                $text = $percent_dislikes."% of people disliked ".$video->post_text." a new all time high";
                $emphasis = Insight::EMPHASIS_HIGH;
            }

            if($text != null) {
                $this->insight_dao->insertInsight("strong_opinions_high".$video->id, $instance->id,
                $simplified_post_date, $prefix, $text, $filename, $emphasis, serialize($video));
            }
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }


    /**
     * Calculate and store insight baselines
     * @param Instance $instance
     * @return void
     */
    private function generateInsightBaselines($instance) {
        $video_dao = DAOFactory::getDAO('VideoDAO');
        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');

        // Average for the year
        $all_time_likes = $video_dao->getAverageLikePercentage($instance->network_username, 'youtube');
        $all_time_dislikes = $video_dao->getAverageDislikePercentage($instance->network_username, 'youtube');
        // Average for the month
        $month_likes = $video_dao->getAverageLikePercentage($instance->network_username, 'youtube', 31);
        $month_dislikes = $video_dao->getAverageDislikePercentage($instance->network_username, 'youtube', 31);
        // Like and Dislike Highs
        $likes_high = $video_dao->getHighestLikePercentage($instance->network_username, 'youtube', null);
        $dislikes_high = $video_dao->getHighestDislikePercentage($instance->network_username, 'youtube', null);

        $insight_baseline_dao->insertInsightBaseline('avg_like_percentage_all_time', $instance->id,
        $all_time_likes, date('Y-m-d'));
        $this->logger->logSuccess("Averaged $all_time_likes % likes in the last year", __METHOD__.','.__LINE__);

        $insight_baseline_dao->insertInsightBaseline('avg_like_percentage_1_month', $instance->id,
        $month_likes, date('Y-m-d'));
        $this->logger->logSuccess("Averaged $month_likes % likes in the last month", __METHOD__.','.__LINE__);

        $insight_baseline_dao->insertInsightBaseline('avg_dislike_percentage_all_time', $instance->id,
        $all_time_dislikes, date('Y-m-d'));
        $this->logger->logSuccess("Averaged $all_time_dislikes % dislikes in the last year", __METHOD__.','.__LINE__);

        $insight_baseline_dao->insertInsightBaseline('avg_dislike_percentage_1_month', $instance->id,
        $month_dislikes, date('Y-m-d'));
        $this->logger->logSuccess("Averaged $month_dislikes % dislikes in the last month", __METHOD__.','.__LINE__);

        $insight_baseline_dao->insertInsightBaseline('high_like_percentage_all_time', $instance->id,
        $likes_high, date('Y-m-d'));
        $this->logger->logSuccess("Averaged $likes_high % dislikes in the last month", __METHOD__.','.__LINE__);

        $insight_baseline_dao->insertInsightBaseline('high_dislike_percentage_all_time', $instance->id,
        $dislikes_high, date('Y-m-d'));
        $this->logger->logSuccess("Averaged $dislikes_high % dislikes in the last month", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('StrongOpinionsInsight');
