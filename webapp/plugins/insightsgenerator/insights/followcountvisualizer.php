<?php
/*
 Plugin Name: Follow Count Visualizer
 Description: How many people follow you, described as a real-world group.
 */
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/followcountvisualizer.php
 *
 * Copyright (c) 2014-2016 Chris Moyer
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
 * @copyright 2014-2016 Chris Moyer
 * @author Chris Moyer <chris [at] inarow [dot] net>
 */
class FollowCountVisualizerInsight extends InsightPluginParent implements InsightPlugin {
    /**
     * Slug used for this insight
     */
    private $slug = 'follow_count_visualizer';
    /**
     * Follower count milestones
     */
    public $milestones = array(
        56 => array(
            "headline"=>"%username's followers would fill a yellow school bus",
            "text"=> "%username has %total followers&mdash;and they wouldn't all fit on a %thres-seat yellow bus.",
            "hero_image" => array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/bus.jpg',
                'alt_text' => 'Yellow school bus',
                'credit' => 'Photo: Ivy Dawned',
                'img_link' => 'https://www.flickr.com/photos/ivydawned/5460058051'
                ),
            ),
        115 => array(
            "headline"=>"%username has as many fans as the Rolling Stones",
            "text"=>"%username has %total followers, but only %thres people attended the Rolling Stones' first live performance.",
            "hero_image" => array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/stones.jpg',
                'alt_text' => 'Crowd at a concert',
                'credit' => 'Photo: Chris',
                'img_link' => 'https://www.flickr.com/photos/cr01/7392740268/',
                ),
            ),
        200 => array(
            "headline"=>"%username's followers would pack a New York City subway",
            "text"=>"%username has %total followers, but only %thres people fit in a typical subway car.",
            "hero_image" => array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/subway.jpg',
                'alt_text' => 'New York City subway car',
                'credit' => 'Photo: Julian Dunn',
                'img_link' => 'https://www.flickr.com/photos/juliandunn/6920197196',
                ),
            ),
        360 => array(
            "headline"=>"%username's followers outnumber the Mormon Tabernacle Choir",
            "text"=>"%username has %total followers, but there are only %thres singers in the Mormon Tabernacle Choir.",
            "hero_image" => array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/choir.jpg',
                'alt_text' => 'Mormon Tabernacle Choir',
                'credit' => '',
                'img_link' => '',
                ),
            ),
        400 => array(
            "headline"=>"%username's followers would fill a 747",
            "text"=>"Some of %username's %total followers would have to go on standby, because they'd fill a %thres-seat airplane to capacity.",
            "hero_image" => array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/747.jpg',
                'alt_text' => '747',
                'credit' => 'Photo: Aero Icarus',
                'img_link' => 'https://www.flickr.com/photos/aero_icarus/4707805048/',
                ),
            ),
        485 => array(
            "headline"=>"%username's followers outnumber the biggest marching band",
            "text"=>"%username has %total followers, but there are only %thres players in the largest collegiate marching band.",
            "hero_image" => array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/marchingband.jpg',
                'alt_text' => 'Marching band',
                'credit' => '',
                'img_link' => 'http://commons.wikimedia.org/wiki/File:Rosemount_High_School_marching_band.jpg',
                ),
            ),
        560 => array(
            "headline"=>"%username's followers would need more than 10 buses",
            "text"=>"%username has %total followers, but only %thres students would fill 10 yellow school buses.",
            "hero_image" => array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/buses.jpg',
                'alt_text' => 'Yellow school buses',
                'credit' => 'Photo: dhendrix73',
                'img_link' => 'https://www.flickr.com/photos/dhendrix/6906652333/',
                ),
            ),
        600 => array(
            "headline"=>"%username's follower count is greater than the population of Eminence, MO",
            "text"=>"%username has %total followers, but only %thres people live Eminence, Missouri.",
            "hero_image" => array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2015-02/small-town-duck-race.jpg',
                'alt_text' => 'Eminence, MO',
                'credit' => 'Photo: Geoff Bosco',
                'img_link' => 'https://www.flickr.com/photos/109434304@N07/13931759957/',
                ),
            ),
        1000 => array(
            "headline"=>"%username's followers would pack the Hammerstein Ballroom",
            "text"=>"%username has %total followers, but the Hammerstein Ballroom's max capacity is only %thres.",
            "hero_image" => array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/hammersteinballroom.jpg',
                'alt_text' => 'A crowd at the Hammerstein Ballroom',
                'credit' => 'Photo: Jorge Hernandez',
                'img_link' => 'https://www.flickr.com/photos/mediajorgenyc/3882364741',
                ),
            ),
        1510 => array(
            "headline"=>"%username's followers would fill the Apollo Theater",
            "text"=>"%username has %total followers, but only %thres people would fill all the seats at the
                Apollo Theater.",
            "hero_image" => array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/apollotheater.jpg',
                'alt_text' => 'Apollo Theater',
                'credit' => 'Photo: Hans Joachim Dudeck',
                'img_link' => 'http://commons.wikimedia.org/wiki/File:Apollo_Theater_Harlem_NYC_2010.JPG',
                ),
            ),
        2740 => array(
            "headline"=>"%username's followers would fill Lincoln Center",
            "text"=>"%username has %total followers, but only %thres people would fill all the seats in the concert
                hall at Lincoln Center.",
            "hero_image" => array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/lincolncenter.jpg',
                'alt_text' => 'Avery Fisher Hall at Lincoln Center',
                'credit' => 'Photo: Mikhail Klassen',
                'img_link' => 'https://en.wikipedia.org/wiki/File:Avery_fisher_hall.jpg',
                ),
            ),
        4815 => array(
            "headline"=>"%username's followers outnumber the student body at Wake Forest University",
            "text"=>"%username has %total followers, but only %thres undergraduates are enrolled at Wake Forest
                University.",
            "hero_image" => array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/wakeforest.jpg',
                'alt_text' => 'Wake Forest University',
                'credit' => 'Photo: JHMM13',
                'img_link' => 'http://en.wikipedia.org/wiki/File:WakeForestRolledQuad.jpg',
                ),
            ),
        5950 => array(
            "headline"=>"%username's followers would fill Radio City Music Hall",
            "text"=>"%username has %total followers, but there are only %thres seats at Radio City Music Hall.",
            "hero_image" => array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/radiocity.jpg',
                'alt_text' => 'Radio City Music Hall',
                'credit' => 'Photo: flickr4jazz',
                'img_link' => 'https://en.wikipedia.org/wiki/File:Radio_City_Music_Hall_3051638324_4a385c5623.jpg',
                ),
            ),
        6182 => array(
            "headline"=>"%username's followers outnumber the student body at Brown University",
            "text"=>"%username has %total followers, but there are only %thres undegraduates enrolled at Brown
                University.",
            "hero_image" => array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/graduates.jpg',
                'alt_text' => 'Graduates',
                'credit' => 'Photo: Sakeeb Sabakka',
                'img_link' => 'http://www.fotopedia.com/items/flickr-4647211575',
                ),
            ),
        6296 => array(
            "headline"=>"%username's followers are gonna need a bigger boat",
            "text"=>"%username has %total followers, but the world's largest cruise ships can only accomodate %thres passengers.",
            "hero_image" => array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2015-02/cruise-ship.jpg',
                'alt_text' => 'Cruise ships',
                'credit' => 'Photo: Jorge in Brazil',
                'img_link' => 'https://www.flickr.com/photos/85213921@N04/12540080855',
                ),
            ),
        7550 => array(
            "headline"=>"More people follow %username than live in Rhinebeck",
            "text"=>"%username has %total followers, but there are only %thres residents of Rhinebeck, New York.",
            "hero_image" => array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2015-02/rhinebeck.jpg',
                'alt_text' => 'Rhinebeck, NY',
                'credit' => 'Photo: Doug Kerr',
                'img_link' => 'https://www.flickr.com/photos/dougtone/3482245481',
                ),
            ),
        12500 => array(
            "headline"=>"%username's followers would fill Wembley Arena",
            "text"=>"%username has %total followers, but there are only %thres seats at Wembley Arena.",
            "hero_image" => array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2015-02/wembley.jpg',
                'alt_text' => 'Wembley Arena',
                'credit' => 'Photo: Tim Sheerman-Chase',
                'img_link' => 'https://www.flickr.com/photos/tim_uk/10353361694',
                ),
            ),
        17400 => array(
            "headline"=>"%username's followers would fill the Hollywood Bowl",
            "text"=>"%username has %total followers, but there are only %thres seats in the Hollywood Bowl.",
            "hero_image" => array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/hollywoodbowl.jpg',
                'alt_text' => 'Hollywood Bowl',
                'credit' => 'Photo: Matthew Field',
                'img_link' => 'http://commons.wikimedia.org/wiki/File:Hollywood_bowl_and_sign.jpg',
                ),
            ),
        28700 => array(
            "headline"=>"%username's followers outnumber UCLA's student body",
            "text"=>"%username has %total followers, but there are only %thres undergraduates enrolled at UCLA.",
            "hero_image" => array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/ucla.jpg',
                'alt_text' => 'UCLA vs Oregon',
                'credit' => 'Photo: Dave Cooper',
                'img_link' => 'https://www.flickr.com/photos/96414272@N00/2123934452',
                ),
            ),
        36000 => array(
            "headline"=>"%username's followers outnumber Boston Marathon runners",
            "text"=>"%username has %total followers, but only %thres people ran the 2014 Boston Marathon.",
            "hero_image" => array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/marathon.jpg',
                'alt_text' => 'Marathon runners',
                'credit' => '',
                'img_link' => '',
                ),
            ),
        40700 => array(
            "headline"=>"%username's follower count is greater than the population of Manassas",
            "text"=>"%username has %total followers, but only %thres people live in Manassas, Virgina.",
            "hero_image" => array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2015-02/manassas.jpg',
                'alt_text' => 'Manassas, VA',
                'credit' => 'Photo: Alan Kotok',
                'img_link' => 'https://www.flickr.com/photos/runneralan/14837893767',
                ),
            ),
        50000 => array (
            "headline"=>"%username's followers would fill Yankee Stadium",
            "text"=>"%username has %total followers, but only %thres fans can fit in Yankee Stadium.",
            "hero_image" => array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/yankeestadium.jpg',
                'alt_text' => 'Yankee Stadium',
                'credit' => 'Photo: Shawn Collins',
                'img_link' => 'https://www.flickr.com/photos/40683329@N00/4076671043',
            ),
        ),
        57000 => array (
            "headline"=>"More people follow %username than live in Greenland",
            "text"=>"%username has %total followers, but only %thres people live in Greenland.",
            "hero_image" => array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/greenland.jpg',
                'alt_text' => 'Greenland',
                'credit' => 'Photo: Christine Zenino',
                'img_link' => 'http://en.wikipedia.org/wiki/File:Tasiilaq_-_Greenland_summer_2009.jpg',
            ),
        ),
        259000 => array(
            "headline"=>"%username's follower count outnumbers the population of Buffalo",
            "text"=>"%username has %total followers, but only %thres people live in Buffalo, New York.",
            "hero_image" => array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/buffalo.jpg',
                'alt_text' => 'Taste of Buffalo',
                'credit' => 'Photo: Taste of Buffalo presented by TOPS',
                'img_link' => 'http://www.tasteofbuffalo.com/media-room-2016',
            ),
        ),
        334300 => array(
            "headline"=>"%username's follower count is larger than the population of Belize",
            "text"=>"%username has %total followers, but only %thres people live in Belize.",
            "hero_image" => array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/belize.jpg',
                'alt_text' => 'Belize City',
                'credit' => 'Photo: Studentbz',
                'img_link' => 'https://commons.wikimedia.org/wiki/File:Belize_City_Aerial_Shots.jpg',
            ),
        ),
        500000 => array(
            "headline"=>"More people follow %username than attended Woodstock",
            "text"=>"%username has %total followers&mdash;more than the estimated %thres in the crowd at Woodstock in 1969.",
            "hero_image" => array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/woodstock.jpg',
                'alt_text' => 'Woodstock crowd',
                'credit' => 'Photo: Mark Goff',
                'img_link' => 'http://fi.wikipedia.org/wiki/Woodstock#mediaviewer/Tiedosto:Swami_opening.jpg',
            ),
        ),
        750000 => array(
            "headline"=>"More people follow %username than live in Louisville",
            "text"=>"%username has %total followers, but only about %thres people live in Louisville, Kentucky.",
            "hero_image" => array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/louisville.jpg',
                'alt_text' => 'Louisville',
                'credit' => 'Photo: @potlikker',
                'img_link' => 'https://www.flickr.com/photos/southernfoodwaysalliance/2693340978',
            ),
        ),
        917100 => array(
            "headline"=>"More people follow %username than live in Delaware",
            "text"=>"%username has %total followers, but the entire population of Delaware is only about %thres people.",
            "hero_image" => array(
                'url' => 'https://www.thinkup.com/assets/images/insights/2014-05/delaware.jpg',
                'alt_text' => 'Rehoboth Beach in Delaware',
                'credit' => 'Photo: Dough4872',
                'img_link' => 'http://en.wikipedia.org/wiki/File:Rehoboth_Beach_at_Delaware_Avenue.JPG',
            ),
        ),
    );

    public function generateInsight(Instance $instance, User $user, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $user, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);

        if ($this->shouldGenerateInsight($this->slug, $instance, $insight_date='today', $regenerate=false)) {
            $baseline_dao = DAOFactory::getDAO('InsightBaselineDAO');
            $met_milestone = 0;
            foreach ($this->milestones as $count=>$text) {
                if ($user->follower_count > $count && $user->follower_count < ($count*2)) {
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

                    $insight->headline = $this->getVariableCopy(array($this->milestones[$met_milestone]['headline']),
                        array( 'total' => number_format($user->follower_count)));
                    $insight->text = $this->getVariableCopy(array($this->milestones[$met_milestone]['text']),
                        array('total'=>number_format($user->follower_count), 'thres'=>number_format($met_milestone)));
                    $insight->setHeroImage($this->milestones[$met_milestone]["hero_image"]);
                    $this->insight_dao->insertInsight($insight);
                }
            }
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('FollowCountVisualizerInsight');
