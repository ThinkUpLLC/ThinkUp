<?php
/*
 Plugin Name: Most popular post per month (End of Year)
 Description: Your most popular post each month this year.
 When: December 30
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/eoymostpopularkpermonth.php
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

class EOYMostPopularPerMonthInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     **/
    var $slug = 'eoy_most_popular_per_month';
    /**
     * Date to run this insight
     **/
    var $run_date = '12-30';
    //staging
    //var $run_date = '12-19';
    /**
     * Popular posts per month
     * @var array [month int] => array('post_id'=>x, 'popularity_index'=>y)
     */
    var $posts_per_month;

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

            $i = 1;
            while ($i < 13) {
                $this->posts_per_month[$i] = array('post_id'=>null, 'popularity_index'=>0);
                $i++;
            }

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

            $this->setScoredPosts($last_year_of_posts);

            $earliest_pub_date = $post_dao->getEarliestCapturedPostPubDate($instance);
            $qualified_year = $year.".";
            if ( date('Y', strtotime($earliest_pub_date)) == date('Y') ) {
                if ( date('n', strtotime($earliest_pub_date)) > 1 ) { //not January
                    //Earliest post was this year; figure out what month we have data since this year
                    $since = date('F', strtotime($earliest_pub_date));
                    $qualified_year = $year." (at least since ".$since.").";
                    $cut_off_posts = (date('n', strtotime($earliest_pub_date)) - 1);
                    $this->posts_per_month = array_slice($this->posts_per_month, $cut_off_posts);
                }
            }

            $top_posts = $this->getMostPopularPosts($instance);
            if (count($top_posts) < 3) {
                $this->logger->logInfo("Not enough top posts per month to generate", __METHOD__.','.__LINE__);
                return;
            }

            //Populate Instagram photos
            if ($instance->network == 'instagram') {
                $photo_dao = DAOFactory::getDAO('PhotoDAO');
                $popular_photos = array();
                foreach ($top_posts as $key => $post) {
                    $post = $photo_dao->getPhoto($post->post_id, 'instagram');
                    $popular_photos[] = $post;
                }
                $top_posts = $popular_photos;
            }

            $copy = array(
                'twitter' => array(
                    'normal' => array(
                        'headline' => "%username's biggest tweets of each month in %year",
                        'body' => "Twelve months make a year, and this year's almost behind us. Take one last look ".
                            "back at %username's biggest tweets of each month in %qualified_year"
                    ),
                ),
                'facebook' => array(
                    'normal' => array(
                        'headline' => "%username's biggest posts of each month in %year",
                        'body' => "This year's about to enter the history books. For better or for worse, these were ".
                            "%username's most popular status updates of each month of %qualified_year"
                    ),
                ),
                'instagram' => array(
                    'normal' => array(
                        'headline' => "%username's biggest Instagram posts of each month in %year",
                        'body' => "The calendar is about to flip to a new year. Before it does, check out ".
                            "%username's most popular Instagram photos and videos of each month of %qualified_year"
                    ),
                )
            );

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
                    'qualified_year' => $qualified_year
                )
            );

            $insight->headline = $headline;
            $insight->text = $insight_text;
            $insight->setPosts($top_posts);
            $insight->filename = basename(__FILE__, ".php");
            $insight->emphasis = Insight::EMPHASIS_HIGH;

            $this->insight_dao->insertInsight($insight);
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

    /**
     * Get popular posts of each month.
     * @param Instance $instance
     * @return array $posts
     */
    public function getMostPopularPosts(Instance $instance) {
        $post_dao = DAOFactory::getDAO('PostDAO');
        $link_dao = DAOFactory::getDAO('LinkDAO');
        $top_posts = array();
        foreach ($this->posts_per_month as $post_for_month) {
            if (isset($post_for_month['post_id'])) {
                $top_post = $post_dao->getPost($post_for_month['post_id'], $instance->network);
                $top_post->links = $link_dao->getLinksForPost($top_post->post_id, $top_post->network);
                $top_posts[] = $top_post;
            }
        }
        return $top_posts;
    }

    /**
     * Score monthly posts.
     * @param PostIterator $last_year_of_posts
     * @return void
     */
    public function setScoredPosts($last_year_of_posts) {
        $scored_posts = array();
        foreach ($last_year_of_posts as $post) {
            $popularity_index = Utils::getPopularityIndex($post);
            $month = date('n', strtotime($post->pub_date));
            if ($popularity_index > $this->posts_per_month[$month]['popularity_index']) {
                $this->posts_per_month[$month]['post_id'] = $post->post_id;
                $this->posts_per_month[$month]['popularity_index'] = $popularity_index;
            }
        }
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('EOYMostPopularPerMonthInsight');
