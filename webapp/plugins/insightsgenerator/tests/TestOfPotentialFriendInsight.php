<?php

/**
 *
 * webapp/plugins/insightsgenerator/tests/TestOfPotentialFriendInsight.php
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
 * TestOfDiversifyLinks
 *
 * Tests the diversify links Insight.
 *
 * Copyright (c) Gareth Brady
 *
 * @author Gareth Brady gareth.brady92@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Gareth Brady
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/potentialfriend.php';

class TestOfPotentialFriendInsight extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new PotentialFriendInsight();
        $this->assertIsA($insight_plugin, 'PotentialFriendInsight');
    }

    public function testNoReplies() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $builders = array();

        $days_ago_3 = date('Y-m-d H:i:s', strtotime('-3 days'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'7612345', 'user_name'=>'testeriffic',
        'full_name'=>'Jack Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>999999, 'friend_count'=>12,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'twitter', 'description'=>'Test'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1000', 'user_name'=>'v3',
        'full_name'=>'Jack Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>999999, 'friend_count'=>12,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'twitter', 'description'=>'Test'));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'1000', 'follower_id'=>7612345,
        'last_seen'=>'-1d', 'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>139, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Test Tweeter', 'network'=>'twitter',
        'post_text'=>"No replies here", 'source'=>'web', 'pub_date'=>'-1d',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'in_reply_to_user_id'=> NULL, 'in_reply_to_post_id'=>NULL));

        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new PotentialFriendInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('potential_friend_insight', 10, $today); 
        $this->assertNull($result);
    }

    public function testRepliesToFollowees() {
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $builders = array();

        $days_ago_3 = date('Y-m-d H:i:s', strtotime('-3 days'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'7612345', 'user_name'=>'testeriffic',
        'full_name'=>'Jack Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>999999, 'friend_count'=>12,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'twitter', 'description'=>'Test'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1000', 'user_name'=>'v3',
        'full_name'=>'Jack Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>999999, 'friend_count'=>12,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'twitter', 'description'=>'Test'));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'1000', 'follower_id'=>7612345,
        'last_seen'=>'-1d', 'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>139, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Test Tweeter', 'network'=>'twitter',
        'post_text'=>'@v3 Thanks!', 'source'=>'web', 'pub_date'=>'-1d',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'in_reply_to_user_id'=>1000, 'in_reply_to_post_id'=>132));

        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new PotentialFriendInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('potential_friend_insight', 10, $today); 
        $this->assertNull($result);
    }

    public function testReplyToNonFollowee() {
        TimeHelper::setTime(2);
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $builders = array();

        $days_ago_3 = date('Y-m-d H:i:s', strtotime('-3 days'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'7612345', 'user_name'=>'testeriffic',
        'full_name'=>'Jack Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>999999, 'friend_count'=>12,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'twitter', 'description'=>'Test'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1000', 'user_name'=>'v3',
        'full_name'=>'Jack Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>999999, 'friend_count'=>12,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'twitter', 'description'=>'Test'));
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>139, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Test Tweeter', 'network'=>'twitter',
        'post_text'=>'@v3 Thanks!', 'source'=>'web', 'pub_date'=>'-1d',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'in_reply_to_user_id'=>1000, 'in_reply_to_post_id'=>132));

        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new PotentialFriendInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('potential_friend_insight', 10, $today);
        $text = "@testeriffic replied to @v3 <b>1 time</b> this week, but doesn't follow them on Twitter.";
        $text .= " <br>Do you want to follow his or her updates, or maybe you don't follow them on purpose?";
        $this->assertEqual( $result->headline,"Want to keep up to date with @testeriffic's new acquaintance ?");
        $this->assertEqual($result->text,$text);
        $this->assertNotEqual(false, strpos($result->related_data, "Jack Dorsey")); 
    }

    public function testReplyToNonFolloweeAndFollowee() {
        TimeHelper::setTime(1);
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $builders = array();

        $days_ago_3 = date('Y-m-d H:i:s', strtotime('-3 days'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'7612345', 'user_name'=>'testeriffic',
        'full_name'=>'Jack Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>999999, 'friend_count'=>12,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'twitter', 'description'=>'Test'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1000', 'user_name'=>'v3',
        'full_name'=>'Jack Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>999999, 'friend_count'=>12,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'twitter', 'description'=>'Test'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1001', 'user_name'=>'v4',
        'full_name'=>'Jack Dorser', 'avatar'=>'avatar.jpg', 'follower_count'=>999999, 'friend_count'=>12,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'twitter', 'description'=>'Test'));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'1000', 'follower_id'=>7612345,
        'last_seen'=>'-1d', 'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>139, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Test Tweeter', 'network'=>'twitter',
        'post_text'=>'@v3 Thanks!', 'source'=>'web', 'pub_date'=>'-1d',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'in_reply_to_user_id'=>1000, 'in_reply_to_post_id'=>132));
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>140, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Test Tweeter', 'network'=>'twitter',
        'post_text'=>'@v4 Thanks!', 'source'=>'web', 'pub_date'=>'-1d',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'in_reply_to_user_id'=>1001, 'in_reply_to_post_id'=>133));
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>141, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Test Tweeter', 'network'=>'twitter',
        'post_text'=>'@v4 Thanks!', 'source'=>'web', 'pub_date'=>'-1d',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'in_reply_to_user_id'=>1001, 'in_reply_to_post_id'=>134));

        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'twitter';
        $insight_plugin = new PotentialFriendInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('potential_friend_insight', 10, $today);
        $text = "@testeriffic replied to @v4 <b>2 times</b> this week, but doesn't follow them on Twitter.";
        $text .= " <br>Do you want to follow his or her updates, or maybe you don't follow them on purpose?";
        $this->assertEqual( $result->headline,"Looks like @testeriffic has been talking to someone new this week.");
        $this->assertEqual($result->text,$text); 
        $this->assertNotEqual(false, strpos($result->related_data, "Jack Dorser"));
    }

    public function testRepliesToMultipleNonFollowees() {
        TimeHelper::setTime(3);
        $insight_dao = DAOFactory::getDAO('InsightDAO');
        $builders = array();

        $days_ago_3 = date('Y-m-d H:i:s', strtotime('-3 days'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'7612345', 'user_name'=>'testeriffic',
        'full_name'=>'Jack Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>999999, 'friend_count'=>12,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'facebook', 'description'=>'Test'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1000', 'user_name'=>'v3',
        'full_name'=>'Jack Dorsey', 'avatar'=>'avatar.jpg', 'follower_count'=>999999, 'friend_count'=>12,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'facebook', 'description'=>'Test'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'1001', 'user_name'=>'v4',
        'full_name'=>'Jack Dorser', 'avatar'=>'avatar.jpg', 'follower_count'=>999999, 'friend_count'=>12,
        'is_verified'=>1, 'is_protected'=>0, 'network'=>'facebook', 'description'=>'Test'));

        $builders[] = FixtureBuilder::build('posts', array('post_id'=>138, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Test Tweeter', 'network'=>'facebook',
        'post_text'=>'@v3 Thanks!', 'source'=>'web', 'pub_date'=>'-1d',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'in_reply_to_user_id'=>1000, 'in_reply_to_post_id'=>132));
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>139, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Test Tweeter', 'network'=>'facebook',
        'post_text'=>'@v3 Thanks!', 'source'=>'web', 'pub_date'=>'-1d',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'in_reply_to_user_id'=>1000, 'in_reply_to_post_id'=>132));
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>140, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Test Tweeter', 'network'=>'facebook',
        'post_text'=>'@v4 Thanks!', 'source'=>'web', 'pub_date'=>'-1d',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'in_reply_to_user_id'=>1001, 'in_reply_to_post_id'=>133));
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>141, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Test Tweeter', 'network'=>'facebook',
        'post_text'=>'@v4 Thanks!', 'source'=>'web', 'pub_date'=>'-1d',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'in_reply_to_user_id'=>1001, 'in_reply_to_post_id'=>134));
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>142, 'author_user_id'=>7612345,
        'author_username'=>'testeriffic', 'author_fullname'=>'Test Tweeter', 'network'=>'facebook',
        'post_text'=>'@v4 Thanks!', 'source'=>'web', 'pub_date'=>'-1d',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'in_reply_to_user_id'=>1001, 'in_reply_to_post_id'=>134));

        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = 7612345;
        $instance->network_username = 'testeriffic';
        $instance->network = 'facebook';
        $insight_plugin = new PotentialFriendInsight();
        $insight_plugin->generateInsight($instance, null, $posts, 3);

        $today = date('Y-m-d');
        $result = $insight_dao->getInsight('potential_friend_insight', 10, $today);
        $text = "testeriffic replied to v4 <b>3 times</b> this week, but doesn't follow them on facebook.";
        $text .= " <br>Do you want to follow his or her updates, or maybe you don't follow them on purpose?";
        $this->assertEqual($result->headline,"testeriffic has been chatting to new people this week!");
        $this->assertEqual($result->text,$text); 
        $this->assertNotEqual(false, strpos($result->related_data, "Jack Dorser"));
    }
}
