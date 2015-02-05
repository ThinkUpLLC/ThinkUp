<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/model/class.InsightsGeneratorPlugin.php
 *
 * Copyright (c) 2012-2015 Gina Trapani
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
 * @copyright 2012-2015 Gina Trapani
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
        $logger = Logger::getInstance();

        // If we've got a Mandrill key and template, send HTML
        if ($config->getValue('mandrill_api_key') != null && !empty($options['mandrill_template'])) {
            $logger->logUserInfo("Mandrill API key and template set; sending HTML", __METHOD__.','.__LINE__);
            $view->assign('insights', $insights);
            $view->assign('application_url', Utils::getApplicationURL());
            $view->assign('header_text', $this->getEmailMessageHeaderText());
            if (Utils::isThinkUpLLC()) {
                $logger->logUserInfo("Email via ThinkUpLLC, process welcome / free trial messaging",
                    __METHOD__.','.__LINE__);
                $thinkupllc_endpoint = $config->getValue('thinkupllc_endpoint');
                $view->assign('unsub_url', $thinkupllc_endpoint.'settings.php');
                if (!isset($options['last_daily_email'])) {
                    $logger->logUserInfo("No daily email ever sent before, include welcome message",
                        __METHOD__.','.__LINE__);
                    $view->assign('show_welcome_message', true);
                } else {
                    if ($owner->is_free_trial) {
                        $logger->logUserInfo("Owner is in free trial", __METHOD__.','.__LINE__);
                        $creation_date = new DateTime($owner->joined);
                        $now = new DateTime();
                        $end_of_trial = $creation_date->add(new DateInterval('P15D'));
                        if ($end_of_trial >= $now) {
                            $interval = $now->diff($end_of_trial);
                            $days_left = $interval->format('%a');

                            if ($days_left > 2) {
                                $view->assign('pay_prompt_headline', $days_left.' days left in your free trial!');
                            } elseif ($days_left == 0) {
                                //Last day
                                $view->assign('pay_prompt_headline', 'Last chance!');
                            } else {
                                //Show hours if it's 24 or 48 hours
                                $view->assign('pay_prompt_headline', 'Only '.($days_left*24).' hours left!');
                            }
                            $explainer_copy_options = array(
                                "Your free trial expires today. Don't lose any of your insights!", //Last chance!
                                "It's time to become a member. We'd love to have you.", // 1 day left
                                "It's just ".(($owner->membership_level == 'Member')?'16':'32').
                                    " cents a day to get smarter about the time you spend online.", //2 days left
                                "Isn't this better than boring \"analytics\"?", //3 days left
                                "Just wait 'til you see ThinkUp next week.", //4 days left
                                "We never sell your data and we don't show you ads.", //5 days left
                                "Get our exclusive book on the future of social media for free.", //6 days left
                                "ThinkUp gives you social network superpowers.", //7 days left
                                "The longer you use ThinkUp, the smarter it gets.", //8 days left
                                "ThinkUp helps you be more thoughtful about your time online.", //9 days left
                                "ThinkUp works in email, on the web, and on all your devices.", //10 days left
                                "ThinkUp members can cancel at any time—with no hassles.", //11 days left
                                'Wait until you see what ThinkUp has in store tomorrow.', //12 days left
                                "Your morning ThinkUp email will make your day.", //13 days left
                            );
                            $view->assign('pay_prompt_explainer', $explainer_copy_options[$days_left]);
                            if ($owner->membership_level == 'Member') {
                                $view->assign('pay_prompt_button_label', 'Just $5/month');
                            } elseif ($owner->membership_level == 'Pro') {
                                $view->assign('pay_prompt_button_label', 'Just $10/month');
                            }
                        }
                    } else {
                        //Check subscription status and show a message if Payment failed
                        //@TODO Handle Payment due state here as well
                        $logger->logUserInfo("User is not in free trial; check subscription status",
                            __METHOD__.','.__LINE__);

                        $thinkupllc_api_accessor = new ThinkUpLLCAPIAccessor();
                        $membership_details = $thinkupllc_api_accessor->getSubscriptionStatus($owner->email);
                        $logger->logUserInfo("Subscription status is ".Utils::varDumpToString($membership_details),
                            __METHOD__.','.__LINE__);

                        if (isset($membership_details->subscription_status)
                            && $membership_details->subscription_status == 'Payment failed') {

                            $logger->logUserInfo("Owner has payment failure; include alert in email",
                                __METHOD__.','.__LINE__);

                            $payment_failed_copy = array ();
                            $payment_failed_copy[] = array(
                                'headline'=>'Oops! Your account needs attention',
                                'explainer' => "We had a problem processing your last membership payment. "
                                    ."But it's easy to fix."
                            );
                            $payment_failed_copy[] = array(
                                'headline'=>'Uh oh, problem with your subscription...',
                                'explainer' => "There was a problem processing your last membership payment. "
                                    ."To fix it, update your payment info."
                            );
                            $payment_failed_copy[] = array(
                                'headline'=>'Your ThinkUp subscription is out of date...',
                                'explainer' => "We tried to charge your Amazon account for your ThinkUp membership,"
                                    ." and there was an error. But it's easy to fix."
                            );
                            $payment_failed_copy[] = array(
                                'headline'=>'Action required to keep your ThinkUp account active',
                                'explainer' => "We weren't able to process your last membership payment—maybe your "
                                    ."info is out of date? Fixing it just takes a moment."
                            );
                            $payment_failed_copy[] = array(
                                'headline'=>"Urgent! Keep your ThinkUp account active",
                                'explainer' => "We tried to process your ThinkUp subscription, but "
                                    ."the payment was not successful. Please update your payment information "
                                    ."now to make sure your ThinkUp membership stays in good standing."
                            );

                            $copy_index = TimeHelper::getDayOfYear() % count($payment_failed_copy);
                            $payment_failed_headline = $payment_failed_copy[$copy_index]['headline'];
                            $payment_failed_explainer = $payment_failed_copy[$copy_index]['explainer'];
                            $payment_failed_button_label = "Update your payment info";

                            $view->assign('payment_failed_headline', $payment_failed_headline);
                            $view->assign('payment_failed_explainer', $payment_failed_explainer);
                            $view->assign('payment_failed_button_label', $payment_failed_button_label);
                        }
                    }
                }
                $thinkupllc_email_tout = $config->getValue('thinkupllc_email_tout');
                if (isset($thinkupllc_email_tout)) {
                    $view->assign('thinkupllc_email_tout', $thinkupllc_email_tout);
                }
            } else {
                $logger->logUserInfo("Email is NOT via ThinkUpLLC", __METHOD__.','.__LINE__);
                $view->assign('unsub_url', Utils::getApplicationURL().'account/index.php?m=manage#instances');
            }
            // It's a weekly digest if we're going back more than a day or two.
            $daily_or_weekly = $weekly ? 'Weekly' : 'Daily';
            $view->assign('weekly_or_daily', $daily_or_weekly);
            $view->assign('pay_prompt_url', $config->getValue('thinkupllc_endpoint').'membership.php');
            if ($config->getValue('image_proxy_enabled') == true) {
                $view->assign('image_proxy_sig', $config->getValue('image_proxy_sig'));
            }
            $insights_markup = $view->fetch(Utils::getPluginViewDirectory($this->folder_name)
                .'_email.insights_html.tpl');

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
                $logger->logUserInfo("About to call Mailer::mailHTMLViaMandrillTemplate", __METHOD__.','.__LINE__);
                Mailer::mailHTMLViaMandrillTemplate($owner->email, $subject_line,
                    $options['mandrill_template']->option_value, $parameters);
                return true;
            } catch (Mandrill_Unknown_Template $e) {
                // In this case, we'll fall back to plain text sending and warn the user in the log
                $logger->logUserError("Invalid mandrill template configured:".
                $options['mandrill_template']->option_value.".", __METHOD__.','.__LINE__);
                unset($options['mandrill_template']);
            }
        } else {
            if ($config->getValue('mandrill_api_key') == null) {
                $logger->logUserInfo("Mandrill API key is null", __METHOD__.','.__LINE__);
            }
            if (empty($options['mandrill_template'])) {
                $logger->logUserInfo("Mandrill template is not set", __METHOD__.','.__LINE__);
            }
        }

        $view->assign('app_title', $config->getValue('app_title_prefix')."ThinkUp" );
        $view->assign('application_url', Utils::getApplicationURL());
        $view->assign('insights', $insights);
        $message = $view->fetch(Utils::getPluginViewDirectory($this->folder_name).$template);
        list ($subject, $message) = explode("\n", $message, 2);

        $logger->logUserInfo("About to call Mailer::mail", __METHOD__.','.__LINE__);
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

        // Use a HIGH emphasis insight headline as the email subject line
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
        // If neither high nor medium are available, use a generic headline
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
