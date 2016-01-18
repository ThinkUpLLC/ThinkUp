<?php
/*
 Plugin Name: Oscars 2015
 Description: Did you mention the Oscars?
 When: Monday, February 23, 2015
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/oscars2014.php
 *
 * Copyright (c) 2014-2016 Gina Trapani, Anil Dash
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
 * @copyright 2014-2016 Gina Trapani, Anil Dash
 * @author Anil Dash <anil[at]thinkup[dot]com>
 */

class Oscars2015Insight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        if (Utils::isTest() || date("Y-m-d") == '2015-02-23') {
            parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
            $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
            $hero_image = array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2015-02/oscars2015.jpg',
                'alt_text' => 'Oprah got a Lego Oscar!',
                'credit' => 'Photo: Disney | ABC Television Group',
                'img_link' => 'https://www.flickr.com/photos/disneyabc/16620198142'
            );

            $post_dao = DAOFactory::getDAO('PostDAO');
            $last_month_of_posts = $post_dao->getAllPostsByUsernameOrderedBy($instance->network_username,
            $network=$instance->network, $count=0, $order_by="pub_date", $in_last_x_days = 30,
            $iterator = false, $is_public = false);

            if (self::shouldGenerateWeeklyInsight('oscars_2015', $instance, $insight_date='today',
                $regenerate_existing_insight=false, $day_of_week=1, count($last_month_of_posts))) {
                foreach ($last_month_of_posts as $post) {
                    $this->logger->logInfo("Post text is: " . $post->post_text, __METHOD__.','.__LINE__);
                    //  see if $post date is before the awards aired
                    if ($post->pub_date < "2015-02-22 18:00:00") {
                        $mentioned_oscar_winner = self::detectOscarWinnerReferences($post->post_text);
                        $mentioned_oscar_loser = self::detectOscarLoserReferences($post->post_text);
                        $oscar_mention_count = self::countOscarMentions($post->post_text);
                        if ($mentioned_oscar_winner) {
                            $this->logger->logInfo("Winner mention: $mentioned_oscar_winner", __METHOD__.','.__LINE__);
                            $insight_body = "$this->username was talking about $mentioned_oscar_winner before the "
                                . "Academy Award winners were even announced!";
                        } else {
                            $this->logger->logInfo("No winners mentioned, skipping insight. ", __METHOD__.','.__LINE__);
                        }
                        if ($mentioned_oscar_loser) {
                            $this->logger->logInfo("Loser mention: $mentioned_oscar_loser", __METHOD__.','.__LINE__);
                            $insight_body_suffix = " Looks like the Academy voters might have missed "
                            . "$this->username's " . $this->terms->getNoun('post', InsightTerms::PLURAL)
                            . " about " . $mentioned_oscar_loser . ", though.";
                        }
                    }
                }

                if ($insight_body_suffix) {
                    $insight_text = $insight_body . $insight_body_suffix;
                } else {
                    $insight_text = $insight_body;
                }

                if ($insight_body) {
                    $headline = "Somebody was ready for the Oscars party!";

                    $my_insight = new Insight();
                    $my_insight->slug = 'oscars_2015'; //slug to label this insight's content
                    $my_insight->instance_id = $instance->id;
                    $my_insight->date = $this->insight_date; //date is often this or $simplified_post_date
                    $my_insight->headline = $headline; // or just set a string like 'Ohai';
                    $my_insight->text = $insight_text; // or just set a strong like "Greetings humans";
                    $my_insight->filename = basename(__FILE__, ".php"); //Same for every insight, must be set this way
                    $my_insight->emphasis = Insight::EMPHASIS_HIGH; //Optional emphasis, default Insight::EMPHASIS_LOW
                    $my_insight->setHeroImage($hero_image);

                    $this->insight_dao->insertInsight($my_insight);
                }
            }
            $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
        }
    }

    /**
     * detect the mention of Oscar-related terms in text.
     * @param str $text
     * @return str Topic names in $text
     */
    public static function detectOscarWinnerReferences($text) {
        $topic = '';
        $matches = array();
        $url_free_text = preg_replace('!https?://[\S]+!', ' ', $text);
        $depunctuated_text = " ". preg_replace('/[^a-z0-9]+/i', ' ', $url_free_text) ." ";

        if(preg_match_all("/\balejandro gonzález iñárritu/i", $depunctuated_text, $matches)) { $topic = 'Alejandro González Iñárritu'; }
        if(preg_match_all("/\bamerican sniper/i", $depunctuated_text, $matches)) { $topic = 'American Sniper'; }
        if(preg_match_all("/\bbig hero 6/i", $depunctuated_text, $matches)) { $topic = 'Big Hero 6'; }
        if(preg_match_all("/\bbirdman/i", $depunctuated_text, $matches)) { $topic = 'Birdman'; }
        if(preg_match_all("/\bcitizenfour/i", $depunctuated_text, $matches)) { $topic = 'Citizenfour'; }
//        if(preg_match_all("/\bcommon/i", $depunctuated_text, $matches)) { $topic = 'Common'; }
        if(preg_match_all("/\bcrisis hotline/i", $depunctuated_text, $matches)) { $topic = 'Crisis Hotline'; }
        if(preg_match_all("/\beddie redmayne/i", $depunctuated_text, $matches)) { $topic = 'Eddie Redmayne'; }
//        if(preg_match_all("/\bfeast/i", $depunctuated_text, $matches)) { $topic = 'Feast'; }
//        if(preg_match_all("/\bglory/i", $depunctuated_text, $matches)) { $topic = 'Glory'; }
        if(preg_match_all("/\bgrand budapest hotel/i", $depunctuated_text, $matches)) { $topic = 'Grand Budapest Hotel'; }
        if(preg_match_all("/\bimitation game/i", $depunctuated_text, $matches)) { $topic = 'The Imitation Game'; }
        if(preg_match_all("/\iñárritu/i", $depunctuated_text, $matches)) { $topic = 'Alejandro González Iñárritu'; }
        if(preg_match_all("/\binarritu/i", $depunctuated_text, $matches)) { $topic = 'Alejandro González Iñárritu'; }
        if(preg_match_all("/\binterstellar/i", $depunctuated_text, $matches)) { $topic = 'Interstellar'; }
        if(preg_match_all("/\bj. k. simmons/i", $depunctuated_text, $matches)) { $topic = 'J.K. Simmons'; }
        if(preg_match_all("/\bjk simmmons/i", $depunctuated_text, $matches)) { $topic = 'J.K. Simmons'; }
        if(preg_match_all("/\bjohn legend/i", $depunctuated_text, $matches)) { $topic = 'John Legend'; }
        if(preg_match_all("/\bjulianne moore/i", $depunctuated_text, $matches)) { $topic = 'Julianne Moore'; }
        if(preg_match_all("/\bpatricia arquette/i", $depunctuated_text, $matches)) { $topic = 'Patricia Arquette'; }
//        if(preg_match_all("/\bthe phone call/i", $depunctuated_text, $matches)) { $topic = 'The Phone Call'; }
        if(preg_match_all("/\bveterans press 1/i", $depunctuated_text, $matches)) { $topic = 'Crisis Hotline'; }
//        if(preg_match_all("/\bwhiplash/i", $depunctuated_text, $matches)) { $topic = 'Whiplash'; }

        return $topic;
    }

    /**
     * detect the mention of Oscar-related terms in text.
     * @param str $text
     * @return str Topic names in $text
     */
    public static function detectOscarLoserReferences($text) {
        $topic = '';
        $matches = array();
        $url_free_text = preg_replace('!https?://[\S]+!', ' ', $text);
        $depunctuated_text = " ". preg_replace('/[^a-z0-9]+/i', ' ', $url_free_text) ." ";

        if(preg_match_all("/\ba single life/i", $depunctuated_text, $matches)) { $topic = 'A Single Life'; }
        if(preg_match_all("/\baya/i", $depunctuated_text, $matches)) { $topic = 'Aya'; }
        if(preg_match_all("/\bbegin again/i", $depunctuated_text, $matches)) { $topic = 'Begin Again'; }
        if(preg_match_all("/\bbenedict cumberbatch/i", $depunctuated_text, $matches)) { $topic = 'Benedict Cumberbatch'; }
        if(preg_match_all("/\bbennett miller/i", $depunctuated_text, $matches)) { $topic = 'Bennett Miller'; }
        if(preg_match_all("/\bbeyond the lights/i", $depunctuated_text, $matches)) { $topic = 'Beyond The Lights'; }
        if(preg_match_all("/\bboogaloo and graham/i", $depunctuated_text, $matches)) { $topic = 'Boogaloo And Graham'; }
        if(preg_match_all("/\bboyhood/i", $depunctuated_text, $matches)) { $topic = 'Boyhood'; }
        if(preg_match_all("/\bbradley cooper/i", $depunctuated_text, $matches)) { $topic = 'Bradley Cooper'; }
        if(preg_match_all("/\bbutter lamp/i", $depunctuated_text, $matches)) { $topic = 'Butter Lamp'; }
        if(preg_match_all("/\bcaptain america/i", $depunctuated_text, $matches)) { $topic = 'Captain America: Winter Soldier'; }
        if(preg_match_all("/\bdam keeper/i", $depunctuated_text, $matches)) { $topic = 'Dam Keeper'; }
        if(preg_match_all("/\bdawn of the planet of the apes/i", $depunctuated_text, $matches)) { $topic = 'Dawn Of The Planet Of The Apes'; }
        if(preg_match_all("/\bdays of future past/i", $depunctuated_text, $matches)) { $topic = 'Days Of Future Past'; }
        if(preg_match_all("/\bdiane warren/i", $depunctuated_text, $matches)) { $topic = 'Diane Warren'; }
        if(preg_match_all("/\bedward norton/i", $depunctuated_text, $matches)) { $topic = 'Edward Norton'; }
        if(preg_match_all("/\bemma stone/i", $depunctuated_text, $matches)) { $topic = 'Emma Stone'; }
        if(preg_match_all("/\bethan hawke/i", $depunctuated_text, $matches)) { $topic = 'Ethan Hawke'; }
        if(preg_match_all("/\beverything is awesome/i", $depunctuated_text, $matches)) { $topic = 'Everything Is Awesome'; }
        if(preg_match_all("/\bfelicity jones/i", $depunctuated_text, $matches)) { $topic = 'Felicity Jones'; }
        if(preg_match_all("/\bfinding vivian maier/i", $depunctuated_text, $matches)) { $topic = 'Finding Vivian Maier'; }
        if(preg_match_all("/\bfive armies/i", $depunctuated_text, $matches)) { $topic = 'Five Armies'; }
        if(preg_match_all("/\bfoxcatcher/i", $depunctuated_text, $matches)) { $topic = 'Foxcatcher'; }
        if(preg_match_all("/\bglen campbell/i", $depunctuated_text, $matches)) { $topic = 'Glen Campbell'; }
        if(preg_match_all("/\bguardians of the galaxy/i", $depunctuated_text, $matches)) { $topic = 'Guardians Of The Galaxy'; }
        if(preg_match_all("/\bhobbit/i", $depunctuated_text, $matches)) { $topic = 'Hobbit'; }
        if(preg_match_all("/\bhow to train your dragon/i", $depunctuated_text, $matches)) { $topic = 'How To Train Your Dragon 2'; }
        if(preg_match_all("/\bi'm not gonna miss you/i", $depunctuated_text, $matches)) { $topic = 'I\'m Not Gonna Miss You'; }
        if(preg_match_all("/\binherent vice/i", $depunctuated_text, $matches)) { $topic = 'Inherent Vice'; }
        if(preg_match_all("/\binto the woods/i", $depunctuated_text, $matches)) { $topic = 'Into The Woods'; }
        if(preg_match_all("/\bjoanna/i", $depunctuated_text, $matches)) { $topic = 'Joanna'; }
        if(preg_match_all("/\bkeira knightley/i", $depunctuated_text, $matches)) { $topic = 'Keira Knightley'; }
        if(preg_match_all("/\blast days in vietnam/i", $depunctuated_text, $matches)) { $topic = 'Last Days In Vietnam'; }
        if(preg_match_all("/\blaura dern/i", $depunctuated_text, $matches)) { $topic = 'Laura Dern'; }
        if(preg_match_all("/\blost stars/i", $depunctuated_text, $matches)) { $topic = 'Lost Stars'; }
        if(preg_match_all("/\bmaleficent/i", $depunctuated_text, $matches)) { $topic = 'Maleficent'; }
        if(preg_match_all("/\bmarion cotillard/i", $depunctuated_text, $matches)) { $topic = 'Marion Cotillard'; }
        if(preg_match_all("/\bmark ruffalo/i", $depunctuated_text, $matches)) { $topic = 'Mark Ruffalo'; }
        if(preg_match_all("/\bme and my moulton/i", $depunctuated_text, $matches)) { $topic = 'Me And My Moulton'; }
        if(preg_match_all("/\bmeryl streep/i", $depunctuated_text, $matches)) { $topic = 'Meryl Streep'; }
        if(preg_match_all("/\bmichael keaton/i", $depunctuated_text, $matches)) { $topic = 'Michael Keaton'; }
        if(preg_match_all("/\bmorten tyldum/i", $depunctuated_text, $matches)) { $topic = 'Morten Tyldum'; }
        if(preg_match_all("/\bmr. turner/i", $depunctuated_text, $matches)) { $topic = 'Mr. Turner'; }
        if(preg_match_all("/\bnightcrawler/i", $depunctuated_text, $matches)) { $topic = 'Nightcrawler'; }
        if(preg_match_all("/\bour curse/i", $depunctuated_text, $matches)) { $topic = 'Our Curse'; }
        if(preg_match_all("/\bparvaneh/i", $depunctuated_text, $matches)) { $topic = 'Parvaneh'; }
        if(preg_match_all("/\breese witherspoon/i", $depunctuated_text, $matches)) { $topic = 'Reese Witherspoon'; }
        if(preg_match_all("/\brichard linklater/i", $depunctuated_text, $matches)) { $topic = 'Richard Linklater'; }
        if(preg_match_all("/\brobert duvall/i", $depunctuated_text, $matches)) { $topic = 'Robert Duvall'; }
        if(preg_match_all("/\brosamund pike/i", $depunctuated_text, $matches)) { $topic = 'Rosamund Pike'; }
        if(preg_match_all("/\bsalt of the earth/i", $depunctuated_text, $matches)) { $topic = 'Salt Of The Earth'; }
        if(preg_match_all("/\bselma/i", $depunctuated_text, $matches)) { $topic = 'Selma'; }
        if(preg_match_all("/\bshawn patterson/i", $depunctuated_text, $matches)) { $topic = 'Shawn Patterson'; }
        if(preg_match_all("/\bsong of the sea/i", $depunctuated_text, $matches)) { $topic = 'Song Of The Sea'; }
        if(preg_match_all("/\bsteve carell/i", $depunctuated_text, $matches)) { $topic = 'Steve Carell'; }
        if(preg_match_all("/\bthe bigger picture/i", $depunctuated_text, $matches)) { $topic = 'The Bigger Picture'; }
        if(preg_match_all("/\bthe boxtrolls/i", $depunctuated_text, $matches)) { $topic = 'The Boxtrolls'; }
        if(preg_match_all("/\bthe reaper/i", $depunctuated_text, $matches)) { $topic = 'The Reaper'; }
        if(preg_match_all("/\bprincess kaguya/i", $depunctuated_text, $matches)) { $topic = 'The Tale Of The Princess Kaguya'; }
        if(preg_match_all("/\btheory of everything/i", $depunctuated_text, $matches)) { $topic = 'Theory Of Everything'; }
        if(preg_match_all("/\bunbroken/i", $depunctuated_text, $matches)) { $topic = 'Unbroken'; }
        if(preg_match_all("/\bvirunga/i", $depunctuated_text, $matches)) { $topic = 'Virunga'; }
        if(preg_match_all("/\bwes anderson/i", $depunctuated_text, $matches)) { $topic = 'Wes Anderson'; }
        if(preg_match_all("/\bwhite earth/i", $depunctuated_text, $matches)) { $topic = 'White Earth'; }
        if(preg_match_all("/\bwinter soldier/i", $depunctuated_text, $matches)) { $topic = 'Captain America: Winter Soldier'; }
        if(preg_match_all("/\bx-men/i", $depunctuated_text, $matches)) { $topic = 'X-Men'; }

        return $topic;
    }

    /**
     * detect the mention of the Oscars in text.
     * @param str $text
     * @return int count of Oscar metnions in $text
     */
    public static function countOscarMentions($text) {
        $count = 0;
        $matches = array();
        $url_free_text = preg_replace('!https?://[\S]+!', ' ', $text);
        $depunctuated_text = " ". preg_replace('/[^a-z0-9]+/i', ' ', $url_free_text) ." ";

        preg_match_all("/\boscar/i", $depunctuated_text, $matches);
        $count += sizeof($matches[0]);

        preg_match_all("/\bacademy award/i", $depunctuated_text, $matches);
        $count += sizeof($matches[0]);

        preg_match_all("/\bbest picture/i", $depunctuated_text, $matches);
        $count += sizeof($matches[0]);

        preg_match_all("/\bbest director/i", $depunctuated_text, $matches);
        $count += sizeof($matches[0]);

        preg_match_all("/\bbest supporting/i", $depunctuated_text, $matches);
        $count += sizeof($matches[0]);

        return $count;
    }

}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('Oscars2015Insight');
