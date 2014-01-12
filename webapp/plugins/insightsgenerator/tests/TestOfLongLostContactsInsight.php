<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfLongLostContactsInsight.php
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
 * Test of LongLostContactsInsight
 *
 * Test for the LongLostContactsInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Nilaksh Das, Gina Trapani
 * @author Nilaksh Das <nilakshdas [at] gmail [dot] com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/longlostcontacts.php';

class TestOfLongLostContactsInsight extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testLongLostContactsInsight() {
        // Get data ready that insight requires
        $builders = self::buildData();
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'twitteruser';
        $instance->network = 'twitter';
        $insight_plugin = new LongLostContactsInsight();
        $insight_plugin->generateInsight($instance, $last_week_of_posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $today = date ('Y-m-d');
        $result = $insight_dao->getInsight('long_lost_contacts', 10, $today);
        $contacts = unserialize($result->related_data);
        $this->debug(Utils::varDumpToString($result));
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertPattern('/\@twitteruser hasn\'t replied to /', $result->headline);
        $this->assertPattern('/<strong>2 contacts<\/strong> /', $result->headline);
        $this->assertPattern('/in over a year: /', $result->headline);
        $this->assertNoPattern('/a contact/', $result->headline);
        $this->assertIsA($contacts, "array");
        $this->assertIsA($contacts["people"][0], "User");
        $this->assertEqual(count($contacts["people"]), 2);
    }

    private function buildData() {
        $builders = array();

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'7612345', 'user_name'=>'twitteruser',
        'full_name'=>'Twitter User', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter User'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'7612346', 'user_name'=>'twitterfoll1',
        'full_name'=>'Twitter Follower One', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter Follower'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'7612347', 'user_name'=>'twitterfoll2',
        'full_name'=>'Twitter Follower Two', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter Follower'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'7612348', 'user_name'=>'twitterfoll3',
        'full_name'=>'Twitter Follower Three', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter Follower'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'7612349', 'user_name'=>'twitterfoll4',
        'full_name'=>'Twitter Follower Four', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter Follower'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'7612350', 'user_name'=>'twitterfoll5',
        'full_name'=>'Twitter Follower Five', 'avatar'=>'avatar.jpg', 'follower_count'=>36000, 'is_protected'=>0,
        'network'=>'twitter', 'description'=>'A test Twitter Follower'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'7612346', 'follower_id'=>'7612345',
        'last_seen'=>'-1d', 'first_seen'=>'-1d', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'7612347', 'follower_id'=>'7612345',
        'last_seen'=>'-1d', 'first_seen'=>'-1d', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'7612348', 'follower_id'=>'7612345',
        'last_seen'=>'-1d', 'first_seen'=>'-1d', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'7612349', 'follower_id'=>'7612345',
        'last_seen'=>'-1d', 'first_seen'=>'-1d', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'7612350', 'follower_id'=>'7612345',
        'last_seen'=>'-1d', 'first_seen'=>'-1d', 'network'=>'twitter'));

        $time_ago_1 = date('Y-m-d H:i:s', strtotime('-370 days'));
        $time_ago_2 = date('Y-m-d H:i:s', strtotime('-369 days'));
        $time_ago_3 = date('Y-m-d H:i:s', strtotime('-367 days'));
        $time_ago_4 = date('Y-m-d H:i:s', strtotime('-130 days'));
        $time_ago_5 = date('Y-m-d H:i:s', strtotime('-20 days'));

        $builders[] = FixtureBuilder::build('posts', array('id'=>138, 'post_id'=>138, 'author_user_id'=>7612345,
        'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is a reply to a twitter post', 'source'=>'web',
        'pub_date'=>$time_ago_1, 'in_reply_to_user_id'=>7612346, 'reply_count_cache'=>0, 'is_protected'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>139, 'post_id'=>139, 'author_user_id'=>7612345,
        'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is a reply to a twitter post', 'source'=>'web',
        'pub_date'=>$time_ago_2, 'in_reply_to_user_id'=>7612347, 'reply_count_cache'=>0, 'is_protected'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>140, 'post_id'=>140, 'author_user_id'=>7612345,
        'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is a reply to a twitter post', 'source'=>'web',
        'pub_date'=>$time_ago_3, 'in_reply_to_user_id'=>7612348, 'reply_count_cache'=>0, 'is_protected'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>141, 'post_id'=>141, 'author_user_id'=>7612345,
        'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is a reply to a twitter post', 'source'=>'web',
        'pub_date'=>$time_ago_4, 'in_reply_to_user_id'=>7612348, 'reply_count_cache'=>0, 'is_protected'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>142, 'post_id'=>142, 'author_user_id'=>7612345,
        'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is a reply to a twitter post', 'source'=>'web',
        'pub_date'=>$time_ago_4, 'in_reply_to_user_id'=>7612349, 'reply_count_cache'=>0, 'is_protected'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>143, 'post_id'=>143, 'author_user_id'=>7612345,
        'author_username'=>'twitteruser', 'author_fullname'=>'Twitter User', 'author_avatar'=>'avatar.jpg',
        'network'=>'twitter', 'post_text'=>'This is a reply to a twitter post', 'source'=>'web',
        'pub_date'=>$time_ago_5, 'in_reply_to_user_id'=>7612350, 'reply_count_cache'=>0, 'is_protected'=>0));

        return $builders;
    }
}