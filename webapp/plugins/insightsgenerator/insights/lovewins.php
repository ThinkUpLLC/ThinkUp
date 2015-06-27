<?php
/*
 Plugin Name: Love Wins
 Description: Did you celebrate the SCOTUS decision on marriage equality? #LoveWins
 When: June 26-28, 2015
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/lovewins.php
 *
 * Copyright (c) 2015 Gina Trapani
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
 * Copyright (c) 2015 Gina Trapani
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2015 Gina Trapani
 */

class LoveWinsInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     **/
    var $slug = 'lovewins';

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $has_never_run = !$this->insight_dao->doesInsightExist($this->slug, $instance->id);

        if (Utils::isTest() || ($has_never_run
            && (date("Y-m-d") == '2015-06-27' || date("Y-m-d") == '2015-06-28') )
            ) {
            $this->logger->logInfo("Should generate insight", __METHOD__.','.__LINE__);

            $hero_image = array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2015-06/white-house-rainbow.jpg',
                'alt_text' => '1600 Pennsylvania Avenue',
                'credit' => 'Photo: Jason Goldman',
                'img_link' => 'https://twitter.com/Goldman44/status/614599247959322624'
            );

            $topics = array(
                'lovewins' => array("LoveWins","marriage equality", "scotus", "gay marriage", "pride"),
            );

            $matches = array();
            $matched_posts = array();
            $matched = false;

            foreach ($last_week_of_posts as $post) {
                foreach ($topics as $key => $strings) {
                    foreach ($strings as $string) {
                        if (preg_match_all('/\b'.strtolower($string).'\b/i', strtolower($post->post_text),
                            $matches)) {
                            $matched = true;
                            $this->logger->logInfo("FOUND ".$string." in ".$post->post_text,
                                __METHOD__.','.__LINE__);
                        }
                        //DEBUG
                        else {
                            $this->logger->logInfo("Didn't find ".$string." in ".$post->post_text,
                                __METHOD__.','.__LINE__);
                        }
                    }
                }
                if ($matched) {
                    $this->logger->logInfo("Matched post ".$post->post_text, __METHOD__.','.__LINE__);
                    $matched_posts[] = $post;
                }
                $matched = false;
            }

            if (count($matched_posts) > 0) {
                if ($instance->network == 'facebook') {
                    $headline = $this->username ." had enough pride for all 50 states";
                    $insight_text = $this->username
                        .' joined the <a href="https://facebook.com/celebratepride">marriage equality celebration</a> '
                        .'this week!';
                } else {
                    $headline = $this->username." joined the #LoveWins celebration";
                    $insight_text = $this->username
                        .' was all about <a href="https://twitter.com/hashtag/LoveWins">marriage equality</a> '
                        .'this week.';
                }

                $insight = new Insight();
                $insight->instance_id = $instance->id;
                $insight->slug = $this->slug;
                $insight->date = date("Y-m-d");
                $insight->headline = $headline;
                $insight->text = $insight_text;
                $insight->filename = basename(__FILE__, ".php");
                $insight->emphasis = Insight::EMPHASIS_HIGH;
                $insight->setHeroImage($hero_image);

                $matched_posts_sliced = array_slice($matched_posts, 0, 5);
                $insight->setPosts($matched_posts_sliced);
                $this->insight_dao->insertInsight($insight);
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('LoveWinsInsight');
