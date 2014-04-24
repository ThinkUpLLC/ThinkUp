<?php
/*
 Plugin Name: Follower Count
 Description: Upcoming follower count milestones (chart).
 When: Sundays for Twitter, Wednesdays otherwise, and 1st of the month for Twitter, 2nd otherwise
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

        //Monthly
        $did_monthly = false;
        if ($instance->network == 'twitter') {
            $day_of_month = 1;
        } else {
            $day_of_month = 2;
        }
        $should_generate_insight = $this->shouldGenerateMonthlyInsight(
            $slug = 'follower_count_history_by_month_milestone', $instance, $this->insight_date,
            $regenerate_existing_insight=false, $day_of_month = $day_of_month);

        if ($should_generate_insight) {
            $count_dao = DAOFactory::getDAO('CountHistoryDAO');
            $follower_count_history_by_month = $count_dao->getHistory($instance->network_user_id,
                $instance->network, 'MONTH', 15, $this->insight_date, 'followers', 5);
            if (isset($follower_count_history_by_month['milestone'])
                && $follower_count_history_by_month["milestone"]["will_take"] > 0
                && $follower_count_history_by_month["milestone"]["next_milestone"] > 0) {
                $insight = new Insight();
                if ($follower_count_history_by_month['milestone']['will_take'] == 1) {
                    $insight->headline = 'Nice: Only ';
                } else {
                    $insight->headline = 'Looks like it will be ';
                }
                $insight->headline .= '<strong>'.
                    $follower_count_history_by_month['milestone']['will_take'].' month';
                if ($follower_count_history_by_month['milestone']['will_take'] > 1) {
                    $insight->headline .= 's';
                }
                $insight->headline .= "</strong> till $this->username reaches <strong>".
                    number_format($follower_count_history_by_month['milestone']['next_milestone']);
                $insight->headline .= '</strong> '.$this->terms->getNoun('follower',InsightTerms::PLURAL).'.';
                $insight->slug = 'follower_count_history_by_month_milestone';
                $insight->related_data = $follower_count_history_by_month;
                $insight->instance_id = $instance->id;
                $insight->date = $this->insight_date;
                $insight->filename = basename(__FILE__, ".php");
                $insight->emphasis = Insight::EMPHASIS_LOW;
                if (isset($follower_count_history_by_month["trend"])
                && $follower_count_history_by_month["trend"] !== false) {
                    $insight->text = $this->username." is gaining ".$follower_count_history_by_month["trend"]." ".
                        $this->terms->getNoun( 'follower', InsightTerms::PLURAL) . " a month.";
                } else {
                    //This shouldn't happen
                    $insight->text = '';
                }
                $this->insight_dao->insertInsight($insight);
                $did_monthly = true;
            }
        }

        //Weekly
        if ($instance->network == 'twitter') {
            $day_of_week = 0;
        } else {
            $day_of_week = 3;
        }
        $should_generate_insight = $this->shouldGenerateWeeklyInsight($slug='follower_count_history_by_week_milestone',
            $instance, $insight_date = $this->insight_date, $regenerate_existing_insight=false,
            $day_of_week = $day_of_week, $count_last_week_of_posts=null );

        if (!$did_monthly && $should_generate_insight) {
            $count_dao = DAOFactory::getDAO('CountHistoryDAO');
            $follower_count_history_by_week = $count_dao->getHistory($instance->network_user_id,
                $instance->network, 'WEEK', 15, $this->insight_date, 'followers', 5);
            if (isset($follower_count_history_by_week['milestone'])
                && $follower_count_history_by_week["milestone"]["will_take"] > 0
                && $follower_count_history_by_week["milestone"]["next_milestone"] > 0 ) {
                $insight = new Insight();

                if ($follower_count_history_by_week['milestone']['will_take'] == 1) {
                    $insight->headline = 'Wow! Only ';
                } else {
                    $insight->headline = 'Looks like it will be ';
                }
                $insight->headline .= '<strong>'.
                    $follower_count_history_by_week['milestone']['will_take'].' week';
                if ($follower_count_history_by_week['milestone']['will_take'] > 1) {
                    $insight->headline .= 's';
                }
                $insight->headline .= "</strong> till $this->username reaches <strong>".
                    number_format($follower_count_history_by_week['milestone']['next_milestone']);
                $insight->headline .= '</strong> '.$this->terms->getNoun('follower', InsightTerms::PLURAL) . '.';
                $this->logger->logInfo("Storing insight ".$headline, __METHOD__.','.__LINE__);
                $insight->slug = 'follower_count_history_by_week_milestone';
                $insight->related_data = $follower_count_history_by_week;

                $insight->instance_id = $instance->id;
                $insight->date = $this->insight_date;
                $insight->filename = basename(__FILE__, ".php");
                $insight->emphasis = Insight::EMPHASIS_LOW;
                if (isset($follower_count_history_by_week["trend"])
                && $follower_count_history_by_week["trend"] !== false) {
                    $insight->text = $this->username." is gaining ".$follower_count_history_by_week["trend"]." ".
                        $this->terms->getNoun( 'follower', InsightTerms::PLURAL) . " a week.";
                } else {
                    //This shouldn't happen
                    $insight->text = '';
                }
                $this->insight_dao->insertInsight($insight);
            }
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('FollowerCountInsight');
