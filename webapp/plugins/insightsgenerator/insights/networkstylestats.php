 <?php
/*
 Plugin Name: Network Style Stats
 Description: Stats on network activity and content of posts.
 When: Weekly , Monthly (28th of the month), Yearly (28th of December).
*/
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/networkstylestats.php
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

class NetworkStyleStatsInsight extends InsightPluginParent implements InsightPlugin {
    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $yearly_date = date('m-d', strtotime('28 December'));
        $should_generate_insight = self::shouldGenerateWeeklyInsight('style_stats', $instance, $insight_date='today',
        $regenerate_existing_insight=false, $day_of_week= 6, count($last_week_of_posts),
        $excluded_networks=array('foursquare', 'google+','youtube'));

        $should_generate_monthly = self::shouldGenerateMonthlyInsight('style_stats', $instance, $insight_date='today',
        $regenerate_existing_insight=false, 28, count($last_week_of_posts),
        $excluded_networks=array('foursquare', 'google+','youtube'));
        if ($should_generate_insight) {
            $num_days = 7;
            $time_frame = "week";
            $slug = "network_style_stats_weekly";
            if($instance->crawler_last_run != null) { 
                $fav_dao = new FavoritePostMySQLDAO();
                $favorites =$fav_dao->getAllFavoritePostsByUsernameWithinRange($instance->network_username,
                $instance->network,0,7);
            }
            $post_dao = DAOFactory::getDAO('PostDAO');
            $posts = $post_dao->getAllPostsByUsernameOrderedBy($instance->network_username, $network=$instance->network,
            $count=0, $order_by="pub_date", $in_last_x_days = 7);
            if (sizeof($posts) >= 5 || sizeof($favorites) >= 5) {
                self::createInsightRatioData($posts,$favorites,$instance,$time_frame,$slug);
            }
        }

        if ($should_generate_monthly) {
            $time_frame = "month";
            $slug = "network_style_stats_monthly";
            if($instance->crawler_last_run != null) { 
                $fav_dao = new FavoritePostMySQLDAO();
                $favorites =$fav_dao->getAllFavoritePostsByUsernameWithinRange($instance->network_username,
                $instance->network,0,date('t'));
            }
            $post_dao = DAOFactory::getDAO('PostDAO');
            $posts = $post_dao->getAllPostsByUsernameOrderedBy($instance->network_username, $instance->network,
            $count=0, $order_by="pub_date", $in_last_x_days = date('t'));
            if (sizeof($posts) >= 5 || sizeof($favorites) >= 5) {
                self::createInsightRatioData($posts,$favorites,$instance,$time_frame,$slug);
            }
        }
        if($instance->network != 'google+' && $instance->network != 'foursquare' && $instance->network != 'youtube') {
            if (date("m-d") == $yearly_date || Utils::isTest()) {
                $time_frame = "year";
                $slug = "network_style_stats_annually";
                if($instance->crawler_last_run != null) { 
                    $fav_dao = new FavoritePostMySQLDAO();
                    $favorites =$fav_dao->getAllFavoritePostsByUsernameWithinRange($instance->network_username,
                    $instance->network,0,date('z'));
                }
                $post_dao = DAOFactory::getDAO('PostDAO');
                $posts = $post_dao->getAllPostsByUsernameOrderedBy($instance->network_username, $instance->network,
                $count=0, $order_by="pub_date", $in_last_x_days = date('z'));
                if (sizeof($posts) >= 5 || sizeof($favorites) >= 5) {
                    self::createInsightRatioData($posts,$favorites,$instance,$time_frame,$slug);
                }
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
    /**
    * Create ratios and get posts for insight..
    * @param arr $network_totals array of total count of each type of twitter posts.
    * @param arr $network_zero_check array of bools to check if there is a type of twitter post type not used.
    * @param arr $user_posts information on replies tweets and retweets to retrive post object. Stores favorite object.
    * @return arr contains insight text and random post of certain type.
    */
    private function createInsightRatioData($posts,$favorites,$instance,$time_frame,$slug) {
        $network_totals = array("post" => 0, "reply" => 0, "retweet" => 0,"favorite" => 0);
        $network_zero_check = array("post" => false ,"reply" => false,"retweet" => false,"favorite" => false);
        //Checks if crawler was run before. 
        //If crawlers hasn't been run before all favorites will be captured as if they where all made within
        // last week.

        foreach($favorites as $fav_post) {
            $network_totals["favorite"]++;
            $network_posts["favorite"][] = $fav_post;
        }
        
        foreach ($posts as $post) {
            if($instance->network_user_id == $post->author_user_id && $post->in_reply_to_user_id == null &&
            $post->in_retweet_of_post_id == null) {
                $network_totals["post"]++;
                $network_posts["post"][] = $post;
            } elseif($post->in_reply_to_user_id != $instance->network_user_id && $post->in_reply_to_user_id != null) {
                $network_totals["reply"]++;
                $network_posts["reply"][] = $post;
            } elseif($post->in_retweet_of_post_id != null) {
                $network_totals["retweet"]++;
                $network_posts["retweet"][] = $post;
            } else {
                continue;
            }
        }

        $terms = new InsightTerms($instance->network);
        foreach (array_keys($network_totals) as $key) {
            //Checks for zero array values, true if non-zero. Stores non-zero key in the event only on key has values.
            if($network_totals[$key] != 0) {
                $network_zero_check[$key] = false;
                $zero_key = $key;
                $non_zero_count++;
            } else {
                $network_zero_check[$key] = true;
            }
        }
        //Checks to make sure only one type of post has been created. Ratio can't be calculated with one type.
        if($non_zero_count == 1) {
            $one_activity_text = '';
            switch ($zero_key) {
                case "post":
                    $one_activity_text ="$this->username only wrote " .$terms->getNoun($zero_key, InsightTerms::PLURAL);
                    $one_activity_text .= " this $time_frame. "; 
                    break;
                case "reply":
                    $one_activity_text="$this->username only wrote " . $terms->getNoun($zero_key, InsightTerms::PLURAL);
                    $one_activity_text .= " to other users this $time_frame. ";
                    break;
                case "retweet":
                    $one_activity_text = "$this->username only " . $terms->getVerb('shared') . " other user's ";
                    $one_activity_text.= $terms->getNoun('post', true) . " this $time_frame. ";
                    break;
                case "favorite":
                    $one_activity_text = "$this->username only " . $terms->getVerb('favorited') . " other user's ";
                    $one_activity_text .= $terms->getNoun('post', true) . " this $time_frame. ";
                    break;
            }
            $text = $one_activity_text;
            $headline = $headline = $this->getStyleStats($network_posts[$zero_key],$time_frame,$instance->network,$zero_key);
                //Checks more than 1 type of post is non-zero. Picks 2 random post types to create ratio.
                //Checks to ensure a post of the same type isn't compared with itself and that a post isn't compared 
                //with a 0 value post type. 
                //Randomly switches between posts type until ratio calculation can be carried out.
        } elseif($non_zero_count > 1) {
            $network_totals_keys = array_rand($network_totals, 2);
            while ($network_totals_keys[0] == $network_totals_keys[1] ||
                $network_totals[$network_totals_keys[0]] == 0 || $network_totals[$network_totals_keys[1]] == 0) {
                if($network_totals_keys[0] == $network_totals_keys[1]) {
                    $network_totals_keys[rand(0, 1)] = array_rand($network_totals, 1);
                }
                if($network_totals[$network_totals_keys[0]] == 0) {
                    $network_totals_keys[0] = array_rand($network_totals, 1);
                }
                if($network_totals[$network_totals_keys[1]] == 0) {
                    $network_totals_keys[1] = array_rand($network_totals, 1);
                }
            }

            $calulated_ratio = $network_totals[$network_totals_keys[0]]/$network_totals[$network_totals_keys[1]];
            $multi_terms = $terms->getMultiplierAdverb($calulated_ratio);
            switch ($network_totals_keys[0]) {
                case "post":
                    $ratio1 = $terms->getNoun($network_totals_keys[0], true) . " $this->username wrote";
                    $ratio1 .= " this $time_frame was "; 
                    break;
                case "reply":
                    $ratio1 = $terms->getNoun($network_totals_keys[0], true) . " $this->username wrote";
                    $ratio1 .= " this $time_frame was ";
                    break;
                case "retweet":
                    $ratio1 = $terms->getNoun('post',true) ." $this->username " . $terms->getVerb('shared');
                    $ratio1 .= " this $time_frame was ";
                    break;
                case "favorite":
                    $ratio1 = $terms->getNoun('post',true) ." $this->username " . $terms->getVerb('favorited');
                    $ratio1 .= " this $time_frame was ";
                    break;
            }
            switch ($network_totals_keys[1]) {
                case "post":
                    $ratio2 = $terms->getNoun($network_totals_keys[1],true) ." $this->username tweeted.";
                    break;
                case "reply":
                    $ratio2 = $terms->getNoun($network_totals_keys[1], true) ." $this->username wrote.";
                    break;
                case "retweet":
                    $ratio2 =$terms->getNoun('post',true) ." $this->username " . $terms->getVerb('shared') . ".";
                    break;
                case "favorite":
                    $ratio2 =$terms->getNoun('post',true) ." $this->username " . $terms->getVerb('favorited') . ".";
                    break;
            }
            if($multi_terms == "1x") {
                $multi_string = "<b>the same</b> as ";
            } else {
                $multi_string = "about <b>$multi_terms</b>";
            }
            $text = "The number of $ratio1 $multi_string the number of $ratio2";
            $headline = $headline = $this->getStyleStats($network_posts[$network_totals_keys[0]],$time_frame,
                $instance->network,$network_totals_keys[0]);
            $vis_data = $this->getVisData($network_totals,$instance->network);
        }
        $my_insight = new Insight();
        $my_insight->slug = $slug; //slug to label this insight's content
        $my_insight->instance_id = $instance->id;
        $my_insight->date = date('Y-m-d'); //date is often this or $simplified_post_date
        $my_insight->headline = $headline;
        $my_insight->text = $text;
        $my_insight->setBarChart($vis_data);
        $my_insight->filename = basename(__FILE__, ".php"); //Same for every insight, must be set exactly this way
        $my_insight->emphasis = Insight::EMPHASIS_MED; //Set emphasis optionally, default is Insight::EMPHASIS_LOW
        $this->insight_dao->insertInsight($my_insight);
    }

    /**
    * Create vis_data array/
    * @param arr $network_totals array of total count of each type of twitter posts.
    * @param str $network string stores name of network.
    * @return arr contains insight vis_data.
    */

    private function getVisData($network_totals, $network) {
        $terms = new InsightTerms($network);
        $resultset = array();
        $metadata = array();
            
        $new_network_totals = array();
        foreach ($network_totals as $type => $count) {
            $new_network_totals[$terms->getNoun($type,true)] = $count;
        }
        unset($network_totals);
        foreach ($new_network_totals as $type => $count) {
            $resultset[] = array('c' => array( array('v' =>$type), array('v' => $count)));
            $metadata = array( array('type' => 'string', 'label' => 'Url'),
                        array('type' => 'number', 'label' => 'Number of Shares'));
        }
        $vis_data = json_encode(array('rows' => $resultset, 'cols' => $metadata));
        return $vis_data;
    }

    /**
    * Creates dtyle stats for insight.
    * @param arr $network_posts array of post objects.
    * @param str string stating time frame.
    * @param str string stating network.
    * @param str string stating post type..
    * @return str string with style stats for headline.
    */

    private function getStyleStats($network_posts, $time_frame,$network,$post_type) {
        $terms = new InsightTerms($network);
        $totals = array("questions" => 0, "quotations" => 0, "links" => 0);
        foreach ($network_posts as $post) {
            if ((strpos($post->post_text, '? ') !== false) || self::endsWith($post->post_text, '?')) {
                $totals["questions"]++;
            }
            if (strpos($post->post_text, '"') !== false || self::startsWith($post->post_text, 'OH') ) {
                $totals["quotations"]++;
            }
            if (sizeof($post->links) > 0 ) {
                foreach ($post->links as $link) {
                        $totals["links"]++;
                }
            }
        }
        $posts_positive = array();
        foreach ($totals as $type => $total) {
            if ($total == 0) {
                $posts_zero[$type] = $total;
            } else {
                $posts_positive[$type] = $total;
            }
        }
        $keys_pos = array_keys($posts_positive);
        $last_type_pos = end($keys_pos);

        foreach ($posts_positive as $type => $total) {
            if ($type == $last_type_pos && count($posts_positive) >= 2) { //last item in list
                $style_analysis .= "and ";
            }
            if ($style_analysis == '') { //first item
                $style_analysis .= (($total == 0)?"None":$total)." of $this->username's ";
                $style_analysis .= $terms->getNoun($post_type, true) ." this $time_frame " .
                (($total == 1)?"was a":"were")." ".(($total == 1)?substr($type, 0, -1):$type);
            } elseif ($total == 0) {

            } else {
                $style_analysis .= (($total == 0)?"none":$total)." ".(($total == 1)?"was a":"were")." ".
                (($total == 1)?substr($type, 0, -1):$type);
            }
            if ($type == $last_type_pos) {  //last item in list
                $style_analysis .= ".";
            } else if (count($posts_positive) > 2) {
                $style_analysis .= ", ";
            } else {
                $style_analysis .= " ";
            }
        }
        $keys_zero = array_keys($posts_zero);
        $last_type_zero = end($keys_zero);
        foreach ($posts_zero as $type => $total) {
            if ($type == $last_type_zero && count($posts_zero) >= 2) { //last item in list
                $style_analysis_neg .= "or ";
            }
            if ($style_analysis_neg == '') { //first item
                $style_analysis_neg .= "$this->username " . $terms->getNoun($post_type, true);
                $style_analysis_neg .= " this $time_frame didn't contain any $type";
            } else {
                $style_analysis_neg .= "$type";
            }
            if ($type == $last_type_zero) {  //last item in list
                $style_analysis_neg .= ".";
            } else if (count($posts_zero) > 2) {
                $style_analysis_neg .= ", ";
            } else {
                $style_analysis_neg .= " ";
            }
        }

        if ($style_analysis) {
                $insight_text = $style_analysis;
            } elseif ($style_analysis_neg) {
                $insight_text = $style_analysis_neg;
            } else {
                $insight_text = '';
            }
        return $insight_text;
    }

    private function endsWith($str, $end_str) {
        $full_str_end = substr($str, strlen($str) - (strlen($end_str)));
        return $full_str_end == $end_str;
    }

    private function startsWith($str, $start_str) {
        $full_str_start = substr($str, 0, strlen($str) - (strlen($start_str)));
        return $full_str_start == $start_str;
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('NetworkStyleStatsInsight');