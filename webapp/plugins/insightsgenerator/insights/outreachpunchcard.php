<?php
/*
 Plugin Name: Outreach Punchcard
 Description: How best can you time your posts to get best outreach.
 When: Mondays
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/outreachpunchcard.php
 *
 * Copyright (c) 2012-2013 Nilaksh Das, Gina Trapani
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
 * @copyright 2012-2013 Nilaksh Das, Gina Trapani
 * @author Nilaksh Das <nilakshdas [at] gmail [dot] com>
 */

class OutreachPunchcardInsight extends InsightPluginParent implements InsightPlugin {
    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $in_test_mode =  ((isset($_SESSION["MODE"]) && $_SESSION["MODE"] == "TESTS") || getenv("MODE")=="TESTS");
        //Only insert this insight if it's Monday or if we're testing
        if ((date('w') == 1 || $in_test_mode) && count($last_week_of_posts)) {
            $post_dao = DAOFactory::getDAO('PostDAO');
            $quickest_response_timediffs = array();
            $punchcard = array();
            for ($i = 1; $i <= 7; $i++) {
                for ($j = 0; $j < 24; $j++) {
                    $punchcard['posts'][$i][$j] = 0;
                    $punchcard['responses'][$i][$j] = 0;
                }
            }

            foreach ($last_week_of_posts as $post) {
                $responses = array();
                $responses = array_merge(
                    (array)$post_dao->getRepliesToPost($post->post_id, $post->network),
                    (array)$post_dao->getRetweetsOfPost($post->post_id, $post->network)
                );

                if (count($responses)) {
                    $quickest_response_timediff = strtotime($responses[0]->pub_date) - strtotime($post->pub_date);

                    foreach ($responses as $response) {
                        $response_dotw = date('N', strtotime($response->pub_date)); // Day of the week
                        $response_hotd = date('G', strtotime($response->pub_date)); // Hour of the day
                        $punchcard['responses'][$response_dotw][$response_hotd]++;

                        $response_timediff = strtotime($response->pub_date) - strtotime($post->pub_date);
                        $quickest_response_timediff = min($response_timediff, $quickest_response_timediff);
                    }

                    $quickest_response_timediffs[] = $quickest_response_timediff;
                }

                $post_dotw = date('N', strtotime($post->pub_date)); // Day of the week
                $post_hotd = date('G', strtotime($post->pub_date)); // Hour of the day
                $punchcard['posts'][$post_dotw][$post_hotd]++;
            }

            $insight_text = '';
            switch ($instance->network) {
                case 'twitter':
                    $post_type = 'tweets';
                    $connections = 'followers';
                    break;

                case 'facebook':
                    $post_type = 'status updates';
                    $connections = 'friends';
                    break;

                case 'google+':
                    $post_type = 'posts';
                    $connections = 'circles';
                    break;

                case 'foursquare':
                    $post_type = 'checkins';
                    $connections = 'friends';
                    break;
                
                default:
                    $post_type = 'posts';
                    $connections = 'friends';
                    break;
            }            
            if (count($quickest_response_timediffs)) {
                // We have responses
                $avg_timediff = floor(array_sum($quickest_response_timediffs) / count($quickest_response_timediffs));
                $syntactic_avg_timediff = self::getSyntacticTimeDifference($avg_timediff);

                if ($avg_timediff < (60 * 60)) {
                    // Got responses within 1 hour
                    $insight_text = $this->username."'s ".$post_type." from last week started getting responses "
                    ."in as little as ".$syntactic_avg_timediff." of being posted. "
                    ."This is a brilliant way to space updates and achieve the maximum outreach!";
                } else if ($avg_timediff < (6 * 60 * 60)) {
                    // Got responses within 6 hours
                    $insight_text = $this->username."'s ".$connections." started responding "
                    ."to their last week's ".$post_type." only after ".$syntactic_avg_timediff.". "
                    ."The time of posting updates can be adjusted a little bit to get a better outreach.";
                } else {
                    $insight_text = $this->username."'s ".$post_type." from last week didn't get any responses "
                    ."for as long as ".$syntactic_avg_timediff." of being posted. "
                    ."Changing the time of posting updates may help in getting more outreach.";
                }
            } else {
                // No post got any responses
                $insight_text = $this->username."'s ".$post_type." from last week didn't get any responses at all! "
                ."It maybe so that ".$connections." check in at a different time and the "
                .$post_type." get lost in their newsfeeds.";
            }

            $this->insight_dao->insertInsight("outreach_punchcard", $instance->id, $this->insight_date,
            "Outreach:", $insight_text, basename(__FILE__, ".php"), Insight::EMPHASIS_LOW,
            serialize($punchcard));
        }


        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

    /**
     * Get the human comprehensible, syntactic time difference .
     * @param Time difference in seconds $delta
     * @return str Syntactic time difference
     */
    public static function getSyntacticTimeDifference($delta) {
        $tokens = array();
        $tokens['second'] = 1;
        $tokens['minute'] = 60 * $tokens['second'];
        $tokens['hour'] = 60 * $tokens['minute'];
        $tokens['day'] = 24 * $tokens['hour'];

        arsort($tokens);

        foreach ($tokens as $unit => $value) {
            if ($delta < $value) {
                continue;
            } else {
                $number_of_units = floor($delta / $value);
                return $number_of_units.' '.$unit.(($number_of_units > 1) ? 's' : '');
            }
        }
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('OutreachPunchcardInsight');
