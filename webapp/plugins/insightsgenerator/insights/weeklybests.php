<?php
/*
 Plugin Name: Weekly Bests
 Description: Your most popular posts from last week.
 When: Thursdays
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/weeklybests.php
 *
 * Copyright (c) 2013 Nilaksh Das, Gina Trapani
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
 * @copyright 2013 Nilaksh Das, Gina Trapani
 * @author Nilaksh Das <nilakshdas [at] gmail [dot] com>
 */

class WeeklyBestsInsight extends InsightPluginParent implements InsightPlugin {
    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        if (self::shouldGenerateInsight('weekly_best', $instance, $insight_date='today',
        $regenerate_existing_insight=false, $day_of_week=4, count($last_week_of_posts))) {
            $most_popular_post = null;
            $best_popularity_params = array('index' => 0, 'reply' => 0, 'retweet' => 0, 'like' => 0);
            $insight_text = '';

            foreach ($last_week_of_posts as $post) {
                $reply_count = $post->reply_count_cache;
                $retweet_count = $post->retweet_count_cache;
                $fav_count = $post->favlike_count_cache;

                $popularity_index = (5 * $reply_count) + (3 * $retweet_count) + (2 * $fav_count);

                if ($popularity_index > $best_popularity_params['index']) {
                    $best_popularity_params['index'] = $popularity_index;
                    $best_popularity_params['reply'] = $reply_count;
                    $best_popularity_params['retweet'] = $retweet_count;
                    $best_popularity_params['like'] = $fav_count;

                    $most_popular_post = $post;
                }
            }

            if (isset($most_popular_post)) {
                $headline = $this->username."'s most popular ".$this->terms->getNoun('post')
                ." from last week got ";
                foreach ($best_popularity_params as $key => $value) {
                    if ($value && $key != 'index') {
                        $headline .= "<strong>".$value." ".$this->terms->getNoun($key, ($value > 1))."</strong>, ";
                    }
                }

                $headline = rtrim($headline, ", ");
                $headline .= '.';
                if (!(strpos($headline, ',') === false)) {
                    $headline = substr_replace($headline, " and",
                    strpos($headline, strrchr($headline, ',')), 1);
                }

                $simplified_post_date = date('Y-m-d', strtotime($most_popular_post->pub_date));
                $hot_posts_data = $this->insight_dao->getPreCachedInsightData('PostMySQLDAO::getHotPosts',
                $instance->id, $simplified_post_date);

                if (isset($hot_posts_data)) {
                    $this->insight_dao->insertInsightDeprecated("weekly_best", $instance->id, $this->insight_date,
                    $headline, $insight_text, basename(__FILE__, ".php"),
                    Insight::EMPHASIS_LOW, serialize(array($most_popular_post, $hot_posts_data)));
                }
            }
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('WeeklyBestsInsight');
