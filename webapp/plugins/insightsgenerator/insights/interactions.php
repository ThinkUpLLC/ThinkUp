<?php
/*
 Plugin Name: Interactions
 Description: People you mentioned in your posts in the last week.
 When: Wednesdays for Twitter, Saturday otherwise
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/interactions.php
 *
 * Copyright (c) 2013-2014 Nilaksh Das, Gina Trapani
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013-2014 Nilaksh Das, Gina Trapani
 * @author Nilaksh Das <nilakshdas [at] gmail [dot] com>
 */

class InteractionsInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        if ($instance->network == 'twitter') {
            $day_of_week = 3;
        } else {
            $day_of_week = 6;
        }
        $should_generate_insight = self::shouldGenerateWeeklyInsight('interactions', $instance, $insight_date='today',
            $regenerate_existing_insight=false, $day_of_week=3, count($last_week_of_posts),
            $excluded_networks=array('facebook', 'google+', 'foursquare', 'youtube'));

        if ($should_generate_insight) {
            $user_dao = DAOFactory::getDAO('UserDAO');

            $mentions_count = array();
            $mentions_info = array();
            $mentions_avatars = array();
            $insight_data = array();
            $insight_text = '';

            if ($instance->network == 'twitter') {
                $talk_time = 15;
            } else {
                $talk_time = 38;
            }

            foreach ($last_week_of_posts as $post) {
                if ($post->in_reply_to_user_id && $instance->network_user_id != $post->in_reply_to_user_id) {
                    $mentioned_user = $user_dao->getDetails($post->in_reply_to_user_id, $instance->network);
                    if (isset($mentioned_user)) {
                        $mention_in_post = $instance->network == 'twitter' ? '@' : '';
                        $mention_in_post .= $mentioned_user->username;
                        $mentions_info[$mention_in_post] = $mentioned_user;
                        // Update mention count
                        if (array_key_exists($mention_in_post, $mentions_count)) {
                            $mentions_count[$mention_in_post]++;
                        } else {
                            $mentions_count[$mention_in_post] = 1;
                        }
                    }
                }
            }

           if (count($mentions_count)) {
                // Get most mentioned user
                arsort($mentions_count);
                $most_mentioned_user = each($mentions_count);

                // Add mentions to dataset
                $users_mentioned = array();
                foreach (array_slice($mentions_count, 0, 10) as $mention => $count) {
                    $mention_info['mention'] = $mention;
                    $mention_info['count'] = $count;
                    $mention_info['user'] = $mentions_info[$mention];
                    $users_mentioned[] = $mention_info;
                }
            }

            if (isset($most_mentioned_user) && ($talk_time * $most_mentioned_user['value']) >= 60) {
                $headline = $this->username." replied to ".$most_mentioned_user['key'] ." <strong>"
                    .$this->terms->getOccurrencesAdverb($most_mentioned_user['value'])."</strong> last week.";
                $conversation_seconds = $this->terms->getOccurrencesAdverb($most_mentioned_user['value']) * $talk_time;

                $milestones = $this->convertSecondsToMilestone($conversation_seconds);
                $insight_text = 'Time having good conversation is time well spent.';
                // $header_image = $users_mentioned[0][user]->avatar;
                $header_image = $users_mentioned[0]["user"]->avatar;

                //Instantiate the Insight object
                $my_insight = new Insight();

                //REQUIRED: Set the insight's required attributes
                $my_insight->instance_id = $instance->id;
                $my_insight->slug = 'interactions'; //slug to label this insight's content
                $my_insight->date = $this->insight_date; //date of the data this insight applies to
                $my_insight->headline = $headline;
                $my_insight->text = $insight_text;
                $my_insight->header_image = $header_image;
                $my_insight->emphasis = Insight::EMPHASIS_LOW;
                $my_insight->filename = basename(__FILE__, ".php");
                $my_insight->setPeople($users_mentioned);
                $my_insight->setMilestones($milestones);

                $this->insight_dao->insertInsight($my_insight);

            }
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }


    /**
     * Take some seconds and make a pretty milestone.
     * @param int $total_seconds How many seconds to covnert
     * @param int $total_drill_down How many units to drill down from days
     * @return array
     */
    protected function convertSecondsToMilestone($total_seconds, $total_drill_down = 2) {
        $secondsInAMinute = 60;
        $secondsInAnHour  = 60 * $secondsInAMinute;
        $secondsInADay    = 24 * $secondsInAnHour;

        // extract days
        $days = floor($total_seconds / $secondsInADay);

        // extract hours
        $hourSeconds = $total_seconds % $secondsInADay;
        $hours = floor($hourSeconds / $secondsInAnHour);

        // extract minutes
        $minuteSeconds = $hourSeconds % $secondsInAnHour;
        $minutes = floor($minuteSeconds / $secondsInAMinute);

        // extract the remaining seconds
        $remainingSeconds = $minuteSeconds % $secondsInAMinute;
        $seconds = ceil($remainingSeconds);

        // return the final array
        $obj = array(
            'd' => (int) $days,
            'h' => (int) $hours,
            'm' => (int) $minutes,
            's' => (int) $seconds,
        );

        $places = 0;
        $text = '';

        $milestones = array(
            "per_row"    => 2,
            "label_type" => "text",
            "items" => array(),
        );

        if ($obj["d"] && $places < $total_drill_down) {
            $milestones["items"][] = array(
                "number" => $obj["d"],
                "label"  => "days",
            );
            $places++;
        }
        if ($obj["h"] && $places < $total_drill_down) {
            $milestones["items"][] = array(
                "number" => $obj["h"],
                "label"  => "hours",
            );

            $places++;
        }
        if ($obj["m"] && $places < $total_drill_down) {
            if ($obj["m"] > 1) {
                $milestones["items"][] = array(
                    "number" => $obj["m"],
                    "label"  => "minutes",
                );
            } else {
                $milestones["items"][] = array(
                    "number" => $obj["m"],
                    "label"  => "minute",
                );
            }
            $places++;
        }
        if ($obj["s"] && $places < $total_drill_down) {
            $milestones["items"][] = array(
                "number" => $obj["s"],
                "label"  => "seconds",
            );
            $places++;
        }

        return $milestones;
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('InteractionsInsight');
