<?php
/*
 Plugin Name: All About You (End of Year)
 Description: How often you referred to yourself ("I", "me", "myself", "my") in the past year.
 When: December 2
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/eoyallaboutyou.php
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

require_once THINKUP_WEBAPP_PATH. 'plugins/insightsgenerator/insights/allaboutyou.php';

class EOYAllAboutYouInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     **/
    var $slug = 'eoy_all_about_you';
    /**
     * Date to run this insight
     **/
    var $run_date = '12-02';
    //staging
    //var $run_date = '11-26';

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
            $network = $instance->network;

            $count = 0;

            $post_dao = DAOFactory::getDAO('PostDAO');
            $last_year_of_posts = $post_dao->getThisYearOfPostsIterator(
                $author_id = $instance->network_user_id,
                $network = $network
            );
            $total_posts = 0;

            foreach ($last_year_of_posts as $post) {
                $count += AllAboutYouInsight::hasFirstPersonReferences($post->post_text) ? 1 : 0;
                $total_posts++;
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

            if ($instance->network == 'twitter') {
                $headline = $this->getVariableCopy(array(
                    "A year's worth of %username"
                ));
            } elseif ($instance->network == 'instagram') {
                $headline = $this->getVariableCopy(array(
                    "A year of %username photos"
                ));
            } else {
                $headline = $this->getVariableCopy(array(
                    "A year about %username"
                ));
            }
            $percent = round($count / $total_posts * 100);
            if ($count > 0) {
                if ($instance->network == 'twitter' || $instance->network == 'facebook') {
                    $insight_text = $this->getVariableCopy(
                        array(
                            "In %year, <strong>$percent%</strong> of %username's %posts " .
                            "&mdash; a grand total of %total &mdash; contained " .
                            "the words &ldquo;I&rdquo;, &ldquo;me&rdquo;, &ldquo;my&rdquo;, " .
                            "&ldquo;mine&rdquo;, or &ldquo;myself&rdquo;%qualified_year. " .
                            (($instance->network == 'facebook')?
                                "Go ahead: tell your story"
                                :"Sometimes, you've just got to get personal")."."
                        ),
                        array(
                            'total' => number_format($count),
                            'year' => $year,
                            'qualified_year' => $qualified_year
                        )
                    );
                } else {
                    $insight_text = $this->getVariableCopy(
                        array(
                            "In %year, <strong>$percent%</strong> of %username's Instagram captions " .
                            "&mdash; a grand total of %total &mdash; contained " .
                            "the words &ldquo;I&rdquo;, &ldquo;me&rdquo;, &ldquo;my&rdquo;, " .
                            "&ldquo;mine&rdquo;, or &ldquo;myself&rdquo;%qualified_year. " .
                            "What a life in pictures!"
                        ),
                        array(
                            'total' => number_format($count),
                            'year' => $year,
                            'qualified_year' => $qualified_year
                        )
                    );
                }
            } else {
                $insight_text = $this->getVariableCopy(
                    array(
                        "In %year, none of %username's %posts contained " .
                        "the words &ldquo;I&rdquo;, &ldquo;me&rdquo;, &ldquo;my&rdquo;, " .
                        "&ldquo;mine&rdquo;, or &ldquo;myself&rdquo;%qualified_year. Sometimes, " .
                        "you've just got to get personal &mdash; unless you're " .
                        "%username, apparently!"
                    ),
                    array(
                        'total' => number_format($count),
                        'year' => $year,
                        'qualified_year' => $qualified_year
                    )
                );
            }

            $insight->headline = $headline;
            $insight->text = $insight_text;
            $insight->filename = basename(__FILE__, ".php");
            $insight->emphasis = Insight::EMPHASIS_HIGH;
            $insight->header_image = $user->avatar;

            $this->insight_dao->insertInsight($insight);
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

    /**
     * Get year of posts as an iterator
     * @param Instance $instance
     * @return PostIterator $posts
     */
    public function getYearOfPosts(Instance $instance) {
        $post_dao = DAOFactory::getDAO('PostDAO');
        $days = TimeHelper::getDaysSinceJanFirst();

        $posts = $post_dao->getAllPostsByUsernameOrderedBy(
            $instance->network_username,
            $network=$instance->network,
            $count=0,
            $order_by='pub_date',
            $in_last_x_days = $days,
            $iterator = true,
            $is_public = false
        );
        return $posts;
    }

}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('EOYAllAboutYouInsight');
