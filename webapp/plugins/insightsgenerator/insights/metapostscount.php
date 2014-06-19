<?php
/*
 Plugin Name: Meta-Posts Count
 Description: How often you post about the service you are using
 When: Weekly on Thursday
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/metapostcount.php
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
class MetaPostsCountInsight extends CriteriaMatchInsightPluginParent implements InsightPlugin {
    /**
     * @var int Number of posts examined
     */
    var $total_posts = 0;

    public function shouldGenerate(Instance $instance, $last_week_of_posts) {
        if (!in_array($instance->network, array('twitter','facebook'))) {
            return false;
        }

        return $this->shouldGenerateWeeklyInsight($this->getSlug(), $instance, $insight_date='today',
            $regenerate_existing_insight=false, $day_of_week=4, count($last_week_of_posts),
            $excluded_networks=null);
    }

    public function getSlug() {
        return 'metapostscount';
    }

    public function getNumberOfDaysNeeded() {
        return 7;
    }

    public function postMatchesCriteria(Post $post, Instance $instance) {
        $this->total_posts++;
        $text = strtolower($post->post_text);
        if ($instance->network == 'facebook') {
            $pattern = '/\b(facebook|newsfeed|news feed)/';
        }
        else if ($instance->network == 'twitter') {
            $pattern = '/\b(twitter|tweet[a-z]*)/';
        }

        return preg_match($pattern, $text);
    }

    public function getInsightForCounts($this_period_count, $last_period_count, $instance, $matching_posts) {
        $insight = null;
        if ($this_period_count > 0) {
            $insight = new Insight();
            $insight->slug = $this->getSlug();
            $insight->instance_id = $instance->id;
            $insight->date = $this->insight_date;

            // We track the percent, rather than the raw count for comparison
            $percent = floor($this_period_count/$this->total_posts*100);
            $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
            $baseline_dao->updateInsightBaseline($this->getSlug().'_count', $instance->id, $percent);

            $network = ucfirst($instance->network);
            $potential_headlines = array( "It's $network all the way down",);
            if ($instance->network == 'facebook') {
                $potential_headlines[] = "Feelings for Facebook";
                $potential_headlines[] = "Feeding the news feed";
            }
            else if ($instance->network == 'twitter') {
                $potential_headlines[] = "Tweets on tweets on tweets";
                $potential_headlines[] = "Tweetin' 'bout Twitter";
            }
            $insight->headline = $this->getVariableCopy($potential_headlines);

            $insight->text = $this->getVariableCopy(array(
                "%username used %service to talk about %service %total time".($this_period_count>1?'s':'')." this week. "
                . "That's roughly %percent of %username's %posts for the week"
            ), array(
                'service' => $network,
                'total' => $this_period_count,
                'percent' => sprintf('%d%%', $percent)
            ));

            $diff = $percent-$last_period_count;
            if ($last_period_count > 0 && abs($diff) > 10) {
                $diffword = $diff < 0 ? 'down' : 'up';
                $insight->text .= ", $diffword ".abs($diff)."% from last week.";
            }
            else {
                $insight->text .= '.';
            }

            $insight->filename = basename(__FILE__, ".php");
            $insight->emphasis = Insight::EMPHASIS_LOW;
        }
        return $insight;
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('MetaPostsCountInsight');
