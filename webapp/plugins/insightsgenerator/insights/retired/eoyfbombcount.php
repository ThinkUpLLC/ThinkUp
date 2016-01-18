<?php
/*
 Plugin Name: F-bomb count (End of Year)
 Description: How often you drop the f-bomb this year.
 When: December 6
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/eoyfbombcount.php
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

class EOYFBombCountInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     **/
    var $slug = 'eoy_fbomb_count';
    /**
     * Date to run this insight
     **/
    var $run_date = '12-06';
    //staging
    //var $run_date = '11-06';

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
            $day_of_year = $this->run_date
        );

        if ($should_generate_insight) {
            $this->logger->logInfo("Should generate", __METHOD__.','.__LINE__);
            $insight = new Insight();
            $insight->instance_id = $instance->id;
            $insight->slug = $this->slug;
            $insight->date = "$year-$this->run_date";

            $count = 0;
            $post_dao = DAOFactory::getDAO('PostDAO');

            /**
             * Track occurences of exclamations per month
             */
            $point_chart = array();

            $last_year_of_posts = $post_dao->getThisYearOfPostsIterator(
                $author_id = $instance->network_user_id,
                $network = $instance->network
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
                if ($this->hasFBomb($post, $instance)) {
                    $date = new DateTime($post->pub_date);
                    $month = $date->format('M');
                    $point_chart[$month]++;
                    $count++;
                }
                $total_posts++;
            }
            $percent = round($count / $total_posts * 100);

            $max_month = $this->getMaxMonth($point_chart);

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
                        'headline' => "%username gave %total fucks on Twitter in %year",
                        'body' => "Whiskey Tango Foxtrot: %username said &ldquo;fuck&rdquo; " .
                            "<strong>%adverbed_total</strong> on Twitter this year, with %month eliciting the most " .
                            "fucks%qualified_year."
                    ),
                    'one' => array(
                        'headline' => "%username really gave a fuck on Twitter in %year",
                        'body' => "Fuck yeah: %username said &ldquo;fuck&rdquo; <strong>once</strong> " .
                            "on Twitter this year%qualified_year, in %month."
                    )
                ),
                'facebook' => array(
                    'normal' => array(
                        'headline' => "%username put the &ldquo;F&rdquo; in &ldquo;Facebook&rdquo; this year",
                        'body' => "%username dropped <strong>%total F-bombs</strong> on Facebook in %year, " .
                            "with %month on the receiving end of the most fucks%qualified_year. WTF?!"
                    ),
                    'one' => array(
                        'headline' => "%username put the &ldquo;F&rdquo; in &ldquo;Facebook&rdquo; this year",
                        'body' => "%username dropped <strong>1 F-bomb</strong> on Facebook in %year, in %month."
                    )
                )
            );

            if ($count > 1) {
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
                            array('label' => 'Occurences', 'type' => 'number'),
                        ),
                        'rows' => $rows
                    ));
                }
            } elseif ($count == 1) {
                $type = "one";
            } else {
                return;
            }

            $terms = new InsightTerms($instance->network);
            $adverbed_total = $terms->getOccurrencesAdverb( $count );
            $headline = $this->getVariableCopy(
                array(
                    $copy[$instance->network][$type]['headline']
                ),
                array(
                    'total' => $count,
                    'year' => $year
                )
            );

            $insight_text = $this->getVariableCopy(
                array(
                    $copy[$instance->network][$type]['body']
                ),
                array(
                    'year' => $year,
                    'total' => $count,
                    'month' => $max_month,
                    'qualified_year' => $qualified_year,
                    'adverbed_total' => $adverbed_total
                )
            );

            $insight->headline = $headline;
            $insight->text = $insight_text;
            $insight->header_image = $user->avatar;
            $insight->filename = basename(__FILE__, ".php");
            $insight->emphasis = Insight::EMPHASIS_HIGH;

            $this->insight_dao->insertInsight($insight);
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

    public function getMaxMonth($point_chart) {
        $short_month = array_search(max($point_chart),$point_chart);
        return date('F', strtotime("$short_month 1 2014"));
    }

    public function hasFBomb(Post $post, Instance $instance) {
        $text = strtolower($post->post_text);
        $has_fbomb = $post->in_reply_to_user_id != $instance->network_user_id && preg_match('/fuck/', $text);

        return $has_fbomb;
    }

}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('EOYFBombCountInsight');
