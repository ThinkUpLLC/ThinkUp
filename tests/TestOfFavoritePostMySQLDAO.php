<?php
/**
 *
 * ThinkUp/tests/TestOfFavoritePostMySQLDAO.php
 *
 * Copyright (c) 2009-2013 Amy Unruh, Gina Trapani
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Amy Unruh, Gina Trapani
 * @author Amy Unruh
 */

require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterPlugin.php';
require_once THINKUP_WEBAPP_PATH.'plugins/facebook/model/class.FacebookPlugin.php';

class TestOfFavoritePostMySQLDAO extends ThinkUpUnitTestCase {
    /**
     *
     * @var FavoritePostMySQLDAO
     */
    protected $dao;

    public function setUp() {
        parent::setUp();
        $config = Config::getInstance();
        $this->builders = self::buildData();
        $this->dao = new FavoritePostMySQLDAO();
    }

    protected function buildData() {

        $builders = array();
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>1));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>13, 'user_name'=>'ev',
        'full_name'=>'Ev Williams', 'avatar'=>'avatar.jpg', 'is_protected'=>0, 'follower_count'=>10,
        'last_updated'=>'2005-01-01 13:48:05', 'network'=>'twitter'));

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
            $is_protected = $counter == 18 ? 1 : 0; // post with id 18 is protected
            $builders[] = FixtureBuilder::build('posts', array('post_id'=>$counter, 'author_user_id'=>13,
            'author_username'=>'ev', 'author_fullname'=>'Ev Williams', 'author_avatar'=>'avatar.jpg',
            'post_text'=>'This is post '.$counter, 'source'=>$source, 'pub_date'=>'2006-01-01 00:'.
            $pseudo_minute.':00', 'reply_count_cache'=>rand(0, 4), 'retweet_count_cache'=>5, 'network'=>'twitter',
            'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0,
            'is_protected' => $is_protected));
            $counter++;
        }

        //Add link posts from 'linkbaiter'
        $counter = 0;
        while ($counter < 40) {
            $post_id = $counter + 80;
            $is_protected = $counter == 18 ? 1 : 0; // post with id 18 is protected
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);
            $builders[] = FixtureBuilder::build('posts', array('post_id'=>$post_id, 'author_user_id'=>19,
            'author_username'=>'linkbaiter', 'author_fullname'=>'Link Baiter', 'is_geo_encoded'=>0,
            'post_text'=>'This is link post '.$counter, 'source'=>'web', 'pub_date'=>'2006-03-01 00:'.
            $pseudo_minute.':00', 'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'twitter',
            'is_protected' => $is_protected));

            $builders[] = FixtureBuilder::build('links', array('url'=>'http://example.com/'.$counter,
            'explanded_url'=>'http://example.com/'.$counter.'.html', 'title'=>'Link $counter', 'clicks'=>0,
            'post_id'=>$post_id, 'image_src'=>''));

            $counter++;
        }

        $builders[] = FixtureBuilder::build('posts', array('post_id'=>10822735852740608, 'author_user_id'=>23,
        'author_username'=>'user3', 'author_fullname'=>'User 3', 'network'=>'twitter',
        'post_text'=>'@nytimes has posted an interactive panoramic photo that shows how Times Square has changed over'.
        ' the last 20 years http://nyti.ms/hmTVzP',
        'source'=>'web', 'pub_date'=>'-300s', 'reply_count_cache'=>0, 'retweet_count_cache'=>0,
        'location'=>'New York City', 'is_geo_encoded'=>0, 'is_protected' => 0));

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
     * Test constructor both directly and via factory
     */
    public function testConstructor() {
        $dao = new FavoritePostMySQLDAO();
        $this->assertTrue(isset($dao));
        $dao = DAOFactory::getDAO('FavoritePostDAO');
        $this->assertTrue(isset($dao));
    }

    /**
     * Test creation of fav post, where the post has not yet been saved to database.
     */
    public function testAddFavoriteFullPost() {
        $favoriter_id = 21; //user 2
        $vals = $this->buildPostArray1();
        $res = $this->dao->addFavorite($favoriter_id, $vals);
        $this->assertEqual($res, 1);
    }

    public function testAddFavoriteMissingPostData() {
        $favoriter_id = 21; //user 2
        $vals = $this->buildFavoriteArray();
        $this->expectException('Exception',
        'Error: Favorited post ID 345840895515801 is not in storage and could not be inserted.');
        $res = $this->dao->addFavorite($favoriter_id, $vals);
        $this->assertEqual($res, 1);
    }

    /**
     * Test creation of fav post, where post already exists in db, but not favorite bookkeeping,
     * and so we are just adding an entry to the favorites table.
     */
    public function testAddFavoritePostExists() {
        $favoriter_id = 21; //user 2
        $vals = $this->buildPostArray2();
        $res = $this->dao->addFavorite($favoriter_id, $vals);
        $this->assertEqual($res, 1);
        // now try again-- this time the 'add' should return 0
        $res = $this->dao->addFavorite($favoriter_id, $vals);
        $this->assertEqual($res, 0);
    }

    /**
     * Test unfavoriting of fav'd post
     */
    public function testFavPostUnfav() {
        $res = $this->dao->unFavorite(81, 20, 'twitter');
        $this->assertEqual($res, 1);
    }

    /**
     * Test attempted unfav of a post that is favorited, but not by the given user
     */
    public function testNonFavPostUnfav() {
        $res = $this->dao->unFavorite(82, 19, 'twitter');
        $this->assertEqual($res, 0);
    }

    /**
     * Check unfavoriting the same post by multiple users
     */
    public function testMultipleFavsOfPost() {
        $this->dao = new FavoritePostMySQLDAO();
        $res = $this->dao->unFavorite(87, 20, 'twitter');
        $this->assertEqual($res, 1);
        $res = $this->dao->unFavorite(87, 21, 'twitter');
        $this->assertEqual($res, 1);
        $res = $this->dao->unFavorite(87, 20, 'twitter');
        $this->assertEqual($res, 0);
    }

    /**
     * Test fetch of N favorited posts for a given user by userid
     */
    public function testGetAllFavsForUserID() {
        $this->dao = new FavoritePostMySQLDAO();
        $res = $this->dao->getAllFavoritePosts(20, 'twitter', 6, 1, false); //not public
        $this->assertIsA($res, "array");
        $this->assertEqual(count($res), 6);
        $this->assertEqual($res[0]->post_text, 'This is link post 19');
        $this->assertEqual($res[1]->post_text, 'This is link post 18');
        $i = 0;
        while ($i < 6) {
            $this->debug( $res[$i]->post_text );
            $i++;
        }
        // just check that we get the same result w/out the explicit arg
        $res = $this->dao->getAllFavoritePosts(20, 'twitter', 6); //not public
        $this->assertIsA($res, "array");
        $this->assertEqual(count($res), 6);
        $this->assertEqual($res[0]->post_text, 'This is link post 19');
        $this->assertEqual($res[1]->post_text, 'This is link post 18');

        //iterator
        $res = $this->dao->getAllFavoritePostsIterator(20, 'twitter', 6);
        $this->assertIsA($res, "PostIterator");
        $i = 0;
        while ($i < 6) {
            $this->assertTrue($res->valid());
            $seeme = $res->current();
            $this->debug( $seeme->post_text );
            $res->next();
            $i++;
        }
        $this->assertFalse($res->valid());
    }

    /**
     * Test fetch of N favorited posts for a given user by userid, but where public = true.
     * post with id 18 should not be fetched this time
     */
    public function testGetAllFavsForUserID2() {
        $this->dao = new FavoritePostMySQLDAO();
        $res = $this->dao->getAllFavoritePosts(20, 'twitter', 6, 1, true); // public
        $this->assertIsA($res, "array");
        $this->assertEqual(count($res), 6);
        $this->assertEqual($res[0]->post_text, 'This is link post 19');
        $this->assertEqual($res[1]->post_text, 'This is link post 17');
        $i = 0;
        while ($i < 6) {
            $this->debug( $res[$i]->post_text );
            $i++;
        }
    }

    /**
     * Test fetch of all favorited posts for a given user by username
     */
    public function testGetAllFavsForUsername() {
        $dao = new FavoritePostMySQLDAO();
        $res = $this->dao->getAllFavoritePostsByUsername('user1', 'twitter', 100);
        $this->assertIsA($res, "array");
        $this->assertEqual(count($res), 40);

        //iterator
        $res = $this->dao->getAllFavoritePostsByUsernameIterator('user1', 'twitter', 100);
        $this->assertIsA($res, "PostIterator");
        $i = 0;
        while ($i < 40) {
            $this->assertTrue($res->valid());
            $res->next();
            $i++;
        }
        $this->assertFalse($res->valid());
    }

    /**
     * Test fetch of all favorited posts for a given user with post # less than a given upper bound.
     */
    public function testGetAllFavsForUserUpperBound() {
        $res = $this->dao->getAllFavoritePostsUpperBound(20, 'twitter', 100, 10);
        $this->assertIsA($res, "array");
        $this->assertEqual(count($res), 10);
    }

    /**
     * Test pagination
     */
    public function testFavoritesPagination() {
        $res = $this->dao->getAllFavoritePosts(20, 'twitter', 10, 3); // fetch page 3
        $this->assertIsA($res, "array");
        $this->assertEqual(count($res), 10);
        $this->assertEqual($res[0]->post_text, 'This is post 19');
    }

    /**
     * test fetch of all posts of a given owner that have been favorited by others
     */
    public function testGetAllFavoritedPosts() {
        $res = $this->dao->getAllFavoritedPosts(19, 'twitter', 30);
        $this->assertIsA($res, "array");
        $this->assertEqual(count($res), 20);
        $this->assertEqual($res[0]->post_text, 'This is link post 7');
        $this->assertEqual($res[1]->post_text, 'This is link post 19');
    }

    /**
     * test fetch of information for all users who have favorited a given post
     */
    public function testGetFavdsOfPost() {
        $res = $this->dao->getUsersWhoFavedPost(87); // twitter is default network
        $this->assertIsA($res, "array");
        $this->assertEqual(count($res), 2);
        $res = $this->dao->getUsersWhoFavedPost(87, 'twitter', true);
        $this->assertIsA($res, "array");
        $this->assertEqual(count($res), 2);
    }

    public function testGetFavoritesFromOneYearAgo() {
        //build post published one year ago today
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>'abadadfd1212', 'author_user_id'=>'19',
        'author_username'=>'linkbaiter', 'author_fullname'=>'Link Baiter', 'is_geo_encoded'=>0,
        'post_text'=>'This is link post '.$counter, 'source'=>'web', 'pub_date'=>'-365d',
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'twitter',
        'is_protected' => 0));

        //build favorite of that post by test user ev
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'abadadfd1212', 'author_user_id'=>'19',
        'fav_of_user_id'=>'13', 'network'=>'twitter'));

        //get favorites from one year ago today
        $result = $this->dao->getFavoritesFromOneYearAgo('13', 'twitter');

        //assert post is returned
        $this->assertEqual(sizeof($result), 1);
        $this->assertEqual($result[0]->post_id, 'abadadfd1212');

        //build post published one year and 4 days ago today
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>'abadadfd1213', 'author_user_id'=>'19',
        'author_username'=>'linkbaiter', 'author_fullname'=>'Link Baiter', 'is_geo_encoded'=>0,
        'post_text'=>'This is link post '.$counter, 'source'=>'web', 'pub_date'=>'-369d',
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'twitter',
        'is_protected' => 0));

        //build favorite of that post by test user ev
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'abadadfd1213', 'author_user_id'=>'19',
        'fav_of_user_id'=>'13', 'network'=>'twitter'));

        $since_date = date("Y-m-d", strtotime("-4 day"));
        //get favorites from one year ago today
        $result = $this->dao->getFavoritesFromOneYearAgo('13', 'twitter', $since_date);

        //assert post is returned
        $this->assertEqual(sizeof($result), 1);
        $this->assertEqual($result[0]->post_id, 'abadadfd1213');
    }

    public function testGetUsersWhoFavoritedMostOfYourPosts() {
        //build post published 3 days ago
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>'abadadfd1212', 'author_user_id'=>'19',
        'author_username'=>'linkbaiter', 'author_fullname'=>'Link Baiter', 'is_geo_encoded'=>0,
        'post_text'=>'This is link post '.$counter, 'source'=>'web', 'pub_date'=>'-3d',
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'twitter',
        'is_protected' => 0));

        //build post published 4 days ago
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>'abadadfd1213', 'author_user_id'=>'19',
        'author_username'=>'linkbaiter', 'author_fullname'=>'Link Baiter', 'is_geo_encoded'=>0,
        'post_text'=>'This is link post '.$counter, 'source'=>'web', 'pub_date'=>'-4d',
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'twitter',
        'is_protected' => 0));

        //build favorite of those posts by test user ev
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'abadadfd1212', 'author_user_id'=>'19',
        'fav_of_user_id'=>'13', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'abadadfd1213', 'author_user_id'=>'19',
        'fav_of_user_id'=>'13', 'network'=>'twitter'));

        //build favorite of that post by test user user1
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'abadadfd1212', 'author_user_id'=>'19',
        'fav_of_user_id'=>'20', 'network'=>'twitter'));

        //build favorite of that post by test user user2
        $builders[] = FixtureBuilder::build('favorites', array('post_id'=>'abadadfd1212', 'author_user_id'=>'19',
        'fav_of_user_id'=>'21', 'network'=>'twitter'));

        $result = $this->dao->getUsersWhoFavoritedMostOfYourPosts('19', 'twitter', 7);
        $this->debug(Utils::varDumpToString($result));
        $this->assertEqual(sizeof($result), 1);
        $this->assertEqual($result[0]->username, 'ev');
    }

    /**
     * helper method to build a post
     */
    private function buildPostArray1() {
        $vals = array();
        $vals['post_id']='2904';
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
        $vals = array();
        $vals['post_id']='10822735852740608';
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

    private function buildFavoriteArray() {
        $vals = array();
        $vals["favoriter_id"]= "1075560752";
        $vals["network"] = "facebook page";
        $vals["author_user_id"] = "340319429401281";
        $vals["post_id"]="345840895515801";
        return $vals;
    }
}
