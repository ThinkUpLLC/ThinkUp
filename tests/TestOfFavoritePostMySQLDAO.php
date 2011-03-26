<?php
/**
 *
 * ThinkUp/tests/TestOfFavoritePostMySQLDAO.php
 *
 * Copyright (c) 2009-2011 Amy Unruh, Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Amy Unruh, Gina Trapani
 * @author Amy Unruh
 */

require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterPlugin.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/facebook/model/class.FacebookPlugin.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.URLProcessor.php';

class TestOfFavoritePostMySQLDAO extends ThinkUpUnitTestCase {
    /**
     *
     * @var FavoritePostMySQLDAO
     */
    protected $dao;
    /**
     * Constructor
     */
    public function __construct() {
        $this->UnitTestCase('FavoritePostMySQLDAO class test');
    }

    public function setUp() {
        parent::setUp();
        $config = Config::getInstance();
        $this->prefix = $config->getValue('table_prefix');
        $this->builders = self::buildData();
    }

    protected function buildData() {

        $builders = array();
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>1));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>13, 'user_name'=>'ev',
        'full_name'=>'Ev Williams', 'avatar'=>'avatar.jpg', 'is_protected'=>0, 'follower_count'=>10,
        'last_updated'=>'1/1/2005', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>19, 'user_name'=>'linkbaiter',
        'full_name'=>'Link Baiter', 'avatar'=>'avatar.jpg', 'is_protected'=>0, 'follower_count'=>70,
        'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>20, 'user_name'=>'user1',
        'full_name'=>'User 1', 'avatar'=>'avatar.jpg', 'is_protected'=>0, 'follower_count'=>90,
        'network'=>'twitter'));

        //protected user
        $builders[] = FixtureBuilder::build('users', array('user_id'=>21, 'user_name'=>'user2',
        'full_name'=>'User 2', 'avatar'=>'avatar.jpg', 'is_protected'=>1, 'follower_count'=>80,
        'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>22, 'user_name'=>'quoter',
        'full_name'=>'Quotables', 'is_protected'=>0, 'follower_count'=>80, 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>23, 'user_name'=>'user3',
        'full_name'=>'User 3', 'is_protected'=>0, 'follower_count'=>100, 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>24, 'user_name'=>'notonpublictimeline',
        'full_name'=>'Not on Public Timeline', 'is_protected'=>1, 'network'=>'twitter', 'follower_count'=>100));

        //Make public
        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>13, 'network_username'=>'ev',
        'is_public'=>1, 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>19, 'network_username'=>'linkbaiter',
        'is_public'=>1, 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>23, 'network_username'=>'user3',
        'is_public'=>1, 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>24,
        'network_username'=>'notonpublictimeline', 'is_public'=>0, 'network'=>'twitter'));

        //Add straight text posts from ev
        $counter = 0;
        while ($counter < 40) {
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
            if ($counter % 3 == 0) {
                $source = '<a href="http://twitter.com" rel="nofollow">Tweetie for Mac</a>';
            } else if ($counter % 3 == 1) {
                $source = '<a href="http://twitter.com/tweetbutton" rel="nofollow">Tweet Button</a>';
            } else {
                $source = 'web';
            }
            $builders[] = FixtureBuilder::build('posts', array('post_id'=>$counter, 'author_user_id'=>13,
            'author_username'=>'ev', 'author_fullname'=>'Ev Williams', 'author_avatar'=>'avatar.jpg', 
            'post_text'=>'This is post '.$counter, 'source'=>$source, 'pub_date'=>'2006-01-01 00:'.
            $pseudo_minute.':00', 'reply_count_cache'=>rand(0, 4), 'retweet_count_cache'=>5, 'network'=>'twitter',
            'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
            $counter++;
        }

        //Add link posts from 'linkbaiter'
        $counter = 0;
        while ($counter < 40) {
            $post_id = $counter + 80;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);
            $builders[] = FixtureBuilder::build('posts', array('post_id'=>$post_id, 'author_user_id'=>19,
            'author_username'=>'linkbaiter', 'author_fullname'=>'Link Baiter', 'is_geo_encoded'=>0,
            'post_text'=>'This is link post '.$counter, 'source'=>'web', 'pub_date'=>'2006-03-01 00:'.
            $pseudo_minute.':00', 'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'twitter'));

            $builders[] = FixtureBuilder::build('links', array('url'=>'http://example.com/'.$counter,
            'explanded_url'=>'http://example.com/'.$counter.'.html', 'title'=>'Link $counter', 'clicks'=>0, 
            'post_id'=>$post_id, 'is_image'=>0));

            $counter++;
        }

        $builders[] = FixtureBuilder::build('posts', array('post_id'=>10822735852740608, 'author_user_id'=>23,
        'author_username'=>'user3', 'author_fullname'=>'User 3', 'network'=>'twitter', 
        'post_text'=>'@nytimes has posted an interactive panoramic photo that shows how Times Square has changed over'.
        ' the last 20 years http://nyti.ms/hmTVzP', 
        'source'=>'web', 'pub_date'=>'-300s', 'reply_count_cache'=>0, 'retweet_count_cache'=>0, 
        'location'=>'New York City', 'is_geo_encoded'=>0));

        // have 'user1' favorite some of ev's posts
        for ($i = 0; $i < 20; $i++) {
            $builders[] = FixtureBuilder::build('favorites', array('post_id'=>$i,
            'author_user_id'=>13, 'fav_of_user_id'=>20, 'network'=>'twitter'));
        }
        // have 'user1' favorite some linkbaiter posts
        for ($i = 80; $i < 100; $i++) {
            $builders[] = FixtureBuilder::build('favorites', array('post_id'=>$i,
            'author_user_id'=>19, 'fav_of_user_id'=>20, 'network'=>'twitter'));
        }
        // have 'user2' favorite one of the same linkbaiter posts
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>87,
        'author_user_id'=>19, 'fav_of_user_id'=>21, 'network'=>'twitter'));

        return $builders;
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    /**
     * Test constructor
     */
    public function testConstructor() {
        $dao = new FavoritePostMySQLDAO();
        $this->assertTrue(isset($dao));
    }

    /**
     * Test creation of fav post, where the post has not yet been saved to database.
     */
    public function testFavPostCreation() {
        $dao = new FavoritePostMySQLDAO();
        $favoriter_id = 21; //user 2
        $vals = $this->buildPostArray1();
        $res = $dao->addFavorite($favoriter_id, $vals);
        $this->assertEqual($res, 1);
    }

    /**
     * Test creation of fav post, where post already exists in db, but not favorite bookkeeping,
     * and so we are just adding an entry to the favorites table.
     */
    public function testFavPostCreationPostExists() {
        $dao = new FavoritePostMySQLDAO();
        $favoriter_id = 21; //user 2
        $vals = $this->buildPostArray2();
        $res = $dao->addFavorite($favoriter_id, $vals);
        $this->assertEqual($res, 1);
        // now try again-- this time the 'add' should return 0
        $res = $dao->addFavorite($favoriter_id, $vals);
        $this->assertEqual($res, 0);
    }

    /**
     * Test unfavoriting of fav'd post
     */
    public function testFavPostUnfav() {
        $dao = new FavoritePostMySQLDAO();
        $res = $dao->unFavorite(81, 20, 'twitter');
        $this->assertEqual($res, 1);
    }

    /**
     * Test attempted unfav of a post that is favorited, but not by the given user
     */
    public function testNonFavPostUnfav() {
        $dao = new FavoritePostMySQLDAO();
        $res = $dao->unFavorite(82, 19, 'twitter');
        $this->assertEqual($res, 0);
    }

    /**
     * Check unfavoriting the same post by multiple users
     */
    public function testMultipleFavsOfPost() {
        $dao = new FavoritePostMySQLDAO();
        $res = $dao->unFavorite(87, 20, 'twitter');
        $this->assertEqual($res, 1);
        $res = $dao->unFavorite(87, 21, 'twitter');
        $this->assertEqual($res, 1);
        $res = $dao->unFavorite(87, 20, 'twitter');
        $this->assertEqual($res, 0);
    }

    /**
     * Test fetch of N favorited posts for a given user by userid
     */
    public function testGetAllFavsForUserID() {
        $dao = new FavoritePostMySQLDAO();
        $res = $dao->getAllFavoritePosts(20, 'twitter', 6);
        $this->assertIsA($res, "array");
        $this->assertEqual(count($res), 6);
        $this->assertEqual($res[0]->post_text, 'This is link post 19');
    }

    /**
     * Test fetch of all favorited posts for a given user by username
     */
    public function testGetAllFavsForUsername() {
        $dao = new FavoritePostMySQLDAO();
        $res = $dao->getAllFavoritePostsByUsername('user1', 'twitter', 100);
        $this->assertIsA($res, "array");
        $this->assertEqual(count($res), 40);
    }

    /**
     * Test fetch of all favorited posts for a given user with post # less than a given upper bound.
     */
    public function testGetAllFavsForUserUpperBound() {
        $dao = new FavoritePostMySQLDAO();
        $res = $dao->getAllFavoritePostsUpperBound(20, 'twitter', 100, 10);
        $this->assertIsA($res, "array");
        $this->assertEqual(count($res), 10);
    }

    /**
     * Test pagination
     */
    public function testFavoritesPagination() {
        $dao = new FavoritePostMySQLDAO();
        $res = $dao->getAllFavoritePosts(20, 'twitter', 10, 3); // fetch page 3
        $this->assertIsA($res, "array");
        $this->assertEqual(count($res), 10);
        $this->assertEqual($res[0]->post_text, 'This is post 19');
    }

    /**
     * helper method to build a post
     */
    private function buildPostArray1() {
        $dao = new PostMySQLDAO();
        $vals = array();
        $vals['post_id']=2904;
        $vals['author_username']='quoter';
        $vals['author_fullname']="Quoter of Quotables";
        $vals['author_avatar']='avatar.jpg';
        $vals['author_user_id']= 22;
        $vals['post_text']="Go confidently in the direction of your dreams! Live the life you've imagined.";
        $vals['pub_date']='3/1/2010';
        $vals['source']='web';
        $vals['network']= 'twitter';
        $vals['is_protected'] = 0;
        return $vals;
    }

    /**
     * helper method to build a post
     */
    private function buildPostArray2() {
        $dao = new PostMySQLDAO();
        $vals = array();
        $vals['post_id']=10822735852740608;
        $vals['author_username']='user3';
        $vals['author_fullname']="User 3";
        $vals['author_avatar']='avatar.jpg';
        $vals['author_user_id']= 23;
        $vals['post_text']="@nytimes has posted an interactive panoramic photo that shows how Times Square has ".
        "changed over the last 20 years http://nyti.ms/hmTVzP";
        $vals['pub_date']='-200s';
        $vals['source']='web';
        $vals['network']= 'twitter';
        $vals['is_protected'] = 0;
        return $vals;
    }
}
