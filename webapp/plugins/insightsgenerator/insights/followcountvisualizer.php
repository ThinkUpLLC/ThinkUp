<?php
/*
 Plugin Name: Follow Count Visualizer
 Description: How many people follow you, described as a real-world group.
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/followcountvisualizer.php
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
class FollowCountVisualizerInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug used for this insight
     */
    private $slug = 'follow_count_visualizer';

    /**
     * Milestones we check against.
     */
    public $milestones = array(
        56 => "%username's followers could fill a yellow school bus!",
        115 => "That's how many fans saw the Rolling Stones' first live performance!",
        200 => "%username's followers could fill a New York City subway car!",
        360 => "%username's followers outnumber singers in the Mormon Tabernacle Choir!",
        400 => "%username's followers could fill up a 747!",
        485 => "More people follow %username than play in the largest collegiate marching band!",
        560 => "That's 10 school buses full of students!",
        600 => "More people follow %username than live in Eminence, Missouri!",
        /*TODO fill in this gap */
        1000 => "%username's followers would fill the Hammerstein Ballroom to capacity!",
        1510 => "%username's followers would fill the Apollo Theater!",
        2740 => "%username's followers could fill all the seats in the concert hall at Lincoln Center!",
        3100 => "%username has more followers than there are hot dog vendors in New York City!",
        4815 => "%username's followers outnumber the student body at Wake Forest University!",
        5950 => "%username's followers could fill the seats at Radio City Music Hall!",
        6140 => "%username's followers outnumber the student body at Brown University!",
        7550 => "More people follow %username than live in Rhinebeck, New York!",
        /*TODO fill in this gap */
        12500 => "%username's followers could fill up Wembley Arena!",
        /*TODO fill in this gap */
        17400 => "%username's followers could fill all the seats in the Hollywood Bowl!",
        /*TODO fill in this gap */
        28700 => "%username's followers outnumber the undergraduates enrolled at UCLA!",
        36000 => "That's how many runners were in the 2014 Boston Marathon!",
        40700 => "That's the entire population of Manassas, Virginia!",
        50000 => "%username's followers could fill Yankee Stadium!",
        57000 => "More people follow %username than live in Greenland!",
        /*TODO fill in this gap */
        259000 => "That's the entire population of Buffalo, New York!",
        334300 => "%username's follower count is the size of Belize's entire population!",
        500000 => "That's how many people went to Woodstock!",
        /*TODO fill in this gap */
        750000 => "That's the entire population of Louisville, Kentucky!",
        917100 => "That's more than the entire population of Delaware!",
    );

    /**
     * Images for associated milestones
     */
    private $hero_images = array(
        56 => array(
            'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/bus.jpg',
            'alt_text' => 'Yellow school bus',
            'credit' => 'Photo: Ivy Dawned',
            'img_link' => 'https://www.flickr.com/photos/ivydawned/5460058051',
        ),
        115 => array(
            'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/stones.jpg',
            'alt_text' => 'Crowd at a concert',
            'credit' => 'Photo: Chris',
            'img_link' => 'https://www.flickr.com/photos/cr01/7392740268/',
        ),
        200 => array(
            'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/subway.jpg',
            'alt_text' => 'New York City subway car',
            'credit' => 'Photo: Julian Dunn',
            'img_link' => 'https://www.flickr.com/photos/juliandunn/6920197196',
        ),
        360 => array(
            'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/choir.jpg',
            'alt_text' => 'Mormon Tabernacle Choir',
            'credit' => '',
            'img_link' => '',
        ),
        400 => array(
            'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/747.jpg',
            'alt_text' => '747',
            'credit' => 'Photo: Aero Icarus',
            'img_link' => 'https://www.flickr.com/photos/aero_icarus/4707805048/',
        ),
        485 => array(
            'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/marchingband.jpg',
            'alt_text' => 'Marching band',
            'credit' => '',
            'img_link' => 'http://commons.wikimedia.org/wiki/File:Rosemount_High_School_marching_band.jpg',
        ),
        560 => array(
            'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/buses.jpg',
            'alt_text' => 'Yellow school buses',
            'credit' => 'Photo: dhendrix73',
            'img_link' => 'https://www.flickr.com/photos/dhendrix/6906652333/',
        ),
        600 => array(
            'url' => 'http://maps.googleapis.com/maps/api/staticmap?center=Eminence,MO&zoom=15&size=600x300&sensor=false&maptype=hybrid',
            'alt_text' => 'Eminence, MO',
            'credit' => '',
            'img_link' => '',
        ),
        1000 => array(
            'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/hammersteinballroom.jpg',
            'alt_text' => 'A crowd at the Hammerstein Ballroom',
            'credit' => 'Photo: Jorge Hernandez',
            'img_link' => 'https://www.flickr.com/photos/mediajorgenyc/3882364741',
        ),
        1510 => array(
            'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/apollotheater.jpg',
            'alt_text' => 'Apollo Theater',
            'credit' => 'Photo: Hans Joachim Dudeck',
            'img_link' => 'http://commons.wikimedia.org/wiki/File:Apollo_Theater_Harlem_NYC_2010.JPG',
        ),
        2740 => array(
            'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/lincolncenter.jpg',
            'alt_text' => 'Avery Fisher Hall at Lincoln Center',
            'credit' => 'Photo: Mikhail Klassen',
            'img_link' => 'https://en.wikipedia.org/wiki/File:Avery_fisher_hall.jpg',
        ),
        3100 => array(
            'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/hotdogvendors.jpg',
            'alt_text' => 'New York City street food vendors',
            'credit' => '',
            'img_link' => 'http://en.wikipedia.org/wiki/File:StreetfoodNY.jpg',
        ),
        4815 => array(
            'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/wakeforest.jpg',
            'alt_text' => 'Wake Forest University',
            'credit' => 'Photo: JHMM13',
            'img_link' => 'http://en.wikipedia.org/wiki/File:WakeForestRolledQuad.jpg',
        ),
        5950 => array(
            'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/radiocity.jpg',
            'alt_text' => 'Radio City Music Hall',
            'credit' => 'Photo: flickr4jazz',
            'img_link' => 'https://en.wikipedia.org/wiki/File:Radio_City_Music_Hall_3051638324_4a385c5623.jpg',
        ),
        6140 => array(
            'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/graduates.jpg',
            'alt_text' => 'Graduates',
            'credit' => 'Photo: Sakeeb Sabakka',
            'img_link' => 'http://www.fotopedia.com/items/flickr-4647211575',
        ),
        7550 => array(
            'url' => 'http://maps.googleapis.com/maps/api/staticmap?center=Rhinebeck,NY&zoom=15&size=600x300&sensor=false&maptype=hybrid',
            'alt_text' => 'Rhinebeck, NY',
            'credit' => '',
            'img_link' => '',
        ),
        12500 => array(
            'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/wembley.jpg',
            'alt_text' => 'Wembley Arena',
            'credit' => 'Photo: Mick Baker',
            'img_link' => 'https://www.flickr.com/photos/36593372@N04/8240126447',
        ),
        17400 => array(
            'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/hollywoodbowl.jpg',
            'alt_text' => 'Hollywood Bowl',
            'credit' => 'Photo: Matthew Field',
            'img_link' => 'http://commons.wikimedia.org/wiki/File:Hollywood_bowl_and_sign.jpg',
        ),
        28700 => array(
            'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/ucla.jpg',
            'alt_text' => 'UCLA vs Oregon',
            'credit' => 'Photo: Dave Cooper',
            'img_link' => 'https://www.flickr.com/photos/96414272@N00/2123934452',
        ),
        36000 => array(
            'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/marathon.jpg',
            'alt_text' => 'Marathon runners',
            'credit' => '',
            'img_link' => '',
        ),
        40700 => array(
            'url' => 'http://maps.googleapis.com/maps/api/staticmap?center=Manassass,VA&zoom=14&size=600x300&sensor=false&maptype=hybrid',
            'alt_text' => 'Manassas, VA',
            'credit' => '',
            'img_link' => '',
        ),
        50000 => array(
            'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/yankeestadium.jpg',
            'alt_text' => 'Yankee Stadium',
            'credit' => 'Photo: Shawn Collins',
            'img_link' => 'https://www.flickr.com/photos/40683329@N00/4076671043',
        ),
        57000 => array(
            'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/greenland.jpg',
            'alt_text' => 'Greenland',
            'credit' => 'Photo: Christine Zenino',
            'img_link' => 'http://en.wikipedia.org/wiki/File:Tasiilaq_-_Greenland_summer_2009.jpg',
        ),
        259000 => array(
            'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/buffalo.jpg',
            'alt_text' => 'Taste of Buffalo',
            'credit' => 'Photo: Taste of Buffalo presented by TOPS',
            'img_link' => 'http://www.tasteofbuffalo.com/media-room-2013',
        ),
        334300 => array(
            'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/belize.jpg',
            'alt_text' => 'Belize City',
            'credit' => 'Photo: Studentbz',
            'img_link' => 'https://commons.wikimedia.org/wiki/File:Belize_City_Aerial_Shots.jpg',
        ),
        500000 => array(
            'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/woodstock.jpg',
            'alt_text' => 'Woodstock crowd',
            'credit' => 'Photo: Mark Goff',
            'img_link' => 'http://fi.wikipedia.org/wiki/Woodstock#mediaviewer/Tiedosto:Swami_opening.jpg',
        ),
        750000 => array(
            'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/louisville.jpg',
            'alt_text' => 'Louisville',
            'credit' => 'Photo: @potlikker',
            'img_link' => 'https://www.flickr.com/photos/southernfoodwaysalliance/2693340978',
        ),
        917100 => array(
            'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/delaware.jpg',
            'alt_text' => 'Rehoboth Beach in Delaware',
            'credit' => 'Photo: Dough4872',
            'img_link' => 'http://en.wikipedia.org/wiki/File:Rehoboth_Beach_at_Delaware_Avenue.JPG',
        )
    );

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        if ($this->shouldGenerateInsight($this->slug, $instance, $insight_date='today', $regenerate=false)) {
            $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
            $met_milestone = 0;
            foreach ($this->milestones as $count=>$text) {
                if ($user->follower_count >= $count && $user->follower_count < ($count*2)) {
                    $met_milestone = $count;
                } else if ($user->follower_count < $count) {
                    break;
                }
            }

            if ($met_milestone) {
                $baseline = $baseline_dao->getMostRecentInsightBaseline('follower_vis_last_run', $instance->id);
                if (!isset($baseline->value) || (isset($baseline->value) && $met_milestone > $baseline->value)) {
                    $baseline_dao->insertInsightBaseline('follower_vis_last_run', $instance->id, $met_milestone);
                    $insight = new Insight();
                    $insight->slug = $this->slug;
                    $insight->instance_id = $instance->id;
                    $insight->date = $this->insight_date;
                    $insight->filename = basename(__FILE__, ".php");
                    $insight->emphasis = Insight::EMPHASIS_HIGH;

                    if ($user->follower_count == $met_milestone) {
                        $headlines = array(
                            '%username has reached %total %followers!',
                            '%total people follow %username.',
                        );
                    } else {
                        $headlines = array(
                            '%username has passed %total %followers!',
                            'More than %total people follow %username.',
                        );
                    }

                    $insight->headline = $this->getVariableCopy($headlines,
                        array( 'total' => number_format($met_milestone)));
                    $insight->text = str_replace('%username', $this->username, $this->milestones[$met_milestone]);
                    if (isset($this->hero_images[$met_milestone])) {
                        $insight->setHeroImage($this->hero_images[$met_milestone]);
                    }
                    $this->insight_dao->insertInsight($insight);
                }
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }

}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('FollowCountVisualizerInsight');
