<?php
/*
 Plugin Name: Congrats Count (Congrats-o-meter)
 Description: How often you've congratulated someone in the past month, and for what.
 When: 16th of the month
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/congratscount.php
 *
 * Copyright (c) 2014 Chris Moyer
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
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer <chris [at] inarow [dot] net>
 */
class CongratsCountInsight extends CriteriaMatchInsightPluginParent implements InsightPlugin {
    public function shouldGenerate(Instance $instance, $last_week_of_posts) {
        return $this->shouldGenerateMonthlyInsight($this->getSlug(), $instance, $insight_date='today',
            $regenerate_existing_insight=false, $day_of_month=16, count($last_week_of_posts),
            array('youtube','foursquare'));
    }

    public function getSlug() {
        return 'congratscount';
    }

    public function getNumberOfDaysNeeded() {
        return date('t', strtotime('-1 month'));
    }

    public function postMatchesCriteria(Post $post, Instance $instance) {
        if ($post->in_reply_to_user_id == 0 || $post->in_reply_to_user_id == $instance->network_user_id
           || $post->in_reply_to_post_id == 0) {
            return false;
        }
        $text = strtolower($post->post_text);
        return preg_match('/(\W|^)(maz[ae]l tov|congrat.*)(\W|$)/', $text);
    }

    public function getInsightForCounts($this_period_count, $last_period_count, $instance, $matching_posts) {
        $insight = null;
        if ($this_period_count > 0) {
                $unique_friends = array();
                $posts_to_show = array();
                $post_dao = DAOFactory::getDAO('PostDAO');
                foreach ($matching_posts as $post) {
                    if (!in_array($post->in_reply_to_user_id, $unique_friends)) {
                        $unique_friends[] = $post->in_reply_to_user_id;
                    }
                    $replied_post = $post_dao->getPost($post->in_reply_to_post_id, $instance->network);
                    if ($replied_post) {
                        $posts_to_show[] = $replied_post;
                    }
                }

                $insight = new Insight();
                $insight->slug = $this->getSlug();
                $insight->instance_id = $instance->id;
                $insight->date = $this->insight_date;
                $insight->headline = $this->getVariableCopy(array(
                    "%username's friend".(count($unique_friends)==1?'':'s')." had some great news!",
                    'Congratulations on the congrats, %username!',
                    '%network is for announcing good news!',
                ), array('network' => ucfirst($instance->network)));
                $posts = $this_period_count > 1 ? '%posts' : '%post';
                $these = $this_period_count > 1 ? 'these' : 'this';
                $are = $this_period_count > 1 ? 'are' : 'is';
                $people_term = count($unique_friends) > 1 ? 'people' : 'person';
                $insight->text = $this->getVariableCopy(array(
                    "%username congratulated %total_friends $people_term in the past month for $these $posts.",
                    "%total_posts $posts inspired %username to congratulate someone this past month.",
                    "Here $are the $posts that inspired %username to congratulate people this month.",
                ), array(
                    'network' => ucfirst($instance->network),
                    'total_posts'=>$this_period_count,
                    'total_friends'=>count($unique_friends)
                ));

                $insight->filename = basename(__FILE__, ".php");
                if (count($posts_to_show)) {
                    $insight->setPosts($posts_to_show);
                }
        }
        return $insight;
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('CongratsCountInsight');
