<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php
 *
 * Copyright (c) 2012-2013 Gina Trapani
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
 * @copyright 2012-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani [at] gmail [dot] com>
 */
class InsightPluginParent {
    /**
     * @var InsightDAO
     */
    var $insight_dao;
    /**
     * @var Logger
     */
    var $logger;
    /**
     * @var InsightTerms
     */
    var $terms;
    /**
     * Insight date
     * @var str
     */
    var $insight_date;
    /**
     * Username.
     * @var str
     */
    var $username;
    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        $this->logger = Logger::getInstance();
        $this->logger->setUsername($instance->network_username);
        $this->insight_date = date("Y-m-d");
        $this->insight_dao = DAOFactory::getDAO('InsightDAO');
        $this->username = ($instance->network == 'twitter')?'@'.$instance->network_username:$instance->network_username;
        $this->terms = new InsightTerms($instance->network);
    }

    /**
     * Determine whether an insight should be generated.
     * @param str $slug slug of the insight to be generated
     * @param Instance $instance user and network details for which the insight has to be generated
     * @param str $insight_date valid strtotime parameter for insight date, defaults to 'today'
     * @param bool $regenerate_existing_insight whether the insight should be regenerated over a day
     * @param int $count_related_posts if set, wouldn't run insight if there are no posts related to insight
     * @param arr $excluded_networks array of networks for which the insight shouldn't be run
     * @return bool Whether the insight should be generated or not
     */
    public function shouldGenerateInsight($slug, Instance $instance, $insight_date=null,
        $regenerate_existing_insight=false, $count_related_posts=null, $excluded_networks=null) {
        $run = true;

        // Always generate if testing
        if (Utils::isTest()) {
            return true;
        }

        // Check the number of related posts
        if (isset($count_related_posts)) {
            $run = $run && $count_related_posts;
        }

        // Check boolean whether insight should be regenerated over a day
        if (!$regenerate_existing_insight) {
            $insight_date = isset($insight_date) ? $insight_date : 'today';

            $existing_insight = $this->insight_dao->getInsight($slug, $instance->id,
            date('Y-m-d', strtotime($insight_date)));

            if (isset($existing_insight)) {
                $run = $run && false;
            } else {
                $run = $run && true;
            }
        }

        // Check array of networks for which the insight should run
        if (isset($excluded_networks)) {
            if (in_array($instance->network, $excluded_networks)) {
                $run = $run && false;
            } else {
                $run = $run && true;
            }
        }
        return $run;
    }

    /**
     * Determine whether a weekly insight should be generated.
     * @param str $slug slug of the insight to be generated
     * @param Instance $instance user and network details for which the insight has to be generated
     * @param str $insight_date valid strtotime parameter for insight date, defaults to 'today'
     * @param bool $regenerate_existing_insight whether the insight should be regenerated over a day
     * @param int $day_of_week the day of week (0 for Sunday through 6 for Saturday) on which the insight should run
     * @param int $count_last_week_of_posts if set, wouldn't run insight if there are no posts from last week
     * @param arr $excluded_networks array of networks for which the insight shouldn't be run
     * @return bool Whether the insight should be generated or not
     */
    public function shouldGenerateWeeklyInsight($slug, Instance $instance, $insight_date=null,
        $regenerate_existing_insight=false, $day_of_week=null, $count_last_week_of_posts=null,
        $excluded_networks=null) {
        $run = true;

        // Always generate if testing
        if (Utils::isTest()) {
            return true;
        } else {
            $run = self::shouldGenerateInsight( $slug, $instance, $insight_date, $regenerate_existing_insight,
                $count_last_week_of_posts, $excluded_networks);

            // Check the day of the week (0 for Sunday through 6 for Saturday) on which the insight should run
            if (isset($day_of_week)) {
                if (date('w') == $day_of_week) {
                    $run = $run && true;
                } else {
                    $run = $run && false;
                }
            }
        }
        return $run;
    }

    /**
     * Determine whether a monthly insight should be generated.
     * @param str $slug slug of the insight to be generated
     * @param Instance $instance user and network details for which the insight has to be generated
     * @param str $insight_date valid strtotime parameter for insight date, defaults to 'today'
     * @param bool $regenerate_existing_insight whether the insight should be regenerated over a day
     * @param int $day_of_month the day of the month on which the insight should run
     * @return bool Whether the insight should be generated or not
     */
    public function shouldGenerateMonthlyInsight($slug, Instance $instance, $insight_date=null,
        $regenerate_existing_insight=false, $day_of_month=null, $count_related_posts=null, $excluded_networks=null) {
        $run = true;

        // Always generate if testing
        if (Utils::isTest()) {
            return true;
        } else {
            $run = self::shouldGenerateInsight( $slug, $instance, $insight_date, $regenerate_existing_insight,
                $count_related_posts, $excluded_networks);

            // Check the day of the month
            if (isset($day_of_month)) {
                if (date('j') == $day_of_month) {
                    $run = $run && true;
                } else {
                    $run = $run && false;
                }
            }
        }
        return $run;
    }

    public function renderConfiguration($owner) {
    }

    public function renderInstanceConfiguration($owner, $instance_username, $instance_network) {
    }

    public function activate() {
    }

    public function deactivate() {
    }
}