<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfBigReshareInsight.php
 *
 * Copyright (c) 2012-2013 Gina Trapani
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
 * Test of Big Reshare Insight
 *
 * Test for BigReshareInsight class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/bigreshare.php';

class TestOfBigReshareInsight extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testSingleBigReshareBy2xFollowers() {
        // Assert that insight doesn't exist
        $insight_dao = new InsightMySQLDAO();
        $yesterday = date ('Y-m-d', strtotime('-1 day'));
        $result = $insight_dao->getInsight('big_reshare_1345', 10, $yesterday);
        $this->assertNull($result);

        //original post
        $post1_builder = FixtureBuilder::build('posts', array('id'=>1345, 'post_id'=>'134', 'author_user_id'=>22,
        'author_username'=>'quoter', 'author_fullname'=>'Quoter of Quotables', 'network'=>'twitter',
        'post_text'=>'Be liberal in what you accept and conservative in what you send', 'source'=>'web',
        'pub_date'=>'-1d', 'reply_count_cache'=>0, 'retweet_count_cache'=>0,
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
        'location'=>'New Delhi, Delhi, India', 'geo'=>'28.635308,77.22496', 'is_geo_encoded'=>1));

        $post1_object = new Post($post1_builder->columns);
        $builders = self::buildData();

        //retweet author has 2x more followers
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'20', 'user_name'=>'user1',
        'full_name'=>'User 1', 'avatar'=>'avatar.jpg', 'is_protected'=>0, 'follower_count'=>160,
        'network'=>'twitter'));

        // Get data ready that insight requires
        $posts = array();
        $posts[] = $post1_object;
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = '22';
        $instance->network = 'twitter';
        $instance->network_username = 'testeriffic';
        $bigreshare_insight_plugin = new BigReshareInsight();
        $bigreshare_insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got generated
        $insight_dao = new InsightMySQLDAO();
        $yesterday = date ('Y-m-d', strtotime('-1 day'));
        $result = $insight_dao->getInsight('big_reshare_1345', 10, $yesterday);
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'big_reshare_1345');
        $this->assertEqual($result->filename, 'bigreshare');
        $this->assertPattern('/Someone with \<strong\>2x\<\/strong\> more followers than \@testeriffic retweeted/',
        $result->headline);
    }

    public function testMultipleBigReshare() {
        // Assert that insight doesn't exist
        $insight_dao = new InsightMySQLDAO();
        $yesterday = date ('Y-m-d', strtotime('-1 day'));
        $result = $insight_dao->getInsight('big_reshare_1345', 10, $yesterday);
        $this->assertNull($result);

        //original post
        $post1_builder = FixtureBuilder::build('posts', array('id'=>1345, 'post_id'=>'134', 'author_user_id'=>22,
        'author_username'=>'quoter', 'author_fullname'=>'Quoter of Quotables', 'network'=>'twitter',
        'post_text'=>'Be liberal in what you accept and conservative in what you send', 'source'=>'web',
        'pub_date'=>'-1d', 'reply_count_cache'=>0, 'retweet_count_cache'=>0,
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
        'location'=>'New Delhi, Delhi, India', 'geo'=>'28.635308,77.22496', 'is_geo_encoded'=>1));

        $post1_object = new Post($post1_builder->columns);
        $builders = self::buildData();

        // First retweet author has 2x more followers
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'20', 'user_name'=>'user1',
        'full_name'=>'User 1', 'avatar'=>'avatar.jpg', 'is_protected'=>0, 'follower_count'=>160,
        'network'=>'twitter'));

        // Second big retweet
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>'138', 'author_user_id'=>'23',
        'author_username'=>'jacob', 'author_fullname'=>'Jacob', 'network'=>'twitter',
        'post_text'=>'Meesa like ThinkUp',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'retweet_count_cache'=>0,
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
        'in_retweet_of_post_id'=>'134', 'location'=>'Mumbai, Maharashtra, India', 'geo'=>'19.017656,72.856178',
        'reply_retweet_distance'=>1500, 'is_geo_encoded'=>1));

        // Get data ready that insight requires
        $posts = array();
        $posts[] = $post1_object;
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = '22';
        $instance->network = 'twitter';
        $bigreshare_insight_plugin = new BigReshareInsight();
        $bigreshare_insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got generated
        $insight_dao = new InsightMySQLDAO();
        $yesterday = date ('Y-m-d', strtotime('-1 day'));
        $result = $insight_dao->getInsight('big_reshare_1345', 10, $yesterday);
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'big_reshare_1345');
        $this->assertEqual($result->filename, 'bigreshare');
        $this->assertPattern('/People with lots of followers retweeted/', $result->headline);
        $sharers = unserialize($result->related_data.people);
        $retweet_user = $sharers["people"][0];
        $this->assertEqual($retweet_user->description,
        '"Be liberal in what you accept and conservative in what you send"');
    }

    public function testSingleBigReshareWithLessThan2xFollowers() {
        // Assert that insight doesn't exist
        $insight_dao = new InsightMySQLDAO();
        $yesterday = date ('Y-m-d', strtotime('-1 day'));
        $result = $insight_dao->getInsight('big_reshare_1345', 10, $yesterday);
        $this->assertNull($result);

        //original post
        $post1_builder = FixtureBuilder::build('posts', array('id'=>1345, 'post_id'=>'134', 'author_user_id'=>22,
        'author_username'=>'quoter', 'author_fullname'=>'Quoter of Quotables', 'network'=>'twitter',
        'post_text'=>'Be liberal in what you accept and conservative in what you send', 'source'=>'web',
        'pub_date'=>'-1d', 'reply_count_cache'=>0, 'retweet_count_cache'=>0,
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
        'location'=>'New Delhi, Delhi, India', 'geo'=>'28.635308,77.22496', 'is_geo_encoded'=>1));

        $post1_object = new Post($post1_builder->columns);
        $builders = self::buildData();

        //retweet author has less than 2x more followers
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'20', 'user_name'=>'user1',
        'full_name'=>'User 1', 'avatar'=>'avatar.jpg', 'is_protected'=>0, 'follower_count'=>100,
        'network'=>'twitter'));

        // Get data ready that insight requires
        $posts = array();
        $posts[] = $post1_object;
        $instance = new Instance();
        $instance->id = 10;
        $instance->network_user_id = '22';
        $instance->network = 'twitter';
        $bigreshare_insight_plugin = new BigReshareInsight();
        $bigreshare_insight_plugin->generateInsight($instance, $posts, 3);

        // Assert that insight got generated
        $insight_dao = new InsightMySQLDAO();
        $yesterday = date ('Y-m-d', strtotime('-1 day'));
        $result = $insight_dao->getInsight('big_reshare_1345', 10, $yesterday);
        $this->assertNotNull($result);
        $this->assertEqual($result->slug, 'big_reshare_1345');
        $this->assertEqual($result->filename, 'bigreshare');
        $this->assertPattern('/Someone with lots of followers retweeted/', $result->headline);
        $sharers = unserialize($result->related_data.people);
        $retweet_user = $sharers["people"][0];
        $this->assertEqual($retweet_user->description,
        '"Be liberal in what you accept and conservative in what you send"');
    }

    private function buildData() {
        $builders = array();

        //add post authors
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'19', 'user_name'=>'linkbaiter',
        'full_name'=>'Link Baiter', 'avatar'=>'avatar.jpg', 'is_protected'=>0, 'follower_count'=>70,
        'network'=>'twitter'));

        //protected user
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'21', 'user_name'=>'user2',
        'full_name'=>'User 2', 'avatar'=>'avatar.jpg', 'is_protected'=>1, 'follower_count'=>80,
        'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'22', 'user_name'=>'quoter',
        'full_name'=>'Quotables', 'is_protected'=>0, 'follower_count'=>80, 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>'23', 'user_name'=>'jacob',
        'full_name'=>'Jacob', 'is_protected'=>0, 'follower_count'=>320, 'network'=>'twitter'));


        //Add retweets of a original post
        //retweet 1
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>'135', 'author_user_id'=>'20',
        'author_username'=>'user1', 'author_fullname'=>'User 1', 'network'=>'twitter',
        'post_text'=>'RT @quoter Be liberal in what you accept and conservative in what you send', 'source'=>'web',
        'pub_date'=>'-1d', 'reply_count_cache'=>0, 'retweet_count_cache'=>0,
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
        'in_retweet_of_post_id'=>'134', 'location'=>'Chennai, Tamil Nadu, India', 'geo'=>'13.060416,80.249634',
        'reply_retweet_distance'=>2000, 'is_geo_encoded'=>1, 'in_reply_to_post_id'=>null));
        //retweet 2
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>'136', 'author_user_id'=>'21',
        'author_username'=>'user2', 'author_fullname'=>'User 2', 'network'=>'twitter',
        'post_text'=>'RT @quoter Be liberal in what you accept and conservative in what you send',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'retweet_count_cache'=>0,
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
        'in_retweet_of_post_id'=>'134', 'location'=>'Dwarka, New Delhi, Delhi, India', 'geo'=>'28.635308,77.22496',
        'reply_retweet_distance'=>'0', 'is_geo_encoded'=>1));
        //retweet 3
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>'137', 'author_user_id'=>'19',
        'author_username'=>'linkbaiter', 'author_fullname'=>'Link Baiter', 'network'=>'twitter',
        'post_text'=>'RT @quoter Be liberal in what you accept and conservative in what you send',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'retweet_count_cache'=>0,
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
        'in_retweet_of_post_id'=>'134', 'location'=>'Mumbai, Maharashtra, India', 'geo'=>'19.017656,72.856178',
        'reply_retweet_distance'=>1500, 'is_geo_encoded'=>1));

        return $builders;
    }
}
