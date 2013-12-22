<?php
/*
 Plugin Name: Outreach Punchcard
 Description: What times of day your posts get the biggest reaction.
 When: Saturdays
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/outreachpunchcard.php
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

class OutreachPunchcardInsight extends InsightPluginParent implements InsightPlugin {
    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        if (parent::shouldGenerateInsight('outreach_punchcard', $instance, $insight_date='today',
        $regenerate_existing_insight=false, $day_of_week=6, count($last_week_of_posts))) {
            $cfg = Config::getInstance();
            $local_timezone = new DateTimeZone($cfg->getValue('timezone'));

            $post_dao = DAOFactory::getDAO('PostDAO');
            $punchcard = array();
            $responses_chron = array();
            $response_avg_timediffs = array();
            for ($hotd = 0; $hotd < 24; $hotd++) {
                for ($dotw = 1; $dotw <= 7; $dotw++) {
                    $punchcard['posts'][$dotw][$hotd] = 0;
                    $punchcard['responses'][$dotw][$hotd] = 0;
                }
                $responses_chron[$hotd] = 0;
            }

            foreach ($last_week_of_posts as $post) {
                $responses = array();
                $responses = array_merge(
                (array)$post_dao->getRepliesToPost($post->post_id, $post->network),
                (array)$post_dao->getRetweetsOfPost($post->post_id, $post->network)
                );

                foreach ($responses as $response) {
                    $response_pub_date = new DateTime($response->pub_date);
                    $response_dotw = date('N',
                    (date('U',
                    strtotime($response->pub_date)) + timezone_offset_get($local_timezone, $response_pub_date)
                    )
                    ); // Day of the week
                    $response_hotd = date('G',
                    (date('U',
                    strtotime($response->pub_date)) + timezone_offset_get($local_timezone, $response_pub_date)
                    )
                    ); // Hour of the day
                    $punchcard['responses'][$response_dotw][$response_hotd]++;

                    $responses_chron[$response_hotd]++;
                }

                $post_pub_date = new DateTime($post->pub_date);
                $post_dotw = date('N',
                (date('U',
                strtotime($post->pub_date)) + timezone_offset_get($local_timezone, $post_pub_date)
                )
                ); // Day of the week
                $post_hotd = date('G',
                (date('U',
                strtotime($post->pub_date)) + timezone_offset_get($local_timezone, $post_pub_date)
                )
                ); // Hour of the day
                $punchcard['posts'][$post_dotw][$post_hotd]++;
            }

            arsort($responses_chron);
            $most_responses = each($responses_chron);

            $insight_text = '';

            if ($most_responses['value'] > 0) {
                $time1_low_hotd = $most_responses['key'];
                $time1_high_hotd = $time1_low_hotd + 1;

                $time1_low = (($time1_low_hotd % 12) ? ($time1_low_hotd % 12) : 12)
                .((floor($time1_low_hotd / 12) == 1) ? 'pm' : 'am');
                $time1_high = (($time1_high_hotd % 12) ? ($time1_high_hotd % 12) : 12)
                .((floor($time1_high_hotd / 12) == 1) ? 'pm' : 'am');

                $insight_text = $this->username."'s ".$this->terms->getNoun('post', InsightTerms::PLURAL)
                ." from last week got <strong>".$most_responses['value']." "
                .($most_responses['value'] > 1 ? 'responses' : 'response')."</strong>"
                ." between <strong>".$time1_low." and ".$time1_high."</strong>";

                $insight_comparison_text = '';
                foreach ($responses_chron as $key => $value) {
                    if ($value > 0 && $value < $most_responses['value']) {
                        $time2_low_hotd = $key;
                        $time2_high_hotd = $time2_low_hotd + 1;

                        $time2_low = (($time2_low_hotd % 12) ? ($time2_low_hotd % 12) : 12)
                        .((floor($time2_low_hotd / 12) == 1) ? 'pm' : 'am');
                        $time2_high = (($time2_high_hotd % 12) ? ($time2_high_hotd % 12) : 12)
                        .((floor($time2_high_hotd / 12) == 1) ? 'pm' : 'am');

                        $insight_comparison_text = ", as compared to <strong>".$value." "
                        .($value > 1 ? 'responses' : 'response')."</strong>"
                        ." between <strong>".$time2_low." and ".$time2_high."</strong>";
                    }
                }

                $insight_text .= $insight_comparison_text.".";

                $this->insight_dao->insertInsightDeprecated("outreach_punchcard", $instance->id, $this->insight_date,
                "Time of day:", $insight_text, basename(__FILE__, ".php"), Insight::EMPHASIS_LOW,
                serialize($punchcard));
            }
        }


        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('OutreachPunchcardInsight');
