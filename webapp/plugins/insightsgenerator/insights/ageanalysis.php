<?php
/*
 * Plugin Name: Age Analysis Description: Age of people who have made your post the most popular today.
 */
/**
 *
 *
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/ageanalysis.php
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
 * AgeAnalysis (name of file)
 *
 * Description of what this class does
 *
 * Copyright (c) 2014 Anna Shkerina
 *
 * @author Anna Shkerina blond00792@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Anna Shkerina
 */
class AgeAnalysisInsight extends InsightPluginParent implements InsightPlugin {
	public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
		parent::generateInsight ( $instance, $last_week_of_posts, $number_days );
		$this->logger->logInfo ( "Begin generating insight", __METHOD__ . ',' . __LINE__ );
		
		$insight_baseline_dao = DAOFactory::getDAO ( 'InsightBaselineDAO' );
		$filename = basename ( __FILE__, ".php" );
		
		if (self::shouldGenerateInsight ( 'gender_analysis', $instance, $regenerate_existing_insight = true )) {
			$post_dao = DAOFactory::getDAO ( 'PostDAO' );
			$fpost_dao = DAOFactory::getDAO ( 'FavoritePostDAO' );
			$posts = $post_dao->getMostFavCommentPostsByUserId ( $instance->network_user_id, $instance->network );
			foreach ( $posts as $post ) {
				$birthdays_fav = $fpost_dao->getBirthdayOfFavoriters ( $post->post_id );
				$birthdays_comm = $fpost_dao->getBirthdayOfCommenters ( $post->post_id );
				$age_data = array (
						'18' => 0,
						'18_25' => 0,
						'25_35' => 0,
						'35_45' => 0,
						'45' => 0 
				);
				
				foreach ( $birthdays_comm as $birthday_comm ) {
					$birthday = strtotime ( $birthday_comm );
					if ($birthday === false) {
						return false;
					}
					
					$age = date ( 'Y' ) - date ( 'Y', $birthday );
					if (date ( 'md' ) < date ( 'md', $birthday )) {
						$age --;
					}
					
					if ($age > 0 & $age < 18) {
						$age_data ['18'] ++;
					} elseif ($age >= 18 & $age < 25) {
						$age_data ['18_25'] ++;
					} elseif ($age >= 25 & $age < 35) {
						$age_data ['25_35'] ++;
					} elseif ($age >= 35 & $age < 45) {
						$age_data ['35_45'] ++;
					} elseif ($age >= 45) {
						$age_data ['45'] ++;
					}
				}
				
				foreach ( $birthdays_fav as $birthday_fav ) {
					$birthday = strtotime ( $birthday_fav );
					if ($birthday === false) {
						return false;
					}
					$age = date ( 'Y' ) - date ( 'Y', $birthday );
					if (date ( 'md' ) < date ( 'md', $birthday )) {
						$age --;
					}
					
					if ($age > 0 & $age < 18) {
						$age_data ['18'] ++;
					} elseif ($age >= 18 & $age < 25) {
						$age_data ['18_25'] ++;
					} elseif ($age >= 25 & $age < 35) {
						$age_data ['25_35'] ++;
					} elseif ($age >= 35 & $age < 45) {
						$age_data ['35_45'] ++;
					} elseif ($age >= 45) {
						$age_data ['45'] ++;
					}
				}
				
				$simplified_post_date = date ( 'Y-m-d', strtotime ( $post->pub_date ) );
				
				if (max ( $age_data ) == $age_data ['18']) {
					$this->insight_dao->insertInsightDeprecated ( 'age_analysis', $instance->id, $simplified_post_date,
							"Teens!", "<strong>" . number_format ( $age_data ['18'] ) . 
							" people</strong> at the age less than 18 years interested in " . 
							$instance->network_username . "'s post", $filename, Insight::EMPHASIS_HIGH, 
							serialize ( array ( $post, $age_data ) ) );
				} elseif (max ( $age_data ) == $age_data ['18_25']) {
					$this->insight_dao->insertInsightDeprecated ( 'age_analysis', $instance->id, $simplified_post_date,
							"So young!", "<strong>" . number_format ( $age_data ['18_25'] ) . 
							" people</strong> aged 18 to 25 years interested in " . $instance->network_username . 
							"'s post", $filename, Insight::EMPHASIS_HIGH, serialize ( array ( $post, $age_data ) ) );
				} elseif (max ( $age_data ) == $age_data ['25_35']) {
					$this->insight_dao->insertInsightDeprecated ( 'age_analysis', $instance->id, $simplified_post_date,
							"Oh, adults!", "<strong>" . number_format ( $age_data ['25_35'] ) . 
							" people</strong> aged 25 to 35 years interested in " . $instance->network_username . 
							"'s post", $filename, Insight::EMPHASIS_HIGH, serialize ( array ( $post, $age_data ) ) );
				} elseif (max ( $age_data ) == $age_data ['35_45']) {
					$this->insight_dao->insertInsightDeprecated ( 'age_analysis', $instance->id, $simplified_post_date,
							"Middle-aged!", "<strong>" . number_format ( $age_data ['35_45'] ) . 
							" people</strong>aged 35 to 45 years interested in " . $instance->network_username .
							"'s post", $filename, Insight::EMPHASIS_HIGH, serialize ( array ( $post, $age_data ) ) );
				} elseif (max ( $age_data ) == $age_data ['45']) {
					$this->insight_dao->insertInsightDeprecated ( 'age_analysis', $instance->id, $simplified_post_date,
							"Seniors!", "<strong>" . number_format ( $age_data ['45'] ) .
							" people</strong> at the age more than 45 years interested in " . 
							$instance->network_username . "'s post", $filename, Insight::EMPHASIS_HIGH, 
							serialize ( array ( $post, $age_data ) ) );
				}
			}
			$this->logger->logInfo ( "Done generating insight", __METHOD__ . ',' . __LINE__ );
		}
	}
}
$insights_plugin_registrar = PluginRegistrarInsights::getInstance ();
$insights_plugin_registrar->registerInsightPlugin ( 'AgeAnalysisInsight' );

