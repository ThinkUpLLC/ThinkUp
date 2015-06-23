<?php
/*
 Plugin Name: Outreach Punchcard
 Description: What times of day your posts get the biggest reaction.
 When: 7th for Twitter, 14th for Facebook, 23rd for Instagram
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
    /**
     * Slug for this insight
     **/
    var $slug = 'outreach_punchcard';

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $since_date = date("Y-m-d");
        $filename = basename(__FILE__, ".php");
        $regenerate = false;

        switch ($instance->network) {
            case 'twitter':
                $day_of_month = 7;
                break;
            case 'facebook':
                $day_of_month = 14;
                break;
            case 'instagram':
                $day_of_month = 23;
                break;
            default:
                $day_of_month = 23;
        }

        $should_generate_insight = self::shouldGenerateMonthlyInsight($this->slug, $instance,
            $insight_date=$since_date, $regenerate_existing_insight=$regenerate, $day_of_month = $day_of_month,
            $count_related_posts=null, $excluded_networks=null, $enable_bonus_alternate_day = true);

        if ($should_generate_insight) {
            $this->logger->logInfo("Should generate", __METHOD__.','.__LINE__);

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

            $last_months_posts = $post_dao->getAllPostsByUsernameOrderedBy($instance->network_username,
                $instance->network, $count=null, $order_by="pub_date", $in_last_x_days = 30,
                $iterator = false, $is_public = false);

            $punchcard = array();
            $responses_chron = array();
            $response_avg_timediffs = array();
            for ($hotd = 0; $hotd < 24; $hotd++) {
                for ($dotm = 1; $dotm <= 30; $dotm++) {
                    $punchcard['posts'][$dotm][$hotd] = 0;
                    $punchcard['responses'][$dotm][$hotd] = 0;
                }
                $responses_chron[$hotd] = 0;
            }

            // $this->logger->logInfo("Last month's posts: ".Utils::varDumpToString($last_months_posts),
            //     __METHOD__.','.__LINE__);

            if ($instance->network !== 'instagram') { //count replies and retweets
                foreach ($last_months_posts as $post) {
                    $responses = array();
                    $responses = array_merge(
                    (array)$post_dao->getRepliesToPost($post->post_id, $post->network),
                    (array)$post_dao->getRetweetsOfPost($post->post_id, $post->network)
                    );

                    foreach ($responses as $response) {
                        $response_pub_date = new DateTime($response->pub_date);
                        $response_dotm = date('j', (date('U', strtotime($response->pub_date)+$offset))); // Day of month
                        $response_hotd = date('G', (date('U', strtotime($response->pub_date)+$offset))); // Hour of day
                        $punchcard['responses'][$response_dotm][$response_hotd]++;

                        $responses_chron[$response_hotd]++;
                    }

                    $post_pub_date = new DateTime($post->pub_date);
                    $post_dotm = date('j', (date('U', strtotime($post->pub_date)+$offset))); // Day of the month
                    $post_hotd = date('G', (date('U', strtotime($post->pub_date)+$offset))); // Hour of the day
                    $punchcard['posts'][$post_dotm][$post_hotd]++;
                }
            } else { //count likes
                foreach ($last_months_posts as $post) {
                    $post_pub_date = new DateTime($post->pub_date);
                    $post_dotm = date('j', (date('U', strtotime($post->pub_date)+$offset))); // Day of month
                    $post_hotd = date('G', (date('U', strtotime($post->pub_date)+$offset))); // Hour of day
                    $punchcard['responses'][$post_dotm][$post_hotd]++;

                    //$this->logger->logInfo("HOTD: ".$post_hotd, __METHOD__.','.__LINE__);

                    $responses_chron[$post_hotd] += $post->favlike_count_cache;
                    $punchcard['posts'][$post_dotm][$post_hotd]++;
                }
            }

            arsort($responses_chron);
            $most_responses = each($responses_chron);

            $insight_text = '';

            if ($most_responses['value'] > 2) {
                $time1_low_hotd = $most_responses['key'];
                $time1_high_hotd = $time1_low_hotd + 1;

                $time1_low = (($time1_low_hotd % 12) ? ($time1_low_hotd % 12) : 12)
                .((floor($time1_low_hotd / 12) == 1) ? 'pm' : 'am');
                $time1_high = (($time1_high_hotd % 12) ? ($time1_high_hotd % 12) : 12)
                .((floor($time1_high_hotd / 12) == 1) ? 'pm' : 'am');

                $plural = $most_responses['value']==1 ? InsightTerms::SINGULAR : InsightTerms::PLURAL;

                if ($instance->network == 'instagram')  {
                    $insight_text = "In the past month, what ". $this->username." posted "
                        ."between <strong>".$time1_low." and ".$time1_high
                        ."</strong> on Instagram got the most love - " .$most_responses['value']
                        . " " .$this->terms->getNoun('like', $plural) . " in all.";
                } else {
                    $insight_text = "In the past month, ". $this->username."'s "
                        . $this->terms->getNoun('post', InsightTerms::PLURAL)
                        ." got the biggest response between <strong>".$time1_low." and ".$time1_high."</strong> - "
                        . $most_responses['value']." "
                        . $this->terms->getNoun('reply', $plural) . " in all.";
                }

                // $this->logger->logInfo("Responses chron: ".Utils::varDumpToString($responses_chron),
                //     __METHOD__.','.__LINE__);

                foreach ($responses_chron as $key => $value) {
                    if ($value > 0 && $value < $most_responses['value']) {
                        $time2_low_hotd = $key;
                        $time2_high_hotd = $time2_low_hotd + 1;

                        $time2_low = (($time2_low_hotd % 12) ? ($time2_low_hotd % 12) : 12)
                        .((floor($time2_low_hotd / 12) == 1) ? 'pm' : 'am');
                        $time2_high = (($time2_high_hotd % 12) ? ($time2_high_hotd % 12) : 12)
                        .((floor($time2_high_hotd / 12) == 1) ? 'pm' : 'am');

                        if ($instance->network == 'instagram') {
                            $response_text = ($value > 1 ? 'hearts' : 'heart');
                        } else {
                            $response_text = ($value > 1 ? 'responses' : 'response');
                        }
                        $comparison_text = " That's compared to ".$value." "
                            .$response_text ." between ".$time2_low." and ".$time2_high.". ";
                    }
                }

                $insight_text .= $comparison_text;
                $headline = $this->username."'s best time is around " . $time1_low;

                $optimal_hour = substr($time1_low, 0, -2);

                //Instantiate the Insight object
                $my_insight = new Insight();

                //REQUIRED: Set the insight's required attributes
                $my_insight->instance_id = $instance->id;
                $my_insight->slug = $this->slug; //slug to label this insight's content
                $my_insight->date = $this->insight_date; //date of the data this insight applies to
                $my_insight->headline = $headline;
                $my_insight->text = $insight_text;
                $my_insight->header_image = '';
                $my_insight->emphasis = Insight::EMPHASIS_HIGH;
                $my_insight->filename = basename(__FILE__, ".php");
                // $my_insight->related_data = $punchcard;
                $my_insight->related_data = $optimal_hour;

                $this->insight_dao->insertInsight($my_insight);

            } else {
                $this->logger->logInfo("No insight: Most responses is ".Utils::varDumpToString($most_responses),
                    __METHOD__.','.__LINE__);
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('OutreachPunchcardInsight');
