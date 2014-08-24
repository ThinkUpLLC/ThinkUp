<?php
/*
 Plugin Name: Bio tracker
 Description: Which of your friends have changed their profile's bio information, and how.
 When: Always
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/biotracker.php
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
class BioTrackerInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     **/
    var $slug = 'bio_tracker';

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        if ($instance->network == 'twitter' && $this->shouldGenerateInsight($this->slug, $instance)) {
            $user_versions_dao = DAOFactory::getDAO('UserVersionsDAO');
            $versions = $user_versions_dao->getRecentFriendsVersions($user->id, 7, array('description'));
            //$this->logger->logInfo(Utils::varDumpToString($versions), __METHOD__.','.__LINE__);
            $changes = array();
            $examined_users = array();
            foreach ($versions as $change) {
                $user_key = intval($change['user_key']);
                if (!in_array($user_key, $examined_users)) {
                    $examined_users[] = $user_key;
                    $last_description = $user_versions_dao->getVersionBeforeDay($user_key,date('Y-m-d'),'description');
                    if ($last_description) {
                        $user_dao = DAOFactory::getDAO('UserDAO');
                        $user = $user_dao->getDetailsByUserKey($user_key);
                        if ($user && $user->description !== $last_description['field_value']) {
                            $changes[] = array(
                                'user' => $user,
                                'field_name' => 'description',
                                'field_description' => 'bio',
                                'before' => $last_description['field_value'],
                                'after' => $user->description
                            );
                        }
                    }
                }
            }
            $this->logger->logInfo("Got ".count($changes)." changes", __METHOD__.','.__LINE__);
            if (count($changes) > 0) {
                $changes = array_slice($changes, 0, 10);
                $insight = new Insight();
                $insight->instance_id = $instance->id;
                $insight->slug = $this->slug;
                $insight->date = $this->insight_date;
                $insight->filename = basename(__FILE__, ".php");
                $insight->emphasis = Insight::EMPHASIS_MED;
                $insight->related_data = array('changes' => $changes);
                $insight->text = $this->getText($changes, $instance);
                $insight->headline = $this->getHeadline($changes, $instance);
                $this->insight_dao->insertInsight($insight);
            }
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

    private function getText($changes, $instance) {
        $network = ucfirst($instance->network);
        if (count($changes) == 1) {
            $username = ($changes[0]['user']->network == 'twitter' ? '@' : '') . $changes[0]['user']->username;
            $base = "$username has a new $network bio.";
            $they = $username;
        } else {
            $base = count($changes) . " of ".$this->username."'s friends changed their $network bios.";
            $they = "They";
        }
        return $base . ' '. $this->getVariableCopy(array(
            "Spot the difference?",
            "Even small changes can be big news.",
            "%they might appreciate that someone noticed."
        ), array('they' => $they));
    }

    private function getHeadline($changes, $instance) {
        $network = ucfirst($instance->network);
        if (count($changes) == 1) {
            $username = ($changes[0]['user']->network == 'twitter' ? '@' : '') . $changes[0]['user']->username;
            $base = $this->getVariableCopy(array(
                "What's new with %user1?",
                "Something's different about %user1.",
                "What's changed about %user1?",
                "Something's happening with %user1.",
                "Did anyone notice what's different about %user1?"
            ), array('user1' => $username));
        } else {
            $base = $this->getVariableCopy(array(
                "Hi Profile Changes!",
                "Changing of the Bio.",
                "Auto Biography.",
                "Ch-ch-ch-ch-changes!",
                "Bio(nic) Vision.",
                "Mapping the %network Bio-me.",
            ), array('network' => $network));
        }
        return $base;
    }

}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('BioTrackerInsight');
