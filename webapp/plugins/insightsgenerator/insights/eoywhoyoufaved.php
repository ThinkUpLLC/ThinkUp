<?php
/*
 Plugin Name: Who you faved most (End of Year)
 Description: People the user faved the most in the past year.
 When: December 16
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/eoywhoyoufaved.php
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
 * Copyright (c) 2014-2016 Chris Moyer
 *
 * @author Chris Moyer chri@inarow.net
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014-2016 Chris Moyer
 */

class EOYWhoYouFavedInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     **/
    var $slug = 'eoy_who_you_faved';
    /**
     * Date to run this insight
     **/
    var $run_date = '12-16';
    //staging
    //var $run_date = '11-26';

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $regenerate = false;
        //testing
        // $regenerate = true;

        $year = date('Y');
        $should_generate_insight = self::shouldGenerateEndOfYearAnnualInsight(
            $this->slug, $instance, $insight_date = "$year-$this->run_date",
            $regenerate, $day_of_year = $this->run_date,
            $count_related_posts=null,
            array('facebook') //exclude facebook

        );

        if (!$should_generate_insight) {
            $this->logger->logInfo("Done generating insight (Skipped)", __METHOD__.','.__LINE__);
            return;
        }

        $this->logger->logInfo("Should generate", __METHOD__.','.__LINE__);
        $fave_dao = DAOFactory::getDAO('FavoritePostDAO');

        $fave_counts = $fave_dao->getCountOfFavoritedUsersInRange($instance->network_user_id, $instance->network,
            date('Y-m-d', strtotime('January 1')), date('Y-m-d'));

        $favees = array();
        foreach ($fave_counts as $tmp) {
            $favees[$tmp['user_id']] = $tmp['count'];
        }

        arsort($favees);

        $people = array();
        $user_dao = DAOFactory::getDAO('UserDAO');
        foreach ($favees as $aid => $count) {
            $tmp_user = $user_dao->getDetails($aid, $instance->network);
            if ($tmp_user) {
                $people[] = $tmp_user;
            }
        }

        if (count($people) == 0) {
            $this->logger->logInfo("Done generating insight (Not enough data)", __METHOD__.','.__LINE__);
            return;
        }

        $people = array_slice($people, 0, 3);

        $post_dao = DAOFactory::getDAO('PostDAO');
        $earliest_pub_date = $post_dao->getEarliestCapturedPostPubDate($instance);
        $qualified_year = "";
        if (date('Y', strtotime($earliest_pub_date)) == date('Y')) {
            if (date('n', strtotime($earliest_pub_date)) > 1 ) { //not January
                //Earliest post was this year; figure out what month we have data since this year
                $since = date('F', strtotime($earliest_pub_date));
                $qualified_year = " (at least since ".$since.")";
            }
        }

        if (count($people) == 1) {
            $title = $this->username."'s most-liked person on ".ucfirst($instance->network).", $year";
            $text = $this->getVariableCopy(array("Every time you like a %post, a little red heart lights up. "
                . $this->username. " gave the most hearts to @".$people[0]->username." in $year".$qualified_year."."));
        } else {
            $title = $this->username."'s most-liked people on ".ucfirst($instance->network).", $year";
            $text = $this->getVariableCopy(array("Every time you like a %post, a little red heart lights up. "
                . $this->username . " gave the most hearts to these fine folks in $year".$qualified_year."."));
        }

        $insight = new Insight();
        $insight->instance_id = $instance->id;
        $insight->slug = $this->slug;
        $insight->date = "$year-$this->run_date";
        $insight->headline = $title;
        $insight->text = $text;
        $insight->filename = basename(__FILE__, ".php");
        $insight->emphasis = Insight::EMPHASIS_HIGH;
        $insight->setPeople($people);
        $this->insight_dao->insertInsight($insight);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('EOYWhoYouFavedInsight');
