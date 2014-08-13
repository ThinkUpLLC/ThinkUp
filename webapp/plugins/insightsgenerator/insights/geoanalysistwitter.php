<?php
/*
 * Plugin Name: Geografical Analysis 
 * Description: Most favourite places to make tweets 
 * When: Wednesdays
 */
/**
 *
 *
 *
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/geoanalysistwitter.php
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
 * GeoAnalysisTwitter (name of file)
 *
 * Description of what this class does
 *
 * Copyright (c) 2014 Anna Shkerina
 *
 * @author Anna Shkerina blond00792@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Anna Shkerina
 */
class GeoAnalysisTwitterInsight extends InsightPluginParent implements InsightPlugin {
	public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
		if ($instance->network == 'twitter') {
			parent::generateInsight ( $instance, $last_week_of_posts, $number_days );
			$this->logger->logInfo ( "Begin generating insight", __METHOD__ . ',' . __LINE__ );
			
			$filename = basename ( __FILE__, ".php" );
			
			if (self::shouldGenerateInsight ( 'geo_analysis_twitter', $instance, $insight_date = 'today', 
					$regenerate_existing_insight = true, $day_of_week = 4, count ( $last_week_of_posts ) )) {
				$fpost_dao = DAOFactory::getDAO ( 'FavoritePostDAO' );		
				$geo_data = array ();
				$geos = $fpost_dao->getGeoOfPostsFromOneWeekAgo ( $instance->network_user_id );
				
				foreach ( $geos as $geo ) {
					$geo_str = trim ( $geo ['geo'] );
					$pos = strpos ( $geo_str, "," );
					$lat = substr ( $geo_str, 0, $pos - 3 );
					$long = substr ( $geo_str, $pos + 1, strlen ( $geo_str ) - $pos - 4 );
					array_push ( $geo_data, array (
							"lat" => $lat,
							"long" => $long,
							"place" => $geo ['place'] 
					)
					 );
				}
				
				$geo_data = array_map ( "unserialize", array_unique ( array_map ( "serialize", $geo_data ) ) );
				
				$this->insight_dao->insertInsightDeprecated ( 'geo_analysis_twitter', $instance->id, 
						$this->insight_date, "Here am I!", "<strong>" . number_format ( count ( $geo_data ) ) . 
						" places</strong> were used by " . $this->username . " to make tweets last week", 
						$filename, Insight::EMPHASIS_HIGH, serialize ( array ($geo_data ) ) );
				
				$this->logger->logInfo ( "Done generating insight", __METHOD__ . ',' . __LINE__ );
			}
		}
	}
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance ();
$insights_plugin_registrar->registerInsightPlugin ( 'GeoAnalysisTwitterInsight' );

