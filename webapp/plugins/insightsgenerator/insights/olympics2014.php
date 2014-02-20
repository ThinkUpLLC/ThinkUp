<?php
/*
 Plugin Name: Olympics 2014
 Description: Did you mention the Olympics?
 When: Saturday
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/olympics2014.php
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

class Olympics2014Insight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
        $insight_text = '';
        $header_image = 'https://pbs.twimg.com/media/Bf5LVvHCMAEwdC0.jpg:large';

        if (self::shouldGenerateInsight('olympics_2014', $instance, $insight_date='today',
        $regenerate_existing_insight=false, $day_of_week=0, count($last_week_of_posts))) {
            $text = '';
            $event_count = 0;
            foreach ($last_week_of_posts as $post) {
                $event_count += self::countOlympicReferences($post->post_text);
            }
            $this->logger->logInfo("there are $event_count Olympic event mentions", __METHOD__.','.__LINE__);

            if ($event_count > 0) {
                $headline = "Do they give out medals for " .$this->terms->getNoun('post', InsightTerms::PLURAL) .
                    " during the Games?";

                $insight_text = "$this->username referenced ";

                if ($event_count > 0) {

                    $this->logger->logInfo("there are event mentions", __METHOD__.','.__LINE__);

                    $insight_text .= "the Olympics ";
                    if ($event_count > 1) {
                        $this->logger->logInfo("there is more than one event mention", __METHOD__.','.__LINE__);
                        $insight_text .= "$event_count times since they started";
                        $insight_text .= ", which is kind of like winning $event_count gold medals in " .
                            ucfirst($instance->network) . ", right?";
                    } else {
                        $insight_text .= "just as the whole world's attention was focused on the games";
                        $insight_text .= ", and that's a pretty great way to join a global conversation.";
                    }
                }

                $insight_baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
                $insight_baseline_dao->insertInsightBaseline("olympics_2014", $instance->id, $event_count,
                $this->insight_date); // just in case we do this insight again next year

                $my_insight = new Insight();

                $my_insight->slug = 'olympics_2014'; //slug to label this insight's content
                $my_insight->instance_id = $instance->id;
                $my_insight->date = $this->insight_date; //date is often this or $simplified_post_date
                $my_insight->headline = $headline; // or just set a string like 'Ohai';
                $my_insight->text = $insight_text; // or just set a strong like "Greetings humans";
                $my_insight->filename = basename(__FILE__, ".php"); //Same for every insight, must be set this way
                $my_insight->emphasis = Insight::EMPHASIS_HIGH; //Optional emphasis, default is Insight::EMPHASIS_LOW

                if ($event_count) {
                    $this->insight_dao->insertInsight($my_insight);
                }
            }
        }

        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

    /**
     * Count the number of times "Olympic" or "Olympics" appears in text.
     * @param str $text
     * @return int Total occurences of the event names in $text
     */
    public static function countOlympicReferences($text) {
        $count = 0;
        $matches = array();
        $url_free_text = preg_replace('!https?://[\S]+!', ' ', $text);
        $depunctuated_text = " ". preg_replace('/[^a-z0-9]+/i', ' ', $url_free_text) ." ";

        preg_match_all("/\bolympic\b/i", $depunctuated_text, $matches);
        $count += sizeof($matches[0]);

        preg_match_all("/\bolympics\b/i", $depunctuated_text, $matches);
        $count += sizeof($matches[0]);

        preg_match_all("/\bsochi\b/i", $depunctuated_text, $matches);
        $count += sizeof($matches[0]);

        preg_match_all("/\bsochi2014\b/i", $depunctuated_text, $matches);
        $count += sizeof($matches[0]);

        preg_match_all("/\bopening ceremony\b/i", $depunctuated_text, $matches);
        $count += sizeof($matches[0]);

        preg_match_all("/\bopening ceremonies\b/i", $depunctuated_text, $matches);
        $count += sizeof($matches[0]);

        preg_match_all("/\bclosing ceremony\b/i", $depunctuated_text, $matches);
        $count += sizeof($matches[0]);

        preg_match_all("/\bclosing ceremonies\b/i", $depunctuated_text, $matches);
        $count += sizeof($matches[0]);

        preg_match_all("/\bgold medal\b/i", $depunctuated_text, $matches);
        $count += sizeof($matches[0]);

        preg_match_all("/\bgold medals\b/i", $depunctuated_text, $matches);
        $count += sizeof($matches[0]);

        preg_match_all("/\bsilver medal\b/i", $depunctuated_text, $matches);
        $count += sizeof($matches[0]);

        preg_match_all("/\bsilver medals\b/i", $depunctuated_text, $matches);
        $count += sizeof($matches[0]);

        preg_match_all("/\bbronze medal\b/i", $depunctuated_text, $matches);
        $count += sizeof($matches[0]);

        preg_match_all("/\bbronze medals\b/i", $depunctuated_text, $matches);
        $count += sizeof($matches[0]);


        return $count;
    }

}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('Olympics2014Insight');
