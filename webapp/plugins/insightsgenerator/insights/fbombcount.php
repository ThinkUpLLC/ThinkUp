<?php
/*
 Plugin Name: F-Bomb Count
 Description: How often you drop an f-bomb.
 When: 4th of the month for Twitter, 6th otherwise
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/fbombcount.php
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
class FBombCountInsight extends CriteriaMatchInsightPluginParent implements InsightPlugin {
    /**
     * @var array Posts that will be included in the insight
     */
    var $posts_to_include = array();

    public function shouldGenerate(Instance $instance, $last_week_of_posts) {
        if ($instance->network == 'twitter') {
            $day_of_month = 4;
        } else {
            $day_of_month = 6;
        }
        return $this->shouldGenerateMonthlyInsight($this->getSlug(), $instance, $insight_date='today',
            $regenerate_existing_insight=false, $day_of_month=$day_of_month, count($last_week_of_posts),
            $excluded_networks=null);
    }

    public function getSlug() {
        return 'fbombcount';
    }

    public function getNumberOfDaysNeeded() {
        return date('t', strtotime('-1 month'));
    }

    public function postMatchesCriteria(Post $post, Instance $instance) {
        $text = strtolower($post->post_text);
        $has_fbomb = preg_match('/fuck/', $text);

        if ($has_fbomb && $post->in_reply_to_post_id) {
            $post_dao = DAOFactory::getDAO('PostDAO');
            $post = $post_dao->getPost($post->in_reply_to_post_id, $instance->network);
            if ($post) {
                $this->posts_to_include[] = $post;
            }
        }
        return $has_fbomb;
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
                'F yeah!',
                'Been dropping those F bombs?',
            );
            if ($instance->network == 'facebook') {
                $potential_headlines[] = 'Facebook Users Curse Knowledgeably';
            }
            $insight->headline = $this->getVariableCopy($potential_headlines);

            $insight->text = $this->username.' said &ldquo;fuck&rdquo; '
                . $this->terms->getOccurrencesAdverb($this_period_count) . ' in the past month.';

            if ($this_period_count != $last_period_count && $last_period_count > 0) {
                $f_diff = $this_period_count-$last_period_count;
                $diff = $f_diff < 0 ? 'fewer' : 'more';
                $insight->text .= ' That\'s '.number_format($f_diff).' '.$diff.' than the prior month. '
                    .'Fucking Awesome.';
            }

            $insight->filename = basename(__FILE__, ".php");
            $insight->emphasis = Insight::EMPHASIS_LOW;
            if (count($this->posts_to_include) > 0) {
                $insight->setPosts($this->posts_to_include);
                if ( count($this->posts_to_include) > 1 )  {
                    //plural
                    $insight->text .= $this->getVariableCopy(array(
                        " Here are the %posts that elicited a \"fuck.\"",
                        " These are the %posts that inspired %username to say \"fuck\"."
                        )
                    );
                } else {
                    //singular
                    $insight->text .= $this->getVariableCopy(array(
                        " Here is the %post that elicited a \"fuck.\"",
                        " This is the %post that inspired %username to say \"fuck\"."
                        )
                    );
                }
            }
        }
        return $insight;
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('FBombCountInsight');
