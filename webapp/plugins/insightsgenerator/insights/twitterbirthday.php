<?php
/*
 Plugin Name: Twitter Birthday
 Description: A happy birthday notice and comparison to the join dates of your friends.
 When: Yearly, on twitter anniversary
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/twitterbirthday.php
 *
 * Copyright (c) 2014 Chris Moyer
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
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer <chris [at] inarow [dot] net>
 */
class TwitterBirthdayInsight extends InsightPluginParent implements InsightPlugin {
    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $joined_ts = strtotime($user->joined, TimeHelper::getTime());
        $joined_day = date('m-d', $joined_ts);
        $is_twitter = $instance->network == 'twitter';
        if ($is_twitter && date('m-d', TimeHelper::getTime()) == $joined_day) {
            $insight = new Insight();
            $insight->slug = "twitterbirthday";
            $insight->instance_id = $instance->id;
            $insight->date = $this->insight_date;
            $insight->filename = basename(__FILE__, ".php");
            $insight->headline = "Happy Twitter birthday!";

            $follow_dao = DAOFactory::getDAO('FollowDAO');
            $all_friends = $follow_dao->countTotalFriends($instance->network_user_id, $instance->network);
            $late_friends = $follow_dao->countTotalFriendsJoinedAfterDate($instance->network_user_id,
                $instance->network, $user->joined);
            $years = date('Y', TimeHelper::getTime()) - date('Y', $joined_ts);

            $insight->text = $this->username." joined Twitter $years year".($years==1?'':'s')." ago today";

            if ($all_friends > 0 && $late_friends > 0) {
                $percent_before = floor($late_friends / $all_friends * 100);
                $insight->text .= ", " . "before ".$percent_before."% of the people ".$this->username." follows did.";
            }
            else {
                $insight->text  .= ".";
            }

            $week_seconds = 60 * 60 * 24 * 7;
            $followers = $follow_dao->getFriendsJoinedInTimeFrame($user->user_id, $instance->network,
                date('Y-m-d', $joined_ts - $week_seconds), date('Y-m-d', $joined_ts + $week_seconds));

            $just_before = null;
            $just_after = null;

            $last_user = null;
            foreach ($followers as $follower) {
                if (strtotime($follower->joined, TimeHelper::getTime()) > $joined_ts) {
                    $just_after = $follower;
                    $just_before = $last_user;
                    break;
                }
                $last_user = $follower;
            }
            if (!$just_after && $last_user) {
                $just_before = $last_user;
            }

            $bonus_text = array();
            $people = array();
            if ($just_before) {
                $time = $this->secondsToRelativeTime(abs($joined_ts - strtotime($just_before->joined, TimeHelper::getTime())));
                $bonus_text[] = "@".$just_before->username." just beat ".$this->username.", joining $time earlier";
                $people[] = $just_before;
            }
            if ($just_after) {
                $time = $this->secondsToRelativeTime(abs($joined_ts - strtotime($just_after->joined, TimeHelper::getTime())));
                $bonus_text[] = "@".$just_after->username." was a little slower, getting on Twitter $time later";
                $people[] = $just_after;
            }

            if (count($bonus_text)) {
                $insight->text .= " ".join(', and ', $bonus_text).".";
                $insight->setPeople($people);
            }

            $res = $this->insight_dao->insertInsight($insight);
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

    public function secondsToRelativeTime($seconds) {
        if ($seconds >= (60*60*24*7)) {
            $weeks = floor($seconds / (60*60*24*7));
            return $weeks." week".($weeks==1?'':'s');
        }
        if ($seconds >= (60*60*24)) {
            $days = floor($seconds / (60*60*24));
            return $days." day".($days==1?'':'s');
        }
        if ($seconds >= (60*60)) {
            $hours = floor($seconds / (60*60));
            return $hours." hour".($hours==1?'':'s');
        }
        if ($seconds >= 60) {
            $minutes = floor($seconds / 60);
            return $minutes." minute".($minutes==1?'':'s');
        }

        return $seconds." second".($seconds==1?'':'s');
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('TwitterBirthdayInsight');
