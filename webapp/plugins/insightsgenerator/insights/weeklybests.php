<?php
/*
 Plugin Name: Weekly and Monthly Best Posts
 Description: Your most popular posts from last week and last month.
 When: Thursdays for Twitter, Sunday otherwise, and 1st of the month
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
    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        //Monthly
        $did_monthly = false;
        $should_generate_insight = $this->shouldGenerateMonthlyInsight( $slug = 'monthly_best', $instance,
            $this->insight_date, $regenerate_existing_insight=false, $day_of_month = 1);

        if ($should_generate_insight) {
            $most_popular_post = null;
            $best_popularity_params = array('index' => 0, 'reply' => 0, 'retweet' => 0, 'like' => 0);
            $insight_text = '';

            $post_dao = DAOFactory::getDAO('PostDAO');
            //There's probably a better way to get the number of days in last month
            $last_month_time = strtotime('first day of last month');
            $last_month_year = date('Y', $last_month_time);
            $last_month_month = date('m', $last_month_time);
            $days_of_posts_to_retrieve = TimeHelper::getDaysInMonth( $last_month_year, $last_month_month);

            $last_months_posts = $post_dao->getAllPostsByUsernameOrderedBy($instance->network_username,
                $instance->network, $count=0, $order_by="pub_date", $in_last_x_days = $days_of_posts_to_retrieve,
                $iterator = false, $is_public = false);

            foreach ($last_months_posts as $post) {
                if($post->network == 'instagram') {
                    $photo_dao = DAOFactory::getDAO('PhotoDAO');
                    $post =$photo_dao->getPhoto($post->post_id, 'instagram');
                }
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
                $my_insight = new Insight();

                $my_insight->headline = $this->getVariableCopy(array(
                    'Happy %new_month!',
                    'Welcome to %new_month!'
                ), array('new_month'=> date('F')));

                $my_insight->text = $this->getVariableCopy(array(
                    "This was %username's most popular %post of %last_month %month_year.",
                    "Behold, %username's most popular %post of %last_month %month_year."
                    ),
                    array(
                    'last_month'=> date('F', $last_month_time),
                    'month_year'=> date('Y', $last_month_time))
                );

                $my_insight->slug = 'monthly_best'; //slug to label this insight's content
                $my_insight->instance_id = $instance->id;
                $my_insight->date = $this->insight_date; //date is often this or $simplified_post_date
                $my_insight->filename = basename(__FILE__, ".php");
                $my_insight->emphasis = Insight::EMPHASIS_LOW;
                $my_insight->setPosts(array($most_popular_post));

                $this->insight_dao->insertInsight($my_insight);

                $did_monthly = true;
            }
        }

        //Weekly
        if ($instance->network == 'twitter') {
            $day_of_week = 4;
        } else {
            $day_of_week = 0;
        }
        $should_generate_insight = self::shouldGenerateWeeklyInsight('weekly_best', $instance, $insight_date='today',
            $regenerate_existing_insight=false, $day_of_week=$day_of_week, count($last_week_of_posts));

        if (!$did_monthly && $should_generate_insight) {
            $most_popular_post = null;
            $best_popularity_params = array('index' => 0, 'reply' => 0, 'retweet' => 0, 'like' => 0);
            $insight_text = '';

            foreach ($last_week_of_posts as $post) {
                if($post->network == 'instagram') {
                    $photo_dao = DAOFactory::getDAO('PhotoDAO');
                    $post =$photo_dao->getPhoto($post->post_id, 'instagram');
                }
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
                $headline = "This was $this->username's most popular ".$this->terms->getNoun('post') . " of last week.";
                $insight_text = $this->username." earned ";
                foreach ($best_popularity_params as $key => $value) {
                    if ($value && $key != 'index') {
                        $insight_text .= "<strong>".$value." ".$this->terms->getNoun($key, ($value > 1))."</strong>, ";
                    }
                }

                $insight_text = rtrim($insight_text, ", ");
                $insight_text .= '.';
                if (!(strpos($insight_text, ',') === false)) {
                    $insight_text = substr_replace($insight_text, " and",
                    strpos($insight_text, strrchr($insight_text, ',')), 1);
                }

                $simplified_post_date = date('Y-m-d', strtotime($most_popular_post->pub_date));

                $my_insight = new Insight();

                $my_insight->slug = 'weekly_best'; //slug to label this insight's content
                $my_insight->instance_id = $instance->id;
                $my_insight->date = $this->insight_date; //date is often this or $simplified_post_date
                $my_insight->headline = $headline; // or just set a string like 'Ohai';
                $my_insight->text = $insight_text; // or just set a strong like "Greetings humans";
                $my_insight->header_image = $header_image;
                $my_insight->filename = basename(__FILE__, ".php");
                $my_insight->emphasis = Insight::EMPHASIS_LOW;
                $my_insight->setPosts(array($most_popular_post));

                $this->insight_dao->insertInsight($my_insight);
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('WeeklyBestsInsight');
