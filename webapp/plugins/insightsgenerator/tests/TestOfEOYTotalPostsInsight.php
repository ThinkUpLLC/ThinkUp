<?php
/**
 *
 * webapp/plugins/insightsgenerator/tests/TestOfEOYTotalPostsInsight.php
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
 * EOYTotalPosts (name of file)
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
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/eoytotalposts.php';

class TestOfEOYTotalPostsInsight extends ThinkUpInsightUnitTestCase {

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
        $insight_plugin = new EOYTotalPostsInsight();
        $this->assertIsA($insight_plugin, 'EOYTotalPostsInsight' );
    }

    public function testTotalPosts() {
        $insight_plugin = new EOYTotalPostsInsight();
        $year = Date('Y');

        // A bunch of posts in 2014-02-07
        for ($i=1; $i<10; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                'post_text' => 'This is a post from this year!',
                'pub_date' => "$year-0$i-0'",
                'author_username' => $this->instance->network_username,
                'network' => $this->instance->network
                )
            );
        }
        // More posts in diff year
        for ($i=0; $i<3; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'This is the wrong year',
                    'pub_date' => '2013-09-07',
                    'author_username' => $this->instance->network_username,
                    'network' => $this->instance->network,
                    'retweet_count_cache' => 50
                )
            );
        }


        $total_posts = $insight_plugin->getTotalPostCount($this->instance, $year);

        // test that total posts is 9
        $this->assertEqual(9, $total_posts);

        // test post count from 2013 is 3
        $total_posts = $insight_plugin->getTotalPostCount($this->instance, 2013);
        $this->assertEqual(3, $total_posts);
        // $this->debug(Utils::varDumpToString($most_talkative_days));
    }

    public function testPostingTime() {
        $insight_plugin = new EOYTotalPostsInsight();

        $posting_time = $insight_plugin->getPostingTime(100);
        $this->assertEqual('25 minutes', $posting_time);

        $posting_time = $insight_plugin->getPostingTime(720);
        $this->assertEqual('3 hours', $posting_time);

        $posting_time = $insight_plugin->getPostingTime(10230);
        $this->assertEqual('1 day, 18 hours, and 37 minutes', $posting_time);
    }

    public function testTwitterNormalCase() {
        // Set up and test normal twitter case
        $builders = self::setUpPublicInsight($this->instance);
        $year = Date('Y');
        for ($i=0; $i<40; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'This is a post',
                    'pub_date' => "$year-02-07",
                    'post_id' => 100 + $i,
                    'author_username' => $this->instance->network_username,
                    'network' => $this->instance->network,
                )
            );
        }

        $posts = array();
        $insight_plugin = new EOYTotalPostsInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('eoy_total_posts', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        // $this->debug(Utils::varDumpToString($result->related_data));
        $year = date('Y');
        $this->assertEqual("@buffy tweeted 40 times in $year", $result->headline);
        $this->assertEqual("In $year, @buffy put keyboard to tweet box a total of <b>40 " .
            "times</b>, amounting to over <b>10 minutes</b> of @buffy's life.", $result->text);

        // $this->dumpAllHTML();
        $this->dumpRenderedInsight($result, "Normal case, Twitter");
    }

    public function testFacebookNormalCase() {
        // Set up and test normal facebook case
        $this->instance->network = 'facebook';
        $this->instance->network_username = 'Buffy Summers';

        $builders = self::setUpPublicInsight($this->instance);
        $year = Date('Y');

        for ($i=0; $i<40; $i++) {
            $builders[] = FixtureBuilder::build('posts',
                array(
                    'post_text' => 'This is a post',
                    'pub_date' => "$year-02-07",
                    'post_id' => 100 + $i,
                    'author_username' => $this->instance->network_username,
                    'network' => $this->instance->network,
                )
            );
        }

        $posts = array();
        $insight_plugin = new EOYTotalPostsInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('eoy_total_posts', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $year = date('Y');
        $this->assertEqual("Buffy Summers posted to Facebook 40 times in $year", $result->headline);
        $this->assertEqual("This year, Buffy Summers posted a grand total of " .
            "<b>40 times</b> to Facebook. That's over " .
            "<b>10 minutes</b> dedicated to keeping in touch with friends."
            , $result->text);

        $this->dumpRenderedInsight($result, "Normal case, Facebook");
        // $this->dumpAllHTML();
    }

    private function dumpAllHTML() {
        $controller = new InsightStreamController();
        $_GET['u'] = $this->instance->network_username;
        $_GET['n'] = $this->instance->network;
        $_GET['d'] = date ('Y-m-d');
        $_GET['s'] = 'eoy_total_posts';
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

