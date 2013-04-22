<?php
/*
 Plugin Name: Network Anniversary stats
 Description: Show stats upon completion of a new year as user of Twitter
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/networkanniversary.php
 *
 * Copyright (c) 2013 Cassio Melo
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
 * @copyright 2012-2013 Gina Trapani
 */

class NetworkAnniversaryInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $in_test_mode =  ((isset($_SESSION["MODE"]) && $_SESSION["MODE"] == "TESTS") || getenv("MODE")=="TESTS");
        $text = '';

        $user_dao = DAOFactory::getDAO('UserDAO');
        $user_obj = $user_dao->getDetails($instance->network_user_id, $instance->network);
        $joined = strtotime($user_obj->joined);
        
        // Check if today is a network anniversary day (lookup joined date)
        if ($in_test_mode || (strtotime(date('m-d')) == date('m-d', $joined))) {
            
            $n_years = date('Y') - date('Y', $joined);
            $n_followers = $user_obj->follower_count;
            $n_tweets = $user_obj->post_count;
            
            // Get posts
            $post_dao = DAOFactory::getDAO('PostDAO');
            
            $posts_it = $post_dao->getAllPostsIterator(13, 'twitter', 0);
            
            $n_statuses = $user_obj->post_count;
            $n_smileys = 0;
            $n_chars = 0;
            $n_questions = 0;
            
            foreach($posts_it as $key => $value) {
                
                $n_chars += strlen($value->post_text);
                $n_smileys += (self::countSmileys($value->post_text) > 0) ? 1 : 0; // ratio is percentage of statuses that has
                                                                                   // *at least* one smiley
                $n_questions += (self::isQuestion($value->post_text)) ? 1 : 0;
            }
            
            $percent_smileys = ($n_statuses > 0) ? round(($n_smileys/$n_statuses)*100) : 0;
            
            // estimate total writting time 
            $est_seconds = 15 * $n_statuses; // [15 seconds x number of tweets as hours, minutes]
            $est_minutes = round($est_seconds/60);
            
            if ($est_minutes == 0 && $n_statuses > 0) { // round up to one minute if necessary
                $est_minutes = 1;
            }
            
            $est_hours = round($est_seconds/3600);
            
            // pluralize when necessary (e.g. 1 hour, 2 hours)
            $est_minutes_string = ($est_minutes <= 1) ? ($est_minutes." minute ") : ($est_minutes." minutes ");
            $est_hours_string = ($est_hours == 1) ? " hour " : " hours ";
            
            $hours_minutes = ($est_hours == 0) ? $est_minutes_string : ($est_hours.$est_hours_string."and ".($est_minutes%60)." minutes ");
            
            // format text
            $text = 'Happy Twitter birthday! '.$n_years.' years ago today, you joined Twitter, '.
             'and since then you\'ve put out '.$n_chars.' characters, 140 (or fewer!) at a time. '. 
             'It\'s likely you\'ve spent about '.$hours_minutes. 
             'tweeting in those '.$n_years.' years. You\'ve asked your '.$n_followers.' followers '.$n_questions.' questions. '. 
            'And '.$percent_smileys.'% of your tweets have smileys in them! That deserves one more. :) ';
        
            $this->logger->logInfo("text: ".$text, __METHOD__.','.__LINE__);
           
            $this->insight_dao->insertInsight("network_anniversary", $instance->id, $this->insight_date, "Network Anniversary:",
            $text, basename(__FILE__, ".php"), Insight::EMPHASIS_LOW);
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
    
    
    
    /**
     * Count the number of times a smiley appears in text.
     * @param str $text
     * @return int Total occurences of smileys in $text (see commented array below)
     */
    
    public static function countSmileys($text) {
        //$smile = array(">:]", ":-)", ":)", ";)", ":D", ":o)", ":]", ":3", ":c)", ":>", "=]", "8)", "=)", ":}", ":^)");
        $smile = "/(^|\W)(\>\:\]|\:-\)|\:\)|\;\)|\:D|\=D|\:P|\:o\)|\:\]|\:3|\:c\)|\:\>|\=\]|8\)|\=\)|\:\}|\:\^\))($|\W)/i";
        $matches = array();
        
        preg_match_all($smile, $text, $matches);
        //print_r(sizeof($matches[0])."\n");
        
        return sizeof($matches[0]); 
    }
    
    
    /**
     * Returns Tells is a text has a question mark.
     * @param str $text
     * @return bol if the text contains a valid question mark.
     */
    public static function isQuestion($text) {
        
        $question = "/\?+/i";
        $matches = array();
        
        preg_match_all($question, $text, $matches);
        //print_r(sizeof($matches[0])."\n");
        
        return (sizeof($matches[0]) > 0);
    }
    
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('NetworkAnniversaryInsight');
