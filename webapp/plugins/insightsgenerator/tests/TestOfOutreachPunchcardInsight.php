<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfOutreachPunchcardInsight.php
 *
 * Copyright (c) 2013 Nilaksh Das, Gina Trapani
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
 * @copyright 2013 Nilaksh Das, Gina Trapani
 * @author Nilaksh Das <nilakshdas [at] gmail [dot] com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/outreachpunchcard.php';

class TestOfOutreachPunchcardInsight extends ThinkUpInsightUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testOutreachPunchcardInsight() {
        $cfg = Config::getInstance();
        $install_timezone = new DateTimeZone($cfg->getValue('timezone'));
        $owner_timezone = new DateTimeZone($test_timezone='America/Los_Angeles');

        // Get data ready that insight requires
        $posts = self::getTestPostObjects();

        $post_pub_date = new DateTime($posts[0]->pub_date);
        $now = new DateTime();
        $offset = timezone_offset_get($owner_timezone, $now) - timezone_offset_get($install_timezone, $now);
        $post_dotw = date('N', (date('U', strtotime($posts[0]->pub_date)))+ timezone_offset_get($owner_timezone, $now));
        $post_hotd = date('G', (date('U', strtotime($posts[0]->pub_date)))+ timezone_offset_get($owner_timezone, $now));

        $builders = array();

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'7654321', 'user_name'=>'twitteruser',
        'full_name'=>'Twitter User', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter User'));

        $instance_id = 10;
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User',
        'email'=>'test@example.com', 'is_activated'=>1, 'email_notification_frequency' => 'never', 'is_admin' => 0,
        'timezone' => $test_timezone));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>'1','instance_id'=>$instance_id));

        $install_offset = $install_timezone->getOffset(new DateTime());
        $date_r = date("Y-m-d",strtotime('-1 day')-$install_offset);

        // Response between 1pm and 2pm install time
        $time = gmdate('Y-m-d H:i:s', strtotime('yesterday 13:11:09'));
        $builders[] = FixtureBuilder::build('posts', array('id'=>136, 'post_id'=>136, 'author_user_id'=>7654321,
        'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is a reply.', 'source'=>'web',
        'pub_date'=>$time, 'in_reply_to_post_id'=>133, 'reply_count_cache'=>0, 'is_protected'=>0));

        // Response between 1pm and 2pm install time
        $time = gmdate('Y-m-d H:i:s', strtotime('yesterday 13:01:13'));
        $builders[] = FixtureBuilder::build('posts', array('id'=>137, 'post_id'=>137, 'author_user_id'=>7654321,
        'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is a reply.', 'source'=>'web',
        'pub_date'=>$time, 'in_reply_to_post_id'=>133, 'reply_count_cache'=>0, 'is_protected'=>0));

        // Response between 1pm and 2pm install time
        $time = gmdate('Y-m-d H:i:s', strtotime('yesterday 13:13:56'));
        $builders[] = FixtureBuilder::build('posts', array('id'=>138, 'post_id'=>138, 'author_user_id'=>7654321,
        'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is a reply.', 'source'=>'web',
        'pub_date'=>$time, 'in_reply_to_post_id'=>135, 'reply_count_cache'=>0, 'is_protected'=>0));

        // Response between 11am and 12pm install time
        $time = gmdate('Y-m-d H:i:s', strtotime('yesterday 11:07:42'));
        $builders[] = FixtureBuilder::build('posts', array('id'=>139, 'post_id'=>139, 'author_user_id'=>7654321,
        'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'source'=>'web',
        'post_text'=>'RT @testeriffic: New Year\'s Eve! Feeling very gay today, but not very homosexual.',
        'pub_date'=>$time, 'in_retweet_of_post_id'=>134, 'reply_count_cache'=>0, 'is_protected'=>0));

        $time1str_low = date('ga', (date('U', strtotime($date_r.' 13:00:00')) + $offset));
        $time1str_high = date('ga', (date('U', strtotime($date_r.' 14:00:00')) + $offset));
        $time1str = $time1str_low." and ".$time1str_high;

        $time2str_low = date('ga', (date('U', strtotime($date_r.' 11:00:00')) + $offset));
        $time2str_high = date('ga', (date('U', strtotime($date_r.' 12:00:00')) + $offset));
        $time2str = $time2str_low." and ".$time2str_high;

        $instance = new Instance();
        $instance->id = $instance_id;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new OutreachPunchcardInsight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got inserted with correct punchcard information
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('outreach_punchcard', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/\@testeriffic\'s tweets from last week got/', $result->headline);
        $this->assertPattern('/between <strong>'.$time1str.'<\/strong> - 3 replies/', $result->headline);
        $this->assertPattern('/That\'s compared to 1 response/', $result->text);
        $this->assertPattern('/1 response between '.$time2str.'/', $result->text);

        //Test email rendering
        $result->related_data = unserialize($result->related_data);
        $email_insight = $this->getRenderedInsightInEmail($result);
        $this->debug($email_insight);
    }

    public function testOutreachPunchcardInsightNoResponse() {
        $instance_id = 10;
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User',
        'email'=>'test@example.com', 'is_activated'=>1, 'email_notification_frequency' => 'never', 'is_admin' => 0,
        'timezone' => 'UTC'));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>'1','instance_id'=>$instance_id));

        // Get data ready that insight requires
        $posts = self::getTestPostObjects();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new OutreachPunchcardInsight();
        $insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight did not got inserted for no responses
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('outreach_punchcard', 10, $today);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNull($result);
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
            $p->pub_date = gmdate("Y-m-d H:i:s", strtotime('-2 days'));
            $posts[] = $p;
        }
        return $posts;
    }
}
