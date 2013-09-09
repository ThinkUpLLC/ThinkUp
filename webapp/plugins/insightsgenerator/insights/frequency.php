<?php
/*
 Plugin Name: Frequency
 Description: How frequently you posted this week as compared to last week.
 When: Mondays
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/frequency.php
 *
 * Copyright (c) 2013 Gina Trapani
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
 * @copyright 2013 Gina Trapani
 * @author Gina Trapani <ginatrapani [at] gmail [dot] com>
 */

class FrequencyInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        if (self::shouldGenerateInsight('frequency', $instance, $insight_date='today',
        $regenerate_existing_insight=false, $day_of_week=1)) {
            $count = sizeof($last_week_of_posts);
            if ($count > 1) {
                $text = "$this->username posted <strong>$count times</strong> in the past week";
            } else {
                $prefix = "Nudge, nudge:";
                $text = "$this->username didn't post anything new in the past week";
            }

            $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
            $insight_baseline_dao->insertInsightBaseline("frequency", $instance->id, $count,
            $this->insight_date);

            if ($count > 1) {
                //Week over week comparison
                //Get insight baseline from last Monday
                $last_monday = date('Y-m-d', strtotime('-7 day'));
                $last_monday_insight_baseline = $insight_baseline_dao->getInsightBaseline("frequency",
                $instance->id, $last_monday);
                if (isset($last_monday_insight_baseline) ) {
                    //compare it to this Monday's  number, and add a sentence comparing it.
                    if ($last_monday_insight_baseline->value > ($count + 1) ) {
                        $prefix = "Slowing down:";
                        $difference = $last_monday_insight_baseline->value - $count;
                        $text .= ", $difference fewer times than the prior week.";
                    } elseif ($last_monday_insight_baseline->value < ($count - 1) ) {
                        $prefix = "Ramping up:";
                        $difference = $count - $last_monday_insight_baseline->value;
                        $text .= ", $difference more times than the prior week.";
                    } else {
                        $text .= ".";
                    }
                } else {
                    $text .= ".";
                }
            } else {
                $text .= ".";
            }
            $prefix = (isset($prefix))?$prefix:'Post rate:';
            $this->insight_dao->insertInsight("frequency", $instance->id, $this->insight_date, $prefix,
            $text, basename(__FILE__, ".php"), Insight::EMPHASIS_LOW);
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('FrequencyInsight');
