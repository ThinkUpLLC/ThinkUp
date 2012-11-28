<?php
/*
 Plugin Name: Map Available
 Description: Map of post replies (requires Maps plugin).
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/map.php
 *
 * Copyright (c) 2012 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
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
 * @copyright 2012 Gina Trapani
 * @author Gina Trapani <ginatrapani [at] gmail [dot] com>
 */

class MapInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
        $filename = basename(__FILE__, ".php");

        foreach ($last_week_of_posts as $post) {
            $simplified_post_date = date('Y-m-d', strtotime($post->pub_date));
            // Map insight: If not a reply or retweet and geoencoded, show the map in the stream
            if (!isset($post->in_reply_to_user_id) && !isset($post->in_reply_to_post_id)
            && !isset($post->in_retweet_of_post_id) && $post->reply_count_cache > 5) {
                $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
                $options = $plugin_option_dao->getOptionsHash('geoencoder', true);
                if (isset($options['gmaps_api_key']->option_value) && $post->is_geo_encoded == 1) {
                    //Get post's replies and loop through them to make sure at least 5 are indeed geoencoded
                    $post_dao = DAOFactory::getDAO('PostDAO');
                    $post_replies = $post_dao->getRepliesToPost($post->post_id, $post->network);
                    $total_geoencoded_replies = 0;
                    foreach ($post_replies as $reply) {
                        if ($reply->is_geo_encoded == 1) {
                            $total_geoencoded_replies++;
                        }
                    }
                    if ($total_geoencoded_replies > 4) {
                        $this->insight_dao->insertInsight('geoencoded_replies', $instance->id, $simplified_post_date,
                        "Going global!", "Your post got responses from locations all over the map.",
                        $filename, Insight::EMPHASIS_LOW, serialize($post));
                    }
                }
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('MapInsight');
