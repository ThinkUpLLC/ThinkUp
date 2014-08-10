<?php
/*
 Plugin Name: Most Followed Follwees.
 Description: Shows user who is the most popular within their follows.
 When: 20th of the July.
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/mostfollowedfollowees.php
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

class MostFollowedFolloweesInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
        if ($instance->network == 'twitter' || 'instagram') {
            $firstrun = !$this->insight_dao->doesInsightExist('most_followed_followees', $instance->id);
            $yearly_date = date('m-d', strtotime('20 July'));
            
            if ($firstrun == true || date("m-d") == $yearly_date || Utils::isTest()) {
                $user_dao = new UserMySQLDAO();
                $user_details = $user_dao->getDetails($instance->network_user_id, $instance->network);
                if($user_details->friend_count > 10) {
                    $follow_DAO = new FollowMySQLDAO();
                    $followees = $follow_DAO->getFolloweesOrderedByFollowCount($instance->network_user_id,
                    $instance->network, true,true);
                    $terms = new InsightTerms($instance->network);
                    $post_string = $insight->text . $terms->getNoun("post",InsightTerms::PLURAL);
                    $user_text = ' ';
                    $instance->network == 'twitter' ? $at_string = "@":"";
                    $user_included = false;
                    //Loop to remove user from array.
                    foreach($followees as $followee => $details) {
                        if($details->username == $instance->network_username) {
                            unset($followees[$followee]);
                            $user_included = true;
                        }
                    }
                    foreach($followees as $followee => $details) {
                        $count++;
                        if($count == 4) {
                            $user_text .= $at_string . $details->username . ' and ';
                        } else {
                            $user_text .= $at_string . $details->username . ', ';
                        }
                    }
                    $user_text =substr($user_text, 0, -2); //removes final ', '

                    $insight = new Insight();
                    $insight->slug = 'most_followed_followees';
                    $insight->instance_id = $instance->id;
                    $insight->date = $this->insight_date;
                    $service_name = $instance->network == 'twitter' || 'instagram' ? ucfirst($instance->network)
                    :$instance->network;
                    $insight->headline ="The five most popular $service_name accounts $this->username follows.";
                    if($user_included == true) {
                        $insight->text = "Whoa! The five most popular people in $this->username's stream includes... ";
                        $insight->text .="$this->username! Beyond that, $user_text have more";
                        $insight->text .=" followers than anyone else $this->username follows.";
                    } else {
                        $insight->text = $this->getVariableCopy(array(
                        "Looks like %username isn't the only one impressed with $user_text's $post_string.",
                        "$user_text have more followers than anyone else %username follows."));
                    }
                    $insight->setPeople($followees);
                    $insight->filename = basename(__FILE__, ".php");
                    $this->insight_dao->insertInsight($insight);
                }
            }
            $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
        }
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('MostFollowedFolloweesInsight');
