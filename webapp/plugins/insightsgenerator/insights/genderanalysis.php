<?php
/*
 * Plugin Name: Gender Analysis
 * Description: Gender of people who have made your post the most popular today.
 */
/**
 *
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/genderanalysis.php
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
 * GenderAnalysis (name of file)
 *
 * Description of what this class does
 *
 * Copyright (c) 2014 Anna Shkerina
 *
 * @author Anna Shkerina blond00792@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Anna Shkerina
 */
class GenderAnalysisInsight extends InsightPluginParent implements InsightPlugin {
	public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
		parent::generateInsight ( $instance, $last_week_of_posts, $number_days );
		$this->logger->logInfo ( "Begin generating insight", __METHOD__ . ',' . __LINE__ );
		
		$insight_baseline_dao = DAOFactory::getDAO ( 'InsightBaselineDAO' );
		$filename = basename ( __FILE__, ".php" );
		
 		$post_dao = DAOFactory::getDAO ( 'PostDAO' );
 		$fpost_dao = DAOFactory::getDAO ( 'FavoritePostDAO' );
 		$posts = $post_dao->getMostFavCommentPostsByUserId ( $instance->network_user_id, $instance->network );
 		foreach ( $posts as $post ) {				
 				$gender_fav = $fpost_dao->getGenderOfFavoriters ( $post->post_id );
				$gender_comm = $fpost_dao->getGenderOfCommenters ( $post->post_id );

 				$female = $gender_fav ['female_likes_count'] + $gender_comm ['female_comm_count'];
 				$male = $gender_fav ['male_likes_count'] + $gender_comm ['male_comm_count'];

 				$gender_data = array (
 				'gender' => 'value',
				'female' => $female,
				'male' => $male 
		);
 				$simplified_post_date = date('Y-m-d', strtotime($post->pub_date));
 				echo "time= ".$simplified_post_date;
			
				if ($female > $male) {
					$this->insight_dao->insertInsightDeprecated ( 'gender_analysis' . $post->post_id, $instance->id, 
							$simplified_post_date, "Women favorite!", "<strong>" . number_format ( $female ) . 
							" times women</strong> interested in ". $instance->network_username . "'s post", $filename, 
							Insight::EMPHASIS_HIGH, serialize ( array ($post, $gender_data) ) );
				 } elseif ($male > $female) {
					$this->insight_dao->insertInsightDeprecated ( 'Gender Analysis' . $post->post_id, $instance->id, 
							$simplified_post_date, "Men favorite!", "<strong>" . number_format ( $male ) . 
							" times men</strong> interested in  " . $instance->network_username . "'s post", $filename, 
							Insight::EMPHASIS_HIGH, serialize ( array ($post, $gender_data) ) );
				} else {
					$this->insight_dao->insertInsightDeprecated ( 'Gender Analysis' . $post->post_id, $instance->id, 
							$simplified_post_date, "Loved by all!", "<strong>" . number_format ( $female+$male). 
							" times women and men </strong> interested in  " 
							. $instance->network_username . "'s post", $filename, 
							Insight::EMPHASIS_HIGH, serialize (array ($post, $gender_data) ) );
				} 
			}
			
			$this->logger->logInfo ( "Done generating insight", __METHOD__ . ',' . __LINE__ );
			echo "Gender done\n";
	}
}
$insights_plugin_registrar = PluginRegistrarInsights::getInstance ();
$insights_plugin_registrar->registerInsightPlugin ( 'GenderAnalysisInsight' );

		

