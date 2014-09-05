<?php
/*
 * Plugin Name: Age Analysis
 * Description: Age of people who have made your post the most popular today.
 * When: Weekly on Thursdays (Facebook)
 */
/**
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
 * AgeAnalysisInsight
 *
 * Copyright (c) 2014 Anna Shkerina
 *
 * @author Anna Shkerina blond00792@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Anna Shkerina
 */
class AgeAnalysisInsight extends InsightPluginParent implements InsightPlugin {
    public function generateInsight(Instance $instance,  User $user, $last_week_of_posts, $number_days) {
        if ($instance->network == 'facebook') {
            parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
            $this->logger->logInfo("Begin generating insight", __METHOD__ . ',' . __LINE__);

            if (self::shouldGenerateWeeklyInsight('age_analysis', $instance, null, false, 4)) {
                $post_dao = DAOFactory::getDAO('PostDAO');
                $fpost_dao = DAOFactory::getDAO('FavoritePostDAO');
                $posts = $post_dao->getMostFavCommentPostsByUserId($instance->network_user_id, $instance->network);
                $total = 0;
                foreach ($posts as $post) {
                    $favoriters = $fpost_dao->getBirthdayOfFavoriters($post->post_id);
                    $commentors = $fpost_dao->getBirthdayOfCommenters($post->post_id);
                    $interactors = array_merge($favoriters, $commentors);
                    $age_data = array ('18' => 0, '18_25' => 0, '25_35' => 0, '35_45' => 0, '45' => 0);
                    $too_old = strtotime('200 years ago');
                    foreach ($interactors as $birthday_sql ) {
                        if (empty($birthday_sql)) {
                            continue;
                        }
                        $birthday_ts = strtotime ($birthday_sql);
                        if ($birthday_ts < $too_old) {
                            continue;
                        }

                        $age = date ('Y') - date ('Y', $birthday_ts);
                        if (date ('md') < date ('md', $birthday_ts)) {
                            $age--;
                        }

                        $total++;
                        if ($age < 18) {
                            $age_data['18']++;
                        } elseif ($age >= 18 & $age < 25) {
                            $age_data['18_25']++;
                        } elseif ($age >= 25 & $age < 35) {
                            $age_data['25_35']++;
                        } elseif ($age >= 35 & $age < 45) {
                            $age_data['35_45']++;
                        } elseif ($age >= 45) {
                            $age_data['45']++;
                        }
                    }

                    $generations = array( '18' => 'Teens', '18_25' => 'Generation Z-ers', '25_35' => 'Millenials',
                        '35_45' => 'Gen X-ers', '45' => 'Baby Boomers' );
                    $ages = array( '18' => 'less than 18', '18_25' => '18-25', '25_35' => '25-35',
                        '35_45' => '35-45', '45' => '45+' );

                    $num_gens = 0;
                    $max_gen = null;
                    foreach ($generations as $g=>$l) {
                        if (max($age_data) == $age_data[$g]) {
                            $max_gen = $g;
                        }
                        if ($age_data[$g] > 0) {
                            $num_gens++;
                        }
                    }

                    if ($num_gens > 0) {
                        $generation = $generations[$max_gen];
                        if ($num_gens == 1) {
                            $headline = $generation . ' said it all';
                        } else {
                            $headline = $this->getVariableCopy(array(
                                '%username resonates with %generation',
                                '%generation have a lot to say',
                            ), array('generation' => $generation));
                        }

                        $text = $this->getVariableCopy(array(
                            "%generation — people %age_range years old — had the most to say in response to "
                                . "%username's posts on Facebook this week.",
                            "%generation — people %age_range years old — wrote %percent of the comments on "
                                . "%username's Facebook posts this week.",
                        ), array(
                            'generation' => $generation,
                            'age_range' => $ages[$max_gen],
                            'percent' => sprintf('%2d', $age_data[$max_gen] / $total * 100)
                        ));

                        $insight = new Insight();
                        $insight->slug = 'age_analysis';
                        $insight->instance_id = $instance->id;
                        $insight->date = $this->insight_date;
                        $insight->filename = basename(__FILE__, ".php");
                        $insight->emphasis = Insight::EMPHASIS_MED;
                        $insight->related_data['age_data'] = $age_data;
                        $insight->headline = $headline;
                        $insight->text = $text;
                        $this->insight_dao->insertInsight($insight);
                    }
                }
                $this->logger->logInfo("Done generating insight", __METHOD__ . ',' . __LINE__);
            }
        }
    }
}
$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('AgeAnalysisInsight');
