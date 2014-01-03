<?php
/*
 Plugin Name: Follower Count
 Description: Upcoming follower count milestones (chart).
 When: 1st of the month
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/followercounthistory.php
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

class FollowerCountInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
        $filename = basename(__FILE__, ".php");
        $insight_text = '';

        // Follower count history milestone
        $insight_date = new DateTime();
        $insight_day_of_week = (int) $insight_date->format('w');
        $insight_day_of_month = (int) $insight_date->format('j');

        if ($insight_day_of_month == 1) { //it's the first day of the month
            $count_dao = DAOFactory::getDAO('CountHistoryDAO');
            //by month
            $follower_count_history_by_month = $count_dao->getHistory($instance->network_user_id,
            $instance->network, 'MONTH', 15, $this->insight_date);
            $headline = "<strong>";
            if ( isset($follower_count_history_by_month['milestone'])
            && $follower_count_history_by_month["milestone"]["will_take"] > 0
            && $follower_count_history_by_month["milestone"]["next_milestone"] > 0) {
                $headline .= $follower_count_history_by_month['milestone']['will_take'].' month';
                if ($follower_count_history_by_month['milestone']['will_take'] > 1) {
                    $headline .= 's';
                }
                $headline .= "</strong> till $this->username reaches <strong>".
                number_format($follower_count_history_by_month['milestone']['next_milestone']);
                $headline .= '</strong> followers at the current growth rate.';

                $this->insight_dao->insertInsightDeprecated('follower_count_history_by_month_milestone', $instance->id,
                $this->insight_date, $headline, $insight_text, $filename, Insight::EMPHASIS_LOW,
                serialize($follower_count_history_by_month));
            }
        } else if ($insight_day_of_week == 0) { //it's Sunday
            $count_dao = DAOFactory::getDAO('CountHistoryDAO');
            //by week
            $follower_count_history_by_week = $count_dao->getHistory($instance->network_user_id,
            $instance->network, 'WEEK', 15, $this->insight_date);
            $headline = "<strong>";
            if ( isset($follower_count_history_by_week['milestone'])
            && $follower_count_history_by_week["milestone"]["will_take"] > 0
            && $follower_count_history_by_week["milestone"]["next_milestone"] > 0 ) {
                $headline .= $follower_count_history_by_week['milestone']['will_take'].' week';
                if ($follower_count_history_by_week['milestone']['will_take'] > 1) {
                    $headline .= 's';
                }
                $headline .= "</strong> till $this->username reaches <strong>".
                number_format($follower_count_history_by_week['milestone']['next_milestone']);
                $headline .= '</strong> followers at the current growth rate.';
                $this->logger->logInfo("Storing insight ".$headline, __METHOD__.','
                .__LINE__);

                $this->insight_dao->insertInsightDeprecated('follower_count_history_by_week_milestone', $instance->id,
                $this->insight_date, $headline, $insight_text, $filename, Insight::EMPHASIS_LOW,
                serialize($follower_count_history_by_week));
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('FollowerCountInsight');
