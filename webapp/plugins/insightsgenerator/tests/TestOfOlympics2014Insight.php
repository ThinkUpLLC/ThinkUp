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

class TestOfOlympics2014Insight extends ThinkUpUnitTestCase {

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
        $instance->id = 2;
        $instance->network_username = 'Johnny Carson';
        $instance->network = 'facebook';

        $insight_builder = FixtureBuilder::build('insights', array('id'=>30, 'instance_id'=>2,
        'slug'=> 'olympics_2014', 'date'=>date ('Y-m-d') ));

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
        $result = $insight_dao->getInsight('olympics_2014', 2, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('Seems like you\'ve been training for the Olympic conversation.', $result->headline);
        $this->assertEqual('Johnny Carson mentioned the Olympics already and the games are just getting started!',
            $result->text);
        $this->assertEqual('https://pbs.twimg.com/media/Bf5LVvHCMAEwdC0.jpg:large', $result->header_image);
    }

    public function testOlympic2014MultipleEventReferences() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 2;
        $instance->network_username = 'Johnny Carson';
        $instance->network = 'facebook';

        $insight_builder = FixtureBuilder::build('insights', array('id'=>30, 'instance_id'=>2,
        'slug'=> 'olympics_2014', 'date'=>date ('Y-m-d') ));

        $posts = array();
        $posts[] = new Post(array(
            'post_text' => 'These sochi games are kind of a cluster already.',
            'pub_date' => '2014-02-07'
        ));
        $posts[] = new Post(array(
            'post_text' => 'These were amazing opening ceremonies, the best since Beijing!',
            'pub_date' => '2014-02-07'
        ));
        $posts[] = new Post(array(
            'post_text' => 'The Olympic opening ceremony is the greatest global event since Beyonce\'s album',
            'pub_date' => '2014-02-07'
        ));

        $insight_plugin = new Olympics2014Insight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('olympics_2014', 2, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('Seems like you\'ve been training for the Olympic conversation.', $result->headline);
        $this->assertEqual('Johnny Carson mentioned the Olympics 4 times and the games are just getting started!',
            $result->text);
        $this->assertEqual('https://pbs.twimg.com/media/Bf5LVvHCMAEwdC0.jpg:large', $result->header_image);
    }

}
