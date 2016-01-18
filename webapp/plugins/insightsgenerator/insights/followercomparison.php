<?php
/*
 Plugin Name: Follower Count Comparison
 Description: See how many friends you could help out by retweeting them.
 When: Monthly on the 8th for Twitter
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/followercomparison.php
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
class FollowerComparisonInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug used for this insight
     */
    private $slug = 'follower_comparison';

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $network = $instance->network;
        $user_id = $instance->network_user_id;
        if ($network == 'twitter' && $this->shouldGenerateMonthlyInsight($this->slug, $instance, 'today', false, 8)) {

            $my_count = $user->follower_count;
            $follow_dao = DAOFactory::getDAO('FollowDAO');
            $follow_count = $follow_dao->countTotalFriends($user_id, $network);
            $median = $follow_dao->getMedianFollowerCountOfFriends($user_id, $network);
            $number_under = $follow_dao->getCountOfFriendsWithFewerFollowers($user_id, $network, $my_count);
            $rank = $follow_count - $number_under +1;
            $percentile = (int)sprintf('%d', 100 * ($number_under / $follow_count));

            if ($percentile >= 50 && $my_count > $median) {
                $insight = new Insight();
                $insight->slug = $this->slug;
                $insight->date = $this->insight_date;
                $insight->instance_id = $instance->id;
                $insight->filename = basename(__FILE__, ".php");
                $insight->emphasis = Insight::EMPHASIS_MED;
                $insight->headline = $this->getVariableCopy(array(
                    '%username has it good',
                    'A closer look at %username\'s follower count',
                    '%username could lend a hand',
                ));
                $insight->text = $this->getVariableCopy(array(
                    "%username has more followers than %percentile% of the %total people %username follows. That means "
                        . "<strong>%percentoftotal</strong> of %username's friends would reach a bigger audience "
                        . "if %username retweeted them.",
                    "%username has more followers than %percentile% of the people %username follows. That means "
                        . "<strong>%percentoftotal</strong> of %username's friends would reach a bigger audience "
                        . "if %username retweeted them."
                ), array(
                    'total' => number_format($follow_count),
                    'percentoftotal' => number_format($number_under),
                    'percentile' => $percentile,
                ));
                $this->insight_dao->insertInsight($insight);
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('FollowerComparisonInsight');
