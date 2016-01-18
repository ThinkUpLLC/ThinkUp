<?php
/*
 Plugin Name: Gender Analysis(End of Year)
 Description: Gender breakdown of commentors and likers on your Facebook status updates in the last year
 When: December 7
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/eoygenderanalysis.php
 *
 * Copyright (c) 2012-2016 Gina Trapani
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
 * Copyright (c) 2014-2016 Adam Pash
 *
 * @author Chris Moyer chri@inarow.net
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014-2016 Chris Moyer
 */

class EOYGenderAnalysisInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug for this insight
     **/
    var $slug = 'eoy_gender_analysis';
    /**
     * Date to run this insight
     **/
    var $run_date = '12-07';
    //staging
    //var $run_date = '12-03';

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        if ($instance->network != 'facebook') {
            $this->logger->logInfo("Done generating insight (Skipped non-Facebook)", __METHOD__.','.__LINE__);
            return;
        }

        $year = date('Y');
        $should_generate_insight = self::shouldGenerateEndOfYearAnnualInsight(
            $this->slug, $instance, $insight_date = "$year-$this->run_date",
            $regenerate = false, $day_of_year = $this->run_date
        );

        if (!$should_generate_insight) {
            $this->logger->logInfo("Done generating insight (Skipped)", __METHOD__.','.__LINE__);
            return;
        }

        $this->logger->logInfo("Should generate", __METHOD__.','.__LINE__);
        $post_dao = DAOFactory::getDAO('PostDAO');
        $fav_post_dao = DAOFactory::getDAO('FavoritePostDAO');
        $post_iterator = $post_dao->getThisYearOfPostsIterator($instance->network_user_id, $instance->network);
        $males = 0;
        $females = 0;
        foreach ($post_iterator as $post) {
            $faves = $fav_post_dao->getGenderOfFavoriters($post->post_id, $post->network);
            $females += empty($faves['female_likes_count']) ? 0 : intval($faves['female_likes_count']);
            $males += empty($faves['male_likes_count']) ? 0 : intval($faves['male_likes_count']);
            $comments = $fav_post_dao->getGenderOfCommenters($post->post_id, $post->network);
            $females += empty($comments['female_comment_count']) ? 0 : intval($comments['female_comment_count']);
            $males += empty($comments['male_comment_count']) ? 0 : intval($comments['male_comment_count']);
        }

        if (($females + $males) < 10) {
            $this->logger->logInfo("Done generating insight (Not enough data)", __METHOD__.','.__LINE__);
            return;
        }

        if ($males == $females) {
            $who = 'women and men equally';
        } elseif ($females > $males) {
            $who = 'women';
        } else {
            $who = 'men';
        }

        $earliest_pub_date = $post_dao->getEarliestCapturedPostPubDate($instance);
        $qualified_year = "";
        if (date('Y', strtotime($earliest_pub_date)) == date('Y')) {
            if (date('n', strtotime($earliest_pub_date)) > 1 ) { //not January
                //Earliest post was this year; figure out what month we have data since this year
                $since = date('F', strtotime($earliest_pub_date));
                $qualified_year = " (at least since ".$since.")";
            }
        }

        $female_text = $females == 1 ? 'like or comment' : 'likes and comments';
        $text = 'This year, <strong>'.number_format($females).' '.$female_text.'</strong> on '
            .$this->username.'\'s status updates were '
            .'by people who identify as female, compared to <strong>'.number_format($males)
            .'</strong> by people who identify as male'. $qualified_year.'.';

        $insight = new Insight();
        $insight->instance_id = $instance->id;
        $insight->slug = $this->slug;
        $insight->date = "$year-$this->run_date";
        $insight->headline = $this->username."'s status updates resonated with $who in $year";
        $insight->text = $text;
        $insight->filename = basename(__FILE__, ".php");
        $insight->emphasis = Insight::EMPHASIS_HIGH;
        $insight->header_image = $user->avatar;
        $insight->setPieChart(array('gender' => 'value','female' => $females, 'male' => $males));
        $this->insight_dao->insertInsight($insight);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('EOYGenderAnalysisInsight');
