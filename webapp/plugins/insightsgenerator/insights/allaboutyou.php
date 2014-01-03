<?php
/*
 Plugin Name: All About You
 Description: How often you referred to yourself ("I", "me", "myself", "my") in the past week.
 When: Sundays
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/allaboutyou.php
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

class AllAboutYouInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
        $insight_text = '';

        if (self::shouldGenerateInsight('all_about_you', $instance, $insight_date='today',
        $regenerate_existing_insight=false, $day_of_week=0, count($last_week_of_posts))) {
            $text = '';
            $count = 0;
            foreach ($last_week_of_posts as $post) {
                $count += self::countFirstPersonReferences($post->post_text);
            }
            if ($count > 1) {
                $headline = "$this->username's ".$this->terms->getNoun('post', (count($last_week_of_posts) > 1))
                ." contained the words \"I\", \"me\", \"my\", \"mine\", or \"myself\" "
                ."<strong>".$count." times</strong> in the last week.";

                $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
                $insight_baseline_dao->insertInsightBaseline("all_about_you", $instance->id, $count,
                $this->insight_date);

                //Week over week comparison
                //Get insight baseline from last Sunday
                $last_sunday = date('Y-m-d', strtotime('-7 day'));
                $last_sunday_insight_baseline = $insight_baseline_dao->getInsightBaseline("all_about_you",
                $instance->id, $last_sunday);
                if (isset($last_sunday_insight_baseline) ) {
                    //compare it to this Sunday's number, and add a sentence comparing it.
                    if ($last_sunday_insight_baseline->value > $count ) {
                        $difference = $last_sunday_insight_baseline->value - $count;
                        $insight_text = "That's $difference fewer time".($difference>1?"s":"")." than the prior week.";
                    } elseif ($last_sunday_insight_baseline->value < $count ) {
                        $difference = $count - $last_sunday_insight_baseline->value;
                        $insight_text = "That's $difference more time".($difference>1?"s":"")." than the prior week.";
                    } else {
                        $insight_text .= ".";
                    }
                }
                $this->insight_dao->insertInsightDeprecated("all_about_you", $instance->id, $this->insight_date,
                $headline, $insight_text, basename(__FILE__, ".php"), Insight::EMPHASIS_LOW);
            }
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

    /**
     * Count the number of times "I", "me", "my", "myself" or "mine" appears in text.
     * @param str $text
     * @return int Total occurences of "I", "me", "my", "myself" or "mine" in $text
     */
    public static function countFirstPersonReferences($text) {
        $count = 0;
        $matches = array();
        $url_free_text = preg_replace('!https?://[\S]+!', ' ', $text);
        $depunctuated_text = " ". preg_replace('/[^a-z0-9]+/i', ' ', $url_free_text) ." ";

        //echo $depunctuated_text;
        preg_match_all("/\bI\b/i", $depunctuated_text, $matches);
        //print_r($matches[0]);
        $count += sizeof($matches[0]);

        preg_match_all("/\bme\b/i", $depunctuated_text, $matches);
        //print_r($matches[0]);
        $count += sizeof($matches[0]);

        preg_match_all("/\bmyself\b/i", $depunctuated_text, $matches);
        //print_r($matches[0]);
        $count += sizeof($matches[0]);

        preg_match_all("/\bmy\b/i", $depunctuated_text, $matches);
        //print_r($matches[0]);
        $count += sizeof($matches[0]);

        preg_match_all("/\bmine\b/i", $depunctuated_text, $matches);
        //print_r($matches[0]);
        $count += sizeof($matches[0]);

        return $count;
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('AllAboutYouInsight');
