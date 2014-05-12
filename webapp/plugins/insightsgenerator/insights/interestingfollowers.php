<?php
/*
 Plugin Name: Interesting Followers
 Description: New least likely and verified followers.
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/interestingfollowers.php
 *
 * Copyright (c) 2012-2013 Gina Trapani
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
 * @copyright 2012-2013 Gina Trapani, Nilaksh Das
 * @author Gina Trapani <ginatrapani [at] gmail [dot] com>
 * @author Nilaksh Das <nilakshdas@gmail.com>
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

        // Least likely followers based on follower-to-followee ratio
        // We grab up to 10 possibilities, then filter for some spam account criteria and take the top 3 that remain
        $least_likely_followers = $follow_dao->getLeastLikelyFollowersByDay($instance->network_user_id,
            $instance->network, 0, 10);
        $least_likely_followers = array_filter($least_likely_followers, array($this, 'filterFollowers'));
        $least_likely_followers = array_slice($least_likely_followers, 0, 3);

        if (sizeof($least_likely_followers) > 0 ) { //if not null, store insight
            if (sizeof($least_likely_followers) > 1) {
                $my_insight->headline = '<strong>'.sizeof($least_likely_followers).
                    " interesting people</strong> ". "followed $this->username.";
                $my_insight->slug = 'least_likely_followers';
                $my_insight->emphasis = Insight::EMPHASIS_MED;
                $my_insight->setPeople($least_likely_followers);
            } else {
                $follower = $least_likely_followers[0];
                $name = $this->getFollowerName($follower);
                $my_insight->headline = "Hey, did you see that " .$name . " followed $this->username?";
                $my_insight->header_image = $verified_followers[0]->avatar;
                $my_insight->slug = 'least_likely_followers';
                $my_insight->emphasis = Insight::EMPHASIS_MED;
                $my_insight->setPeople($least_likely_followers);
            }
        }

        // Verified followers
        $verified_followers = $follow_dao->getVerifiedFollowersByDay($instance->network_user_id, $instance->network, 0,
        3);

        if (sizeof($verified_followers) > 0 ) { //if not null, store insight
            if (sizeof($verified_followers) > 1) {
                $my_insight->slug = 'verified_followers';
                $my_insight->headline = '<strong>'.sizeof($verified_followers)." verified users</strong> ".
                    "followed $this->username!";
                $my_insight->emphasis = Insight::EMPHASIS_HIGH;
                $my_insight->setPeople($verified_followers);
            } else {
                $follower = $verified_followers[0];
                $name = $this->getFollowerName($follower);
                $my_insight->slug = 'verified_followers';
                $my_insight->headline = 'Wow: <strong>'.$name."</strong>, a verified user, followed $this->username.";
                $my_insight->header_image = $verified_followers[0]->avatar;
                $my_insight->emphasis = Insight::EMPHASIS_HIGH;
                $my_insight->setPeople($verified_followers);
            }
        }
        if ($my_insight->headline) {
            $this->insight_dao->insertInsight($my_insight);
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
