 <?php
/*
 Plugin Name: Twitter Ratio
 Description: Stats on twitter activity.
 When: Saturdays, 28th of every month.
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/twitterratios.php
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

class TwitterRatiosInsight extends InsightPluginParent implements InsightPlugin {
    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $yearly_date = date('m-d', strtotime('28 December'));
        $should_generate_insight = self::shouldGenerateWeeklyInsight('style_stats', $instance, $insight_date='today',
        $regenerate_existing_insight=false, $day_of_week= 6, count($last_week_of_posts),
        $excluded_networks=array('foursquare', 'facebook','google+','youtube'));

        $should_generate_monthly = self::shouldGenerateMonthlyInsight('style_stats', $instance, $insight_date='today',
        $regenerate_existing_insight=false, 28, count($last_week_of_posts),
        $excluded_networks=array('foursquare', 'facebook','google+','youtube'));

        if ($should_generate_insight) {
            $num_days = 7;
            $time_frame = "week";
            $slug = "twitter_ratios_weekly";
            if($instance->crawler_last_run != null) { 
                $fav_dao = new FavoritePostMySQLDAO();
                $favorites =$fav_dao->getAllFavoritePostsByUsernameWithinRange($instance->network_username,
                'twitter',0,7);
            }
            $post_dao = DAOFactory::getDAO('PostDAO');
            $posts = $post_dao->getAllPostsByUsernameOrderedBy($instance->network_username, $network="twitter",
            $count=0, $order_by="pub_date", $in_last_x_days = 7);
            if (sizeof($posts) >= 5 || sizeof($favorites) >= 5) {
                self::createInsightRatioData($posts,$favorites,$instance,$time_frame,$slug);
            }
        }

        if ($should_generate_monthly) {
            $time_frame = "month";
            $slug = "twitter_ratios_monthly";
            if($instance->crawler_last_run != null) { 
                $fav_dao = new FavoritePostMySQLDAO();
                $favorites =$fav_dao->getAllFavoritePostsByUsernameWithinRange($instance->network_username,
                'twitter',0,date('t'));
            }
            $post_dao = DAOFactory::getDAO('PostDAO');
            $posts = $post_dao->getAllPostsByUsernameOrderedBy($instance->network_username, $network="twitter",
            $count=0, $order_by="pub_date", $in_last_x_days = date('t'));
            if (sizeof($posts) >= 5 || sizeof($favorites) >= 5) {
                self::createInsightRatioData($posts,$favorites,$instance,$time_frame,$slug);
            }
        }

        if (date("m-d") == $yearly_date || Utils::isTest()) {
            $time_frame = "year";
            $slug = "twitter_ratios_annually";
            if($instance->crawler_last_run != null) { 
                $fav_dao = new FavoritePostMySQLDAO();
                $favorites =$fav_dao->getAllFavoritePostsByUsernameWithinRange($instance->network_username,
                'twitter',0,date('z'));
            }
            $post_dao = DAOFactory::getDAO('PostDAO');
            $posts = $post_dao->getAllPostsByUsernameOrderedBy($instance->network_username, $network="twitter",
            $count=0, $order_by="pub_date", $in_last_x_days = date('z'));
            if (sizeof($posts) >= 5 || sizeof($favorites) >= 5) {
                self::createInsightRatioData($posts,$favorites,$instance,$time_frame,$slug);
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
    /**
    * Create ratios and get posts for insight..
    * @param arr $twitter_totals array of total count of each type of twitter posts.
    * @param arr $twitter_zero_check array of bools to check if there is a type of twitter post type not used.
    * @param arr $user_posts information on replies tweets and retweets to retrive post object. Stores favorite object.
    * @return arr contains insight text and random post of certain type.
    */
    private function createInsightRatioData($posts,$favorites,$instance,$time_frame,$slug) {
        $twitter_totals = array("Tweets" => 0, "Replies" => 0, "Retweets" => 0,"Favorites" => 0);
        $twitter_zero_check = array("Tweets" => false ,"Replies" => false,"Retweets" => false,"Favorites" => false);
        //Checks if crawler was run before. 
        //If crawlers hasn't been run before all favorites will be captured as if they where all made within
        // last week.

        foreach($favorites as $fav_post) {
            $twitter_totals["Favorites"]++;
        }
        
        foreach ($posts as $post) {
            if($instance->network_user_id == $post->author_user_id && $post->in_reply_to_user_id == null &&
            $post->in_retweet_of_post_id == null) {
                    $twitter_totals["Tweets"]++;
            } elseif($post->in_reply_to_user_id != $instance->network_user_id && $post->in_reply_to_user_id != null) {
                $twitter_totals["Replies"]++;
            } elseif($post->in_retweet_of_post_id != null) {
                $twitter_totals["Retweets"]++;
            } else {
                continue;
            }
        }

        $insight_terms = new InsightTerms('twitter');
        foreach (array_keys($twitter_totals) as $key) {
            //Checks for zero array values, true if non-zero. Stores non-zero key in the event only on key has values.
            if($twitter_totals[$key] != 0) {
                $twitter_zero_check[$key] = false;
                $zero_key = $key;
                $non_zero_count++;
            } else {
                $twitter_zero_check[$key] = true;
            }
        }
        //Checks to make sure only one type of post has been created. Ratio can't be calculated with one type.
        if($non_zero_count == 1) {
            $one_activity_text = '';
            switch ($zero_key) {
                case "Tweets":
                    $one_activity_text = "$this->username only tweeted last $time_frame. "; 
                    $one_activity_text .= "Why not get the coversation flowing ";
                    $one_activity_text .= "by replying to other users this $time_frame ?";
                    break;
                case "Replies":
                    $one_activity_text = "$this->username only replied to other users last $time_frame. ";
                    $one_activity_text .= "Why not share the best tweets with a retweet this $time_frame ?";
                    break;
                case "Retweets":
                    $one_activity_text = "$this->username only retweeted other user's tweets last $time_frame. ";
                    $one_activity_text .="Why not give other users something to retweet with some ";
                    $one_activity_text .= "new tweets this $time_frame ?";
                    break;
                case "Favorites":
                    $one_activity_text = "$this->username only favorited other user's tweets last $time_frame. ";
                    $one_activity_text .= "Why not share the best Twitter has to offer with a retweet this ";
                    $one_activity_text .= "$time_frame ?";
                    break;
            }
            $text = $one_activity_text;
                //Checks more than 1 type of post is non-zero. Picks 2 random post types to create ratio.
                //Checks to ensure a post of the same type isn't compared with itself and that a post isn't compared 
                //with a 0 value post type. 
                //Randomly switches between posts type until ratio calculation can be carried out.
            } elseif($non_zero_count > 1) {
                $twitter_totals_keys = array_rand($twitter_totals, 2);
                while ($twitter_totals_keys[0] == $twitter_totals_keys[1] ||
                    $twitter_totals[$twitter_totals_keys[0]] == 0 || $twitter_totals[$twitter_totals_keys[1]] == 0) {
                    if($twitter_totals_keys[0] == $twitter_totals_keys[1]) {
                        $twitter_totals_keys[rand(0, 1)] = array_rand($twitter_totals, 1);
                    }
                    if($twitter_totals[$twitter_totals_keys[0]] == 0) {
                        $twitter_totals_keys[0] = array_rand($twitter_totals, 1);
                    }
                    if($twitter_totals[$twitter_totals_keys[1]] == 0) {
                        $twitter_totals_keys[1] = array_rand($twitter_totals, 1);
                    }
                }

                $calulated_ratio = $twitter_totals[$twitter_totals_keys[0]]/$twitter_totals[$twitter_totals_keys[1]];
                $terms = $insight_terms->getMultiplierAdverb($calulated_ratio);
                switch ($twitter_totals_keys[0]) {
                    case "Tweets":
                        $ratio1 = "tweets $this->username wrote was ";
                        break;
                    case "Replies":
                        $ratio1 = " replies $this->username wrote was ";
                        break;
                    case "Retweets":
                        $ratio1 = "tweets $this->username retweeted was ";
                        break;
                     case "Favorites":
                         $ratio1 = "tweets $this->username favorited was ";
                         break;
                }
                switch ($twitter_totals_keys[1]) {
                    case "Tweets":
                        $ratio2 = "tweets $this->username tweeted.";
                        break;
                    case "Replies":
                        $ratio2 = "replies $this->username wrote.";
                        break;
                    case "Retweets":
                        $ratio2 = "tweets $this->username retweeted.";
                        break;
                    case "Favorites":
                        $ratio2 = "tweets $this->username favorited.";
                        break;
                }
                if($terms == "1x") {
                    $terms = "<b>the same</b> as ";
                } else {
                    $terms = "about <b>$terms</b>";
                }
                $text = "<b>Fun fact:</b> the number of $ratio1 $terms the number of $ratio2";
                $vis_data = $this->getVisData($twitter_totals);
            }
            $my_insight = new Insight();
            $my_insight->slug = $slug; //slug to label this insight's content
            $my_insight->instance_id = $instance->id;
            $my_insight->date = date('Y-m-d'); //date is often this or $simplified_post_date
            $my_insight->headline = $this->getVariableCopy(array(
                "Lets break down %username's Twitter activity.",
                "Lets take a look at %username's Twitter ratios.",
                "%username's Twitter ratios for $time_frame look like this."));
            $my_insight->text = $text;
            $my_insight->setBarChart($vis_data);
            $my_insight->filename = basename(__FILE__, ".php"); //Same for every insight, must be set exactly this way
            $my_insight->emphasis = Insight::EMPHASIS_MED; //Set emphasis optionally, default is Insight::EMPHASIS_LOW
            $this->insight_dao->insertInsight($my_insight);
        }

        private function getVisData($twitter_totals) {
            $resultset = array();
            $metadata = array();
            foreach ($twitter_totals as $total => $count) {
                $resultset[] = array('c' => array( array('v' =>$total), array('v' => $count)));
                $metadata = array( array('type' => 'string', 'label' => 'Url'),
                            array('type' => 'number', 'label' => 'Number of Shares'));
            }
            $vis_data = json_encode(array('rows' => $resultset, 'cols' => $metadata));
            return $vis_data;
        }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('TwitterRatiosInsight');