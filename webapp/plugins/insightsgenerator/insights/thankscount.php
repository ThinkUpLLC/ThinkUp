<?php
/*
 Plugin Name: Thanks Count
 Description: How often did you use phrases of gratitude
 When: 2nd of the Month
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
        return $this->shouldGenerateMonthlyInsight($this->getSlug(), $instance, $insight_date='today',
            $regenerate_existing_insight=false, $day_of_month=2, count($last_week_of_posts), $excluded_networks=null);
    }

    public function getSlug() {
        return 'thankscount';
    }

    public function getNumberOfDaysNeeded() {
        return date('t', strtotime('-1 month'));
    }

    public function postMatchesCriteria(Post $post) {
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
                    $insight->headline = $user_prefix. $thankee->username . ' had to have been happy to be thanked.';
                    $insight->header_image = $thankee->avatar;
                }
                else {
                    $insight->headline = 'Gratitude is contagious. Saying &ldquo;thanks&rdquo; is a really great way '
                        . 'to spend time on '.$instance->network.'.';
                }

                $times = $this_period_count == 1 ? 'time' : 'times';
                $insight->text = $this->username.' thanked someone '.$this_period_count. ' '.$times. ' on '.
                    $instance->network.' last month.';

                if ($this_period_count > $last_period_count && $last_period_count > 0) {
                    $month_name = date('F', strtotime('-2 month'));
                    $insight->text .= ' Seems like there was even more to be thankful about than in '.$month_name.'.';
                }
                $insight->filename = basename(__FILE__, ".php");
                $insight->emphasis = Insight::EMPHASIS_LOW;
        }
        return $insight;
    }
}
