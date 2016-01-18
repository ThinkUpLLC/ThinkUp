<?php
/*
 Plugin Name: Word count (End of Year)
 Description: How many words you posted this year.
 When: December 10
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/eoywordcount.php
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

class EOYWordCountInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     **/
    var $slug = 'eoy_word_count';
    /**
     * Date to run this insight
     **/
    var $run_date = '12-10';
    //staging
    //var $run_date = '12-04';

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $regenerate = false;
        //testing
        //$regenerate = true;

        $year = date('Y');

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

            // Track occurences of words per month
            $point_chart = array();

            $post_dao = DAOFactory::getDAO('PostDAO');
            $last_year_of_posts = $post_dao->getThisYearOfPostsIterator(
                $author_id = $instance->network_user_id,
                $network = $network
            );

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
            $word_count = 0;
            foreach ($last_year_of_posts as $post) {
                if ($post->in_retweet_of_post_id == null) { //don't count retweets
                    $post_word_count = $this->countWords($post->post_text);
                    $word_count += $post_word_count;
                    $date = new DateTime($post->pub_date);
                    $month = $date->format('M');
                    $point_chart[$month] += $post_word_count;
                    $count++;
                }
            }

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

            $max_month = $this->getMaxMonth($point_chart);
            $month_words = number_format($point_chart[substr($max_month, 0, 3)]);
            $number_pages = round($word_count / 275);
            $word_count = number_format($word_count);
            if ($number_pages > 5) {
                $page_copy = "If %username were writing a book, that would be about ".$number_pages." pages. ";
            } else {
                $page_copy = "";
            }

            $copy = array(
                'twitter' => array(
                    'normal' => array(
                        'headline' => "%username tweeted %total words in 2015",
                        'body' => "In %year, %username entered a grand total of <strong>%total words</strong> " .
                            "into the Twitter data entry box%qualified_year, reaching peak wordage " .
                            "in %month, with %words_in_month words. %page_copyHere's the month-by-month breakdown."
                    )
                ),
                'facebook' => array(
                    'normal' => array(
                        'headline' => "%username had a word or two (or %total) for Facebook in 2015",
                        'body' => "In %year, %username typed and tapped <strong>%total words</strong> " .
                            "into Facebook's status update or comment box%qualified_year, topping " .
                            "out with %words_in_month words in %month. %page_copyHere's a breakdown by month."
                    )
                )
            );

            $type = 'normal';
            foreach ($point_chart as $label => $number) {
                $rows[] = array('c'=>array(array('v'=>$label), array('v' => $number)));
            }
            $insight->setLineChart(array(
                'cols' => array(
                    array('label' => 'Month', 'type' => 'string'),
                    array('label' => 'Words', 'type' => 'number'),
                ),
                'rows' => $rows
            ));
            $headline = $this->getVariableCopy(
                array(
                    $copy[$network][$type]['headline']
                ),
                array(
                    'total' => $word_count
                )
            );

            $insight_text = $this->getVariableCopy(
                array(
                    $copy[$network][$type]['body']
                ),
                array(
                    'year' => $year,
                    'total' => $word_count,
                    'month' => $max_month,
                    'words_in_month' => $month_words,
                    'qualified_year' => $qualified_year,
                    'page_copy' => $page_copy
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

    public function getMaxMonth($point_chart) {
        $short_month = array_search(max($point_chart),$point_chart);
        return date('F', strtotime("$short_month 1 2015"));
    }

    public function countWords($str) {
        while (substr_count($str, "  ")>0){
            $str = str_replace("  ", " ", $str);
        }
        return substr_count($str, " ")+1;
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('EOYWordCountInsight');
