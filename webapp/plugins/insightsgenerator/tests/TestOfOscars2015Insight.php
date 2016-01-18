<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfOscars2015Insight.php
 *
 * Copyright (c) 2012-2016 Gina Trapani, Anil Dash
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
 * Test of Oscars2015Insight
 *
 * Test for the Oscars2015Insight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2016 Gina Trapani, Anil Dash
 * @author Anil Dash <anil[at]thinkup[dot]com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/oscars2015.php';

class TestOfOscars2015Insight extends ThinkUpInsightUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testDetectOscarWinnerReferences() {
        $mentioned_oscar_winner = Oscars2015Insight::detectOscarWinnerReferences("I *loved* watching birdman!");
        $this->assertEqual($mentioned_oscar_winner, 'Birdman');

        $mentioned_oscar_winner = Oscars2015Insight::detectOscarWinnerReferences("can't believe Citizenfour.");
        $this->assertEqual($mentioned_oscar_winner, 'Citizenfour');

        $mentioned_oscar_winner = Oscars2015Insight::detectOscarWinnerReferences("John Legend was terrific");
        $this->assertEqual($mentioned_oscar_winner, "John Legend");

    }

    public function testDetectOscarLoserReferences() {
        $mentioned_oscar_loser = Oscars2015Insight::detectOscarLoserReferences("I *loved* watching Captain America!");
        $this->assertEqual($mentioned_oscar_loser, 'Captain America: Winter Soldier');

        $mentioned_oscar_loser = Oscars2015Insight::detectOscarLoserReferences("can't believe meryl streep.");
        $this->assertEqual($mentioned_oscar_loser, "Meryl Streep");

        $mentioned_oscar_loser = Oscars2015Insight::detectOscarLoserReferences("Nightcrawler was terrific");
        $this->assertEqual($mentioned_oscar_loser, "Nightcrawler");
    }

    public function testCountOscarEventReferences() {
        $count = Oscars2015Insight::countOscarMentions("Everybody shut up, the Oscars are on!");
        $this->assertEqual($count, 1);

        $count = Oscars2015Insight::countOscarMentions("first Academy Awards I've watched in years.");
        $this->assertEqual($count, 1);

        $count = Oscars2015Insight::countOscarMentions(
        "Now that I'm back on Android, realizing just how undersung Google Now is. I want it everywhere.");
        $this->assertEqual($count, 0);
    }

    public function testOscars2015WinnerReference() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 3;
        $instance->network_username = 'catlady99';
        $instance->network = 'twitter';
        $builders = self::setUpPublicInsight($instance);

        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'Hope that inarritu wins.',
            'pub_date' => '2015-02-19',
            'author_username' => $instance->network_username,
            'network' => $instance->network
            )
        );

        $posts = array();
        $insight_plugin = new Oscars2015Insight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('oscars_2015', 3, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('Somebody was ready for the Oscars party!', $result->headline);
        $this->assertEqual('@catlady99 was talking about Alejandro González Iñárritu before the Academy Award winners were '
            . 'even announced!',
            $result->text);

        $controller = new InsightStreamController();
        $_GET['u'] = 'catlady99';
        $_GET['n'] = 'twitter';
        $_GET['d'] = date ('Y-m-d') ;
        $_GET['s'] = 'oscars_2015';
        $results = $controller->go();
        //output this to an HTML file to see the insight fully rendered
        $this->debug($results);
    }

    public function testOscars2015WinnerAndLoserReference() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 1;
        $instance->network_username = 'Cat Lady';
        $instance->network = 'facebook';
        $builders = self::setUpPublicInsight($instance);

        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'Really glad Benedict Cumberbatch lost.',
            'pub_date' => '2015-02-19',
            'author_username' => $instance->network_username,
            'network' => $instance->network
            )
        );

        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'Hope that Glory wins.',
            'pub_date' => '2015-02-25',
            'author_username' => $instance->network_username,
            'network' => $instance->network
            )
        );

        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'Hope that Julianne Moore wins.',
            'pub_date' => '2015-02-19',
            'author_username' => $instance->network_username,
            'network' => $instance->network
            )
        );

        $posts = array();
        $insight_plugin = new Oscars2015Insight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('oscars_2015', 1, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('Somebody was ready for the Oscars party!', $result->headline);
        $this->assertEqual('Cat Lady was talking about Julianne Moore before the Academy Award winners were '
            . 'even announced! Looks like the Academy voters might have missed Cat Lady\'s status updates about '
            . 'Benedict Cumberbatch, though.',
            $result->text);

        $controller = new InsightStreamController();
        $_GET['u'] = 'Cat Lady';
        $_GET['n'] = 'facebook';
        $_GET['d'] = date ('Y-m-d') ;
        $_GET['s'] = 'oscars_2015';
        $results = $controller->go();
        //output this to an HTML file to see the insight fully rendered
        $this->debug($results);
    }

    public function testOscars2015WinnerReferenceNotInLastMonth() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 3;
        $instance->network_username = 'catlady99';
        $instance->network = 'twitter';
        $builders = self::setUpPublicInsight($instance);

        //This post should not be counted because it's more than 30 days old
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'Cannot wait for Dallas Buyers Club',
            'pub_date' => '2015-01-01',
            'author_username' => $instance->network_username,
            'network' => $instance->network
            )
        );

        $posts = array();
        $insight_plugin = new Oscars2015Insight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('oscars_2015', 3, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNull($result);
    }

    public function testOscars2015OnlyALoserReference() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 1;
        $instance->network_username = 'Cat Lady';
        $instance->network = 'facebook';
        $builders = self::setUpPublicInsight($instance);

        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'That Jonah Hill sucks.',
            'pub_date' => '2015-02-19',
            'author_username' => $instance->network_username,
            'network' => $instance->network
            )
        );

        $posts = array();
        $insight_plugin = new Oscars2015Insight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('oscars_2015', 1, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNull($result);
    }
}
