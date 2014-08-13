<?php
/*
 Plugin Name: Follower Map
 Description: Shows user where their new followers are located.
 When: 25th of the month
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/followermap.php
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

class FollowerMapInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     */
    var $slug = 'follower_map_insight'; 

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
    
        // $should_generate_insight = $this->shouldGenerateMonthlyInsight($this->slug, $instance, 'today',
        //     $regenerate=false, 25);
        $should_generate_insight = true;
        if ($should_generate_insight) {
            $follow_dao = DAOFactory::getDAO('FollowDAO');
            $followers_with_location = $follow_dao->getNewFollowersWithLocationWithinLastXDays(
            $instance->network_user_id, $instance->network, date('t'));
            if(count($followers_with_location) != 0) {
                $terms = new InsightTerms($instance->network);
                if(count($followers_with_location) == 1) {
                    $friend_string = $terms->getNoun('friend', InsightTerms::SINGULAR);
                    $are_is = 'is';
                } else {
                    $friend_string = $terms->getNoun('friend', InsightTerms::PLURAL);
                    $are_is = 'are';
                }
                //Builds array to be passed into map.
                foreach ($followers_with_location as $follower) {
                    $resultset[] = array('c' => array(array('v' => $follower['full_name']),
                        array( 'v' =>$follower['user_name']))); 
                }
                $metadata = array(array('type' => 'string', 'label' => 'Country'),
                 array('type' => 'string', 'label' =>'Population'));
                $vis_data = json_encode(array('rows' => $resultset, 'cols' => $metadata));
                $network_string = $instance->network == 'twitter' ? ucfirst($instance->network):$instance->network;

                $my_insight = new Insight();
                //REQUIRED: Set the insight's required attributes
                $my_insight->slug = 'follower_map_insight'; //slug to label this insight's content
                $my_insight->instance_id = $instance->id;
                $my_insight->date = date('Y-m-d'); //date is often this or $simplified_post_date
                $my_insight->headline =$this->getVariableCopy(array(
                "Want to know where $this->username's new $friend_string $are_is from ?",
                "Location, Location, Location",
                "$network_string is a global community."
                ));;
                $my_insight->text = "Here $are_is $this->username's new $friend_string from the last month on a map.";
                $my_insight->setBarChart($vis_data);
                $my_insight->filename = basename(__FILE__, ".php");
                $my_insight->emphasis = Insight::EMPHASIS_MED;
                $this->insight_dao->insertInsight($my_insight);
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('FollowerMapInsight');