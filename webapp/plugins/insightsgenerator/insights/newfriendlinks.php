<?php
/*
 Plugin Name: New Friend Links
 Description: Displays links from new friends bios.
 When: First Run, Every Saturday
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/newfriendlinks.php
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
 * @author Gareth Brady <gareth.bray92 [at] gmail [dot] com>
 */

class NewFriendLinksInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     */
    var $slug = 'new_friend_links'; 

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $should_generate_weekly_insight = $this->shouldGenerateWeeklyInsight($this->slug, $instance, 'today',
        $regenerate=false, 6);

        if ($should_generate_weekly_insight) {
            $follow_dao = DAOFactory::getDAO('FollowDAO');
            $followers = $follow_dao->getNewFollowersWithinLastXDays($instance->network_user_id,
            $instance->network, 14);
            $followees = $follow_dao->getNewFolloweesWithinLastXDays($instance->network_user_id,
            $instance->network, 14);
            $followees_with_links = $this->getUsersWithUrl($followees);
            $followers_with_links = $this->getUsersWithUrl($followers);
            $is_followee = false;
            //no new  relationships with links.
            if($followers_with_links == null && $followees_with_links == null) {//no new  relationships with links.
                $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
            //followers have links.
            } elseif($followers_with_links != null && $followees_with_links == null) {
                $this->makeInsight($followers_with_links, 'new_friend_links', $instance, $is_followee);
            //followees have links. 
            } elseif($followees_with_links !=null && $followers_with_links == null) {
                $is_followee = true;
                $this->makeInsight($followees_with_links, 'new_friend_links', $instance , $is_followee);
            //Both have links.
            } elseif($followees_with_links != null && $followers_with_links != null) {
                $type = rand(0,1);
                if($type == 0) {
                    $is_followee = true;
                    $this->makeInsight($followers_with_links, 'new_friend_links', $instance,$is_followee);
                } else {
                    $this->makeInsight($followees_with_links, 'new_friend_links', $instance,$is_followee);
                }
            }
        }
    }

    /**
    * Determine if new friends have links in their profiles.
    * @param arr $users
    * @return arr Array of users with links.
    */
    private function getUsersWithUrl($users) {
        $users_with_links = null;
        foreach($users as $user) {
            if($user->url == "") {
                continue;
            } else {
                $users_with_links[] = $user;
            }
        }
        if($users_with_links == null) {
            return null;
        } else {
            return $users_with_links;
        }
    }
    /**
    * Generate Insights.
    * @param arr $users
    * @param str $slug
    * @param instance $instance
    * @param str $type_of_friend
    */
    private function makeInsight($users, $slug, $instance,$is_followee) {
        $terms = new InsightTerms($instance->network);
        $insight = new Insight();
        $insight->slug = $slug;
        $insight->instance_id = $instance->id;
        $insight->date = $this->insight_date;
        var_dump("Before If");
        if(count($users) == 1) {
            $at = $instance->network == 'twitter' ? '@' : '';
            $user = $users[0]->username;
            $insight->headline = "Did you see $at$user's website?";
            $insight->text = "This link was in $at$user's bio.";
            $insight->header_image = $users[0]->avatar;
        } elseif($is_followee == true) {
            $add = $instance->network == 'twitter' ? 'followed' : 'befriended';
            $insight->text = "The people $this->username $add this week have these links in their bios.";
            $insight->headline = $this->getVariableCopy(array(
                "Check out the websites for the people $this->username $add this week.",
                "Find out more about the people $this->username $add this week."
                ), array('network' => ucfirst($instance->network)));
        } else {
            var_dump("follower");
            $terms = new InsightTerms($instance->network);
            $friend = $terms->getNoun('follower', InsightTerms::PLURAL);
            $insight->text = "$this->username's followers have these links in their bios.";
            $insight->headline = $this->getVariableCopy(array(
                "Check out the websites for $this->username's new $friend.",
                "Find out more about $this->username's new $friend."
                ), array('network' => ucfirst($instance->network)));
        }
        $insight->setPeople($users);
        $insight->filename = basename(__FILE__, ".php");
        $this->insight_dao->insertInsight($insight);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('NewFriendLinksInsight');
