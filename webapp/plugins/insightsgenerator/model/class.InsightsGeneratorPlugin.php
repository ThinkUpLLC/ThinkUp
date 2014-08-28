<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/model/class.InsightsGeneratorPlugin.php
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
 *
 * Insights Generator Plugin
 *
 * Pluggable plugin (recursion!) for generating items in the insights stream.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

class InsightsGeneratorPlugin extends Plugin implements CrawlerPlugin {
    /**
     * @const The day on which we send weekly digests.
     */
    const WEEKLY_DIGEST_DAY_OF_WEEK = 1; //Monday

    /**
     * @var Current Unix timestamp, here for testing.
     */
    var $current_timestamp;

    public function __construct($vals=null) {
        parent::__construct($vals);
        $this->folder_name = 'insightsgenerator';
        $this->current_timestamp = time();
    }

    public function activate() {

    }

    public function deactivate() {

    }

    public function renderConfiguration($owner) {
        $controller = new InsightsGeneratorPluginConfigurationController($owner);
        return $controller->go();
    }

    public function renderInstanceConfiguration($owner, $instance_username, $instance_network) {
        return '';
    }

    public function crawl() {
        $logger = Logger::getInstance();

        // Include all the insights files so they register themselves
        foreach (glob(THINKUP_WEBAPP_PATH."plugins/insightsgenerator/insights/*.php") as $filename) {
            require_once $filename;
        }

        //Get instances by owner
        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $user_dao = DAOFactory::getDAO('UserDAO');
        $current_owner = $owner_dao->getByEmail(Session::getLoggedInUser());

        $instances = $instance_dao->getByOwner($current_owner, false, true);

        // Get posts for last 7 days
        $number_days = 7;
        $post_dao = DAOFactory::getDAO('PostDAO');

        $insights_plugin_registrar = PluginRegistrarInsights::getInstance();

        foreach ($instances as $instance) {
            $user = $user_dao->getDetails($instance->network_user_id, $instance->network);
            if ($user === null) {
                $user = new User();
                $user->user_name = $instance->network_username;
                $user->user_id = $instance->network_user_id;
                $user->network = $instance->network;
            }
            $last_week_of_posts = $post_dao->getAllPostsByUsernameOrderedBy($instance->network_username,
                $network=$instance->network, $count=0, $order_by="pub_date", $in_last_x_days = $number_days,
                $iterator = false, $is_public = false);
            $insights_plugin_registrar->runRegisteredPluginsInsightGeneration($instance, $user, $last_week_of_posts,
                $number_days);
            $logger->logUserSuccess("Completed insight generation for ".$instance->network_username." on ".
            $instance->network, __METHOD__.','.__LINE__);
        }

        // Don't do email for regular users
        if (!$current_owner->is_admin) {
            return;
        }

        // Send email digest the first run after 4am
        $tz = $current_owner->timezone;
        if (empty($tz)) {
            $config = Config::getInstance();
            $tz = $config->getValue('timezone');
        }

        if (!empty($tz)) {
            $original_tz = date_default_timezone_get();
            date_default_timezone_set($tz);
            $localized_hour = (int)date('G', $this->current_timestamp);
            date_default_timezone_set($original_tz);
        } else {
            $localize_hour = (int)date('G', $this->current_timestamp);
        }
        if ($localized_hour >= 4) {
            //Get plugin options
            $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
            $options = $plugin_option_dao->getOptionsHash($this->folder_name, true);
            //Get plugin ID
            $plugin_dao = DAOFactory::getDAO('PluginDAO');
            $plugin_id = $plugin_dao->getPluginId($this->folder_name);
            //Get today's date
            $today = date('Y-m-d', $this->current_timestamp);
            $now = date('Y-m-d H:i:s', $this->current_timestamp);

            $do_daily = false;
            $do_weekly = false;

            $last_daily = isset($options['last_daily_email']) ? $options['last_daily_email']->option_value : null;
            $last_daily_day = null;
            if ($last_daily) {
                $last_daily_day = substr($last_daily, 0, 10);
            }
            if ($last_daily_day != $today) {
                if ($last_daily === null) {
                    $plugin_option_dao->insertOption($plugin_id, 'last_daily_email', $now);
                } else {
                    $plugin_option_dao->updateOption($options['last_daily_email']->id,
                        'last_daily_email', $now);
                }
                $do_daily = true;
            }

            $last_weekly = isset($options['last_weekly_email']) ? $options['last_weekly_email']->option_value : null;
            $last_weekly_day = null;
            if ($last_weekly) {
                $last_weekly_day = substr($last_weekly, 0, 10);
            }
            if ($last_weekly_day != $today && date('w', $this->current_timestamp) == self::WEEKLY_DIGEST_DAY_OF_WEEK) {
                if ($last_weekly === null) {
                    $plugin_option_dao->insertOption($plugin_id, 'last_weekly_email', $now);
                } else {
                    $plugin_option_dao->updateOption($options['last_weekly_email']->id,
                        'last_weekly_email', $now);
                }
                $do_weekly = true;
            }

            if ($do_daily || $do_weekly) {
                $owners = $owner_dao->getAllOwners();
            }

            if ($do_daily) {
                foreach ($owners as $owner) {
                    if ($this->sendDailyDigest($owner, $options, $last_daily)) {
                        $logger->logUserSuccess("Mailed daily digest to ".$owner->email.".", __METHOD__.','.__LINE__);
                    }
                }
            }

            if ($do_weekly) {
                foreach ($owners as $owner) {
                    if ($this->sendWeeklyDigest($owner, $options, $last_weekly)) {
                        $logger->logUserSuccess("Mailed weekly digest to ".$owner->email.".", __METHOD__.','.__LINE__);
                    }
                }
            }
        }
    }

    /**
     * Email daily insight digest.
     * @param Owner $owner Owner to send for
     * @param array $options Plugin options
     * @param str $last_sent When we last sent this message
     * return bool Whether email was sent
     */
    private function sendDailyDigest($owner, $options, $last_sent) {
        if (in_array($owner->email_notification_frequency, array('both','daily'))) {
            if ($last_sent === null) {
                $last_sent = '-24 hour';
            }
            else if (strlen($last_sent) <= 10) {
                $last_sent .= ' 04:00';
            }
            return $this->sendDigestSinceWithTemplate($owner, $last_sent, '_email.daily_insight_digest.tpl',
                $options, false);
        } else {
            return false;
        }

    }

    /**
     * Email weekly insight digest.
     * @param Owner $owner Owner to send for
     * @param array $options Plugin options
     * @param str $last_sent When we last sent this message
     * return bool Whether email was sent
     */
    private function sendWeeklyDigest($owner, $options, $last_sent) {
        if (in_array($owner->email_notification_frequency, array('both','weekly'))) {
            if ($last_sent === null) {
                $last_sent = '-7 day';
            }
            else if (strlen($last_sent) <= 10) {
                $last_sent .= ' 04:00';
            }
            return $this->sendDigestSinceWithTemplate($owner, $last_sent, '_email.weekly_insight_digest.tpl',
                $options, true);
        } else {
            return false;
        }
    }

    /**
     * Send out insight email digest for a given time period.
     * @param Owner $owner Owner to send for
     * @param str $start When to start insight lookup
     * @param str $template Email view template to use
     * @param array $options Plugin options
     * @param bool $weekly Is this a weekly email?
     * return bool Whether email was sent
     */
    private function sendDigestSinceWithTemplate($owner, $start, $template, &$options, $weekly) {
        $insights_dao = DAOFactory::GetDAO('InsightDAO');
        $start_time = date( 'Y-m-d H:i:s', strtotime($start, $this->current_timestamp));
        $insights = $insights_dao->getAllOwnerInstanceInsightsSince($owner->id, $start_time);
        $num_insights = count($insights);
        if ($num_insights == 0) {
            return false;
        }

        $config = Config::getInstance();
        $view = new ViewManager();
        $view->caching=false;

        // If we've got a Mandrill key and template, send HTML
        if ($config->getValue('mandrill_api_key') != null && !empty($options['mandrill_template'])) {
            $view->assign('insights', $insights);
            $view->assign('application_url', Utils::getApplicationURL());
            $view->assign('header_text', $this->getEmailMessageHeaderText());
            if (Utils::isThinkUpLLC()) {
                $thinkupllc_endpoint = $config->getValue('thinkupllc_endpoint');
                $view->assign('unsub_url', $thinkupllc_endpoint.'settings.php');
                if (!isset($options['last_daily_email'])) {
                    $view->assign('show_welcome_message', true);
                }
                $thinkupllc_email_tout = $config->getValue('thinkupllc_email_tout');
                if (isset($thinkupllc_email_tout)) {
                    $view->assign('thinkupllc_email_tout', $thinkupllc_email_tout);
                }
            } else {
                $view->assign('unsub_url', Utils::getApplicationURL().'account/index.php?m=manage#instances');
            }
            // It's a weekly digest if we're going back more than a day or two.
            $daily_or_weekly = $weekly ? 'Weekly' : 'Daily';
            $view->assign('weekly_or_daily', $daily_or_weekly);
            $insights_markup = $view->fetch(Utils::getPluginViewDirectory($this->folder_name).'_email.insights_html.tpl');

            $parameters = array();
            $parameters['insights'] = $insights_markup;
            $parameters['app_title'] = $config->getValue('app_title_prefix')."ThinkUp";
            $parameters['application_url'] = Utils::getApplicationURL();
            $parameters['weekly_or_daily'] = $daily_or_weekly;

            try {
                if (!isset($options['last_daily_email'])) {
                    $subject_line = "Welcome to ThinkUp! Here are your insights.";
                } else {
                    $subject_line = $this->getEmailMessageSubjectLine($daily_or_weekly, $insights);
                }
                Mailer::mailHTMLViaMandrillTemplate($owner->email, $subject_line,
                $options['mandrill_template']->option_value, $parameters);
                return true;
            } catch (Mandrill_Unknown_Template $e) {
                // In this case, we'll fall back to plain text sending and warn the user in the log
                $logger = Logger::getInstance();
                $logger->logUserError("Invalid mandrill template configured:".
                $options['mandrill_template']->option_value.".", __METHOD__.','.__LINE__);
                unset($options['mandrill_template']);
            }
        }

        $view->assign('app_title', $config->getValue('app_title_prefix')."ThinkUp" );
        $view->assign('application_url', Utils::getApplicationURL());
        $view->assign('insights', $insights);
        $message = $view->fetch(Utils::getPluginViewDirectory($this->folder_name).$template);
        list ($subject, $message) = explode("\n", $message, 2);

        Mailer::mail($owner->email, $subject, $message);
        return true;
    }

    /**
     * Return random email body header text.
     * @return str
     */
    private function getEmailMessageHeaderText() {
        $header_text_choices = array (
            "Here's what's up!",
            "Okay, check it out:",
            "How are you doing?",
            "It's a good day.",
            "Here's what you've got:" );
        $rand_index = rand(0, (sizeof($header_text_choices)-1));
        return $header_text_choices[$rand_index];
    }

    /**
     * Return email subject line based on the insight headline of a high or medium insight (converted to second person).
     * If neither exist, use generic headline text.
     * @param str $daily_or_weekly "Daily" or "Weekly"
     * @param arr $insights Insight objects
     * @return str
     */
    public function getEmailMessageSubjectLine($daily_or_weekly, $insights) {
        $num_insights = count($insights);
        $insight_headline_subject = null;

        //Testing: Use high/med insight headline as subject line for 10% of users
        if ( TimeHelper::getTime() % 10 == 1) {
            foreach ($insights as $insight) {
                if ($insight->emphasis == Insight::EMPHASIS_HIGH) {
                    $terms = new InsightTerms($insight->instance->network);
                    $insight_headline_subject = $terms->swapInSecondPerson($insight->instance->network_username,
                        strip_tags(html_entity_decode($insight->headline, ENT_NOQUOTES, 'UTF-8')));
                    break;
                }
            }
            // If no HIGH insights existed, check medium
            if ( !isset($insight_headline_subject) ) {
                foreach ($insights as $insight) {
                    if ($insight->emphasis == Insight::EMPHASIS_MED) {
                        $terms = new InsightTerms($insight->instance->network);
                        $insight_headline_subject = $terms->swapInSecondPerson($insight->instance->network_username,
                            strip_tags(html_entity_decode($insight->headline, ENT_NOQUOTES, 'UTF-8')) );
                        break;
                    }
                }
            }
        }
        if ( !isset($insight_headline_subject) ) {
            if ($daily_or_weekly == "Daily") {
                $subject_line_choices = array (
                "ThinkUp has new insights for you! Take a look",
                "You have new insights from ThinkUp",
                "Your new insights from ThinkUp",
                "New ThinkUp insights are ready for you",
                "These are your latest ThinkUp insights",
                "A few new ThinkUp insights for you",
                "New ThinkUp insights are waiting for you",
                "ThinkUp: Today's insights",
                "These are your ThinkUp insights for ".date('l', $this->current_timestamp),
                );
                if ($num_insights > 1) {
                    $subject_line_choices[] = "ThinkUp found %total insights for you today. Here's a look.";
                    $subject_line_choices[] = "You have %total new insights from ThinkUp";
                }
            } else {
                $subject_line_choices = array (
                "This week was great! ThinkUp's got details",
                "How did you do online this week? Here are your ThinkUp insights",
                "Your ThinkUp insights this week",
                "New ThinkUp insights are ready for you",
                "This week's ThinkUp insights"
                );
            }
            $rand_index = TimeHelper::getTime() % count($subject_line_choices);
            $subject = $subject_line_choices[$rand_index];
            $subject = str_replace('%total', number_format($num_insights), $subject);
        } else {
            $subject = $insight_headline_subject;
        }
        return $subject;
    }
}
