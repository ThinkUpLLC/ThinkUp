<?php
/*
 Plugin Name: LOL count (End of Year)
 Description: How often you LOLed in a post this year
 When: December 13
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/eoylolcount.php
 *
 * Copyright (c) 2014-2016 Adam Pash
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

class EOYLOLCountInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     **/
    var $slug = 'eoy_lol_count';
    /**
     * Date to run this insight
     **/
    var $run_date = '12-12';
    //staging
    //var $run_date = '12-08';
    /**
     * @var array Popularity scores of LOLed at posts
     */
    var $scores = array();

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

            $last_year_of_posts = $post_dao->getThisYearOfPostsIterator(
                $author_id = $instance->network_user_id,
                $network = $network
            );

            foreach ($last_year_of_posts as $post) {
                if ($this->hasLOL($post)) {
                    $count++;
                }
            }
            $most_popular_lolees = $this->getMostPopularLOLees($instance);

            $earliest_pub_date = $post_dao->getEarliestCapturedPostPubDate($instance);
            $qualified_year = $year;
            if ( date('Y', strtotime($earliest_pub_date)) == date('Y') ) {
                if ( date('n', strtotime($earliest_pub_date)) > 1 ) { //not January
                    //Earliest post was this year; figure out what month we have data since this year
                    $since = date('F', strtotime($earliest_pub_date));
                    $qualified_year = $year." (at least since ".$since.")";
                }
            }

            $copy = array(
                'twitter' => array(
                    'normal' => array(
                        'headline' => "%username's Twitter LOLs, %year",
                        'body' => array(
                            'normal' => "%username found <strong>%total things</strong> to LOL about on " .
                                "Twitter in %qualified_year, including these LOLed-at tweets.",
                            'one' => "%username found <strong>%total things</strong> to LOL about on " .
                                "Twitter in %qualified_year, including this LOLed-at tweet.",
                            'none' => "%username found <strong>%total things</strong> to LOL about on " .
                                "Twitter in %qualified_year. Not a bad year!",
                        )
                    ),
                    'one' => array(
                        'headline' => "Funny, but rarely LOL-funny",
                        'body' => array(
                            'normal' => "%username found <strong>1 thing</strong> to LOL about on " .
                                "Twitter in %qualified_year."
                        )
                    )
                ),
                'facebook' => array(
                    'normal' => array(
                        'headline' => "%username's LOLs of Facebook, %year",
                        'body' => array(
                            'normal' => "ROFL. %username LOLed at <strong>%total things</strong> on Facebook " .
                                "in %qualified_year, including these LOL-worthy status updates.",
                            'one' => "ROFL. %username LOLed at <strong>%total things</strong> on Facebook " .
                                "in %qualified_year, including this LOL-worthy status update.",
                            'none' => "ROFL. %username LOLed at <strong>%total things</strong> on Facebook " .
                                "in %qualified_year. Gotta love a good LOL.",
                        )
                    ),
                    'one' => array(
                        'headline' => "%username's one LOL on Facebook, %year",
                        'body' => array(
                            'normal' => "%username LOLed <strong>once</strong> on Facebook " .
                                "in %qualified_year. Not the funniest of years."
                        )
                    )
                )
            );

            if ($count === 0) {
                return;
            }
            if ($count > 1) {
                $type = 'normal';
                if (count($most_popular_lolees) > 1) {
                    $body_type = 'normal';
                } else if (count($most_popular_lolees) === 1) {
                    $body_type = 'one';
                } else {
                    $body_type = 'none';
                }
            } else {
                $type = 'one';
                $body_type = 'normal';
            }
            $headline = $this->getVariableCopy(
                array(
                    $copy[$network][$type]['headline']
                ),
                array(
                    'total' => $count,
                    'year' => $year
                )
            );

            $insight_text = $this->getVariableCopy(
                array(
                    $copy[$network][$type]['body'][$body_type]
                ),
                array(
                    'total' => $count,
                    'qualified_year' => $qualified_year
                )
            );

            $insight->headline = $headline;
            $insight->text = $insight_text;
            $lolees = array_slice($most_popular_lolees,0,12);
            $insight->setPosts($lolees);
            $insight->filename = basename(__FILE__, ".php");
            $insight->emphasis = Insight::EMPHASIS_HIGH;

            $this->insight_dao->insertInsight($insight);
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }


    /**
     * Get at most three most popular posts that instigated a LOL
     * @return array $posts
     */
    public function getMostPopularLOLees(Instance $instance) {
        $top_three = array_slice($this->scores, 0, 3, true);
        $posts = array();
        $post_dao = DAOFactory::getDAO('PostDAO');
        foreach ($top_three as $post_id => $score) {
            $posts[] = $post_dao->getPost($post_id, $instance->network);
        }
        return $posts;
    }

    public function hasLOL(Post $post) {
        $text = strtolower($post->post_text);
        $has_lol = preg_match('/(\W|^)(lol|lolol.*|lol.*ing|loled|rofl.*|lmao.*|haha[ha]*)(\W|$)/', $text);

        if ($has_lol && $post->in_reply_to_post_id) {
            $post_dao = DAOFactory::getDAO('PostDAO');
            $funny_post = $post_dao->getPost($post->in_reply_to_post_id, $post->network);
            if ($funny_post) {
                $popularity_index = Utils::getPopularityIndex($funny_post);
                $this->scores[$funny_post->post_id] = $popularity_index;
            }
        }
        arsort($this->scores);
        return $has_lol;
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('EOYLOLCountInsight');
