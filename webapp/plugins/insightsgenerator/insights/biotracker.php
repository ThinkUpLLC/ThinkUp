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
 * Copyright (c) 2014-2016 Chris Moyer
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
 * @copyright 2014-2016 Chris Moyer
 * @author Chris Moyer <chris [at] inarow [dot] net>
 */
class BioTrackerInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for the bio change insight
     **/
    var $slug_bio = 'bio_tracker';
    /**
     * Slug for the avatar change insight
     **/
    var $slug_avatar = 'avatar_tracker';

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating profile change insight", __METHOD__.','.__LINE__);

        //Bio changes
        if (($instance->network == 'twitter' || $instance->network == 'instagram')
            && $this->shouldGenerateInsight($this->slug_bio, $instance)) {
            $this->logger->logInfo("Should generate bio change tracker", __METHOD__.','.__LINE__);
            $user_versions_dao = DAOFactory::getDAO('UserVersionsDAO');
            $versions = $user_versions_dao->getRecentFriendsVersions($user, 7, array('description'));
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
                        if ($user
                            && Utils::stripURLsOutOfText($user->description)
                            !== Utils::stripURLsOutOfText($last_description['field_value'])) {
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
            $this->logger->logInfo("Got ".count($changes)." bio changes", __METHOD__.','.__LINE__);
            if (count($changes) > 0) {
                $changes = array_slice($changes, 0, 10);
                $insight = new Insight();
                $insight->instance_id = $instance->id;
                $insight->slug = $this->slug_bio;
                $insight->date = $this->insight_date;
                $insight->filename = basename(__FILE__, ".php");
                $insight->emphasis = Insight::EMPHASIS_MED;
                $insight->related_data = array('changes' => $changes);
                $insight->text = $this->getTextBioChange($changes, $instance);
                $insight->headline = $this->getHeadlineBioChange($changes, $instance);
                if (count($changes) == 1) {
                    $insight->header_image = $changes[0]["user"]->avatar;
                }
                $this->insight_dao->insertInsight($insight);
            }
        }

        //Avatar changes
        if (($instance->network == 'twitter' || $instance->network == 'instagram')
            && $this->shouldGenerateInsight($this->slug_avatar, $instance)) {
            $this->logger->logInfo("Should generate avatar change tracker", __METHOD__.','.__LINE__);
            $user_versions_dao = DAOFactory::getDAO('UserVersionsDAO');
            $versions = $user_versions_dao->getRecentFriendsVersions($user, 7, array('avatar'));
            //$this->logger->logInfo(Utils::varDumpToString($versions), __METHOD__.','.__LINE__);
            $changes = array();
            $examined_users = array();
            $user_dao = DAOFactory::getDAO('UserDAO');
            foreach ($versions as $change) {
                $user_key = intval($change['user_key']);
                if (!in_array($user_key, $examined_users)) {
                    $examined_users[] = $user_key;
                    $last_version = $user_versions_dao->getVersionBeforeDay($user_key,date('Y-m-d'),'avatar');
                    if ($last_version) {
                        $user = $user_dao->getDetailsByUserKey($user_key);
                        if ($user && ($user->avatar !== $last_version['field_value'])) {
                            $do_show_change = true;

                            //Extra check for ThinkUp LLC users
                            if (Utils::isThinkUpLLC()) {
                                $api_accessor = new ThinkUpLLCAPIAccessor();

                                $avatar_url1_https = preg_replace('/^http:(.+)$/', "https:$1", $user->avatar);
                                $avatar_url2_https = preg_replace('/^http:(.+)$/', "https:$1",
                                    $last_version['field_value']);

                                if ($instance->network == 'twitter') {
                                    //Get the original version of the avatar
                                    //https://dev.twitter.com/overview/general/user-profile-images-and-banners
                                    $avatar_url1_https = str_replace('_normal', '', $avatar_url1_https);
                                    $avatar_url2_https = str_replace('_normal', '', $avatar_url2_https);
                                }

                                $do_show_change = $api_accessor->didAvatarsChange($avatar_url1_https,
                                    $avatar_url2_https);

                                if (!$do_show_change) {
                                    $this->logger->logInfo("Skipping change for ".$avatar_url1_https." and ".
                                        $avatar_url2_https, __METHOD__.','.__LINE__);
                                }
                            }

                            if ($do_show_change) {
                                $changes[] = array(
                                    'user' => $user,
                                    'field_name' => 'avatar',
                                    'field_description' => 'avatar',
                                    'before' => $last_version['field_value'],
                                    'after' => $user->avatar
                                );
                            }
                        }
                    }
                }
            }
            $this->logger->logInfo("Got ".count($changes)." avatar changes", __METHOD__.','.__LINE__);
            if (count($changes) > 0) {
                $changes = array_slice($changes, 0, 10);
                $insight = new Insight();
                $insight->instance_id = $instance->id;
                $insight->slug = $this->slug_avatar;
                $insight->date = $this->insight_date;
                $insight->filename = basename(__FILE__, ".php");
                $insight->emphasis = Insight::EMPHASIS_MED;
                $insight->related_data = array('changes' => $changes);
                $insight->text = $this->getTextAvatarChange($changes, $instance);
                $insight->headline = $this->getHeadlineAvatarChange($changes, $instance);
                $this->insight_dao->insertInsight($insight);
            }
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

    private function getTextBioChange($changes, $instance) {
        $network = ucfirst($instance->network);
        $text_options = array(
            "Spot the difference?",
            "Even small changes can be big news."
        );
        if (count($changes) == 1) {
            $username = ($changes[0]['user']->network == 'twitter' ? '@' : '') . $changes[0]['user']->username;
            $base = "$username has an updated $network profile.";
            $they = $username;
        } else {
            $base = count($changes) . " of ".$this->username."'s friends changed their $network description.";
            $text_options[] = "They might appreciate that someone noticed.";
        }
        return $base . ' '. $this->getVariableCopy($text_options);
    }

    private function getTextAvatarChange($changes, $instance) {
        $network = ucfirst($instance->network);
        $text_options = array(
            "What do you think?"
        );
        if (count($changes) == 1) {
            $username = ($changes[0]['user']->network == 'twitter' ? '@' : '') . $changes[0]['user']->username;
            $base = "$username has a new $network photo.";
            $they = $username;
        } else {
            $base = count($changes) . " of ".$this->username."'s friends changed their $network avatar.";
            $text_options[] = "They might appreciate that someone noticed.";
        }
        return $base . ' '. $this->getVariableCopy($text_options);
    }

    private function getHeadlineBioChange($changes, $instance) {
        $network = ucfirst($instance->network);
        $username = ($changes[0]['user']->network == 'twitter' ? '@' : '') . $changes[0]['user']->username;
        if (count($changes) == 1) {
            $base = $this->getVariableCopy(array(
                "Something's different about %user1",
                "%user1 changes it up",
                "%user1 makes an adjustment",
                "%user1 tries something new",
                "What's new with %user1"
            ), array('user1' => $username));
        } else {
            $second_username = ($changes[0]['user']->network == 'twitter' ? '@' : '') . $changes[1]['user']->username;
            if (count($changes) > 2) {
                $total_more = count($changes) - 2;
                $base = $username.", ".$second_username.", and ".$total_more." other".
                (($total_more == 1)?"":"s")." changed their profiles";
            } else {
                $base = $username." and ".$second_username." changed their profiles";
            }
        }
        return $base;
    }

    private function getHeadlineAvatarChange($changes, $instance) {
        $network = ucfirst($instance->network);
        $username = ($changes[0]['user']->network == 'twitter' ? '@' : '') . $changes[0]['user']->username;
        if (count($changes) == 1) {
            $base = $this->getVariableCopy(array(
                "%user1 has a new profile picture",
                "%user1 got a new look",
                "%user1 is looking good!"
            ), array('user1' => $username));
        } else {
            $second_username = ($changes[0]['user']->network == 'twitter' ? '@' : '') . $changes[1]['user']->username;
            if (count($changes) > 2) {
                $total_more = count($changes) - 2;
                $base = $username.", ".$second_username.", and ".$total_more." other".
                (($total_more == 1)?"":"s")." changed their avatar";
            } else {
                $base = $username." and ".$second_username." changed their profile photos";
            }
        }
        return $base;
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('BioTrackerInsight');
