<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfEOYLOLCountInsight.php
 *
 * Copyright (c) 2012-2014 Gina Trapani
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
 * Test of EOYLOLCountInsight
 *
 * Test for the EOYLOLCountInsight class.
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
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/eoylolcount.php';

class TestOfEOYLOLCountInsight extends ThinkUpInsightUnitTestCase {

    public function setUp(){
        parent::setUp();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'ev';
        $instance->author_id = '18';
        $instance->network_user_id = '18';
        $instance->network = 'twitter';
        $this->instance = $instance;
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testLOLCount() {
        $insight_plugin = new EOYLOLCountInsight();
        $year = Date('Y');

        // posts with LOLs
        for ($i=0; $i<5; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'lmao, that was a funny post!',
                    'pub_date' => "$year-01-01",
                    'author_username' => $this->instance->network_username,
                    'author_user_id' => $this->instance->network_user_id,
                    'network' => $this->instance->network,
                )
            );
        }

        // posts without LOLs
        for ($i=0; $i<5; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'This is a post',
                    'pub_date' => "$year-01-01",
                    'author_username' => $this->instance->network_username,
                    'author_user_id' => $this->instance->network_user_id,
                    'network' => $this->instance->network,
                )
            );
        }

        $post_dao = DAOFactory::getDAO('PostDAO');
        $posts = $post_dao->getThisYearOfPostsIterator(
            $author_id = $this->instance->network_user_id,
            $network = $this->instance->network
        );

        $count = 0;
        foreach($posts as $key => $value) {
            $count += $insight_plugin->hasLOL($value) ? 1 : 0;
        }

        $this->assertEqual(5, $count);
    }

    public function testTopThreeLOLs() {
        $insight_plugin = new EOYLOLCountInsight();
        $year = Date('Y');

        // posts that were LOLed at
        for ($i=0; $i<5; $i++) {
            $score = $i+100;
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => "I just said the funniest thing x$score!",
                    'pub_date' => "$year-01-01",
                    'author_username' => $this->instance->network_username,
                    'author_user_id' => $this->instance->network_user_id,
                    'network' => $this->instance->network,
                    'post_id' => $i+99999,
                    'retweet_count_cache' => 100+$i,
                    'favlike_count_cache' => 100+$i,
                    'reply_count_cache' => 100+$i
                )
            );
        }

        // posts with LOLs
        for ($i=0; $i<5; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'lmao, that was a funny post!',
                    'pub_date' => "$year-01-01",
                    'author_username' => $this->instance->network_username,
                    'author_user_id' => $this->instance->network_user_id,
                    'network' => $this->instance->network,
                    'in_reply_to_post_id' => $i+99999
                )
            );
        }

        $post_dao = DAOFactory::getDAO('PostDAO');
        $posts = $post_dao->getThisYearOfPostsIterator(
            $author_id = $this->instance->network_user_id,
            $network = $this->instance->network
        );

        foreach($posts as $key => $value) {
            $insight_plugin->hasLOL($value);
        }

        // $this->debug(Utils::varDumpToString($insight_plugin->scores));
        $posts = $insight_plugin->getMostPopularLOLees($this->instance);
        $this->assertEqual(3, count($posts));

        $i = 0;
        foreach ($posts as $post) {
            $score = 104 - $i;
            $this->assertEqual($post->post_text, "I just said the funniest thing x$score!");
            $i++;
        }
    }

    public function testTwitterNormalCase() {
        // set up posts with exclamation
        $builders = self::setUpPublicInsight($this->instance);
        $year = Date('Y');
        // posts with LOLs
        for ($i=0; $i<5; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'lmao, that was a funny post!',
                    'pub_date' => "$year-01-01",
                    'author_username' => $this->instance->network_username,
                    'author_user_id' => $this->instance->network_user_id,
                    'network' => $this->instance->network,
                    'in_reply_to_post_id' => $i+99999
                )
            );
        }

        // posts that were LOLed at
        for ($i=0; $i<5; $i++) {
            $score = $i+100;
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => "I just said the funniest thing x$score!",
                    'pub_date' => "$year-01-01",
                    'author_username' => 'Funnyperson',
                    'author_user_id' => $this->instance->network_user_id,
                    'author_fullname' => 'Funny Person',
                    'network' => $this->instance->network,
                    'post_id' => $i+99999,
                    'retweet_count_cache' => 100+$i,
                    'favlike_count_cache' => 100+$i,
                    'reply_count_cache' => 100+$i
                )
            );
        }

        $posts = array();
        $insight_plugin = new EOYLOLCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        //
        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('eoy_lol_count', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $this->assertEqual("omg lol @twitter, $year", $result->headline);
        $this->assertEqual("@ev found 5 things to LOL about on Twitter in $year, " .
            "including these LOLed-at tweets.", $result->text);

        $this->dumpRenderedInsight($result, "Normal case, Twitter");
        // $this->dumpAllHTML();
    }

    public function testTwitterOnePost() {
        // set up posts with exclamation
        $builders = self::setUpPublicInsight($this->instance);
        $year = Date('Y');
        // posts with LOLs
        for ($i=0; $i<5; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'lmao, that was a funny post!',
                    'pub_date' => "$year-01-01",
                    'author_username' => $this->instance->network_username,
                    'author_user_id' => $this->instance->network_user_id,
                    'network' => $this->instance->network,
                    'in_reply_to_post_id' => 99999
                )
            );
        }

        // posts that were LOLed at
        for ($i=0; $i<5; $i++) {
            $score = $i+100;
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => "I just said the funniest thing x$score!",
                    'pub_date' => "$year-01-01",
                    'author_username' => 'Funnyperson',
                    'author_user_id' => $this->instance->network_user_id,
                    'author_fullname' => 'Funny Person',
                    'network' => $this->instance->network,
                    'post_id' => $i+99999,
                    'retweet_count_cache' => 100+$i,
                    'favlike_count_cache' => 100+$i,
                    'reply_count_cache' => 100+$i
                )
            );
        }

        $posts = array();
        $insight_plugin = new EOYLOLCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        //
        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('eoy_lol_count', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $this->assertEqual("omg lol @twitter, $year", $result->headline);
        $this->assertEqual("@ev found 5 things to LOL about on Twitter in $year, " .
            "including this LOLed-at tweet.", $result->text);

        $this->dumpRenderedInsight($result, "One loled at post, Twitter");
        // $this->dumpAllHTML();
    }

    public function testTwitterNoPost() {
        // set up posts with exclamation
        $builders = self::setUpPublicInsight($this->instance);
        $year = Date('Y');
        // posts with LOLs
        for ($i=0; $i<5; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'lmao, that was a funny post!',
                    'pub_date' => "$year-01-01",
                    'author_username' => $this->instance->network_username,
                    'author_user_id' => $this->instance->network_user_id,
                    'network' => $this->instance->network
                )
            );
        }

        $posts = array();
        $insight_plugin = new EOYLOLCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        //
        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('eoy_lol_count', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $this->assertEqual("omg lol @twitter, $year", $result->headline);
        $this->assertEqual("@ev found 5 things to LOL about on Twitter in $year. " .
            "Not a bad year!", $result->text);

        $this->dumpRenderedInsight($result, "No loled at posts, Twitter");
        // $this->dumpAllHTML();
    }

    public function testTwitterOneMatch() {
        $builders = self::setUpPublicInsight($this->instance);
        $year = Date('Y');
        // set up posts with no exclamation
        $builders[] = FixtureBuilder::build('posts',
            array(
                'post_text' => 'This is a year I LOLed once!',
                'pub_date' => "$year-01-01",
                'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id,
                'network' => $this->instance->network,
            )
        );

        $posts = array();
        $insight_plugin = new EOYLOLCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        //
        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('eoy_lol_count', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $this->assertEqual("Funny, but rarely LOL funny", $result->headline);
        $this->assertEqual("@ev found 1 thing to LOL about on Twitter in $year.",
            $result->text);

        $this->dumpRenderedInsight($result, "One match, Twitter");
        // $this->dumpAllHTML();
    }

    public function testFacebookNormalCase() {
        // set up posts with exclamation
        $this->instance->network_username = 'Mark Zuckerberg';
        $this->instance->network = 'facebook';
        $builders = self::setUpPublicInsight($this->instance);
        $year = Date('Y');
        // posts with LOLs
        for ($i=0; $i<5; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'lmao, that was a funny post!',
                    'pub_date' => "$year-01-01",
                    'author_username' => $this->instance->network_username,
                    'author_user_id' => $this->instance->network_user_id,
                    'network' => $this->instance->network,
                    'in_reply_to_post_id' => $i+99999
                )
            );
        }

        // posts that were LOLed at
        for ($i=0; $i<5; $i++) {
            $score = $i+100;
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => "I just said the funniest thing x$score!",
                    'pub_date' => "$year-01-01",
                    'author_username' => 'Funnyperson',
                    'author_user_id' => $this->instance->network_user_id,
                    'author_fullname' => 'Funny Person',
                    'network' => $this->instance->network,
                    'post_id' => $i+99999,
                    'retweet_count_cache' => 100+$i,
                    'favlike_count_cache' => 100+$i,
                    'reply_count_cache' => 100+$i
                )
            );
        }

        $posts = array();
        $insight_plugin = new EOYLOLCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        //
        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('eoy_lol_count', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $this->assertEqual("The LOLs of Facebook, $year",
            $result->headline);
        $this->assertEqual("ROFL. Mark Zuckerberg LOLed at 5 things on Facebook in " .
            "$year, including these LOL-worthy status updates.", $result->text);

        $this->dumpRenderedInsight($result, "Normal case, Facebook");
        // $this->dumpAllHTML();
    }

    public function testFacebookOnePost() {
        $this->instance->network_username = 'Mark Zuckerberg';
        $this->instance->network = 'facebook';
        // set up posts with exclamation
        $builders = self::setUpPublicInsight($this->instance);
        $year = Date('Y');
        // posts with LOLs
        for ($i=0; $i<5; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'lmao, that was a funny post!',
                    'pub_date' => "$year-01-01",
                    'author_username' => $this->instance->network_username,
                    'author_user_id' => $this->instance->network_user_id,
                    'network' => $this->instance->network,
                    'in_reply_to_post_id' => 99999
                )
            );
        }

        // posts that were LOLed at
        for ($i=0; $i<5; $i++) {
            $score = $i+100;
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => "I just said the funniest thing x$score!",
                    'pub_date' => "$year-01-01",
                    'author_username' => 'Funnyperson',
                    'author_user_id' => $this->instance->network_user_id,
                    'author_fullname' => 'Funny Person',
                    'network' => $this->instance->network,
                    'post_id' => $i+99999,
                    'retweet_count_cache' => 100+$i,
                    'favlike_count_cache' => 100+$i,
                    'reply_count_cache' => 100+$i
                )
            );
        }

        $posts = array();
        $insight_plugin = new EOYLOLCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        //
        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('eoy_lol_count', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $this->assertEqual("The LOLs of Facebook, $year",
            $result->headline);
        $this->assertEqual("ROFL. Mark Zuckerberg LOLed at 5 things on Facebook in " .
            "$year, including this LOL-worthy status update.", $result->text);

        $this->dumpRenderedInsight($result, "One loled at post, Twitter");
        // $this->dumpAllHTML();
    }

    public function testFacebookNoPost() {
        $this->instance->network_username = 'Mark Zuckerberg';
        $this->instance->network = 'facebook';
        // set up posts with exclamation
        $builders = self::setUpPublicInsight($this->instance);
        $year = Date('Y');
        // posts with LOLs
        for ($i=0; $i<5; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'lmao, that was a funny post!',
                    'pub_date' => "$year-01-01",
                    'author_username' => $this->instance->network_username,
                    'author_user_id' => $this->instance->network_user_id,
                    'network' => $this->instance->network
                )
            );
        }

        $posts = array();
        $insight_plugin = new EOYLOLCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        //
        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('eoy_lol_count', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $this->assertEqual("The LOLs of Facebook, $year",
            $result->headline);
        $this->assertEqual("ROFL. Mark Zuckerberg LOLed at 5 things on Facebook in " .
            "$year. Gotta love a good LOL.", $result->text);

        $this->dumpRenderedInsight($result, "No loled at posts, Twitter");
        // $this->dumpAllHTML();
    }

    public function testFacebookOneMatch() {
        $this->instance->network_username = 'Mark Zuckerberg';
        $this->instance->network = 'facebook';
        $builders = self::setUpPublicInsight($this->instance);
        $year = Date('Y');
        // set up one post with a LOL
        $builders[] = FixtureBuilder::build('posts',
            array(
                'post_text' => 'LOL this is a post that I did',
                'pub_date' => "$year-01-01",
                'author_username' => $this->instance->network_username,
                'author_user_id' => $this->instance->network_user_id,
                'network' => $this->instance->network,
            )
        );

        $posts = array();
        $insight_plugin = new EOYLOLCountInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('eoy_lol_count', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $this->assertEqual("The LOLs of Facebook, $year",
            $result->headline);
        $this->assertEqual("Mark Zuckerberg LOLed once on Facebook in " .
            "$year. Not the funniest of years.", $result->text);

        $this->dumpRenderedInsight($result, "One match, Facebook");
        // $this->dumpAllHTML();
    }

    private function dumpAllHTML() {
        $controller = new InsightStreamController();
        $_GET['u'] = $this->instance->network_username;
        $_GET['n'] = $this->instance->network;
        $_GET['d'] = date ('Y-m-d');
        $_GET['s'] = 'eoy_lol_count';
        $results = $controller->go();
        //output this to an HTML file to see the insight fully rendered
        $this->debug($results);
    }

    private function dumpRenderedInsight($result, $message) {
        // return false;
        if (isset($message)) {
            $this->debug("<h4 style=\"text-align: center; margin-top: 20px;\">$message</h4>");
        }
        $this->debug($this->getRenderedInsightInHTML($result));
        $this->debug($this->getRenderedInsightInEmail($result));
    }
}

