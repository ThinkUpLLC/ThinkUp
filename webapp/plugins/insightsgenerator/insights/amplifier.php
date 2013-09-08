<?php
/*
 Plugin Name: Amplifier
 Description: How many more users a message has reached due to your reshare or retweet.
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/Amplifier.pho
 *
 * Copyright (c) 2012-2013 Gina Trapani
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
 * @copyright 2012-2013 Gina Trapani
 */

class AmplifierInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $filename = basename(__FILE__, ".php");

        foreach ($last_week_of_posts as $post) {
            //if post was a retweet, check if insight exists
            if ($post->in_retweet_of_post_id != null && $post->in_rt_of_user_id != null) {
                $simplified_post_date = date('Y-m-d', strtotime($post->pub_date));

                //if insight doesn't exist fetch user details of original author and instance
                if (self::shouldGenerateInsight('amplifier_'.$post->id, $instance,
                $insight_date=$simplified_post_date)) {
                    if (!isset($user_dao)) {
                        $user_dao = DAOFactory::getDAO('UserDAO');
                    }
                    if (!isset($instance_user)) {
                        $instance_user = $user_dao->getDetails($post->author_user_id, $post->network);
                    }
                    $retweeted_user = $user_dao->getDetails($post->in_rt_of_user_id, $post->network);
                    //if user exists and has fewer followers than instance user, build and insert insight
                    if (isset($retweeted_user) && $retweeted_user->follower_count < $instance_user->follower_count) {
                        $add_audience = number_format($instance_user->follower_count - $retweeted_user->follower_count);
                        $insight_text = "$this->username broadcast this post to <strong>$add_audience</strong> ".
                        "more people than its author originally reached.";

                        $this->insight_dao->insertInsight('amplifier_'.$post->id, $instance->id,
                        $simplified_post_date, "Amplifier:", $insight_text, $filename, Insight::EMPHASIS_LOW,
                        serialize($post));
                    }
                }
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('AmplifierInsight');
