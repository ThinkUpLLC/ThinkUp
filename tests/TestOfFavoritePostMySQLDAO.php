<?php
/**
 *
 * ThinkUp/tests/TestOfFavoritePostMySQLDAO.php
 *
 * Copyright (c) 2009-2010 Amy Unruh, Gina Trapani
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
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterPlugin.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/facebook/model/class.FacebookPlugin.php';

/**
 * Test of PostMySQL DAO implementation
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Amy Unruh, Gina Trapani
 * @author Amy Unruh
 *
 */
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

        $this->DAO = new FavoritePostMySQLDAO();
        $q = "INSERT INTO tu_owner_instances (owner_id, instance_id) VALUES (1, 1)";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_users (user_id, user_name, full_name, avatar, last_updated)
        VALUES (13, 'ev', 'Ev Williams', 'avatar.jpg', '1/1/2005');";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_users (user_id, user_name, full_name, avatar, is_protected, follower_count)
        VALUES (18, 'shutterbug', 'Shutter Bug', 'avatar.jpg', 0, 10);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_users (user_id, user_name, full_name, avatar, is_protected, follower_count)
        VALUES (19, 'linkbaiter', 'Link Baiter', 'avatar.jpg', 0, 70);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_users (user_id, user_name, full_name, avatar, is_protected, follower_count)
        VALUES (20, 'user1', 'User 1', 'avatar.jpg', 0, 90);";
        PDODAO::$PDO->exec($q);

        //protected user
        $q = "INSERT INTO tu_users (user_id, user_name, full_name, avatar, is_protected, follower_count)
        VALUES (21, 'user2', 'User 2', 'avatar.jpg', 1, 80);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_users (user_id, user_name, full_name, avatar, is_protected, follower_count)
        VALUES (22, 'quoter', 'Quotables', 'avatar.jpg', 0, 80);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_users (user_id, user_name, full_name, avatar, is_protected, follower_count)
        VALUES (23, 'user3', 'User 3', 'avatar.jpg', 0, 100);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_users (user_id, user_name, full_name, avatar, is_protected, follower_count)
        VALUES (24, 'notonpublictimeline', 'Not on Public Timeline', 'avatar.jpg', 1, 100);";
        PDODAO::$PDO->exec($q);

        //Make public
        $q = "INSERT INTO tu_instances (network_user_id, network_username, is_public) VALUES (13, 'ev', 1);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_instances (network_user_id, network_username, is_public) VALUES (18, 'shutterbug', 1);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_instances (network_user_id, network_username, is_public) VALUES (19, 'linkbaiter', 1);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_instances (network_user_id, network_username, is_public) VALUES (23, 'user3', 1);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_instances (network_user_id, network_username, is_public)
        VALUES (24, 'notonpublictimeline', 0);";
        PDODAO::$PDO->exec($q);

        //Add straight text posts
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
            $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
            post_text, source, pub_date, reply_count_cache, retweet_count_cache, network) VALUES 
            ($counter, 13, 'ev', 'Ev Williams', 'avatar.jpg', 
            'This is post $counter', '$source', '2006-01-01 00:$pseudo_minute:00', ".rand(0, 4).", 5, 'twitter');";
            PDODAO::$PDO->exec($q);
            $counter++;
        }

        //Add link posts
        $counter = 0;
        while ($counter < 40) {
            $post_id = $counter + 80;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);
            $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
            post_text, source, pub_date, reply_count_cache, retweet_count_cache, network) 
            VALUES ($post_id, 19, 'linkbaiter', 'Link Baiter', 'avatar.jpg', 
            'This is link post $counter', 'web', '2006-01-01 00:$pseudo_minute:00', 0, 0, 'twitter');";
            PDODAO::$PDO->exec($q);

            $q = "INSERT INTO tu_links (url, expanded_url, title, clicks, post_id, is_image)
            VALUES ('http://example.com/".$counter."', 'http://example.com/".$counter.".html', 
            'Link $counter', 0, $post_id, 0);";
            PDODAO::$PDO->exec($q);

            $counter++;
            
            // have 'user1' favorite some of ev's posts
            for ($i = 0; $i < 20; $i++) {
              // $rand_postid = rand(0, 40);
              $q = "INSERT IGNORE INTO tu_favorites (status_id, author_user_id, fav_of_user_id, network) VALUES (" .
                "$i, 13, 20, 'twitter');";
              PDODAO::$PDO->exec($q);
            }
            // have 'user1' favorite some linkbaiter posts
            for ($i = 80; $i < 100; $i++) {
              // $rand_postid = rand(0, 40);
              $q = "INSERT IGNORE INTO tu_favorites (status_id, author_user_id, fav_of_user_id, network) VALUES (" .
                "$i, 19, 20, 'twitter');";
              PDODAO::$PDO->exec($q);
            }
        }
    }

    public function tearDown() {
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
     * Test creation of fav post
     */
    public function testFavPostCreation() {
      
    }
    
    /**
     * Test unfavoriting of fav post
     */
    public function testFavPostUnfav() {
      
    }
    
    /**
     * Test attempted unfav of a post that is not favorited by the owner
     */
    public function testNonFavPostUnfav() {
      
    }

    /**
     * Test fetch of all favorited posts for a given user by userid
     */
    public function testGetAllFavsForUserID() {
      $dao = new FavoritePostMySQLDAO();
      $res = $dao->getAllFPosts(20, 'twitter', 6);
      $this->assertIsA($res, "array");
      $this->assertEqual(count($res), 6);
      // print_r($res);
    }
    
    /**
     * Test fetch of all favorited posts for a given user by username
     */
    public function testGetAllFavsForUsername() {
      $dao = new FavoritePostMySQLDAO();
      $res = $dao->getAllFPostsByUsername('user1', 'twitter', 6);
      $this->assertIsA($res, "array");
      $this->assertEqual(count($res), 6);
      // print_r($res);
    }
    
    /**
     * Test fetch of all favorited posts for a given user with post # less than a given upper bound.
     */
    public function testGetAllFavsForUserUB() {
      
    }
    
    /**
     * Test pagination
     */
    public function testFavoritesPagination() {
      
    }
}
