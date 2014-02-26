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

    public function testCountOscarEventReferences() {
        $count = Oscars2014Insight::countOscarReferences("I *loved* watching American Hustle!");
        $this->assertEqual($count, 1);

        $count = Oscars2014Insight::countOscarReferences("can't believe Captain Phillips was even nominated");
        $this->assertEqual($count, 1);

        $count = Oscars2014Insight::countOscarReferences("Dallas Buyers Club was terrific");
        $this->assertEqual($count, 1);

        $count = Oscars2014Insight::countOscarReferences("oscar for Philomena, yo!");
        $this->assertEqual($count, 2);

        $count = Oscars2014Insight::countOscarReferences("So glad that 12 Years a Slave won.");
        $this->assertEqual($count, 1);

        $count = Oscars2014Insight::countOscarReferences("The Wolf of Wall Street was awful.");
        $this->assertEqual($count, 1);

        $count = Oscars2014Insight::countOscarReferences("Everybody shut up, the Oscars are on!");
        $this->assertEqual($count, 1);

        $count = Oscars2014Insight::countOscarReferences("This is the first Academy Awards I've watched in years.");
        $this->assertEqual($count, 1);

        $count = Oscars2014Insight::countOscarReferences(
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
            'post_text' => 'These Oscars are kind of a cluster already.',
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
        $this->assertEqual('Do they give out medals for status updates?', $result->headline);
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

    public function testOscar2014MultipleEventReferencesUppercase() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 5;
        $instance->network_username = 'Johnny Carson';
        $instance->network = 'facebook';
        $builders = self::setUpPublicInsight($instance);

        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'These Sochi games are kind of a cluster already.',
            'pub_date' => '2014-02-07',
            'author_username' => $instance->network_username,
            'network' => $instance->network
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'These were amazing Opening Ceremonies, the best since Beijing!',
            'pub_date' => '2014-02-07',
            'author_username' => $instance->network_username,
            'network' => $instance->network
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'The Oscar Closing Ceremony is the greatest global event since Beyonce\'s album',
            'pub_date' => '2014-02-07',
            'author_username' => $instance->network_username,
            'network' => $instance->network
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'Hope nothing else goes wrong! They got a Bronze Medal in mistakes. #Sochi2014',
            'pub_date' => '2014-02-19',
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
        $this->assertEqual('Do they give out medals for status updates?', $result->headline);
        $this->assertEqual('Johnny Carson mentioned the Oscars 6 times since they started. ' .
            'That\'s kind of like winning 6 gold medals in Facebook, right?', $result->text);

        $controller = new InsightStreamController();
        $_GET['u'] = 'Johnny Carson';
        $_GET['n'] = 'facebook';
        $_GET['d'] = date ('Y-m-d') ;
        $_GET['s'] = 'oscars_2014';
        $results = $controller->go();
        //output this to an HTML file to see the insight fully rendered
        $this->debug($results);
    }

    public function testOscar2014MultipleEventReferencesLowercase() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 3;
        $instance->network_username = 'catlady99';
        $instance->network = 'twitter';
        $builders = self::setUpPublicInsight($instance);

        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'These were amazing opening ceremonies in sochi!',
            'pub_date' => '2014-02-07',
            'author_username' => $instance->network_username,
            'network' => $instance->network
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'The oscar closing ceremony is the greatest global event since Beyonce\'s album',
            'pub_date' => '2014-02-07',
            'author_username' => $instance->network_username,
            'network' => $instance->network
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'Hope nothing else goes wrong! #sochi2014',
            'pub_date' => '2014-02-19',
            'author_username' => $instance->network_username,
            'network' => $instance->network
            )
        );
        //This post should not be counted because it's more than 30 days old
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'Cannot wait for Sochi games',
            'pub_date' => '2014-01-01',
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
        $result = $insight_dao->getInsight('oscars_2014', 3, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('Do they give out medals for tweets?', $result->headline);
        $this->assertEqual('@catlady99 mentioned the Oscars 5 times since they started. That\'s kind of like ' .
            'winning 5 gold medals in Twitter, right?',
            $result->text);

        $controller = new InsightStreamController();
        $_GET['u'] = 'catlady99';
        $_GET['n'] = 'twitter';
        $_GET['d'] = date ('Y-m-d') ;
        $_GET['s'] = 'oscars_2014';
        $results = $controller->go();
        //output this to an HTML file to see the insight fully rendered
        $this->debug($results);
    }

    public function testOscar2014InsightRegeneration() {
        // Get data ready that insight requires
        $instance = new Instance();
        $instance->id = 3;
        $instance->network_username = 'catlady99';
        $instance->network = 'twitter';
        $builders = self::setUpPublicInsight($instance);

        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'These were amazing opening ceremonies in sochi!',
            'pub_date' => '2014-02-07',
            'author_username' => $instance->network_username,
            'network' => $instance->network
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'The oscar closing ceremony is the greatest global event since Beyonce\'s album',
            'pub_date' => '2014-02-07',
            'author_username' => $instance->network_username,
            'network' => $instance->network
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'Hope nothing else goes wrong! #sochi2014',
            'pub_date' => '2014-02-19',
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
        $result = $insight_dao->getInsight('oscars_2014', 3, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual('Do they give out medals for tweets?', $result->headline);
        $this->assertEqual('@catlady99 mentioned the Oscars 5 times since they started. That\'s kind of like ' .
            'winning 5 gold medals in Twitter, right?',
            $result->text);

        //Add a new post that should update the insight with 2 more mentions
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'Great closing ceremony! #sochi2014',
            'pub_date' => '2014-02-21',
            'author_username' => $instance->network_username,
            'network' => $instance->network
            )
        );
        $insight_plugin->generateInsight($instance, $posts, 3);
        // Assert that insight got updated
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('oscars_2014', 3, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertEqual('@catlady99 mentioned the Oscars 7 times since they started. That\'s kind of like ' .
            'winning 7 gold medals in Twitter, right?',
            $result->text);

        $controller = new InsightStreamController();
        $_GET['u'] = 'catlady99';
        $_GET['n'] = 'twitter';
        $_GET['d'] = date ('Y-m-d') ;
        $_GET['s'] = 'oscars_2014';
        $results = $controller->go();
        //output this to an HTML file to see the insight fully rendered
        $this->debug($results);
    }
}
