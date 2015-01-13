<?php
/*
 Plugin Name: Exclamation Count
 Description: How often you used exclamation points!!!!!
 When: 12th of the month (Twitter), 15th of the month (Facebook)
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/exclamationcount.php
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
class ExclamationCountInsight extends CriteriaMatchInsightPluginParent implements InsightPlugin {

    /**
     * Track occurences of single, multiple, etc, exlamations
     */
    var $point_chart = array();

    /**
     * Total posts examined
     */
    var $total_posts = 0;

    public function shouldGenerate(Instance $instance, $last_week_of_posts) {
        if ($instance->network == 'twitter') {
            $day_of_month = 12;
        } else if ($instance->network == 'facebook') {
            $day_of_month = 16;
        } else if ($instance->network == 'instagram') {
            $day_of_month = 10;
        } else {
            return false;
        }

        return $this->shouldGenerateMonthlyInsight($this->getSlug(), $instance, $insight_date='today',
            $regenerate_existing_insight=false, $day_of_month=$day_of_month, count($last_week_of_posts));
    }

    public function getSlug() {
        return 'exclamationcount';
    }

    public function getNumberOfDaysNeeded() {
        return '30';
    }

    public function postMatchesCriteria(Post $post, Instance $instance) {
        $this->total_posts++;
        $does_match = preg_match_all('/!+/', $post->post_text, $matches);
        if ($does_match) {
            foreach ($matches[0] as $match) {
                $number = strlen($match);
                if (!isset($this->point_chart[$number])) {
                    $this->point_chart[$number] = 0;
                }
                $this->point_chart[$number]++;
            }
        }


        return $does_match;
    }

    public function getInsightForCounts($this_period_count, $last_period_count, $instance, $matching_posts) {
        $insight = null;
        if ($this_period_count > 0) {
                $insight = new Insight();
                $insight->slug = $this->getSlug();
                $insight->instance_id = $instance->id;
                $insight->date = $this->insight_date;
                $insight->filename = basename(__FILE__, ".php");
                $insight->emphasis = Insight::EMPHASIS_MED;
                $insight->headline = $this->getVariableCopy(
                  array(
                    //'The emphasis is %username\'s!!!',
                    '30 days of !!!',
                    'OMG %username is serious!',
                  )
                );
                $showchart = count($this->point_chart) > 2;
                $insight->text = $this->getVariableCopy(array(
                    "%username used exclamation points in %total %post".($this_period_count==1?'':'s').
                    " this past month! ".($showchart ? "" : "That's %percent% of %username's %posts!")
                ), array(
                    'total' => $this_period_count,
                    'percent' => floor($this_period_count / $this->total_posts * 100)
                ));

                if ($showchart) {
                    $insight->text .= " Some things are just one-exclamation-point exciting! "
                        . "Others are really exciting!!!! Here's ".$this->username."'s breakdown.";
                    asort($this->point_chart);
                    $rows = array();
                    foreach ($this->point_chart as $label => $number) {
                        $rows[] = array('c'=>array(array('v'=>str_repeat('!', $label)), array('v' => $number)));
                    }
                    $insight->setBarChart(array(
                        'cols' => array(
                            array('label' => 'Exclamation Points', 'type' => 'string'),
                            array('label' => 'Occurences', 'type' => 'number'),
                        ),
                        'rows' => $rows
                    ));
                }
        }
        return $insight;
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('ExclamationCountInsight');
