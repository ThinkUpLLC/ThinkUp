<?php
/*
 Plugin Name: Biggest Fans
 Description: Who has liked your posts the most over the last 7 and 30 days.
 When: Sundays and 1st of the month
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/biggestfans.php
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

class BiggestFansInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $since_date = date("Y-m-d");
        $filename = basename(__FILE__, ".php");

        if (self::shouldGenerateMonthlyInsight('biggest_fans_last_30_days', $instance, $insight_date=$since_date,
        $regenerate_existing_insight=false,  $day_of_month = 1)) { //it's the first day of the month
            // Past 30 days
            $fav_dao = DAOFactory::getDAO('FavoritePostDAO');
            $fans = $fav_dao->getUsersWhoFavoritedMostOfYourPosts($instance->network_user_id,
            $instance->network, 30);
            if (isset($fans) && sizeof($fans) > 0 ) {
                $my_insight = new Insight();
                //REQUIRED: Set the insight's required attributes
                $my_insight->slug = 'biggest_fans_last_30_days'; //slug to label this insight's content
                $my_insight->instance_id = $instance->id;
                $my_insight->date = $since_date; //date is often this or $simplified_post_date
                $my_insight->headline = "These were $this->username's biggest fans last month.";
                $my_insight->text = "They ".$this->terms->getVerb('liked').
                " $this->username's " .$this->terms->getNoun('post', InsightTerms::PLURAL).
                " the most over the last 30 days."; // or just set a string like 'Ohai';
                $my_insight->filename = basename(__FILE__, ".php");
                $my_insight->emphasis = Insight::EMPHASIS_MED;
                $my_insight->setPeople($fans);
                $this->insight_dao->insertInsight($my_insight);
            }
        }

        if (self::shouldGenerateWeeklyInsight('biggest_fans_last_7_days', $instance, $insight_date=$since_date,
        $regenerate_existing_insight=false, $day_of_week = 0 )) { //it's Sunday
            // Past 7 days
            $fav_dao = DAOFactory::getDAO('FavoritePostDAO');
            $fans = $fav_dao->getUsersWhoFavoritedMostOfYourPosts($instance->network_user_id,
            $instance->network, 7);
            if (isset($fans) && sizeof($fans) > 0 ) {
                $my_insight = new Insight();
                //REQUIRED: Set the insight's required attributes
                $my_insight->slug = 'biggest_fans_last_7_days'; //slug to label this insight's content
                $my_insight->instance_id = $instance->id;
                $my_insight->date = $since_date; //date is often this or $simplified_post_date
                $my_insight->headline = "Last week, these were $this->username's biggest admirers.";
                $my_insight->text = "Here's who " .$this->terms->getVerb('liked')." $this->username's ".
                    $this->terms->getNoun('post', InsightTerms::PLURAL)." most over the last week.";
                $my_insight->filename = basename(__FILE__, ".php");
                $my_insight->setPeople($fans);
                $this->insight_dao->insertInsight($my_insight);
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('BiggestFansInsight');
