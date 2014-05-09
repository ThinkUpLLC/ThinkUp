<?php
/*
 Plugin Name: Twitter Age
 Description: How much of Twitter's life have you been a user?
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/twitterage.php
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
class TwitterAgeInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     **/
    var $slug = 'twitter_age';

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
        if ($instance->network == 'twitter') {
            $firstrun = !$this->insight_dao->doesInsightExist($this->slug, $instance->id);
            if ($firstrun) {
                $joined_timestamp = strtotime($user->joined);
                $twitter_start = strtotime('July 15, 2006');
                $early_period = floor((TimeHelper::getTime() - $twitter_start)*.4);
                if ($joined_timestamp <= strtotime('March 28, 2009')) {
                    $headline = 'Before Justin Bieber joined Twitter...';
                } else if ($joined_timestamp <= ($twitter_start + $early_period)) {
                    $headline = $this->getVariableCopy(array(
                        'Before it was cool...',
                        'Hey, early adopter.'
                    ));
                } else if ($joined_timestamp <= strtotime('November 7, 2013')) {
                    $headline = 'Pre-IPO!';
                } else if ($joined_timestamp > strtotime('-6 months')) {
                    $headline = 'Welcome to the party.';
                } else {
                    $headline = 'One in 200 million.';
                }
                $seconds_joined = TimeHelper::getTime() - $joined_timestamp;
                $year_seconds = (365*24*60*60);
                $month_seconds = (30*24*60*60);
                $week_seconds = (7*24*60*60);
                $years = floor($seconds_joined / $year_seconds);
                $months = floor(($seconds_joined - ($year_seconds*$years)) / $month_seconds);
                $weeks = floor(($seconds_joined - ($year_seconds*$years) - ($month_seconds*$monhts)) / $week_seconds);

                $percentage = sprintf('%d%%', ($seconds_joined / (TimeHelper::getTime()-$twitter_start))*100);
                if ($seconds_joined >= $year_seconds) {
                    $text = $this->username." joined Twitter $years year".($years==1?'':'s');
                    if ($months) {
                        $text .= " and $months month".($months==1?'':'s');
                    }
                    $text .= " ago, over $percentage of Twitter's lifetime.";
                } else if ($seconds_joined >= $month_seconds) {
                    $text = $this->username." joined Twitter $months month".($months==1?'':'s');
                    if ($weeks) {
                        $text .= " and $weeks week".($weeks==1?'':'s');
                    }
                    $text .= " ago, over $percentage of Twitter's lifetime.";
                } else {
                    $text = $this->username . " joined Twitter ";
                    if ($weeks < 2) {
                        $text .= "this week.";
                    } else {
                        $text .= $weeks." weeks ago.";
                    }
                    $text .= " Take a bow!";
                }
                $insight = new Insight();
                $insight->slug = $this->slug;
                $insight->instance_id = $instance->id;
                $insight->date = $this->insight_date;
                $insight->headline = $headline;
                $insight->text = $text;
                $insight->filename = basename(__FILE__, ".php");
                $this->insight_dao->insertInsight($insight);
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('TwitterAgeInsight');
