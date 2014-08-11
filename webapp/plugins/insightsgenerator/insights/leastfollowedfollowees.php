<?php
/*
 Plugin Name: Most Followed Follwers.
 Description: Shows user who is the most popular within their follows.
 When: 25th of July Everty Year.
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/leastfollowedfollowees.php
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

class LeastFollowedFolloweesInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $firstrun = !$this->insight_dao->doesInsightExist($this->slug, $instance->id);
        $yearly_date = date('m-d', strtotime('25 July'));
        
        if ($firstrun == true || date("m-d") == $yearly_date || Utils::isTest()) {
            $user_dao = new UserMySQLDAO();
            $user_details = $user_dao->getDetails($instance->network_user_id, $instance->network);
            if($user_details->friend_count > 10) {
                $follow_DAO = new FollowMySQLDAO();
                $followees = $follow_DAO->getFolloweesOrderedByFollowCount($instance->network_user_id,
                $instance->network, false);
                $terms = new InsightTerms($instance->network);
                $post_string = $insight->text .= $terms->getNoun("post",InsightTerms::PLURAL);
                $user_text = ' ';
                $instance->network == 'twitter' ? $at_string = "@":"";
                $user_included = false;

                foreach($followees as $followee => $details) {
                    $count++;
                    if($count == 4) {
                        $user_text .= $at_string . $details->username . ' and ';
                    } else {
                        $user_text .= $at_string . $details->username . ', ';
                    }
                }
                $user_text =substr($user_text, 0, -2);//removes final ', '

                $insight = new Insight();
                $insight->slug = 'least_followed_followees';
                $insight->instance_id = $instance->id;
                $insight->date = $this->insight_date;
                $service_name = $instance->network == 'twitter' || 'instagram' ? ucfirst($instance->network)
                :$instance->network;
                $insight->headline = $this->getVariableCopy(array(
                "The five least-followed $service_name accounts $this->username follows.",
                "The undiscovered gems followed by $this->username"));
                $insight->text = "$user_text are the five least followed users $this->username follows. ";
                $insight->text .= "$this->username knows something the rest of $service_name doesn't.";
                $insight->setPeople($followees);
                $insight->filename = basename(__FILE__, ".php");
                $this->insight_dao->insertInsight($insight);
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('LeastFollowedFolloweesInsight');
