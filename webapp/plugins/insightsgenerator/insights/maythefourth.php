<?php
/*
 Plugin Name: May The Fourth Be With You
 Description: Did you talk about Star Wars?
 When: May 4, 2015
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/maythefourth.php
 *
 * Copyright (c) 2015 Anil Dash
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
 * Copyright (c) 2014-2015 Chris Moyer, Anil Dash
 *
 * @author Chris Moyer chris@inarow.net, Anil Dash anil@thinkup.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014-2015 Chris Moyer, Anil Dash
 */

class StarWarsInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     **/
    var $slug = 'starwars';
    /**
     * Date to run this insight
     */
    var $run_date = '2015-05-04';

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        if (Utils::isTest() || date("Y-m-d") == $this->run_date) {
            parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
            $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

            $hero_image = array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2015-05/starwars.jpg',
                'alt_text' => 'RockTrooper',
                'credit' => 'Photo: JD Hancock',
                'img_link' => 'https://www.flickr.com/photos/jdhancock/4932301604'
            );

            $should_generate_insight = self::shouldGenerateWeeklyInsight(
                $this->slug,
                $instance,
                $insight_date = $this->run_date,
                $regenerate_existing_insight=false,
                $day_of_week=5,
                count($last_week_of_posts)
            );

            if ($should_generate_insight) {
                $this->logger->logInfo("Should generate", __METHOD__.','.__LINE__);
                $post_dao = DAOFactory::getDAO('PostDAO');

                $topics = array(
                    'force' => array("star wars","force awakens", "bb-8", "darth", "bb8", "episode 7"),
                );

                $matches = array();
                foreach ($last_week_of_posts as $post) {
                    foreach ($topics as $key => $strings) {
                        foreach ($strings as $string) {
                            if (preg_match_all('/\b'.$string.'\b/i', $post->post_text) > 0) {
                                $matches[$key] = array('term'=>$string, 'post'=>$post);
                                unset($topics[$key]);
                                break;
                            }
                        }
                    }
                }

                if (count($matches) > 0) {
                    $headline = "The Force is strong with " . $this->username.".";
                    $insight_text = "May the Fourth Be With You... always. " .
                        $this->username . " was ready for Star Wars day.";

                    $insight = new Insight();
                    $insight->instance_id = $instance->id;
                    $insight->slug = $this->slug;
                    $insight->date = $this->run_date;
                    $insight->headline = $headline;
                    $insight->text = $insight_text;
                    $insight->filename = basename(__FILE__, ".php");
                    $insight->emphasis = Insight::EMPHASIS_HIGH;
                    $insight->setHeroImage($hero_image);
                    $this->insight_dao->insertInsight($insight);

                }

            }
            $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
        }
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('StarWarsInsight');
