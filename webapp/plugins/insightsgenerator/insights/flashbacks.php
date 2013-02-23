<?php
/*
 Plugin Name: Flashback
 Description: The most popular posts you published on this day in years past.
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/flashbacks.php
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

class FlashbackInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $existing_insight = $this->insight_dao->getInsight("posts_on_this_day_popular_flashback", $instance->id,
        $this->insight_date);
        if (!isset($existing_insight)) {
            //Generate flashback post list
            $post_dao = DAOFactory::getDAO('PostDAO');
            $flashback_posts = $post_dao->getOnThisDayFlashbackPosts($instance->network_user_id, $instance->network,
            $this->insight_date);
            $most_popular_post = null;
            $most_responses = 0;
            if (isset($flashback_posts) && sizeof($flashback_posts) > 0 ) {
                foreach ($flashback_posts as $post) {
                    $total_responses = $post->reply_count_cache + $post->all_retweets + $post->favlike_count_cache;
                    if ($total_responses > 0 && $total_responses > $most_responses) {
                        $most_popular_post = $post;
                        $most_responses = $total_responses;
                    }
                }
                if (isset($most_popular_post)) {
                    $post_year = date(date( 'Y' , strtotime($most_popular_post->pub_date)));
                    $current_year = date('Y');
                    $number_of_years_ago = $current_year - $post_year;
                    $plural = ($number_of_years_ago > 1 )?'s':'';

                    $insight_text = "$this->username's most popular post <strong>$number_of_years_ago year$plural ago".
                    "</strong> today was:";
                    $this->insight_dao->insertInsight("posts_on_this_day_popular_flashback", $instance->id,
                    $this->insight_date, "Time machine:", $insight_text, basename(__FILE__, ".php"),
                    Insight::EMPHASIS_LOW, serialize($most_popular_post));
                }
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('FlashbackInsight');
