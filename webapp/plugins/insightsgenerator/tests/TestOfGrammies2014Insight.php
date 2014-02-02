<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfGrammies2014Insight.php
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
 * Test of Grammies2014Insight
 *
 * Test for the Grammies2014Insight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2014 Gina Trapani, Anil Dash
 * @author Anil Dash <anil[at]thinkup[dot]com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/grammies2014.php';

class TestOfGrammies2014Insight extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testcountGrammyEventReferences() {
        $count = Grammies2014Insight::countGrammyReferences("I *loved* watching the Grammies!");
        $this->assertEqual($count, 1);

        $count = Grammies2014Insight::countGrammyReferences(
        "Now that I'm back on Android, realizing just how under sung Google Now is. I want it everywhere.");
        $this->assertEqual($count, 0);
    }

    public function testcountGrammy2014ArtistReferences() {
        $count = Grammies2014Insight::countGrammy2014ArtistReferences(
        "I can't believe Kacey Musgraves didn't win.");
        $this->assertEqual($count, 1);

        $count = Grammies2014Insight::countGrammy2014ArtistReferences(
        "Kendrick Lamar definitely didn't deserve to lose to Macklemore.");
        $this->assertEqual($count, 2);

        $count = Grammies2014Insight::countGrammy2014ArtistReferences(
        "What is Robin Thicke doing up there with Pharrell?");
        $this->assertEqual($count, 2);
    }

    public function testcountGrammy2014EventAndArtistReferences() {
        $artist_count = Grammies2014Insight::countGrammy2014ArtistReferences(
        "What is Robin Thicke doing up there with Pharrell?");

        $event_count = Grammies2014Insight::countGrammyReferences(
        "I *loved* watching the Grammies!");

        $count = $event_count + $artist_count;
        $this->assertEqual($count, 3);

    }

    public function testGrammy2014EventNoArtist() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

        $insight_builder = FixtureBuilder::build('insights', array('id'=>30, 'instance_id'=>10,
        'slug'=> 'grammies_2014', 'date'=>date ('Y-m-d') ));

        $posts = array();
        $posts[] = new Post(array(
            'post_text' => 'So sick of people tweeting about the Grammies.',
            'pub_date' => '2014-01-26'
        ));

        $insight_plugin = new Grammies2014Insight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('grammies_2014', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('Always a good idea to jump into the Grammy conversation.', $result->headline);
        $this->assertEqual('@testeriffic mentioned the Grammies ', $result->text);
    }

    public function testGrammy2014EventAndOneArtist() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';

        $insight_builder = FixtureBuilder::build('insights', array('id'=>30, 'instance_id'=>10,
        'slug'=> 'grammies_2014', 'date'=>date ('Y-m-d') ));

        $posts = array();
        $posts[] = new Post(array(
            'post_text' => 'So sick of people tweeting about the Grammies.',
            'pub_date' => '2014-01-26'
        ));
        $posts[] = new Post(array(
            'post_text' => 'I can\'t believe Kacey Musgraves didn\'t win.',
            'pub_date' => '2014-01-26'
        ));

        $insight_plugin = new Grammies2014Insight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('grammies_2014', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('Always a good idea to jump into the Grammy conversation.', $result->headline);
        $this->assertEqual('@testeriffic mentioned the Grammies ', $result->text);
    }

    public function testGrammy2014EventAndMultipleArtists() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'Johnny Carson';
        $instance->network = 'facebook';

        $insight_builder = FixtureBuilder::build('insights', array('id'=>30, 'instance_id'=>10,
        'slug'=> 'grammies_2014', 'date'=>date ('Y-m-d') ));

        $posts = array();
        $posts[] = new Post(array(
            'post_text' => 'So sick of people tweeting about the Grammies.',
            'pub_date' => '2014-01-26'
        ));
        $posts[] = new Post(array(
            'post_text' => 'I can\'t believe Kacey Musgraves didn\'t win.',
            'pub_date' => '2014-01-26'
        ));
        $posts[] = new Post(array(
            'post_text' => 'Kendrick Lamar definitely didn\'t deserve to lose to Macklemore.',
            'pub_date' => '2014-01-26'
        ));
        $posts[] = new Post(array(
            'post_text' => 'What is Robin Thicke doing up there with Pharrell?',
            'pub_date' => '2014-01-26'
        ));

        $insight_plugin = new Grammies2014Insight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('grammies_2014', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('Always a good idea to jump into the Grammy conversation.', $result->headline);
        $this->assertEqual('Johnny Carson mentioned the Grammies and talked about a couple of artists, too.',
            $result->text);
    }

    public function testGrammy2014NoEventAndMultipleArtists() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 2;
        $instance->network_username = 'Johnny Carson';
        $instance->network = 'facebook';

        $insight_builder = FixtureBuilder::build('insights', array('id'=>30, 'instance_id'=>2,
        'slug'=> 'grammies_2014', 'date'=>date ('Y-m-d') ));

        $posts = array();
        $posts[] = new Post(array(
            'post_text' => 'I can\'t believe Kacey Musgraves didn\'t win.',
            'pub_date' => '2014-01-26'
        ));
        $posts[] = new Post(array(
            'post_text' => 'Kendrick Lamar definitely didn\'t deserve to lose to Macklemore.',
            'pub_date' => '2014-01-26'
        ));
        $posts[] = new Post(array(
            'post_text' => 'What is Robin Thicke doing up there with Pharrell?',
            'pub_date' => '2014-01-26'
        ));

        $insight_plugin = new Grammies2014Insight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('grammies_2014', 2, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('Always a good idea to jump into the Grammy conversation.', $result->headline);
        $this->assertEqual('Johnny Carson mentioned some of this year\'s biggest Grammy acts.',
            $result->text);
    }

}
