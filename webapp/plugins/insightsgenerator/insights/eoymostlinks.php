<?php
/*
 Plugin Name: Most linked-to site (End of Year)
 Description: The site you linked to the most this year
 When: December 12
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/eoymostlinks.php
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

class EOYMostLinksInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     **/
    var $slug = 'eoy_most_links';
    /**
     * Date to run this insight
     **/
    var $run_date = '12-14';
    //staging
    //var $run_date = '12-05';

    var $posts_by_domain = array();

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

            $post_dao = DAOFactory::getDAO('PostDAO');
            $network = $instance->network;

            $last_year_of_posts = $post_dao->getThisYearOfPostsWithLinksIterator(
                $author_id = $instance->network_user_id,
                $network = $network
            );
            $domain_counts = $this->getDomainCounts($last_year_of_posts);
            $this->logger->logInfo("Got domain counts "/*.Utils::varDumpToString($domain_counts)*/,
                __METHOD__.','.__LINE__);

            $popular_domain = $this->getPopularDomain($domain_counts);
            $this->logger->logInfo("Got popular domain ".Utils::varDumpToString($popular_domain),
                __METHOD__.','.__LINE__);

            $posts = $this->getMostPopularPostsLinkingTo($instance, $popular_domain);
            $this->logger->logInfo("Got popular posts linking to ", __METHOD__.','.__LINE__);

            $most_recent_unexpanded_link_date = $post_dao->getMostRecentUnexpandedLinkPubDate($instance);
            if (isset($most_recent_unexpanded_link_date)) {
                $this->logger->logInfo("Most recent unexpanded link date is ".$most_recent_unexpanded_link_date." - ".
                    date('Y-m-d', strtotime($most_recent_unexpanded_link_date)), __METHOD__.','.__LINE__);
            } else {
                $this->logger->logInfo("No links have gone unexpanded ", __METHOD__.','.__LINE__);
            }
            $qualified_year = "";
            if ( isset($most_recent_unexpanded_link_date)
                && date('Y', strtotime($most_recent_unexpanded_link_date)) == date('Y') ) {
                if ( date('n', strtotime($most_recent_unexpanded_link_date)) > 1 ) { //not January
                    //Earliest post was this year; figure out what month we have data since this year
                    $since = date('F', strtotime($most_recent_unexpanded_link_date));
                    $qualified_year = " (at least since ".$since.")";
                }
            }

            $copy = array(
                'twitter' => array(
                    'normal' => array(
                        'headline' => "ICYMI: %username's most linked-to site of $year",
                        'body' => "What's Twitter without the tabs? In $year, %username " .
                            "shared more #content from <strong>%domain</strong> than from any other web " .
                            "site%qualified_year. These were %username's most popular tweets with a link to " .
                            "<strong>%domain</strong>."
                    ),
                    'one' => array(
                        'headline' => "ICYMI: %username's most linked-to site of $year",
                        'body' => "What's Twitter without the tabs? In $year, %username " .
                            "shared more #content from <strong>%domain</strong> than from any other web " .
                            "site%qualified_year. This was %username's most popular tweet with a link to ".
                            "<strong>%domain</strong>."
                    ),
                    'none' => array(
                        'headline' => "%username tweeted nary a link in $year",
                        'body' => "This year, %username didn't post a single link on " .
                            "Twitter%qualified_year. You can do better than that, internet!"
                    )
                ),
                'facebook' => array(
                    'normal' => array(
                        'headline' => "%username's most-shared site of $year",
                        'body' => "Looks like <strong>%domain</strong> owes %username a thank you. In " .
                            "$year, %username directed friends to <strong>%domain</strong> more than " .
                            "to any other site%qualified_year. Here are the posts with links to ".
                            "<strong>%domain</strong>."
                    ),
                    'one' => array(
                        'headline' => "%username's most-shared site of $year",
                        'body' => "Looks like <strong>%domain</strong> owes %username a thank you. In " .
                            "$year, %username directed friends to <strong>%domain</strong> more than " .
                            "to any other site%qualified_year. Here is the post that links to <strong>%domain</strong>."
                    ),
                    'none' => array(
                        'headline' => "%username shared no links in $year",
                        'body' => "%username didn't share any links on Facebook this year%qualified_year. " .
                            "Maybe the internet will do better in 2015!"
                    )
                )
            );

            if (sizeof($posts) > 1) {
                $type = 'normal';
            } else if (sizeof($posts) == 1) {
                $type = 'one';
            } else {
                $type = 'none';
                //Don't show this insight if there are no multiple posts linking to a single popular domain
                return;
            }
            $headline = $this->getVariableCopy(
                array(
                    $copy[$network][$type]['headline']
                )
            );

            $insight_text = $this->getVariableCopy(
                array(
                    $copy[$network][$type]['body']
                ),
                array(
                    'domain' => str_replace('www.', '', $popular_domain),
                    'qualified_year' => $qualified_year
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
     * Get at most three most popular posts that linked to this domain
     * @param Instance $instance
     * @param String $domain
     * @return array $posts
     */
    public function getMostPopularPostsLinkingTo(Instance $instance, $domain) {
        // debug
        // if (!isset($this->logger)) {
        //     $this->logger = Logger::getInstance();
        // }
        $posts = array();
        // $this->logger->logInfo("Posts by domain ".Utils::varDumpToString($this->posts_by_domain),
        //     __METHOD__.','.__LINE__);

        $post_ids = $this->posts_by_domain[$domain];
        // $this->logger->logInfo("Post IDs ".Utils::varDumpToString($post_ids),
        //     __METHOD__.','.__LINE__);
        $post_dao = DAOFactory::getDAO('PostDAO');

        foreach ($post_ids as $post_id) {
            $post = $post_dao->getPost($post_id, $instance->network);
            $popularity_index = Utils::getPopularityIndex($post);
            if (isset($posts[$popularity_index])) {
                $popularity_index++;
            }
            $posts[$popularity_index] = $post;
        }
        krsort($posts);
        return array_slice($posts, 0, 3);
    }

    /**
     * Get counts for domains in last year of posts with links.
     * @param array $posts
     * @return array $domain_counts
     */
    public function getDomainCounts($posts) {
        //debug
        // if (!isset($this->logger)) {
        //     $this->logger = Logger::getInstance();
        // }
        $total_counts = array();
        $tweet_counts = array();
        $retweet_counts = array();
        foreach ($posts as $post) {
            $is_retweet = isset($post->in_retweet_of_post_id);
            $post_id = $post->post_id;
            $network = $post->network;
            foreach ($post->links as $link) {
                if ($link->expanded_url == ""  || $this->isIntraNetwork($link->expanded_url, $network)) {
                    // $this->logger->logInfo("Skipping link ID ".$link->id." with expanded URL ". $link->expanded_url,
                    //     __METHOD__.','.__LINE__);
                    continue;
                } else {
                    //$this->logger->logInfo("Processing url ".$link->expanded_url, __METHOD__.','.__LINE__);
                    $url = parse_url($link->expanded_url);
                    $domain = $url['host'];
                }
                if(!array_key_exists($domain, $total_counts)) {
                    $total_counts[$domain] = 0;
                }
                if(!array_key_exists($domain, $this->posts_by_domain)) {
                    $this->posts_by_domain[$domain] = array();
                }
                if(!array_key_exists($domain, $tweet_counts)) {
                    $tweet_counts[$domain] = 0;
                }
                if(!array_key_exists($domain, $retweet_counts)) {
                    $retweet_counts[$domain] = 0;
                }
                $total_counts[$domain]++;
                if ($is_retweet) {
                    $retweet_counts[$domain]++;
                } else {
                    $tweet_counts[$domain]++;
                    $this->posts_by_domain[$domain][] = $post_id;
                }
            }
        }
        arsort($total_counts);
        return $total_counts;
    }

    /**
     * Get most popular domain from array of linked-to domains
     * @param array $domain_counts
     * @return String $popular_url
     */
    public function getPopularDomain($domain_counts) {
        $popular_url = array_search(max($domain_counts), $domain_counts);
        return $popular_url;
    }

    /**
     * Is this a link to the originating network?
     * @param  str  $url
     * @param  str  $network
     * @return bool
     */
    public function isIntraNetwork($url, $network) {
        if ($network == 'twitter') {
            return preg_match('/twitter.com/', $url);
        } elseif ($network = 'facebook') {
            return preg_match("/facebook.com/", $url);
        }
        return false;
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('EOYMostLinksInsight');

