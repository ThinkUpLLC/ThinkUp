<?php
/*
 * Plugin Name: Geografical Analysis 
 * Description: Location of people who have made your post the most popular today. 
 * When: Saturdays
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/geoanalysisfacebook.php
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
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp. If not, see
 * <http://www.gnu.org/licenses/>.
 *
 *
 * GeoAnalysisFacebook (name of file)
 *
 * Description of what this class does
 *
 * Copyright (c) 2014 Anna Shkerina
 *
 * @author Anna Shkerina blond00792@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Anna Shkerina
 */
class GeoAnalysisFacebookInsight extends InsightPluginParent implements InsightPlugin {
	public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
		parent::generateInsight ( $instance, $last_week_of_posts, $number_days );
		$this->logger->logInfo ( "Begin generating insight", __METHOD__ . ',' . __LINE__ );
		
		$insight_baseline_dao = DAOFactory::getDAO ( 'InsightBaselineDAO' );
		$filename = basename ( __FILE__, ".php" );
		
		if (self::shouldGenerateInsight ( 'geo_analysis_facebook', $instance, $regenerate_existing_insight=true)) {
			//$post_dao = DAOFactory::getDAO ( 'PostDAO' );
			$fpost_dao = DAOFactory::getDAO ( 'FavoritePostDAO' );
			//$posts = $post_dao->getLastWeekPostsByUserId ( $instance->network_user_id, $instance->network );
			$geo_data = array();
			foreach ( $last_week_of_posts as $post ) {
				$locations_fav = $fpost_dao->getLocationOfFavoriters ( $post->post_id );
				$locations_comm = $fpost_dao->getLocationOfCommenters ( $post->post_id );
				//$geo = array_merge ( $locations_comm, $locations_fav );
				$geo = array_map("unserialize", array_unique(array_map("serialize", array_merge ( $locations_comm,
						 $locations_fav ))));
				foreach ( $geo as $g ) {
					$pos = strpos ( $g ['location'], "," );
					if ($pos == 0) {
						$city = $g ['location'];
					} else {
						$city = substr ( $g ['location'], 0, $pos );
					}
					array_push($geo_data, array ("name" => $g ['name'],"city" => $city));
				}
			}
			$geo_data = array_map("unserialize", array_unique(array_map("serialize", $geo_data)));
									
			$this->insight_dao->insertInsightDeprecated ( 'geo_analysis_facebook', $instance->id,
					$this->insight_date, "All over the world", "<strong>" . number_format ( count ( $geo_data ) )
					. " people</strong> interested in " . $instance->network_username . "'s posts last week",
					$filename, Insight::EMPHASIS_HIGH, serialize ( array ($geo_data ) ) );
			$this->logger->logInfo ( "Done generating insight", __METHOD__ . ',' . __LINE__ );
		}
	}
}
$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin ( 'GeoAnalysisFacebookInsight' );

