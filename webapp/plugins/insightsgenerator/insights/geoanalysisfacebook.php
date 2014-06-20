<?php
/*
 * Plugin Name: Geografical Analysis Description: Location of people who have made your post the most popular today.
 */
/**
 *
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
		
		$post_dao = DAOFactory::getDAO ( 'PostDAO' );
		$fpost_dao = DAOFactory::getDAO ( 'FavoritePostDAO' );
		$posts = $post_dao->getMostFavCommentPostsByUserId ( $instance->network_user_id, $instance->network );
		foreach ( $posts as $post ) {
			$locations_fav = $fpost_dao->getLocationOfFavoriters ( $post->post_id );
			$locations_comm = $fpost_dao->getLocationOfCommenters ( $post->post_id );
			$geo_data = array ();
			$geo_data[] = $locations_comm;
			$geo_data[] = $locations_fav;
			
			echo "geo=".Utils::varDumpToString($geo_data)."<\n>";
			$simplified_post_date = date ( 'Y-m-d', strtotime ( $post->pub_date ) );
			
			$this->insight_dao->insertInsightDeprecated ( 'geo_analysis_facebook', $instance->id, $simplified_post_date,
					"All over the world", "<strong>" . number_format ( count($geo_data) ) . " people</strong> 
					from different places interested in " . $instance->network_username . "'s post", 
					$filename, Insight::EMPHASIS_HIGH, serialize ( array ( $post, $geo_data ) ) );
		}
		$this->logger->logInfo ( "Done generating insight", __METHOD__ . ',' . __LINE__ );
	}
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance ();
$insights_plugin_registrar->registerInsightPlugin ( 'GeoAnalysisFacebookInsight' );

