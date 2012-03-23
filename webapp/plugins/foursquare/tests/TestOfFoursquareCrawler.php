<?php
/**
 *
 * webapp/plugins/foursquare/tests/TestOfFoursquareCrawler.php
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
 * Test of Foursquare Crawler 
 *
 * Tests the foursquare crawler
 *
 * Copyright (c) 2011 Aaron Kalair
 *
 * @author Aaron Kalair <aaronkalair[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011 Aaron Kalair
 */
  
require_once 'tests/init.tests.php';
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
    
    // Create the data structures we need for the tests
    public function setUp() {
        // Call the parents constructor
        parent::setUp();
        // Get an instance
        $this->logger = Logger::getInstance();
        // Create an array with instance details
        $r = array('id'=>1, 'network_username'=>'aaronkalair@gmail.com', 'network_user_id'=>'113612142759476883204',
        'network_viewer_id'=>'113612142759476883204', 'last_post_id'=>'0', 'last_page_fetched_replies'=>0,
        'last_page_fetched_tweets'=>'0', 'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0',
        'total_follows_in_system'=>'0', 'is_archive_loaded_replies'=>'0',
        'is_archive_loaded_follows'=>'0', 'crawler_last_run'=>'', 'earliest_reply_in_system'=>'',
        'avg_replies_per_day'=>'0', 'is_public'=>'0', 'is_active'=>'0', 'network'=>'foursquare',
        'last_favorite_id' => '0', 'last_unfav_page_checked' => '0', 'last_page_fetched_favorites' => '0',
        'owner_favs_in_system' => '0', 'total_posts_by_owner'=>0,
        'posts_per_day'=>0, 'posts_per_week'=>0, 'percentage_replies'=>0, 'percentage_links'=>0,
        'earliest_post_in_system'=>'01-01-2009', 'favorites_profile' => '0',
        'url' => 'http://www.fousquare.com/user/18127856'
        );
        // Create an instance with these details
        $this->profile1_instance = new Instance($r);
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
        parent::tearDown();
        $this->logger->close();
    }
    
    // Test the constructor
    public function testConstructor() {
        // Create a new foursquare crawler for this instance with the access token fauxaccesstoken
        $fsc = new FoursquareCrawler($this->profile1_instance, 'fauxaccesstoken', 10);
        // Check the access token was set correctly
        $this->assertEqual($fsc->access_token, 'fauxaccesstoken');
    }
    
    
    public function testFetchUser() {
        // Create a new foursquare crawler for this instance with the access token fauxaccesstoken
        $fsc = new FoursquareCrawler($this->profile1_instance, 'secret', 10);
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
        $this->assertEqual($user->user_id, 18127856);
        // Check the location is correct
        $this->assertEqual($user->location, "Harefield, UK");
        // Check the URL was set correctly
        $this->assertEqual($user->url, 'http://www.foursquare.com/user/18127856');
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
        $this->assertEqual($user->user_id, 18127856);
        // Check the location is correct
        $this->assertEqual($user->location, "Harefield, UK");
        // Check the URL was set correctly
        $this->assertEqual($user->url, 'http://www.foursquare.com/user/18127856');
        // Check the user isn't protected
        $this->assertFalse($user->is_protected);
    }
    
    public function testGetOAuthTokens() {
        // Create a new foursquare crawler for this instance with a valid access token
        $fsc = new FoursquareCrawler($this->profile1_instance, 'secret', 10);

        // Test getting token
        $tokens = $fsc->getOAuthTokens('test_client_id', 'test_client_secret',
        'http://dev.thinkup.com/account/?p=foursquare', 'test-foursquare-provided-code');
        $this->assertEqual($tokens->access_token, 'secret');

    }
    
    public function testFetchInstanceUserCheckins() {
        $builders = self::buildData();
         
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
        $this->assertEqual($post->author_avatar, 'https://foursquare.com/img/blank_boy.png');
        // Check the author used id was set
        $this->assertEqual($post->author_user_id, '18127856');
        // Check the publication date was set
        $this->assertEqual($post->pub_date, '2011-12-27 17:31:50');
        // Check the checkin was set as public
        $this->assertFalse($post->is_protected);
        // Check the place name was set
        $this->assertEqual($post->place, 'Bedworth Sloughs');
        // Check the location was set
        $this->assertEqual($post->location, 'Bedworth, CV12 0AS');   
        // Check the place ID was set
        $this->assertEqual($post->place_id, '4e22eac31838712abe8186e3');
        // Check the geo co ordinates were set
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
        $map_url .= "=color:blue%7C52.477241961421,-1.4845029364055&sensor=false";
        // Check the map image was set
        $this->assertEqual($place['map_image'], $map_url);
        // Check the lat long co ordinates were set
        $this->assertPattern('/POINT\(52.477241961421/', $place['longlat']);
        $this->assertPattern('/ -1.4845029364055/', $place['longlat']);
               
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
        $this->assertEqual($comment->author_avatar, 'https://foursquare.com/img/blank_boy.png');
        // Check the author used id was set
        $this->assertEqual($comment->author_user_id, '18127856');
        // Check the publication date was set
        $this->assertEqual($comment->pub_date, '2012-02-19 17:48:41');
        // Check the checkin was set as public
        $this->assertFalse($comment->is_protected);
        // Check the place name was set
        $this->assertEqual($comment->place, 'Bedworth Sloughs');
        // Check the location was set
        $this->assertEqual($comment->location, 'Bedworth, CV12 0AS');   
        // Check the place ID was set
        $this->assertEqual($comment->place_id, '4e22eac31838712abe8186e3');
        // Check the geo co ordinates were set
        $this->assertEqual($comment->geo, '52.477241961421,-1.4845029364055');

        // Check the link for this checkin was set
        // Link string
        $link_string = 'https://img-s.foursquare.com/pix/noTc5a0afTgqTuLlsi3a33tLR0iUOZaa2hLm7LsNn1Q.jpg';
        $link = $link_dao->getLinkByUrl($link_string);
        
        // Check the expanded url was set correctly
        // Expanded URL string
        $expanded_url = 'https://img-s.foursquare.com/pix/noTc5a0afTgqTuLlsi3a33tLR0iUOZaa2hLm7LsNn1Q.jpg';
        $this->assertEqual($link->expanded_url, $expanded_url);
        // Check the title was set
        $this->assertEqual($link->title, 'Checkin Photo');
        // Check the description was set
        $this->assertEqual($link->description, 'Checkin Photo');
        // Check the img_src was set
        // img_src string
        $img_src = 'https://img-s.foursquare.com/pix/noTc5a0afTgqTuLlsi3a33tLR0iUOZaa2hLm7LsNn1Q.jpg';
        $this->assertEqual($link->image_src, $img_src);
        // Check the caption was set
        $this->assertEqual($link->caption, 'Checkin Photo'); 
        // Check the post key is correct
        $this->assertEqual($link->post_key, 1); 
    }

}
