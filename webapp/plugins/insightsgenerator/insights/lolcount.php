<?php
/*
 Plugin Name: LOL-o-meter
 Description: How often you post that you are LOLing.
 When: 3rd of the month for Twitter, 5th otherwise
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/lolcount.php
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
class LOLCountInsight extends CriteriaMatchInsightPluginParent implements InsightPlugin {
    /**
     * @var array Posts that will be included in the insight
     */
    var $posts_to_include = array();

    public function shouldGenerate(Instance $instance, $last_week_of_posts) {
        if ($instance->network == 'twitter') {
            $day_of_month = 3;
        } else {
            $day_of_month = 5;
        }
        return $this->shouldGenerateMonthlyInsight($this->getSlug(), $instance, $insight_date='today',
            $regenerate_existing_insight=false, $day_of_month=$day_of_month, count($last_week_of_posts),
            $excluded_networks=null);
    }

    public function getSlug() {
        return 'lolcount';
    }

    public function getNumberOfDaysNeeded() {
        return date('t', strtotime('-1 month'));
    }

    public function postMatchesCriteria(Post $post, Instance $instance) {
        $text = strtolower($post->post_text);
        $has_lol = preg_match('/(\W|^)(lol.*|rofl.*|haha[ha]*)(\W|$)/', $text);

        if ($has_lol && $post->in_reply_to_post_id) {
            $post_dao = DAOFactory::getDAO('PostDAO');
            $post = $post_dao->getPost($post->in_reply_to_post_id, $instance->network);
            if ($post) {
                $this->posts_to_include[] = $post;
            }
        }
        return $has_lol;
    }

    public function getInsightForCounts($this_period_count, $last_period_count, $instance, $matching_posts) {
        $insight = null;
        if ($this_period_count > 0) {
            $insight = new Insight();
            $insight->slug = $this->getSlug();
            $insight->instance_id = $instance->id;
            $insight->date = $this->insight_date;

            $network = ucfirst($instance->network);
            $potential_headlines = array(
                'LOLOLOLOL, indeed.',
                'LOL activity detected!',
                'OMG LOL!',
            );

            $insight->text = 'Looks like '.$this->username.' found '.number_format($this_period_count).' thing'
                . ($this_period_count==1?'':'s') . ' LOL-worthy in the last month.';

            if ($this_period_count > $last_period_count && $last_period_count > 0) {
                $potential_headlines[] = $network.' must be getting even funnier!';
                $lol_diff = $this_period_count-$last_period_count;
                $insight->text .= ' That\'s '.number_format($lol_diff).' more laugh'.($lol_diff==1?'':'s').' than the '
                    .'prior month.';
            }

            $insight->headline = $this->getVariableCopy($potential_headlines);
            $insight->filename = basename(__FILE__, ".php");
            $insight->emphasis = Insight::EMPHASIS_LOW;
            if (count($this->posts_to_include) > 0) {
                $insight->setPosts($this->posts_to_include);
            }
        }
        return $insight;
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('LOLCountInsight');
