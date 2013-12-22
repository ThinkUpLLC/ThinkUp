<?php
/*
 Plugin Name:  Split Opinions
 Description: Hightlights videos that split the audience with a like or dislike percentage of 40 - 60 percent
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
            $percent_likes = round(($video->likes / $total_likes_and_dislikes ) * 100, 2);
            $percent_dislikes = round(($video->dislikes / $total_likes_and_dislikes ) * 100, 2);

            // If the video splits opinion in the range of 60/40 likes / dislikes or 40/60 likes / dislikes
            if($percent_likes >= 40 && $percent_likes <= 60) {
                $prefix = 'Split opinion:';
                $text = "<a href=http://plus.google.com/$instance->network_user_id/about>".$instance->network_username;
                $text .= '</a>\'s video ';
                $text .= "<a href=http://www.youtube.com/watch?v=$post->post_id>".$video->post_text."</a> really ";
                $text .= "touched a nerve! $percent_likes"."% of people liked it and ".$percent_dislikes."% disliked ";
                $text .= "it.";
                $this->insight_dao->insertInsightDeprecated("split_opinions".$video->id, $instance->id, $simplified_post_date,
                $prefix, $text, $filename, 1, serialize($video));
            }
        }
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('SplitOpinionsInsight');
