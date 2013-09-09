<?php
/**
 *
 * ThinkUp/tests/TestOfHashtagMySQLDAO.php
 *
 * Copyright (c) 2011-2013 Amy Unruh
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013  Amy Unruh
 * @author Amy Unruh
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfHashtagMySQLDAO extends ThinkUpUnitTestCase {
    /**
     *
     * @var HashtagMySQLDAO
     */
    protected $dao;

    public function setUp() {
        parent::setUp();
        $this->builders = self::buildData();
        $this->dao = new HashtagMySQLDAO();
    }

    protected function buildData() {
        //random test data to check basic retrieval
        for ($i = 1; $i < 3; $i++) {
            $ht = 'hashtag' . ($i +10);
            $builders[] = FixtureBuilder::build('hashtags',
            array('hashtag' => $ht, 'network'=>'twitter', 'count_cache' => $i + 3));
            $builders[] = FixtureBuilder::build('hashtags_posts',
            array('post_id' => 1000 + $i,'hashtag_id' => $i, 'network' => 'twitter'));
            $builders[] = FixtureBuilder::build('hashtags_posts',
            array('post_id' => 1000 + $i, 'hashtag_id' => $i+100, 'network' => 'twitter'));
        }

        $builders[] = FixtureBuilder::build('instances',
        array(
                        'network_user_id' => '1',
                        'network_viewer_id' => '1',
                        'network_username' => 'ecucurella',
                        'last_post_id'  => '1',
                        'crawler_last_run' => '2013-02-28 15:21:16',
                        'total_posts_by_owner' => 0,
                        'total_posts_in_system' => 0,
                        'total_replies_in_system' => 0,
                        'total_follows_in_system' => 0,
                        'posts_per_day' => 0,
                        'posts_per_week' => 0,
                        'percentage_replies' => 0,
                        'percentage_links' => 0,
                        'earliest_post_in_system' => '2013-02-28 15:21:16',
                        'earliest_reply_in_system' => '2013-02-28 15:21:16',
                        'is_archive_loaded_posts' => 0,
                        'is_archive_loaded_replies' => 0,
                        'is_archive_loaded_follows' => 0,
                        'is_public' => 0,
                        'is_active' => 0,
                        'network'  => 'twitter',
                        'favorites_profile' => 0,
                        'owner_favs_in_system' => 0));

        $builders[] = FixtureBuilder::build('instances_hashtags',
        array(
                        'instance_id' => 1,
                        'hashtag_id' => 1,
                        'last_post_id'  => '0',
                        'earliest_post_id' => '0'));

        $builders[] = FixtureBuilder::build('instances_hashtags',
        array(
                        'instance_id' => 1,
                        'hashtag_id' => 2,
                        'last_post_id'  => '0',
                        'earliest_post_id' => '0'));

        $builders[] = FixtureBuilder::build('hashtags',
        array('hashtag' => '#hashtag111', 'network'=>'twitter', 'count_cache' => 4));

        $builders[] = FixtureBuilder::build('hashtags_posts',
        array('post_id' => 1, 'hashtag_id' => 3, 'network' => 'twitter'));
        $builders[] = FixtureBuilder::build('hashtags_posts',
        array('post_id' => 2, 'hashtag_id' => 3, 'network' => 'twitter'));
        $builders[] = FixtureBuilder::build('hashtags_posts',
        array('post_id' => 3, 'hashtag_id' => 3, 'network' => 'twitter'));
        $builders[] = FixtureBuilder::build('hashtags_posts',
        array('post_id' => 4, 'hashtag_id' => 3, 'network' => 'twitter'));

        $builders[] = FixtureBuilder::build('posts',
        array(
                        'post_id' => '1',
                        'author_user_id' => '100',
                        'author_username' => 'ecucurella',
                        'author_fullname' => 'Eduard Cucurella',
                        'author_avatar' => 'http://aa.com',
                        'author_follower_count' => 0,
                        'post_text' => '#hashtag11',
                        'is_protected' => 0,
                        'source' => '<a href=""></a>',
                        'location' => 'BCN',
                        'place' => '',
                        'place_id' => '',
                        'geo' => '',
                        'pub_date' => '2013-02-28 11:02:34',
                        'in_reply_to_user_id' => '',
                        'in_reply_to_post_id' => '',
                        'reply_count_cache' => 1,
                        'is_reply_by_friend' => 0,
                        'in_retweet_of_post_id' => '',
                        'old_retweet_count_cache' => 0,
                        'is_retweet_by_friend' => 0,
                        'reply_retweet_distance' => 0,
                        'network' => 'twitter',
                        'is_geo_encoded' => 0,
                        'in_rt_of_user_id' => '',
                        'retweet_count_cache' => 0,
                        'retweet_count_api' => 0,
                        'favlike_count_cache' => 0));

        $builders[] = FixtureBuilder::build('posts',
        array(
                        'post_id' => '2',
                        'author_user_id' => '101',
                        'author_username' => 'vetcastellnou',
                        'author_fullname' => 'Veterans Castellnou',
                        'author_avatar' => 'http://aa.com',
                        'author_follower_count' => 0,
                        'post_text' => '#hashtag11',
                        'is_protected' => 0,
                        'source' => '<a href=""></a>',
                        'location' => 'BCN',
                        'place' => '',
                        'place_id' => '',
                        'geo' => '',
                        'pub_date' => '2013-02-28 11:02:34',
                        'in_reply_to_user_id' => '',
                        'in_reply_to_post_id' => '',
                        'reply_count_cache' => 1,
                        'is_reply_by_friend' => 0,
                        'in_retweet_of_post_id' => '',
                        'old_retweet_count_cache' => 0,
                        'is_retweet_by_friend' => 0,
                        'reply_retweet_distance' => 0,
                        'network' => 'twitter',
                        'is_geo_encoded' => 0,
                        'in_rt_of_user_id' => '',
                        'retweet_count_cache' => 0,
                        'retweet_count_api' => 0,
                        'favlike_count_cache' => 0));

        $builders[] = FixtureBuilder::build('posts',
        array(
                        'post_id' => '3',
                        'author_user_id' => '102',
                        'author_username' => 'efectivament',
                        'author_fullname' => 'efectivament',
                        'author_avatar' => 'http://aa.com',
                        'author_follower_count' => 0,
                        'post_text' => '#hashtag11',
                        'is_protected' => 0,
                        'source' => '<a href=""></a>',
                        'location' => 'BCN',
                        'place' => '',
                        'place_id' => '',
                        'geo' => '',
                        'pub_date' => '2013-02-28 11:02:34',
                        'in_reply_to_user_id' => '',
                        'in_reply_to_post_id' => '',
                        'reply_count_cache' => 1,
                        'is_reply_by_friend' => 0,
                        'in_retweet_of_post_id' => '',
                        'old_retweet_count_cache' => 0,
                        'is_retweet_by_friend' => 0,
                        'reply_retweet_distance' => 0,
                        'network' => 'twitter',
                        'is_geo_encoded' => 0,
                        'in_rt_of_user_id' => '',
                        'retweet_count_cache' => 0,
                        'retweet_count_api' => 0,
                        'favlike_count_cache' => 0));

        $builders[] = FixtureBuilder::build('posts',
        array(
                        'post_id' => '4',
                        'author_user_id' => '102',
                        'author_username' => 'efectivament',
                        'author_fullname' => 'efectivament',
                        'author_avatar' => 'http://aa.com',
                        'author_follower_count' => 0,
                        'post_text' => '#hashtag11',
                        'is_protected' => 0,
                        'source' => '<a href=""></a>',
                        'location' => 'BCN',
                        'place' => '',
                        'place_id' => '',
                        'geo' => '',
                        'pub_date' => '2013-02-28 11:02:34',
                        'in_reply_to_user_id' => '',
                        'in_reply_to_post_id' => '',
                        'reply_count_cache' => 1,
                        'is_reply_by_friend' => 0,
                        'in_retweet_of_post_id' => '',
                        'old_retweet_count_cache' => 0,
                        'is_retweet_by_friend' => 0,
                        'reply_retweet_distance' => 0,
                        'network' => 'twitter',
                        'is_geo_encoded' => 0,
                        'in_rt_of_user_id' => '',
                        'retweet_count_cache' => 0,
                        'retweet_count_api' => 0,
                        'favlike_count_cache' => 0));

        return $builders;
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    public function testGetHashtag() {
        $res = $this->dao->getHashtag('hashtag11', 'twitter');
        $this->assertIsA($res, 'Hashtag');
        $this->assertEqual($res->count_cache, 4);
    }

    public function testGetByHashtag() {
        $this->debug("Begin testGetByHashtag");
        for ($i = 1; $i < 3; $i++) {
            $res = $this->dao->getHashtag('hashtag'.($i+10), 'twitter');
            $this->assertEqual($res->hashtag, 'hashtag'.($i+10));
            $this->assertEqual($res->network, 'twitter');
            $this->assertEqual($res->count_cache, ($i+3));
        }
        $this->debug("End testGetByHashtag");
    }

    public function testDeleteHashtagByID() {
        $this->debug("Begin testDeleteHashtagByHashtagId");
        $res = $this->dao->getHashtagByID(1);
        $this->assertIsA($res, 'Hashtag');
        $res = $this->dao->deleteHashtagByID(1);
        $this->assertTrue($res);
        $res = $this->dao->getHashtagByID(1);
        $this->assertNull($res);
        $res = $this->dao->getHashtagByID(2);
        $this->assertIsA($res, 'Hashtag');
        $res = $this->dao->deleteHashtagByID(2);
        $this->assertTrue($res);
        $res = $this->dao->getHashtagByID(2);
        $this->assertNull($res);
        $res = $this->dao->deleteHashtagByID(256);
        $this->assertFalse($res);
        $this->debug("End testDeleteHashtagByHashtagId");
    }

    public function testInsertHashtag() {
        $this->debug("Begin testInsertHashtagByHashtagName");
        $res = $this->dao->getHashtag('#mwc2013', 'twitter');
        $this->assertNull($res);
        $res = $this->dao->insertHashtag('#mwc2013', 'twitter');
        $this->assertEqual($res,4);
        $res = $this->dao->getHashtag('#mwc2013', 'twitter');
        $this->assertNotNull($res);
        $this->assertEqual($res->hashtag,'#mwc2013');
        $this->debug("End testInsertHashtagByHashtagName");
    }
}
