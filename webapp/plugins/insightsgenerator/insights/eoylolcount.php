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
 * Copyright (c) 2012-2014 Gina Trapani
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
 * Copyright (c) 2014 Adam Pash
 *
 * @author Adam Pash adam.pash@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Adam Pash
 */

class EOYLOLCountInsight extends InsightPluginParent implements InsightPlugin {
// class EOYLOLCountInsight extends CriteriaMatchInsightPluginParent implements InsightPlugin {

    /**
     * @var array Popularity scores of LOLed at posts
     */
    var $scores = array();

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $slug = 'eoy_lol_count';
        $date = '12-13';
        $year = date('Y');

        $should_generate_insight = self::shouldGenerateEndOfYearAnnualInsight(
            $slug,
            $instance,
            $insight_date = "$year-$date",
            false,
            $day_of_year = $date
        );

        if ($should_generate_insight) {
            $insight = new Insight();
            $insight->instance_id = $instance->id;
            $insight->slug = $slug;
            $insight->date = date('Y-m-d');
            $insight->eoy = true;

            $count = 0;
            $post_dao = DAOFactory::getDAO('PostDAO');
            $network = $instance->network;

            $post_dao = DAOFactory::getDAO('PostDAO');
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

            $copy = array(
                'twitter' => array(
                    'normal' => array(
                        'headline' => "omg lol @twitter, %year",
                        'body' => array(
                            'normal' => "%username found %total things to LOL about on " .
                                "Twitter in %year, including these LOLed-at tweets.",
                            'one' => "%username found %total things to LOL about on " .
                                "Twitter in %year, including this LOLed-at tweet.",
                            'none' => "%username found %total things to LOL about on " .
                                "Twitter in %year. Not a bad year!",
                        )
                    ),
                    'one' => array(
                        'headline' => "Funny, but rarely LOL funny",
                        'body' => array(
                            'normal' => "%username found 1 thing to LOL about on " .
                                "Twitter in %year."
                        )
                    )
                ),
                'facebook' => array(
                    'normal' => array(
                        'headline' => "The LOLs of Facebook, %year",
                        'body' => array(
                            'normal' => "ROFL. %username LOLed at %total things on Facebook " .
                                "in %year, including these LOL-worthy status updates.",
                            'one' => "ROFL. %username LOLed at %total things on Facebook " .
                                "in %year, including this LOL-worthy status update.",
                            'none' => "ROFL. %username LOLed at %total things on Facebook " .
                                "in %year. Gotta love a good LOL.",
                        )
                    ),
                    'one' => array(
                        'headline' => "The LOLs of Facebook, %year",
                        'body' => array(
                            'normal' => "%username LOLed once on Facebook " .
                                "in %year. Not the funniest of years."
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
                    'year' => $year
                )
            );

            $insight->headline = $headline;
            $insight->text = $insight_text;
            $insight->setPosts($most_popular_lolees);
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

    public function getMaxMonth($point_chart) {
        $short_month = array_search(max($point_chart),$point_chart);
        return date('F', strtotime("$short_month 1 2014"));
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
