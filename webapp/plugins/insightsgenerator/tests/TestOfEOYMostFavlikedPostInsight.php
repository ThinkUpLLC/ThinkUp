<?php
/**
 *
 * webapp/plugins/insightsgenerator/tests/TestOfEOYMostFavlikedPostInsight.php
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
 *
 * EOYMostFavlikedPost (name of file)
 *
 * Description of what this class does
 *
 * Copyright (c) 2014 Adam Pash
 *
 * @author Adam Pash adam.pash@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Adam Pash
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/eoymostfavlikedpost.php';

class TestOfEOYMostFavlikedPostInsight extends ThinkUpInsightUnitTestCase {

    public function setUp() {
        parent::setUp();

        $this->instance = new Instance();
        $this->instance->id = 10;
        $this->instance->network_username = 'buffy';
        $this->instance->network = 'twitter';

    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new EOYMostFavlikedPostInsight();
        $this->assertIsA($insight_plugin, 'EOYMostFavlikedPostInsight' );
    }

    public function testTopThreePosts() {
        $insight_plugin = new EOYMostFavlikedPostInsight();

        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'This is very liked',
            'pub_date' => '2014-02-07',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'favlike_count_cache' => 100
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'This is pretty well liked',
            'pub_date' => '2014-02-07',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'favlike_count_cache' => 50
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'This is least liked',
            'pub_date' => '2014-02-07',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'favlike_count_cache' => 25
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'These Sochi games are kind of a cluster already.',
            'pub_date' => '2014-02-07',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'favlike_count_cache' => 1
            )
        );

        // test that query returns 3 results sorted by retweets descending
        $posts = $insight_plugin->topThreeThisYear($this->instance);
        $this->assertEqual(3, sizeof($posts));
        $this->assertEqual(100, $posts[0]->favlike_count_cache);
        $this->assertEqual(50, $posts[1]->favlike_count_cache);
        $this->assertEqual(25, $posts[2]->favlike_count_cache);

        // test only counts from this year
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'These Sochi games are kind of a cluster already.',
            'pub_date' => '2013-12-07',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'favlike_count_cache' => 1000
            )
        );

        $posts = $insight_plugin->topThreeThisYear($this->instance);
        $this->assertEqual(100, $posts[0]->favlike_count_cache);
    }

    public function testTwitterNormalCase() {
        // Set up and test normal twitter case
        $builders = self::setUpPublicInsight($this->instance);
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'This is very liked',
            'pub_date' => '2014-02-07',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'favlike_count_cache' => 100
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'This is pretty well liked',
            'pub_date' => '2014-02-07',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'favlike_count_cache' => 50
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'This is least liked',
            'pub_date' => '2014-02-07',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'favlike_count_cache' => 25
            )
        );

        $posts = array();
        $insight_plugin = new EOYMostFavlikedPostInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('eoy_most_favliked', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $this->assertEqual("@buffy's most-faved tweet of $year", $result->headline);
        $this->assertEqual("In the Walk of Fame that is @buffy's Twitter stream, " .
            "these fan favorites earned the most stars in $year.", $result->text);

        $this->dumpRenderedInsight($result, "Normal case, Twitter");
    }

    public function testFacebookNormalCase() {
        // set up and test facebook
        $this->instance->network_username = 'Buffy Summers';
        $this->instance->network = 'facebook';
        $builders = self::setUpPublicInsight($this->instance);
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'This is very liked',
            'pub_date' => '2014-02-07',
            'author_username' => $this->instance->network_username,
            'author_fullname' => $this->instance->network_username,
            'network' => $this->instance->network,
            'favlike_count_cache' => 100
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'This is pretty well liked',
            'pub_date' => '2014-02-07',
            'author_username' => $this->instance->network_username,
            'author_fullname' => $this->instance->network_username,
            'network' => $this->instance->network,
            'favlike_count_cache' => 50
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'This is least liked',
            'pub_date' => '2014-02-07',
            'author_username' => $this->instance->network_username,
            'author_fullname' => $this->instance->network_username,
            'network' => $this->instance->network,
            'favlike_count_cache' => 1
            )
        );

        $posts = array();
        $insight_plugin = new EOYMostFavlikedPostInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('eoy_most_favliked', $this->instance->id, $today);
        // $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $this->assertEqual("Buffy Summers's most-liked status update of $year", $result->headline);
        $this->assertEqual("Liked it? Nah. They LOVED it. These status updates had " .
            "Buffy Summers's friends mashing the thumbs-up button the most in $year.",
            $result->text);

        $this->dumpRenderedInsight($result, "Normal case: Facebook");
        // $this->dumpAllHTML();
    }

    // // Text variations in amount: if matches == 1
    public function testTwitterOneMatch() {
        // on twitter
        // set up single popular post
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'This is very liked',
            'pub_date' => '2014-02-07',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'favlike_count_cache' => 100
            )
        );

        $posts = array();
        $insight_plugin = new EOYMostFavlikedPostInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('eoy_most_favliked', $this->instance->id, $today);
        // $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $this->assertEqual("@buffy's most-faved tweet of $year", $result->headline);
        $this->assertEqual("In the Walk of Fame that is @buffy's Twitter stream, " .
            "this fan favorite earned the most stars in $year.", $result->text);

        $this->dumpRenderedInsight($result, "One match: Twitter");
    }

    public function testFacebookOneMatch() {
        // now test facebook
        $this->instance->network_username = 'Buffy Summers';
        $this->instance->network = 'facebook';
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'This is very liked',
            'pub_date' => '2014-02-07',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'favlike_count_cache' => 100
            )
        );

        $posts = array();
        $insight_plugin = new EOYMostFavlikedPostInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('eoy_most_favliked', $this->instance->id, $today);
        // $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $this->assertEqual("Buffy Summers's most-liked status update of $year", $result->headline);
        $this->assertEqual("Liked it? Nah. They LOVED it. This status update had " .
            "Buffy Summers's friends mashing the thumbs-up button the most in $year.",
            $result->text);

        $this->dumpRenderedInsight($result, "One match: Facebook");
    }

    // // test if user had no likes this year
    public function testTwitterNoMatches() {
        // on twitter
        // set up single popular post
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'This is very liked',
            'pub_date' => '2014-02-07',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'favlike_count_cache' => 0
            )
        );

        $posts = array();
        $insight_plugin = new EOYMostFavlikedPostInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('eoy_most_favliked', $this->instance->id, $today);
        // $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $this->assertEqual("What's in a fave?", $result->headline);
        $this->assertEqual("@buffy didn't get any faves in $year, which is crazy! " .
            "Give @thinkup a mention and we'd be happy to change that.", $result->text);

        $this->dumpRenderedInsight($result, "No matches: Twitter");
    }

    public function testFacebookNoMatches() {
        // now test facebook
        $this->instance->network_username = 'Buffy Summers';
        $this->instance->network = 'facebook';
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'This is very liked',
            'pub_date' => '2014-02-07',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'favlike_count_cache' => 0
            )
        );

        $posts = array();
        $insight_plugin = new EOYMostFavlikedPostInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('eoy_most_favliked', $this->instance->id, $today);
        // $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $this->assertEqual("Like, what's the deal?", $result->headline);
        $this->assertEqual("No one liked Buffy Summers's status updates on Facebook " .
            "in $year, but no biggie: We like Buffy Summers plenty.", $result->text);

        $this->dumpRenderedInsight($result, "No matches: Facebook");
    }

    public function testFavlikeCount() {
        // test twitter
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'This is very liked',
            'pub_date' => '2014-02-07',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'favlike_count_cache' => 100
            )
        );

        $posts = array();
        $insight_plugin = new EOYMostFavlikedPostInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('eoy_most_favliked', $this->instance->id, $today);
        $this->assertPattern("/100 favorites/", $result->related_data);

        // test one favlike
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'This is very liked',
            'pub_date' => '2014-02-07',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'favlike_count_cache' => 1
            )
        );

        $posts = array();
        $insight_plugin = new EOYMostFavlikedPostInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('eoy_most_favliked', $this->instance->id, $today);
        $this->assertPattern("/1 favorite/", $result->related_data);
        $this->assertNoPattern("/1 favorites/", $result->related_data);

        // test facebook
        $this->instance->network_username = 'Buffy Summers';
        $this->instance->network = 'facebook';
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'This is very liked',
            'pub_date' => '2014-02-07',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'favlike_count_cache' => 100
            )
        );

        $posts = array();
        $insight_plugin = new EOYMostFavlikedPostInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('eoy_most_favliked', $this->instance->id, $today);
        $this->assertPattern("/100 likes/", $result->related_data);
    }

    private function dumpAllHTML() {
        $controller = new InsightStreamController();
        $_GET['u'] = $this->instance->network_username;
        $_GET['n'] = $this->instance->network;
        $_GET['d'] = date ('Y-m-d');
        $_GET['s'] = 'eoy_most_favliked';
        $results = $controller->go();
        //output this to an HTML file to see the insight fully rendered
        $this->debug($results);
    }

    private function dumpRenderedInsight($result, $message) {
        if (isset($message)) {
            $this->debug("<h4 style=\"text-align: center; margin-top: 20px;\">$message</h4>");
        }
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }

}

