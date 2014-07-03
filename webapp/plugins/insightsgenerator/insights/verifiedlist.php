<?php
/*
 Plugin Name: Verified Follower List
 Description: List of all verified followers
 When: Once
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/verifiedlist.php
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
 */

class VerifiedListInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        if ($instance->network == 'twitter' || $instance->network == 'facebook') {
            $insight = $this->insight_dao->getMostRecentInsight('verifiedlist', $instance->id);
            if (!$insight || strtotime($insight->time_generated) <= (time()-(60*60*24*365))) {
                $follow_dao = DAOFactory::getDAO('FollowDAO');
                $count = $follow_dao->getVerifiedFollowerCount($instance->network_user_id, $instance->network);
                $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
                $baseline = $baseline_dao->getMostRecentInsightBaseline('verifiedlistcount', $instance->id);

                $baseline_dao->insertInsightBaseline("verifiedlistcount", $instance->id, $count, $this->insight_date);
                $baseline_count = isset($baseline->value) ? $baseline->value : 0;

                if ($count > 0 && (!$insight || $count != $baseline_count)) {
                    $insight = new Insight();
                    $insight->slug = 'verifiedlist';
                    $insight->instance_id = $instance->id;
                    $insight->date = $this->insight_date;
                    $insight->filename = basename(__FILE__, ".php");
                    if ($instance->network == 'facebook') {
                        $insight->headline = 'Did you know Facebook has verified users?';
                    } else {
                        $insight->headline = 'Verified!';
                    }

                    $bodies = array();
                    $bodies[] = "%username has %total verified "
                        .$this->terms->getNoun('follower',$count==1? InsightTerms::SINGULAR : InsightTerms::PLURAL).'.';
                    $bodies[] = "%total of %username's followers sport the coveted blue verified badge.";
                    $bodies[] ="%username is basking in the reflected ".(ucfirst($instance->network))."-legitimacy "
                        . "of %total verified "
                        .$this->terms->getNoun('follower',$count==1? InsightTerms::SINGULAR : InsightTerms::PLURAL).'.';

                    if ($count > $baseline_count && $baseline) {
                        $bodies[] = "With %total verified followers, %username must be doing something right.";
                    }

                    $body = $this->getVariableCopy($bodies, array('total'=>$count));
                    if ($count != $baseline_count && $baseline) {
                        $updown = $count > $baseline_count ? 'up' : 'down';
                        $body .= " That's $updown from $baseline_count last year.";
                    }

                    $insight->text = $body;

                    $verifieds = $follow_dao->getVerifiedFollowers($instance->network_user_id, $instance->network, 15);
                    $insight->setPeople($verifieds);

                    $this->insight_dao->insertInsight($insight);
                }
            }
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('VerifiedListInsight');
