<?php
/*
 Plugin Name: Local Followers
 Description: Followers whose location is the same as yours.
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/localfollowers.php
 *
 * Copyright (c) 2013 Nilaksh Das, Gina Trapani
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
 * @copyright 2013 Nilaksh Das, Gina Trapani
 * @author Nilaksh Das <nilakshdas [at] gmail [dot] com>
 */

class LocalFollowersInsight extends InsightPluginParent implements InsightPlugin {
    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $insight_text = '';

        if (self::shouldGenerateInsight('local_followers', $instance, $insight_date='today',
        $regenerate_existing_insight=true)) {
            $user_dao = DAOFactory::getDAO('UserDAO');
            $follow_dao = DAOFactory::getDAO('FollowDAO');

            $user = $user_dao->getDetails($instance->network_user_id, $instance->network);

            if (isset($user->location) && $user->location != "") {
                $followers = $follow_dao->getFollowersFromLocationByDay($instance->network_user_id, $instance->network,
                $user->location, 0);

                if (count($followers)) {
                    $headline = "<strong>"
                    .(count($followers) > 1 ? count($followers)." people" : "1 person")
                    ."</strong> in ".$user->location." ".$this->terms->getPhraseForAddingAsFriend($this->username).".";

                    if (count($followers) == 1) {
                        $header_image = $followers[0]->avatar;
                    } else {
                        $header_image = '';
                    }

                    $my_insight = new Insight();

                    //REQUIRED: Set the insight's required attributes
                    $my_insight->slug = 'local_followers'; //slug to label this insight's content
                    $my_insight->instance_id = $instance->id;
                    $my_insight->date = $this->insight_date; //date is often this or $simplified_post_date
                    $my_insight->headline = $headline; // or just set a string like 'Ohai';
                    $my_insight->text = $insight_text; // or just set a strong like "Greetings humans";
                    $my_insight->header_image = $header_image;
                    $my_insight->filename = basename(__FILE__, ".php"); //Same for every insight, must be set exactly this way
                    $my_insight->emphasis = Insight::EMPHASIS_LOW; //Set emphasis optionally, default is Insight::EMPHASIS_LOW
                    $my_insight->setPeople($followers);

                    $this->insight_dao->insertInsight($my_insight);

                }
            }
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('LocalFollowersInsight');
