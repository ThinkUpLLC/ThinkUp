<?php
/*
 Plugin Name: Grammies 2014
 Description: How often you mentioned the Grammies or one of the Grammy artists last week.
 When: Monday
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/grammies2014.php
 *
 * Copyright (c) 2012-2014 Gina Trapani, Anil Dash
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (https://thinkup.com).
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
 * @copyright 2012-2014 Gina Trapani, Anil Dash
 * @author Anil Dash <anil[at]thinkup[dot]com>
 */

class Grammies2014Insight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
        $insight_text = '';
        $header_image = '';

        if (self::shouldGenerateInsight('grammies_2014', $instance, $insight_date='today',
        $regenerate_existing_insight=false, $day_of_week=1, count($last_week_of_posts))) {
            $text = '';
            $artist_count = 0;
            $event_count = 0;
            foreach ($last_week_of_posts as $post) {
                $artist_count += self::countGrammy2014ArtistReferences($post->post_text);
            }
            $this->logger->logInfo("there are $artist_count Grammy artist mentions", __METHOD__.','.__LINE__);
            foreach ($last_week_of_posts as $post) {
                $event_count += self::countGrammyReferences($post->post_text);
            }
            $this->logger->logInfo("there are $event_count Grammy event mentions", __METHOD__.','.__LINE__);

            if ($artist_count > 0 | $event_count > 0) {
                $headline = "Always a good idea to jump into the Grammy conversation.";

                $insight_text = "$this->username mentioned ";

                if ($event_count > 0) {

                    $this->logger->logInfo("there are event mentions", __METHOD__.','.__LINE__);

                    $insight_text .= "the Grammies ";
                    if ($event_count > 1) {
                        $this->logger->logInfo("there is more than one event mention", __METHOD__.','.__LINE__);
                        $insight_text .= "$event_count times ";
                    }

                    if ($artist_count > 1) {
                        $this->logger->logInfo("there is more than one artist mention", __METHOD__.','.__LINE__);
                        $insight_text .= "and talked about a couple of artists, too.";
                    }

                } elseif ($event_count == 0) {

                    $this->logger->logInfo("there are no event mentions", __METHOD__.','.__LINE__);

                    if ($artist_count == 1) {
                        $this->logger->logInfo("there is one artist mention", __METHOD__.','.__LINE__);
                        $insight_text .= "one of this year's acts, which is always good for a response.";
                    } elseif ($artist_count > 1) {
                        $this->logger->logInfo("there is more than one artist mention", __METHOD__.','.__LINE__);
                        $insight_text .= "some of this year's biggest Grammy acts.";
                    }
                }

                $count = $artist_count + $event_count;

                $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
                $insight_baseline_dao->insertInsightBaseline("grammies_2014", $instance->id, $count,
                $this->insight_date); // just in case we do this insight again next year

                $my_insight = new Insight();

                $my_insight->slug = 'grammies_2014'; //slug to label this insight's content
                $my_insight->instance_id = $instance->id;
                $my_insight->date = $this->insight_date; //date is often this or $simplified_post_date
                $my_insight->headline = $headline; // or just set a string like 'Ohai';
                $my_insight->text = $insight_text; // or just set a strong like "Greetings humans";
                $my_insight->header_image = $header_image;
                $my_insight->filename = basename(__FILE__, ".php"); //Same for every insight, must be set exactly this way
                $my_insight->emphasis = Insight::EMPHASIS_HIGH; //Set emphasis optionally, default is Insight::EMPHASIS_LOW

                if ($count) {
                    $this->insight_dao->insertInsight($my_insight);
                }
            }
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

    /**
     * Count the number of times "Grammy" or "Grammies" appears in text.
     * @param str $text
     * @return int Total occurences of the event names in $text
     */
    public static function countGrammyReferences($text) {
        $count = 0;
        $matches = array();
        $url_free_text = preg_replace('!https?://[\S]+!', ' ', $text);
        $depunctuated_text = " ". preg_replace('/[^a-z0-9]+/i', ' ', $url_free_text) ." ";

        //echo $depunctuated_text;
        preg_match_all("/\bGrammy\b/i", $depunctuated_text, $matches);
        //print_r($matches[0]);
        $count += sizeof($matches[0]);

        //echo $depunctuated_text;
        preg_match_all("/\bGrammies\b/i", $depunctuated_text, $matches);
        //print_r($matches[0]);
        $count += sizeof($matches[0]);

        return $count;
    }

    /**
     * Count the number of times the name of Grammy artists appears in text.
     * @param str $text
     * @return int Total occurences of artist names in $text
     */
    public static function countGrammy2014ArtistReferences($text) {
        $count = 0;
        $matches = array();
        $url_free_text = preg_replace('!https?://[\S]+!', ' ', $text);
        $depunctuated_text = " ". preg_replace('/[^a-z0-9]+/i', ' ', $url_free_text) ." ";

        //echo $depunctuated_text;
        preg_match_all("/\bLorde\b/i", $depunctuated_text, $matches);
        //print_r($matches[0]);
        $count += sizeof($matches[0]);

        preg_match_all("/\bKendrick Lamar\b/i", $depunctuated_text, $matches);
        //print_r($matches[0]);
        $count += sizeof($matches[0]);

        preg_match_all("/\bBeyonce\b/i", $depunctuated_text, $matches);
        //print_r($matches[0]);
        $count += sizeof($matches[0]);

        preg_match_all("/\bTaylor Swift\b/i", $depunctuated_text, $matches);
        //print_r($matches[0]);
        $count += sizeof($matches[0]);

        preg_match_all("/\bDaft Punk\b/i", $depunctuated_text, $matches);
        //print_r($matches[0]);
        $count += sizeof($matches[0]);

        preg_match_all("/\bJay Z\b/i", $depunctuated_text, $matches);
        //print_r($matches[0]);
        $count += sizeof($matches[0]);

        preg_match_all("/\bRobin Thicke\b/i", $depunctuated_text, $matches);
        //print_r($matches[0]);
        $count += sizeof($matches[0]);

        preg_match_all("/\bMacklemore\b/i", $depunctuated_text, $matches);
        //print_r($matches[0]);
        $count += sizeof($matches[0]);

        preg_match_all("/\bKacey Musgraves\b/i", $depunctuated_text, $matches);
        //print_r($matches[0]);
        $count += sizeof($matches[0]);

        preg_match_all("/\bPharrell\b/i", $depunctuated_text, $matches);
        //print_r($matches[0]);
        $count += sizeof($matches[0]);

        return $count;
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('Grammies2014Insight');
