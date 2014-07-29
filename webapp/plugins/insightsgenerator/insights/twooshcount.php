<?php
/*
 Plugin Name: Twoosh Count.
 Description: Counts how many tweets are exactly 140 characters.
 When: Weekly on a friday.
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/twooshcount.php
 *
 * Copyright (c) 2014 Gareth Brady
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
 * @copyright 2014 Gareth Brady
 * @author Gareth Brady <gareth.brady92 [at] gmail [dot] com>
 */
class TwooshCountInsight extends CriteriaMatchInsightPluginParent implements InsightPlugin {
    public function shouldGenerate(Instance $instance, $last_week_of_posts) {
        return $this->shouldGenerateWeeklyInsight($this->getSlug(), $instance, $insight_date='today',
        $regenerate_existing_insight=false, 5, count($last_week_of_posts),
        array('youtube','foursquare','instagram','facebook','google+'));
    }

    public function getSlug() {
        return 'twoosh_count_weekly';
    }

    public function getNumberOfDaysNeeded() {
        return 40;
    }

    public function postMatchesCriteria(Post $post, Instance $instance) {
        if (strlen($post->post_text) == 140) {
            return true;
        } else {
            return false;
        }
    }

    public function getInsightForCounts($this_period_count, $last_period_count, $instance, $matching_posts) {
        $insight = null;
        if ($this_period_count > 0) {
            $insight = new Insight();
            $insight->slug = $this->getSlug();
            $insight->instance_id = $instance->id;
            $insight->date = $this->insight_date;
            $some_a_string = $this_period_count > 1 ? 'some' : 'a';
            $twoosh_string = $this_period_count > 1 ? 'twooshes' : 'twoosh';
            $tweet_string = $this_period_count > 1 ? 'tweets' : 'tweet';
            $were_was = $this_period_count > 1 ? 'were' : 'was';
            $are_is = $this_period_count > 1 ? 'are' : 'is';

            $insight->headline = $this->getVariableCopy(array(
                "TWOOSH!",
                "$this->username had $some_a_string $twoosh_string this week.",
                "Looks like there $were_was $some_a_string $twoosh_string this week.",
            ));
            $insight->text ="$this->username posted $this_period_count tweets this week that were";
            $insight->text .=" exactly 140 characters.";
            $insight->setPosts($matching_posts);
            $insight->filename = basename(__FILE__, ".php");
        }
        return $insight;
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('TwooshCountInsight');
