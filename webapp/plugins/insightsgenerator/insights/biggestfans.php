<?php
/*
 Plugin Name: Biggest Fans
 Description: Who has liked your posts the most over the last 7 and 30 days.
 When: Sundays for Twitter, Wednesdays otherwise, and 1st of the month for Twitter, 2nd otherwise
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

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $since_date = date("Y-m-d");
        $filename = basename(__FILE__, ".php");

        if ($instance->network == 'twitter') {
            $day_of_month = 1;
        } else {
            $day_of_month = 2;
        }
        $should_generate_insight = self::shouldGenerateMonthlyInsight('biggest_fans_last_30_days', $instance,
            $insight_date=$since_date, $regenerate_existing_insight=false, $day_of_month = $day_of_month);

        $prefix = $instance->network == 'twitter' ? '@' : '';
        if ($should_generate_insight) { //it's the right day of the month
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
                if (count($fans) == 1) {
                    $my_insight->headline = $prefix.$fans[0]->username
                        . " was $this->username's biggest fan last month.";
                    if ($fans[0]->avatar) {
                        $my_insight->header_image = $fans[0]->avatar;
                    }
                    $my_insight->text = $prefix.$fans[0]->username." ".$this->terms->getVerb('liked').
                        " $this->username's " .$this->terms->getNoun('post', InsightTerms::PLURAL).
                        " the most over the last 30 days.";
                } else {
                    $my_insight->headline = "These were $this->username's biggest fans last month.";
                    $my_insight->text = "They ".$this->terms->getVerb('liked').
                        " $this->username's " .$this->terms->getNoun('post', InsightTerms::PLURAL).
                        " the most over the last 30 days.";
                }
                $my_insight->filename = basename(__FILE__, ".php");
                $my_insight->emphasis = Insight::EMPHASIS_MED;
                $my_insight->setPeople($fans);
                $this->insight_dao->insertInsight($my_insight);
            }
        }

        if ($instance->network == 'twitter') {
            $day_of_week = 0;
        } else {
            $day_of_week = 3;
        }
        $should_generate_insight = self::shouldGenerateWeeklyInsight('biggest_fans_last_7_days', $instance,
            $insight_date=$since_date, $regenerate_existing_insight=false, $day_of_week = $day_of_week );
        if ($should_generate_insight) { //it's Sunday
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
                if (count($fans) == 1) {
                    $my_insight->headline = 'Last week, '.$prefix.$fans[0]->username
                        . " was $this->username's biggest admirer.";
                    if ($fans[0]->avatar) {
                        $my_insight->header_image = $fans[0]->avatar;
                    }
                } else {
                    $my_insight->headline = "Last week, these were $this->username's biggest admirers.";
                }
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
