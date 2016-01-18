<?php
/*
 Plugin Name: Exclamation count (End of Year)
 Description: How often you used exclamation points this year!!!!!
 When: December 5
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/eoyexclamationcount.php
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

class EOYExclamationCountInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     **/
    var $slug = 'eoy_exclamation_count';
    /**
     * Date to run this insight
     **/
    var $run_date = '12-07';
    //staging
    //var $run_date = '11-05';

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
            $count_related_posts=null,
            array('instagram') //exclude instagram
        );

        if ($should_generate_insight) {
            $this->logger->logInfo("Should generate", __METHOD__.','.__LINE__);
            $insight = new Insight();
            $insight->instance_id = $instance->id;
            $insight->slug = $this->slug;
            $insight->date = "$year-$this->run_date";

            $count = 0;
            $post_dao = DAOFactory::getDAO('PostDAO');
            $network = $instance->network;

            /**
             * Track occurences of exclamations per month
             */
            $point_chart = array();
            $last_year_of_posts = $post_dao->getThisYearOfPostsIterator(
                $author_id = $instance->network_user_id,
                $network = $network
            );
            $total_posts = 0;

            $months = array(
                'Jan',
                'Feb',
                'Mar',
                'Apr',
                'May',
                'Jun',
                'Jul',
                'Aug',
                'Sep',
                'Oct',
                'Nov',
                'Dec'
            );
            foreach ($months as $month) {
                $point_chart[$month] = 0;
            }
            foreach ($last_year_of_posts as $post) {
                if ($this->hasExclamationPoint($post->post_text)) {
                    $date = new DateTime($post->pub_date);
                    $month = $date->format('M');
                    $point_chart[$month]++;
                    $count++;
                }
                $total_posts++;
            }
            $percent = round($count / $total_posts * 100);

            $earliest_pub_date = $post_dao->getEarliestCapturedPostPubDate($instance);
            $qualified_year = "";
            if ( date('Y', strtotime($earliest_pub_date)) == date('Y') ) {
                if ( date('n', strtotime($earliest_pub_date)) > 1 ) { //not January
                    //Earliest post was this year; figure out what month we have data since this year
                    $since = date('F', strtotime($earliest_pub_date));
                    $qualified_year = " (at least since ".$since.")";
                    $since_int = date('n', strtotime($earliest_pub_date));
                    $since_int--;
                    $point_chart = array_slice($point_chart, $since_int );
                }
            }

            $copy = array(
                'twitter' => array(
                    'normal' => array(
                        'headline' => "%username's !!!'s of Twitter, %year",
                        'body' => "OMG! In %year, %username used exclamation points " .
                            "in <strong>%total tweets</strong>. That's %percent% " .
                            "of %username's tweets this year%qualified_year!"
                    ),
                    'none' => array(
                        'headline' => "%username was not impressed with %year",
                        'body' => "In %year, %username didn't use one exclamation " .
                            "point on Twitter%qualified_year. Must be holding out for something " .
                            "really exciting!"
                    )
                ),
                'facebook' => array(
                    'normal' => array(
                        'headline' => "%username's emphatic %year on Facebook!",
                        'body' => "Enthusiasm is contagious, and in %year, %username " .
                            "spread the excitement in a total of <strong>%total %posts" .
                            "</strong> containing exclamation points. " .
                            "That's %percent% of %username's Facebook posts " .
                            "this year%qualified_year!"
                    ),
                    'none' => array(
                        'headline' => "%username was not impressed with %year",
                        'body' => "In %year, %username didn't use one exclamation " .
                            "point on Facebook%qualified_year. Must be holding out for something " .
                            "really exciting!"
                    )
                )
            );

            if ($count > 0) {
                $type = 'normal';
                $rows = array();
                $do_include_chart = false;
                foreach ($point_chart as $label => $number) {
                    $rows[] = array('c'=>array(array('v'=>$label), array('v' => $number)));
                    if ($number >= 4) { //Y-axis always renders 4 points
                        $do_include_chart = true;
                    }
                }
                if ($do_include_chart && sizeof($rows) > 2) {
                    $insight->setLineChart(array(
                        'cols' => array(
                            array('label' => 'Month', 'type' => 'string'),
                            array('label' => 'Total', 'type' => 'number'),
                        ),
                        'rows' => $rows
                    ));
                }
            } else {
                $type = 'none';
            }
            $headline = $this->getVariableCopy(
                array(
                    $copy[$network][$type]['headline']
                ),
                array(
                    'year' => $year
                )
            );

            $insight_text = $this->getVariableCopy(
                array(
                    $copy[$network][$type]['body']
                ),
                array(
                    'year' => $year,
                    'total' => number_format($count),
                    'percent' => $percent,
                    'qualified_year' => $qualified_year
                )
            );

            $insight->headline = $headline;
            $insight->text = $insight_text;
            $insight->filename = basename(__FILE__, ".php");
            $insight->emphasis = Insight::EMPHASIS_HIGH;
            $insight->header_image = $user->avatar;

            $this->insight_dao->insertInsight($insight);
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

    public function hasExclamationPoint($post_text) {
        $does_match = preg_match_all('/!+/', $post_text);
        return $does_match;
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('EOYExclamationCountInsight');
