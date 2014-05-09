<?php
/*
 Plugin Name: Twitter Age
 Description: How much of Twitter's life have you been a user?
 When: Once (on first crawl)
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
                $first_half_of_twitters_life = floor((TimeHelper::getTime() - $twitter_start)*.5);

                // $start = $twitter_start + $first_half_of_twitters_life;
                // echo "Second half of Twitter's lifetime currently starts on ".gmdate("Y-m-d", $start);
                $explainer = '';

                if ($joined_timestamp < strtotime('March 5, 2007')) {
                    $headline = 'Before Barack Obama joined Twitter...';
                } else if ($joined_timestamp < strtotime('August 23, 2007')) {
                    $headline = 'Before the hashtag, there was '.$this->username.'.';
                    $explainer = 'That\'s before the hashtag was even '.
                        ' <a href="https://twitter.com/chrismessina/status/223115412">'.
                        'invented</a>!';
                } else if ($joined_timestamp < strtotime('March 26, 2008')) {
                    $headline = 'Before Lady Gaga joined Twitter...';
                } else if ($joined_timestamp < strtotime('August 14, 2008')) {
                    $headline = 'Before Ellen DeGeneres joined Twitter...';
                } else if ($joined_timestamp < strtotime('January 16, 2009')) {
                    $headline = 'Before Ashton Kutcher joined Twitter...';
                } else if ($joined_timestamp < strtotime('January 23, 2009')) {
                    $headline = 'Before Oprah Winfrey joined Twitter...';
                } else if ($joined_timestamp < strtotime('February 20, 2009')) {
                    $headline = 'Before Katy Perry joined Twitter...';
                } else if ($joined_timestamp < strtotime('March 28, 2009')) {
                    $headline = 'Before Justin Bieber joined Twitter...';
                } else if ($joined_timestamp < strtotime('July 2, 2009')) {
                    $headline = 'Before Tyra Banks joined Twitter...';
                // At time of dev, 50% of Twitter's life ago was 2010-06-12
                // As time passes, this date will get later
                } else if ($joined_timestamp < ($twitter_start + $first_half_of_twitters_life)) {
                    $headline = $this->getVariableCopy(array(
                        'Somebody is an early bird!',
                        'Achievement unlocked: %username is old-school.'
                    ));
                } else if ($joined_timestamp < strtotime('September 8, 2011')) {
                    $headline = 'One of the first 100 million Twitter users...';
                } else if ($joined_timestamp < strtotime('November 7, 2013')) {
                    $headline = 'Pre-IPO!';
                    $explainer = "That's even before Twitter's initial public offering on November 7, 2013.";
                } else if ($joined_timestamp > strtotime('-6 months')) {
                    $headline = 'Welcome to the party.';
                } else {
                    $headline = 'One in 200 million...';
                }
                $seconds_joined = TimeHelper::getTime() - $joined_timestamp;
                $year_seconds = (365*24*60*60);
                $month_seconds = (30*24*60*60);
                $week_seconds = (7*24*60*60);
                $years = floor($seconds_joined / $year_seconds);
                $months = floor(($seconds_joined - ($year_seconds*$years)) / $month_seconds);
                $weeks = floor(($seconds_joined - ($year_seconds*$years) - ($month_seconds*$months)) / $week_seconds);

                $percentage = sprintf('%d%%', ($seconds_joined / (TimeHelper::getTime()-$twitter_start))*100);
                if ($seconds_joined >= $year_seconds) {
                    $text = $this->username." joined Twitter $years year".($years==1?'':'s');
                    if ($months) {
                        $text .= " and $months month".($months==1?'':'s');
                    }
                    $text .= " ago.";
                    if ($explainer == '') {
                        $explainer = "That's over $percentage of Twitter's lifetime!";
                    }
                } else if ($seconds_joined >= $month_seconds) {
                    $text = $this->username." joined Twitter $months month".($months==1?'':'s');
                    if ($weeks) {
                        $text .= " and $weeks week".($weeks==1?'':'s');
                    }
                    $text .= " ago.";
                    if ($explainer == '') {
                        $explainer = "That's over $percentage of Twitter's lifetime!";
                    }
                } else {
                    $text = $this->username . " joined Twitter ";
                    if ($weeks < 2) {
                        $text .= "this week.";
                    } else {
                        $text .= $weeks." weeks ago.";
                    }
                    $text .= " Take a bow!";
                }
                if ($explainer !== ''){
                    $text .= " " . $explainer;
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
