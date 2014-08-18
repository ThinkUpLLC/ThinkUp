<?php
/*
 Plugin Name: Unfollower Analysis 
 Description: People who unfollowed you last week
 When: Thursdays
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/unfollowersanalysis.php
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
 *
 * UnfollowersAnalysis (name of file)
 *
 * Description of what this class does
 *
 * Copyright (c) 2014 Anna Shkerina
 *
 * @author Anna Shkerina blond00792@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Anna Shkerina
 */

class UnfollowersAnalysisInsight extends InsightPluginParent implements InsightPlugin {

public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
		if ($instance->network == 'twitter') {
			parent::generateInsight ( $instance, $last_week_of_posts, $number_days );
			$this->logger->logInfo ( "Begin generating insight", __METHOD__ . ',' . __LINE__ );
			
			$filename = basename ( __FILE__, ".php" );
			
			if (self::shouldGenerateInsight ( 'unfollowers_analysis', $instance, $insight_date='today',
        $regenerate_existing_insight=true, $day_of_week=0)) {
				$follow_dao = DAOFactory::getDAO('FollowDAO');
				$unfollowers = $follow_dao->getUnfollowersFromOneWeekAgo($instance->network_user_id);
				
				if (count($unfollowers) != 0) {
				$this->insight_dao->insertInsightDeprecated ( 'unfollowers_analysis', $instance->id,
					$this->insight_date, "Them left you", "<strong>"
                    .(count($unfollowers) > 1 ? count($unfollowers)." people" : "1 person")
                    ."</strong>  unfollowed " . $this->username . " last week",
						$filename, Insight::EMPHASIS_HIGH, serialize ( $unfollowers ));
				}
				$this->logger->logInfo ( "Done generating insight", __METHOD__ . ',' . __LINE__ );
			}
		}
	}
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('UnfollowersAnalysisInsight');

