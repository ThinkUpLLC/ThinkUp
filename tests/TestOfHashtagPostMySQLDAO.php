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
     *
     * @var HashtagPostMySQLDAO
     */
    protected $dao_hp;
    /**
     *
     * @var HashtagMySQLDAO
     */
    protected $dao_h;
    
    public function setUp() {
        parent::setUp();
        $this->builders = self::buildData();
        $this->dao_hp = new HashtagPostMySQLDAO();
        $this->dao_h = new HashtagMySQLDAO();
        }

    protected function buildData() {
        $builders[] = FixtureBuilder::build('hashtags', 
            array('hashtag' => 'exist_yet', 'network'=>'twitter', 'count_cache' => 100));
        $builders[] = FixtureBuilder::build('posts',
            array('post_id' => '1', 'author_user_id' => '1', 'author_username' => 'aun', 'author_fullname' => 'afn',
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
        $this->dao_hp->insertHashtag($ht_exist, '1', 'twitter');
        $res = $this->dao_h->getHashtagsForPost('1');
        $this->assertEqual(sizeof($res), 1);
        $this->assertEqual($res[0]['post_id'],'1');
        $this->assertEqual($res[0]['hashtag_id'],1); 
        $res = $this->dao_h->getByHashtag(1);
        $this->assertEqual($res->hashtag, 'exist_yet');
        $this->assertEqual($res->count_cache, 101);
        $ht_not_exist = 'not_exist_yet';
        $this->dao_hp->insertHashtag($ht_not_exist, '1', 'twitter');
        $res = $this->dao_h->getHashtagsForPost('1');
        $this->assertEqual(sizeof($res), 2);
        $this->assertEqual($res[1]['post_id'],'1');
        $this->assertEqual($res[1]['hashtag_id'],2);        
        $res = $this->dao_h->getByHashtag(2);
        $this->assertEqual($res->hashtag, 'not_exist_yet');
        $this->assertEqual($res->count_cache, 1);
    }
 }
