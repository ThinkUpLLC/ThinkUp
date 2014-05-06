<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfAmplifierInsight.php
 *
 * Copyright (c) 2014 Chris Moyer
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
 * Test of Amplifier Insight
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer <chris[at]inarow[dot]net>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/amplifier.php';

class TestOfAmplifierInsight extends ThinkUpInsightUnitTestCase {
    public function setUp(){
        parent::setUp();

        $instance = new Instance();
        $instance->id = 1;
        $instance->network_user_id = 42;
        $instance->network_username = 'tester';
        $instance->network = 'twitter';
        $this->instance = $instance;

        TimeHelper::setTime(1);
        $this->builders = array();
        $this->builders[] = FixtureBuilder::build('users',array('user_id'=>42,'network'=>'twitter',
            'avatar' => 'avatar.jpg',
            'user_name' => 'tester', 'full_name' => 'The Rewteetee', 'follower_count' => 100));
        $this->builders[] = FixtureBuilder::build('users',array('user_id'=>43,'network'=>'twitter',
            'avatar' => 'avatar.jpg',
            'user_name' => 'lowfollowers', 'full_name' => 'The Rewteetee', 'follower_count' => 10));
        $this->builders[] = FixtureBuilder::build('users',array('user_id'=>44,'network'=>'twitter',
            'avatar' => 'avatar.jpg',
            'user_name' => 'highfollowers', 'full_name' => 'The Rewteetee', 'follower_count' => 1000));
        $this->builders[] = FixtureBuilder::build('users',array('user_id'=>45,'network'=>'twitter',
            'avatar' => 'avatar.jpg',
            'user_name' => 'midfollowers', 'full_name' => 'Some Followers', 'follower_count' => 51));
        $this->builders[] = FixtureBuilder::build('users',array('user_id'=>46,'network'=>'twitter',
            'avatar' => 'avatar.jpg',
            'user_name' => '49followers', 'full_name' => 'Some Followers', 'follower_count' => 49));
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new AmplifierInsight();
        $this->assertIsA($insight_plugin, 'AmplifierInsight' );
    }

    public function testNoInsight() {
        $today = date('Y-m-d');
        $posts = array();
        $posts[] = new Post(array('id'=>1, 'post_text'=>'A Post', 'author_user_id'=>$this->instance->network_user_id,
            'author_username' => 'lowfollowers', 'author_full_name' => 'The Retweetee',
            'in_retweet_of_post_id'=>1, 'in_rt_of_user_id'=>44, 'network' => 'twitter','pub_date'=>$today));

        $insight_plugin = new AmplifierInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $insight_dao = DAOFactory::GetDAO('InsightDAO');
        $result = $insight_dao->getInsight('amplifier_1', $this->instance->id, $today);
        $this->assertNull($result);
    }

    public function testInsightV1() {
        TimeHelper::setTime(1);
        $today = date('Y-m-d');
        $posts = array();
        $posts[] = new Post(array('id'=>1, 'post_text'=>'A Post', 'author_user_id'=>$this->instance->network_user_id,
            'author_username' => 'lowfollowers', 'author_full_name' => 'The Retweetee',
            'in_retweet_of_post_id'=>1, 'in_rt_of_user_id'=>43, 'network' => 'twitter','pub_date'=>$today));

        $insight_plugin = new AmplifierInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $insight_dao = DAOFactory::GetDAO('InsightDAO');
        $result = $insight_dao->getInsight('amplifier_1', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, '90 more people saw @lowfollowers\'s tweet thanks to @tester.');
        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['people']), 1);
        $this->assertEqual($data['people'][0]->username,'lowfollowers');
        $this->assertEqual($data['people'][0]->user_id, 43);
        $this->assertEqual(count($data['posts']), 1);
        $this->assertEqual($data['posts'][0]->post_text, 'A Post');
        $this->debug($this->getRenderedInsightInEmail($result));
        $this->debug($this->getRenderedInsightInHTML($result));
    }

    public function testInsightV2() {
        TimeHelper::setTime(2);
        $today = date('Y-m-d');
        $posts = array();
        $posts[] = new Post(array('id'=>1, 'post_text'=>'A Post', 'author_user_id'=>$this->instance->network_user_id,
            'author_username' => 'lowfollowers', 'author_full_name' => 'The Retweetee',
            'in_retweet_of_post_id'=>1, 'in_rt_of_user_id'=>43, 'network' => 'twitter','pub_date'=>$today));

        $insight_plugin = new AmplifierInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $insight_dao = DAOFactory::GetDAO('InsightDAO');
        $result = $insight_dao->getInsight('amplifier_1', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, '@tester boosted The Rewteetee\'s tweet to 90 more people.');
        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['people']), 1);
        $this->assertEqual($data['people'][0]->username,'lowfollowers');
        $this->assertEqual($data['people'][0]->user_id, 43);
        $this->assertEqual(count($data['posts']), 1);
        $this->assertEqual($data['posts'][0]->post_text, 'A Post');
        $this->debug($this->getRenderedInsightInEmail($result));
        $this->debug($this->getRenderedInsightInHTML($result));
    }

    public function testInsightV3() {
        TimeHelper::setTime(3);
        $today = date('Y-m-d');
        $posts = array();
        $posts[] = new Post(array('id'=>1, 'post_text'=>'A Post', 'author_user_id'=>$this->instance->network_user_id,
            'author_username' => 'lowfollowers', 'author_full_name' => 'The Retweetee',
            'in_retweet_of_post_id'=>1, 'in_rt_of_user_id'=>43, 'network' => 'twitter','pub_date'=>$today));

        $insight_plugin = new AmplifierInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $insight_dao = DAOFactory::GetDAO('InsightDAO');
        $result = $insight_dao->getInsight('amplifier_1', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, 'The Rewteetee can thank @tester for 90 more people seeing this tweet.');
        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['people']), 1);
        $this->assertEqual($data['people'][0]->username,'lowfollowers');
        $this->assertEqual($data['people'][0]->user_id, 43);
        $this->assertEqual(count($data['posts']), 1);
        $this->assertEqual($data['posts'][0]->post_text, 'A Post');
        $this->debug($this->getRenderedInsightInEmail($result));
        $this->debug($this->getRenderedInsightInHTML($result));
    }

    public function testInsightV4() {
        TimeHelper::setTime(35);
        $today = date('Y-m-d');
        $posts = array();
        $posts[] = new Post(array('id'=>1, 'post_text'=>'A Post', 'author_user_id'=>$this->instance->network_user_id,
            'author_username' => 'lowfollowers', 'author_full_name' => 'The Retweetee',
            'in_retweet_of_post_id'=>1, 'in_rt_of_user_id'=>43, 'network' => 'twitter','pub_date'=>$today));

        $insight_plugin = new AmplifierInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $insight_dao = DAOFactory::GetDAO('InsightDAO');
        $result = $insight_dao->getInsight('amplifier_1', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, '@tester boosted The Rewteetee\'s tweet to 10x more people.');
        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['people']), 1);
        $this->assertEqual($data['people'][0]->username,'lowfollowers');
        $this->assertEqual($data['people'][0]->user_id, 43);
        $this->assertEqual(count($data['posts']), 1);
        $this->assertEqual($data['posts'][0]->post_text, 'A Post');
        $this->debug($this->getRenderedInsightInEmail($result));
        $this->debug($this->getRenderedInsightInHTML($result));
    }

    public function testInsightV4TwoX() {
        TimeHelper::setTime(35);
        $today = date('Y-m-d');
        $posts = array();
        $posts[] = new Post(array('id'=>1, 'post_text'=>'A Post', 'author_user_id'=>$this->instance->network_user_id,
            'author_username' => 'lowfollowers', 'author_full_name' => 'The Retweetee',
            'in_retweet_of_post_id'=>1, 'in_rt_of_user_id'=>46, 'network' => 'twitter','pub_date'=>$today));

        $insight_plugin = new AmplifierInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $insight_dao = DAOFactory::GetDAO('InsightDAO');
        $result = $insight_dao->getInsight('amplifier_1', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, '@tester boosted Some Followers\'s tweet to 2x more people.');
        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['people']), 1);
        $this->assertEqual($data['people'][0]->username,'49followers');
        $this->assertEqual($data['people'][0]->user_id, 46);
        $this->assertEqual(count($data['posts']), 1);
        $this->assertEqual($data['posts'][0]->post_text, 'A Post');
        $this->debug($this->getRenderedInsightInEmail($result));
        $this->debug($this->getRenderedInsightInHTML($result));
    }

    public function testInsightV5() {
        TimeHelper::setTime(33);
        $today = date('Y-m-d');
        $posts = array();
        $posts[] = new Post(array('id'=>1, 'post_text'=>'A Post', 'author_user_id'=>$this->instance->network_user_id,
            'author_username' => 'lowfollowers', 'author_full_name' => 'The Retweetee',
            'in_retweet_of_post_id'=>1, 'in_rt_of_user_id'=>43, 'network' => 'twitter','pub_date'=>$today));

        $insight_plugin = new AmplifierInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $insight_dao = DAOFactory::GetDAO('InsightDAO');
        $result = $insight_dao->getInsight('amplifier_1', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, 'The Rewteetee can thank @tester for 10x more people seeing this tweet.');
        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['people']), 1);
        $this->assertEqual($data['people'][0]->username,'lowfollowers');
        $this->assertEqual($data['people'][0]->user_id, 43);
        $this->assertEqual(count($data['posts']), 1);
        $this->assertEqual($data['posts'][0]->post_text, 'A Post');
        $this->debug($this->getRenderedInsightInEmail($result));
        $this->debug($this->getRenderedInsightInHTML($result));
    }
    public function testInsightV5b() {
        TimeHelper::setTime(33);
        $today = date('Y-m-d');
        $posts = array();
        $posts[] = new Post(array('id'=>1, 'post_text'=>'A Post', 'author_user_id'=>$this->instance->network_user_id,
            'author_username' => 'lowfollowers', 'author_full_name' => 'The Retweetee',
            'in_retweet_of_post_id'=>1, 'in_rt_of_user_id'=>45, 'network' => 'twitter','pub_date'=>$today));

        $insight_plugin = new AmplifierInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $insight_dao = DAOFactory::GetDAO('InsightDAO');
        $result = $insight_dao->getInsight('amplifier_1', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, 'Some Followers can thank @tester for 49 more people seeing this tweet.');
        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['people']), 1);
        $this->assertEqual($data['people'][0]->username,'midfollowers');
        $this->assertEqual($data['people'][0]->user_id, 45);
        $this->assertEqual(count($data['posts']), 1);
        $this->assertEqual($data['posts'][0]->post_text, 'A Post');
        $this->debug($this->getRenderedInsightInEmail($result));
        $this->debug($this->getRenderedInsightInHTML($result));
    }


    public function testInsightV6() {
        TimeHelper::setTime(34);
        $today = date('Y-m-d');
        $posts = array();
        $posts[] = new Post(array('id'=>1, 'post_text'=>'A Post', 'author_user_id'=>$this->instance->network_user_id,
            'author_username' => 'lowfollowers', 'author_full_name' => 'The Retweetee',
            'in_retweet_of_post_id'=>1, 'in_rt_of_user_id'=>43, 'network' => 'twitter','pub_date'=>$today));

        $insight_plugin = new AmplifierInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);
        $insight_dao = DAOFactory::GetDAO('InsightDAO');
        $result = $insight_dao->getInsight('amplifier_1', $this->instance->id, $today);
        $this->assertNotNull($result);
        $this->assertEqual($result->headline, '10x more people saw @lowfollowers\'s tweet thanks to @tester.');
        $data = unserialize($result->related_data);
        $this->assertEqual(count($data['people']), 1);
        $this->assertEqual($data['people'][0]->username,'lowfollowers');
        $this->assertEqual($data['people'][0]->user_id, 43);
        $this->assertEqual(count($data['posts']), 1);
        $this->assertEqual($data['posts'][0]->post_text, 'A Post');
        $this->debug($this->getRenderedInsightInEmail($result));
        $this->debug($this->getRenderedInsightInHTML($result));
    }
}
