<?php
/*
 Plugin Name: Most retweeted post (End of Year)
 Description: User's most retweeted post in current year.
 When: Annually on December 8
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/eoymostretweetedpost.php
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
 * EOYMostRetweetedPost (name of file)
 *
 * Description of what this class does
 *
 * Copyright (c) 2014-2016 Adam Pash
 *
 * @author Adam Pash adam.pash@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014-2016 Adam Pash
 */

class EOYMostRetweetedPostInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     **/
    var $slug = 'eoy_most_retweeted';
    /**
     * Date to run this insight
     **/
    var $run_date = '12-08';
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
            null,
            $excluded_networks = array('facebook', 'instagram')
        );

        if ($should_generate_insight) {
            $this->logger->logInfo("Should generate", __METHOD__.','.__LINE__);

            $insight = new Insight();
            $insight->instance_id = $instance->id;
            $insight->slug = $this->slug;
            $insight->date = "$year-$this->run_date";

            $top_three_shared = $this->topThreeThisYear($instance);

            $post_dao = DAOFactory::getDAO('PostDAO');
            $earliest_pub_date = $post_dao->getEarliestCapturedPostPubDate($instance);
            $qualified_year = "";
            if ( date('Y', strtotime($earliest_pub_date)) == date('Y') ) {
                if ( date('n', strtotime($earliest_pub_date)) > 1 ) { //not January
                    //Earliest post was this year; figure out what month we have data since this year
                    $since = date('F', strtotime($earliest_pub_date));
                    $qualified_year = " (at least since ".$since.")";
                }
            }

            $copy = array(
                'twitter' => array(
                    'normal' => array(
                        'headline' => "%username's most-retweeted tweets of %year",
                        'body' => "Tweet, retweet, repeat. In %year, " .
                            "%username earned the most retweets for these gems%qualified_year."
                    ),
                    'one' => array(
                        'headline' => "%username's most-retweeted tweet of %year",
                        'body' => "Tweet, retweet, repeat. In %year, " .
                            "%username earned the most retweets for this gem%qualified_year."
                    ),
                    'none' => array(
                        'headline' => "Retweets aren't everything",
                        'body' => "%username didn't get any retweets in %year".
                            $qualified_year.", which is a-okay. We're not all here to broadcast."
                    ),
                ),
                'facebook' => array(
                    'normal' => array(
                        'headline' => "%username's most-shared status updates of %year",
                        'body' => "With shares on the rise, %year was a bull " .
                            "market for %username's most-shared status updates."
                    ),
                    'one' => array(
                        'headline' => "%username's most-shared status update of %year",
                        'body' => "With shares on the rise, %year was a " .
                            "bull market for %username's most-shared status update."
                    ),
                    'none' => array(
                        'headline' => "Shares aren't everything",
                        'body' => "No one shared %username's status updates on " .
                            "Facebook in %year — not that there's anything wrong " .
                            "with that. Sometimes it's best to keep things close-knit."
                    ),
                )
            );

            $network = $instance->network;

            if (sizeof($top_three_shared) > 1) {
                $type = 'normal';
            } else if (sizeof($top_three_shared) == 1) {
                $type = 'one';
            } else {
                $type = 'none';
            }

            $insight->headline = $this->getVariableCopy(
                array(
                    $copy[$network][$type]['headline']
                ),
                array(
                    'year' => $year
                )
            );
            $insight->text = $this->getVariableCopy(
                array(
                    $copy[$network][$type]['body']
                ),
                array(
                    'year' => $year,
                    'qualified_year' => $qualified_year
                )
            );

            $insight->emphasis = Insight::EMPHASIS_HIGH;
            $insight->filename = basename(__FILE__, ".php");

            //Avoid broken avatars
            foreach ($top_three_shared as $post) {
                $post->author_avatar = $user->avatar;
            }
            $insight->setPosts($top_three_shared);

            $this->insight_dao->insertInsight($insight);
            $insight = null;

            $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
        }
    }

    /**
     * Get three most retweeted posts this year
     * @param Instance $instance
     * @param str $order Defaults to 'retweets'
     * @return array Three most retweeted posts in descending order
     */
    public function topThreeThisYear(Instance $instance, $order='retweet_count_api') {
        $post_dao = DAOFactory::getDAO('PostDAO');
        $days = TimeHelper::getDaysSinceJanFirst();
        $posts = $post_dao->getAllPostsByUsernameOrderedBy(
            $instance->network_username,
            $network=$instance->network,
            $count=3,
            $order_by=$order,
            $in_last_x_days = $days,
            $iterator = false,
            $is_public = false
        );
        //Filter out posts with 0 retweets
        $posts_with_retweets = array();
        foreach ($posts as $post) {
            if ($post->retweet_count_api > 0) {
                $posts_with_retweets[] = $post;
            }
        }
        return $posts_with_retweets;
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('EOYMostRetweetedPostInsight');
