<?php
/*
 Plugin Name: Olympics 2014
 Description: Did you mention the Olympics?
 When: Sunday, February 23, 2014
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/olympics2014.php
 *
 * Copyright (c) 2014 Gina Trapani, Anil Dash
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
 * @copyright 2014 Gina Trapani, Anil Dash
 * @author Anil Dash <anil[at]thinkup[dot]com>
 */

class Olympics2014Insight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        if (Utils::isTest() || date("Y-m-d") == '2014-02-23') {
            parent::generateInsight($instance, $last_week_of_posts, $number_days);
            $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
            $hero_image = array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2014-02/olympics2014.jpg',
                'alt_text' => 'The Olympic rings in Sochi',
                'credit' => 'Photo: Atos International',
                'img_link' => 'http://www.flickr.com/photos/atosorigin/12568057033/'
            );

            $post_dao = DAOFactory::getDAO('PostDAO');
            $last_month_of_posts = $post_dao->getAllPostsByUsernameOrderedBy($instance->network_username,
            $network=$instance->network, $count=0, $order_by="pub_date", $in_last_x_days = 30,
            $iterator = false, $is_public = false);

            if (self::shouldGenerateWeeklyInsight('olympics_2014', $instance, $insight_date='today',
            $regenerate_existing_insight=true, $day_of_week=0, count($last_month_of_posts))) {
                $event_count = 0;
                foreach ($last_month_of_posts as $post) {
                    $event_count += self::countOlympicReferences($post->post_text);
                }
                $this->logger->logInfo("There are $event_count Olympic-related mentions", __METHOD__.','.__LINE__);

                if ($event_count > 0) {
                    $headline = "Do they give out medals for ".$this->terms->getNoun('post', InsightTerms::PLURAL)."?";
                    $insight_text = "$this->username mentioned ";

                    if ($event_count > 0) {
                        $this->logger->logInfo("There are event mentions", __METHOD__.','.__LINE__);

                        $insight_text .= "the Olympics ";
                        if ($event_count > 1) {
                            $this->logger->logInfo("there is more than one event mention", __METHOD__.','.__LINE__);
                            $insight_text .= "$event_count times since they started.";
                            $insight_text .= " That's kind of like winning $event_count gold medals in " .
                                ucfirst($instance->network) . ", right?";
                        } else {
                            $insight_text .= "just as the whole world's attention was focused on the Games.";
                            $insight_text .= " That's a pretty great way to join a global conversation.";
                        }
                    }
                    $my_insight = new Insight();
                    $my_insight->slug = 'olympics_2014'; //slug to label this insight's content
                    $my_insight->instance_id = $instance->id;
                    $my_insight->date = $this->insight_date; //date is often this or $simplified_post_date
                    $my_insight->headline = $headline; // or just set a string like 'Ohai';
                    $my_insight->text = $insight_text; // or just set a strong like "Greetings humans";
                    $my_insight->filename = basename(__FILE__, ".php"); //Same for every insight, must be set this way
                    $my_insight->emphasis = Insight::EMPHASIS_HIGH; //Optional emphasis, default is Insight::EMPHASIS_LOW
                    $my_insight->setHeroImage($hero_image);

                    $this->insight_dao->insertInsight($my_insight);
                }
            }
            $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
        }
    }

    /**
     * Count the number of times Olympic-related terms appear in text.
     * @param str $text
     * @return int Total occurences of the event names in $text
     */
    public static function countOlympicReferences($text) {
        $count = 0;
        $matches = array();
        $url_free_text = preg_replace('!https?://[\S]+!', ' ', $text);
        $depunctuated_text = " ". preg_replace('/[^a-z0-9]+/i', ' ', $url_free_text) ." ";

        preg_match_all("/\bolympic/i", $depunctuated_text, $matches);
        $count += sizeof($matches[0]);

        preg_match_all("/\bolympian/i", $depunctuated_text, $matches);
        $count += sizeof($matches[0]);

        preg_match_all("/\bsochi/i", $depunctuated_text, $matches);
        $count += sizeof($matches[0]);

        preg_match_all("/\bopening ceremony\b/i", $depunctuated_text, $matches);
        $count += sizeof($matches[0]);

        preg_match_all("/\bopening ceremonies\b/i", $depunctuated_text, $matches);
        $count += sizeof($matches[0]);

        preg_match_all("/\bclosing ceremony\b/i", $depunctuated_text, $matches);
        $count += sizeof($matches[0]);

        preg_match_all("/\bclosing ceremonies\b/i", $depunctuated_text, $matches);
        $count += sizeof($matches[0]);

        preg_match_all("/\bgold medal/i", $depunctuated_text, $matches);
        $count += sizeof($matches[0]);

        preg_match_all("/\bsilver medal/i", $depunctuated_text, $matches);
        $count += sizeof($matches[0]);

        preg_match_all("/\bbronze medal/i", $depunctuated_text, $matches);
        $count += sizeof($matches[0]);

        return $count;
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('Olympics2014Insight');
