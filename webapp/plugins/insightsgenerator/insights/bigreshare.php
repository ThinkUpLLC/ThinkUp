<?php
/*
 Plugin Name: Big Reshare
 Description: Retweet or reshare by someone with more followers than you have.
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/bigreshare.php
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

class BigReshareInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $post_dao = DAOFactory::getDAO('PostDAO');
        $user_dao = DAOFactory::getDAO('UserDAO');
        $service_user = $user_dao->getDetails($instance->network_user_id, $instance->network);

        foreach ($last_week_of_posts as $post) {
            $big_reshares = $post_dao->getRetweetsByAuthorsOverFollowerCount($post->post_id, $instance->network,
            $service_user->follower_count);

            if (isset($big_reshares) && sizeof($big_reshares) > 0 ) {
                if (!isset($config)) {
                    $config = Config::getInstance();
                }
                $post_link = '<a href="'.$config->getValue('site_root_path'). 'post/?t='.$post->post_id.'&n='.
                $post->network.'&v=fwds">';

                if (sizeof($big_reshares) > 1) {
                    $notification_text = "People with lots of followers ".$this->terms->getVerb('shared')." "
                    .$post_link."$this->username's post</a>.";
                } else {
                    $follower_count_multiple =
                    intval(($big_reshares[0]->follower_count) / $service_user->follower_count);
                    if ($follower_count_multiple > 1 ) {
                        $notification_text = "Someone with <strong>".$follower_count_multiple.
                        "x</strong> more followers than $this->username ".$this->terms->getVerb('shared')." "
                        .$post_link."this post</a>.";
                    } else {
                        $notification_text = "Someone with lots of followers ".$this->terms->getVerb('shared')." "
                        .$post_link."$this->username's post</a>.";
                    }
                }
                //Replace each big resharer's bio line with the text of the post
                foreach ($big_reshares as $sharer) {
                    $sharer->description = '"'.$post->post_text.'"';
                }
                $simplified_post_date = date('Y-m-d', strtotime($post->pub_date));
                $this->insight_dao->insertInsight("big_reshare_".$post->id, $instance->id,
                $simplified_post_date, "Big reshare!", $notification_text, basename(__FILE__, ".php"),
                Insight::EMPHASIS_HIGH, serialize($big_reshares));
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('BigReshareInsight');
