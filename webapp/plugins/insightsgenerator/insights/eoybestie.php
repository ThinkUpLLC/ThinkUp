<?php
/*
 Plugin Name: Bestie (End of Year)
 Description: Who you've interacted with most in the past year.
 When: December 9
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/eoybestie.php
 *
 * Copyright (c) 2014-2016 Gina Trapani
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
 * Copyright (c) 2014-2016 Gina Trapani
 *
 * @author Gina Trapani <ginatrapani at gmail dot com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014-2016 Gina Trap9ani
 */

class EOYBestieInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     **/
    var $slug = 'eoy_bestie';
    /**
     * Date to run this insight
     **/
    var $run_date = '12-09';
    //staging
    //var $run_date = '12-04';

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

            $post_dao = DAOFactory::getDAO('PostDAO');

            $days = TimeHelper::getDaysSinceJanFirst();
            $bestie = $post_dao->getBestie($instance, $days );

            if (isset($bestie)) {
                $type = 'normal';
            } else {
                $type = 'none';
            }

            $earliest_pub_date = $post_dao->getEarliestCapturedPostPubDate($instance);
            $qualified_year = "";
            if ( date('Y', strtotime($earliest_pub_date)) == date('Y') ) {
                if ( date('n', strtotime($earliest_pub_date)) > 1 ) { //not January
                    //Earliest post was this year; figure out what month we have data since this year
                    $since = date('F', strtotime($earliest_pub_date));
                    $qualified_year = " (at least since ".$since.")";
                }
            }

            $insight = new Insight();
            $insight->instance_id = $instance->id;
            $insight->slug = $this->slug;
            $insight->date = "$year-$this->run_date";

            $network = $instance->network;

            $copy = array(
                'twitter' => array(
                    'normal' => array(
                        'headline' => "%username's Twitter bestie of %year",
                        'body' => "Nobody likes tweeting into the void. %username and @%bestie made Twitter a ".
                            "void-free place to tweet this year. %username tweeted at @%bestie ".
                            "<strong>%u_to_b times</strong> in 2015, and @%bestie replied ".
                            "<strong>%b_to_u times</strong>%qualified_year. OMG you two!"
                    ),
                    'none' => array(
                        'headline' => "%username's Twitter bestie of %year",
                        'body' => "%username didn't reply to any one person more than 3 times ".
                            "this year%qualified_year. That means no one can claim the title of %username's Twitter ".
                            "bestie. Playing hard-to-get, huh?"
                    )
                ),
                'facebook' => array(
                    'normal' => array(
                        'headline' => "%username's Facebook bestie of %year",
                        'body' => "Everyone loves getting comments from their friends. In 2015, %bestie commented ".
                            "on %username's status updates <strong>%b_to_u times</strong>, more than ".
                            "anyone else%qualified_year. Best friends forever!"
                    ),
                    'none' => array(
                        'headline' => "%username's Facebook bestie of %year",
                        'body' => "%username's friends must consider %username's words definitive - no one replied ".
                            "more than three times to %username's status updates all year%qualified_year."
                    )
                ),
                'instagram' => array(
                    'normal' => array(
                        'headline' => "%username's Instagram bestie of %year",
                        'body' => "Everyone loves getting comments from their friends. In 2015, %bestie commented ".
                            "on %username's Instagram photos and videos <strong>%b_to_u times</strong>, more than ".
                            "anyone else%qualified_year. Best friends forever!"
                    ),
                    'none' => array(
                        'headline' => "%username's Instagram bestie of %year",
                        'body' => "%username's photos and videos left %username's friends speechless - no one replied ".
                            "more than three times to %username's Instagram posts all year%qualified_year."
                    )
                )
            );

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
                    'bestie' => ((isset($bestie['user_name'])?$bestie['user_name']:"")),
                    'u_to_b' => ((isset($bestie['total_replies_to'])?$bestie['total_replies_to']:"")),
                    'b_to_u' => ((isset($bestie['total_replies_from'])?$bestie['total_replies_from']:"")),
                    'qualified_year' => $qualified_year
                )
            );

            $insight->headline = $headline;
            $insight->text = $insight_text;
            $insight->filename = basename(__FILE__, ".php");
            $insight->emphasis = Insight::EMPHASIS_HIGH;
            if (isset($bestie['avatar'])) {
                $insight->header_image = $bestie['avatar'];
            }
            $this->insight_dao->insertInsight($insight);
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('EOYBestieInsight');
