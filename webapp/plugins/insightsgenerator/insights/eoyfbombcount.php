<?php
/*
 Plugin Name: F-bomb count (End of Year)
 Description: How often you drop the f-bomb this year.
 When: December 6
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/eoyfbombcount.php
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

class EOYFBombCountInsight extends InsightPluginParent implements InsightPlugin {
// class EOYFBombCountInsight extends CriteriaMatchInsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $slug = 'eoy_fbomb_count';
        $date = '12-06';
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
                if ($this->hasFBomb($post)) {
                    $date = new DateTime($post->pub_date);
                    $month = $date->format('M');
                    $point_chart[$month]++;
                    $count++;
                }
                $total_posts++;
            }
            $percent = round($count / $total_posts * 100);

            $max_month = $this->getMaxMonth($point_chart);

            $copy = array(
                'twitter' => array(
                    'normal' => array(
                        'headline' => "%username gave %total fucks on Twitter in %year",
                        'body' => "%username said &ldquo;fuck&rdquo; <strong>%total times</strong> " .
                            "on Twitter this year, with %month eliciting the most fucks. " .
                            "Overall: Great fucking year."
                    ),
                    'one' => array(
                        'headline' => "%username gave 1 fuck on Twitter in %year",
                        'body' => "%username said &ldquo;fuck&rdquo; <strong>once</strong> " .
                            "on Twitter this year, in %month. " .
                            "Overall: Great fucking year."
                    )
                ),
                'facebook' => array(
                    'normal' => array(
                        'headline' => "%username is redefining the &ldquo;f&rdquo; in &ldquo;Facebook&rdquo;",
                        'body' => "%username dropped %total f-bombs on Facebook in %year, " .
                            "with %month on the receiving end of the most fucks. Fuck yeah."
                    ),
                    'one' => array(
                        'headline' => "%username is redefining the &ldquo;f&rdquo; in &ldquo;Facebook&rdquo;",
                        'body' => "%username dropped 1 f-bomb on Facebook in %year, " .
                            "in %month. Fuck yeah."
                    )
                )
            );

            if ($count > 1) {
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
                $type = 'one';
            }
            $headline = $this->getVariableCopy(
                array(
                    $copy[$network][$type]['headline']
                ),
                array(
                    'total' => $count,
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
                    'month' => $max_month
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

    public function getMaxMonth($point_chart) {
        $short_month = array_search(max($point_chart),$point_chart);
        return date('F', strtotime("$short_month 1 2014"));
    }

    public function hasFBomb(Post $post) {
        $text = strtolower($post->post_text);
        $has_fbomb = $post->in_reply_to_user_id != $instance->network_user_id && preg_match('/fuck/', $text);

        return $has_fbomb;
    }

}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('EOYFBombCountInsight');
