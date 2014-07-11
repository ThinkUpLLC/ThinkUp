 <?php
/*
 Plugin Name: Twitter Ratio
 Description: Stats on twitter activity.
 When: Saturdays 
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/StyleStat.php
 *
 * Copyright (c) 2012-2013 Gareth Brady
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
 * @copyright 2012-2013 Gareth Brady
 * @author Gareth Brady <gareth.brady92 [at] gmail [dot] com>
 */

class TwitterRatiosInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
        $should_generate_insight = self::shouldGenerateWeeklyInsight('style_stats', $instance, $insight_date='today',
        $regenerate_existing_insight=false, $day_of_week= 6, count($last_week_of_posts),
        $excluded_networks=array('foursquare', 'facebook','google+','youtube'));

        if ($should_generate_insight) {
            $user_posts = array(
                "usertweets" => null,
                "replies" => null,
                "retweets" => null,
                "favorites" => null,
                );
            $twitter_totals = array("usertweets" => 0, "replies" => 0, "retweets" => 0,
            "favorites" => 0);
            $twitter_zero_check = array("usertweets" => false , "replies" => false,
            "retweets" => false,"favorites" => false);
            if (sizeof( $last_week_of_posts) >= 5) {
                $this->logger->logSuccess("Calculating twiiter ratios ", __METHOD__.','.__LINE__);
                //Checks if crawler was run before. 
                //If crawlers hasn't been run before all favorites will be captured as if they where all made within
                // last week.
                if($instance->crawler_last_run != null) { 
                    $fav_dao = new FavoritePostMySQLDAO();
                    $favorites = $fav_dao->getAllFavoritePostsByUsernameWithinRange($instance->network_username,
                     'twitter',0,7);

                    foreach($favorites as $fav_post) {
                        $twitter_totals["favorites"]++;
                        $user_posts["favorites"][] = $fav_post;
                    }
                }
               
                foreach ($last_week_of_posts as $post) {
                    if($instance->network_user_id == $post->author_user_id && $post->in_reply_to_user_id == null &&
                    $post->in_retweet_of_post_id == null) {
                        $twitter_totals["usertweets"]++;
                        $user_posts["usertweets"][] = $post->post_id;
                    } elseif($post->in_reply_to_user_id != $instance->network_user_id &&
                        $post->in_reply_to_user_id != null) {
                        $twitter_totals["replies"]++;
                        $user_posts["replies"][] = $post->in_reply_to_post_id;
                    } elseif($post->in_retweet_of_post_id != null) {
                        $twitter_totals["retweets"]++;
                        $user_posts["retweets"][] = $post->in_retweet_of_post_id;
                    } else {
                        continue;
                    }
                }

                $insight_data = self::createInsightRatioData($twitter_totals,$twitter_zero_check,$user_posts);
                $post_dao = DAOFactory::getDAO('PostDAO');
                $replied_post = array();
                $replied_post[] = $post_dao->getPost($user_posts["retweets"][0], $instance->network);
                $my_insight = new Insight();

                //REQUIRED: Set the insight's required attributes
                $my_insight->slug = 'twitter_ratios'; //slug to label this insight's content
                $my_insight->instance_id = $instance->id;
                $my_insight->date = date('Y-m-d'); //date is often this or $simplified_post_date
                $my_insight->headline = $this->getVariableCopy(array(
                    "Last week's %username's twitter activity looked like this.",
                    "Lets take a look at %username's twitter activity.",
                    "What did %username do on Twitter this week ?"));
                $my_insight->text = $insight_data["text"];
                $post_for_insight = array();
                $post_for_insight[] = $insight_data["post"];
                $my_insight->setPosts($post_for_insight);
                $my_insight->filename = basename(__FILE__, ".php"); //Same for every insight, must be set exactly this way
                $my_insight->emphasis = Insight::EMPHASIS_MED; //Set emphasis optionally, default is Insight::EMPHASIS_LOW

                $this->insight_dao->insertInsight($my_insight);

                } else {
                    $this->logger->logSuccess("Only ".sizeof( $last_week_of_posts).
                    " posts last week, not enough to calculate style stats ", __METHOD__.','.__LINE__);
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
        private function createInsightRatioData($twitter_totals, $twitter_zero_check,$user_posts) {
            $non_zero_key;
            $non_zero_count = 0;
            $insight_terms = new InsightTerms('twitter');
            $post_dao = DAOFactory::getDAO('PostDAO');
            $insight_data = array(
                "text" => "",
                "post" => null,
            );
            foreach (array_keys($twitter_totals) as $key) {
                if($twitter_totals[$key] != 0) {
                    $twitter_zero_check[$key] = false;
                    $non_zero_key = $key;
                    $non_zero_count++;
                } else {
                    $twitter_zero_check[$key] = true;
                }
            }
            //Checks to make sure only one type of post has been created. Ratio can't be calculated with one type.
            if($non_zero_count == 1) {
                $one_activity_text = '';
                switch ($non_zero_key) {
                    case "usertweets":
                        $one_activity_text = "$this->username only tweeted this week. 
                        Why not get the coversation flowing this week by replying to other users ?";
                        $key = array_rand($user_posts["usertweets"], 1);
                        $insight_data["post"] = $post_dao->getPost($user_posts["usertweets"][$key], $instance->network);
                        break;
                    case "replies":
                        $one_activity_text = "$this->username only replied to other users this week.
                         Why not share the best tweets with a retweet this week ?";
                        $key = array_rand($user_posts["replies"], 1);
                        $insight_data["post"] = $post_dao->getPost($user_posts["replies"][$key], $instance->network);
                        break;
                    case "retweets":
                        $one_activity_text = "$this->username only retweeted other user's tweets this week.
                         Why not give other users something to retweet with some new tweets this week ?";
                        $key = array_rand($user_posts["retweets"], 1);
                        $insight_data["post"] = $post_dao->getPost($user_posts["retweets"][$key], $instance->network);
                        break;
                    case "favorites":
                        $one_activity_text = "$this->username only favorited other user's tweets this week.
                        Why not share the best Twitter has to offer with a retweet this week ?";
                        $key = array_rand($user_posts["favorites"], 1);
                        $insight_data["post"] = $post_dao->getPost($user_posts["favorites"][$key], $instance->network);
                        break;
                    }
                $insight_data["text"] = $one_activity_text;
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
                $ratio1;
                $ratio2;
                $ratio_ending;
                $terms = $insight_terms->getMultiplierAdverb($calulated_ratio);
                switch ($twitter_totals_keys[0]) {
                    case "usertweets":
                        $ratio1 = "tweets $this->username wrote was ";
                        $ratio_ending = "Here's one of $this->username's tweets from last week:";
                        $key = array_rand($user_posts["usertweets"], 1);
                        $insight_data["post"] = $post_dao->getPost($user_posts["usertweets"][$key], "twitter");
                        break;
                    case "replies":
                        $ratio1 = " replies $this->username wrote was ";
                        $ratio_ending = "Here's one of the tweets $this->username replied to:";
                        $key = array_rand($user_posts["replies"], 1);
                        $insight_data["post"] = $post_dao->getPost($user_posts["replies"][$key], "twitter");
                        break;
                    case "retweets":
                        $ratio1 = "tweets $this->username retweeted was ";
                        $ratio_ending = "Here's one of those retweets:";
                        $key = array_rand($user_posts["retweets"], 1);
                        $insight_data["post"] = $post_dao->getPost($user_posts["retweets"][$key], "twitter");
                        break;
                     case "favorites":
                         $ratio1 = "tweets $this->username favorited was ";
                         $ratio_ending = "Here's a post $this->username favorited last week:";
                         $key = array_rand($user_posts["favorites"], 1);
                         $insight_data["post"] = $user_posts["favorites"][$key];
                         break;
                    }
                switch ($twitter_totals_keys[1]) {
                    case "usertweets":
                        $ratio2 = "tweets $this->username tweeted.";
                        break;
                    case "replies":
                        $ratio2 = "replies $this->username wrote.";
                        break;
                    case "retweets":
                        $ratio2 = "tweets $this->username retweeted.";
                        break;
                    case "favorites":
                        $ratio2 = "tweets $this->username favorited.";
                        break;
                    }
                if($terms == "1x") {
                    $terms = "<strong>the same</strong> as ";
                } else {
                    $terms = "about <strong>$terms</strong>";
                }

                $insight_data["text"] = "Last week the number of $ratio1 $terms" 
                ." the number of $ratio2 <br> $ratio_ending";
            }
            return $insight_data;
        }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('TwitterRatiosInsight');