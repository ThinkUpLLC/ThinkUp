<?php
/*
 Plugin Name: Exclamation count (End of Year)
 Description: How often you used exclamation points this year!!!!!
 When: December 5
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/eoyexclamationcount.php
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

class EOYExclamationCountInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $slug = 'eoy_exclamation_count';
        $date = '12-05';
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

            $count = 0;
            $post_dao = DAOFactory::getDAO('PostDAO');
            $network = $instance->network;

            /**
             * Track occurences of exclamations per month
             */
            $point_chart = array();

            $post_dao = DAOFactory::getDAO('PostDAO');
            $last_year_of_posts = $post_dao->getThisYearOfPostsIterator(
                $author_id = $instance->network_user_id,
                $network = $network
            );
            $total_posts = 0;

            $months = array(
                'Jan',
                'Feb',
                'Mar',
                'Apr',
                'May',
                'Jun',
                'Jul',
                'Aug',
                'Sep',
                'Oct',
                'Nov',
                'Dec'
            );
            foreach ($months as $month) {
                $point_chart[$month] = 0;
            }
            foreach ($last_year_of_posts as $post) {
                if ($this->hasExclamationPoint($post->post_text)) {
                    $date = new DateTime($post->pub_date);
                    $month = $date->format('M');
                    $point_chart[$month]++;
                    $count++;
                }
                $total_posts++;
            }
            $percent = round($count / $total_posts * 100);

            $copy = array(
                'twitter' => array(
                    'normal' => array(
                        'headline' => "The !!!'s of Twitter, %year",
                        'body' => "OMG! In %year, %username used exclamation points " .
                            "in <strong>%total tweets</strong>. That's %percent% " .
                            "of @username's total tweets this year!"
                    ),
                    'none' => array(
                        'headline' => "%username is unimpressed with %year",
                        'body' => "In %year, %username didn't use one exclamation " .
                            "point on Twitter. Must be holding out for something " .
                            "really exciting!"
                    )
                ),
                'facebook' => array(
                    'normal' => array(
                        'headline' => "%username's emphatic %year on Facebook!",
                        'body' => "Enthusiasm is contagious, and in %year, %username " .
                            "spread the excitement in a total of <strong>%total posts" .
                            "</strong> containing exclamation points. " .
                            "That's %percent% of %username's total Facebook posts " .
                            "this year!"
                    ),
                    'none' => array(
                        'headline' => "%username is unimpressed with %year",
                        'body' => "In %year, %username didn't use one exclamation " .
                            "point on Facebook. Must be holding out for something " .
                            "really exciting!"
                    )
                )
            );

            if ($count > 0) {
                $type = 'normal';
                $rows = array();
                foreach ($point_chart as $label => $number) {
                    $rows[] = array('c'=>array(array('v'=>$label), array('v' => $number)));
                }

                $insight->setBarChart(array(
                    'cols' => array(
                        array('label' => 'Month', 'type' => 'string'),
                        array('label' => 'Occurences', 'type' => 'number'),
                    ),
                    'rows' => $rows
                ));
            } else {
                $type = 'none';
            }
            $headline = $this->getVariableCopy(
                array(
                    $copy[$network][$type]['headline']
                ),
                array(
                    'year' => $year
                )
            );

            $insight_text = $this->getVariableCopy(
                array(
                    $copy[$network][$type]['body']
                ),
                array(
                    'year' => $year,
                    'total' => $count,
                    'percent' => $percent
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

    public function hasExclamationPoint($post_text) {
        $does_match = preg_match_all('/!+/', $post_text);
        return $does_match;
    }

}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('EOYExclamationCountInsight');
