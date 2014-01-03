<?php
/*
 Plugin Name: Click Spike
 Description: Link click spikes and high insights for the past 7, 30, and 365 days.
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/clickspike.php
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
class ClickSpikeInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        self::generateInsightBaselines($instance, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $shortlink_dao = DAOFactory::getDAO('ShortLinkDAO');
        $filename = basename(__FILE__, ".php");
        $insight_text = '';

        $simplified_post_date = "";
        foreach ($last_week_of_posts as $post) {
            foreach ($post->links as $link) {
                $click_count = $shortlink_dao->getHighestClickCountByLinkID($link->id);
                if ($click_count > 0 ) {
                    // First get spike/high 7/30/365 day baselines
                    if ($simplified_post_date != date('Y-m-d', strtotime($post->pub_date))) {
                        $simplified_post_date = date('Y-m-d', strtotime($post->pub_date));

                        $average_click_count_7_days =
                        $insight_baseline_dao->getInsightBaseline('avg_click_count_last_7_days', $instance->id,
                        $simplified_post_date);

                        $average_click_count_30_days =
                        $insight_baseline_dao->getInsightBaseline('avg_click_count_last_30_days', $instance->id,
                        $simplified_post_date);

                        $high_click_count_7_days =
                        $insight_baseline_dao->getInsightBaseline('high_click_count_last_7_days', $instance->id,
                        $simplified_post_date);

                        $high_click_count_30_days =
                        $insight_baseline_dao->getInsightBaseline('high_click_count_last_30_days', $instance->id,
                        $simplified_post_date);

                        $high_click_count_365_days =
                        $insight_baseline_dao->getInsightBaseline('high_click_count_last_365_days', $instance->id,
                        $simplified_post_date);
                    }
                    // Compare click counts to baselines and store insights where there's a spike or high
                    if (isset($high_click_count_365_days->value)
                    && $click_count >= $high_click_count_365_days->value) {
                        //TODO: Stop using the cached dashboard data and generate fresh here
                        $click_stats_data = $this->insight_dao->getPreCachedInsightData(
                        'ShortLinkMySQLDAO::getRecentClickStats', $instance->id, $simplified_post_date);

                        if (isset($click_stats_data)) {
                            $insight_text = "That's a new 365-day record!";
                            $this->insight_dao->insertInsightDeprecated('click_high_365_day_'.$link->id, $instance->id,
                            $simplified_post_date, 
                            "Viewers clicked $this->username's link <strong>".
                            number_format($click_count). " times</strong>.", $insight_text, $filename, Insight::EMPHASIS_HIGH,
                            serialize(array($link, $click_stats_data)));

                            $this->insight_dao->deleteInsight('click_high_30_day_'.$link->id, $instance->id,
                            $simplified_post_date);
                            $this->insight_dao->deleteInsight('click_high_7_day_'.$link->id, $instance->id,
                            $simplified_post_date);
                            $this->insight_dao->deleteInsight('click_spike_30_day_'.$link->id, $instance->id,
                            $simplified_post_date);
                            $this->insight_dao->deleteInsight('click_spike_7_day_'.$link->id, $instance->id,
                            $simplified_post_date);
                        }
                    } elseif (isset($high_click_count_30_days->value)
                    && $click_count >= $high_click_count_30_days->value) {
                        //TODO: Stop using the cached dashboard data and generate fresh here
                        $click_stats_data = $this->insight_dao->getPreCachedInsightData(
                        'ShortLinkMySQLDAO::getRecentClickStats', $instance->id, $simplified_post_date);

                        if (isset($click_stats_data)) {
                            $insight_text = "That's a new 30-day record!";
                            $this->insight_dao->insertInsightDeprecated('click_high_30_day_'.$link->id, $instance->id,
                            $simplified_post_date, 
                            "Viewers clicked $this->username's link <strong>".
                            number_format( $click_count ). " times</strong>.", $insight_text, $filename, Insight::EMPHASIS_HIGH,
                            serialize(array($link, $click_stats_data)));

                            $this->insight_dao->deleteInsight('click_high_7_day_'.$link->id, $instance->id,
                            $simplified_post_date);
                            $this->insight_dao->deleteInsight('click_spike_30_day_'.$link->id, $instance->id,
                            $simplified_post_date);
                            $this->insight_dao->deleteInsight('click_spike_7_day_'.$link->id, $instance->id,
                            $simplified_post_date);
                        }
                    } elseif (isset($high_click_count_7_days->value)
                    && $click_count >= $high_click_count_7_days->value) {
                        //TODO: Stop using the cached dashboard data and generate fresh here
                        $click_stats_data = $this->insight_dao->getPreCachedInsightData(
                        'ShortLinkMySQLDAO::getRecentClickStats', $instance->id, $simplified_post_date);

                        if (isset($click_stats_data)) {
                            $insight_text = "That's a new 7-day record.";
                            $this->insight_dao->insertInsightDeprecated('click_high_7_day_'.$link->id, $instance->id,
                            $simplified_post_date, 
                            "Viewers clicked $this->username's link <strong>".
                            number_format($click_count). " times</strong>.", $insight_text, $filename, Insight::EMPHASIS_HIGH,
                            serialize(array($link, $click_stats_data)));

                            $this->insight_dao->deleteInsight('click_high_30_day_'.$link->id, $instance->id,
                            $simplified_post_date);
                            $this->insight_dao->deleteInsight('click_spike_30_day_'.$link->id, $instance->id,
                            $simplified_post_date);
                            $this->insight_dao->deleteInsight('click_spike_7_day_'.$link->id, $instance->id,
                            $simplified_post_date);
                        }
                    } elseif (isset($average_click_count_30_days->value)
                    && $click_count > ($average_click_count_30_days->value*2)) {
                        //TODO: Stop using the cached dashboard data and generate fresh here
                        $click_stats_data = $this->insight_dao->getPreCachedInsightData(
                        'ShortLinkMySQLDAO::getRecentClickStats', $instance->id, $simplified_post_date);

                        if (isset($click_stats_data)) {
                            $multiplier = floor($click_count/$average_click_count_30_days->value);
                            $this->insight_dao->insertInsightDeprecated('click_spike_30_day_'.$link->id, $instance->id,
                            $simplified_post_date, 
                            "Viewers clicked $this->username's link <strong>". number_format($click_count).
                             " times</strong>, more than <strong>".$multiplier. "x</strong> your 30-day average.",
                            $insight_text, $filename, Insight::EMPHASIS_LOW, serialize(array($link, $click_stats_data)));

                            $this->insight_dao->deleteInsight('click_high_30_day_'.$link->id, $instance->id,
                            $simplified_post_date);
                            $this->insight_dao->deleteInsight('click_high_7_day_'.$link->id, $instance->id,
                            $simplified_post_date);
                            $this->insight_dao->deleteInsight('click_spike_7_day_'.$link->id, $instance->id,
                            $simplified_post_date);
                        }
                    } elseif (isset($average_click_count_7_days->value)
                    && $click_count > ($average_click_count_7_days->value*2)) {
                        //TODO: Stop using the cached dashboard data and generate fresh here
                        $click_stats_data = $this->insight_dao->getPreCachedInsightData(
                        'ShortLinkMySQLDAO::getRecentClickStats', $instance->id, $simplified_post_date);

                        if (isset($click_stats_data)) {
                            $multiplier = floor($click_count/$average_click_count_7_days->value);
                            $this->insight_dao->insertInsightDeprecated('click_spike_7_day_'.$link->id, $instance->id,
                            $simplified_post_date, 
                            "Viewers clicked $this->username's link <strong>". number_format($click_count).
                            " times</strong>, more than <strong>" .$multiplier. "x</strong> your 7-day average.",
                            $insight_text, $filename, Insight::EMPHASIS_LOW, serialize(array($link, $click_stats_data)));

                            $this->insight_dao->deleteInsight('click_high_30_day_'.$link->id, $instance->id,
                            $simplified_post_date);
                            $this->insight_dao->deleteInsight('click_high_7_day_'.$link->id, $instance->id,
                            $simplified_post_date);
                            $this->insight_dao->deleteInsight('click_spike_30_day_'.$link->id, $instance->id,
                            $simplified_post_date);
                        }
                    }
                }
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
    /**
     * Calculate and store insight baselines for a specified number of days.
     * @param Instance $instance
     * @param int $number_days Number of days to backfill
     * @return void
     */
    private function generateInsightBaselines($instance, $number_days=3) {
        $shortlink_dao = DAOFactory::getDAO('ShortLinkDAO');
        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');

        $days_ago = 0;
        // Generate baseline post insights for the last $number_days
        while ($days_ago < $number_days) {
            $since_date = date("Y-m-d", strtotime("-".$days_ago." day"));

            if ($shortlink_dao->doesHaveClicksSinceDate($instance, 7, $since_date)) {
                //Save average clicks over past 7 days
                $average_click_count_7_days = null;
                $average_click_count_7_days = $shortlink_dao->getAverageClickCount($instance, 7, $since_date);
                if ($average_click_count_7_days != null ) {
                    $insight_baseline_dao->insertInsightBaseline('avg_click_count_last_7_days', $instance->id,
                    $average_click_count_7_days, $since_date);
                    $this->logger->logSuccess("Averaged $average_click_count_7_days clicks in the 7 days before ".
                    $since_date, __METHOD__.','.__LINE__);
                }

                //Save click high for last 7 days
                $high_click_count_7_days = $shortlink_dao->getHighestClickCount($instance, 7, $since_date);
                if ($high_click_count_7_days != null ) {
                    $insight_baseline_dao->insertInsightBaseline('high_click_count_last_7_days', $instance->id,
                    $high_click_count_7_days, $since_date);
                    $this->logger->logSuccess("High of $high_click_count_7_days clicks in the 7 days before ".
                    $since_date, __METHOD__.','.__LINE__);
                }
            }

            if ($shortlink_dao->doesHaveClicksSinceDate($instance, 30, $since_date)) {
                //Save average clicks over past 30 days
                $average_click_count_30_days = null;
                $average_click_count_30_days = $shortlink_dao->getAverageClickCount($instance, 30, $since_date);
                if ($average_click_count_30_days != null ) {
                    $insight_baseline_dao->insertInsightBaseline('avg_click_count_last_30_days', $instance->id,
                    $average_click_count_30_days, $since_date);
                    $this->logger->logSuccess("Averaged $average_click_count_30_days clicks in the 30 days before ".
                    $since_date, __METHOD__.','.__LINE__);
                }

                //Save click high for last 30 days
                $high_click_count_30_days = $shortlink_dao->getHighestClickCount($instance, 30, $since_date);
                if ($high_click_count_30_days != null ) {
                    $insight_baseline_dao->insertInsightBaseline('high_click_count_last_30_days', $instance->id,
                    $high_click_count_30_days, $since_date);
                    $this->logger->logSuccess("High of $high_click_count_30_days clicks in the 30 days before ".
                    $since_date, __METHOD__.','.__LINE__);
                }
            }

            if ($shortlink_dao->doesHaveClicksSinceDate($instance, 365, $since_date)) {
                //Save click high for last 365 days
                $high_click_count_365_days = $shortlink_dao->getHighestClickCount($instance, 365, $since_date);
                if ($high_click_count_365_days != null ) {
                    $insight_baseline_dao->insertInsightBaseline('high_click_count_last_365_days', $instance->id,
                    $high_click_count_365_days, $since_date);
                    $this->logger->logSuccess("High of $high_click_count_365_days clicks in the 365 days before ".
                    $since_date, __METHOD__.','.__LINE__);
                }
            }
            $days_ago++;
        }
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('ClickSpikeInsight');
