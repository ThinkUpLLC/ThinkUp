<?php
/**
 *
 * ThinkUp/webapp/plugins/foursquare/tests/TestOfFoursquareCrawler.php
 *
 * Copyright (c) 2012-2013 Aaron Kalair
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
 *
 * Test of Foursquare Crawler
 *
 * Tests the foursquare crawler
 *
 * @author Aaron Kalair <aaronkalair[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Aaron Kalair
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/foursquare/model/class.FoursquareCrawler.php';
// API requests will be handled locally
require_once THINKUP_ROOT_PATH.'webapp/plugins/foursquare/tests/classes/mock.FoursquareAPIAccessor.php';

class TestOfFoursquareCrawler extends ThinkUpUnitTestCase {
    /**
     *
     * @var Instance
     */
    var $instance;
    /**
     *
     * @var Logger
     */
    var $logger;
    /**
     * @var array
     */
    var $builders;
    // Create the data structures we need for the tests
    public function setUp() {
        // Call the parents constructor
        parent::setUp();
        // Get an instance
        $this->logger = Logger::getInstance();
        // Create an array with instance details
        $r = array('id'=>1, 'network_username'=>'aaronkalair@gmail.com', 'network_user_id'=>'113612142759476883204',
        'network_viewer_id'=>'113612142759476883204', 'last_post_id'=>'0',
        'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0',
        'total_follows_in_system'=>'0', 'is_archive_loaded_replies'=>'0',
        'is_archive_loaded_follows'=>'0', 'crawler_last_run'=>'', 'earliest_reply_in_system'=>'',
        'avg_replies_per_day'=>'0', 'is_public'=>'0', 'is_active'=>'0', 'network'=>'foursquare',
        'last_favorite_id' => '0', 'owner_favs_in_system' => '0', 'total_posts_by_owner'=>0,
        'posts_per_day'=>0, 'posts_per_week'=>0, 'percentage_replies'=>0, 'percentage_links'=>0,
        'earliest_post_in_system'=>'2009-01-01 13:48:05', 'favorites_profile' => '0',
        'url' => 'http://www.fousquare.com/user/113612142759476883204', 'is_archive_loaded_posts'=> 1
        );
        // Create an instance with these details
        $this->profile1_instance = new Instance($r);
        // Insert into storage
        $this->builders = FixtureBuilder::build('instances', $r);
    }

    // Build data needed for the tests
    private function buildData() {
        // Create a owner
        $builders = array();
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'113612142759476883204',
        'network'=>'foursquare'));
        return $builders;
    }

    // Tidy up the database and close the logger
    public function tearDown() {
        $this->logger->close();
        $this->builders = null;
        parent::tearDown();
    }

    // Test the constructor
    public function testConstructor() {
        // Create a new foursquare crawler for this instance with the access token fauxaccesstoken
        $fsc = new FoursquareCrawler($this->profile1_instance, 'fauxaccesstoken');

        // Check the access token was set correctly
        $this->assertEqual($fsc->access_token, 'fauxaccesstoken');
    }

    public function testFetchUser() {
        // Create a new foursquare crawler for this instance with the access token fauxaccesstoken
        $fsc = new FoursquareCrawler($this->profile1_instance, 'secret');
        // Fetch this users details from foursquare
        $fsc->fetchUser($this->profile1_instance->network_user_id, $this->profile1_instance->network, true);
        // Get a new user DAO
        $user_dao = new UserMySQLDAO();
        // Get the foursquare users object
        $user = $user_dao->getUserByName('aaronkalair@gmail.com', 'foursquare');

        // Check that we got something back from the database
        $this->assertTrue(isset($user));
        // Check the username is correct
        $this->assertEqual($user->username, 'aaronkalair@gmail.com');
        // Check the full name is correct
        $this->assertEqual($user->full_name, 'Bob Cats');
        // Check the user ID is correct
        $this->assertEqual($user->user_id, '113612142759476883204');
        // Check the location is correct
        $this->assertEqual($user->location, "Harefield, UK");
        // Check the URL was set correctly
        $this->assertEqual($user->url, 'http://www.foursquare.com/user/113612142759476883204');
        // Check the user isn't protected
        $this->assertFalse($user->is_protected);
    }

    public function testInitializeInstanceUser() {
        // Create a new foursquare crawler for this instance with the access token fauxaccesstoken
        $fsc = new FoursquareCrawler($this->profile1_instance, 'secret', 10);
        // Call the initaliseInstanceUser method with this users id and access token
        $fsc->initializeInstanceUser('secret', 1);
        // Get a new user DAO
        $user_dao = new UserMySQLDAO();
        // Get the foursquare users object
        $user = $user_dao->getUserByName('aaronkalair@gmail.com', 'foursquare');

        // Check that we got something back from the database
        $this->assertTrue(isset($user));
        // Check the username is correct
        $this->assertEqual($user->username, 'aaronkalair@gmail.com');
        // Check the full name is correct
        $this->assertEqual($user->full_name, 'Bob Cats');
        // Check the user ID is correct
        $this->assertEqual($user->user_id, '113612142759476883204');
        // Check the location is correct
        $this->assertEqual($user->location, "Harefield, UK");
        // Check the URL was set correctly
        $this->assertEqual($user->url, 'http://www.foursquare.com/user/113612142759476883204');
        // Check the user isn't protected
        $this->assertFalse($user->is_protected);
    }

    public function testGetOAuthTokens() {
        // Create a new foursquare crawler for this instance with a valid access token
        $fsc = new FoursquareCrawler($this->profile1_instance, 'secret', 10);

        // Test getting token
        $tokens = $fsc->getOAuthTokens('ci', 'cs',
        'http://test/account/?p=foursquare', '5dn');
        $this->assertEqual($tokens->access_token, 'secret');
    }

    public function testFetchInstanceUserCheckins() {
        $builders = self::buildData();

        // Create a new foursquare crawler for this instance with a valid access token
        $fsc = new FoursquareCrawler($this->profile1_instance, 'secret', 10);
        // Make a request for this users checkins
        $fsc->fetchInstanceUserCheckins();
        //sleep(1000);
        // Get a new post dao
        $post_dao = new PostMySQLDAO();
        // Get a new place DAO
        $place_dao = new PlaceMySQLDAO();
        // Get a new link DAO
        $link_dao = new LinkMySQLDAO();
        // Get the checkin from the database that the fetchInstanceUserCheckins method should have saved
        $post = $post_dao->getPost('4efa01068b81ef98d2e9cd0b', 'foursquare', true);
        // Get the place information from the database that fetchInstanceUserCheckins method should have saved
        $place = $place_dao->getPlaceByID('4e22eac31838712abe8186e3');
        // Check the post was actually set
        $this->assertIsA($post, 'Post');
        // Check these values were set as blank, as they can't be null but we dont use them
        $this->assertEqual($post->reply_count_cache, 1);
        $this->assertEqual($post->favlike_count_cache, 0);
        $this->assertEqual($post->retweet_count_cache, 0);
        $this->assertEqual($post->author_follower_count, 0);
        // Check the source was set
        $this->assertEqual($post->source, 'foursquare for iPhone');
        // Check the username was set
        $this->assertEqual($post->author_username, 'aaronkalair@gmail.com');
        // Check the fullname was set
        $this->assertEqual($post->author_fullname, 'Bob Cats');
        // Check the avatar was set
        $this->assertEqual($post->author_avatar, 'https://foursquare.com/img/100x100/blank_boy.png');
        // Check the author used id was set
        $this->assertEqual($post->author_user_id, '113612142759476883204');
        // Check the publication date was set
        //$this->assertEqual($post->pub_date, '2011-12-27 17:31:50');
        $this->assertEqual($post->pub_date, date( 'Y-m-d H:i:s' , '1325007110'));

        // Check the checkin was set as public
        $this->assertFalse($post->is_protected);
        // Check the place name was set
        $this->assertEqual($post->place, 'Bedworth Sloughs');
        // Check the location was set
        $this->assertEqual($post->location, 'Bedworth, CV12 0AS');
        // Check the place ID was set
        $this->assertEqual($post->place_id, '4e22eac31838712abe8186e3');
        // Check the geo coordinates were set
        $this->assertEqual($post->geo, '52.477241961421,-1.4845029364055');

        // Now check the details about the place were stored in the places table
        // Check all the fields were returned
        $this->assertEqual(sizeof($place), 12);
        // Check the place type was set
        $this->assertEqual($place['place_type'], 'Field');
        // Check the place name was set
        $this->assertEqual($place['name'], 'Bedworth Sloughs');
        // Check the fullname was set
        $this->assertEqual($place['full_name'], 'Bedworth Sloughs');
        // Check the network was set
        $this->assertEqual($place['network'], 'foursquare');
        // Build the map url to check against like this to meet code style guidelines
        $map_url = "http://maps.googleapis.com/maps/api/staticmap?size=150x150&zoom=15&maptype=roadmap&markers";
        $map_url .= "=color:blue%7C".$post->geo."&sensor=false";
        // Check the map image was set
        $this->assertEqual($place['map_image'], $map_url);
        // Check the lat long co ordinates were set
        $this->assertPattern('/POINT\(52.4772419614/', $place['longlat']);
        $this->assertPattern('/ -1.4845029364/', $place['longlat']);

        // Check the comment for this post was set

        // Get the comment from the database
        $comment = $post_dao->getPost('4f4135f9e4b028f640ef42eb', 'foursquare', true);
        // Check the post was actually set
        $this->assertIsA($comment, 'Post');
        // Check these values were set as blank, as they can't be null but we dont use them
        $this->assertEqual($comment->reply_count_cache, 0);
        $this->assertEqual($comment->favlike_count_cache, 0);
        $this->assertEqual($comment->retweet_count_cache, 0);
        $this->assertEqual($comment->author_follower_count, 0);
        $this->assertEqual($comment->source, '');
        // Check the username was set
        $this->assertEqual($comment->author_username, 'aaronkalair@gmail.com');
        // Check the fullname was set
        $this->assertEqual($comment->author_fullname, 'Bob Cats');
        // Check the avatar was set
        $this->assertEqual($comment->author_avatar, 'https://foursquare.com/img/100x100/blank_boy.png');
        // Check the author used id was set
        $this->assertEqual($comment->author_user_id, '113612142759476883204');
        // Check the publication date was set
        //$this->assertEqual($comment->pub_date, '2012-02-19 17:48:41');
        $this->assertEqual($comment->pub_date, date( 'Y-m-d H:i:s' , '1329673721'));

        // Check the checkin was set as public
        $this->assertFalse($comment->is_protected);
        // Check the place name was set
        $this->assertEqual($comment->place, 'Bedworth Sloughs');
        // Check the location was set
        $this->assertEqual($comment->location, 'Bedworth, CV12 0AS');
        // Check the place ID was set
        $this->assertEqual($comment->place_id, '4e22eac31838712abe8186e3');
        // Check the geo co ordinates were set
        //$this->assertEqual($comment->geo, '52.477241961421,-1.4845029364055');
        $this->assertPattern('/52.4772419614/', $comment->geo);
        $this->assertPattern('/-1.4845029364/', $comment->geo);

        // Check the link for this checkin was set
        // Link string
        $link_string = 'https://img-s.foursquare.com/pix/noTc5a0afTgqTuLlsi3a33tLR0iUOZaa2hLm7LsNn1Q.jpg';
        $link = $link_dao->getLinkByUrl($link_string);

        // Check the expanded url was set correctly
        // Expanded URL string
        $expanded_url = 'https://img-s.foursquare.com/pix/noTc5a0afTgqTuLlsi3a33tLR0iUOZaa2hLm7LsNn1Q.jpg';
        $this->assertEqual($link->expanded_url, $expanded_url);
        // Check the title was set
        $this->assertEqual($link->title, ' ');
        // Check the description was set
        $this->assertEqual($link->description, ' ');
        // Check the img_src was set
        // img_src string
        $img_src = 'https://img-s.foursquare.com/pix/noTc5a0afTgqTuLlsi3a33tLR0iUOZaa2hLm7LsNn1Q.jpg';
        $this->assertEqual($link->image_src, $img_src);
        // Check the caption was set
        $this->assertEqual($link->caption, ' ');
        // Check the post key is correct
        $this->assertEqual($link->post_key, 1);
    }

    public function testFetchInstanceUserCheckinsFullArchive() {
        $builders = self::buildData();

        // Tell ThinkUp we haven't loaded old posts yet
        $this->profile1_instance->is_archive_loaded_posts = false;

        // Create a new foursquare crawler for this instance with a valid access token
        $fsc = new FoursquareCrawler($this->profile1_instance, 'secret', 10);
        // Make a request for this users checkins
        $fsc->fetchInstanceUserCheckins();
        // Get a new post dao
        $post_dao = new PostMySQLDAO();
        // Get a new place DAO
        $place_dao = new PlaceMySQLDAO();
        // Get a new link DAO
        $link_dao = new LinkMySQLDAO();

        // Get the first checkin from the database that the fetchInstanceUserCheckins method should have saved
        $post = $post_dao->getPost('4efa01068b81ef98d2e9cd0b', 'foursquare', true);
        // Get the first place information from the database that fetchInstanceUserCheckins method should have saved
        $place = $place_dao->getPlaceByID('4e22eac31838712abe8186e3');
        // Check the post was actually set
        $this->assertIsA($post, 'Post');
        // Check these values were set as blank, as they can't be null but we dont use them
        $this->assertEqual($post->reply_count_cache, 1);
        $this->assertEqual($post->favlike_count_cache, 0);
        $this->assertEqual($post->retweet_count_cache, 0);
        $this->assertEqual($post->author_follower_count, 0);
        // Check the source was set
        $this->assertEqual($post->source, 'foursquare for iPhone');
        // Check the username was set
        $this->assertEqual($post->author_username, 'aaronkalair@gmail.com');
        // Check the fullname was set
        $this->assertEqual($post->author_fullname, 'Bob Cats');
        // Check the avatar was set
        $this->assertEqual($post->author_avatar, 'https://foursquare.com/img/100x100/blank_boy.png');
        // Check the author used id was set
        $this->assertEqual($post->author_user_id, '113612142759476883204');
        // Check the publication date was set
        //$this->assertEqual($post->pub_date, '2011-12-27 17:31:50');
        $this->assertEqual($post->pub_date, date( 'Y-m-d H:i:s' , '1325007110'));

        // Check the checkin was set as public
        $this->assertFalse($post->is_protected);
        // Check the place name was set
        $this->assertEqual($post->place, 'Bedworth Sloughs');
        // Check the location was set
        $this->assertEqual($post->location, 'Bedworth, CV12 0AS');
        // Check the place ID was set
        $this->assertEqual($post->place_id, '4e22eac31838712abe8186e3');
        // Check the geo co ordinates were set
        //$this->assertEqual($post->geo, '52.477241961421,-1.4845029364055');
        $this->assertPattern('/52.4772419614/', $post->geo);
        $this->assertPattern('/-1.4845029364/', $post->geo);

        // Now check the details about the place were stored in the places table
        // Check all the fields were returned
        $this->assertEqual(sizeof($place), 12);
        // Check the place type was set
        $this->assertEqual($place['place_type'], 'Field');
        // Check the place name was set
        $this->assertEqual($place['name'], 'Bedworth Sloughs');
        // Check the fullname was set
        $this->assertEqual($place['full_name'], 'Bedworth Sloughs');
        // Check the network was set
        $this->assertEqual($place['network'], 'foursquare');
        // Build the map url to check against like this to meet code style guidelines
        $map_url = "http://maps.googleapis.com/maps/api/staticmap?size=150x150&zoom=15&maptype=roadmap&markers";
        $map_url .= "=color:blue%7C".$post->geo."&sensor=false";
        // Check the map image was set
        $this->assertEqual($place['map_image'], $map_url);
        // Check the lat long co ordinates were set
        $this->assertPattern('/POINT\(52.4772419614/', $place['longlat']);
        $this->assertPattern('/ -1.4845029364/', $place['longlat']);

        // Check the comment for this post was set

        // Get the comment from the database
        $comment = $post_dao->getPost('4f4135f9e4b028f640ef42eb', 'foursquare', true);
        // Check the post was actually set
        $this->assertIsA($comment, 'Post');
        // Check these values were set as blank, as they can't be null but we dont use them
        $this->assertEqual($comment->reply_count_cache, 0);
        $this->assertEqual($comment->favlike_count_cache, 0);
        $this->assertEqual($comment->retweet_count_cache, 0);
        $this->assertEqual($comment->author_follower_count, 0);
        $this->assertEqual($comment->source, '');
        // Check the username was set
        $this->assertEqual($comment->author_username, 'aaronkalair@gmail.com');
        // Check the fullname was set
        $this->assertEqual($comment->author_fullname, 'Bob Cats');
        // Check the avatar was set
        $this->assertEqual($comment->author_avatar, 'https://foursquare.com/img/100x100/blank_boy.png');
        // Check the author used id was set
        $this->assertEqual($comment->author_user_id, '113612142759476883204');
        // Check the publication date was set
        //$this->assertEqual($comment->pub_date, '2012-02-19 17:48:41');
        $this->assertEqual($comment->pub_date, date( 'Y-m-d H:i:s' , '1329673721'));

        // Check the checkin was set as public
        $this->assertFalse($comment->is_protected);
        // Check the place name was set
        $this->assertEqual($comment->place, 'Bedworth Sloughs');
        // Check the location was set
        $this->assertEqual($comment->location, 'Bedworth, CV12 0AS');
        // Check the place ID was set
        $this->assertEqual($comment->place_id, '4e22eac31838712abe8186e3');
        // Check the geo co ordinates were set
        //$this->assertEqual($comment->geo, '52.477241961421,-1.4845029364055');
        $this->assertPattern('/52.4772419614/', $comment->geo);
        $this->assertPattern('/-1.484502936/', $comment->geo);

        // Check the link for this checkin was set
        // Link string
        $link_string = 'https://img-s.foursquare.com/pix/noTc5a0afTgqTuLlsi3a33tLR0iUOZaa2hLm7LsNn1Q.jpg';
        $link = $link_dao->getLinkByUrl($link_string);

        // Check the expanded url was set correctly
        // Expanded URL string
        $expanded_url = 'https://img-s.foursquare.com/pix/noTc5a0afTgqTuLlsi3a33tLR0iUOZaa2hLm7LsNn1Q.jpg';
        $this->assertEqual($link->expanded_url, $expanded_url);
        // Check the title was set
        $this->assertEqual($link->title, ' ');
        // Check the description was set
        $this->assertEqual($link->description, ' ');
        // Check the img_src was set
        // img_src string
        $img_src = 'https://img-s.foursquare.com/pix/noTc5a0afTgqTuLlsi3a33tLR0iUOZaa2hLm7LsNn1Q.jpg';
        $this->assertEqual($link->image_src, $img_src);
        // Check the caption was set
        $this->assertEqual($link->caption, ' ');
        // Check the post key is correct
        $this->assertEqual($link->post_key, 1);

        // Check the checkin from the second page of results was inserted correctly
        // Get the first checkin from the database that the fetchInstanceUserCheckins method should have saved
        $post = $post_dao->getPost('4efa01068b81ef98d679cd0b', 'foursquare', true);
        // Get the first place information from the database that fetchInstanceUserCheckins method should have saved
        $place = $place_dao->getPlaceByID('4e22eac31838712aui8186e3');
        // Check the post was actually set
        $this->assertIsA($post, 'Post');
        // Check these values were set as blank, as they can't be null but we dont use them
        $this->assertEqual($post->reply_count_cache, 0);
        $this->assertEqual($post->favlike_count_cache, 0);
        $this->assertEqual($post->retweet_count_cache, 0);
        $this->assertEqual($post->author_follower_count, 0);
        // Check the source was set
        $this->assertEqual($post->source, 'foursquare for iPhone');
        // Check the username was set
        $this->assertEqual($post->author_username, 'aaronkalair@gmail.com');
        // Check the fullname was set
        $this->assertEqual($post->author_fullname, 'Bob Cats');
        // Check the avatar was set
        $this->assertEqual($post->author_avatar, 'https://foursquare.com/img/100x100/blank_boy.png');
        // Check the author used id was set
        $this->assertEqual($post->author_user_id, '113612142759476883204');
        // Check the publication date was set
        //$this->assertEqual($post->pub_date, '2011-12-27 17:31:59');
        $this->assertEqual($post->pub_date, date( 'Y-m-d H:i:s' , '1325007119'));
        // Check the checkin was set as public
        $this->assertFalse($post->is_protected);
        // Check the place name was set
        $this->assertEqual($post->place, 'Empire State Building');
        // Check the location was set
        $this->assertEqual($post->location, 'New York');
        // Check the place ID was set
        $this->assertEqual($post->place_id, '4e22eac31838712aui8186e3');
        // Check the geo co ordinates were set
        //$this->assertEqual($post->geo, '82.476241961421,-6.4845029364055');
        $this->assertPattern('/82.4762419614/', $post->geo);
        $this->assertPattern('/-6.4845029364/', $post->geo);

        // Now check the details about the place were stored in the places table
        // Check all the fields were returned
        $this->assertEqual(sizeof($place), 12);
        // Check the place type was set
        $this->assertEqual($place['place_type'], 'Building');
        // Check the place name was set
        $this->assertEqual($place['name'], 'Empire State Building');
        // Check the fullname was set
        $this->assertEqual($place['full_name'], 'Empire State Building');
        // Check the network was set
        $this->assertEqual($place['network'], 'foursquare');

        // Check we set the old posts loaded bit
        $this->assertTrue($this->profile1_instance->is_archive_loaded_posts);
    }
}
