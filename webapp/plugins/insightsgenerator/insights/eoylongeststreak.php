<?php
/*
 Plugin Name: Longest streak (End of Year)
 Description: Your longest posting streak this year
 When: December 17
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/eoylongeststreak.php
 *
 * Copyright (c) 2012-2014 Gina Trapani
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
 * Copyright (c) 2014 Adam Pash
 *
 * @author Adam Pash adam.pash@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Adam Pash
 */

class EOYLongestStreakInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $slug = 'eoy_longest_streak';
        $date = '12-17';
        $year = date('Y');

        $should_generate_insight = self::shouldGenerateEndOfYearAnnualInsight(
            $slug,
            $instance,
            $insight_date = "$year-$date",
            false,
            $day_of_year = $date
        );

        if ($should_generate_insight) {
            $insight = new Insight();
            $insight->instance_id = $instance->id;
            $insight->slug = $slug;
            $insight->date = date('Y-m-d');
            $insight->eoy = true;

            $post_dao = DAOFactory::getDAO('PostDAO');
            $network = $instance->network;

            $post_dao = DAOFactory::getDAO('PostDAO');
            $last_year_of_posts = $post_dao->getThisYearOfPostsIterator(
                $author_id = $instance->network_user_id,
                $network = $network
            );

            $streaks = $this->getStreaks($last_year_of_posts);
            $longest_streak = $this->getLongestStreak($streaks);

            $copy = array(
                'twitter' => array(
                    'normal' => array(
                        'headline' => "%username's longest tweet-streak of %year",
                        'body' => "Sometimes the tweets flow like water and you just " .
                            "don't need a day off. In %year, %username's longest " .
                            "tweeting streak lasted for %total days, from %date1 to %date2."
                    ),
                    'everyday' => array(
                        'headline' => "%username has tweeted every single day in %year!",
                        'body' => "Sometimes the tweets flow like water and you just " .
                            "don't need a day off. So far in %year, %username hasn't taken off " .
                            "a single day, with a streak that has so far lasted for %total " .
                            "days, from %date1 to %date2."
                    ),
                ),
                'facebook' => array(
                    'normal' => array(
                        'headline' => "%username's longest update streak of %year",
                        'body' => "Facebook is great for sharing what we're up to, " .
                            "and sometimes we're up to a lot. In %year, %username " .
                            "posted at least one status update or comment to Facebook " .
                            "for %total days in a row, from %date1 to %date2."
                    ),
                    'everyday' => array(
                        'headline' => "%username has posted to Facebook every single day in %year!",
                        'body' => "Facebook is great for sharing what we're up to, and in %year, " .
                            "%username was up to a lot &mdash; posting at least one time every day " .
                            "so far this year for a streak of %total days, from %date1 through %date2.",
                    ),
                )
            );

            if ($longest_streak['length']-2 == Date('z')-1) {
                $type = 'everyday';
            } else {
                $type = 'normal';
            }
            $rows = array();
            foreach ($longest_streak['counts'] as $label => $number) {
                $date = $this->getDateFromDay($label, 'F j');
                $rows[] = array('c'=>array(array('v'=>$date), array('v' => $number)));
            }

            $post_noun = ucfirst($this->terms->getNoun('post', InsightTerms::PLURAL));
            $insight->setBarChart(array(
                'cols' => array(
                    array('label' => 'Day', 'type' => 'string'),
                    array('label' => $post_noun, 'type' => 'number'),
                ),
                'rows' => $rows
            ));
            $headline = $this->getVariableCopy(
                array(
                    $copy[$network][$type]['headline']
                ),
                array(
                    'year' => $year,
                )
            );

            $insight_text = $this->getVariableCopy(
                array(
                    $copy[$network][$type]['body']
                ),
                array(
                    'year' => $year,
                    'total' => $longest_streak['length'],
                    'date1' => $this->getDateFromDay(
                        $longest_streak['start_day'],
                        'F j'
                    ),
                    'date2' => $this->getDateFromDay(
                        $longest_streak['end_day'],
                        'F j'
                    ),
                )
            );

            $insight->headline = $headline;
            $insight->text = $insight_text;
            $insight->filename = basename(__FILE__, ".php");
            $insight->emphasis = Insight::EMPHASIS_HIGH;

            $this->insight_dao->insertInsight($insight);
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

    public function getStreaks($posts) {
        // return array(0,1);
        $streak_array = array();
        $point_chart = array();
        foreach ($posts as $post) {
            $date = new DateTime($post->pub_date);
            $month_day = $date->format('M-d');
            $day_of_year = $date->format('z');
            $month_day = $day_of_year;
            if (isset($streak_array[$month_day])) {
                $streak_array[$month_day]++;
            } else {
                $streak_array[$month_day] = 1;
            }
            // $point_chart[$month]++;
        }
        return $streak_array;
    }

    public function getLongestStreak($streak_array) {
        $streak_range = array();
        $streak_start = 0;
        foreach ($streak_array as $day => $count) {
            // check if next day has a post
            if (isset($streak_array[$day+1])) {
                // if previous day has a post
                if (isset($streak_array[$day-1])) {
                    // we are in a streak
                    $streak_range[$streak_start] = $day+1;
                } else {
                    // start a new streak
                    $streak_start = $day;
                    $streak_range[$streak_start] = $day+1;
                }
            }
        }

        $longest_streak = array();
        $longest_streak_start_day = 0;
        $length = 0;
        foreach ($streak_range as $start_day => $end_day) {
            if ($end_day - $start_day > $length) {
                $length = $end_day - $start_day + 1;
                $longest_streak_start_day = $start_day;
            }
        }
        $longest_streak['start_day'] = $longest_streak_start_day;
        $longest_streak['end_day'] = $streak_range[$longest_streak_start_day];
        $longest_streak['length'] = $length;
        $longest_streak['counts'] = array_slice($streak_array, $longest_streak['start_day'], $longest_streak['length']);
        return $longest_streak;
    }

    public function getDateFromDay($day_of_year, $format = 'd-m-Y', $year) {
        if (!isset($year)) {
            $year = Date('Y');
        }
        $offset = intval(intval($day_of_year) * 86400);
        $str = date( $format, strtotime('Jan 1, ' . $year) + $offset);
        return( $str );
    }

}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('EOYLongestStreakInsight');
