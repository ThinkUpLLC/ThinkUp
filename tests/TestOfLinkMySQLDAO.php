<?php
/**
 *
 * ThinkUp/tests/TestOfLinkMySQLDAO.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Christoffer Viken
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
 * Test Of Link DAO
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Christoffer Viken
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author christoffer Viken <christoffer[at]viken[dot]me>
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfLinkMySQLDAO extends ThinkUpUnitTestCase {

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
            $post_key = $counter + 80;
            $builders[] = FixtureBuilder::build('links', array('url'=>'http://example.com/'.$counter,
            'title'=>'Link '.$counter, 'post_key'=>$post_key, 'expanded_url'=>'', 'error'=>'', 'image_src'=>''));
            $counter++;
        }

        //Insert test links (images from Flickr, not expanded)
        $counter = 0;
        while ($counter < 5) {
            $post_key = $counter + 80;
            $builders[] = FixtureBuilder::build('links', array('url'=>'http://flic.kr/p/'.$counter,
            'title'=>'Link '.$counter, 'post_key'=>$post_key, 'expanded_url'=>'', 'error'=>'',
            'image_src'=>'http://flic.kr/thumbnail.png'));
            $counter++;
        }

        //Insert test links with errors (images from Flickr, not expanded)
        $counter = 0;
        while ($counter < 5) {
            $post_key = $counter + 80;
            $builders[] = FixtureBuilder::build('links', array('url'=>'http://flic.kr/p/'.$counter.'e',
            'title'=>'Link '.$counter, 'post_key'=>$post_key, 'error'=>'Generic test error message, Photo not found',
            'image_src'=>'http://flic.kr/thumbnail.png', 'expanded_url'=>'', 'error'=>''));
            $counter++;
        }

        //Insert several of the same shortened link
        $counter = 0;
        while ($counter < 5) {
            $post_key = $counter + 80;
            $builders[] = FixtureBuilder::build('links', array('url'=>'http://bit.ly/beEEfs',
            'title'=>'Link '.$counter, 'post_key'=>$post_key, 'error'=>'',  'expanded_url'=>'', 'error'=>'',
            'image_src'=>'http://iamathumbnail.png'));
            $counter++;
        }

        //Insert several posts, the last one protected.
        $counter = 0;
        while ($counter < 4) {
            $post_id = $counter + 80;
            $user_id = ($counter * 5) + 2;
            $builders[] = FixtureBuilder::build('posts', array('id'=>$post_id, 'post_id'=>$post_id,
            'network'=>'twitter', 'author_user_id'=>$user_id, 'author_username'=>'user'.$counter,
            'in_reply_to_post_id'=>0, 'is_protected' => 0, 'author_fullname'=>'User.'.$counter.' Name.'.$counter,
            'post_text'=>'Post by user'.$counter));
            $counter++;
        }
        $post_id = $counter + 80;
        $user_id = ($counter * 5) + 2;
        $builders[] = FixtureBuilder::build('posts', array('id'=>$post_id, 'post_id'=>$post_id,
        'author_user_id'=>$user_id, 'author_username'=>'user'.$counter, 'in_reply_to_post_id'=>0, 'is_protected' => 1,
        'network'=>'twitter', 'author_fullname'=>'User.'.$counter.' Name.'.$counter,
        'post_text'=>'Post by user'.$counter));
        $counter++;

        $builders[] = FixtureBuilder::build('follows', array('follower_id'=>2, 'user_id'=>7, 'active'=>1,
        'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('follows', array('follower_id'=>2, 'user_id'=>22, 'active'=>1,
        'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('follows', array('follower_id'=>2, 'user_id'=>17, 'active'=>1,
        'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('follows', array('follower_id'=>2, 'user_id'=>12, 'active'=>0,
        'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('follows', array('follower_id'=>27, 'user_id'=>2, 'active'=>1,
        'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('follows', array('follower_id'=>18, 'user_id'=>22, 'active'=>0,
        'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('follows', array('follower_id'=>12, 'user_id'=>22,  'active'=>1,
        'network'=>'twitter'));

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

    public function testInsert(){
        $link = new Link(array('url'=>'http://example.com/test', 'image_src'=>'',
        'expanded_url'=>'http://very.long.domain.that.nobody.would.bother.to.type.com/index.php',
        'title'=>'Very Long URL', 'post_key'=>1234));

        $result = $this->DAO->insert($link);
        //Is insert ID returned?
        $this->assertEqual($result, 56);

        //OK now check it
        $result = $this->DAO->getLinkByUrl('http://example.com/test');
        $this->assertIsA($result, "Link");
        $this->assertEqual($result->url, 'http://example.com/test');
        $this->assertEqual($result->expanded_url,
        'http://very.long.domain.that.nobody.would.bother.to.type.com/index.php');
        $this->assertEqual($result->title, 'Very Long URL');
        $this->assertEqual($result->post_key, 1234);
        $this->assertEqual($result->image_src, '');
        $this->assertEqual($result->caption, '');
        $this->assertEqual($result->description, '');

        //test another with new fields set
        $link = new Link(array('url'=>'http://example.com/test2', 'image_src'=>'',
        'expanded_url'=>'http://very.long.domain.that.nobody.would.bother.to.type.com/index.php',
        'title'=>'Very Long URL', 'post_key'=>1234567,
        'image_src'=>'http://example.com/thumbnail.png', 'description'=>'My hot link', 'caption'=>"Hot, huh?"));

        $result = $this->DAO->insert($link);
        //Is insert ID returned?
        $this->assertEqual($result, 57);

        //OK now check it
        $result = $this->DAO->getLinkByUrl('http://example.com/test2');
        $this->assertIsA($result, "Link");
        $this->assertEqual($result->url, 'http://example.com/test2');
        $this->assertEqual($result->expanded_url,
        'http://very.long.domain.that.nobody.would.bother.to.type.com/index.php');
        $this->assertEqual($result->title, 'Very Long URL');
        $this->assertEqual($result->post_key, 1234567);
        $this->assertEqual($result->image_src, 'http://example.com/thumbnail.png');
        $this->assertEqual($result->caption, 'Hot, huh?');
        $this->assertEqual($result->description, 'My hot link');

        //test another with too-lengthy content
        $long_content  = '';
        $i = 1;
        while ($i < 255) {
            $long_content .= '-'.$i;
            $i++;
        }
        $link = new Link(array('url'=>'http://example.com/test3', 'image_src'=>'',
        'expanded_url'=>'http://very.long.domain.that.nobody.would.bother.to.type.com/index.php'.$long_content,
        'title'=>'Very Long URL'.$long_content, 'post_key'=>1234567,
        'image_src'=>'http://example.com/thumbnail.png'.$long_content, 'description'=>'My hot link', 'caption'=>"Hot, huh?"));

        //SQL mode must be set to strict to get the "Data too long for column" error
        LinkMySQLDAO::$PDO->exec('SET SESSION sql_mode = "STRICT_ALL_TABLES";');

        $this->expectException('DataExceedsColumnWidthException');
        $result = $this->DAO->insert($link);
    }

    /**
     * Test Of saveExpandedUrl method
     */
    public function testSaveExpandedUrl() {
        $links_to_expand = $this->DAO->getLinksToExpand();
        $this->assertIsA($links_to_expand, 'Array');
        $this->assertTrue(sizeof($links_to_expand)>0);

        //Just expanded URL
        $link = $links_to_expand[0]->url;
        $this->DAO->saveExpandedUrl($link, "http://expandedurl.com");
        $updated_link = $this->DAO->getLinkByUrl($link);
        $this->assertEqual($updated_link->expanded_url, "http://expandedurl.com");

        //With title
        $this->DAO->saveExpandedUrl($link, "http://expandedurl1.com", 'my title');
        $updated_link = $this->DAO->getLinkByUrl($link);
        $this->assertEqual($updated_link->expanded_url, "http://expandedurl1.com");
        $this->assertEqual($updated_link->title, "my title");

        //With title and image_src
        $this->DAO->saveExpandedUrl($link, "http://expandedurl2.com", 'my title1', 'http://expandedurl2.com/thumb.png',
        'this is my description');
        $updated_link = $this->DAO->getLinkByUrl($link);
        $this->assertEqual($updated_link->expanded_url, "http://expandedurl2.com");
        $this->assertEqual($updated_link->image_src, "http://expandedurl2.com/thumb.png");
        $this->assertEqual($updated_link->title, "my title1");
        $this->assertEqual($updated_link->description, "this is my description");

        //With title, image_src, and click_count
        $this->DAO->saveExpandedUrl($link, "http://expandedurl3.com", 'my title3', '', 'yoyo');
        $updated_link = $this->DAO->getLinkByUrl($link);
        $this->assertEqual($updated_link->expanded_url, "http://expandedurl3.com");
        $this->assertEqual($updated_link->image_src, "");
        $this->assertEqual($updated_link->title, "my title3");
        $this->assertEqual($updated_link->description, "yoyo");
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

    public function testUpdateTitle(){
        $builders = FixtureBuilder::build('links', array('id'=>1234, 'url'=>'http://example.com/',
        'title'=>'Old title', 'post_key'=>10000, 'expanded_url'=>'', 'error'=>'', 'image_src'=>''));

        $result = $this->DAO->getLinkByUrl('http://example.com/');
        $this->assertEqual($result->title, 'Old title');

        $this->DAO->updateTitle(1234, 'New title');
        //OK now check it
        $result = $this->DAO->getLinkByUrl('http://example.com/');
        $this->assertEqual($result->title, 'New title');

        // Try setting title to greater than 255 chars
        $long_content  = '';
        $i = 1;
        while ($i < 255) {
            $long_content .= '-'.$i;
            $i++;
        }
        $this->DAO->updateTitle(1234, 'Title'.$long_content);

        //OK now check it
        $result = $this->DAO->getLinkByUrl('http://example.com/');
        $this->debug($result->title);
        $this->assertEqual($result->title, 'Title-1-2-3-4-5-6-7-8-9-10-11-12-13-14-15-16-17-18-19-20-21-22-23-24-25-'.
        '26-27-28-29-30-31-32-33-34-35-36-37-38-39-40-41-42-43-44-45-46-47-48-49-50-51-52-53-54-55-56-57-58-59-60-61'.
        '-62-63-64-65-66-67-68-69-70-71-72-73-74-75-76-77-78-79-80-81-82-83-84-85-86-');
    }

    public function testGetLinksByFriends(){
        $result = $this->DAO->getLinksByFriends(2, 'twitter', 15, 1, false); // not public

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 12);
        //leep(1000);
        $posts = array(
        80=>array('pid'=>80, 'uid'=>2, 'fr'=>true),
        81=>array('pid'=>81, 'uid'=>7, 'fr'=>true),
        82=>array('pid'=>82, 'uid'=>12, 'fr'=>false),
        83=>array('pid'=>83, 'uid'=>17, 'fr'=>true),
        84=>array('pid'=>84, 'uid'=>22, 'fr'=>true)
        );
        foreach($result as $key=>$val){
            $this->assertIsA($val, "Link");
            $this->assertIsA($val->container_post, "Post");
            $num = $val->post_key;
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
     * test weeding out the protected items
     */
    public function testGetLinksByFriends2(){

        $result = $this->DAO->getLinksByFriends(2, 'twitter', 15, 1, true); // public

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 8); // (1 protected post x 4) less than the previous test
        $posts = array(
        80=>array('pid'=>80, 'uid'=>2, 'fr'=>true),
        81=>array('pid'=>81, 'uid'=>7, 'fr'=>true),
        82=>array('pid'=>82, 'uid'=>12, 'fr'=>false),
        83=>array('pid'=>83, 'uid'=>17, 'fr'=>true),
        84=>array('pid'=>84, 'uid'=>22, 'fr'=>true)
        );
        foreach($result as $key=>$val){
            $this->assertIsA($val, "Link");
            $this->assertIsA($val->container_post, "Post");
            $num = $val->post_key;
            $pid = $posts[$num]['pid'];
            $uid = $posts[$num]['uid'];
            $this->assertEqual($val->container_post->post_id, $pid);
            $this->assertEqual($val->container_post->author_user_id, $uid);
            $this->assertEqual($val->container_post->post_text, 'Post by '.$val->container_post->author_username);
            $this->assertEqual($val->container_post->in_reply_to_post_id, 0);
            $this->assertTrue($posts[$num]['fr']);
        }
    }

    /**
     * Test of countLinksPostedByUserSinceDaysAgo Method
     */
    public function testCountLinksPostedByUserSinceDaysAgo() {
        $builders = array();
        $user_id = 12345;
        $counter = 0;
        while ($counter < 47) {
            $post_key = $counter + 1760;
            $post_date = date('Y-m-d H:i:s', strtotime('-'.$counter.' day'));

            $builders[] = FixtureBuilder::build('posts', array('id'=>$post_key, 'post_id'=>$post_key,
            'network'=>'twitter', 'author_user_id'=>$user_id, 'author_username'=>'user',
            'in_reply_to_post_id'=>0, 'is_protected' => 0, 'author_fullname'=>'User',
            'post_text'=>'Link post http://example.com/'.$counter, 'pub_date'=>$post_date));

            $builders[] = FixtureBuilder::build('links', array('url'=>'http://example.com/'.$counter,
            'title'=>'Link '.$counter, 'post_key'=>$post_key, 'expanded_url'=>'', 'error'=>'', 'image_src'=>''));
            $counter++;
        }

        $result = $this->DAO->countLinksPostedByUserSinceDaysAgo($user_id, 'twitter', 26);

        $this->assertEqual($result, 27);
    }

    /**
     * Test Of getPhotosByFriends Method
     */
    public function testGetPhotosByFriends(){
        $result = $this->DAO->getPhotosByFriends(2, 'twitter', 15, 1, false); // not public

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
            $this->assertIsA($val, "Link");
            $this->assertIsA($val->container_post, "Post");
            $num = $val->post_key;
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
     * Test Of getPhotosByFriends Method, weeding out the protected items
     */
    public function testGetPhotosByFriends2(){
        $result = $this->DAO->getPhotosByFriends(2, 'twitter', 15, 1, true); // public

        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 6); // (1 protected post x 3) less than the previous test
        $posts = array(
        80=>array('pid'=>80, 'uid'=>2, 'fr'=>true),
        81=>array('pid'=>81, 'uid'=>7, 'fr'=>true),
        82=>array('pid'=>82, 'uid'=>12, 'fr'=>false),
        83=>array('pid'=>83, 'uid'=>17, 'fr'=>true),
        84=>array('pid'=>84, 'uid'=>22, 'fr'=>true)
        );
        foreach($result as $key=>$val){
            $this->assertIsA($val, "Link");
            $this->assertIsA($val->container_post, "Post");
            $num = $val->post_key;
            $pid = $posts[$num]['pid'];
            $uid = $posts[$num]['uid'];
            $this->assertEqual($val->container_post->post_id, $pid);
            $this->assertEqual($val->container_post->author_user_id, $uid);
            $this->assertEqual($val->container_post->post_text, 'Post by '.$val->container_post->author_username);
            $this->assertEqual($val->container_post->in_reply_to_post_id, 0);
            $this->assertTrue($posts[$num]['fr']);
        }
    }

    /**
     * Test Of getLinksToExpand Method
     */
    public function testGetLinksToExpand() {
        $links_to_expand = $this->DAO->getLinksToExpand();
        $this->assertEqual(count($links_to_expand), 51);
        $this->assertIsA($links_to_expand, "array");
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
        $flickr_links_to_expand = $this->DAO->getLinksToExpandByUrl('http://flic.kr/');

        $this->assertEqual(count($flickr_links_to_expand), 10);
        $this->assertIsA($flickr_links_to_expand, "array");

        $flickr_links_to_expand = $this->DAO->getLinksToExpandByUrl('http://flic.kr/', 5);

        $this->assertEqual(count($flickr_links_to_expand), 5);
        $this->assertIsA($flickr_links_to_expand, "array");
    }

    /**
     * test adding a dup, with the IGNORE modifier, check the result.
     * Set counter higher to avoid clashes w/ prev inserts.
     */
    public function testUniqueConstraint1() {
        $config = Config::getInstance();
        $config_array = $config->getValuesArray();
        $counter = 2000;
        $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
        $source = '<a href="http://twitter.com" rel="nofollow">Tweetie for Mac</a>';
        $q  = "INSERT IGNORE INTO " . $config_array['table_prefix'] .
        "links (url, title, post_key, image_src) ";
        $q .= " VALUES ('http://example.com/".$counter."', 'Link $counter', $counter, '');";
        $res = PDODAO::$PDO->exec($q);
        $this->assertEqual($res, 1);

        $q  = "INSERT IGNORE INTO " . $config_array['table_prefix'] .
        "links (url, title, post_key, image_src) ";
        $q .= " VALUES ('http://example.com/".$counter."', 'Link $counter', $counter, '');";
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
            'title'=>'Link '.$counter, 'post_key'=>$counter, 'expanded_url'=>'', 'error'=>'', 'image_src'=>''));
            $builder2 = FixtureBuilder::build('links', array('url'=>'http://example.com/'.$counter,
            'title'=>'Link '.$counter, 'post_key'=>$counter, 'expanded_url'=>'', 'error'=>'', 'image_src'=>''));
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
            $post_key = $counter + 180;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);
            $lbuilders[] = FixtureBuilder::build('links', array('url'=>'http://example2.com/'.$counter,
            'title'=>'Link '.$counter, 'post_key'=>$post_key, 'expanded_url'=>'', 'error'=>'', 'image_src'=>''));
            $counter++;
        }
        //Insert several posts for fav checking-- links will be associated with 5 of them
        $counter = 0;
        while ($counter < 10) {
            $post_id = $counter + 180;
            $user_id = ($counter * 5) + 2;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);

            $lbuilders[] = FixtureBuilder::build('posts', array('id'=>$post_id,'post_id'=>$post_id,
            'author_user_id'=>$user_id, 'author_username'=>"user$counter",
            'author_fullname'=>"User$counter Name$counter", 'author_avatar'=>'avatar.jpg',
            'post_text'=>'This is post '.$post_id, 'pub_date'=>'2009-01-01 00:'. $pseudo_minute.':00',
            'network'=>'twitter', 'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));

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

    /**
     * Test of getLinksByFavorites method, weeding out the protected items
     */
    public function testGetFavoritedLinks2() {
        $lbuilders = array();
        // test links for fav checking
        $counter = 0;
        while ($counter < 5) {
            $post_key = $counter + 180;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);
            $lbuilders[] = FixtureBuilder::build('links', array('url'=>'http://example2.com/'.$counter,
            'title'=>'Link '.$counter, 'post_key'=>$post_key, 'expanded_url'=>'', 'error'=>''));
            $counter++;
        }
        //Insert several posts for fav checking-- links will be associated with 5 of them
        $counter = 0;
        while ($counter < 10) {
            $post_id = $counter + 180;
            $user_id = ($counter * 5) + 2;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);
            $is_protected = $counter == 0 ? 1 : 0;

            $lbuilders[] = FixtureBuilder::build('posts', array('id'=>$post_id, 'post_id'=>$post_id,
            'author_user_id'=>$user_id, 'author_username'=>"user$counter",
            'author_fullname'=>"User$counter Name$counter", 'author_avatar'=>'avatar.jpg',
            'post_text'=>'This is post '.$post_id, 'pub_date'=>'2009-01-01 00:'.
            $pseudo_minute.':00', 'network'=>'twitter', 'is_protected' => $is_protected,
            'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));

            // user '20' favorites the first 7 of the test posts, only 5 of which will have links
            if ($counter < 7) {
                $lbuilders[] = FixtureBuilder::build('favorites', array('post_id'=>$post_id,
                'author_user_id'=>$user_id, 'fav_of_user_id'=>20, 'network'=>'twitter'));
            }
            $counter++;
        }
        $result = $this->DAO->getLinksByFavorites(20, 'twitter', 15, 1, true);
        $this->assertIsA($result, "array");
        $this->assertEqual(count($result), 4);
        $lbuilders = null;
    }

    public function testGetFavoritedLinksPaging() {
        $lbuilders = array();
        $counter = 0;
        while ($counter < 15) {
            $post_key = $counter + 280;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);
            $lbuilders[] = FixtureBuilder::build('links', array('url'=>'http://example2.com/'.$counter,
            'title'=>'Link '.$counter, 'post_key'=>$post_key, 'expanded_url'=>'', 'error'=>'', 'image_src'=>''));
            $counter++;
        }
        //create posts-- links will be associated with the first 15 of them
        $counter = 0;
        while ($counter < 30) {
            $post_id = $counter + 280;
            $user_id = ($counter * 5) + 2;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);

            $lbuilders[] = FixtureBuilder::build('posts', array('id'=>$post_id, 'post_id'=>$post_id,
            'author_user_id'=>$user_id, 'author_username'=>"user$counter",
            'author_fullname'=>"User$counter Name$counter", 'author_avatar'=>'avatar.jpg', 'network'=>'twitter',
            'post_text'=>'This is post '.$post_id, 'pub_date'=>'2009-01-01 00:'. $pseudo_minute.':00',
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

    public function testGetLinksForPost() {
        $result = $this->DAO->getLinksForPost(80, 'twitter');
        $this->debug(Utils::varDumpToString($result));
        $this->assertEqual(4, sizeof($result)); //should be 4 links for this post
        $this->assertEqual($result[0]->url, "http://example.com/0");
        $this->assertEqual($result[1]->url, "http://flic.kr/p/0");
        $this->assertEqual($result[2]->url, "http://flic.kr/p/0e");
        $this->assertEqual($result[3]->url, "http://bit.ly/beEEfs");

        $result = $this->DAO->getLinksForPost(800, 'twitter');
        $this->assertEqual(0, sizeof($result)); //should be no links for this post
    }

    public function testDeleteLinksByHashtagId() {
        $result = $this->DAO->getLinksForPost(1000, 'twitter');
        $this->assertEqual(0, sizeof($result));
        $result = $this->DAO->getLinksForPost(1001, 'twitter');
        $this->assertEqual(0, sizeof($result));
        $result = $this->DAO->getLinksForPost(1002, 'twitter');
        $this->assertEqual(0, sizeof($result));
        $result = $this->DAO->getLinksForPost(1003, 'twitter');
        $this->assertEqual(0, sizeof($result));

        $builder = $this->buildSearchData();

        $result = $this->DAO->getLinksForPost(1000, 'twitter');
        $this->assertEqual(2, sizeof($result));
        $result = $this->DAO->getLinksForPost(1001, 'twitter');
        $this->assertEqual(1, sizeof($result));
        $result = $this->DAO->getLinksForPost(1002, 'twitter');
        $this->assertEqual(1, sizeof($result));
        $result = $this->DAO->getLinksForPost(1003, 'twitter');
        $this->assertEqual(0, sizeof($result));

        $result = $this->DAO->deleteLinksByHashtagId(1);

        $result = $this->DAO->getLinksForPost(1000, 'twitter');
        $this->assertEqual(0, sizeof($result));
        $result = $this->DAO->getLinksForPost(1001, 'twitter');
        $this->assertEqual(1, sizeof($result));
        $result = $this->DAO->getLinksForPost(1002, 'twitter');
        $this->assertEqual(0, sizeof($result));
        $result = $this->DAO->getLinksForPost(1003, 'twitter');
        $this->assertEqual(0, sizeof($result));
    }

    private function buildSearchData() {
        $builders = array();

        $builders[] = FixtureBuilder::build('hashtags',
        array('id' => 1, 'hashtag' => '#Messi', 'network' => 'twitter', 'count_cache' => 0));

        $builders[] = FixtureBuilder::build('hashtags_posts',
        array('post_id' => 1000, 'hashtag_id' => 1, 'network' => 'twitter'));
        $builders[] = FixtureBuilder::build('hashtags_posts',
        array('post_id' => 1002, 'hashtag_id' => 1, 'network' => 'twitter'));

        $builders[] = FixtureBuilder::build('posts', array(
            'id' => 1000,
            'post_id' => '1000',
            'author_user_id' => '100',
            'author_username' => 'ecucurella',
            'author_fullname' => 'Eduard Cucurella',
            'author_avatar' => 'http://aa.com',
            'author_follower_count' => 0,
            'post_text' => '#Messi is the best http://flic.kr/p/ http://flic.kr/a/',
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

        $builders[] = FixtureBuilder::build('posts', array(
            'id' => 1001,
            'post_id' => '1001',
            'author_user_id' => '101',
            'author_username' => 'vetcastellnou',
            'author_fullname' => 'Veterans Castellnou',
            'author_avatar' => 'http://aa.com',
            'author_follower_count' => 0,
            'post_text' => 'Post without any hashtag http://flic.kr/p/',
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

        $builders[] = FixtureBuilder::build('posts', array(
            'id' => 1002,
            'post_id' => '1002',
            'author_user_id' => '102',
            'author_username' => 'efectivament',
            'author_fullname' => 'efectivament',
            'author_avatar' => 'http://aa.com',
            'author_follower_count' => 0,
            'post_text' => 'Post with #Messi hashtag http://flic.kr/p/',
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

        $builders[] = FixtureBuilder::build('posts', array(
            'id' => 1003,
            'post_id' => '1003',
            'author_user_id' => '102',
            'author_username' => 'efectivament',
            'author_fullname' => 'efectivament',
            'author_avatar' => 'http://aa.com',
            'author_follower_count' => 0,
            'post_text' => 'Post without any hashtag 2',
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

        $builders[] = FixtureBuilder::build('links', array(
            'id' => 2000,
            'url'=>'http://flic.kr/p/',
            'title'=>'Link ',
            'post_key'=>1000,
            'expanded_url'=>'',
            'error'=>'',
            'image_src'=>'http://flic.kr/thumbnail.png'));

        $builders[] = FixtureBuilder::build('links', array(
            'id' => 2001,
            'url'=>'http://flic.kr/a/',
            'title'=>'Link ',
            'post_key'=>1000,
            'expanded_url'=>'',
            'error'=>'',
            'image_src'=>'http://flic.kr/thumbnail.png'));

        $builders[] = FixtureBuilder::build('links', array(
            'id' => 2002,
            'url'=>'http://flic.kr/p/',
            'title'=>'Link ',
            'post_key'=>1001,
            'expanded_url'=>'',
            'error'=>'',
            'image_src'=>'http://flic.kr/thumbnail.png'));

        $builders[] = FixtureBuilder::build('links', array(
            'id' => 2003,
            'url'=>'http://flic.kr/p/',
            'title'=>'Link ',
            'post_key'=>1002,
            'expanded_url'=>'',
            'error'=>'',
            'image_src'=>'http://flic.kr/thumbnail.png'));

        $builders[] = FixtureBuilder::build('users', array(
            'user_id'=>100,
            'user_name'=>'ecucurella',
            'full_name'=>'Eduard Cucurella'));

        $builders[] = FixtureBuilder::build('users', array(
            'user_id'=>101,
            'user_name'=>'vetcastellnou',
            'full_name'=>'Veterans Castellnou'));

        $builders[] = FixtureBuilder::build('users', array(
            'user_id'=>102,
            'user_name'=>'efectivament',
            'full_name'=>'efectivament'));

        return $builders;
    }

    /**
     * Test of getLinksByUserSinceDaysAgo Method
     */
    public function testGetLinksByUserSinceDaysAgo() {
        $builders = array();
        $user_id = 12345;
        $counter = 120;
        $days = 0;
        while ($counter != 0) {
            $post_key = $counter + 1760;
            $today = date('Y-m-d H:i',strtotime("-$days minutes"));
            $days++;

            $builders[] = FixtureBuilder::build('posts', array('id'=>$post_key, 'post_id'=>$post_key,
            'network'=>'twitter', 'author_user_id'=>$user_id, 'author_username'=>'user','in_reply_to_user_id' => NULL,
            'in_retweet_of_post_id' => NULL,
            'in_reply_to_post_id'=>0, 'is_protected' => 0, 'author_fullname'=>'User',
            'post_text'=>'Link post http://example.com/'.$counter, 'pub_date'=>$today));

            $builders[] = FixtureBuilder::build('links', array('url'=>'http://example.com/'.$counter,
            'title'=>'Link '.$counter, 'post_key'=>$post_key, 'expanded_url'=>'', 'error'=>'', 'image_src'=>''));
            $counter--;
        }
        $post_date = date('Y-m-d H:i:s', strtotime('-8 days'));
        $builders[] = FixtureBuilder::build('posts', array('id'=>767, 'post_id'=>767,
            'network'=>'twitter', 'author_user_id'=>$user_id, 'author_username'=>'user','in_reply_to_user_id' => NULL,
            'in_retweet_of_post_id' => NULL,
            'in_reply_to_post_id'=>0, 'is_protected' => 0, 'author_fullname'=>'User',
            'post_text'=>'Link post http://example.com/'.$counter, 'pub_date'=>$post_date));

        $builders[] = FixtureBuilder::build('links', array('url'=>'http://example.com/'.$counter,
        'title'=>'Link '.$counter, 'post_key'=>767, 'expanded_url'=>'', 'error'=>'', 'image_src'=>''));


        $result = $this->DAO->getLinksByUserSinceDaysAgo($user_id, 'twitter', $limit= 100, $days_ago = 0);
        $this->assertEqual(count($result), 100);
        $this->assertEqual($result[0]['id'], 56);
        $this->assertEqual($result[99]['id'], 155);
        $result = $this->DAO->getLinksByUserSinceDaysAgo($user_id, 'twitter', $limit= 0, $days_ago = 9);
        $this->assertEqual($result[0]['id'], 56);
        $this->assertEqual($result[120]['id'], 176);
        $this->assertEqual(count($result), 121);

        $builders[] = FixtureBuilder::build('posts', array('id'=>768, 'post_id'=>768,
            'network'=>'twitter', 'author_user_id'=>$user_id, 'author_username'=>'user','in_reply_to_user_id' => NULL,
            'in_retweet_of_post_id' => 1234,
            'in_reply_to_post_id'=>0, 'is_protected' => 0, 'author_fullname'=>'User',
            'post_text'=>'Link post http://example.com/'.$counter, 'pub_date'=>$post_date));

        $builders[] = FixtureBuilder::build('links', array('url'=>'http://example.com/'.$counter,
        'title'=>'Link '.$counter, 'post_key'=>768, 'expanded_url'=>'', 'error'=>'', 'image_src'=>''));

        $result = $this->DAO->getLinksByUserSinceDaysAgo($user_id, 'twitter', $limit= 0, $days_ago = 0);
        $this->assertEqual($result[0]['id'], 56);
        $this->assertEqual($result[0]['in_retweet_of_post_id'], null);
        $this->assertEqual($result[121]['post_key'], 768);
        $this->assertEqual($result[121]['in_retweet_of_post_id'], 1234);
        $this->assertEqual(count($result), 122);


    }
}
