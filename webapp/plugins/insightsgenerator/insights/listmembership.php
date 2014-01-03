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

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
        $filename = basename(__FILE__, ".php");

        if (self::shouldGenerateInsight('new_group_memberships', $instance, $insight_date='today',
        $regenerate_existing_insight=true)) {
            //get new group memberships per day
            $group_membership_dao = DAOFactory::getDAO('GroupMemberDAO');
            $new_groups = $group_membership_dao->getNewMembershipsByDate($instance->network, $instance->network_user_id,
            $this->insight_date);
            if (sizeof($new_groups) > 0 ) { //if not null, store insight
                $count_history_dao = DAOFactory::getDAO('CountHistoryDAO');
                $list_membership_count_history_by_day = $count_history_dao->getHistory($instance->network_user_id,
                $instance->network, 'DAY', 15, null, 'group_memberships');
                if (sizeof($new_groups) > 1) {
                    $group_name_list = '';
                    $group_number = 0;
                    $headline = "Do ";
                    foreach ($new_groups as $group) {
                        if (sizeof($new_groups) > 4) { //If more than 4 lists, just display first 4
                            if ($group_number >= 4) {
                                if ($group_number == 4) {
                                    $group_name_list .= ", and ". (sizeof($new_groups) - 4)." more";
                                }
                            } else {
                                if ($group_name_list != '') {
                                    $headline .=", ";
                                    $group_name_list .= ", ";
                                }
                            }
                            if ($group_number == 3) {
                                $headline .= "and ";
                            }
                            if ($group_number < 4 ) {
                                $group->setMetadata();
                                $headline .= "&ldquo;" . str_replace('-', ' ', $group->keyword) . "&rdquo;";
                                $group_name_list .= '<a href="'.$group->url.'">'.$group->keyword.'</a>';
                            }
                            $group_number++;
                        } else  { //Display all lists
                            if ($group == end($new_groups)) {
                                $group_name_list .= " and";
                                $headline .= " and ";
                            } else {
                                if ($group_name_list != '') {
                                    $headline .= ", ";
                                    $group_name_list .= ",";
                                }
                            }
                            $group->setMetadata();
                            if ($group_number == sizeof($new_groups)){
                                    $headline .= "and ";
                            }
                            $headline .= "&ldquo;" . str_replace('-', ' ', $group->keyword). "&rdquo;";
                            $group_name_list .= ' <a href="'.$group->url.'">'.$group->keyword.'</a>';
                        }
                    }
                    $headline .= ' sound like good descriptions of ' . $this->username . '?';
                    $insight_text = "$this->username is on ".sizeof($new_groups)." new lists: ".$group_name_list;
                    if (is_array($list_membership_count_history_by_day['history'])
                    && end($list_membership_count_history_by_day['history']) > sizeof($new_groups)) {
                        $total_lists = end($list_membership_count_history_by_day['history']) + sizeof($new_groups);
                        $insight_text .=  ", bringing the total to <strong>". number_format($total_lists).
                        " lists</strong>.";
                    } else {
                        $insight_text .= ".";
                    }
                    $this->insight_dao->insertInsightDeprecated('new_group_memberships', $instance->id,
                    $this->insight_date, $headline, $insight_text, $filename, Insight::EMPHASIS_LOW,
                    serialize($list_membership_count_history_by_day));

                } else {
                    $new_groups[0]->setMetadata();
                    $headline = "Does \"" . str_replace('-', ' ', $new_groups[0]->keyword).
                    "\" seem like a good description of " . $this->username . "?";
                    $insight_text = "$this->username got added to a new list, ".'<a href="'.$new_groups[0]->url.'">'.
                    $new_groups[0]->keyword."</a>";
                    if (end($list_membership_count_history_by_day['history']) > sizeof($new_groups)) {
                        $total_lists = end($list_membership_count_history_by_day['history']) + sizeof($new_groups);
                        $insight_text .= ", bringing the total to <strong>". number_format($total_lists)
                        . " lists</strong>";
                    }
                    $insight_text .= ".";

                    $this->insight_dao->insertInsightDeprecated('new_group_memberships', $instance->id,
                    $this->insight_date, $headline, $insight_text, $filename, Insight::EMPHASIS_LOW,
                    serialize($list_membership_count_history_by_day));
                }
            }
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('ListMembershipInsight');
