<?php
/*
 Plugin Name: Who you amplified most on Twitter (End of Year)
 Description: People the user retweeted the most in the past year
 When: December 18
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/eoywhoyouamplified.php
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

class EOYWhoYouAmplifiedInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     **/
    var $slug = 'eoy_who_you_amplified';
    /**
     * Date to run this insight
     **/
    var $run_date = '12-18';
    //staging
    //var $run_date = '12-11';

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        if ($instance->network != 'twitter') {
            $this->logger->logInfo("Done generating insight (Skipped non-Twitter)", __METHOD__.','.__LINE__);
            return;
        }

        $year = date('Y');
        $regenerate = false;
        //testing
        //$regenerate = true;

        $should_generate_insight = self::shouldGenerateEndOfYearAnnualInsight(
            $this->slug, $instance, $insight_date = "$year-$this->run_date",
            $regenerate, $day_of_year = $this->run_date
        );

        if (!$should_generate_insight) {
            $this->logger->logInfo("Done generating insight (Skipped)", __METHOD__.','.__LINE__);
            return;
        }

        $this->logger->logInfo("Should generate", __METHOD__.','.__LINE__);
        $post_dao = DAOFactory::getDAO('PostDAO');

        $amp_counts = $post_dao->getRetweetsPerUserInRange($instance->network_user_id, $instance->network,
            date('Y-m-d', strtotime('January 1')), date('Y-m-d'));

        $ampees = array();
        foreach ($amp_counts as $tmp) {
            $ampees[$tmp['user_id']] = $tmp['count'];
        }

        arsort($ampees);

        $people = array();
        $user_dao = DAOFactory::getDAO('UserDAO');
        foreach ($ampees as $aid => $count) {
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

        $earliest_pub_date = $post_dao->getEarliestCapturedPostPubDate($instance);
        $qualified_year = "";
        if (date('Y', strtotime($earliest_pub_date)) == date('Y')) {
            if (date('n', strtotime($earliest_pub_date)) > 1 ) { //not January
                //Earliest post was this year; figure out what month we have data since this year
                $since = date('F', strtotime($earliest_pub_date));
                $qualified_year = " (at least since ".$since.")";
            }
        }

        $title = 'Who '.$this->username.' amplified most on Twitter, '.$year;
        if (count($people) == 1) {
            $text = "Let's turn this tweet up to 11! In $year, ".$this->username
                . " retweeted this user more than any others$qualified_year.";
        } else {
            $text = "Let's turn this tweet up to 11! In $year, ".$this->username
                . " retweeted these users more than any others$qualified_year.";
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
$insights_plugin_registrar->registerInsightPlugin('EOYWhoYouAmplifiedInsight');
