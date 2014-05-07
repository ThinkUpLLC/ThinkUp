<?php
/*
 Plugin Name: Follow Count Visualizer
 Description: How many people follow you, described as a real-world group.
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/followcountvisualizer.php
 *
 * Copyright (c) 2014 Chris Moyer
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer <chris [at] inarow [dot] net>
 */
class FollowCountVisualizerInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug used for this insight
     */
    private $slug = 'follow_count_visualizer';

    /**
     * Milestones we check against.
     */
    private $milestones = array(
        56 => "That's how many high school students can sit on a yellow school bus.",
        115 => "That's how many people saw the Rolling Stones' first live performance.",
        200 => "That's how many riders fit in a New York City subway car.",
        360 => "That's how many singers are in the Mormon Tabernacle Choir!",
        400 => "That's how many passengers fill up a 747.",
        510 => "That's more people than saw Prince's first solo performance.",
        600 => "That's the population of Eminence, Missouri.",
        12500 => "%username's followers could fill up Wembley Arena.",
        36000 => "That's how many people ran the 2014 Boston Marathon.",
        40700 => "That's the population of Manassas, Virginia.",
        50000 => "%username's followers could fill Yankee Stadium.",
        50000 => "That's enough people to fill the Roman Colosseum.",
        57000 => "More people follow %username than live in Greenland!",
        259000 => "That's the population of Buffalo, New York!",
        312000 => "%username's follower count is the size of Belize's entire population!",
        400000 => "That's how many people went to Woodstock!",
        750000 => "That's the population of Louisville, Kentucky.",
        917092 => "That's more than the population of Delaware.",
    );

    /**
     * Images for associated miletsones
     */
    private $hero_images = array(
        56 => array(
            'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/bus.jpg',
            'alt_text' => 'Yellow school bus',
            'credit' => 'Photo: Credit TBD',
            'img_link' => 'http://example.com/tbd',
        ),
    );

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        if ($this->shouldGenerateInsight($this->slug, $instance, $insight_date='today', $regenerate=false)) {
            $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
            $met_milestone = 0;
            foreach ($this->milestones as $count=>$text) {
                if ($user->follower_count >= $count && $user->follower_count < ($count*2)) {
                    $met_milestone = $count;
                } else if ($user->follower_count < $count) {
                    break;
                }
            }

            if ($met_milestone) {
                $baseline = $baseline_dao->getMostRecentInsightBaseline('follower_vis_last_run', $instance->id);
                if (!isset($baseline->value) || (isset($baseline->value) && $met_milestone > $baseline->value)) {
                    $baseline_dao->insertInsightBaseline('follower_vis_last_run', $instance->id, $met_milestone);
                    $insight = new Insight();
                    $insight->slug = $this->slug;
                    $insight->instance_id = $instance->id;
                    $insight->date = $this->insight_date;
                    $insight->filename = basename(__FILE__, ".php");
                    $insight->emphasis = Insight::EMPHASIS_LOW;

                    if ($user->follower_count == $met_milestone) {
                        $headlines = array(
                            '%username has reached %total %followers!',
                            '%total people are following %username.',
                        );
                    }
                    else {
                        $headlines = array(
                            '%username has passed %total %followers!',
                            'More than %total people are following %username.',
                        );
                    }

                    $insight->headline = $this->getVariableCopy($headlines,
                        array( 'total' => number_format($met_milestone)));
                    $insight->text = str_replace('%username', $this->username, $this->milestones[$met_milestone]);
                    if (isset($this->hero_images[$met_milestone])) {
                        $insight->setHeroImage($this->hero_images[$met_milestone]);
                    }
                    $this->insight_dao->insertInsight($insight);
                }
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('FollowCountVisualizerInsight');
