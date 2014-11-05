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
 * EOYMostConversation (name of file)
 *
 * Description of what this class does
 *
 * Copyright (c) 2014 Adam Pash
 *
 * @author Adam Pash adam.pash@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Adam Pash
 */

class EOYMostConversationInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        $slug = 'eoy_most_conversation';
        $date = '12-11';
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
            $insight->slug = $slug;
            $insight->date = date('Y-m-d');
            $insight->eoy = true;

            $top_three_replied_to = $this->topThreeThisYear($instance);
            foreach ($top_three_replied_to as $key => $post) {
                $post->count = $post->reply_count_cache . " " .
                    $this->terms->getNoun('reply', $post->reply_count_cache > 1);
            }

            $copy = array(
                'twitter' => array(
                    'normal' => array(
                        'headline' => "%username's most replied-to tweet of %year",
                        'body' => "Come for the faves, stay for the mentions. " .
                        "In %year, %username inspired the most conversation in " .
                        "these tweets."
                    ),
                    'one' => array(
                        'headline' => "%username's most replied-to tweet of %year",
                        'body' => "Come for the faves, stay for the mentions. " .
                        "In %year, %username inspired the most conversation in " .
                        "this tweet."
                    ),
                    'none' => array(
                        'headline' => "Let's talk",
                        'body' => "%username didn't get any replies in %year, but that's " .
                        "about to change. Give @thinkup a mention — we love to talk!"
                    ),
                ),
                'facebook' => array(
                    'normal' => array(
                        'headline' => "%username's most commented-on status update of %year",
                        'body' => "Some status updates are meant to " .
                        "be trivial. Others sew the seeds of meaningful " .
                        "conversation. In %year, %username received the most comments " .
                        "on these status updates."
                    ),
                    'one' => array(
                        'headline' => "%username's most commented-on status update of %year",
                        'body' => "Some status updates are meant to " .
                        "be trivial. Others sew the seeds of meaningful " .
                        "conversation. In %year, %username received the most comments " .
                        "on this status update."
                    ),
                    'none' => array(
                        'headline' => "No comment",
                        'body' => "Is this thing on? No one commented on %username's " .
                        "status updates on Facebook in %year."
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
                    'year' => $year
                )
            );

            $insight->emphasis = Insight::EMPHASIS_HIGH;
            $insight->filename = $filename;

            $insight->setPosts($top_three_replied_to);

            $this->insight_dao->insertInsight($insight);
            $insight = null;

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

