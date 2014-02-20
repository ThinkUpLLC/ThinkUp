<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfOlympics2014Insight.php
 *
 * Copyright (c) 2012-2015 Gina Trapani, Anil Dash
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
 * Test of Olympics2014Insight
 *
 * Test for the Olympics2014Insight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2014 Gina Trapani, Anil Dash
 * @author Anil Dash <anil[at]thinkup[dot]com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/olympics2014.php';

class TestOfOlympics2014Insight extends ThinkUpInsightUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testcountOlympicEventReferences() {
        $count = Olympics2014Insight::countOlympicReferences("I *loved* watching the Olympics!");
        $this->assertEqual($count, 1);

        $count = Olympics2014Insight::countOlympicReferences("These Sochi opening ceremonies are a mess.");
        $this->assertEqual($count, 2);

        $count = Olympics2014Insight::countOlympicReferences(
        "Now that I'm back on Android, realizing just how under sung Google Now is. I want it everywhere.");
        $this->assertEqual($count, 0);
    }

    public function testOlympic2014OneEventReference() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 5;
        $instance->network_username = 'Johnny Carson';
        $instance->network = 'facebook';
        $builders = self::setUpPublicInsight($instance);

        $posts = array();
        $posts[] = new Post(array(
            'post_text' => 'These sochi games are kind of a cluster already.',
            'pub_date' => '2014-02-07'
        ));

        $insight_plugin = new Olympics2014Insight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('olympics_2014', 5, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('Do they give out medals for status updates during the Games?', $result->headline);
        $this->assertEqual('Johnny Carson referenced the Olympics just as the whole world\'s attention was ' .
            'focused on the games, and that\'s a pretty great way to join a global conversation.',
            $result->text);
        $this->assertEqual('https://pbs.twimg.com/media/Bf5LVvHCMAEwdC0.jpg:large', $result->header_image);

        $controller = new InsightStreamController();
        $_GET['u'] = 'Johnny Carson';
        $_GET['n'] = 'facebook';
        $_GET['d'] = date ('Y-m-d');
        $_GET['s'] = 'olympics_2014';
        $results = $controller->go();
        //output this to an HTML file to see the insight fully rendered
        $this->debug($results);
    }

    public function testOlympic2014MultipleEventReferencesUppercase() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 5;
        $instance->network_username = 'Johnny Carson';
        $instance->network = 'facebook';
        $builders = self::setUpPublicInsight($instance);

        $posts = array();
        $posts[] = new Post(array(
            'post_text' => 'These Sochi games are kind of a cluster already.',
            'pub_date' => '2014-02-07'
        ));
        $posts[] = new Post(array(
            'post_text' => 'These were amazing Opening Ceremonies, the best since Beijing!',
            'pub_date' => '2014-02-07'
        ));
        $posts[] = new Post(array(
            'post_text' => 'The Olympic Closing Ceremony is the greatest global event since Beyonce\'s album',
            'pub_date' => '2014-02-07'
        ));
        $posts[] = new Post(array(
            'post_text' => 'Hope nothing else goes wrong! They got a Bronze Medal in mistakes. #Sochi2014',
            'pub_date' => '2014-02-19'
        ));

        $insight_plugin = new Olympics2014Insight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('olympics_2014', 5, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('Do they give out medals for status updates during the Games?', $result->headline);
        $this->assertEqual('Johnny Carson referenced the Olympics 6 times since they started, which is kind of like ' .
            'winning 6 gold medals in Facebook, right?',
            $result->text);
        $this->assertEqual('https://pbs.twimg.com/media/Bf5LVvHCMAEwdC0.jpg:large', $result->header_image);

        $controller = new InsightStreamController();
        $_GET['u'] = 'Johnny Carson';
        $_GET['n'] = 'facebook';
        $_GET['d'] = date ('Y-m-d') ;
        $_GET['s'] = 'olympics_2014';
        $results = $controller->go();
        //output this to an HTML file to see the insight fully rendered
        $this->debug($results);
    }

    public function testOlympic2014MultipleEventReferencesLowercase() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 3;
        $instance->network_username = 'catlady99';
        $instance->network = 'twitter';
        $builders = self::setUpPublicInsight($instance);

        $posts = array();
        $posts[] = new Post(array(
            'post_text' => 'These were amazing opening ceremonies in sochi!',
            'pub_date' => '2014-02-07'
        ));
        $posts[] = new Post(array(
            'post_text' => 'The olympic closing ceremony is the greatest global event since Beyonce\'s album',
            'pub_date' => '2014-02-07'
        ));
        $posts[] = new Post(array(
            'post_text' => 'Hope nothing else goes wrong! #sochi2014',
            'pub_date' => '2014-02-19'
        ));

        $insight_plugin = new Olympics2014Insight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('olympics_2014', 3, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('Do they give out medals for tweets during the Games?', $result->headline);
        $this->assertEqual('@catlady99 referenced the Olympics 5 times since they started, which is kind of like ' .
            'winning 5 gold medals in Twitter, right?',
            $result->text);
        $this->assertEqual('https://pbs.twimg.com/media/Bf5LVvHCMAEwdC0.jpg:large', $result->header_image);

        $controller = new InsightStreamController();
        $_GET['u'] = 'Johnny Carson';
        $_GET['n'] = 'facebook';
        $_GET['d'] = date ('Y-m-d') ;
        $_GET['s'] = 'olympics_2014';
        $results = $controller->go();
        //output this to an HTML file to see the insight fully rendered
        $this->debug($results);
    }


}
