<?php
/*
 Plugin Name: Biggest Fans (End of Year)
 Description: Who has liked your posts the most over the last year.
 When: December 8
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/eoybiggestfans.php
 *
 * Copyright (c) 2012-2016 Gina Trapani
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
 * Copyright (c) 2014-2016 Adam Pash
 *
 * @author Adam Pash adam.pash@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014-2016 Adam Pash
 */

class EOYBiggestFansInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     **/
    var $slug = 'eoy_biggest_fans';
    /**
     * Date to run this insight
     **/
    var $run_date = '12-08';
    //staging
    //var $run_date = '12-01';

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $year = date('Y');

        $regenerate = false;
        //testing
        //$regenerate = true;

        $should_generate_insight = self::shouldGenerateEndOfYearAnnualInsight(
            $this->slug,
            $instance,
            $insight_date = "$year-$this->run_date",
            $regenerate,
            $day_of_year = $this->run_date,
            null,
            $excluded_networks = array('twitter')
        );

        if ($should_generate_insight) {
            $this->logger->logInfo("Should generate", __METHOD__.','.__LINE__);
            $fav_dao = DAOFactory::getDAO('FavoritePostDAO');
            $fans = $fav_dao->getUsersWhoFavoritedMostOfYourPosts($instance->network_user_id,
                $instance->network, TimeHelper::getDaysSinceJanFirst());
            if (!isset($fans) || sizeof($fans) == 0 ) {
                $this->logger->logInfo("No fans captured", __METHOD__.','.__LINE__);
                return;
            }

            $post_dao = DAOFactory::getDAO('PostDAO');
            $earliest_pub_date = $post_dao->getEarliestCapturedPostPubDate($instance);
            $qualified_year = ".";
            if ( date('Y', strtotime($earliest_pub_date)) == date('Y') ) {
                if ( date('n', strtotime($earliest_pub_date)) > 1 ) { //not January
                    //Earliest post was this year; figure out what month we have data since this year
                    $since = date('F', strtotime($earliest_pub_date));
                    $qualified_year = " (at least since ".$since.").";
                }
            }

            $insight = new Insight();
            $insight->instance_id = $instance->id;
            $insight->slug = $this->slug;
            $insight->date = "$year-$this->run_date";

            $network = $instance->network;

            $copy = array(
                'facebook' => array(
                    'normal' => array(
                        'headline' => "%username's biggest Facebook fans of %year",
                        'body' => "It feels great to have friends who support you. " .
                        "%user_list liked %username's status updates the most this year%qualified_year"
                    )
                ),
                'instagram' => array(
                    'normal' => array(
                        'headline' => "%username's biggest Instagram fans of %year",
                        'body' => "It means a lot to have friends who love your stuff. " .
                        "%user_list liked %username's Instagram photos and videos the most this year%qualified_year"
                    )
                )
            );

            $type = 'normal';
            $headline = $this->getVariableCopy(
                array(
                    $copy[$network][$type]['headline']
                ),
                array(
                    'year' => $year
                )
            );

            $fan_list = array();
            foreach ($fans as $fan) {
                $fan_list[] = $fan->full_name;
            }
            $fan_list = $this->makeList($fan_list);
            $insight_text = $this->getVariableCopy(
                array(
                    $copy[$network][$type]['body']
                ),
                array(
                    'user_list' => $fan_list,
                    'qualified_year' => $qualified_year
                )
            );

            $insight->headline = $headline;
            $insight->text = $insight_text;
            $insight->setPeople($fans);
            $insight->filename = basename(__FILE__, ".php");
            $insight->emphasis = Insight::EMPHASIS_HIGH;
            $this->insight_dao->insertInsight($insight);
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

    /**
     * Turn an array of items into a list with "and"
     * @param arr $items Array of strings to join in a list
     * @return str List of items, like "Item 1, item 2, and item 3"
     */
    private function makeList($items) {
        $count = count($items);

        if ($count === 0) {
            return '';
        }

        if ($count === 1) {
            return $items[0];
        }

        if ($count === 2) {
            return implode(', ', array_slice($items, 0, -1)) . ' and ' . end($items);
        }

        if ($count > 2) {
            return implode(', ', array_slice($items, 0, -1)) . ', and ' . end($items);
        }
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('EOYBiggestFansInsight');
