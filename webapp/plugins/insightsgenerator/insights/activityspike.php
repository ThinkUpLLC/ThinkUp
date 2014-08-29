<?php
/*
 Plugin Name: Activity Spike
 Description: Post activity spikes for the past 7, 30, and 365 days.
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/activityspike.php
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
class ActivitySpikeInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        self::generateInsightBaselines($instance, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
        $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');

        // We test for the presence of the high_fave_count_last_365_days since it's most likely to exist.
        $do365 = $insight_baseline_dao->doesInsightBaselineExistBefore('high_fave_count_last_365_days', $instance->id,
            date('Y-m-d', time() - (365*24*60*60)));

        // We can skip this query if do365 is already true.
        $do30 = $do365 || $insight_baseline_dao->doesInsightBaselineExistBefore( 'high_fave_count_last_365_days',
           $instance->id,  date('Y-m-d', time() - (30*24*60*60)));

        // We can skip this query if do30 is already true.
        $do7 = $do30 || $insight_baseline_dao->doesInsightBaselineExistBefore( 'high_fave_count_last_365_days',
           $instance->id,  date('Y-m-d', time() - (7*24*60*60)));

        $post_date = '';
        $share_verb = ($instance->network == 'twitter')?'retweeted':'reshared';
        $present_tense_share_verb = ($instance->network == 'twitter')?'retweeting':'sharing';
        foreach ($last_week_of_posts as $post) {
            $headline = null;
            if ($post->network == 'instagram') {
                $photo_dao = DAOFactory::getDAO('PhotoDAO');
                $post = $photo_dao->getPhoto($post->post_id, 'instagram');
            }
            // Only show insight for posts with activity
            if ($post->favlike_count_cache > 2 || $post->all_retweets > 2 || $post->reply_count_cache > 2) {
                if ($post_date != date('Y-m-d', strtotime($post->pub_date))) {
                    $post_date = date('Y-m-d', strtotime($post->pub_date));
                    $baselines_to_load = array(
                        'avg_fave_count_last_7_days', 'avg_fave_count_last_30_days',
                        'high_fave_count_last_7_days', 'high_fave_count_last_30_days', 'high_fave_count_last_365_days',
                        'avg_retweet_count_last_7_days', 'avg_retweet_count_last_30_days',
                        'high_retweet_count_last_7_days', 'high_retweet_count_last_30_days',
                        'high_retweet_count_last_365_days',
                        'avg_reply_count_last_7_days', 'avg_reply_count_last_30_days',
                        'high_reply_count_last_7_days','high_reply_count_last_30_days','high_reply_count_last_365_days',
                    );

                    $baselines = array();
                    foreach ($baselines_to_load as $bl_slug) {
                        $baselines[$bl_slug] =
                            $insight_baseline_dao->getInsightBaseline($bl_slug, $instance->id, $post_date);
                    }
                }

                $activities = array(
                    'reply'=>'reply_count_cache',
                    'retweet'=>'all_retweets',
                    'fave'=>'favlike_count_cache',
                );

                // first we check 365 day highs
                if ($do365) {
                    $winning_percent = 0;
                    $winning_activity = null;
                    foreach ($activities as $activity=>$object_key) {
                        if (isset($baselines['high_'.$activity.'_count_last_365_days']->value)) {
                            $base_value = $baselines['high_'.$activity.'_count_last_365_days']->value;
                            $value = $post->{$object_key};
                            $percent_change = $value / ($base_value == 0 ? 1 : $base_value);
                            if ($value > $base_value && $percent_change > $winning_percent) {
                                $winning_percent = $percent_change;
                                $winning_activity = $activity;
                            }
                        }
                    }

                    if ($winning_activity) {
                        $slug = $winning_activity.'_high_365_day_'.$post->id;
                        $emphasis = Insight::EMPHASIS_HIGH;
                        $my_insight_posts = array($post);
                        switch ($winning_activity) {
                            case 'fave':
                                $headline = "A 365-day record for "
                                    . $this->terms->getNoun('like', InsightTerms::PLURAL) . "!";
                                $insight_text = "<strong>"
                                        . number_format($post->favlike_count_cache)." people</strong> "
                                    . $this->terms->getVerb('liked')
                                    . " $this->username's ".$this->terms->getNoun('post').".";
                                break;
                            case 'reply':
                                $headline = "$this->username got <strong>" .
                                    number_format($post->reply_count_cache) . " " .
                                    $this->terms->getNoun('reply', InsightTerms::PLURAL) .
                                    "</strong> &mdash; a 365-day high!";
                                $insight_text = "Why do you think this ".$this->terms->getNoun('post').
                                    " did so well?";
                                break;
                            case 'retweet':
                                $headline = "A new 365-day record!";
                                $insight_text = "<strong>".number_format($post->all_retweets)
                                    . " people</strong> $share_verb $this->username's ".$this->terms->getNoun('post')
                                    .".";
                                break;

                        }
                    }
                }

                if ($do30 && !$headline) {
                    $winning_percent = 0;
                    $winning_activity = null;
                    foreach ($activities as $activity=>$object_key) {
                        if (isset($baselines['high_'.$activity.'_count_last_30_days']->value)) {
                            $base_value = $baselines['high_'.$activity.'_count_last_30_days']->value;
                            $value = $post->{$object_key};
                            $percent_change = $value / ($base_value == 0 ? 1 : $base_value);
                            if ($value > $base_value && $percent_change > $winning_percent) {
                                $winning_percent = $percent_change;
                                $winning_activity = $activity;
                            }
                        }
                    }

                    if ($winning_activity) {
                        $slug = $winning_activity.'_high_30_day_'.$post->id;
                        $emphasis = Insight::EMPHASIS_HIGH;
                        $my_insight_posts = array($post);
                        switch ($winning_activity) {
                            case 'fave':
                                $headline = "Highest number of "
                                    . $this->terms->getNoun('like', InsightTerms::PLURAL)
                                    . " in the past 30 days";
                                $insight_text = "<strong>". number_format($post->favlike_count_cache)
                                    . " people</strong> "
                                    . $this->terms->getVerb('liked') ." $this->username's "
                                    . $this->terms->getNoun('post').".";
                                break;
                            case 'reply':
                                $headline =  $this->username . " got <strong>" .
                                    number_format($post->reply_count_cache) . " " .
                                    $this->terms->getNoun('reply', InsightTerms::PLURAL)."</strong>";
                                $insight_text = "That's a new 30-day record for $this->username.";
                                break;
                            case 'retweet':
                                $headline = "<strong>" . number_format($post->all_retweets)
                                    . " people</strong> $share_verb $this->username";
                                $insight_text = "That's the most one of " . $this->username . "'s " .
                                    $this->terms->getNoun('post', InsightTerms::PLURAL). " has been " . $share_verb
                                    . " in the past month!";
                                break;

                        }
                    }
                }

                if ($do7 && !$headline) {
                    $winning_percent = 0;
                    $winning_activity = null;
                    foreach ($activities as $activity=>$object_key) {
                        if (isset($baselines['high_'.$activity.'_count_last_7_days']->value)) {
                            $base_value = $baselines['high_'.$activity.'_count_last_7_days']->value;
                            $value = $post->{$object_key};
                            $percent_change = $value / ($base_value == 0 ? 1 : $base_value);
                            if ($value > $base_value && $percent_change > $winning_percent) {
                                $winning_percent = $percent_change;
                                $winning_activity = $activity;
                            }
                        }
                    }

                    if ($winning_activity) {
                        $slug = $winning_activity.'_high_7_day_'.$post->id;
                        $emphasis = Insight::EMPHASIS_MED;
                        $my_insight_posts = array($post);
                        switch ($winning_activity) {
                            case 'fave':
                                $headline = $this->username.' really got some '
                                    . $this->terms->getNoun('like', InsightTerms::PLURAL);
                                $insight_text = "<strong>" . number_format($post->favlike_count_cache)
                                    . " people</strong> " . $this->terms->getVerb('liked')
                                    . " $this->username's ".$this->terms->getNoun('post').".";
                                break;
                            case 'reply':
                                $plural = $post->reply_count_cache==1?InsightTerms::SINGULAR : InsightTerms::PLURAL;
                                $headline = $this->username. " got <strong>".number_format($post->reply_count_cache)." "
                                    . $this->terms->getNoun('reply', $plural).'</strong>';
                                $insight_text = "That's a new 7-day record.";
                                break;
                            case 'retweet':
                                $headline = "<strong>".number_format($post->all_retweets)
                                    . " people</strong> $share_verb $this->username";
                                $insight_text = "That's a new 7-day record.";
                                $emphasis = Insight::EMPHASIS_MED;
                                break;

                        }
                    }
                }

                if ($do30 && !$headline) {
                    $winning_percent = 0;
                    $winning_activity = null;
                    foreach ($activities as $activity=>$object_key) {
                        if (isset($baselines['avg_'.$activity.'_count_last_30_days']->value)) {
                            $base_value = $baselines['avg_'.$activity.'_count_last_30_days']->value;
                            $value = $post->{$object_key};
                            $percent_change = $value / ($base_value == 0 ? 1 : $base_value);
                            if ($value > ($base_value*2) && $percent_change > $winning_percent) {
                                $winning_percent = $percent_change;
                                $winning_activity = $activity;
                                $winning_multiplier = floor($value/$base_value);
                            }
                        }
                    }

                    if ($winning_activity) {
                        $slug = $winning_activity.'_spike_30_day_'.$post->id;
                        $emphasis = Insight::EMPHASIS_HIGH;
                        $my_insight_posts = array($post);
                        switch ($winning_activity) {
                            case 'fave':
                                $headline = $this->username.' got '
                                    . $this->terms->getMultiplierAdverb($winning_multiplier) . ' the '
                                    . $this->terms->getNoun('like', InsightTerms::PLURAL);
                                $insight_text = "<strong>" .number_format($post->favlike_count_cache)
                                    . " people</strong> ".$this->terms->getVerb('liked')
                                    . " $this->username's ".$this->terms->getNoun('post').", which is more than "
                                    . "<strong>" . $this->terms->getMultiplierAdverb($winning_multiplier)
                                    . "</strong> $this->username's 30-day average.";
                                break;
                            case 'reply':
                                $plural = $post->reply_count_cache==1?InsightTerms::SINGULAR : InsightTerms::PLURAL;
                                $headline = $this->username . " got <strong>".number_format($post->reply_count_cache)
                                    ." ". $this->terms->getNoun('reply', $plural).'</strong>';
                                $insight_text = "That's more than <strong>"
                                     .$this->terms->getMultiplierAdverb($winning_multiplier)
                                    . "</strong> " . $this->username . "'s 30-day average.";
                                break;
                            case 'retweet':
                                $headline = "<strong>".number_format($post->all_retweets). " people</strong>"
                                    . " $share_verb " . "$this->username!";
                                $insight_text = "Seems like this one is going viral. This "
                                    . $this->terms->getNoun('post')
                                    . " got more than <strong>"
                                    . $this->terms->getMultiplierAdverb($winning_multiplier)
                                    . "</strong> $this->username's 30-day average.";
                                break;
                        }
                    }
                }

                if ($do7 && !$headline) {
                    $winning_percent = 0;
                    $winning_activity = null;
                    foreach ($activities as $activity=>$object_key) {
                        if (isset($baselines['avg_'.$activity.'_count_last_7_days']->value)) {
                            $base_value = $baselines['avg_'.$activity.'_count_last_7_days']->value;
                            $value = $post->{$object_key};
                            $percent_change = $value / ($base_value == 0 ? 1 : $base_value);
                            if ($value > ($base_value*2) && $percent_change > $winning_percent) {
                                $winning_percent = $percent_change;
                                $winning_activity = $activity;
                                $winning_multiplier = floor($value/$base_value);
                            }
                        }
                    }

                    if ($winning_activity) {
                        $slug = $winning_activity.'_spike_7_day_'.$post->id;
                        $emphasis = Insight::EMPHASIS_LOW;
                        $my_insight_posts = array($post);
                        switch ($winning_activity) {
                            case 'fave':
                                $headline = $this->username.' hit a nerve this week';
                                $insight_text = "<strong>".number_format($post->favlike_count_cache)
                                    . " people</strong> ".$this->terms->getVerb('liked') . " $this->username's "
                                    . $this->terms->getNoun('post').", more than <strong>"
                                    . $this->terms->getMultiplierAdverb($winning_multiplier)
                                    . "</strong> $this->username's 7-day average.";
                                break;
                            case 'reply':
                                $plural = $post->reply_count_cache==1?InsightTerms::SINGULAR : InsightTerms::PLURAL;
                                $headline = $this->username. " got <strong>".number_format($post->reply_count_cache)." "
                                    . $this->terms->getNoun('reply', $plural).'</strong>';
                                $insight_text = "That's more than <strong>"
                                    . $this->terms->getMultiplierAdverb($winning_multiplier)
                                    . "</strong> $this->username's 7-day average.";
                                break;
                            case 'retweet':
                                $headline = "<strong>".number_format($post->all_retweets)
                                    . " people</strong> thought $this->username "
                                    . "was worth " . $present_tense_share_verb;
                                $insight_text = "That's more than <strong>"
                                    . $this->terms->getMultiplierAdverb($winning_multiplier)
                                    . "</strong> $this->username's average over the last 7 days.";
                                break;
                        }
                    }
                }

                if (isset($slug) && isset($headline)) {
                    // Clean up previous insights for this post
                    $to_delete = array(
                        'fave_high_365','fave_high_30','fave_high_7', 'fave_spike_30','fave_spike_7',
                        'reply_high_365','reply_high_30','reply_high_7', 'reply_spike_30','reply_spike_7',
                        'retweet_high_365','retweet_high_30','retweet_high_7', 'retweet_spike_30','retweet_spike_7',
                    );
                    foreach ($to_delete as $base_slug) {
                        $delete_slug = $base_slug.'_day_'.$post->id;
                        if ($slug != $delete_slug) {
                            $this->insight_dao->deleteInsight($delete_slug, $instance->id, $post_date);
                        }
                    }

                    $my_insight = new Insight();
                    $my_insight->slug = $slug;
                    $my_insight->instance_id = $instance->id;
                    $my_insight->date = $post_date;
                    $my_insight->headline = $headline;
                    $my_insight->text = $insight_text;
                    $my_insight->header_image = '';
                    $my_insight->filename = basename(__FILE__, ".php");
                    $my_insight->emphasis = $emphasis;
                    if (isset ($my_insight_posts)) {
                        $my_insight->setPosts($my_insight_posts);
                    }
                    $this->insight_dao->insertInsight($my_insight);
                }
                $headline = null;
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

        for ($days_ago=0; $days_ago<$number_days; $days_ago++) {
            $since_date = date("Y-m-d", strtotime("-".$days_ago." day"));
            $username = $instance->network_username;
            $network = $instance->network;

            foreach (array(7,30,365) as $days) {
                if ($post_dao->doesUserHavePostsWithFavesSinceDate($username, $network, $days, $since_date)) {
                    $average_fave_count = $post_dao->getAverageFaveCount($username, $network, $days, $since_date);
                    if ($average_fave_count != null ) {
                        $insight_baseline_dao->insertInsightBaseline('avg_fave_count_last_'.$days.'_days',$instance->id,
                            $average_fave_count, $since_date);
                        $this->logger->logSuccess("Averaged $average_fave_count faves in the $days days before ".
                            $since_date, __METHOD__.','.__LINE__);
                    }
                }

                if ($post_dao->doesUserHavePostsWithRepliesSinceDate($username, $network, $days, $since_date)) {
                    $average_reply_count = $post_dao->getAverageReplyCount($username, $network, $days, $since_date);
                    if ($average_reply_count != null ) {
                        $insight_baseline_dao->insertInsightBaseline('avg_reply_count_last_'.$days.'_days',
                            $instance->id, $average_reply_count, $since_date);
                        $this->logger->logSuccess("Averaged $average_reply_count replies in the $days days before ".
                            $since_date, __METHOD__.','.__LINE__);
                    }
                }

                if ($post_dao->doesUserHavePostsWithRetweetsSinceDate($username, $network, $days, $since_date)) {
                    $average_retweet_count = $post_dao->getAverageRetweetCount($username,$network, $days, $since_date);
                    if ($average_retweet_count != null ) {
                        $insight_baseline_dao->insertInsightBaseline('avg_retweet_count_last_'.$days.'_days',
                            $instance->id, $average_retweet_count, $since_date);
                        $this->logger->logSuccess("Averaged $average_retweet_count retweets in the $days days before ".
                            $since_date, __METHOD__.','.__LINE__);
                    }
                }

                if ($days == 365) {
                    continue;
                }

                $high_fave_count = $post_dao->getAllPostsByUsernameOrderedBy($instance->network_username,
                    $network=$instance->network, 1, 'favlike_count_cache', $days, $iterator = false, $is_public = false,
                    $since=$since_date);
                if ($high_fave_count != null ) {
                    $high_fave_count = $high_fave_count[0]->favlike_count_cache;
                    $insight_baseline_dao->insertInsightBaseline('high_fave_count_last_'.$days.'_days', $instance->id,
                        $high_fave_count, $since_date);
                    $this->logger->logSuccess("High of $high_fave_count faves in the $days days before ".
                        $since_date, __METHOD__.','.__LINE__);
                }

                $high_reply_count = $post_dao->getAllPostsByUsernameOrderedBy($username, $network, 1,
                    'reply_count_cache', $days, $iterator = false, $is_public = false, $since_date);
                if ($high_reply_count != null ) {
                    $high_reply_count = $high_reply_count[0]->reply_count_cache;
                    $insight_baseline_dao->insertInsightBaseline('high_reply_count_last_'.$days.'_days', $instance->id,
                        $high_reply_count, $since_date);
                    $this->logger->logSuccess("High of $high_reply_count replies in the $days days before ".
                        $since_date, __METHOD__.','.__LINE__);
                }

                $high_retweet_count = $post_dao->getAllPostsByUsernameOrderedBy($username, $network, 1, 'retweets',
                    $days, $iterator = false, $is_public = false, $since_date);
                if ($high_retweet_count != null ) {
                    $high_retweet_count = $high_retweet_count[0]->all_retweets;
                    $insight_baseline_dao->insertInsightBaseline('high_retweet_count_last', $instance->id,
                        $high_retweet_count, $since_date);
                    $this->logger->logSuccess("High of $high_retweet_count retweets in the $days days before ".
                        $since_date, __METHOD__.','.__LINE__);
                }
            }
        }
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('ActivitySpikeInsight');
