<?php
/*
 Plugin Name:  Split Opinions
 Description: Hightlights videos that split the audience with 40 - 60 % of likes or dislikes
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/splitopinions.php
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

class SplitOpinionsInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $video_dao = DAOFactory::getDAO('VideoDAO');
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

            // If the video splits opinion in the range of 60/40 likes / dislikes or 40/60 likes / dislikes
            if($percent_likes >= 40 && $percent_likes <= 60) {
                $prefix = 'Split Opinion:';
                $text = 'Your video '.$video->post_text." really touched a nerve ".$percent_likes.'% of people liked ';
                $text .= 'it and '.$percent_dislikes.'% disliked it.';
                $this->insight_dao->insertInsight("split_opinions".$video->id, $instance->id, $simplified_post_date,
                $prefix, $text, $filename, 1, serialize($video));
            }
        }
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('SplitOpinionsInsight');
