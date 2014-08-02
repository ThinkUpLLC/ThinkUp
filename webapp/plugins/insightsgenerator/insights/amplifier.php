<?php
/*
 Plugin Name: Amplifier
 Description: How many more users a message reached due to your reshare or retweet; the top total from yesterday.
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/Amplifier.pho
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
 */

class AmplifierInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $filename = basename(__FILE__, ".php");
        $insight_text = '';

        //Find out if insight already exists
        $should_generate_insight = self::shouldGenerateInsight('top_amplifier', $instance, date('Y-m-d'));

        if ($should_generate_insight) { //insight does not exist
            //Get yesterday's retweets
            $yesterdays_retweets = array();
            $simplified_date_yesterday = date('Y-m-d', strtotime('-1 day'));
            foreach ($last_week_of_posts as $post) {
                if ($post->in_retweet_of_post_id != null && $post->in_rt_of_user_id != null) {
                    $simplified_post_date = date('Y-m-d', strtotime($post->pub_date));
                    if ( $simplified_date_yesterday == $simplified_post_date) {
                        $yesterdays_retweets[] = $post;
                    }
                }
            }

            $largest_added_audience = 0;
            $insight_retweeted_user = null;
            $insight_retweet = null;

            //Get top amplifier from yesterday
            foreach ($yesterdays_retweets as $post) {
                if (!isset($user_dao)) {
                    $user_dao = DAOFactory::getDAO('UserDAO');
                }
                $retweeted_user = $user_dao->getDetails($post->in_rt_of_user_id, $post->network);
                //if user exists and has fewer followers than instance user compare to current top
                if (isset($retweeted_user) && $retweeted_user->follower_count < $user->follower_count) {
                    $added_audience = ($user->follower_count - $retweeted_user->follower_count);
                    if ($added_audience > $largest_added_audience) {
                        $largest_added_audience = $added_audience;
                        $insight_retweeted_user = $retweeted_user;
                        $insight_retweet = $post;
                    }
                }
            }
            //If there's a top amplifier from yesterday, insert the insight
            if ( $largest_added_audience > 0 && isset($insight_retweeted_user) && isset($insight_retweet) ) {
                $multiplier = floor($user->follower_count / $insight_retweeted_user->follower_count);
                if ($multiplier > 1 && (TimeHelper::getTime() / 10) % 2 == 1) {
                    $largest_added_audience = number_format($multiplier).'x';
                }

                $retweeted_username = $insight_retweeted_user->username;
                if ($instance->network == 'twitter') {
                    $retweeted_username = '@'.$retweeted_username;
                }
                $headline = $this->getVariableCopy(array(
                    $insight_retweeted_user->full_name." can thank %username for %added more people seeing this %post.",
                    "%added more people saw %repostedee's %post thanks to %username.",
                    '%username boosted '.$insight_retweeted_user->full_name.'\'s %post to %added more people.'
                ), array('repostedee' => $retweeted_username, 'added' => $largest_added_audience));

                $my_insight = new Insight();

                $my_insight->instance_id = $instance->id;
                $my_insight->slug = 'top_amplifier'; //slug to label this insight's content
                $my_insight->date = date('Y-m-d'); //date of the data this insight applies to
                $my_insight->headline = $headline; // or just set a string like 'Ohai';
                $my_insight->text = $insight_text; // or just set a strong like "Greetings humans";
                $my_insight->header_image = $insight_retweeted_user->avatar;
                $my_insight->emphasis = Insight::EMPHASIS_LOW;
                $my_insight->filename = basename(__FILE__, ".php");
                $my_insight->setPosts(array($insight_retweet));
                $my_insight->setPeople(array($insight_retweeted_user));
                $this->insight_dao->insertInsight($my_insight);
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('AmplifierInsight');
