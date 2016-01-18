<?php
/*
 Plugin Name: Interesting Followers
 Description: New least likely, verified, and local followers.
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/interestingfollowers.php
 *
 * Copyright 2012-2016 Gina Trapani, Nilaksh Das, Chris Moyer
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
 * @copyright 2012-2016 Gina Trapani, Nilaksh Das, Chris Moyer
 * @author Gina Trapani <ginatrapani [at] gmail [dot] com>
 * @author Nilaksh Das <nilakshdas@gmail.com>
 * @author Chris Moyer <chris [at] inarow [dot] net>
 */

class InterestingFollowersInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $my_insight = new Insight();
        $my_insight->instance_id = $instance->id;
        $my_insight->date = $this->insight_date; //date of the data this insight applies to

        $my_insight->text = '';
        $my_insight->filename = basename(__FILE__, ".php");
        $follow_dao = DAOFactory::getDAO('FollowDAO');

        // Least likely followers who are not verified, based on follower-to-followee ratio
        // We grab up to 10 possibilities, then filter for some spam account criteria and take the top 3 that remain
        $least_likely_followers = $follow_dao->getLeastLikelyFollowersByDay($instance->network_user_id,
            $instance->network, 0, 10);
        $least_likely_followers = array_filter($least_likely_followers, array($this, 'filterFollowers'));
        $least_likely_followers = array_slice($least_likely_followers, 0, 3);

        $total_followers = sizeof($least_likely_followers);
        if ($total_followers > 0 ) { //if not null, store insight
            if ($total_followers > 1) {
                $my_insight->headline = "$this->username got $total_followers interesting new followers";
                $my_insight->slug = 'least_likely_followers';
                $my_insight->emphasis = Insight::EMPHASIS_MED;
                $my_insight->setPeople($least_likely_followers);
            } else {
                $follower = $least_likely_followers[0];
                $name = $this->getFollowerName($follower);
                $my_insight->headline = "$this->username got an interesting new follower";
                $my_insight->slug = 'least_likely_followers';
                $my_insight->emphasis = Insight::EMPHASIS_MED;
                $my_insight->setPeople($least_likely_followers);
                $my_insight->header_image = $follower->avatar;
            }
        }

        // Verified followers
        $verified_followers = $follow_dao->getVerifiedFollowersByDay($instance->network_user_id, $instance->network, 0,
            3);

        $total_followers = sizeof($verified_followers);
        if ($total_followers > 0 ) { //if not null, store insight
            if ($total_followers > 1) {
                $my_insight->slug = 'verified_followers';

                $my_insight->headline = "$this->username got $total_followers new verified followers!";
                $my_insight->emphasis = Insight::EMPHASIS_HIGH;
                $my_insight->setPeople($verified_followers);
            } else {
                $follower = $verified_followers[0];
                $name = $this->getFollowerName($follower);
                $my_insight->slug = 'verified_followers';
                $my_insight->headline = "$this->username got a new verified follower!";
                $my_insight->header_image = $verified_followers[0]->avatar;
                $my_insight->emphasis = Insight::EMPHASIS_HIGH;
                $my_insight->setPeople($verified_followers);
            }

            $total_verified = $follow_dao->getVerifiedFollowerCount($instance->network_user_id, $instance->network);
            if ($total_verified > $total_followers) {
                $my_insight->text = "That makes a total of <strong>".number_format($total_verified)
                    ." verified followers</strong>.";
            }
            $my_insight->header_image = 'https://www.thinkup.com/assets/images/insights/2014-07/verified.png';
        }
        if ($my_insight->headline) {
            $this->insight_dao->insertInsight($my_insight);
        }

        //Local followers that are neither verified or least likely
        if (isset($user->location) && $user->location != "") {
            $local_followers_to_check = $follow_dao->getFollowersFromLocationByDay($instance->network_user_id,
                $instance->network, $user->location, 0);

            if (count($local_followers_to_check)) {
                //Clear out insight vars
                $my_insight = null;
                $my_insight = new Insight();
                $my_insight->instance_id = $instance->id;
                $my_insight->date = $this->insight_date; //date of the data this insight applies to

                $my_insight->text = '';
                $my_insight->filename = basename(__FILE__, ".php");

                //Create list of user IDs that have already appeared in an insight
                $follower_ids_already_reported_on = array();
                foreach ($least_likely_followers as $follower) {
                    $follower_ids_already_reported_on[] = $follower->id;
                }
                foreach ($verified_followers as $follower) {
                    $follower_ids_already_reported_on[] = $follower->id;
                }

                //Make sure none of the local followers have already been reported on
                //debug
                //print_r($follower_ids_already_reported_on);
                $local_followers = array();
                foreach ($local_followers_to_check as $follower) {
                    if (!in_array($follower->id, $follower_ids_already_reported_on) ) {
                        $local_followers[] = $follower;
                    }
                }

                //debug
                //print_r($local_followers);
                $total_followers = count($local_followers);
                if ($total_followers) {
                    $headline = "$this->username got "
                      .($total_followers > 1 ? "$total_followers new followers" : "a new follower")
                      ." in ".$user->location;

                    if (count($local_followers) == 1) {
                        $header_image = $local_followers[0]->avatar;
                    } else {
                        $header_image = '';
                    }

                    $my_insight = new Insight();

                    //REQUIRED: Set the insight's required attributes
                    $my_insight->headline = $headline; // or just set a string like 'Ohai';
                    $my_insight->slug = 'local_followers'; //slug to label this insight's content
                    $my_insight->instance_id = $instance->id;
                    $my_insight->date = $this->insight_date; //date is often this or $simplified_post_date
                    $my_insight->text = ''; // or just set a strong like "Greetings humans";
                    $my_insight->header_image = $header_image;
                    $my_insight->filename = basename(__FILE__, ".php");
                    $my_insight->emphasis = Insight::EMPHASIS_MED;
                    $my_insight->setPeople($local_followers);
                    $this->insight_dao->insertInsight($my_insight);
                }
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

    /**
     * Check new followers for various spammy criteria
     *
     * @param Follower $follower The follower to examine
     * @return bool True for OK users, False for Questionable/Spam users
     */
    public function filterFollowers($follower) {
        // Show users that post
        if ($follower->post_count < 100) {
            return false;
        }

        // Show users that take the time to set a profile
        if (strstr($follower->avatar, 'default_profile') !== false) {
            return false;
        }

        // Show users that have info for us to display
        if (empty($follower->description) || empty($follower->url)) {
            return false;
        }

        return true;
    }

    /**
     * Return a string representing a follower's name.
     * @param User $follower Follower to process
     * @return str Name
     */
    public function getFollowerName($follower) {
        if (!empty($follower->full_name)) {
            return $follower->full_name;
        }

        $name = $follower->username;
        if ($follower->network == 'twitter') {
            $name = '@'. $name;
        }

        return $name;
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('InterestingFollowersInsight');
