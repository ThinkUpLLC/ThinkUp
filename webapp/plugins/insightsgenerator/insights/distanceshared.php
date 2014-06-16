<?php
/*
 Plugin Name: Distance Shared.
 Description: How many miles did the user share using fitness apps.
 When: Weekly
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/distanceshared.php
 *
 * Copyright (c) 2014 Gareth Brady
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
 * @copyright 2014 Gareth Brady
 * @author Gareth Brady <gareth.brady92 [at] gmail [dot] com>
 */
class DistanceSharedInsight extends CriteriaMatchInsightPluginParent implements InsightPlugin {
    public function shouldGenerate(Instance $instance, $last_week_of_posts) {
        // return $this->shouldGenerateWeeklyInsight($this->slug, $instance, $insight_date='today',
        // $regenerate_existing_insight=false, $day_of_week=0, $count_last_week_of_posts=null,
        // array('youtube','foursquare','instagram'));
        return true;
    }

    public function getSlug() {
        return 'distanceshared';
    }

    public function getNumberOfDaysNeeded() {
        return 7;

    }

    public function postMatchesCriteria(Post $post, Instance $instance) {
        //Mathces posts that contain fitness applications hashtags and distances.
        $text = strtolower($post->post_text);
        if ((preg_match('/#nikeplus/', $text) || preg_match('/#runkeeper/', $text) || 
            preg_match('/#endomondo/', $text) || preg_match('/#zombiesrun/', $text) ||
            preg_match('/#mapmy/', $text) || preg_match('/#runtastic/', $text))
            && (preg_match('/ mi /', $text) || preg_match('/ km /', $text) 
            || (preg_match('/ mile /', $text) ) || preg_match('/ miles /', $text))) {
            return true;
        } else {
            return false;
        }
    }

    public function getInsightForCounts($this_period_count, $last_period_count, $instance, $matching_posts) {
        $insight = null;
        if ($this_period_count > 0) {
            $mile_array = array(); //previous weeks distances
            $old_mile_array = array(); //distances from 2 weeks ago.
            $post_dao = DAOFactory::getDAO('PostDAO');
            $from = date('Y-m-d', strtotime('-2 week'));
            $until = date('Y-m-d', strtotime('-1 week'));
            $old_mile_array = $post_dao->getOldDistancePosts($instance->network_user_id,$instance->network, $from,
            $until);
            $mile_array = $this->getDistance($matching_posts);
            $old_mile_array = $this->getDistance($old_mile_array);

            $mile_val = $this->calculateTotalDistance($mile_array);
            $old_mile_val = $this->calculateTotalDistance($old_mile_array);


            $mile_diff = $mile_val - $old_mile_val;
            $abs_mile_diff = abs($mile_diff);
            $abs_mile_diff = round($abs_mile_diff, 2);


            $insight = new Insight();
            $insight->slug = $this->getSlug();
            $insight->instance_id = $instance->id;
            $insight->date = $this->insight_date;
            $insight->headline = $this->getVariableCopy(array(
            'Have you been working out ?',
            // 'Keep it up %username!',
            'Good work this week. Can you share more miles next week ?',
            ));
            $less_more = $mile_diff > 0 ? 'more' : 'less';

            if ( $mile_diff != 0 ) {
                $insight->text = 
                "You shared $mile_val miles last week. That's $abs_mile_diff $less_more than last week.";
            } else {
                $insight->text = 
                "You shared $mile_val miles last week. That's the same as last week. Can you share more next week ?";
            }
            $resultset = array();
            foreach ($mile_array as $key => $value) {
                $resultset[] = array('c' => array( 
                array('v' => $key), array('v' => $value)));

                $metadata = array(
                array('type' => 'string', 'label' => 'Day'),
                array('type' => 'number', 'label' => 'Distance in miles'),
                );
                $vis_data = json_encode(array('rows' => $resultset, 'cols' => $metadata));
                $insight->setBarChart($vis_data);
            }
            $insight->filename = basename(__FILE__, ".php");

        }
        return $insight;
    }
    /** 
     * Calculates the total value of miles contained in array.
     * @param arr $mile_array array containing miles.
     * @return float total miles.
     */

    private function calculateTotalDistance($mile_array) {
        foreach($mile_array as $miles) {
            $mile_val += $miles;
        }
        return $mile_val = round($mile_val, 2);
    }

    /** 
     * Retrieves distances from fitness related posts.
     * @param arr $matching_posts array of posts from last week from fitness applications.
     * @return array of distances with key for each day.
     */

    private function getDistance($matching_posts){
        $mile_val = 0;
        $km_val = 0;
        $km_to_mile = 0.621371192;
        $mile_array = array(
            "Sunday" => 0,
            "Monday" => 0,
            "Tuesday" => 0,
            "Wednesday" => 0,
            "Thursday" => 0,
            "Friday" => 0,
            "Saturday" => 0);
        $over_time = true;
        //Compares each posts in array to ensure it is not the same workout shared on multiple networks.
        //pub_date must be greater than 30 seconds 
        //because there is a high chance of fitness posts being shared on each network within 30 seconds of each other.
        foreach ($matching_posts as $post) {
            foreach ($matching_posts as $posts) {
                if(abs(strtotime($post->pub_date) - strtotime($posts->pub_date)) < 30 
                    && $post->post_id !== $posts->post_id && $post->network !== $posts->network) {
                    $over_time = false;
                    break;
                } else {
                    $over_time = true;
                }
            }
            //if overtime is true the posts pub_date is not within 30 seconds of another post.
            //if false move onto next post.
            if ($over_time == true) {
                $date = $post->pub_date;
                $weekday = date('l', strtotime($date));
                $text = strtolower($post->post_text);
                if (preg_match('/ mi | mile | miles /', $text)) {
                    if (preg_match('/[0-9]+\.[0-9]+/',$text, $mile_matches)) {
                        $mile_val = floatval($mile_matches[0]);
                        $mile_array[$weekday] += $mile_val; 
                    } elseif(preg_match('/[0-9]+,[0-9]+/',$text, $mile_matches)) {
                        $mile_matches[0] = str_replace(",", ".", $mile_matches[0]);
                        $mile_val = floatval($mile_matches[0]);
                        $mile_array[$weekday] += $mile_val; 
                    } else {
                        $mile_val = 0;
                    }
                } elseif (preg_match('/ km /', $text)) {
                    if(preg_match('/[0-9]+\.[0-9]+/',$text, $km_matches)) {
                        $km_val = floatval($km_matches[0]);
                        $mile_val = $km_val * $km_to_mile;
                        $mile_array[$weekday] += $mile_val; 
                    } elseif(preg_match('/[0-9]+,[0-9]+/',$text, $km_matches)){
                        $km_matches[0] = str_replace(",", ".", $km_matches[0]);
                        $km_val = floatval($km_matches[0]);
                        $mile_val = $km_val * $km_to_mile;
                        $mile_array[$weekday] += $mile_val; 
                    } else {
                        $mile_val = 0;
                    }
                }
            } elseif ($over_time == false) {
                continue;   
            }
        }
        return $mile_array;
    }
}



$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('DistanceSharedInsight');

    



