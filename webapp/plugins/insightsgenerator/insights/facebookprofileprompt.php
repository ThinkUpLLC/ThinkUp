<?php
/*
 Plugin Name: Facebook Profile Prompt
 Description: Reminder to update a potentially outdated Facebook profile.
 When: 15th of the month (every other month)
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/facebookprofileprompt.php
 *
 * Copyright (c) 2014 Chris Moyer
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
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer <chris [at] inarow [dot] net>
 */
class FacebookProfilePromptInsight extends InsightPluginParent implements InsightPlugin {
    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
        if ($instance->network == 'facebook' &&
            $this->shouldGenerateMonthlyInsight($slug, $instance, 'today', false, 15)) {

            $ok_to_generate = false;

            // Find the last time we prompted (or "beginning of time" if we have never prompted)
            $baseline_slug = 'facebook_profile_prompted';
            $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
            $last_baseline = $insight_baseline_dao->getMostRecentInsightBaseline($baseline_slug, $instance->id);

            $last_prompted = 0;
            if ($last_baseline) {
                $last_prompted = strtotime($last_baseline->date);
            }

            // Only prompt at most every other month.  At least 57 days are between two 15ths of the month.
            $time_diff = time() - $last_prompted;
            if ($time_diff > (60*60*24*57)) {
                // Swap for the FacebookInstance so we have profile_updated
                $facebook_instance_dao = DAOFactory::getDAO('FacebookInstanceDAO');
                $instance = $facebook_instance_dao->get($instance->id);

                $profile_updated_ts = time();
                if ($instance->profile_updated) {
                    $profile_updated_ts = strtotime($instance->profile_updated);
                }

                $time_since_update = time() - $profile_updated_ts;
                if ($time_since_update > (60*60*24*60)) {
                    $ok_to_generate = true;
                }
            }

            if ($ok_to_generate) {
                $insight = new Insight();
                $insight->slug = 'facebook_profile_prompt';
                $insight->filename = basename(__FILE__, ".php");
                $insight->instance_id = $instance->id;
                $insight->date = $this->insight_date;

                $months = floor($time_since_update / (60*60*24*30));
                $insight->headline = $this->getVariableCopy(array(
                    "It’s been over %months months since %username's profile was updated.",
                    "Is %username's Facebook profile up to date?",
                ), array('months' => $months));
                if (date('Y') == date('Y', $profile_updated_ts)) {
                    $nice_date = date('F jS', $profile_updated_ts);
                } else {
                    $nice_date = date('F jS, Y', $profile_updated_ts);
                }
                $insight->text = $this->getVariableCopy(array(
                    "Might be worth checking if it's still up to date. "
                        . "%username's Facebook profile hasn't been updated since $nice_date.",
                    "Can't hurt to see if that profile info is still accurate. "
                        . "(%username's Facebook profile hasn't been updated since $nice_date.)"
                ));

                $insight->setButton(array(
                    'label' => 'Edit Facebook Profile',
                    'url' => 'https://www.facebook.com/me?sk=info&edit=eduwork&ref=update_info_button',
                ));

                $this->insight_dao->insertInsight($insight);
                $insight_baseline_dao->insertInsightBaseline($baseline_slug, $instance->id, $months);
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('FacebookProfilePromptInsight');
