<?php

/*
 Plugin Name: Response Time
 Description: How quickly your posts generate replies, favorites, and reshares every week.
 When: Fridays for Twitter, Mondays otherwise
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

        if ($instance->network == 'twitter') {
            $day_of_week = 5;
        } else {
            $day_of_week = 1;
        }
        $should_generate_insight = self::shouldGenerateWeeklyInsight('response_time', $instance, $insight_date='today',
            $regenerate_existing_insight=false, $day_of_week = $day_of_week, count($last_week_of_posts));

        if ($should_generate_insight) {
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

                $headline = $this->username."'s ".$this->terms->getNoun('post', InsightTerms::PLURAL)
                ." averaged <strong>1 new ".$this->terms->getNoun($response_factor['key'])
                ."</strong> every <strong>".$time_str."</strong> this week.";

                $last_fri = date('Y-m-d', strtotime('-7 day'));
                $last_fri_insight_baseline = $insight_baseline_dao->getInsightBaseline(
                'response_count_'.$response_factor['key'], $instance->id, $last_fri);
                if (isset($last_fri_insight_baseline) && $last_fri_insight_baseline->value > 0) {
                    $last_fri_time_per_response = floor((60 * 60 * 24 * 7) / $last_fri_insight_baseline->value);
                    $time_str1 = strncmp(InsightTerms::getSyntacticTimeDifference($last_fri_time_per_response),
                    "1 ", 2) == 0 ?
                    substr(InsightTerms::getSyntacticTimeDifference($last_fri_time_per_response), 2)
                    : InsightTerms::getSyntacticTimeDifference($last_fri_time_per_response);

                    $tachy_markup = "<i class=\"fa fa-tachometer fa-3x text-muted\" style=\"float: right; "
                    ."color: #ddd;\"></i> That's ";

                    // Only show a comparison string if the rates are substantially different
                    if ($last_fri_time_per_response < $time_per_response && $time_str1 != $time_str) {
                        $insight_text .= $tachy_markup . "slower than the previous week's average of 1 "
                        . $this->terms->getNoun($response_factor['key'])." every " .$time_str1 . ".";
                    } elseif ($last_fri_time_per_response > $time_per_response && $time_str1 != $time_str) {
                        $insight_text .= $tachy_markup . "faster than the previous week's average of 1 "
                        . $this->terms->getNoun($response_factor['key'])." every " .$time_str1 . ".";
                    }
                }

                if (!isset($insight_text)) {
                    $insight_text = 'If you ' . $this->terms->getVerb('posted') .
                        ' once every waking hour, that would be roughly 120 times a week.';
                }

                //Instantiate the Insight object
                $my_insight = new Insight();

                //REQUIRED: Set the insight's required attributes
                $my_insight->instance_id = $instance->id;
                $my_insight->slug = 'response_time'; //slug to label this insight's content
                $my_insight->date = $this->insight_date; //date of the data this insight applies to
                $my_insight->headline = $headline;
                $my_insight->text = $insight_text;
                $my_insight->header_image = '';
                $my_insight->emphasis = Insight::EMPHASIS_MED; //Set emphasis optionally
                $my_insight->filename = basename(__FILE__, ".php"); //Same for every insight

                $this->insight_dao->insertInsight($my_insight);
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('ResponseTimeInsight');
