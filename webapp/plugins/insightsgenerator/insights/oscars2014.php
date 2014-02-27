<?php
/*
 Plugin Name: Oscars
 2014
 Description: Did you mention the Oscars
?
 When: Sunday, February 23, 2014
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/oscars2014.php
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

class Oscars2014Insight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        if (Utils::isTest() || date("Y-m-d") == '2014-03-02') {
            parent::generateInsight($instance, $last_week_of_posts, $number_days);
            $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
            $hero_image = array(
                'url' => 'http://farm6.staticflickr.com/5018/5485153604_953697e312_b.jpg',
                'alt_text' => 'Jennifer Lawyrence at the 83rd Academy Awards',
                'credit' => 'Photo: Mingle MediaTV',
                'img_link' => 'http://www.flickr.com/photos/minglemediatv/5485153604/'
            );

            $post_dao = DAOFactory::getDAO('PostDAO');
            $last_month_of_posts = $post_dao->getAllPostsByUsernameOrderedBy($instance->network_username,
            $network=$instance->network, $count=0, $order_by="pub_date", $in_last_x_days = 30,
            $iterator = false, $is_public = false);

            if (self::shouldGenerateWeeklyInsight('oscars_2014', $instance, $insight_date='today',
            $regenerate_existing_insight=true, $day_of_week=0, count($last_month_of_posts))) {
                $event_count = 0;
                foreach ($last_month_of_posts as $post) {
                    $mentioned_oscar_topic += self::detectOscarTopicReferences($post->post_text);
                }
                $this->logger->logInfo("There is a mention of $mentioned_oscar_topic", __METHOD__.','.__LINE__);

                if ($mentioned_oscar_topic) {
                    $headline = "Can we crash your Oscar party next year?";
                    $insight_text = "$this->username mentioned $mentioned_oscar_topic before the Academy Awards were"
                                    . " even announced!";

                    $my_insight = new Insight();
                    $my_insight->slug = 'oscars_2014'; //slug to label this insight's content
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
     * detect the mention of Oscar-related terms in text.
     * @param str $text
     * @return str Topic names in $text
     */
    public static function detectOscarTopicReferences($text) {
        $topic = '';
        $matches = array();
        $url_free_text = preg_replace('!https?://[\S]+!', ' ', $text);
        $depunctuated_text = " ". preg_replace('/[^a-z0-9]+/i', ' ', $url_free_text) ." ";

        if(preg_match_all("/\bamerican hustle/i", $depunctuated_text, $matches)) { $topic = 'American Hustle'; }

        if(preg_match_all("/\b12 years a slave/i", $depunctuated_text, $matches)) { $topic = '12 Years a Slave'; }
        if(preg_match_all("/\b20 feet from stardom/i", $depunctuated_text, $matches)) { $topic = '20 Feet From Stardom'; }
        if(preg_match_all("/\bact of killing/i", $depunctuated_text, $matches)) { $topic = 'Act Of Killing'; }
        if(preg_match_all("/\ball is lost/i", $depunctuated_text, $matches)) { $topic = 'All Is Lost'; }
        if(preg_match_all("/\bamerican hustle/i", $depunctuated_text, $matches)) { $topic = 'American Hustle'; }
        if(preg_match_all("/\bamy adams/i", $depunctuated_text, $matches)) { $topic = 'Amy Adams'; }
        if(preg_match_all("/\baquel no era yo/i", $depunctuated_text, $matches)) { $topic = 'Aquel No Era Yo'; }
        if(preg_match_all("/\bavant que de tout perdre/i", $depunctuated_text, $matches)) { $topic = 'Avant Que De Tout Perdre'; }
        if(preg_match_all("/\bbad grandpa/i", $depunctuated_text, $matches)) { $topic = 'Bad Grandpa'; }
        if(preg_match_all("/\bbarkhad abdi/i", $depunctuated_text, $matches)) { $topic = 'Barkhad Abdi'; }
        if(preg_match_all("/\bbefore midnight/i", $depunctuated_text, $matches)) { $topic = 'Before Midnight'; }
        if(preg_match_all("/\bblue jasmine/i", $depunctuated_text, $matches)) { $topic = 'Blue Jasmine'; }
        if(preg_match_all("/\bbook thief/i", $depunctuated_text, $matches)) { $topic = 'Book Thief'; }
        if(preg_match_all("/\bbradley cooper/i", $depunctuated_text, $matches)) { $topic = 'Bradley Cooper'; }
        if(preg_match_all("/\bbroken circle breakdown/i", $depunctuated_text, $matches)) { $topic = 'Broken Circle Breakdown'; }
        if(preg_match_all("/\bbruce dern/i", $depunctuated_text, $matches)) { $topic = 'Bruce Dern'; }
        if(preg_match_all("/\bcaptain phillips/i", $depunctuated_text, $matches)) { $topic = 'Captain Phillips'; }
        if(preg_match_all("/\bcate blanchett/i", $depunctuated_text, $matches)) { $topic = 'Cate Blanchett'; }
        if(preg_match_all("/\bcavedigger/i", $depunctuated_text, $matches)) { $topic = 'Cavedigger'; }
        if(preg_match_all("/\bchiwetel/i", $depunctuated_text, $matches)) { $topic = 'Chiwetel'; }
        if(preg_match_all("/\bchiwetel ejiofor/i", $depunctuated_text, $matches)) { $topic = 'Chiwetel Ejiofor'; }
        if(preg_match_all("/\bchristian bale/i", $depunctuated_text, $matches)) { $topic = 'Christian Bale'; }
        if(preg_match_all("/\bcroods/i", $depunctuated_text, $matches)) { $topic = 'Croods'; }
        if(preg_match_all("/\bcutie and the boxer/i", $depunctuated_text, $matches)) { $topic = 'Cutie And The Boxer'; }
        if(preg_match_all("/\bdallas buyers club/i", $depunctuated_text, $matches)) { $topic = 'Dallas Buyers Club'; }
        if(preg_match_all("/\bdesolation of smaug/i", $depunctuated_text, $matches)) { $topic = 'Desolation Of Smaug'; }
        if(preg_match_all("/\bdespicable me 2/i", $depunctuated_text, $matches)) { $topic = 'Despicable Me 2'; }
        if(preg_match_all("/\bdirty wars/i", $depunctuated_text, $matches)) { $topic = 'Dirty Wars'; }
        if(preg_match_all("/\bernest  celestine/i", $depunctuated_text, $matches)) { $topic = 'Ernest  Celestine'; }
        if(preg_match_all("/\bernest and celestine/i", $depunctuated_text, $matches)) { $topic = 'Ernest And Celestine'; }
        if(preg_match_all("/\bfacing fear/i", $depunctuated_text, $matches)) { $topic = 'Facing Fear'; }
        if(preg_match_all("/\bferal/i", $depunctuated_text, $matches)) { $topic = 'Feral'; }
        if(preg_match_all("/\bfrozen/i", $depunctuated_text, $matches)) { $topic = 'Frozen'; }
        if(preg_match_all("/\bget a horse/i", $depunctuated_text, $matches)) { $topic = 'Get A Horse'; }
        if(preg_match_all("/\bgravity/i", $depunctuated_text, $matches)) { $topic = 'Gravity'; }
        if(preg_match_all("/\bgreat beauty/i", $depunctuated_text, $matches)) { $topic = 'Great Beauty'; }
        if(preg_match_all("/\bgreat gatsby/i", $depunctuated_text, $matches)) { $topic = 'Great Gatsby'; }
        if(preg_match_all("/\bhelium/i", $depunctuated_text, $matches)) { $topic = 'Helium'; }
        if(preg_match_all("/\bhobbit/i", $depunctuated_text, $matches)) { $topic = 'Hobbit'; }
        if(preg_match_all("/\binside llewyn davis/i", $depunctuated_text, $matches)) { $topic = 'Inside Llewyn Davis'; }
        if(preg_match_all("/\binto darkness/i", $depunctuated_text, $matches)) { $topic = 'Into Darkness'; }
        if(preg_match_all("/\binvisible woman/i", $depunctuated_text, $matches)) { $topic = 'Invisible Woman'; }
        if(preg_match_all("/\biron man 3/i", $depunctuated_text, $matches)) { $topic = 'Iron Man 3'; }
        if(preg_match_all("/\bjared leto/i", $depunctuated_text, $matches)) { $topic = 'Jared Leto'; }
        if(preg_match_all("/\bjennifer lawrence/i", $depunctuated_text, $matches)) { $topic = 'Jennifer Lawrence'; }
        if(preg_match_all("/\bjonah hill/i", $depunctuated_text, $matches)) { $topic = 'Jonah Hill'; }
        if(preg_match_all("/\bjudi dench/i", $depunctuated_text, $matches)) { $topic = 'Judi Dench'; }
        if(preg_match_all("/\bjulia roberts/i", $depunctuated_text, $matches)) { $topic = 'Julia Roberts'; }
        if(preg_match_all("/\bjune squibb/i", $depunctuated_text, $matches)) { $topic = 'June Squibb'; }
        if(preg_match_all("/\bkaikki hoitaa/i", $depunctuated_text, $matches)) { $topic = 'Kaikki Hoitaa'; }
        if(preg_match_all("/\bkarama has no walls/i", $depunctuated_text, $matches)) { $topic = 'Karama Has No Walls'; }
        if(preg_match_all("/\blady in number 6/i", $depunctuated_text, $matches)) { $topic = 'Lady In Number 6'; }
        if(preg_match_all("/\bleonardo dicaprio/i", $depunctuated_text, $matches)) { $topic = 'Leonardo Dicaprio'; }
        if(preg_match_all("/\blet it go/i", $depunctuated_text, $matches)) { $topic = 'Let It Go'; }
        if(preg_match_all("/\blone ranger/i", $depunctuated_text, $matches)) { $topic = 'Lone Ranger'; }
        if(preg_match_all("/\blone survivor/i", $depunctuated_text, $matches)) { $topic = 'Lone Survivor'; }
        if(preg_match_all("/\blupita nyong o/i", $depunctuated_text, $matches)) { $topic = 'Lupita Nyong O'; }
        if(preg_match_all("/\blupita nyongo/i", $depunctuated_text, $matches)) { $topic = 'Lupita Nyong\'o'; }
        if(preg_match_all("/\bmatthew mcconaughey/i", $depunctuated_text, $matches)) { $topic = 'Matthew Mcconaughey'; }
        if(preg_match_all("/\bmeryl streep/i", $depunctuated_text, $matches)) { $topic = 'Meryl Streep'; }
        if(preg_match_all("/\bmichael fassbender/i", $depunctuated_text, $matches)) { $topic = 'Michael Fassbender'; }
        if(preg_match_all("/\bmissing picture/i", $depunctuated_text, $matches)) { $topic = 'Missing Picture'; }
        if(preg_match_all("/\bmoon song/i", $depunctuated_text, $matches)) { $topic = 'Moon Song'; }
        if(preg_match_all("/\bmr hublot/i", $depunctuated_text, $matches)) { $topic = 'Mr Hublot'; }
        if(preg_match_all("/\bomar/i", $depunctuated_text, $matches)) { $topic = 'Omar'; }
        if(preg_match_all("/\bordinary lacelove/i", $depunctuated_text, $matches)) { $topic = 'Ordinary laceLove'; }
        if(preg_match_all("/\bphilomena/i", $depunctuated_text, $matches)) { $topic = 'Philomena'; }
        if(preg_match_all("/\bprison terminal/i", $depunctuated_text, $matches)) { $topic = 'Prison Terminal'; }
        if(preg_match_all("/\broom on the broom/i", $depunctuated_text, $matches)) { $topic = 'Room On The Broom'; }
        if(preg_match_all("/\bsally hawkins/i", $depunctuated_text, $matches)) { $topic = 'Sally Hawkins'; }
        if(preg_match_all("/\bsandra bullock/i", $depunctuated_text, $matches)) { $topic = 'Sandra Bullock'; }
        if(preg_match_all("/\bsaving mr banks/i", $depunctuated_text, $matches)) { $topic = 'Saving Mr Banks'; }
        if(preg_match_all("/\bstar trek into darkness/i", $depunctuated_text, $matches)) { $topic = 'Star Trek Into Darkness'; }
        if(preg_match_all("/\bthe grandmaster/i", $depunctuated_text, $matches)) { $topic = 'The Grandmaster'; }
        if(preg_match_all("/\bthe hunt/i", $depunctuated_text, $matches)) { $topic = 'The Hunt'; }
        if(preg_match_all("/\bthe square/i", $depunctuated_text, $matches)) { $topic = 'The Square'; }
        if(preg_match_all("/\bvoorman problem/i", $depunctuated_text, $matches)) { $topic = 'Voorman Problem'; }
        if(preg_match_all("/\bwind rises/i", $depunctuated_text, $matches)) { $topic = 'Wind Rises'; }
        if(preg_match_all("/\bwolf of wall street/i", $depunctuated_text, $matches)) { $topic = 'Wolf of Wall Street'; }

        return $topic;
    }

    /**
     * detect the mention of the Oscars in text.
     * @param str $text
     * @return int count of Oscar metnions in $text
     */
    public static function detectOscarMentions($text) {
        $count = 0;
        $matches = array();
        $url_free_text = preg_replace('!https?://[\S]+!', ' ', $text);
        $depunctuated_text = " ". preg_replace('/[^a-z0-9]+/i', ' ', $url_free_text) ." ";

        preg_match_all("/\boscar/i", $depunctuated_text, $matches);
        $count += sizeof($matches[0]);

        preg_match_all("/\bacademy award/i", $depunctuated_text, $matches);
        $count += sizeof($matches[0]);

        return $count;
    }


}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('Oscars
2014Insight');
