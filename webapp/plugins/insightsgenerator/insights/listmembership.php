<?php
/*
 Plugin Name: List Membership
 Description: New lists to which you've been added (chart).
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/listmembership.php
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani [at] gmail [dot] com>
 */

class ListMembershipInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
        $filename = basename(__FILE__, ".php");

        $last_insight = $this->insight_dao->getMostRecentInsight('new_group_memberships', $instance->id);
        $last_insight_ts = 0;
        if ($last_insight) {
            $last_insight_ts = strtotime($last_insight->time_generated);
        }
        if ($last_insight_ts < (time() - (60*60*24*7))) {
            //get new group memberships per day
            $group_membership_dao = DAOFactory::getDAO('GroupMemberDAO');
            $new_groups = $group_membership_dao->getNewMembershipsSince($instance->network, $instance->network_user_id,
                date('Y-m-d h:i', $last_insight_ts));

            if (sizeof($new_groups) > 0 ) {
                // Clean up non-unique names, which just looks weird/bad
                $unique_new_groups = array();
                $seen_groups = array();
                foreach ($new_groups as $group) {
                    $group->setMetadata();
                    if (!in_array($group->keyword, $seen_groups)) {
                        $seen_groups[] = $group->keyword;
                        $unique_new_groups[] = $group;
                    }
                }
                $new_groups = $unique_new_groups;
                $count_history_dao = DAOFactory::getDAO('CountHistoryDAO');
                $list_membership_count_history_by_day = $count_history_dao->getHistory($instance->network_user_id,
                    $instance->network, 'DAY', 15, null, 'group_memberships');
                if (sizeof($new_groups) > 1) {
                    $group_names = array();
                    $group_urls = array();
                    foreach ($new_groups as $group) {
                        $group_names[] = '&ldquo;'.str_replace('-', ' ', $group->keyword).'&rdquo;';
                        $group_urls[] = '<a href="'.$group->url.'">'.$group->keyword.'</a>';
                    }

                    $headline_groups = array_slice($group_names, 0, 4);
                    $number = count($headline_groups);
                    foreach ($headline_groups as $i=>&$name) {
                        if ($number == 2) {
                            if ($i==1) {
                                $name = 'and '.$name;
                            }
                        } else {
                            $name = ($i == ($number - 1)) ? 'and '.$name : $name.',';
                        }
                    }
                    if (TimeHelper::getTime() % 2 == 1) {
                        $headline = $this->username .' got added to lists called ' .join(' ', $headline_groups).'.';
                    } else {
                        $headline = "Do " . join(' ', $headline_groups);
                        $headline .= ' sound like good descriptions of ' . $this->username . '?';
                    }

                    if (count($group_urls) > 4) {
                        $group_name_list = join(', ', array_slice($group_urls, 0, 4)).', and '
                            . (count($group_urls)-4).' more';
                    }
                    else if (count($group_urls) > 2) {
                        $group_urls[count($group_urls)-1] = 'and '.$group_urls[count($group_urls)-1];
                        $group_name_list = join(', ', $group_urls);
                    }
                    else {
                        $group_name_list = join(' and ', $group_urls);
                    }
                    $insight_text = "$this->username is on ".sizeof($new_groups)." new lists: ".$group_name_list;

                    if (is_array($list_membership_count_history_by_day['history'])
                        && end($list_membership_count_history_by_day['history']) > sizeof($new_groups)) {
                        $total_lists = end($list_membership_count_history_by_day['history']) + sizeof($new_groups);
                        $insight_text .=  ", bringing the total to <strong>". number_format($total_lists).
                        " lists</strong>.";
                    } else {
                        $insight_text .= ".";
                    }

                } else {
                    if (TimeHelper::getTime() % 2 == 1 && $instance->network == 'twitter') {
                        $list_name_parts = explode('/', $new_groups[0]->group_name);
                        $maker = $list_name_parts[0];
                        $headline = $maker .' added '.$this->username.' to a list that\'s called &ldquo;'
                            . $new_groups[0]->keyword . '&rdquo;.';
                    }
                    else {
                        $headline = "Does &ldquo;" . str_replace('-', ' ', $new_groups[0]->keyword).
                            "&rdquo; seem like a good description of " . $this->username . "?";
                    }
                    $insight_text = "$this->username got added to a new list, ".'<a href="'.$new_groups[0]->url.'">'.
                        $new_groups[0]->keyword."</a>";
                    if (end($list_membership_count_history_by_day['history']) > sizeof($new_groups)) {
                        $total_lists = end($list_membership_count_history_by_day['history']) + sizeof($new_groups);
                        $insight_text .= ", bringing the total to <strong>". number_format($total_lists)
                            . " lists</strong>";
                    }
                    $insight_text .= ".";
                }
                $insight = new Insight();
                $insight->slug = 'new_group_memberships';
                $insight->instance_id = $instance->id;
                $insight->date = $this->insight_date;
                $insight->filename = basename(__FILE__, ".php");
                $insight->emphasis = Insight::EMPHASIS_LOW;
                $insight->headline = $headline;
                $insight->text = $insight_text;
                if (count($list_membership_count_history_by_day['history']) >= 3) {
                    $insight->related_data = serialize($list_membership_count_history_by_day);
                }
                $this->insight_dao->insertInsight($insight);
            }
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('ListMembershipInsight');
