<?php
/*
 Plugin Name: Most replied-to/commented-on post (End of Year)
 Description: User's most replied-to/commented-on post in current year.
 When: Annually on December 11
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/eoymostconversation.php
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
 * EOYMostConversation
 *
 * Copyright (c) 2014-2016 Adam Pash
 *
 * @author Adam Pash adam.pash@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014-2016 Adam Pash
 */

class EOYMostConversationInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     **/
    var $slug = 'eoy_most_conversation';
    /**
     * Date to run this insight
     **/
    var $run_date = '12-11';
    //staging
    //var $run_date = '12-05';

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

            $top_three_replied_to = $this->topThreeThisYear($instance);
            foreach ($top_three_replied_to as $key => $post) {
                $post->author_avatar = $user->avatar;
            }

            //Populate Instagram photos
            if ($instance->network == 'instagram') {
                $photo_dao = DAOFactory::getDAO('PhotoDAO');
                $popular_photos = array();
                foreach ($top_three_replied_to as $key => $post) {
                    $post = $photo_dao->getPhoto($post->post_id, 'instagram');
                    $popular_photos[] = $post;
                }
                $top_three_replied_to = $popular_photos;
            }

            $post_dao = DAOFactory::getDAO('PostDAO');
            $earliest_pub_date = $post_dao->getEarliestCapturedPostPubDate($instance);
            $qualified_year = "";
            if ( date('Y', strtotime($earliest_pub_date)) == date('Y') ) {
                if ( date('n', strtotime($earliest_pub_date)) > 1 ) { //not January
                    //Earliest post was this year; figure out what month we have data since this year
                    $since = date('F', strtotime($earliest_pub_date));
                    $qualified_year = " (at least since ".$since.")";
                }
            }

            $copy = array(
                'twitter' => array(
                    'normal' => array(
                        'headline' => "%username's most replied-to tweets of %year",
                        'body' => "Come for the faves, stay for the mentions. " .
                        "In %year, %username inspired the most conversation in " .
                        "these tweets%qualified_year."
                    ),
                    'one' => array(
                        'headline' => "%username's most replied-to tweet of %year",
                        'body' => "Come for the faves, stay for the mentions. " .
                        "In %year, %username inspired the most conversation in " .
                        "this tweet%qualified_year."
                    ),
                    'none' => array(
                        'headline' => "Let's talk",
                        'body' => "%username didn't get any replies in %year".$qualified_year.", but that's " .
                        "about to change. Give @thinkup a mention — we love to talk!"
                    ),
                ),
                'facebook' => array(
                    'normal' => array(
                        'headline' => "%username's most commented-on status updates of %year",
                        'body' => "Some status updates are meant to " .
                        "be trivial. Others sow the seeds of meaningful " .
                        "conversation. In %year, %username received the most comments " .
                        "on these status updates%qualified_year."
                    ),
                    'one' => array(
                        'headline' => "%username's most commented-on status update of %year",
                        'body' => "Some status updates are meant to " .
                        "be trivial. Others sow the seeds of meaningful " .
                        "conversation. In %year, %username received the most comments " .
                        "on this status update%qualified_year."
                    ),
                    'none' => array(
                        'headline' => "No comment",
                        'body' => "Is this thing on? No one commented on %username's " .
                        "status updates on Facebook in %year".$qualified_year."."
                    ),
                ),
                'instagram' => array(
                    'normal' => array(
                        'headline' => "%username's most commented-on Instagram posts of %year",
                        'body' => "There's lots of eye candy on Instagram, and not much conversation. " .
                        "But some photos and videos are so good people can't help but respond. " .
                        "In %year, %username received the most comments " .
                        "on these Instagram posts%qualified_year."
                    ),
                    'one' => array(
                        'headline' => "%username's most commented-on Instagram of %year",
                        'body' => "There's lots of eye candy on Instagram, and not much conversation. " .
                        "But some photos and videos are so good people can't help but respond. " .
                        "In %year, %username received the most comments " .
                        "on these Instagram posts%qualified_year."
                    ),
                    'none' => array(
                        'headline' => "No comment",
                        'body' => "Is this thing on? No one commented on %username's " .
                        "Instagram photos and videos in %year".$qualified_year."."
                    ),
                )
            );

            $network = $instance->network;

            if (sizeof($top_three_replied_to) > 1) {
                $type = 'normal';
            } else if (sizeof($top_three_replied_to) == 1) {
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
                    'year' => $year,
                    'qualified_year' => $qualified_year
                )
            );

            $insight->emphasis = Insight::EMPHASIS_HIGH;
            $insight->filename = basename(__FILE__, ".php");
            $insight->setPosts($top_three_replied_to);
            $this->insight_dao->insertInsight($insight);

            $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
        }
    }


    /**
     * Get three most replied to posts this year
     * @param Instance $instance
     * @param str $order Defaults to 'reply_count_cache'
     * @return array Three most replied to posts in descending order
     */
    public function topThreeThisYear(Instance $instance, $order='reply_count_cache') {
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
$insights_plugin_registrar->registerInsightPlugin('EOYMostConversationInsight');

