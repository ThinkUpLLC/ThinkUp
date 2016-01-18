<?php
/*
 Plugin Name: Most popular post (End of Year)
 Description: Your most popular post this year.
 When: December 23
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/eoymostpopular.php
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

class EOYMostPopularInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     **/
    var $slug = 'eoy_most_popular';
    /**
     * Date to run this insight
     **/
    var $run_date = '12-23';
    //staging
    //var $run_date = '12-15';


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
            $network = $instance->network;

            $last_year_of_posts = $post_dao->getThisYearOfPostsIterator(
                $author_id = $instance->network_user_id,
                $network = $network
            );

            $scored_posts = $this->getScoredPosts($last_year_of_posts);
            $top_post = $this->getMostPopularPost($instance, $scored_posts);

            if (isset($top_post)) {
                //Populate Instagram photo
                if ($instance->network == 'instagram') {
                    $photo_dao = DAOFactory::getDAO('PhotoDAO');
                    $top_post = $photo_dao->getPhoto($top_post->post_id, 'instagram');
                }

                $earliest_pub_date = $post_dao->getEarliestCapturedPostPubDate($instance);
                $qualified_year = $year.".";
                if ( date('Y', strtotime($earliest_pub_date)) == date('Y') ) {
                    if ( date('n', strtotime($earliest_pub_date)) > 1 ) { //not January
                        //Earliest post was this year; figure out what month we have data since this year
                        $since = date('F', strtotime($earliest_pub_date));
                        $qualified_year = $year." (at least since ".$since.").";
                    }
                }

                $copy = array(
                    'twitter' => array(
                        'normal' => array(
                            'headline' => "%username's most popular tweet of %year",
                            'body' => "We don't tweet for the glory, but a little " .
                            "attention doesn't hurt. With <strong>%list_of_stats</strong>, " .
                            "this is %username's most popular tweet of %qualified_year"
                        ),
                    ),
                    'facebook' => array(
                        'normal' => array(
                            'headline' => "%username's most popular status update of %year",
                            'body' => "Sometimes you just say the right thing. With <strong>" .
                                "%list_of_stats</strong>, this is %username's most " .
                                "popular status update of %qualified_year"
                        ),
                    ),
                    'instagram' => array(
                        'normal' => array(
                            'headline' => "%username's most popular Instagram post of %year",
                            'body' => "Once in awhile, a photo or video really stands out. With <strong>" .
                                "%list_of_stats</strong>, this is %username's most " .
                                "popular Instagram post of %qualified_year"
                        ),
                    )
                );

                $stats = array();
                if ($top_post->favlike_count_cache > 0) {
                    $stats[] = $top_post->favlike_count_cache . " " .
                        $this->terms->getNoun('like', $top_post->favlike_count_cache);
                }
                if ($top_post->retweet_count_cache > 0) {
                    $stats[] = $top_post->retweet_count_cache . " " .
                        $this->terms->getNoun('retweet', $top_post->retweet_count_cache);
                }
                if ($top_post->reply_count_cache > 0) {
                    $stats[] = $top_post->reply_count_cache . " " .
                        $this->terms->getNoun('reply', $top_post->reply_count_cache);
                }
                $list_of_stats = $this->makeList($stats);

                $type = 'normal';
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
                        'qualified_year' => $qualified_year,
                        'list_of_stats' => $list_of_stats
                    )
                );

                $insight->headline = $headline;
                $insight->text = $insight_text;
                $insight->setPosts(array($top_post));
                $insight->filename = basename(__FILE__, ".php");
                $insight->emphasis = Insight::EMPHASIS_HIGH;

                $this->insight_dao->insertInsight($insight);
            } else {
                $this->logger->logInfo("No top post to report", __METHOD__.','.__LINE__);
            }
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

    /**
     * Get at most three most popular posts that had an image
     * @param Instance $instance
     * @param array $scored_posts
     * @return array $posts
     */
    public function getMostPopularPost(Instance $instance, $scored_posts) {
        $top = array_slice($scored_posts, 0, 1, true);
        $post_dao = DAOFactory::getDAO('PostDAO');
        $top_post = null;
        foreach ($top as $post_id => $score) {
            $top_post = $post_dao->getPost($post_id, $instance->network);
            $link_dao = DAOFactory::getDAO('LinkDAO');
            $top_post->links = $link_dao->getLinksForPost($top_post->post_id, $top_post->network);
        }
        return $top_post;
    }

    /**
     * Get scores for all posts this year
     * @param PostIterator $last_year_of_posts
     * @return array $scored_posts
     */
    public function getScoredPosts($last_year_of_posts) {
        $scored_posts = array();
        foreach ($last_year_of_posts as $post) {
            $popularity_index = Utils::getPopularityIndex($post);
            $scored_posts[$post->post_id] = $popularity_index;
        }
        arsort($scored_posts);
        return $scored_posts;
    }

    /**
     * Turn an array of items into a list with "and"
     * @param arr $items Array of strings to join in a list
     * @return str List of items, like "Item 1, item 2, and item 3"
     */
    private function makeList($items) {
        $count = count($items);
        if ($count === 0) {
            return '';
        }
        if ($count === 1) {
            return $items[0];
        }
        if ($count === 2) {
            return implode(', ', array_slice($items, 0, -1)) . ' and ' . end($items);
        }
        if ($count > 2) {
            return implode(', ', array_slice($items, 0, -1)) . ', and ' . end($items);
        }
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('EOYMostPopularInsight');
