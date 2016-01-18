<?php
/*
 Plugin Name: Most popular links (End of Year)
 Description: The most popular links you shared this year.
 When: December 15
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/eoypopularlink.php
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

class EOYPopularLinkInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     **/
    var $slug = 'eoy_popular_link';
    /**
     * Date to run this insight
     **/
    var $run_date = '12-15';
    //staging
    //var $run_date = '12-08';

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

            $last_year_of_posts = $post_dao->getThisYearOfPostsWithLinksIterator(
                $author_id = $instance->network_user_id,
                $network = $network
            );

            $scored_pics = $this->getScoredLinks($last_year_of_posts);
            $posts = $this->getMostPopularPics($instance, $scored_pics, $user);

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
                        'headline' => "%username's most popular links on Twitter, %year",
                        'body' => "The wealth of the web, shared in a constant 23 characters: " .
                            "These are the most popular links %username shared on " .
                            "Twitter in %qualified_year."
                    ),
                    'one' => array(
                        'headline' => "%username's most popular link on Twitter, %year",
                        'body' => "The wealth of the web, shared in a constant 23 characters: " .
                            "This is the most popular link %username shared on " .
                            "Twitter in %qualified_year."
                    ),
                    'none' => array(
                        'headline' => "%username's words are good enough",
                        'body' => "%username didn't share any links on Twitter in %qualified_year. " .
                            "Crazy, %username!"
                    )
                ),
                'facebook' => array(
                    'normal' => array(
                        'headline' => "%username's most popular links on Facebook, %year",
                        'body' => "We laughed, we cried, we linked. These are the most " .
                            "popular links %username shared on Facebook in %qualified_year."
                    ),
                    'one' => array(
                        'headline' => "%username's most popular link on Facebook, %year",
                        'body' => "We laughed, we cried, we linked. This is the most " .
                            "popular link %username shared on Facebook in %qualified_year."
                    ),
                    'none' => array(
                        'headline' => "No links on Facebook?",
                        'body' => "%username didn't link to anything on Facebook in %qualified_year. " .
                            "The internet promises to try harder, next year."
                    )
                )
            );

            if (sizeof($posts) > 1) {
                $type = 'normal';
            } else if (sizeof($posts) == 1) {
                $type = 'one';
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
                    'qualified_year' => $qualified_year
                )
            );

            $insight->headline = $headline;
            $insight->text = $insight_text;
            $insight->setPosts($posts);
            $insight->filename = basename(__FILE__, ".php");
            $insight->emphasis = Insight::EMPHASIS_HIGH;

            $this->insight_dao->insertInsight($insight);
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

    /**
     * Get at most three most popular posts that had an image
     * @param Instance $instance
     * @param array $scored_pics
     * @param User $user
     * @return array $posts
     */
    public function getMostPopularPics(Instance $instance, $scored_pics, User $user) {
        $top_three = array_slice($scored_pics, 0, 3, true);
        $posts = array();
        $post_dao = DAOFactory::getDAO('PostDAO');
        foreach ($top_three as $post_id => $score) {
            $post = $post_dao->getPost($post_id, $instance->network);
            $post->author_avatar = $user->avatar;
            $posts[] = $post;
        }
        return $posts;
    }

    /**
     * Get scores for all posts with links this year
     * @param PostIterator $last_year_of_posts
     * @return array $scored_links
     */
    public function getScoredLinks($last_year_of_posts) {
        $scored_links = array();
        foreach ($last_year_of_posts as $post) {
            if (sizeof($post->links) > 0) {
                foreach ($post->links as $link) {
                    if ($post->network == 'facebook') {
                        if ( strpos($link->url, 'www.facebook.com/photo.php') === false
                            && strpos($link->url, 'www.facebook.com/events') === false ) {
                            $popularity_index = Utils::getPopularityIndex($post);
                            $scored_links[$post->post_id] = $popularity_index;
                        }
                    } else {
                        if ($link->image_src == '' ) {
                            $popularity_index = Utils::getPopularityIndex($post);
                            $scored_links[$post->post_id] = $popularity_index;
                        }
                    }
                }
            }
        }
        arsort($scored_links);
        return $scored_links;
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('EOYPopularLinkInsight');
