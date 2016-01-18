<?php
/*
 Plugin Name: Most popular pics (End of Year)
 Description: The most popular images you shared this year.
 When: December 4
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/eoypopularpic.php
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

class EOYPopularPicInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     **/
    var $slug = 'eoy_popular_pic';
    /**
     * Date to run this insight
     **/
    var $run_date = '12-04';
    //staging
    //var $run_date = '11-04';

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

            $scored_pics = $this->getScoredPics($last_year_of_posts);
            $posts = $this->getMostPopularPics($instance, $scored_pics);

            $earliest_pub_date = $post_dao->getEarliestCapturedPostPubDate($instance);
            $qualified_year = ".";
            if ( date('Y', strtotime($earliest_pub_date)) == date('Y') ) {
                if ( date('n', strtotime($earliest_pub_date)) > 1 ) { //not January
                    //Earliest post was this year; figure out what month we have data since this year
                    $since = date('F', strtotime($earliest_pub_date));
                    $qualified_year = " (at least since ".$since.").";
                }
            }

            $copy = array(
                'twitter' => array(
                    'normal' => array(
                        'headline' => "%username's most popular picture on Twitter, %year",
                        'body' => "With tweets limited to 140 characters, a picture " .
                            "is worth at least 1,000 characters. In %year, these were the " .
                            "most popular pics %username shared on Twitter%qualified_year"
                    ),
                    'one' => array(
                        'headline' => "%username's most popular picture on Twitter, %year",
                        'body' => "With tweets limited to 140 characters, a picture " .
                            "is worth at least 1,000 characters. In %year, this was the " .
                            "most popular pic %username shared on Twitter%qualified_year"
                    ),
                    'none' => array(
                        'headline' => "%username must yearn for the text-only days of Twitter",
                        'body' => "%username didn't share any pics on Twitter this year%qualified_year " .
                            "Bummer! On the plus side: %username probably doesn't need to worry about " .
                            "leaked nudes!"
                    )
                ),
                'facebook' => array(
                    'normal' => array(
                        'headline' => "%username's most popular picture on Facebook, %year",
                        'body' => "What's a newsfeed without the photos? In %year, " .
                            "these were the most popular pics %username shared on Facebook%qualified_year"
                    ),
                    'one' => array(
                        'headline' => "%username's most popular picture on Facebook, %year",
                        'body' => "What's a newsfeed without the photos? In %year, " .
                            "this was the most popular pic %username shared on Facebook%qualified_year"
                    ),
                    'none' => array(
                        'headline' => "No photos on Facebook?",
                        'body' => "%username didn't share any pics on Facebook this year%qualified_year " .
                            "Bummer! On the plus side: %username probably doesn't need to worry about " .
                            "leaked nudes!"
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
                    'qualified_year' => $qualified_year,
                )
            );

            $insight->headline = $headline;
            $insight->text = $insight_text;
            //Avoid InsightFieldExceedsMaxLengthException
            $posts = array_slice($posts, 0, 12);
            //Avoid broken avatars
            foreach ($posts as $post) {
                $post->author_avatar = $user->avatar;
            }
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
     * @return array $posts
     */
    public function getMostPopularPics(Instance $instance, $scored_pics) {
        $top_three = array_slice($scored_pics, 0, 3, true);
        $posts = array();
        $post_dao = DAOFactory::getDAO('PostDAO');
        foreach ($top_three as $post_id => $score) {
            $posts[] = $post_dao->getPost($post_id, $instance->network);
        }
        return $posts;
    }

    /**
     * Get scores for all pics in this year's posts
     * @param PostIterator $last_year_of_posts
     * @return array $scored_pics
     */
    public function getScoredPics($last_year_of_posts) {
        $scored_pics = array();
        foreach ($last_year_of_posts as $post) {
            if (sizeof($post->links) > 0) {
                foreach ($post->links as $link) {
                    if ($this->isCountablePic($link, $post->network)) {
                        $popularity_index = Utils::getPopularityIndex($post);
                        $scored_pics[$post->post_id] = $popularity_index;
                    }
                }
            }
        }
        arsort($scored_pics);
        return $scored_pics;
    }

    /**
     * Should we count this photo?
     * On Facebook, links to articles with art often include thumbnail images. We only want to count timeline photos,
     * so on Facebook, this returns true only if it's a timeline photo.
     * @param  Link    $link
     * @param  str  $network
     * @return bool
     */
    private function isCountablePic(Link $link, $network) {
        if ($network == 'facebook') {
            if (strpos($link->url, 'www.facebook.com/photo.php') !== false) {
                return true;
            } else {
                return false;
            }
        } else {
            return isset($link->image_src) && ($link->image_src !== '');
        }
    }

}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('EOYPopularPicInsight');
