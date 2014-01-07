<?php
/*
 Plugin Name: Interactions
 Description: People you mentioned in your posts in the last week.
 When: Wednesdays
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/interactions.php
 *
 * Copyright (c) 2013 Nilaksh Das, Gina Trapani
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
 * @copyright 2013 Nilaksh Das, Gina Trapani
 * @author Nilaksh Das <nilakshdas [at] gmail [dot] com>
 */

require_once dirname(__FILE__).'/../../twitter/extlib/twitter-text-php/lib/Twitter/Extractor.php';

function secondsToTextTime($inputSeconds) {

    $secondsInAMinute = 60;
    $secondsInAnHour  = 60 * $secondsInAMinute;
    $secondsInADay    = 24 * $secondsInAnHour;

    // extract days
    $days = floor($inputSeconds / $secondsInADay);

    // extract hours
    $hourSeconds = $inputSeconds % $secondsInADay;
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
    return $obj;
}

class InteractionsInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        if (self::shouldGenerateInsight('interactions', $instance, $insight_date='today',
        $regenerate_existing_insight=false, $day_of_week=3, count($last_week_of_posts),
        $excluded_networks=array('facebook', 'google+', 'foursquare', 'youtube'))) {
            $user_dao = DAOFactory::getDAO('UserDAO');

            $mentions_count = array();
            $mentions_info = array();
            $insight_data = array();
            $insight_text = '';

            foreach ($last_week_of_posts as $post) {
                $post_text = $post->post_text;

                // Extract mentions from post text
                $text_parser = new Twitter_Extractor($post_text);
                $elements = $text_parser->extract();

                $mentions_in_post = $elements['mentions'];
                foreach ($mentions_in_post as $mention_in_post) {
                    if ($mention_in_post == $instance->network_username) {
                        // Don't count metweets
                        continue;
                    } else {
                        $mentioned_user = $user_dao->getUserByName($mention_in_post, $instance->network);
                        if (isset($mentioned_user)) {
                            $mention_in_post = '@'.$mentioned_user->username;
                            $mentions_info[$mention_in_post] = $mentioned_user;
                        } else {
                            $mention_in_post = '@'.$mention_in_post;
                        }

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
                foreach ($mentions_count as $mention => $count) {
                    $mention_info['mention'] = $mention;
                    $mention_info['count'] = $count;
                    $mention_info['user'] = $mentions_info[$mention];

                    $insight_data[] = $mention_info;
                }
            }

            if (isset($most_mentioned_user)) {
                $headline = $this->username." mentioned ".$most_mentioned_user['key']
                ." <strong>".$this->terms->getOccurrencesAdverb($most_mentioned_user['value'])."</strong> last week.";

                $conversation_seconds = $this->terms->getOccurrencesAdverb($most_mentioned_user['value']) * 15;

                $insight_text = "That's roughly";
                $conversation_time = secondsToTextTime($conversation_seconds);

                    if ($conversation_time["d"]) {
                        $insight_text .= ' ' . $conversation_time["d"] . ' days';
                    }
                    if ($conversation_time["h"]) {
                        $insight_text .= ' ' . $conversation_time["h"] . ' hours';
                    }
                    if ($conversation_time["m"]) {
                        $insight_text .= ' ' . $conversation_time["m"] . ' minutes';
                    }
                    if ($conversation_time["s"]) {
                        $insight_text .= ' ' . $conversation_time["s"] . ' seconds';
                    }

                    $insight_text .= ' spent chatting with each other.';

                $this->insight_dao->insertInsightDeprecated('interactions', $instance->id, $this->insight_date, $headline,
                $insight_text, basename(__FILE__, ".php"), Insight::EMPHASIS_LOW, serialize($insight_data));
            }
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('InteractionsInsight');