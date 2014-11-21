<?php
/*
 Plugin Name: Most faved/liked post (End of Year)
 Description: User's most faved/liked post in current year.
 When: Annually on December 1
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/eoymostfavlikedpost.php
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
 * EOYMostFavlikedPost (name of file)
 *
 * Description of what this class does
 *
 * Copyright (c) 2014 Adam Pash
 *
 * @author Adam Pash adam.pash@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Adam Pash
 */

class EOYMostFavlikedPostInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        $slug = 'eoy_most_favliked';
        $date = '12-01';
        $year = date('Y');

        $should_generate_insight = self::shouldGenerateEndOfYearAnnualInsight(
            $slug,
            $instance,
            $insight_date = "$year-$date",
            false,
            $day_of_year = $date
        );
        if ($should_generate_insight) {
            parent::generateInsight($instance, $last_week_of_posts, $number_days);
            $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
            $filename = basename(__FILE__, ".php");

            $insight = new Insight();
            $insight->instance_id = $instance->id;
            $insight->slug = $slug;
            $insight->date = date('Y-m-d');
            $insight->eoy = true;

            $top_three_favliked = $this->topThreeThisYear($instance);
            foreach ($top_three_favliked as $key => $post) {
                if ($post->favlike_count_cache == 0) {
                    unset($top_three_favliked[$key]);
                } else {
                    $post->count = $post->favlike_count_cache . " " .
                        $this->terms->getNoun('favlike', $post->favlike_count_cache > 1);
                }
            }

            $copy = array(
                'twitter' => array(
                    'normal' => array(
                        'headline' => "%username's most-faved tweet of %year",
                        'body' => "In the Walk of Fame that is %username's Twitter " .
                            "stream, these fan favorites earned the most stars in %year."
                    ),
                    'one' => array(
                        'headline' => "%username's most-faved tweet of %year",
                        'body' => "In the Walk of Fame that is %username's Twitter " .
                            "stream, this fan favorite earned the most stars in %year."
                    ),
                    'none' => array(
                        'headline' => "What's in a fave?",
                        'body' => "%username didn't get any faves in %year, which is " .
                        "crazy! Give @thinkup a mention and we'd be happy to " .
                        "change that."
                    ),
                ),
                'facebook' => array(
                    'normal' => array(
                        'headline' => "%username's most-liked status update of %year",
                        'body' => "Liked it? Nah. They LOVED it. These status updates " .
                            "had %username's friends mashing the thumbs-up button the " .
                            "most in %year."
                    ),
                    'one' => array(
                        'headline' => "%username's most-liked status update of %year",
                        'body' => "Liked it? Nah. They LOVED it. This status update " .
                            "had %username's friends mashing the thumbs-up button the " .
                            "most in %year."
                    ),
                    'none' => array(
                        'headline' => "Like, what's the deal?",
                        'body' => "No one liked %username's status updates on Facebook " .
                            "in %year, but no biggie: We like %username plenty."
                    ),
                )
            );

            $network = $instance->network;

            if (sizeof($top_three_favliked) > 1) {
                $type = 'normal';
            } else if (sizeof($top_three_favliked) == 1) {
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

            $insight->setPosts($top_three_favliked);

            $this->insight_dao->insertInsight($insight);
            $insight = null;

            $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
        }
    }


    /**
     * Get three most favliked posts this year
     * @param Instance $instance
     * @param str $order Defaults to 'favlike'
     * @return array Three most favliked posts in descending order
     */
    public function topThreeThisYear(Instance $instance, $order='favlike') {
        $post_dao = DAOFactory::getDAO('PostDAO');
        $days = Utils::daysSinceJanFirst();

        $posts = $post_dao->getAllPostsByUsernameOrderedBy(
            $instance->network_username,
            $network=$instance->network,
            $count=3,
            $order_by=$order,
            $in_last_x_days = $days,
            $iterator = false
        );
        return $posts;
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('EOYMostFavlikedPostInsight');
