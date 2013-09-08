<?php
/*
 Plugin Name: Response Time
 Description: How quickly your posts generate replies, favorites, and reshares every week.
 When: Fridays
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/responsetime.php
 *
 * Copyright (c) 2013 Nilaksh Das, Gina Trapani
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
 * @copyright 2013 Nilaksh Das, Gina Trapani
 * @author Nilaksh Das <nilakshdas [at] gmail [dot] com>
 */

class ResponseTimeInsight extends InsightPluginParent implements InsightPlugin {
    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        if (self::shouldGenerateInsight('response_time', $instance, $insight_date='today',
        $regenerate_existing_insight=false, $day_of_week=5, count($last_week_of_posts))) {
            $response_count = array('reply' => 0, 'retweet' => 0, 'like' => 0);

            foreach ($last_week_of_posts as $post) {
                $reply_count = $post->reply_count_cache;
                $retweet_count = $post->retweet_count_cache;
                $fav_count = $post->favlike_count_cache;

                $response_count['reply'] += $reply_count;
                $response_count['retweet'] +=  $retweet_count;
                $response_count['like'] += $fav_count;
            }

            arsort($response_count);
            $response_factor = each($response_count);

            if ($response_factor['value']) {
                $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
                foreach ($response_count as $key => $value) {
                    $insight_baseline_dao->insertInsightBaseline('response_count_'.$key, $instance->id, $value,
                    $this->insight_date);
                }

                $time_per_response = floor((60 * 60 * 24 * 7) / $response_factor['value']);
                $time_str = strncmp(InsightTerms::getSyntacticTimeDifference($time_per_response), "1 ", 2) == 0 ?
                substr(InsightTerms::getSyntacticTimeDifference($time_per_response), 2)
                : InsightTerms::getSyntacticTimeDifference($time_per_response);

                $insight_text = $this->username."'s ".$this->terms->getNoun('post', InsightTerms::PLURAL)
                ." averaged <strong>1 new ".$this->terms->getNoun($response_factor['key'])
                ."</strong> every <strong>".$time_str."</strong> over the last week";

                $last_fri = date('Y-m-d', strtotime('-7 day'));
                $last_fri_insight_baseline = $insight_baseline_dao->getInsightBaseline(
                'response_count_'.$response_factor['key'], $instance->id, $last_fri);
                if (isset($last_fri_insight_baseline) && $last_fri_insight_baseline->value > 0) {
                    $last_fri_time_per_response = floor((60 * 60 * 24 * 7) / $last_fri_insight_baseline->value);
                    $time_str1 = strncmp(InsightTerms::getSyntacticTimeDifference($last_fri_time_per_response),
                    "1 ", 2) == 0 ?
                    substr(InsightTerms::getSyntacticTimeDifference($last_fri_time_per_response), 2)
                    : InsightTerms::getSyntacticTimeDifference($last_fri_time_per_response);

                    if ($last_fri_time_per_response < $time_per_response) {
                        $insight_text .= ", slower than the previous week's average of 1 "
                        .$this->terms->getNoun($response_factor['key'])." every " .$time_str1;
                    } elseif ($last_fri_time_per_response > $time_per_response) {
                        $insight_text .= ", faster than the previous week's average of 1 "
                        .$this->terms->getNoun($response_factor['key'])." every " .$time_str1;
                    }
                }
                $insight_text .= '.';

                $this->insight_dao->insertInsight("response_time", $instance->id, $this->insight_date, "Response time:",
                $insight_text, basename(__FILE__, ".php"), Insight::EMPHASIS_LOW);
            }
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('ResponseTimeInsight');
