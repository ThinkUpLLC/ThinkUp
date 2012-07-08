<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.InsightsGenerator.php
 *
 * Copyright (c) 2012 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
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
 * Insights Generator
 *
 * Generate and store insights for faster dashboard and view rendering.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class InsightsGenerator {
    /**
     *
     * @var Instance
     */
    var $instance;
    /**
     * Constructor
     * @param Instance $instance
     * @return InsightsGenerator
     */
    public function __construct(Instance $instance) {
        $this->instance = $instance;
        $this->logger = Logger::getInstance();
        $this->logger->setUsername($instance->network_username);
    }
    /**
     * Generate insights and save to storage.
     * @param int $number_days Number of days to back-calculate insights
     */
    public function generateInsights($number_days=3) {
        $this->logger->logUserSuccess("Calculating insights for last ".$number_days." days.",
        __METHOD__.','.__LINE__);
        self::generateCachedDashboardInsights();
        self::generateInsightBaselines($number_days);
        self::generateInsightFeedItems($number_days);
    }
    /**
     * Calculate and store insights for a specified number of days.
     * @param int $number_days Number of days to backfill
     */
    private function generateInsightFeedItems($number_days=3) {
        $post_dao = DAOFactory::getDAO('PostDAO');
        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $insight_dao = DAOFactory::getDAO('InsightDAO');

        // Get retweeted posts for last 7 days
        $posts = $post_dao->getMostRetweetedPostsInLastWeek($this->instance->network_username,
        $this->instance->network, 40, $is_public = false);
        $posts = $post_dao->getAllPostsByUsernameOrderedBy($this->instance->network_username,
        $network=$this->instance->network, $count=0, $order_by="pub_date", $in_last_x_days = $number_days,
        $iterator = false, $is_public = false);

        $baseline_date = null;
        // foreach post
        foreach ($posts as $post) {
            $simplified_post_date = date('Y-m-d', strtotime($post->pub_date));

            if ($simplified_post_date != $baseline_date) { //need to get baselines
                $average_retweet_count_7_days =
                $insight_baseline_dao->getInsightBaseline('avg_retweet_count_last_7_days', $this->instance->id,
                $simplified_post_date);

                $average_retweet_count_30_days =
                $insight_baseline_dao->getInsightBaseline('avg_retweet_count_last_30_days', $this->instance->id,
                $simplified_post_date);

                $high_retweet_count_7_days =
                $insight_baseline_dao->getInsightBaseline('high_retweet_count_last_7_days', $this->instance->id,
                $simplified_post_date);

                $high_retweet_count_30_days =
                $insight_baseline_dao->getInsightBaseline('high_retweet_count_last_30_days', $this->instance->id,
                $simplified_post_date);

                $high_retweet_count_365_days =
                $insight_baseline_dao->getInsightBaseline('high_retweet_count_last_365_days', $this->instance->id,
                $simplified_post_date);

                $baseline_date = $post->pub_date;
            }
            if (isset($high_retweet_count_365_days->value)
            && $post->all_retweets >= $high_retweet_count_365_days->value) {
                $insight_dao->insertInsight('retweet_high_365_day_'.$post->id, $this->instance->id,
                $simplified_post_date, "New 365-day high!", $post->all_retweets." people retweeted your tweet.",
                Insight::EMPHASIS_HIGH, serialize($post));

                $insight_dao->deleteInsight('retweet_high_30_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
                $insight_dao->deleteInsight('retweet_high_7_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
                $insight_dao->deleteInsight('retweet_spike_30_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
                $insight_dao->deleteInsight('retweet_spike_7_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
            } elseif (isset($high_retweet_count_30_days->value)
            && $post->all_retweets >= $high_retweet_count_30_days->value) {
                $insight_dao->insertInsight('retweet_high_30_day_'.$post->id, $this->instance->id,
                $simplified_post_date, "New 30-day high!", $post->all_retweets." people retweeted your tweet.",
                Insight::EMPHASIS_HIGH, serialize($post));

                $insight_dao->deleteInsight('retweet_high_7_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
                $insight_dao->deleteInsight('retweet_spike_30_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
                $insight_dao->deleteInsight('retweet_spike_7_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
            } elseif (isset($high_retweet_count_7_days->value)
            && $post->all_retweets >= $high_retweet_count_7_days->value) {
                $insight_dao->insertInsight('retweet_high_7_day_'.$post->id, $this->instance->id, $simplified_post_date,
                "New 7-day high!", $post->all_retweets." people retweeted your tweet.",
                Insight::EMPHASIS_HIGH, serialize($post));

                $insight_dao->deleteInsight('retweet_high_30_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
                $insight_dao->deleteInsight('retweet_spike_30_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
                $insight_dao->deleteInsight('retweet_spike_7_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
            } elseif (isset($average_retweet_count_30_days->value)
            && $post->all_retweets > ($average_retweet_count_30_days->value*2)) {
                $multiplier = floor($post->all_retweets/$average_retweet_count_30_days->value);
                $insight_dao->insertInsight('retweet_spike_30_day_'.$post->id, $this->instance->id,
                $simplified_post_date, "Retweet spike!", $post->all_retweets.
                " people reshared your tweet, more than ".$multiplier. "x your 30-day average.", Insight::EMPHASIS_LOW,
                serialize($post));

                $insight_dao->deleteInsight('retweet_high_30_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
                $insight_dao->deleteInsight('retweet_high_7_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
                $insight_dao->deleteInsight('retweet_spike_7_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
            } elseif (isset($average_retweet_count_7_days->value)
            && $post->all_retweets > ($average_retweet_count_7_days->value*2)) {
                $multiplier = floor($post->all_retweets/$average_retweet_count_7_days->value);
                $insight_dao->insertInsight('retweet_spike_7_day_'.$post->id, $this->instance->id,
                $simplified_post_date, "Retweet spike!", $post->all_retweets." people reshared your tweet, more than "
                .$multiplier. "x your 7-day average.", Insight::EMPHASIS_LOW, serialize($post));

                $insight_dao->deleteInsight('retweet_high_30_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
                $insight_dao->deleteInsight('retweet_high_7_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
                $insight_dao->deleteInsight('retweet_spike_30_day_'.$post->id, $this->instance->id,
                $simplified_post_date);
            }

            //If not a reply or retweet and geoencoded, show the map in the stream
            if (!isset($post->in_reply_to_user_id) && !isset($post->in_reply_to_post_id)
            && !isset($post->in_retweet_of_post_id) && $post->reply_count_cache > 5) {
                $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
                $options = $plugin_option_dao->getOptionsHash('geoencoder', true);
                if (isset($options['gmaps_api_key']->option_value) && $post->is_geo_encoded == 1) {
                    $insight_dao->insertInsight('geoencoded_replies', $this->instance->id, $simplified_post_date,
                   "Going global!", "Your post got replies and retweets from locations all over the map.",
                    Insight::EMPHASIS_LOW, serialize($post));
                }
            }

            //If more than 20 replies, let user know most-frequently mentioned words are available
            if ($post->reply_count_cache >= 20) {
                if (!isset($config)) {
                    $config = Config::getInstance();
                }
                $insight_dao->insertInsight('replies_frequent_words_'.$post->id, $this->instance->id,
                $simplified_post_date, "Reply spike!",
               'Your post got '.$post->reply_count_cache.' replies. See <a href="'.$config->getValue('site_root_path').
                'post/?t='.$post->post_id.'&n='.$post->network.'">the most frequently-mentioned reply words</a>.',
                Insight::EMPHASIS_HIGH, serialize($post));
            }
        }

        //Generate least likely followers insights
        $follow_dao = DAOFactory::getDAO('FollowDAO');
        $days_ago = 0;
        while ($days_ago < $number_days) {
            //For each of the past 7 days (remove this later & just do day by day?)
            //get least likely followers for that day
            $least_likely_followers = $follow_dao->getLeastLikelyFollowersByDay($this->instance->network_user_id,
            $this->instance->network, $days_ago, 3);
            if (sizeof($least_likely_followers) > 0 ) { //if not null, store insight
                //If followers have more followers than half of what the instance has, jack up emphasis
                $emphasis = Insight::EMPHASIS_LOW;
                foreach ($least_likely_followers as $least_likely_follower) {
                    if ($least_likely_follower->follower_count > ($this->user->follower_count/2)) {
                        $emphasis = Insight::EMPHASIS_HIGH;
                    }
                }

                $insight_date = new DateTime();
                //Not PHP 5.2 compatible
                //$insight_date->sub(new DateInterval('P'.$days_ago.'D'));
                $insight_date->modify('-'.$days_ago.' day');
                $insight_date = $insight_date->format('Y-m-d');
                if (sizeof($least_likely_followers) > 1) {
                    $insight_dao->insertInsight('least_likely_followers', $this->instance->id, $insight_date,
                    "Good people:", sizeof($least_likely_followers)." interesting users followed you.",
                    $emphasis, serialize($least_likely_followers));
                } else {
                    $insight_dao->insertInsight('least_likely_followers', $this->instance->id, $insight_date,
                    "Hey!", "An interesting user followed you.",
                    $emphasis, serialize($least_likely_followers));
                }
            }
            $days_ago++;
        }

        //Generate new list membership insights
        $group_membership_dao = DAOFactory::getDAO('GroupMemberDAO');
        $days_ago = 0;
        while ($days_ago < $number_days) {
            $insight_date = new DateTime();
            $insight_date->modify('-'.$days_ago.' day');
            $insight_date = $insight_date->format('Y-m-d');
            $this->logger->logInfo("Getting new group memberships for ".$insight_date, __METHOD__.','
            .__LINE__);
            //get new group memberships per day
            $new_groups = $group_membership_dao->getNewMembershipsByDate($this->instance->network,
            $this->instance->network_user_id,
            $insight_date);
            if (sizeof($new_groups) > 0 ) { //if not null, store insight
                $group_membership_count_dao = DAOFactory::getDAO('GroupMembershipCountDAO');
                $list_membership_count_history_by_day = $group_membership_count_dao->getHistory(
                $this->instance->network_user_id, $this->instance->network, 'DAY', 15);
                if (sizeof($new_groups) > 1) {
                    $group_name_list = '';
                    foreach ($new_groups as $group) {
                        if ($group == end($new_groups)) {
                            $group_name_list .= " and ";
                        } else {
                            if ($group_name_list != '') {
                                $group_name_list .= ", ";
                            }
                        }
                        $group->setMetadata();
                        $group_name_list .= '<a href="'.$group->url.'">'.$group->keyword.'</a>';
                    }
                    $insight_dao->insertInsight('new_group_memberships', $this->instance->id, $insight_date,
                    "Filed:", "You got added to ".sizeof($new_groups)." lists: ".$group_name_list.
                    ", bringing your total to ".number_format(end($list_membership_count_history_by_day['history'])).
                    ".", Insight::EMPHASIS_LOW, serialize($list_membership_count_history_by_day));
                } else {
                    $new_groups[0]->setMetadata();
                    $insight_dao->insertInsight('new_group_memberships', $this->instance->id, $insight_date, "Filed:",
                    "You got added to a new list, ".'<a href="'.$new_groups[0]->url.'">'.$new_groups[0]->keyword.
                    "</a>, bringing your total to ".
                    number_format(end($list_membership_count_history_by_day['history'])).
                    ".", Insight::EMPHASIS_LOW, serialize($list_membership_count_history_by_day));
                }
            }
            $days_ago++;
        }

        //Follower count history milestone
        $days_ago = $number_days;
        while ($days_ago > -1) {
            $insight_date = new DateTime();
            $insight_date->modify('-'.$days_ago.' day');
            $insight_day_of_week = (int) $insight_date->format('w');
            $this->logger->logInfo("Insight day of week is ".$insight_day_of_week, __METHOD__.','
            .__LINE__);
            $insight_date_formatted = $insight_date->format('Y-m-d');

            $insight_day_of_month = (int) $insight_date->format('j');
            if ($insight_day_of_month == 1) { //it's the first day of the month
                $follower_count_dao = DAOFactory::getDAO('FollowerCountDAO');
                //by month
                $follower_count_history_by_month = $follower_count_dao->getHistory($this->instance->network_user_id,
                $this->instance->network, 'MONTH', 15, $insight_date_formatted);
                if ( isset($follower_count_history_by_month['milestone'])
                && $follower_count_history_by_month["milestone"]["will_take"] > 0
                && $follower_count_history_by_month["milestone"]["next_milestone"] > 0) {
                    $insight_text = "Upcoming milestone: ";
                    $insight_text .= $follower_count_history_by_month['milestone']['will_take'].' month';
                    if ($follower_count_history_by_month['milestone']['will_take'] > 1) {
                        $insight_text .= 's';
                    }
                    $insight_text .= ' till you reach '.
                    number_format($follower_count_history_by_month['milestone']['next_milestone']);
                    $insight_text .= ' followers at your current growth rate.';

                    $insight_dao->insertInsight('follower_count_history_by_month_milestone', $this->instance->id,
                    $insight_date_formatted, "Milestone:", $insight_text, Insight::EMPHASIS_HIGH,
                    serialize($follower_count_history_by_month));
                }
            } else if ($insight_day_of_week == 0) { //it's Sunday
                $follower_count_dao = DAOFactory::getDAO('FollowerCountDAO');
                //by week
                $follower_count_history_by_week = $follower_count_dao->getHistory($this->instance->network_user_id,
                $this->instance->network, 'WEEK', 15, $insight_date_formatted);
                $this->logger->logInfo($insight_date_formatted." is Sunday; Count by week stats are ".
                Utils::varDumpToString($follower_count_history_by_week) , __METHOD__.','
                .__LINE__);
                if ( isset($follower_count_history_by_week['milestone'])
                && $follower_count_history_by_week["milestone"]["will_take"] > 0
                && $follower_count_history_by_week["milestone"]["next_milestone"] > 0 ) {
                    $insight_text = "Upcoming milestone: ";
                    $insight_text .= $follower_count_history_by_week['milestone']['will_take'].' week';
                    if ($follower_count_history_by_week['milestone']['will_take'] > 1) {
                        $insight_text .= 's';
                    }
                    $insight_text .= ' till you reach '.
                    number_format($follower_count_history_by_week['milestone']['next_milestone']);
                    $insight_text .= ' followers at your current growth rate.';
                    $this->logger->logInfo("Storing insight ".$insight_text, __METHOD__.','
                    .__LINE__);

                    $insight_dao->insertInsight('follower_count_history_by_week_milestone', $this->instance->id,
                    $insight_date_formatted, "Milestone:", $insight_text, Insight::EMPHASIS_HIGH,
                    serialize($follower_count_history_by_week));
                }
            }

            $existing_insight = $insight_dao->getInsight("posts_on_this_day_flashback", $this->instance->id,
            $insight_date_formatted);
            if (!isset($existing_insight)) {
                //Generate flashback post list
                $flashback_posts = $post_dao->getOnThisDayFlashbackPosts($this->instance->network_user_id,
                $this->instance->network, $insight_date_formatted);
                if (isset($flashback_posts) && sizeof($flashback_posts) > 0 ) {
                    $oldest_post_year = date(date( 'Y' , strtotime($flashback_posts[0]->pub_date)));
                    $current_year = date('Y');
                    $number_of_years_ago = $current_year - $oldest_post_year;
                    $plural = ($number_of_years_ago > 1 )?'s':'';
                    $insight_dao->insertInsight("posts_on_this_day_flashback", $this->instance->id,
                    $insight_date_formatted, $oldest_post_year." flashback:", $number_of_years_ago." year".
                    $plural. " ago today, you posted: ", Insight::EMPHASIS_MED, serialize($flashback_posts));
                }
            }

            $days_ago--;
        }
    }
    /**
     * Calculate and store insight baselines for a specified number of days.
     * @param int $number_days Number of days to backfill
     */
    private function generateInsightBaselines($number_days=3) {
        $post_dao = DAOFactory::getDAO('PostDAO');
        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');

        $days_ago = 0;
        // Generate baseline post insights for the last 7 days

        while ($days_ago < $number_days) {
            $since_date = date("Y-m-d", strtotime("-".$days_ago." day"));

            //Save average retweets over past 7 days
            $average_retweet_count_7_days = null;
            $average_retweet_count_7_days = $post_dao->getAverageRetweetCount($this->instance->network_username,
            $this->instance->network, 7, $since_date);
            if ($average_retweet_count_7_days != null ) {
                $insight_baseline_dao->insertInsightBaseline('avg_retweet_count_last_7_days', $this->instance->id,
                $average_retweet_count_7_days, $since_date);
                $this->logger->logSuccess("Averaged $average_retweet_count_7_days retweets in the 7 days before ".
                $since_date, __METHOD__.','.__LINE__);
            }
            //Save average retweets over past 30 days
            $average_retweet_count_30_days = null;
            $average_retweet_count_30_days = $post_dao->getAverageRetweetCount($this->instance->network_username,
            $this->instance->network, 30, $since_date);
            if ($average_retweet_count_30_days != null ) {
                $insight_baseline_dao->insertInsightBaseline('avg_retweet_count_last_30_days', $this->instance->id,
                $average_retweet_count_30_days, $since_date);
                $this->logger->logSuccess("Averaged $average_retweet_count_30_days retweets in the 30 days before ".
                $since_date, __METHOD__.','.__LINE__);
            }

            //Save retweet high for last 7 days
            $high_retweet_count_7_days = $post_dao->getAllPostsByUsernameOrderedBy($this->instance->network_username,
            $network=$this->instance->network, 1, 'retweets', 7, $iterator = false, $is_public = false,
            $since=$since_date);
            if ($high_retweet_count_7_days != null ) {
                $high_retweet_count_7_days = $high_retweet_count_7_days[0]->all_retweets;
                $insight_baseline_dao->insertInsightBaseline('high_retweet_count_last_7_days', $this->instance->id,
                $high_retweet_count_7_days, $since_date);
                $this->logger->logSuccess("High of $high_retweet_count_7_days retweets in the 7 days before ".
                $since_date, __METHOD__.','.__LINE__);
            }

            //Save retweet high for last 30 days
            $high_retweet_count_30_days = $post_dao->getAllPostsByUsernameOrderedBy($this->instance->network_username,
            $network=$this->instance->network, 1, 'retweets', 30, $iterator = false, $is_public = false,
            $since=$since_date);
            if ($high_retweet_count_30_days != null ) {
                $high_retweet_count_30_days = $high_retweet_count_30_days[0]->all_retweets;
                $insight_baseline_dao->insertInsightBaseline('high_retweet_count_last_30_days', $this->instance->id,
                $high_retweet_count_30_days, $since_date);
                $this->logger->logSuccess("High of $high_retweet_count_30_days retweets in the 30 days before ".
                $since_date, __METHOD__.','.__LINE__);
            }

            //Save retweet high for last 365 days
            $high_retweet_count_365_days = $post_dao->getAllPostsByUsernameOrderedBy($this->instance->network_username,
            $network=$this->instance->network, 1, 'retweets', 365, $iterator = false, $is_public = false,
            $since=$since_date);
            if ($high_retweet_count_365_days != null ) {
                $high_retweet_count_365_days = $high_retweet_count_365_days[0]->all_retweets;
                $insight_baseline_dao->insertInsightBaseline('high_retweet_count_last_365_days', $this->instance->id,
                $high_retweet_count_365_days, $since_date);
                $this->logger->logSuccess("High of $high_retweet_count_365_days retweets in the 365 days before ".
                $since_date, __METHOD__.','.__LINE__);
            }

            $days_ago++;
        }
    }
    /**
     * Generate insights for dashboard modules and save to storage.
     * This function will be deprecated when the insights stream replaces the dashboard entirely.
     */
    private function generateCachedDashboardInsights() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $simplified_date = date('Y-m-d');

        //Cache FollowMySQLDAO::getLeastLikelyFollowersThisWeek
        $follow_dao = DAOFactory::getDAO('FollowDAO');
        $results = $follow_dao->getLeastLikelyFollowersThisWeek($this->instance->network_user_id,
        $this->instance->network, 13, 1);
        if (isset($results)) {
            //delete existing
            $insight_dao->deleteInsightsBySlug("FollowMySQLDAO::getLeastLikelyFollowersThisWeek", $this->instance->id);
            //insert new
            $insight_dao->insertInsight("FollowMySQLDAO::getLeastLikelyFollowersThisWeek", $this->instance->id,
            $simplified_date, '', '', Insight::EMPHASIS_LOW, serialize($results));
        }

        //Cache PostMySQLDAO::getHotPosts
        $post_dao = DAOFactory::getDAO('PostDAO');
        $hot_posts = $post_dao->getHotPosts($this->instance->network_user_id, $this->instance->network, 10);
        if (sizeof($hot_posts) > 3) {
            $hot_posts_data = self::getHotPostVisualizationData($hot_posts, $this->instance->network);
            //delete existing
            $insight_dao->deleteInsightsBySlug("PostMySQLDAO::getHotPosts", $this->instance->id);
            //insert new
            $insight_dao->insertInsight("PostMySQLDAO::getHotPosts", $this->instance->id,
            $simplified_date, '', '', Insight::EMPHASIS_LOW, serialize($hot_posts_data));
        }

        //Cache ShortLinkMySQLDAO::getRecentClickStats
        $short_link_dao = DAOFactory::getDAO('ShortLinkDAO');
        $click_stats = $short_link_dao->getRecentClickStats($this->instance, 10);
        if (sizeof($click_stats) > 3) {
            $click_stats_data = self::getClickStatsVisualizationData($click_stats);
            //delete existing
            $insight_dao->deleteInsightsBySlug("ShortLinkMySQLDAO::getRecentClickStats", $this->instance->id);
            //insert new
            $insight_dao->insertInsight("ShortLinkMySQLDAO::getRecentClickStats", $this->instance->id,
            $simplified_date, '', '', Insight::EMPHASIS_LOW, serialize($click_stats_data));
        }

        //Cache PostMySQLDAO::getAllPostsByUsernameOrderedBy // getMostRepliedToPostsInLastWeek
        $most_replied_to_1wk = $post_dao->getMostRepliedToPostsInLastWeek($this->instance->network_username,
        $this->instance->network, 5);
        if (sizeof($most_replied_to_1wk) > 1) {
            //delete existing
            $insight_dao->deleteInsightsBySlug("PostMySQLDAO::getMostRepliedToPostsInLastWeek", $this->instance->id);
            //insert new
            $insight_dao->insertInsight("PostMySQLDAO::getMostRepliedToPostsInLastWeek", $this->instance->id,
            $simplified_date, '', '', Insight::EMPHASIS_LOW, serialize($most_replied_to_1wk));
        }

        //Cache PostMySQLDAO::getAllPostsByUsernameOrderedBy // getMostRetweetedPostsInLastWeek
        $most_retweeted_1wk = $post_dao->getMostRetweetedPostsInLastWeek($this->instance->network_username,
        $this->instance->network, 5);
        if (sizeof($most_retweeted_1wk) > 1) {
            //delete existing
            $insight_dao->deleteInsightsBySlug("PostMySQLDAO::getMostRetweetedPostsInLastWeek", $this->instance->id);
            //insert new
            $insight_dao->insertInsight("PostMySQLDAO::getMostRetweetedPostsInLastWeek", $this->instance->id,
            $simplified_date, '', '', Insight::EMPHASIS_LOW, serialize($most_retweeted_1wk));
        }

        //Cache PostMySQLDAO::getClientsUsedByUserOnNetwork
        $clients_usage = $post_dao->getClientsUsedByUserOnNetwork($this->instance->network_user_id,
        $this->instance->network);
        //delete existing
        $insight_dao->deleteInsightsBySlug("PostMySQLDAO::getClientsUsedByUserOnNetwork", $this->instance->id);
        //insert new
        $insight_dao->insertInsight("PostMySQLDAO::getClientsUsedByUserOnNetwork", $this->instance->id,
        $simplified_date, '', '', Insight::EMPHASIS_LOW, serialize($clients_usage));
    }

    /**
     * Convert Hot Posts data to JSON for use with Google Charts
     * @param arr $hot_posts Array returned from PostDAO::getHotPosts
     * @return str JSON
     */
    public static function getHotPostVisualizationData($hot_posts, $network) {
        switch ($network) {
            case 'twitter':
                $post_label = 'Tweet';
                $approval_label = 'Favorites';
                $share_label = 'Retweets';
                $reply_label = 'Replies';
                break;
            case 'facebook':
            case 'facebook page':
                $post_label = 'Post';
                $approval_label = 'Likes';
                $share_label = 'Shares';
                $reply_label = 'Comments';
                break;
            case 'google+':
                $post_label = 'Post';
                $approval_label = "+1s";
                $share_label = 'Shares';
                $reply_label = 'Comments';
                break;
            default:
                $post_label = 'Post';
                $approval_label = 'Favorites';
                $share_label = 'Shares';
                $reply_label = 'Comments';
                break;
        }
        $metadata = array(
        array('type' => 'string', 'label' => $post_label),
        array('type' => 'number', 'label' => $reply_label),
        array('type' => 'number', 'label' => $share_label),
        array('type' => 'number', 'label' => $approval_label),
        );
        $result_set = array();
        foreach ($hot_posts as $post) {
            if (isset($post->post_text) && $post->post_text != '') {
                $post_text_label = htmlspecialchars_decode(strip_tags($post->post_text), ENT_QUOTES);
            } elseif (isset($post->link->title) && $post->link->title != '') {
                $post_text_label = str_replace('|','', $post->link->title);
            } elseif (isset($post->link->url) && $post->link->url != "") {
                $post_text_label = str_replace('|','', $post->link->url);
            } else {
                $post_text_label = date("M j",  date_format (date_create($post->pub_date), 'U' ));
            }

            $result_set[] = array('c' => array(
            array('v' => substr($post_text_label, 0, 100) . '...'),
            array('v' => intval($post->reply_count_cache)),
            array('v' => intval($post->all_retweets)),
            array('v' => intval($post->favlike_count_cache)),
            ));
        }
        return json_encode(array('rows' => $result_set, 'cols' => $metadata));
    }

    /**
     * Convert click stats data to JSON for Google Charts
     * @param arr $click_stats Array returned from ShortLinkDAO::getRecentClickStats
     * @return str JSON
     */
    public static function getClickStatsVisualizationData($click_stats) {
        $metadata = array(
        array('type' => 'string', 'label' => 'Link'),
        array('type' => 'number', 'label' => 'Clicks'),
        );
        $result_set = array();
        foreach ($click_stats as $link_stat) {
            $post_text_label = htmlspecialchars_decode(strip_tags($link_stat['post_text']), ENT_QUOTES);
            $result_set[] = array('c' => array(
            array('v' => substr($post_text_label, 0, 100) . '...'),
            array('v' => intval($link_stat['click_count'])),
            ));
        }
        return json_encode(array('rows' => $result_set, 'cols' => $metadata));
    }

    /**
     * Convert client usage data to JSON for Google Charts
     * @param arr $client_usage Array returned from PostDAO::getClientsUsedByUserOnNetwork
     * @return str JSON
     */
    public static function getClientUsageVisualizationData($client_usage) {
        $metadata = array(
        array('type' => 'string', 'label' => 'Client'),
        array('type' => 'number', 'label' => 'Posts'),
        );
        $result_set = array();
        foreach ($client_usage as $client => $posts) {
            $result_set[] = array('c' => array(
            array('v' => $client, 'f' => $client),
            array('v' => intval($posts)),
            ));
        }
        return json_encode(array('rows' => $result_set, 'cols' => $metadata));
    }
}