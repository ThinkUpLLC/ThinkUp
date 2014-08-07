<?php
/*
 Plugin Name: Potential Friends
 Description: Encourages user to follow/become friends with a user they replied to but don't follow.
 When: Every Wednesday.
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/potentialfriend.php
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

class PotentialFriendInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
    
        $should_generate_insight = $this->shouldGenerateWeeklyInsight('potential_friend_insight', $instance, 'today',
            $regenerate=false, 4);
        if ($should_generate_insight) {
            $follow_dao = DAOFactory::getDAO('FollowDAO');
            $user_dao = DAOFactory::getDAO('UserDAO');
            $non_follow = $follow_dao->getMostRepliedToNonFollowersId($instance->network_user_id, $instance->network);
            if($non_follow != NULL) {
                $most_replied_non_follow[] = $user_dao->getDetails($non_follow[0]["in_reply_to_user_id"],
                $instance->network);

                $insight = new Insight();
                $insight->slug = 'potential_friend_insight';
                $insight->instance_id = $instance->id;
                $insight->date = $this->insight_date;
                $insight->filename = basename(__FILE__, ".php");
                $number_of_replies = $non_follow[0]["Cnt"];
                $potential_friend = $most_replied_non_follow[0]->username;
                $time_term = $number_of_replies == 1 ? 'time' : 'times';
                $at_term = $instance->network == 'twitter' ? ' @': ' ';
                $network_string = $instance->network == 'twitter' ? ucfirst($instance->network):$instance->network;
                $insight->headline = $this->getVariableCopy(array(
                        "$this->username has been chatting to new people this week!",
                        "Looks like $this->username has been talking to someone new this week.",
                        "Want to keep up to date with $this->username's new acquaintance ?"));
                $insight->text = "$this->username replied to$at_term$potential_friend ";
                $insight->text .= "<b>$number_of_replies $time_term</b> this week, but doesn't follow them on";
                $insight->text .= " $network_string. <br>Do you want to follow his or her updates, or maybe you don't";
                $insight->text .= " follow them on purpose?";
                $insight->setPeople($most_replied_non_follow);
                $this->insight_dao->insertInsight($insight);
            }  
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('PotentialFriendInsight');
