<?php
/**
 *
 * ThinkUp/tests/TestOfLinkMySQLDAO.php
 *
 * Copyright (c) 2009-2011 Gina Trapani, Christoffer Viken
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
 *
 *
 * Test Of Link DAO
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Gina Trapani, Christoffer Viken
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author christoffer Viken <christoffer[at]viken[dot]me>
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfLinkMySQLDAO extends ThinkUpUnitTestCase {
    /**
     * Constructor
     */
    public function __construct() {
        $this->UnitTestCase('LinkMySQLDAO class test');
    }

    /**
     * Constructs a database and populates it.
     */
    public function setUp() {
        parent::setUp();
        $this->DAO = new LinkMySQLDAO();
        $this->builders = self::buildData();
    }

    protected function buildData() {
        $builders = array();
        //Insert test links (not images, not expanded)
        $counter = 0;
        while ($counter < 40) {
            $post_id = $counter + 80;
            $builders[] = FixtureBuilder::build('links', array('url'=>'http://example.com/'.$counter,
            'title'=>'Link '.$counter, 'clicks'=>0, 'post_id'=>$post_id, 'network'=>'twitter', 'is_image'=>0, 
            'expanded_url'=>'', 'error'=>''));
            $counter++;
        }

        //Insert test links (images from Flickr, not expanded)
        $counter = 0;
        while ($counter < 5) {
            $post_id = $counter + 80;
            $builders[] = FixtureBuilder::build('links', array('url'=>'http://flic.kr/p/'.$counter,
            'title'=>'Link '.$counter, 'clicks'=>0, 'post_id'=>$post_id, 'network'=>'twitter', 'is_image'=>1, 
            'expanded_url'=>'', 'error'=>''));
            $counter++;
        }

        //Insert test links with errors (images from Flickr, not expanded)
        $counter = 0;
        while ($counter < 5) {
            $post_id = $counter + 80;
            $builders[] = FixtureBuilder::build('links', array('url'=>'http://flic.kr/p/'.$counter.'e',
            'title'=>'Link '.$counter, 'clicks'=>0, 'post_id'=>$post_id, 'network'=>'twitter', 
            'is_image'=>1, 'error'=>'Generic test error message, Photo not found', 
            'expanded_url'=>'', 'error'=>''));
            $counter++;
        }

        //Insert several of the same shortened link
        $counter = 0;
        while ($counter < 5) {
            $post_id = $counter + 80;
            $builders[] = FixtureBuilder::build('links', array('url'=>'http://bit.ly/beEEfs',
            'title'=>'Link '.$counter, 'clicks'=>0, 'post_id'=>$post_id, 'network'=>'twitter',  'is_image'=>1, 
            'error'=>'',  'expanded_url'=>'', 'error'=>''));
            $counter++;
        }

        //Insert several posts
        $counter = 0;
        while ($counter < 5) {
            $post_id = $counter + 80;
            $user_id = ($counter * 5) + 2;
            $builders[] = FixtureBuilder::build('posts', array('post_id'=>$post_id,
            'author_user_id'=>$user_id, 'author_username'=>'user'.$counter, 'in_reply_to_post_id'=>0,
            'author_fullname'=>'User.'.$counter.' Name.'.$counter, 'post_text'=>'Post by user'.$counter));
            $counter++;
        }

        $builders[] = FixtureBuilder::build('follows', array('follower_id'=>2, 'user_id'=>7, 'active'=>1));
        $builders[] = FixtureBuilder::build('follows', array('follower_id'=>2, 'user_id'=>22, 'active'=>1));
        $builders[] = FixtureBuilder::build('follows', array('follower_id'=>2, 'user_id'=>17, 'active'=>1));
        $builders[] = FixtureBuilder::build('follows', array('follower_id'=>2, 'user_id'=>12, 'active'=>0));
        $builders[] = FixtureBuilder::build('follows', array('follower_id'=>27, 'user_id'=>2, 'active'=>1));
        $builders[] = FixtureBuilder::build('follows', array('follower_id'=>18, 'user_id'=>22, 'active'=>0));
        $builders[] = FixtureBuilder::build('follows', array('follower_id'=>12, 'user_id'=>22,  'active'=>1));

        return $builders;
    }

    /**
     * Destructs the database, so it can be reconstructed for next test
     */
    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
        $this->DAO = null;
    }

    /**
     * Test Of Insert Method
     */
    public function testInsert(){
        $result = $this->DAO->insert(
            'http://example.com/test',
            'http://very.long.domain.that.nobody.would.bother.to.type.com/index.php', 'Very Long URL', '12345678901',
            'twitter', false);
        //Is insert ID returned?
        $this->assertEqual($result, 56);

        //OK now check it
        $result = $this->DAO->getLinkByUrl('http://example.com/test');
        $this->assertIsA($result, "Link");
        $this->assertEqual($result->url, 'http://example.com/test');
        $this->assertEqual($result->expanded_url,
        'http://very.long.domain.that.nobody.would.bother.to.type.com/index.php');
        $this->assertEqual($result->title, 'Very Long URL');
        $this->assertEqual($result->post_id, 12345678901);
        $this->assertEqual($result->network, 'twitter');
        $this->assertFalse($result->is_image);
    }

    /**
     * Test Of saveExpandedUrl method
     */
    public function testSaveExpandedUrl() {
        $linkstoexpand = $this->DAO->getLinksToExpand();
        $this->assertIsA($linkstoexpand, 'Array');
        $this->assertTrue(sizeof($linkstoexpand)>0);

        $link = $linkstoexpand[0];
        $this->DAO->saveExpandedUrl($link, "http://expandedurl.com");

        $updatedlink = $this->DAO->getLinkByUrl($link);
        $this->assertEqual($updatedlink->expanded_url, "http://expandedurl.com");

        $this->DAO->saveExpandedUrl($link, "http://expandedurl1.com", 'my title');
        $updatedlink = $this->DAO->getLinkByUrl($link);
        $this->assertEqual($updatedlink->expanded_url, "http://expandedurl1.com");
        $this->assertEqual($updatedlink->title, "my title");

        $this->DAO->saveExpandedUrl($link, "http://expandedurl2.com", 'my title1', 1);
        $updatedlink = $this->DAO->getLinkByUrl($link);
        $this->assertEqual($updatedlink->expanded_url, "http://expandedurl2.com");
        $this->assertEqual($updatedlink->title, "my title1");
        $this->assertTrue($updatedlink->is_image);
    }

    /**
     * Test Of saveExpansionError Method
     */
    public function testSaveExpansionError() {
        $linktogeterror = $this->DAO->getLinkById(10);

        $this->assertEqual($linktogeterror->error, '');
        $this->DAO->saveExpansionError($linktogeterror->url, "This is expansion error text");

        $linkthathaserror = $this->DAO->getLinkById(10);
        $this->assertEqual($linkthathaserror->error, "This is expansion error text");
    }

    /**
     * Test Of update Method
     */
    public function testUpdate(){
        $result = $this->DAO->insert(
            'http://example.com/test',
            'http://very.long.domain.that.nobody.would.bother.to.type.com/index.php',
            'Very Long URL',
        15000, 'twitter'
        );
        $this->assertEqual($result, 56);

        $result = $this->DAO->update(
            'http://example.com/test', 
            'http://very.long.domain.that.nobody.would.bother.to.type.com/image.png', 
            'Even Longer URL', 
        15001, 'twitter',
        true
        );
        $this->assertEqual($result, 1);

        //OK now check it
        $result = $this->DAO->getLinkByUrl('http://example.com/test');
        $this->assertIsA($result, "Link");
        $this->assertEqual($result->url, 'http://example.com/test');
        $this->assertEqual($result->expanded_url,
        'http://very.long.domain.that.nobody.would.bother.to.type.com/image.png');
        $this->assertEqual($result->title, 'Even Longer URL');
        $this->assertEqual($result->post_id, 15001);
        $this->assertEqual($result->id, 56);
    }

    /**
     * Test Of getLinksByFriends Method
     */
    public function testGetLinksByFriends(){
        $result = $this->DAO->getLinksByFriends(2, 'twitter');

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 12);
        $posts = array(
        80=>array('pid'=>80, 'uid'=>2, 'fr'=>true),
        81=>array('pid'=>81, 'uid'=>7, 'fr'=>true),
        82=>array('pid'=>82, 'uid'=>12, 'fr'=>false),
        83=>array('pid'=>83, 'uid'=>17, 'fr'=>true),
        84=>array('pid'=>84, 'uid'=>22, 'fr'=>true)
        );
        foreach($result as $key=>$val){
            $this->assertIsA($val, "link");
            $this->assertIsA($val->container_post, "Post");
            $num = $val->post_id;
            $pid = $posts[$num]['pid'];
            $uid = $posts[$num]['uid'];
            $this->assertEqual($val->container_post->post_id, $pid);
            $this->assertEqual($val->container_post->author_user_id, $uid);
            $this->assertEqual($val->container_post->post_text, 'Post by '.$val->container_post->author_username);
            $this->assertEqual($val->container_post->in_reply_to_post_id, 0);
            $this->assertTrue($posts[$num]['fr']);
        }
        // check pagination
        $result = $this->DAO->getLinksByFriends(2, 'twitter', 5, 2);
        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 5);
    }


    /**
     * Test Of getPhotosByFriends Method
     */
    public function testGetPhotosByFriends(){
        $result = $this->DAO->getPhotosByFriends(2, 'twitter');

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 9);
        $posts = array(
        80=>array('pid'=>80, 'uid'=>2, 'fr'=>true),
        81=>array('pid'=>81, 'uid'=>7, 'fr'=>true),
        82=>array('pid'=>82, 'uid'=>12, 'fr'=>false),
        83=>array('pid'=>83, 'uid'=>17, 'fr'=>true),
        84=>array('pid'=>84, 'uid'=>22, 'fr'=>true)
        );
        foreach($result as $key=>$val){
            $this->assertIsA($val, "link");
            $this->assertIsA($val->container_post, "Post");
            $this->assertTrue($val->is_image);
            $num = $val->post_id;
            $pid = $posts[$num]['pid'];
            $uid = $posts[$num]['uid'];
            $this->assertEqual($val->container_post->post_id, $pid);
            $this->assertEqual($val->container_post->author_user_id, $uid);
            $this->assertEqual($val->container_post->post_text, 'Post by '.$val->container_post->author_username);
            $this->assertEqual($val->container_post->in_reply_to_post_id, 0);
            $this->assertTrue($posts[$num]['fr']);
        }
        // check pagination
        $result = $this->DAO->getPhotosByFriends(2, 'twitter', 5, 2);
        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 4);
    }

    /**
     * Test Of getLinksToExpand Method
     */
    public function testGetLinksToExpand() {
        $linkstoexpand = $this->DAO->getLinksToExpand();
        $this->assertEqual(count($linkstoexpand), 51);
        $this->assertIsA($linkstoexpand, "array");
    }

    /**
     * Test Of getLinkByID
     */
    public function testGetLinkById() {
        $link = $this->DAO->getLinkById(1);

        $this->assertEqual($link->id, 1);
        $this->assertEqual($link->url, 'http://example.com/0');
    }

    /**
     * Test Of getLinksToExpandByURL Method
     */
    public function testGetLinksToExpandByURL() {
        $flickrlinkstoexpand = $this->DAO->getLinksToExpandByUrl('http://flic.kr/');

        $this->assertEqual(count($flickrlinkstoexpand), 10);
        $this->assertIsA($flickrlinkstoexpand, "array");
    }

    /**
     * test adding a dup, with the IGNORE modifier, check the result.
     * Set counter higher to avoid clashes w/ prev inserts.
     */
    public function testUniqueConstraint1() {
        $counter = 2000;
        $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
        $source = '<a href="http://twitter.com" rel="nofollow">Tweetie for Mac</a>';
        $q  = "INSERT IGNORE INTO tu_links (url, title, clicks, post_id, network, is_image) ";
        $q .= " VALUES ('http://example.com/".$counter."', 'Link $counter', 0, $counter, 'twitter', 0);";
        $res = PDODAO::$PDO->exec($q);
        $this->assertEqual($res, 1);

        $q  = "INSERT IGNORE INTO tu_links (url, title, clicks, post_id, network, is_image) ";
        $q .= " VALUES ('http://example.com/".$counter."', 'Link $counter', 0, $counter, 'twitter', 0);";
        $res = PDODAO::$PDO->exec($q);
        $this->assertEqual($res, 0);
    }

    /**
     * test adding a dup w/out the IGNORE modifier; should throw exception on second insert
     */
    public function testUniqueConstraint2() {
        $counter = 2002;
        $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
        $source = '<a href="http://twitter.com" rel="nofollow">Tweetie for Mac</a>';
        $builder1 = $builder2 = null;
        try {
            $builder1 = FixtureBuilder::build('links', array('url'=>'http://example.com/'.$counter,
            'title'=>'Link '.$counter, 'clicks'=>0, 'post_id'=>$counter, 'network'=>'twitter', 'is_image'=>0, 
                'expanded_url'=>'', 'error'=>''));
            $builder2 = FixtureBuilder::build('links', array('url'=>'http://example.com/'.$counter,
            'title'=>'Link '.$counter, 'clicks'=>0, 'post_id'=>$counter, 'network'=>'twitter', 'is_image'=>0, 
                'expanded_url'=>'', 'error'=>''));
        } catch(PDOException $e) {
            $this->assertPattern('/Integrity constraint violation/', $e->getMessage());
        }
        $builder1 = null; $builder2 = null;
    }

    /**
     * Test of getLinksByFavorites method
     */
    public function testGetFavoritedLinks() {
        $lbuilders = array();
        // test links for fav checking
        $counter = 0;
        while ($counter < 5) {
            $post_id = $counter + 180;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);
            $lbuilders[] = FixtureBuilder::build('links', array('url'=>'http://example2.com/'.$counter,
            'title'=>'Link '.$counter, 'clicks'=>0, 'post_id'=>$post_id, 'network'=>'twitter', 'is_image'=>0, 
            'expanded_url'=>'', 'error'=>''));
            $counter++;
        }
        //Insert several posts for fav checking-- links will be associated with 5 of them
        $counter = 0;
        while ($counter < 10) {
            $post_id = $counter + 180;
            $user_id = ($counter * 5) + 2;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);

            $lbuilders[] = FixtureBuilder::build('posts', array('post_id'=>$post_id, 'author_user_id'=>$user_id,
            'author_username'=>"user$counter", 'author_fullname'=>"User$counter Name$counter", 
            'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post '.$post_id, 'pub_date'=>'2009-01-01 00:'.
            $pseudo_minute.':00', 'network'=>'twitter',
            'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));

            // user '20' favorites the first 7 of the test posts, only 5 of which will have links
            if ($counter < 7) {
                $lbuilders[] = FixtureBuilder::build('favorites', array('post_id'=>$post_id,
                'author_user_id'=>$user_id, 'fav_of_user_id'=>20, 'network'=>'twitter'));
            }
            $counter++;
        }
        $result = $this->DAO->getLinksByFavorites(20, 'twitter');
        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 5);
        $lbuilders = null;
    }

    public function testGetFavoritedLinksPaging() {
        $lbuilders = array();
        $counter = 0;
        while ($counter < 15) {
            $post_id = $counter + 280;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);
            $lbuilders[] = FixtureBuilder::build('links', array('url'=>'http://example2.com/'.$counter,
            'title'=>'Link '.$counter, 'clicks'=>0, 'post_id'=>$post_id, 'network'=>'twitter', 'is_image'=>0, 
            'expanded_url'=>'', 'error'=>''));
            $counter++;
        }
        //create posts-- links will be associated with the first 15 of them
        $counter = 0;
        while ($counter < 30) {
            $post_id = $counter + 280;
            $user_id = ($counter * 5) + 2;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);

            $lbuilders[] = FixtureBuilder::build('posts', array('post_id'=>$post_id, 'author_user_id'=>$user_id,
            'author_username'=>"user$counter", 'author_fullname'=>"User$counter Name$counter", 
            'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post '.$post_id, 'pub_date'=>'2009-01-01 00:'.
            $pseudo_minute.':00', 'network'=>'twitter',
            'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));

            // user '20' favorites the first 20 of the test posts, only 15 of which will have links
            if ($counter < 20) {
                $lbuilders[] = FixtureBuilder::build('favorites', array('post_id'=>$post_id,
                'author_user_id'=>$user_id, 'fav_of_user_id'=>20, 'network'=>'twitter'));
            }
            $counter++;
        }
        // 1st page, default count is 15
        $result = $this->DAO->getLinksByFavorites(20, 'twitter');
        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 15);
        // 2nd page, ask for count of 10. So, there should be 5 favs returned.
        $result = $this->DAO->getLinksByFavorites(20, 'twitter', 10, 2);
        $this->assertEqual(count($result), 5);

        $lbuilders = null;
    }
}
