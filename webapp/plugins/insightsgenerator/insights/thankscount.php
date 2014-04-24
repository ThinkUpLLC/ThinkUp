<?php
/*
 Plugin Name: Thanks Count
 Description: How often you used phrases of gratitude.
 When: 2nd of the month for Twitter, 4th otherwise
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/thankscount.php
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
class ThanksCountInsight extends CriteriaMatchInsightPluginParent implements InsightPlugin {
    public function shouldGenerate(Instance $instance, $last_week_of_posts) {
        if ($instance->network == 'twitter') {
            $day_of_month = 2;
        } else {
            $day_of_month = 4;
        }
        return $this->shouldGenerateMonthlyInsight($this->getSlug(), $instance, $insight_date='today',
            $regenerate_existing_insight=false, $day_of_month=$day_of_month, count($last_week_of_posts));
    }

    public function getSlug() {
        return 'thankscount';
    }

    public function getNumberOfDaysNeeded() {
        return date('t', strtotime('-1 month'));
    }

    public function postMatchesCriteria(Post $post, Instance $instance) {
        if ($post->in_reply_to_user_id == $instance->network_user_id) {
            return false;
        }
        $text = strtolower($post->post_text);
        $has_thanks = preg_match('/(\W|^)(thanks|thank you)(\W|$)/', $text);
        if ($has_thanks) {
            if (preg_match('/(\W|^)no (thanks|thank you)/', $text) || preg_match('/thank(s| you),? but/', $text)) {
                $has_thanks = false;
            }
        }
        return $has_thanks;
    }

    public function getInsightForCounts($this_period_count, $last_period_count, $instance, $matching_posts) {
        $insight = null;
        if ($this_period_count > 0) {
                $thankee = null;
                foreach ($matching_posts as $post) {
                    if ($post->in_reply_to_user_id) {
                        $user_dao = DAOFactory::getDAO('UserDAO');
                        $user = $user_dao->getDetails($post->in_reply_to_user_id, $instance->network);
                        if ($user) {
                            $thankee = $user;
                        }
                    }
                }
                $insight = new Insight();
                $insight->slug = $this->getSlug();
                $insight->instance_id = $instance->id;
                $insight->date = $this->insight_date;
                $user_prefix = ($instance->network == 'twitter' ? '@' : '');
                if ($thankee) {
                    $insight->headline = $user_prefix. $thankee->username . ' probably appreciated it.';
                    $insight->header_image = $thankee->avatar;
                    $insight->text = $this->getVariableCopy(array(
                        '%username %posted '.$this_period_count.' thank-you'.($this_period_count!=1?'s':'').
                        ' last month.'
                    ));
                } else {
                    $insight->headline = $this->getVariableCopy(array(
                        'Way to show appreciation.',
                        'Gratitude makes everybody happy.',
                        'Gratitude is contagious.',
                        'Saying &ldquo;thanks&rdquo; is a great way to spend time on '.ucfirst($instance->network).'.'
                    ));
                    $times = $this->terms->getOccurrencesAdverb($this_period_count);
                    $insight->text = $this->getVariableCopy(array(
                        '%username thanked someone '.$times. ' on '.  ucfirst($instance->network).' last month.',
                        '%username %posted '.$this_period_count.' thank-you'.($this_period_count!=1?'s':'').
                        ' last month.'
                    ));
                }

                if ($this_period_count > $last_period_count && $last_period_count > 0) {
                    $two_months_ago_name = date('F', strtotime('-2 month'));
                    $one_month_ago_name = date('F', strtotime('-1 month'));
                    $insight->text .= ' Sounds like there was even more to be thankful about in '.$one_month_ago_name.
                    ' than in '.$two_months_ago_name.'.';
                }
                $insight->filename = basename(__FILE__, ".php");
                $insight->emphasis = Insight::EMPHASIS_LOW;
        }
        return $insight;
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('ThanksCountInsight');
