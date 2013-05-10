<?php
/**
 *
 * ThinkUp/tests/TestOfHashtagPostMySQLDAO.php
 *
 * Copyright (c) 2013 Eduard Cucurella
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
 * @copyright 2013  Eduard Cucurella
 * @author Eduard Cucurella
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfHashtagPostMySQLDAO extends ThinkUpUnitTestCase {
    /**
     * @var HashtagPostMySQLDAO
     */
    protected $hashtagpost_dao;
    /**
     * @var HashtagMySQLDAO
     */
    protected $hashtag_dao;

    public function setUp() {
        parent::setUp();
        $this->builders = self::buildData();
        $this->hashtagpost_dao = new HashtagPostMySQLDAO();
        $this->hashtag_dao = new HashtagMySQLDAO();
    }

    protected function buildData() {
        $builders[] = FixtureBuilder::build('hashtags',
        array('hashtag' => 'exist_yet', 'network'=>'twitter', 'count_cache' => 100));
        $builders[] = FixtureBuilder::build('posts', array('post_id' => '1', 'author_user_id' => '1',
            'author_username' => 'aun', 'author_fullname' => 'afn',
            'author_avatar' => 'http://aa.com', 'author_follower_count' => 0, 'post_text' => 'pt',
            'is_protected' => 0, 'source' => '<a href=""></a>', 'location' => 'BCN', 'place' => '',
            'place_id' => '', 'geo' => '', 'pub_date' => '2013-02-28 11:02:34', 'in_reply_to_user_id' => '1',
            'in_reply_to_post_id' => '1', 'reply_count_cache' => 1, 'is_reply_by_friend' => 0,
            'in_retweet_of_post_id' => '', 'old_retweet_count_cache' => 0, 'is_retweet_by_friend' => 0,
            'reply_retweet_distance' => 0, 'network' => 'twitter', 'is_geo_encoded' => 0,
            'in_rt_of_user_id' => '', 'retweet_count_cache' => 0, 'retweet_count_api' => 0,
            'favlike_count_cache' => 0));
        return $builders;
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    public function testInsertHashtag() {
        $ht_exist = 'exist_yet';
        $this->hashtagpost_dao->insertHashtagPost($ht_exist, '1', 'twitter');
        $res = $this->hashtagpost_dao->getHashtagsForPost('1', 'twitter');
        $this->assertEqual(sizeof($res), 1);
        $this->assertEqual($res[0]['post_id'],'1');
        $this->assertEqual($res[0]['hashtag_id'],1);
        $res = $this->hashtag_dao->getHashtagByID(1);
        $this->assertEqual($res->hashtag, 'exist_yet');
        $this->assertEqual($res->count_cache, 101);
        $ht_not_exist = 'not_exist_yet';
        $this->hashtagpost_dao->insertHashtagPost($ht_not_exist, '1', 'twitter');
        $res = $this->hashtagpost_dao->getHashtagsForPost('1', 'twitter');
        $this->assertEqual(sizeof($res), 2);
        $this->assertEqual($res[1]['post_id'],'1');
        $this->assertEqual($res[1]['hashtag_id'],2);
        $res = $this->hashtag_dao->getHashtagByID(2);
        $this->assertEqual($res->hashtag, 'not_exist_yet');
        $this->assertEqual($res->count_cache, 1);
    }

    public function testGetHashtagsForPost() {
        $builders = array();
        $builders[] = FixtureBuilder::build('hashtags_posts',
        array('post_id' => '1001', 'hashtag_id'=>1, 'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('hashtags_posts',
        array('post_id' => '1001', 'hashtag_id'=>2, 'network'=>'twitter'));

        $res = $this->hashtagpost_dao->getHashtagsForPost('1001', 'twitter');
        $this->assertEqual(sizeof($res), 2);
        $this->assertEqual($res[1]['hashtag_id'], 2);
    }

    public function testInsertHashtagPost() {
        $ht = 'bob';
        $this->hashtagpost_dao->insertHashtagPost($ht, '39089424330978176', 'twitter');
        $res = $this->hashtagpost_dao->getHashtagsForPost('39089424330978176', 'twitter');
        $this->assertEqual(sizeof($res), 1);
        $this->assertEqual($res[0]['post_id'],'39089424330978176');
        $this->assertEqual($res[0]['hashtag_id'],2); //setup generated 1 prev hashtag
    }

    public function testInsertHashtags() {
        $ht = array('bob', 'dole');
        $this->hashtagpost_dao->insertHashtagPosts($ht, '39089424330978176', 'twitter');
        $this->hashtagpost_dao->insertHashtagPosts($ht, '39089424330978177', 'twitter');
        $res1 = $this->hashtagpost_dao->getHashtagsForPost('39089424330978176', 'twitter');
        $res2 = $this->hashtagpost_dao->getHashtagsForPost('39089424330978177', 'twitter');
        $this->assertEqual(sizeof($res1), 2);
        $this->assertEqual(sizeof($res2), 2);
        $this->assertEqual($res2[1]['hashtag_id'],3); //setup generated 1 prev hashtag
        $res3 = $this->hashtag_dao->getHashtag('dole', 'twitter');
        $this->assertEqual($res3->count_cache, 2);
        $res4 = $this->hashtagpost_dao->getHashtagPostsByHashtagID(2); //setup has generated 2 prev hashtags
        $this->assertEqual(sizeof($res4), 2);
        $this->assertEqual($res4[0]['post_id'], '39089424330978176');
    }

    public function testDeleteHashtagsPostsByHashtagId() {
        $this->debug("Begin testDeleteHashtagsPostsByHashtagId");
        $this->hashtagpost_dao->insertHashtagPost('exist_yet', '39089424330978176', 'twitter');
        $this->hashtagpost_dao->insertHashtagPost('exist_yet', '39089424330978177', 'twitter');
        $this->hashtagpost_dao->insertHashtagPost('exist_yet', '39089424330978178', 'twitter');
        $this->hashtagpost_dao->insertHashtagPost('exist_yet', '39089424330978179', 'twitter');
        $res = $this->hashtagpost_dao->getHashtagPostsByHashtagID(1);
        $this->assertEqual(sizeof($res),4);
        $res = $this->hashtagpost_dao->deleteHashtagsPostsByHashtagID(1);
        $res = $this->hashtagpost_dao->getHashtagPostsByHashtagID(1);
        $this->assertEqual(sizeof($res),0);
        $this->debug("End testDeleteHashtagsPostsByHashtagId");
    }

    public function testIsHashtagPostInStorage() {
        $this->debug("Begin testIsHashtagPostInStorage");
        $this->hashtagpost_dao->insertHashtagPost('exist_yet', '39089424330978176', 'twitter');
        $this->assertTrue($this->hashtagpost_dao->isHashtagPostInStorage(1,'39089424330978176','twitter'));
        $this->assertFalse($this->hashtagpost_dao->isHashtagPostInStorage(3,5,'twitter'));
        $this->assertFalse($this->hashtagpost_dao->isHashtagPostInStorage(2,1,'twitter'));
        $this->debug("End testIsHashtagPostInStorage");
    }

    public function testGetTotalPostsByHashtagAndDate() {
        $this->debug("Begin testGetTotalPostsByHashtagAndDate");
        $builders = array();
        $builders[] = FixtureBuilder::build('hashtags',
        array('id'=>102, 'hashtag' => 'thinkupsavedsearch', 'network'=>'facebook'));

        //Test specified date
        $count = 0;
        while ($count < 12) { // Add 12 posts for a hashtag on 4/1
            $builders[] = FixtureBuilder::build('posts', array('post_id' => $count+2, 'author_user_id' => '1',
                'author_username' => 'aun', 'author_fullname' => 'afn',
                'author_avatar' => 'http://aa.com', 'author_follower_count' => 0, 'post_text' => 'pt',
                'is_protected' => 0, 'source' => '<a href=""></a>', 'location' => 'BCN', 'place' => '',
                'place_id' => '', 'geo' => '', 'pub_date' => '2013-04-01 11:02:34', 'in_reply_to_user_id' => '1',
                'in_reply_to_post_id' => '1', 'reply_count_cache' => 1, 'is_reply_by_friend' => 0,
                'in_retweet_of_post_id' => '', 'old_retweet_count_cache' => 0, 'is_retweet_by_friend' => 0,
                'reply_retweet_distance' => 0, 'network' => 'facebook', 'is_geo_encoded' => 0,
                'in_rt_of_user_id' => '', 'retweet_count_cache' => 0, 'retweet_count_api' => 0,
                'favlike_count_cache' => 0));
            $builders[] = FixtureBuilder::build('hashtags_posts', array('post_id' => $count+2, 'hashtag_id' => 102,
                'network'=>'facebook'));
            $count++;
        }
        $count = $this->hashtagpost_dao->getTotalPostsByHashtagAndDate(102, '2013-04-01');
        $this->assertEqual($count, 12);

        //Test today
        $today = date('Y-m-d H:i:s');
        $count = 0;
        while ($count < 7) { // Add 7 posts for a hashtag today
            $builders[] = FixtureBuilder::build('posts', array('post_id' => $count+14, 'author_user_id' => '1',
                'author_username' => 'aun', 'author_fullname' => 'afn',
                'author_avatar' => 'http://aa.com', 'author_follower_count' => 0, 'post_text' => 'pt',
                'is_protected' => 0, 'source' => '<a href=""></a>', 'location' => 'BCN', 'place' => '',
                'place_id' => '', 'geo' => '', 'pub_date' => $today, 'in_reply_to_user_id' => '1',
                'in_reply_to_post_id' => '1', 'reply_count_cache' => 1, 'is_reply_by_friend' => 0,
                'in_retweet_of_post_id' => '', 'old_retweet_count_cache' => 0, 'is_retweet_by_friend' => 0,
                'reply_retweet_distance' => 0, 'network' => 'facebook', 'is_geo_encoded' => 0,
                'in_rt_of_user_id' => '', 'retweet_count_cache' => 0, 'retweet_count_api' => 0,
                'favlike_count_cache' => 0));
            $builders[] = FixtureBuilder::build('hashtags_posts', array('post_id' => $count+14, 'hashtag_id' => 102,
                'network'=>'facebook'));
            $count++;
        }
        $count = $this->hashtagpost_dao->getTotalPostsByHashtagAndDate(102);
        $this->assertEqual($count, 7);

        $this->debug("End testGetTotalPostsByHashtagAndDate");
    }

}
