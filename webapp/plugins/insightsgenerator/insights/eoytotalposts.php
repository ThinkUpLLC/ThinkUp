<?php
/*
 Plugin Name: Total posts this year (End of Year)
 Description: How many times the user posted this year
 When: Annually on December 19
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/eoytotalposts.php
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
 * EOYTotalPosts (name of file)
 *
 * Description of what this class does
 *
 * Copyright (c) 2014 Adam Pash
 *
 * @author Adam Pash adam.pash@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Adam Pash
 */

class EOYTotalPostsInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        $slug = 'eoy_total_posts';
        $date = '12-19';
        $year = date('Y');

        $should_generate_insight = self::shouldGenerateEndOfYearAnnualInsight(
            $slug,
            $instance,
            $insight_date = "$year-$date",
            false,
            $day_of_year = $date
        );
        if ($should_generate_insight) {
            parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
            $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
            $filename = basename(__FILE__, ".php");

            $insight = new Insight();
            $insight->instance_id = $instance->id;
            $insight->slug = 'eoy_total_posts';
            $insight->date = date('Y-m-d');
            $insight->eoy = true;

            $copy = array(
                'twitter' => array(
                    'normal' => array(
                        'headline' => "%username tweeted %total times in %year",
                        'body' => "In %year, %username put keyboard to tweet box " .
                        "a total of <b>%total times</b>, amounting to over <b>%time</b> " .
                        "of %username's life."
                    ),
                    'multiple' => array(
                        'headline' => "%username's most talkative day on Twitter in %year",
                        'body' => "In the running for %username's most talkative day " .
                        "on Twitter, %year, we've got a tie: %username tweeted " .
                        "<b>%total times on %talkative_date</b> — more than on any other " .
                        "days this year. These are %username's most popular tweets " .
                        "from each day."
                    )
                ),
                'facebook' => array(
                    'normal' => array(
                        'headline' => "%username posted to Facebook %total times in %year",
                        'body' => "This year, %username posted a grand total of " .
                        "<b>%total times</b> to Facebook. That's over <b>%time</b> " .
                        "dedicated to keeping in touch with friends."
                    ),
                    'multiple' => array(
                        'headline' => "%username's most talkative day on Facebook in %year",
                        'body' => "In the running for %username's most talkative day " .
                        "on Facebook, %year, we've got a tie: %username posted " .
                        "<b>%total times on %talkative_date</b> — more than on any other " .
                        "days this year. These are %username's most popular " .
                        "status updates from each day."
                    )
                )
            );

            $network = $instance->network;
            $total_post_count = $this->getTotalPostCount($instance, $year);
            $posting_time = $this->getPostingTime($total_post_count);

            if ($total_post_count > 1) {
                $type = 'normal';
            } else {
                return;
            }

            $insight->headline = $this->getVariableCopy(
                array(
                    $copy[$network][$type]['headline']
                ),
                array(
                    'total' => $total_post_count,
                    'year' => $year
                )
            );
            $insight->text = $this->getVariableCopy(
                array(
                    $copy[$network][$type]['body']
                ),
                array(
                    'total' => $total_post_count,
                    'year' => $year,
                    'time' => $posting_time
                )
            );

            $insight->emphasis = Insight::EMPHASIS_HIGH;
            $insight->filename = $filename;

            $milestones = array(
                "label_type" => "icon",
                "items" => array(
                    0 => array(
                        "number" => $total_post_count,
                        "label"  => $this->terms->getNoun('post', $count),
                    ),
                ),
            );
            $insight->setMilestones($milestones);

            $this->insight_dao->insertInsight($insight);
            $insight = null;

            $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
        }
    }


    /**
     * Get the most talkative day(s) on network
     * @param str $network Defaults to 'twitter'
     * @return array Most talkative day or days
     */
    public function getTotalPostCount(Instance $instance, $year) {
        $post_dao = DAOFactory::getDAO('PostDAO');

        $total_post_count = $post_dao->getPostCountForYear(
            $instance->network_username,
            $network = $instance->network,
            $year = $year
        );

        return $total_post_count['post_count'];
    }

    public function getPostingTime($total_posts) {
        $posting_seconds = ($total_posts * 15);
        $time_array = array();

        $posting_time = TimeHelper::secondsToExactTime($posting_seconds);
        if ($posting_time["d"]) {
            $time_array[] = $posting_time["d"] . ' day'.(($posting_time["d"]>1)?'s':'');
        }
        if ($posting_time["h"]) {
            $time_array[] = $posting_time["h"] . ' hour'.(($posting_time["h"]>1)?'s':'');
        }
        if ($posting_time["m"]) {
            $time_array[] = $posting_time["m"] . ' minute'.(($posting_time["m"]>1)?'s':'');
        }
        return $this->makeList($time_array);
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
$insights_plugin_registrar->registerInsightPlugin('EOYTotalPostsInsight');
