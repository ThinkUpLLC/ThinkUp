<?php
/*
 Plugin Name: All About You
 Description: How often you referred to yourself ("I", "me", "myself", "my") in the past week.
 When: Sundays for Twitter, Wednesdays otherwise
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/allaboutyou.php
 *
 * Copyright (c) 2012-2014 Gina Trapani
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
 * @copyright 2012-2014 Gina Trapani
 */

class AllAboutYouInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
        $insight_text = '';

        if ($instance->network == 'twitter') {
            $day_of_week = 0;
        } else {
            $day_of_week = 3;
        }
        $should_generate_insight = self::shouldGenerateWeeklyInsight( 'all_about_you', $instance, $insight_date='today',
            $regenerate_existing_insight=false, $day_of_week = $day_of_week, count($last_week_of_posts));
        if ($should_generate_insight) {
            $text = '';
            $count = 0;
            foreach ($last_week_of_posts as $post) {
                $count += self::hasFirstPersonReferences($post->post_text) ? 1 : 0;
            }

            if ($count > 1) {
                $headline = $this->getVariableCopy(array(
                    '%username is getting personal.',
                    'It\'s getting personal.',
                    'But enough about me&hellip;',
                    'Self-reflection is powerful stuff.',
                    'Speaking from experience&hellip;',
                    'Sometimes %network is a first-person story.',
                    'It\'s just me, myself and I.',
                ), array('network' => ucfirst($instance->network)));
                $percent = round($count / count($last_week_of_posts) * 100);
                $plural = count($last_week_of_posts) > 1;
                $insight_text = "<strong>$percent%</strong> of $this->username's ".$this->terms->getNoun('post', $plural)
                    ." contained the words &ldquo;I&rdquo;, &ldquo;me&rdquo;, &ldquo;my&rdquo;, &ldquo;mine&rdquo;,"
                    ." or &ldquo;myself&rdquo; in the last week.";

                $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
                $insight_baseline_dao->insertInsightBaseline("all_about_you_percent", $instance->id, $percent,
                    $this->insight_date);

                //Week over week comparison
                //Get insight baseline from last Sunday
                $last_sunday = date('Y-m-d', strtotime('-7 day'));
                $last_sunday_insight_baseline = $insight_baseline_dao->getInsightBaseline("all_about_you_percent",
                    $instance->id, $last_sunday);
                if (isset($last_sunday_insight_baseline) ) {
                    //compare it to this Sunday's number, and add a sentence comparing it.
                    $difference = abs($last_sunday_insight_baseline->value - $percent);
                    if ($last_sunday_insight_baseline->value == $percent ) {
                        $insight_text .= " So consistent: that's the same amount as the previous week.";
                    }
                    else {
                        if ($last_sunday_insight_baseline->value > $percent ) {
                            $comparison = 'down';
                        } else {
                            $comparison = 'up';
                        }
                        $insight_text .= " That's $comparison $difference percentage point".($difference>1?"s":"")
                            ." from the previous week.";
                    }
                }

                $my_insight = new Insight();

                $my_insight->slug = 'all_about_you'; //slug to label this insight's content
                $my_insight->instance_id = $instance->id;
                $my_insight->date = $this->insight_date; //date is often this or $simplified_post_date
                $my_insight->headline = $headline; // or just set a string like 'Ohai';
                $my_insight->text = $insight_text; // or just set a strong like "Greetings humans";
                $my_insight->header_image = $header_image;
                $my_insight->filename = basename(__FILE__, ".php"); //Same for every insight, must be set exactly this way
                $my_insight->emphasis = Insight::EMPHASIS_LOW; //Set emphasis optionally, default is Insight::EMPHASIS_LOW

                $this->insight_dao->insertInsight($my_insight);
            }
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

    /**
     * Determine if "I", "me", "my", "myself" or "mine" appear in text.
     * @param str $text
     * @return bool Does "I", "me", "my", "myself" or "mine" appear in $text
     */
    public static function hasFirstPersonReferences($text) {
        $count = 0;
        $matches = array();
        $url_free_text = preg_replace('!https?://[\S]+!', ' ', $text);
        $depunctuated_text = " ". preg_replace('/[^a-z0-9]+/i', ' ', $url_free_text) ." ";

        foreach (array("/\bI\b/i","/\bme\b/i","/\bmyself\b/i","/\bmy\b/i","/\bmine\b/i") as $pattern) {
            if (preg_match($pattern, $depunctuated_text, $matches)) {
                return true;
            }
        }


        return false;
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('AllAboutYouInsight');
