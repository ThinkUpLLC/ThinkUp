<?php
/*
 Plugin Name: Interesting Followers
 Description: New discerning followers.
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/interestingfollowers.php
 *
 * Copyright (c) 2012 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
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
 * @copyright 2012 Gina Trapani
 * @author Gina Trapani <ginatrapani [at] gmail [dot] com>
 */

class InterestingFollowersInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
        $filename = basename(__FILE__, ".php");
        // Least likely followers insights
        $follow_dao = DAOFactory::getDAO('FollowDAO');
        $least_likely_followers = $follow_dao->getLeastLikelyFollowersByDay($instance->network_user_id,
        $instance->network, 0, 3);

        if (sizeof($least_likely_followers) > 0 ) { //if not null, store insight
            if (sizeof($least_likely_followers) > 1) {
                $this->insight_dao->insertInsight('least_likely_followers', $instance->id, $this->insight_date,
                "Standouts:", '<strong>'.sizeof($least_likely_followers)." interesting users</strong> followed you.",
                $filename, Insight::EMPHASIS_LOW, serialize($least_likely_followers));
            } else {
                $this->insight_dao->insertInsight('least_likely_followers', $instance->id, $this->insight_date,
                "Standout:", "An interesting user followed you.", $filename, Insight::EMPHASIS_LOW,
                serialize($least_likely_followers));
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('InterestingFollowersInsight');
