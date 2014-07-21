<?php
/*
 Plugin Name: Distance Shared.
 Description: How many miles did the user share on Twitter using fitness apps.
 When: Weekly, on Sundays
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
        return $this->shouldGenerateWeeklyInsight($this->slug, $instance, $insight_date='today',
        $regenerate_existing_insight=false, $day_of_week=0, $count_last_week_of_posts=null,
        array('youtube','foursquare','instagram','facebook','google+'));
    }

    public function getSlug() {
        return 'distanceshared';
    }

    public function getNumberOfDaysNeeded() {
        return 14;
    }

    public function postMatchesCriteria(Post $post, Instance $instance) {
        //Matches posts that contain fitness applications hashtags and distances.
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
            $insight_terms = new InsightTerms('twitter');
            $mile_array = array(); //previous weeks distances
            $old_mile_array = array(); //distances from 2 weeks ago.
            $from = date('Y-m-d', strtotime('-2 week'));
            $until = date('Y-m-d', strtotime('-1 week'));
            foreach($matching_posts as $post) {
                if($post->pub_date > $from && $post->pub_date < $until) {
                    $old_mile_array[] = $post;
                } else {
                    $mile_array[] = $post;
                }
            }
            $mile_array = $this->getDistanceFromPosts($mile_array);
            $old_mile_array = $this->getDistanceFromPosts($old_mile_array);

            $mile_val = $this->calculateTotalDistance($mile_array);
            $old_mile_val = $this->calculateTotalDistance($old_mile_array);

            $mile_diff = $mile_val - $old_mile_val;
            $abs_mile_diff = abs($mile_diff);
            $abs_mile_diff = round($abs_mile_diff, 2);
            //Array of distance values for contextual feedback.
            $feedback_value = array(
                "racing" => 3.66,
                "runway" => 3.42,
                "beach" => 2.00,
                "football" => 7.00,
                "tennis" => 3.00,
                "running" => 26.00,
                "lhc" => 17,
                "everest" => 5.50,
                "rollercoaster" => 1.54,
                "building" => 0.51
            ); 

            $feedback_key = array_rand($feedback_value, 1);
            $feedback_number = $mile_val/$feedback_value[$feedback_key];
            if ($feedback_number >= 1) {
                $feedback_number = round($feedback_number,1) . " times";
            } else {
                $feedback_number = $insight_terms->getMultiplierAdverb($feedback_number);
            }
        
            $feedback = array(
                "racing" => " is about $feedback_number the distance of a lap around Silverstone.",
                "runway" => " is about $feedback_number the length of the worlds longest runway.",
                "beach" => " is about $feedback_number the length of Copacabana Beach.",
                "football" => " is about $feedback_number the distance a footballer runs during a match.",
                "tennis" => " is about $feedback_number the distance a tennis player travels during a match.",
                "running" => " is about $feedback_number the distance of a marathon.",
                "lhc" => " is about $feedback_number the distance of a walk around the Large Hadron Collider",
                "everest" => " is about $feedback_number the alltitude of Mount Everest.",
                "rollercoaster"=>" is about $feedback_number the distance of a ride on the worlds
                 longest roller coaster.",
                "building" => " is about $feedback_number the height of the worlds tallest building."
                ); 

            $hero_images = array(
                "racing" => array(
                    'url' => 'http://i.imgur.com/zMUHR8w.jpg',
                    'alt_text' => 'Silverstone Race Track',
                    'credit' => 'Photo: estoril',
                    'img_link' => 'https://www.flickr.com/photos/estoril/2890147479/'
                ),
                "runway" => array(
                    'url' => 'http://i.imgur.com/oCNbGmc.jpg',
                    'alt_text' => 'Airport Runway',
                    'credit' => 'Photo: johngilchrist',
                    'img_link' => 'https://www.flickr.com/photos/johngilchrist/3705334636/'
                ),
                "beach" => array(
                    'url' => 'http://i.imgur.com/1XA5P69.jpg',
                    'alt_text' => 'Copacabana',
                    'credit' => 'Photo: Soldon',
                    'img_link' => 'https://www.flickr.com/photos/soldon/3919711826/'
                ),
                "football" => array(
                    'url' => 'http://i.imgur.com/rgnC85u.jpg',
                    'alt_text' => 'Football Players',
                    'credit' => 'Photo: See-ming Lee',
                    'img_link' => 'https://www.flickr.com/photos/seeminglee/8693368996/'
                ),
                "tennis" => array(
                    'url' => 'http://i.imgur.com/IlOTZCh.jpg',
                    'alt_text' => 'Tennis Gear',
                    'credit' => 'Photo: gogri',
                    'img_link' => 'https://www.flickr.com/photos/gogri/2786584532/'
                ),
                "running" => array(
                    'url' => 'http://i.imgur.com/0rWHtT1.jpg',
                    'alt_text' => 'Runner',
                    'credit' => 'Photo: tabo roeder',
                    'img_link' => 'https://www.flickr.com/photos/tabor-roeder/8113284229/'
                ),
                "lhc" => array(
                    'url' => 'http://i.imgur.com/o01EOhq.jpg',
                    'alt_text' => 'Large Hadron Collider ',
                    'credit' => 'Photo: Frank Weber kl',
                    'img_link' => 'https://www.flickr.com/photos/frank-weber-kl/10081726433/'
                ),
                "everest" => array(
                    'url' => 'http://i.imgur.com/JZRWpjC.jpg',
                    'alt_text' => 'Mount Everest',
                    'credit' => 'Photo: rupertuk',
                    'img_link' => 'https://www.flickr.com/photos/rupertuk/534748923/'
                ),
                "rollercoaster" => array(
                    'url' => 'http://i.imgur.com/M4ixlCp.jpg',
                    'alt_text' => 'Steel Dragon 2000',
                    'credit' => 'Photo: thecrypt',
                    'img_link' => 'https://www.flickr.com/photos/thecrypt/2592801421/'
                ),
                "building" => array(
                    'url' => 'http://i.imgur.com/iNRregW.jpg',
                    'alt_text' => 'Burj Khalifa',
                    'credit' => 'Photo: sierragoddess',
                    'img_link' => 'https://www.flickr.com/photos/sierragoddess/6770671083/'
                ));

            $feedback_string = $feedback[$feedback_key];

            $insight = new Insight();
            $insight->slug = $this->getSlug();
            $insight->instance_id = $instance->id;
            $insight->date = $this->insight_date;
            $insight->headline = $this->getVariableCopy(array(
                'Nobody can call %username a couch potato!',
                'Keep it up %username!',
                'Been on the move ?'
            ));

            $threshold_string = '';

            if($mile_diff > 0.01) {
                $threshold_string = "That's even better than last week!";
            } elseif($mile_diff > -1 && $mile_diff < 0) {
                $threshold_string = "That's virtually the same as last week!";
            } elseif($mile_diff == 0) {
                $threshold_string = "That's exactly the same as last week!";
            } else {
                $threshold_string = "That's still better than everyone who decided to stay on the couch.";
            }

            $insight->text =  "$this->username shared <strong>$mile_val miles</strong>. $threshold_string"; 
            $insight->text .=  " <br>Fun Fact: <strong>$mile_val miles</strong> $feedback_string";
            $insight->setHeroImage($hero_images[$feedback_key]);
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

    public function getDistanceFromPosts($matching_posts) {
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
        //Compares each posts in array to ensure it is not the same workout shared on multiple networks.
        //pub_date must be greater than 30 seconds 
        //because there is a high chance of fitness posts being shared on each network within 30 seconds of each other.
        foreach ($matching_posts as $post) {
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
        }
        return $mile_array;
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('DistanceSharedInsight');