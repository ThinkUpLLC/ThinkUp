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
 * Copyright (c) 2014 Adam Pash
 *
 * @author Adam Pash adam.pash@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Adam Pash
 */

class EOYMostRetweetedPostInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {

        $slug = 'eoy_most_retweeted';
        $date = '12-08';
        $year = date('Y');

        $should_generate_insight = self::shouldGenerateEndOfYearAnnualInsight(
            $slug,
            $instance,
            $insight_date = "$year-$date",
            false,
            $day_of_year = $date,
            $excluded_networks = array('facebook')
        );
        if ($should_generate_insight) {
            parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
            $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
            $filename = basename(__FILE__, ".php");

            $insight = new Insight();
            $insight->instance_id = $instance->id;
            $insight->slug = $slug;
            $insight->date = date('Y-m-d');
            $insight->eoy = true;

            $top_three_shared = $this->topThreeThisYear($instance);

            $copy = array(
                'twitter' => array(
                    'normal' => array(
                        'headline' => "%username's most-retweeted tweet of %year",
                        'body' => "Tweet, retweet, repeat. In %year, " .
                            "%username earned the most retweets for these gems."
                    ),
                    'one' => array(
                        'headline' => "%username's most-retweeted tweet of %year",
                        'body' => "Tweet, retweet, repeat. In %year, " .
                            "%username earned the most retweets for this gem."
                    ),
                    'none' => array(
                        'headline' => "Retweets aren't everything",
                        'body' => "%username didn't get any retweets in %year, " .
                            "which is a-okay. We're not all here to broadcast."
                    ),
                ),
                'facebook' => array(
                    'normal' => array(
                        'headline' => "%username's most-shared status update of %year",
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
                    'year' => $year
                )
            );

            $insight->emphasis = Insight::EMPHASIS_HIGH;
            $insight->filename = $filename;

            foreach ($top_three_shared as $post) {
                $post->count = $post->retweet_count_cache . " " .
                    $this->terms->getNoun('retweet', $post->retweet_count_cache > 1);
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
    public function topThreeThisYear(Instance $instance, $order='retweets') {
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
        return $posts;
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('EOYMostRetweetedPostInsight');
