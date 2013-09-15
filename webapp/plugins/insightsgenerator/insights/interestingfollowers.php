<?php
/*
 Plugin Name: Interesting followers
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

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
        $filename = basename(__FILE__, ".php");
        $follow_dao = DAOFactory::getDAO('FollowDAO');

        // Least likely followers based on follower-to-followee ratio
        $least_likely_followers = $follow_dao->getLeastLikelyFollowersByDay($instance->network_user_id,
        $instance->network, 0, 3);

        if (sizeof($least_likely_followers) > 0 ) { //if not null, store insight
            if (sizeof($least_likely_followers) > 1) {
                $this->insight_dao->insertInsight('least_likely_followers', $instance->id, $this->insight_date,
                "Standouts:", '<strong>'.sizeof($least_likely_followers)." interesting users</strong> ".
                "followed $this->username.", $filename, Insight::EMPHASIS_LOW, serialize($least_likely_followers));
            } else {
                $this->insight_dao->insertInsight('least_likely_followers', $instance->id, $this->insight_date,
                "Standout:", "An interesting user followed $this->username.", $filename, Insight::EMPHASIS_LOW,
                serialize($least_likely_followers));
            }
        }

        // Verified followers
        $verified_followers = $follow_dao->getVerifiedFollowersByDay($instance->network_user_id, $instance->network, 0,
        3);

        if (sizeof($verified_followers) > 0 ) { //if not null, store insight
            if (sizeof($verified_followers) > 1) {
                $this->insight_dao->insertInsight('verified_followers', $instance->id, $this->insight_date,
                "Verified followers!", '<strong>'.sizeof($verified_followers)." verified users</strong> ".
                "followed $this->username.", $filename, Insight::EMPHASIS_LOW, serialize($verified_followers));
            } else {
                $this->insight_dao->insertInsight('verified_followers', $instance->id, $this->insight_date,
                "Verified follower!", "A verified user followed $this->username.", $filename, Insight::EMPHASIS_LOW,
                serialize($verified_followers));
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('InterestingFollowersInsight');
