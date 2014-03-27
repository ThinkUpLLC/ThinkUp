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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2014 Gina Trapani
 * @author Gina Trapani <ginatrapani [at] gmail [dot] com>
 */

class FollowerCountInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $did_monthly = false;
        if ($this->shouldGenerateMonthlyInsight('follower_count_history_by_month_milestone', $instance)) {
            $count_dao = DAOFactory::getDAO('CountHistoryDAO');
            $follower_count_history_by_month = $count_dao->getHistory($instance->network_user_id,
                $instance->network, 'MONTH', 15, $this->insight_date, 'followers', 5);
            if (isset($follower_count_history_by_month['milestone'])
                && $follower_count_history_by_month["milestone"]["will_take"] > 0
                && $follower_count_history_by_month["milestone"]["next_milestone"] > 0) {
                $insight = new Insight();
                $insight->headline = '<strong>'.$follower_count_history_by_month['milestone']['will_take'].' month';
                if ($follower_count_history_by_month['milestone']['will_take'] > 1) {
                    $insight->headline .= 's';
                }
                $insight->headline .= "</strong> till $this->username reaches <strong>".
                    number_format($follower_count_history_by_month['milestone']['next_milestone']);
                $insight->headline .= '</strong> '.$this->terms->getNoun('follower',InsightTerms::PLURAL)
                    . ' at the current growth rate.';
                $insight->slug = 'follower_count_history_by_month_milestone';
                $insight->related_data = $follower_count_history_by_month;
                $insight->instance_id = $instance->id;
                $insight->date = $this->insight_date;
                $insight->filename = basename(__FILE__, ".php");
                $insight->emphasis = Insight::EMPHASIS_LOW;
                $insight->text = '';
                $this->insight_dao->insertInsight($insight);
                $did_monthly = true;
            }
        }

        if (!$did_monthly
            && $this->shouldGenerateWeeklyInsight('follower_count_history_by_week_milestone', $instance)) {
            $count_dao = DAOFactory::getDAO('CountHistoryDAO');
            $follower_count_history_by_week = $count_dao->getHistory($instance->network_user_id,
                $instance->network, 'WEEK', 15, $this->insight_date, 'followers', 5);
            if (isset($follower_count_history_by_week['milestone'])
                && $follower_count_history_by_week["milestone"]["will_take"] > 0
                && $follower_count_history_by_week["milestone"]["next_milestone"] > 0 ) {
                $insight = new Insight();

                $insight->headline = '<strong>'.$follower_count_history_by_week['milestone']['will_take'].' week';
                if ($follower_count_history_by_week['milestone']['will_take'] > 1) {
                    $insight->headline .= 's';
                }
                $insight->headline .= "</strong> till $this->username reaches <strong>".
                    number_format($follower_count_history_by_week['milestone']['next_milestone']);
                $insight->headline .= '</strong> '.$this->terms->getNoun('follower', InsightTerms::PLURAL)
                   . ' at the current growth rate.';
                $this->logger->logInfo("Storing insight ".$headline, __METHOD__.','.__LINE__);
                $insight->slug = 'follower_count_history_by_week_milestone';
                $insight->related_data = $follower_count_history_by_week;

                $insight->instance_id = $instance->id;
                $insight->date = $this->insight_date;
                $insight->filename = basename(__FILE__, ".php");
                $insight->emphasis = Insight::EMPHASIS_LOW;
                $insight->text = '';
                $this->insight_dao->insertInsight($insight);
            }
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('FollowerCountInsight');
