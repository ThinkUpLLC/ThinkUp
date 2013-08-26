<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfOutreachPunchcardInsight.php
 *
 * Copyright (c) 2012-2013 Nilaksh Das, Gina Trapani
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
 * Test of Outreach Punchcard Insight
 *
 * Test for OutreachPunchcardInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Nilaksh Das, Gina Trapani
 * @author Nilaksh Das <nilakshdas [at] gmail [dot] com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/outreachpunchcard.php';

class TestOfOutreachPunchcardInsight extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testGetSyntacticTimeDifference() {
        $delta_1 = 60 * 60 * 3; // 3 hours
        $delta_2 = 60 * 6; // 6 minutes
        $delta_3 = 60 * 60 * 24 * 4; // 4 days
        $delta_4 = 60 * 60 * 24; // 1 day

        $result_1 = OutreachPunchcardInsight::getSyntacticTimeDifference($delta_1);
        $result_2 = OutreachPunchcardInsight::getSyntacticTimeDifference($delta_2);
        $result_3 = OutreachPunchcardInsight::getSyntacticTimeDifference($delta_3);
        $result_4 = OutreachPunchcardInsight::getSyntacticTimeDifference($delta_4);

        $this->assertEqual($result_1, '3 hours');
        $this->assertEqual($result_2, '6 minutes');
        $this->assertEqual($result_3, '4 days');
        $this->assertEqual($result_4, '1 day');
    }

    public function testOutreachPunchcardInsight() {
        // Get data ready that insight requires
        $posts = self::getTestPostObjects();

        $builders = array();

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'7654321', 'user_name'=>'twitteruser',
        'full_name'=>'Twitter User', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter User'));

        $time_1 = date("Y-m-d H:i:s", (strtotime('-2 days') + (60 * 6))); // 6 minutes later
        $builders[] = FixtureBuilder::build('posts', array('id'=>136, 'post_id'=>136, 'author_user_id'=>7654321,
        'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is a reply.', 'source'=>'web',
        'pub_date'=>$time_1, 'in_reply_to_post_id'=>133, 'reply_count_cache'=>0, 'is_protected'=>0));

        $time_2 = date("Y-m-d H:i:s", (strtotime('-2 days') + (60 * 60 * 2))); // 2 hours later
        $builders[] = FixtureBuilder::build('posts', array('id'=>137, 'post_id'=>137, 'author_user_id'=>7654321,
        'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is a reply.', 'source'=>'web',
        'pub_date'=>$time_2, 'in_reply_to_post_id'=>133, 'reply_count_cache'=>0, 'is_protected'=>0));

        $time_3 = date("Y-m-d H:i:s", (strtotime('-2 days') + (60 * 4))); // 4 minutes later
        $builders[] = FixtureBuilder::build('posts', array('id'=>138, 'post_id'=>138, 'author_user_id'=>7654321,
        'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is a reply.', 'source'=>'web',
        'pub_date'=>$time_3, 'in_reply_to_post_id'=>135, 'reply_count_cache'=>0, 'is_protected'=>0));

        $time_4 = date("Y-m-d H:i:s", (strtotime('-2 days') + (60 * 14))); // 14 minutes later
        $builders[] = FixtureBuilder::build('posts', array('id'=>139, 'post_id'=>139, 'author_user_id'=>7654321,
        'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'source'=>'web',
        'post_text'=>'RT @testeriffic: New Year\'s Eve! Feeling very gay today, but not very homosexual.',
        'pub_date'=>$time_4, 'in_retweet_of_post_id'=>134, 'reply_count_cache'=>0, 'is_protected'=>0));

        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new OutreachPunchcardInsight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted with average 8 minutes
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('outreach_punchcard', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/\@testeriffic\'s tweets from last week started getting responses/', $result->text);
        $this->assertPattern('/in as little as 8 minutes of being posted/', $result->text);
    }

    public function testOutreachPunchcardInsightNoResponse() {
        // Get data ready that insight requires
        $posts = self::getTestPostObjects();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new OutreachPunchcardInsight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted for no responses
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('outreach_punchcard', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/\@testeriffic\'s tweets from last week didn\'t get any responses/', $result->text);
    }

    public function testOutreachPunchcardInsightPunchcardDataset() {
        // Get data ready that insight requires
        $posts = self::getTestPostObjects();
        $posts_dotw = date('N', strtotime($posts[0]->pub_date)); // Day of the week
        $posts_hotd = date('G', strtotime($posts[0]->pub_date)); // Hour of the day

        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new OutreachPunchcardInsight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted and punchcard data is correct
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('outreach_punchcard', 10, $today);
        $punchcard = unserialize($result->related_data);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual($punchcard['posts'][$posts_dotw][$posts_hotd], 3);
    }

    /**
     * Get test post objects
     * @return array of post objects for use in testing
     */
    private function getTestPostObjects() {
        $post_text_arr = array();
        $post_text_arr[] = "Now that I'm back on Android, realizing just how under sung Google Now is. ".
        "I want it everywhere.";
        $post_text_arr[] = "New Year's Eve! Feeling very gay today, but not very homosexual.";
        $post_text_arr[] = "When @anildash told me he was writing this I was ".
        "like 'yah whatever cool' then I read it and it knocked my socks off http://bit.ly/W9ASnj ";

        $posts = array();
        $counter = 133;
        foreach ($post_text_arr as $test_text) {
            $p = new Post();
            $p->post_id = $counter++;
            $p->network = 'twitter';
            $p->post_text = $test_text;
            $p->pub_date = date("Y-m-d H:i:s",strtotime('-2 days'));
            $posts[] = $p;
        }
        return $posts;
    }
}
