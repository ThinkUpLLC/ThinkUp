<?php
/*
 Plugin Name: Top Of Posts For Facebook
 Description: Top 3 most shared posts of last 7 and 30 days.
 When: Sundays and 1st of the month
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/topofpostsfacebook.php
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
 *
 * TopOfPostsFacebook (name of file)
 *
 * Description of what this class does
 *
 * Copyright (c) 2014 Anna Shkerina
 *
 * @author Anna Shkerina blond00792@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Anna Shkerina
 */

class TopOfPostsFacebookInsight extends InsightPluginParent implements InsightPlugin {

 public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $since_date = date("Y-m-d");

        $insight_date = new DateTime();
        $insight_day_of_week = (int) $insight_date->format('w');
        $insight_day_of_month = (int) $insight_date->format('j');

        $filename = basename(__FILE__, ".php");

        if ($insight_day_of_month == 1) { //it's the first day of the month
            // Past 30 days
            if (self::shouldGenerateInsight('top_of_posts_30_days', $instance, $insight_date=$since_date, 
            		$regenerate_existing_insight=true)) {
                $post_dao = DAOFactory::getDAO('PostDAO');
                $posts = $post_dao->getMostSharedPostsOfTheLastDays($instance->network_user_id,
                $instance->network, 30);
                if (isset($posts) && sizeof($posts) > 0 ) {
                    $this->insight_dao->insertInsightDeprecated("top_of_posts_30_days", $instance->id,
                    $since_date, "Top posts:", "Most shared $this->username's " 
                    .$this->terms->getNoun('post', InsightTerms::PLURAL)." of the last 30 days: ",
                    $filename, Insight::EMPHASIS_LOW, serialize($posts));
                }
            }
        } else if ($insight_day_of_week == 2) { //it's Sunday
            // Past 7 days
            if (self::shouldGenerateInsight('top_of_posts_7_days', $instance, $insight_date=$since_date, 
            		$regenerate_existing_insight=true)) {
            	$post_dao = DAOFactory::getDAO('PostDAO');
                $posts = $post_dao->getMostSharedPostsOfTheLastDays($instance->network_user_id,
                $instance->network, 7);
                echo "posts=".Utils::varDumpToString($posts);
                if (isset($posts) && sizeof($posts) > 0 ) {
                    $this->insight_dao->insertInsightDeprecated("top_of_posts_7_days", $instance->id,
                    $since_date, "Top posts:", "Most shared $this->username's "
                    .$this->terms->getNoun('post', InsightTerms::PLURAL)." of the last 7 days: ",
                    $filename, Insight::EMPHASIS_LOW, serialize($posts));
                }
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('TopOfPostsFacebookInsight');

