<?php
/*
 Plugin Name: Subscriber Change
 Description: How videos affected your subscriber count
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/subscriberchange.php
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

class SubscriberChangeInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_weeks_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
        $filename = basename(__FILE__, ".php");
        $insight_text = '';

        $video_dao = DAOFactory::getDAO('VideoDAO');

        // Get the users subscriber count for comparing with later
        $user_dao = DAOFactory::getDAO('UserDAO');
        $user = $user_dao->getDetails($instance->network_user_id, 'youtube');
        $subscriber_count = $user->follower_count;

        foreach($last_weeks_posts as $post) {

            if($post->network == 'youtube') {
                $video =  $video_dao->getVideoByID($post->post_id, 'youtube');
                $simplified_post_date = date('Y-m-d', strtotime($video->pub_date));
            } else {
                break;
            }

            $gain_or_loss = $video->subscribers_gained - $video->subscribers_lost;
            // if we lost subscribers then we will be doing subscriber_count minus a negative number, so adding
            $total_before_video = $subscriber_count - $gain_or_loss;
            $percent_change = round((abs($gain_or_loss) / $total_before_video) * 100,2);
            $verb = ($gain_or_loss < 0) ? ' decreased' : ' increased';
            $headline = $video->post_text . $verb . " " . $instance->network_username . "'s ";
            $headline .= "subscriber count by <strong>".$percent_change."%</strong>.";
            $insight_text = "<a href=http://plus.google.com/$instance->network_user_id>$instance->network_username</a>'s ";
            $insight_text .= "video <a href=http://www.youtube.com/watch?v=$video->post_id>$video->post_text</a> ";
            $insight_text .= "left an impression on $gain_or_loss subscribers.";
            
            $subscriber_count = intval($subscriber_count);
            $total_before_video = intval($total_before_video);
            $rows = $video_dao->getNetSubscriberChange($instance->network_username, 'youtube', 10);
            $chart = VideoMySQLDAO::getHotVideosVisualizationData($rows, 'Subscriber Change');

            if($percent_change >=50) {
                $this->insight_dao->insertInsightDeprecated('subscriber_change'.$video->id, $instance->id,
                $simplified_post_date, $headline, $insight_text, $filename, Insight::EMPHASIS_HIGH,
                serialize(array($chart,$video)));
            } elseif($percent_change >=25 ) {
                $this->insight_dao->insertInsightDeprecated('subscriber_change'.$video->id, $instance->id,
                $simplified_post_date, $headline, $insight_text, $filename, Insight::EMPHASIS_MED,
                serialize(array($chart,$video)));
            } elseif($percent_change >=10 ) {
                $this->insight_dao->insertInsightDeprecated('subscriber_change'.$video->id, $instance->id,
                $simplified_post_date, $headline, $insight_text, $filename, Insight::EMPHASIS_LOW,
                serialize(array($chart,$video)));
            }
        }
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('SubscriberChangeInsight');
