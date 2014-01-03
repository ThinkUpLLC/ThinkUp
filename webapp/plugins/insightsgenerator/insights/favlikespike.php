<?php
/*
 Plugin Name: Favorite or Like Spike
 Description: Favorite/like spikes and high insights for the past 7, 30, and 365 days.
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/favlikespike.php
 *
 * Copyright (c) 2013 Gina Trapani
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
 * @copyright 2013 Gina Trapani
 * @author Gina Trapani <ginatrapani [at] gmail [dot] com>
 */
class FaveLikeSpikeInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        self::generateInsightBaselines($instance, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
        $headline = '';

        $simplified_post_date = "";
        foreach ($last_week_of_posts as $post) {
            if ($post->favlike_count_cache > 2) { //Only show insight for more than 2 likes
                // First get spike/high 7/30/365 day baselines
                if ($simplified_post_date != date('Y-m-d', strtotime($post->pub_date))) {
                    $simplified_post_date = date('Y-m-d', strtotime($post->pub_date));

                    $average_fave_count_7_days =
                    $insight_baseline_dao->getInsightBaseline('avg_fave_count_last_7_days', $instance->id,
                    $simplified_post_date);

                    $average_fave_count_30_days =
                    $insight_baseline_dao->getInsightBaseline('avg_fave_count_last_30_days', $instance->id,
                    $simplified_post_date);

                    $high_fave_count_7_days =
                    $insight_baseline_dao->getInsightBaseline('high_fave_count_last_7_days', $instance->id,
                    $simplified_post_date);

                    $high_fave_count_30_days =
                    $insight_baseline_dao->getInsightBaseline('high_fave_count_last_30_days', $instance->id,
                    $simplified_post_date);

                    $high_fave_count_365_days =
                    $insight_baseline_dao->getInsightBaseline('high_fave_count_last_365_days', $instance->id,
                    $simplified_post_date);
                }
                // Next compare post favlike counts to baselines and store insights where there's a spike or high
                if (isset($high_fave_count_365_days->value)
                && $post->favlike_count_cache >= $high_fave_count_365_days->value
                && isset($it_is_not_launch_day)) {
                    //TODO: Stop using the cached dashboard data and generate fresh here
                    $hot_posts_data = $this->insight_dao->getPreCachedInsightData('PostMySQLDAO::getHotPosts',
                    $instance->id, $simplified_post_date);

                    if (isset($hot_posts_data)) {

                        $slug = 'fave_high_365_day_'.$post->id;
                        $headline = "That's a 365-day record for " . $this->terms->getNoun('like', InsightTerms::PLURAL)
                            . "!";
                        $insight_text = "<strong>".
                        number_format($post->favlike_count_cache)." people</strong> ".$this->terms->getVerb('liked')
                        ." $this->username's ".$this->terms->getNoun('post').".";
                        $emphasis = Insight::EMPHASIS_HIGH;
                        $posts = array($post, $hot_posts_data);

                        $this->insight_dao->deleteInsight('fave_high_30_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                        $this->insight_dao->deleteInsight('fave_high_7_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                        $this->insight_dao->deleteInsight('fave_spike_30_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                        $this->insight_dao->deleteInsight('fave_spike_7_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                    }
                } elseif (isset($high_fave_count_30_days->value)
                && $post->favlike_count_cache >= $high_fave_count_30_days->value
                && isset($it_is_not_launch_day)) {
                    //TODO: Stop using the cached dashboard data and generate fresh here
                    $hot_posts_data = $this->insight_dao->getPreCachedInsightData('PostMySQLDAO::getHotPosts',
                    $instance->id, $simplified_post_date);

                    if (isset($hot_posts_data)) {

                        $slug = 'fave_high_30_day_'.$post->id;
                        $headline = "That's the highest number of " . $this->terms->getNoun('like', InsightTerms::PLURAL)
                        . $this->username . "'s gotten in the past 30 days.";
                        $insight_text = "<strong>". number_format($post->favlike_count_cache)." people</strong> ".
                        $this->terms->getVerb('liked') ." $this->username's ".$this->terms->getNoun('post').".";
                        $emphasis = Insight::EMPHASIS_HIGH;
                        $posts = array($post, $hot_posts_data);

                        $this->insight_dao->deleteInsight('fave_high_7_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                        $this->insight_dao->deleteInsight('fave_spike_30_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                        $this->insight_dao->deleteInsight('fave_spike_7_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                    }
                } elseif (isset($high_fave_count_7_days->value)
                && $post->favlike_count_cache >= $high_fave_count_7_days->value) {
                    //TODO: Stop using the cached dashboard data and generate fresh here
                    $hot_posts_data = $this->insight_dao->getPreCachedInsightData('PostMySQLDAO::getHotPosts',
                    $instance->id, $simplified_post_date);

                    if (isset($hot_posts_data)) {
                        $slug = 'fave_high_7_day_'.$post->id;
                        $headline = 'This one really got some ' .$this->terms->getNoun('like', InsightTerms::PLURAL) .'.';
                        $insight_text = "<strong>" . number_format($post->favlike_count_cache)." people</strong> "
                        . $this->terms->getVerb('liked') . " $this->username's ".$this->terms->getNoun('post').".";
                        $emphasis = Insight::EMPHASIS_HIGH;
                        $posts = array($post, $hot_posts_data);

                        $this->insight_dao->deleteInsight('fave_high_30_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                        $this->insight_dao->deleteInsight('fave_spike_30_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                        $this->insight_dao->deleteInsight('fave_spike_7_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                    }
                } elseif (isset($average_fave_count_30_days->value)
                && $post->favlike_count_cache > ($average_fave_count_30_days->value*2)) {
                    //TODO: Stop using the cached dashboard data and generate fresh here
                    $hot_posts_data = $this->insight_dao->getPreCachedInsightData('PostMySQLDAO::getHotPosts',
                    $instance->id, $simplified_post_date);

                    if (isset($hot_posts_data)) {
                        $multiplier = floor($post->favlike_count_cache/$average_fave_count_30_days->value);

                        $slug = 'fave_spike_30_day_'.$post->id;
                        $headline = 'This ' . $this->terms->getNoun('post') . ' got '
                            . $this->terms->getMultiplierAdverb($multiplier) . ' the '
                            . $this->terms->getNoun('like', InsightTerms::PLURAL) . ' for ' . $this->username . '.';
                        $insight_text = "<strong>" .number_format($post->favlike_count_cache)
                        ." people</strong> ".$this->terms->getVerb('liked')
                        ." $this->username's ".$this->terms->getNoun('post').", which is more than <strong>".$multiplier
                        ."x</strong> $this->username's 30-day average.";
                        $emphasis = Insight::EMPHASIS_LOW;
                        $posts = array($post, $hot_posts_data);

                        $this->insight_dao->deleteInsight('fave_high_30_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                        $this->insight_dao->deleteInsight('fave_high_7_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                        $this->insight_dao->deleteInsight('fave_spike_7_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                    }
                } elseif (isset($average_fave_count_7_days->value)
                && $post->favlike_count_cache > ($average_fave_count_7_days->value*2)) {
                    //TODO: Stop using the cached dashboard data and generate fresh here
                    $hot_posts_data = $this->insight_dao->getPreCachedInsightData('PostMySQLDAO::getHotPosts',
                    $instance->id, $simplified_post_date);

                    if (isset($hot_posts_data)) {
                        $multiplier = floor($post->favlike_count_cache/$average_fave_count_7_days->value);

                        $slug = 'fave_spike_7_day_'.$post->id;
                        $headline = 'This one hit a nerve this week.';
                        $insight_text = "<strong>".number_format($post->favlike_count_cache)
                        ." people</strong> ".$this->terms->getVerb('liked') . " $this->username's ".
                        $this->terms->getNoun('post').", more than <strong>" .$multiplier
                        ."x</strong> $this->username's 7-day average.";
                        $emphasis = Insight::EMPHASIS_LOW;
                        $posts = array($post, $hot_posts_data);

                        $this->insight_dao->deleteInsight('fave_high_30_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                        $this->insight_dao->deleteInsight('fave_high_7_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                        $this->insight_dao->deleteInsight('fave_spike_30_day_'.$post->id, $instance->id,
                        $simplified_post_date);
                    }
                }

                if (isset($slug) && isset($headline)) {
                    //Instantiate the Insight object
                    $my_insight = new Insight();

                    //REQUIRED: Set the insight's required attributes
                    $my_insight->slug = $slug; //slug to label this insight's content
                    $my_insight->instance_id = $instance->id;
                    $my_insight->date = $simplified_post_date; //date is often this or $simplified_post_date
                    $my_insight->headline = $headline; // or just set a string like 'Ohai';
                    $my_insight->text = $insight_text; // or just set a strong like "Greetings humans";
                    $my_insight->header_image = '';
                    $my_insight->filename = basename(__FILE__, ".php"); //Same for every insight, must be set exactly this way
                    $my_insight->emphasis = $emphasis; //Set emphasis optionally, default is Insight::EMPHASIS_LOW
                    if (isset ($posts)) {
                        $my_insight->setPosts($posts);
                    }

                    $this->insight_dao->insertInsight($my_insight);
                }

                //reset vars
                $headline = null;
                $insight_slug = null;
                $insight_text = null;
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
    /**
     * Calculate and store insight baselines for a specified number of days.
     * @param Instance $instance
     * @param int $number_days Number of days to backfill
     * @return void
     */
    private function generateInsightBaselines($instance, $number_days=3) {
        $post_dao = DAOFactory::getDAO('PostDAO');
        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');

        $days_ago = 0;
        // Generate baseline post insights for the last 7 days

        while ($days_ago < $number_days) {
            $since_date = date("Y-m-d", strtotime("-".$days_ago." day"));

            if ($post_dao->doesUserHavePostsWithFavesSinceDate($instance->network_username, $instance->network, 7,
            $since_date)) {
                //Save average faves over past 7 days
                $average_fave_count_7_days = null;
                $average_fave_count_7_days = $post_dao->getAverageFaveCount($instance->network_username,
                $instance->network, 7, $since_date);
                if ($average_fave_count_7_days != null ) {
                    $insight_baseline_dao->insertInsightBaseline('avg_fave_count_last_7_days', $instance->id,
                    $average_fave_count_7_days, $since_date);
                    $this->logger->logSuccess("Averaged $average_fave_count_7_days faves in the 7 days before ".
                    $since_date, __METHOD__.','.__LINE__);
                }

                //Save fave high for last 7 days
                $high_fave_count_7_days = $post_dao->getAllPostsByUsernameOrderedBy($instance->network_username,
                $network=$instance->network, 1, 'favlike_count_cache', 7, $iterator = false, $is_public = false,
                $since=$since_date);
                if ($high_fave_count_7_days != null ) {
                    $high_fave_count_7_days = $high_fave_count_7_days[0]->favlike_count_cache;
                    $insight_baseline_dao->insertInsightBaseline('high_fave_count_last_7_days', $instance->id,
                    $high_fave_count_7_days, $since_date);
                    $this->logger->logSuccess("High of $high_fave_count_7_days faves in the 7 days before ".
                    $since_date, __METHOD__.','.__LINE__);
                }
            }

            if ($post_dao->doesUserHavePostsWithFavesSinceDate($instance->network_username, $instance->network, 30,
            $since_date)) {
                //Save average faves over past 30 days
                $average_fave_count_30_days = null;
                $average_fave_count_30_days = $post_dao->getAverageFaveCount($instance->network_username,
                $instance->network, 30, $since_date);
                if ($average_fave_count_30_days != null ) {
                    $insight_baseline_dao->insertInsightBaseline('avg_fave_count_last_30_days', $instance->id,
                    $average_fave_count_30_days, $since_date);
                    $this->logger->logSuccess("Averaged $average_fave_count_30_days faves in the 30 days before ".
                    $since_date, __METHOD__.','.__LINE__);
                }

                //Save fave high for last 30 days
                $high_fave_count_30_days = $post_dao->getAllPostsByUsernameOrderedBy($instance->network_username,
                $network=$instance->network, 1, 'favlike_count_cache', 30, $iterator = false, $is_public = false,
                $since=$since_date);
                if ($high_fave_count_30_days != null ) {
                    $high_fave_count_30_days = $high_fave_count_30_days[0]->favlike_count_cache;
                    $insight_baseline_dao->insertInsightBaseline('high_fave_count_last_30_days', $instance->id,
                    $high_fave_count_30_days, $since_date);
                    $this->logger->logSuccess("High of $high_fave_count_30_days faves in the 30 days before ".
                    $since_date, __METHOD__.','.__LINE__);
                }
            }

            if ($post_dao->doesUserHavePostsWithFavesSinceDate($instance->network_username, $instance->network, 365,
            $since_date)) {
                //Save fave high for last 365 days
                $high_fave_count_365_days = $post_dao->getAllPostsByUsernameOrderedBy($instance->network_username,
                $network=$instance->network, 1, 'favlike_count_cache', 365, $iterator = false, $is_public = false,
                $since=$since_date);
                if ($high_fave_count_365_days != null ) {
                    $high_fave_count_365_days = $high_fave_count_365_days[0]->favlike_count_cache;
                    $insight_baseline_dao->insertInsightBaseline('high_fave_count_last_365_days', $instance->id,
                    $high_fave_count_365_days, $since_date);
                    $this->logger->logSuccess("High of $high_fave_count_365_days faves in the 365 days before ".
                    $since_date, __METHOD__.','.__LINE__);
                }
            }
            $days_ago++;
        }
    }

}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('FaveLikeSpikeInsight');
