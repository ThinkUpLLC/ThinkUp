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

        $in_test_mode =  ((isset($_SESSION["MODE"]) && $_SESSION["MODE"] == "TESTS") || getenv("MODE")=="TESTS");
        //Only insert this insight if it's Friday or if we're testing
        if ((date('w') == 5 || $in_test_mode) && count($last_week_of_posts)) {
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

                $insight_text = $this->username."'s ".$this->terms->getNoun('post', InsightTerms::PLURAL)
                ." averaged one new ".$this->terms->getNoun($response_factor['key'])." every "
                .self::getSyntacticTimeDifference($time_per_response)." over the last week";

                $last_wed = date('Y-m-d', strtotime('-7 day'));
                $last_wed_insight_baseline = $insight_baseline_dao->getInsightBaseline(
                'response_count_'.$response_factor['key'], $instance->id, $last_wed);
                if (isset($last_wed_insight_baseline)) {
                    $last_wed_time_per_response = $last_wed_insight_baseline->value > 0 ?
                    floor((60 * 60 * 24 * 7) / $last_wed_insight_baseline->value) : null;

                    if (self::getSyntacticTimeDifference($last_wed_time_per_response)
                    != self::getSyntacticTimeDifference($time_per_response)) {
                        if (isset($last_wed_time_per_response) && $last_wed_time_per_response < $time_per_response) {
                            $insight_text .= ", slower than the previous week's average of one "
                            .$this->terms->getNoun($response_factor['key'])
                            ." every " .self::getSyntacticTimeDifference($last_wed_time_per_response);
                        } elseif (isset($last_wed_time_per_response)
                        && $last_wed_time_per_response > $time_per_response) {
                            $insight_text .= ", faster than the previous week's average of one "
                            .$this->terms->getNoun($response_factor['key'])
                            ." every " .self::getSyntacticTimeDifference($last_wed_time_per_response);
                        }
                    }
                }
                $insight_text .= '.';

                $this->insight_dao->insertInsight("response_time", $instance->id, $this->insight_date, "Response Time:",
                $insight_text, basename(__FILE__, ".php"), Insight::EMPHASIS_LOW);
            }
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

    /**
     * Get the human-readable, syntactic time difference .
     * @param int $delta Time difference in seconds
     * @return str Syntactic time difference
     */
    public static function getSyntacticTimeDifference($delta) {
        $tokens = array();
        $tokens['second'] = 1;
        $tokens['minute'] = 60 * $tokens['second'];
        $tokens['hour'] = 60 * $tokens['minute'];
        $tokens['day'] = 24 * $tokens['hour'];

        arsort($tokens);

        foreach ($tokens as $unit => $value) {
            if ($delta < $value) {
                continue;
            } else {
                $number_of_units = floor($delta / $value);
                return $number_of_units.' '.$unit.(($number_of_units > 1) ? 's' : '');
            }
        }
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('ResponseTimeInsight');
