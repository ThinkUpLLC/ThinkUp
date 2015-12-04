<?php
/**
 *
 * webapp/plugins/insightsgenerator/tests/TestOfEOYMostRetweetedPostInsight.php
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
 * EOYMostRetweetedPost
 *
 * Description of what this class does
 *
 * Copyright (c) 2013 Adam Pash
 *
 * @author Adam Pash adam.pash@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Adam Pash
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/eoymostretweetedpost.php';

class TestOfEOYMostRetweetedPostInsight extends ThinkUpInsightUnitTestCase {

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
        $insight_plugin = new EOYMostRetweetedPostInsight();
        $this->assertIsA($insight_plugin, 'EOYMostRetweetedPostInsight' );
    }

    public function testTopThreePosts() {
        $insight_plugin = new EOYMostRetweetedPostInsight();

        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'This is very shared',
            'pub_date' => '2015-02-07',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'retweet_count_api' => 100
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'This is pretty well shared',
            'pub_date' => '2015-02-07',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'retweet_count_api' => 50
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'This is least shared',
            'pub_date' => '2015-02-07',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'retweet_count_api' => 25
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'These Sochi games are kind of a cluster already.',
            'pub_date' => '2015-02-07',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'retweet_count_api' => 1
            )
        );
        $posts = $insight_plugin->topThreeThisYear($this->instance);

        // test that query returns 3 results sorted by retweets descending
        $this->assertEqual(3, sizeof($posts));
        $this->assertEqual(100, $posts[0]->retweet_count_api);
        $this->assertEqual(50, $posts[1]->retweet_count_api);
        $this->assertEqual(25, $posts[2]->retweet_count_api);

        // test only counts from this year
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'These Sochi games are kind of a cluster already.',
            'pub_date' => '2013-12-07',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'retweet_count_api' => 1000
            )
        );

        $posts = $insight_plugin->topThreeThisYear($this->instance);
        $this->assertEqual(100, $posts[0]->retweet_count_api);
    }

    public function testTwitterNormalCase() {
        // Set up and test normal twitter case
        $builders = self::setUpPublicInsight($this->instance);
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_id' => 1001,
            'post_text' => 'This is very shared',
            'pub_date' => '2015-02-02',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'retweet_count_api' => 100
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_id' => 1002,
            'post_text' => 'This is pretty well shared',
            'pub_date' => '2015-02-07',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'retweet_count_api' => 50
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_id' => 1003,
            'post_text' => 'This is least shared',
            'pub_date' => '2015-02-07',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'retweet_count_api' => 25
            )
        );

        $this->instance->last_post_id = 1001;
        $posts = array();
        $insight_plugin = new EOYMostRetweetedPostInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $year = date('Y');
        $result = $insight_dao->getInsight('eoy_most_retweeted', $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("@buffy's most-retweeted tweets of $year", $result->headline);
        $this->assertEqual("Tweet, retweet, repeat. In $year, @buffy earned the " .
            "most retweets for these gems (at least since February).", $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "Normal case, Twitter");
    }

    // Text variations in amount: if matches == 1
    public function testTwitterOneMatch() {
        // on twitter
        // set up single popular post
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'This is very shared',
            'pub_date' => '2015-02-07',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'retweet_count_api' => 100
            )
        );

        $posts = array();
        $insight_plugin = new EOYMostRetweetedPostInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $year = date('Y');
        $result = $insight_dao->getInsight('eoy_most_retweeted', $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        // $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("@buffy's most-retweeted tweet of $year", $result->headline);
        $this->assertEqual("Tweet, retweet, repeat. In $year, @buffy earned the " .
            "most retweets for this gem.", $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "One match: Twitter");
    }

    // test if user had no shares this year
    public function testTwitterNoMatches() {
        // on twitter
        // set up single popular post
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_text' => 'This is very shared',
            'pub_date' => '2015-02-07',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'retweet_count_api' => 0
            )
        );

        $posts = array();
        $insight_plugin = new EOYMostRetweetedPostInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $year = date('Y');
        $result = $insight_dao->getInsight('eoy_most_retweeted', $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        // $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("Retweets aren't everything", $result->headline);
        $this->assertEqual("@buffy didn't get any retweets in $year, which is a-okay. " .
            "We're not all here to broadcast.", $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "No matches: Twitter");
    }
}

