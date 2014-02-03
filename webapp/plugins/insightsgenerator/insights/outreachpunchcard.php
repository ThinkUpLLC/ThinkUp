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

            $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
            $owner_dao = DAOFactory::getDAO('OwnerDAO');

            $owner_instance = $owner_instance_dao->getByInstance($instance->id);
            $owner_id = $owner_instance[0]->owner_id;
            $owner = $owner_dao->getById($owner_instance[0]->owner_id);
            try {
                $owner_timezone = new DateTimeZone($owner->timezone);
            } catch (Exception $e) {
                // In the odd case the owner has no or a malformed timezone
                $cfg = Config::getInstance();
                $owner_timezone = new DateTimeZone($cfg->getValue('timezone'));
            }
            $now = new DateTime();
            $offset = timezone_offset_get($owner_timezone, $now);


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
                    $response_dotw = date('N', (date('U', strtotime($response->pub_date)+$offset))); // Day of week
                    $response_hotd = date('G', (date('U', strtotime($response->pub_date)+$offset))); // Hour of day
                    $punchcard['responses'][$response_dotw][$response_hotd]++;

                    $responses_chron[$response_hotd]++;
                }

                $post_pub_date = new DateTime($post->pub_date);
                $post_dotw = date('N', (date('U', strtotime($post->pub_date)+$offset))); // Day of the week
                $post_hotd = date('G', (date('U', strtotime($post->pub_date)+$offset))); // Hour of the day
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

                $headline = $this->username."'s ".$this->terms->getNoun('post', InsightTerms::PLURAL)
                ." from last week got"
                ." the best response between <strong>".$time1_low." and ".$time1_high."</strong>"
                . " - ".$most_responses['value']." "
                // . ($most_responses['value'] > 1 ? 'responses' : 'response')."</strong> "
                . $this->terms->getNoun('reply', InsightTerms::PLURAL)
                . " in all.";

                $insight_comparison_text = '';
                foreach ($responses_chron as $key => $value) {
                    if ($value > 0 && $value < $most_responses['value']) {
                        $time2_low_hotd = $key;
                        $time2_high_hotd = $time2_low_hotd + 1;

                        $time2_low = (($time2_low_hotd % 12) ? ($time2_low_hotd % 12) : 12)
                        .((floor($time2_low_hotd / 12) == 1) ? 'pm' : 'am');
                        $time2_high = (($time2_high_hotd % 12) ? ($time2_high_hotd % 12) : 12)
                        .((floor($time2_high_hotd / 12) == 1) ? 'pm' : 'am');

                        $insight_comparison_text = "That's compared to ".$value." "
                        .($value > 1 ? 'responses' : 'response').""
                        ." between ".$time2_low." and ".$time2_high.". ";
                    }
                }

                $insight_text .= $insight_comparison_text
                . "The best timing for an important " . $this->terms->getNoun('post') . "  might be <strong>around "
                . $time1_low . "</strong>.";

                $optimal_hour = substr($time1_low, 0, -2);

                //Instantiate the Insight object
                $my_insight = new Insight();

                //REQUIRED: Set the insight's required attributes
                $my_insight->instance_id = $instance->id;
                $my_insight->slug = 'outreach_punchcard'; //slug to label this insight's content
                $my_insight->date = $this->insight_date; //date of the data this insight applies to
                $my_insight->headline = $headline;
                $my_insight->text = $insight_text;
                $my_insight->header_image = '';
                $my_insight->emphasis = Insight::EMPHASIS_MED;
                $my_insight->filename = basename(__FILE__, ".php"); //Same for every insight, must be set exactly this way
                // $my_insight->related_data = $punchcard;
                $my_insight->related_data = $optimal_hour;

                $this->insight_dao->insertInsight($my_insight);

            }
        }


        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('OutreachPunchcardInsight');
