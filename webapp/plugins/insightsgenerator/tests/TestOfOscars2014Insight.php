<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfOscars2014Insight.php
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
 * Test of Oscars2014Insight
 *
 * Test for the Oscars2014Insight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2014 Gina Trapani, Anil Dash
 * @author Anil Dash <anil[at]thinkup[dot]com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/oscars2014.php';

class TestOfOscars2014Insight extends ThinkUpInsightUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testDetectOscarTopicReferences() {
        $mentioned_oscar_topic = Oscars2014Insight::detectOscarTopicReferences("I *loved* watching american hustle!");
        $this->assertEqual($mentioned_oscar_topic, "American Hustle");

        $mentioned_oscar_topic = Oscars2014Insight::detectOscarTopicReferences("can't believe Captain Phillips.");
        $this->assertEqual($mentioned_oscar_topic, "Captain Phillips");

        $mentioned_oscar_topic = Oscars2014Insight::detectOscarTopicReferences("Dallas Buyers club was terrific");
        $this->assertEqual($mentioned_oscar_topic, "Dallas Buyers Club");

        $mentioned_oscar_topic = Oscars2014Insight::detectOscarTopicReferences("oscar for philomena, yo!");
        $this->assertEqual($mentioned_oscar_topic, "Philomena");

        $mentioned_oscar_topic = Oscars2014Insight::detectOscarTopicReferences("So glad that 12 Years a Slave won.");
        $this->assertEqual($mentioned_oscar_topic, "12 Years a Slave");

        $mentioned_oscar_topic = Oscars2014Insight::detectOscarTopicReferences("The Wolf of Wall Street was awful.");
        $this->assertEqual($mentioned_oscar_topic, "Wolf of Wall Street");

        $mentioned_oscar_topic = Oscars2014Insight::detectOscarTopicReferences("American Hustle was great.");
        $this->assertEqual($mentioned_oscar_topic, "American Hustle");

        $mentioned_oscar_topic = Oscars2014Insight::detectOscarTopicReferences(
        "Now that I'm back on Android, realizing just how undersung Google Now is. I want it everywhere.");
        $this->assertEqual($mentioned_oscar_topic, 0);

    }

    public function testCountOscarEventReferences() {

        $count = Oscars2014Insight::countOscarMentions("Everybody shut up, the Oscars are on!");
        $this->assertEqual($count, 1);

        $count = Oscars2014Insight::countOscarMentions("first Academy Awards I've watched in years.");
        $this->assertEqual($count, 1);

        $count = Oscars2014Insight::countOscarMentions(
        "Now that I'm back on Android, realizing just how undersung Google Now is. I want it everywhere.");
        $this->assertEqual($count, 0);

    }

    public function testOscar2014OneEventReference() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 5;
        $instance->network_username = 'Johnny Carson';
        $instance->network = 'facebook';
        $builders = self::setUpPublicInsight($instance);

        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'So glad that American Hustle won!',
            'pub_date' => '2014-02-07',
            'author_username' => $instance->network_username,
            'network' => $instance->network
            )
        );

        $posts = array();
        $insight_plugin = new Oscars2014Insight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('oscars_2014', 5, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('Can we crash your Oscar party next year?', $result->headline);
        $this->assertEqual('Johnny Carson mentioned the Oscars just as the whole world\'s attention was ' .
            'focused on the Games. That\'s a pretty great way to join a global conversation.',
            $result->text);

        $controller = new InsightStreamController();
        $_GET['u'] = 'Johnny Carson';
        $_GET['n'] = 'facebook';
        $_GET['d'] = date ('Y-m-d');
        $_GET['s'] = 'oscars_2014';
        $results = $controller->go();
        //output this to an HTML file to see the insight fully rendered
        $this->debug($results);
    }

}
