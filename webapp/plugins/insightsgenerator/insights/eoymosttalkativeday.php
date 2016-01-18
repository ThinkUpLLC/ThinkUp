<?php
/*
 Plugin Name: Most talkative day (End of Year)
 Description: Day this year user posted the post.
 When: Annually on December 3
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/eoymosttalkativeday.php
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
 * EOYMostTalkativeDay (name of file)
 *
 * Description of what this class does
 *
 * Copyright (c) 2014-2016 Adam Pash
 *
 * @author Adam Pash adam.pash@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014-2016 Adam Pash
 */

class EOYMostTalkativeDayInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     **/
    var $slug = 'eoy_most_talkative';
    /**
     * Date to run this insight
     **/
    var $run_date = '12-03';
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
            $filename = basename(__FILE__, ".php");

            $insight = new Insight();
            $insight->instance_id = $instance->id;
            $insight->slug = $this->slug;
            $insight->date = "$year-$this->run_date";

            $post_dao = DAOFactory::getDAO('PostDAO');
            $earliest_pub_date = $post_dao->getEarliestCapturedPostPubDate($instance);
            $qualified_year = "this year.";
            if ( date('Y', strtotime($earliest_pub_date)) == date('Y') ) {
                if ( date('n', strtotime($earliest_pub_date)) > 1 ) { //not January
                    //Earliest post was this year; figure out what month we have data since this year
                    $since = date('F', strtotime($earliest_pub_date));
                    $qualified_year = "this year (at least since ".$since.").";
                }
            }

            $copy = array(
                'twitter' => array(
                    'normal' => array(
                        'headline' => "%username's most talkative day on Twitter in %year",
                        'body' => "%username tweeted <strong>%total times on %talkative_date</strong>, " .
                        "more than any other day %qualified_year (Strange — the " .
                        "forecast didn't say anything about a tweetstorm.) " .
                        "These are %username's most popular tweets from that day."
                    ),
                    'multiple' => array(
                        'headline' => "%username's most talkative day on Twitter in %year",
                        'body' => "In the running for %username's most talkative day " .
                        "on Twitter, %year, we've got a tie: %username tweeted " .
                        "<strong>%total times on %talkative_date</strong> — more than on any other " .
                        "days %qualified_year These are %username's most popular tweets " .
                        "from each day."
                    )
                ),
                'facebook' => array(
                    'normal' => array(
                        'headline' => "%username's most talkative day on Facebook in %year",
                        'body' => "%username posted to Facebook <strong>%total times on " .
                        "%talkative_date</strong>, more than any other day %qualified_year " .
                        "These are %username's most popular status updates from that day."
                    ),
                    'multiple' => array(
                        'headline' => "%username's most talkative day on Facebook in %year",
                        'body' => "In the running for %username's most talkative day " .
                        "on Facebook, %year, we've got a tie: %username posted " .
                        "<strong>%total times on %talkative_date</strong> — more than on any other " .
                        "days %qualified_year These are %username's most popular " .
                        "status updates from each day."
                    )
                ),
                'instagram' => array(
                    'normal' => array(
                        'headline' => "%username's most Instagrammed day in %year",
                        'body' => "%username posted on Instagram <strong>%total times on " .
                        "%talkative_date</strong>, more than any other day %qualified_year " .
                        "These are %username's most popular photos and videos from that day."
                    ),
                    'multiple' => array(
                        'headline' => "%username's most Instagrammed day in %year",
                        'body' => "In the running for %username's most Instagrammed day " .
                        "in %year, we've got a tie: %username posted " .
                        "<strong>%total times on %talkative_date</strong> — more than on any other " .
                        "days %qualified_year These are %username's most popular " .
                        "posts from each day."
                    )
                )
            );

            $network = $instance->network;
            $most_talkative_days = $this->getMostTalkativeDays($instance);

            if (sizeof($most_talkative_days) == 1) {
                $type = 'normal';
                $date = new DateTime($most_talkative_days[0]['pub_date']);
                $query_date = $date->format("Y-m-d");
                $talkative_dates = $date->format('F jS');
                $popular_posts = $this->mostPopularPosts(
                    $instance,
                    $date = $query_date
                );
            } else if (sizeof($most_talkative_days) > 1) {
                $type = 'multiple';
                $dates = array();
                $popular_posts = array();
                foreach($most_talkative_days as $day) {
                    $date = new DateTime($day['pub_date']);
                    $query_date = $date->format("Y-m-d");
                    $dates[] = $date->format('F jS');
                    $posts = $this->mostPopularPosts(
                        $instance,
                        $date = $query_date,
                        $limit = 1
                    );
                    $popular_posts[] = $posts[0];
                }
                $talkative_dates = $this->makeList($dates);
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
                    'total' => $most_talkative_days[0]['post_count'],
                    'talkative_date' => $talkative_dates,
                    'qualified_year' => $qualified_year
                )
            );

            $insight->emphasis = Insight::EMPHASIS_HIGH;
            $insight->filename = $filename;

            //Populate Instagram photos
            if ($instance->network == 'instagram') {
                $photo_dao = DAOFactory::getDAO('PhotoDAO');
                $popular_photos = array();
                foreach ($popular_posts as $post) {
                    if ($post->network == 'instagram') {
                        $post = $photo_dao->getPhoto($post->post_id, 'instagram');
                        $popular_photos[] = $post;
                    }
                }
                $popular_posts = $popular_photos;
            }
            foreach ($popular_posts as $post) {
                //Avoid broken avatars
                $post->author_avatar = $user->avatar;
            }
            $insight->setPosts($popular_posts);

            $this->insight_dao->insertInsight($insight);
            $insight = null;

            $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
        }
    }


    /**
     * Get the most talkative day(s) on network
     * @param Instance $instance
     * @return array Most talkative day or days
     */
    public function getMostTalkativeDays(Instance $instance) {
        $post_dao = DAOFactory::getDAO('PostDAO');
        $days = TimeHelper::getDaysSinceJanFirst();

        $most_talkative_days = $post_dao->getMostTalkativeDays(
            $instance->network_username,
            $instance->network,
            $in_last_x_days = $days
        );

        $most_posts = $most_talkative_days[0]['post_count'];
        foreach ($most_talkative_days as $key => $day) {
            if ($day['post_count'] < $most_posts) {
                unset($most_talkative_days[$key]);
            }
        }
        return $most_talkative_days;
    }

    /**
     * Get the most popular posts on a given date
     * @param Instance $instance
     * @param Date $date
     * @param int $limit Defaults to 3
     * @return array Most popular posts from the given date
     */
    public function mostPopularPosts(Instance $instance, $date, $limit=3) {
        $post_dao = DAOFactory::getDAO('PostDAO');
        $days_posts = $post_dao->getAllPostsByUsernameOn(
            $instance->network_username,
            $network=$instance->network,
            $date=$date
        );

        $popularity_counts = array();
        foreach ($days_posts as $post) {
            $reply_count = $post->reply_count_cache;
            $retweet_count = $post->retweet_count_cache;
            $fav_count = $post->favlike_count_cache;

            $popularity_index = Utils::getPopularityIndex($post);
            if (isset($popularity_counts[$popularity_index])) {
                $popularity_index = $popularity_index + 1;
            }
            $popularity_counts[$popularity_index] = $post;
        }

        krsort($popularity_counts);
        return array_slice($popularity_counts, 0, $limit);
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
$insights_plugin_registrar->registerInsightPlugin('EOYMostTalkativeDayInsight');
