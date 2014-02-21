<?php

/**
 *
 * ThinkUp/tests/TestOfPostInsightAPIController.php
 *
 * Copyright (c) 2013 Nilaksh Das
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
 * Test of InsightAPIController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Nilaksh Das
 * @author Nilaksh Das <nilakshdas@gmail.com>
 */
require_once dirname(__FILE__) . '/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfInsightAPIController extends ThinkUpInsightUnitTestCase {

    public function setUp() {
        parent::setUp();
        $config = Config::getInstance();
        $this->builders = self::buildData();
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    protected function buildData() {
        $builders = array();

        $hashed_pass = ThinkUpTestLoginHelper::hashPasswordUsingDeprecatedMethod("oldpassword");

        $builders[] = FixtureBuilder::build( 'owners', array(
            'id'=>1,
            'full_name'=>'ThinkUp J. User',
            'email'=>'me@example.com', 'is_activated'=>1,
            'is_admin'=>1,
            'pwd'=>$hashed_pass,
            'pwd_salt'=> OwnerMySQLDAO::$default_salt,
            'api_key' => 'c9089f3c9adaf0186f6ffb1ee8d6501c'));

        $builders[] = FixtureBuilder::build( 'instances', array('id'=>1));

        $builders[] = FixtureBuilder::build('owner_instances', array( 'owner_id' => 1, 'instance_id' => 1));

        $builders[] = FixtureBuilder::build( 'users', array(
            'user_id' => 13,
            'user_name' => 'ev',
            'full_name' => 'Ev Williams',
            'avatar' => 'avatar.jpg',
            'is_protected' => 0,
            'follower_count' => 10,
            'last_updated' => '2005-01-01 13:48:05',
            'network' => 'twitter'));

        $builders[] = FixtureBuilder::build( 'users', array(
            'user_id' => 14,
            'user_name' => 'jane',
            'full_name' => 'jane mcnulty',
            'avatar' => 'avatar.jpg',
            'is_protected' => 0,
            'follower_count' => 10,
            'last_updated' => '2005-01-01 13:48:05',
            'network' => 'twitter'));

        $builders[] = FixtureBuilder::build( 'users', array(
            'user_id' => 18,
            'user_name' => 'shutterbug',
            'full_name' => 'Shutter Bug',
            'avatar' => 'avatar.jpg',
            'is_protected' => 0,
            'follower_count' => 10,
            'network' => 'twitter'));

        $builders[] = FixtureBuilder::build( 'users', array(
            'user_id' => 19,
            'user_name' => 'linkbaiter',
            'full_name' => 'Link Baiter',
            'avatar' => 'avatar.jpg',
            'is_protected' => 0,
            'follower_count' => 15,
            'network' => 'twitter',
            'last_updated' => '2010-03-02 13:45:55'));

        $builders[] = FixtureBuilder::build( 'users', array(
            'user_id' => 20,
            'user_name' => 'user1',
            'full_name' => 'User 1',
            'avatar' => 'avatar.jpg',
            'is_protected' => 0,
            'follower_count' => 120,
            'network' => 'twitter'));

        //protected user
        $builders[] = FixtureBuilder::build( 'users', array(
            'user_id' => 21,
            'user_name' => 'user2',
            'full_name' => 'User 2',
            'avatar' => 'avatar.jpg',
            'is_protected' => 1,
            'follower_count' => 80,
            'network' => 'twitter'));

        $builders[] = FixtureBuilder::build( 'users', array(
            'user_id' => 22,
            'user_name' => 'quoter',
            'full_name' => 'Quotables',
            'is_protected' => 0,
            'follower_count' => 80,
            'network' => 'twitter'));

        $builders[] = FixtureBuilder::build( 'users', array(
            'user_id' => 23,
            'user_name' => 'user3',
            'full_name' => 'User 3',
            'is_protected' => 0,
            'follower_count' => 100,
            'network' => 'twitter'));

        $builders[] = FixtureBuilder::build( 'users', array(
            'user_id' => 24,
            'user_name' => 'notonpublictimeline',
            'full_name' => 'Not on Public Timeline',
            'is_protected' => 1,
            'network' => 'twitter',
            'follower_count' => 100));

        //Make public
        $builders[] = FixtureBuilder::build( 'instances', array(
            'network_user_id' => 13,
            'network_username' => 'ev',
            'is_public' => 1,
            'network' => 'twitter'));

        $builders[] = FixtureBuilder::build( 'instances', array(
            'network_user_id' => 18,
            'network_username' => 'shutterbug',
            'is_public' => 1,
            'network' => 'twitter'));

        $builders[] = FixtureBuilder::build( 'instances', array(
            'network_user_id' => 19,
            'network_username' => 'linkbaiter',
            'is_public' => 1,
            'network' => 'twitter'));

        $builders[] = FixtureBuilder::build( 'instances', array(
            'network_user_id' => 23,
            'network_username' => 'user3',
            'is_public' => 1,
            'network' => 'twitter'));

        $builders[] = FixtureBuilder::build( 'instances', array(
            'network_user_id' => 24,
            'network_username' => 'notonpublictimeline',
            'is_public' => 0,
            'network' => 'twitter'));

        //public on originating network, private on TU
        $builders[] = FixtureBuilder::build( 'instances', array(
            'network_user_id' => 14,
            'network_username' => 'jane',
            'is_public' => 0,
            'network' => 'twitter'));

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
            $builders[] = FixtureBuilder::build( 'posts', array(
                'post_id' => $counter,
                'author_user_id' => 13,
                'author_username' => 'ev',
                'author_fullname' => 'Ev Williams',
                'author_avatar' => 'avatar.jpg',
                'post_text' => 'This is post ' . $counter,
                'source' => $source,
                'pub_date' => '2006-01-01 00:' . $pseudo_minute . ':00',
                'reply_count_cache' => rand(0, 4),
                'retweet_count_cache' => 5,
                'network' => 'twitter',
                'old_retweet_count_cache' => 0,
                'in_rt_of_user_id' => null,
                'in_reply_to_post_id' => null,
                'in_retweet_of_post_id' => null,
                'is_geo_encoded' => 0,
                'is_protected'=>0));
            $counter++;
        }

        //Add photo posts from Flickr
        $counter = 0;
        while ($counter < 40) {
            $post_id = $counter + 40;
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
            $source = rand(0,1) == 0 ? 'Flickr' : 'Picasa';
            $protected =  (($counter % 2) == 1)?1:0;

            $builders[] = FixtureBuilder::build( 'posts', array(
                'post_id' => $post_id,
                'network' => 'twitter',
                'author_user_id' => 18,
                'author_username' => 'shutterbug',
                'author_fullname' => 'Shutter Bug',
                'author_avatar' => 'avatar.jpg',
                'post_text' => 'This is image post ' . $counter,
                'source' => $source,
                'in_reply_to_post_id' => null,
                'in_retweet_of_post_id' => null,
                'old_retweet_count_cache' => 0,
                'in_rt_of_user_id' => null,
                'pub_date' => '2006-01-02 00:' . $pseudo_minute . ':00',
                'network' => 'twitter',
                'is_geo_encoded' => 0,
                'is_protected' => $protected));

            $builders[] = FixtureBuilder::build( 'links', array(
                'url' => 'http://example.com/' . $counter,
                'expanded_url' => 'http://example.com/' . $counter . '.jpg',
                'title' => '',
                'clicks' => 0,
                'post_key' => $post_id,
                'image_src' => 'image.png'));
            $counter++;
        }

        //Add link posts
        $counter = 0;
        while ($counter < 40) {
            $post_id = $counter + 80;
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
            $builders[] = FixtureBuilder::build( 'posts', array( 'post_id' => $post_id,
                'author_user_id' => 19,
                'author_username' => 'linkbaiter',
                'author_fullname' => 'Link Baiter',
                'is_geo_encoded' => 0,
                'old_retweet_count_cache' => 0,
                'in_rt_of_user_id' => null,
                'post_text' => 'This is link post ' . $counter,
                'source' => 'web',
                'pub_date' => '2006-03-01 00:' . $pseudo_minute . ':00',
                'reply_count_cache' => 0,
                'retweet_count_cache' => 0,
                'network' => 'twitter',
                'is_protected'=>0));
            $builders[] = FixtureBuilder::build( 'links', array(
                'url' => 'http://example.com/' . $post_id.'',
                'explanded_url' => 'http://example.com/' . $counter . '.html',
                'title' => 'Link '.$counter,
                'clicks' => 0,
                'post_key' => $post_id,
                'image_src' => ''));
            $counter++;
        }

        //Add mentions
        $counter = 0;
        while ($counter < 10) {
            $post_id = $counter + 120;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);
            if (($counter / 2) == 0) {
                $builders[] = FixtureBuilder::build( 'posts', array(
                    'post_id' => $post_id,
                    'author_user_id' => 20,
                    'author_username' => 'user1',
                    'author_fullname' => 'User 1',
                    'in_reply_to_post_id' => null,
                    'in_retweet_of_post_id' => null,
                    'is_geo_encoded' => 0,
                    'network' => 'twitter',
                    'old_retweet_count_cache' => 0,
                    'in_rt_of_user_id' => null,
                    'post_text' => 'Hey @ev and @jack thanks for founding Twitter post ' . $counter,
                    'pub_date' => '2006-03-01 00:' . $pseudo_minute . ':00',
                    'location' => 'New Delhi',
                    'is_protected'=>0));
            } else {
                $builders[] = FixtureBuilder::build( 'posts', array(
                    'post_id' => $post_id,
                    'author_user_id' => 21,
                    'author_username' => 'user2',
                    'author_fullname' => 'User 2',
                    'in_reply_to_post_id' => null,
                    'in_retweet_of_post_id' => null,
                    'is_geo_encoded' => 0,
                    'network' => 'twitter',
                    'old_retweet_count_cache' => 0,
                    'in_rt_of_user_id' => null,
                    'post_text' => 'Hey @ev and @jack should fix Twitter - post ' . $counter,
                    'pub_date' => '2006-03-01 00:' . $pseudo_minute . ':00',
                    'place' => 'New Delhi',
                    'is_protected'=>0));
            }
            $counter++;
        }

        //Add replies to specific post
        $builders[] = FixtureBuilder::build( 'posts', array(
            'post_id' => 131,
            'author_user_id' => 20,
            'author_username' => 'user1',
            'author_fullname' => 'User 1',
            'author_follower_count' => 120,
            'network' => 'twitter',
            'post_text' => '@shutterbug Nice shot!',
            'source' => 'web',
            'pub_date' => '2006-03-01 00:00:00',
            'reply_count_cache' => 3,
            'retweet_count_cache' => 0,
            'in_reply_to_post_id' => 41,
            'in_reply_to_user_id' => 18,
            'old_retweet_count_cache' => 0,
            'in_rt_of_user_id' => null,
            'in_retweet_of_post_id' => null,
            'location' => 'New Delhi, Delhi, India',
            'reply_retweet_distance' => 0,
            'is_geo_encoded' => 1,
            'is_protected' => 0));

        $builders[] = FixtureBuilder::build( 'posts', array(
            'post_id' => 132,
            'author_user_id' => 21,
            'author_username' => 'user2',
            'author_fullname' => 'User 2',
            'network' => 'twitter',
            'post_text' => '@shutterbug Nice shot!',
            'source' => 'web',
            'pub_date' => '2006-03-01 00:00:00',
            'reply_count_cache' => 0,
            'retweet_count_cache' => 0,
            'in_reply_to_post_id' => 41,
            'in_reply_to_user_id' => 18,
            'old_retweet_count_cache' => 0,
            'in_rt_of_user_id' => null,
            'in_retweet_of_post_id' => null,
            'location' => 'Chennai, Tamil Nadu, India',
            'reply_retweet_distance' => 2000,
            'is_geo_encoded' => 1,
            'is_protected' => 1));

        $builders[] = FixtureBuilder::build( 'posts', array(
            'post_id' => 133,
            'author_user_id' => 19,
            'author_username' => 'linkbaiter',
            'author_fullname' => 'Link Baiter',
            'author_follower_count' => 15,
            'network' => 'twitter',
            'post_text' => '@shutterbug This is a link post reply http://example.com/',
            'source' => 'web',
            'pub_date' => '2006-03-01 00:00:00',
            'reply_count_cache' => 0,
            'retweet_count_cache' => 0,
            'old_retweet_count_cache' => 0,
            'in_rt_of_user_id' => null,
            'in_retweet_of_post_id' => null,
            'in_reply_to_post_id' => 41,
            'in_reply_to_user_id' => 18,
            'location' => 'Mumbai, Maharashtra, India',
            'reply_retweet_distance' => 1500,
            'is_geo_encoded' => 1,
            'is_protected' => 0));

        $builders[] = FixtureBuilder::build( 'links', array(
            'url' => 'http://example.com',
            'expanded_url' => 'http://example.com/expanded-link.html',
            'title' => 'Link 1',
            'clicks' => 0,
            'post_id' => 133,
            'image_src' => ''));

        //Add retweets of a specific post
        //original post
        $builders[] = FixtureBuilder::build( 'posts', array(
            'post_id' => 134,
            'author_user_id' => 22,
            'author_username' => 'quoter',
            'author_fullname' => 'Quoter of Quotables',
            'network' => 'twitter',
            'post_text' => 'Be liberal in what you accept and conservative in what you send',
            'source' => 'web',
            'pub_date' => '2006-03-01 00:00:00',
            'reply_count_cache' => 2,
            'retweet_count_cache' => 3,
            'old_retweet_count_cache' => 0,
            'in_rt_of_user_id' => null,
            'in_retweet_of_post_id' => null,
            'location' => 'New Delhi, Delhi, India',
            'geo' => '28.635308,77.22496',
            'is_geo_encoded' => 1,
            'is_protected' => 0));

        // original post 2
        $builders[] = FixtureBuilder::build( 'posts', array(
            'post_id' => 148,
            'author_user_id' => 22,
            'author_username' => 'quoter',
            'author_fullname' => 'Quoter of Quotables',
            'network' => 'twitter',
            'post_text' => 'The cake is a lie.',
            'source' => 'web',
            'pub_date' => '2006-03-01 00:00:00',
            'reply_count_cache' => 2,
            'retweet_count_cache' => 3,
            'old_retweet_count_cache' => 0,
            'in_rt_of_user_id' => null,
            'in_retweet_of_post_id' => null,
            'location' => 'New Delhi, Delhi, India',
            'geo' => '28.635308,77.22496',
            'is_geo_encoded' => 1,
            'is_protected' => 0));
        //retweet 1
        $builders[] = FixtureBuilder::build( 'posts', array(
            'post_id' => 135,
            'author_user_id' => 20,
            'author_username' => 'user1',
            'author_fullname' => 'User 1',
            'network' => 'twitter',
            'post_text' => 'RT @quoter Be liberal in what you accept and conservative in what you send',
            'source' => 'web',
            'pub_date' => '2006-03-01 00:01:00',
            'reply_count_cache' => 0,
            'retweet_count_cache' => 0,
            'old_retweet_count_cache' => 0,
            'in_rt_of_user_id' => 22,
            'in_retweet_of_post_id' => 134,
            'location' => 'Chennai, Tamil Nadu, India',
            'geo' => '13.060416,80.249634',
            'reply_retweet_distance' => 2000,
            'is_geo_encoded' => 1,
            'in_reply_to_post_id' => null,
            'is_protected' => 0));
        //retweet 2
        $builders[] = FixtureBuilder::build( 'posts', array(
            'post_id' => 136,
            'author_user_id' => 21,
            'author_username' => 'user2',
            'author_fullname' => 'User 2',
            'network' => 'twitter',
            'post_text' => 'RT @quoter Be liberal in what you accept and conservative in what you send',
            'source' => 'web',
            'pub_date' => '2006-03-01 00:02:00',
            'reply_count_cache' => 0,
            'retweet_count_cache' => 0,
            'old_retweet_count_cache' => 0,
            'in_rt_of_user_id' => 22,
            'in_retweet_of_post_id' => 134,
            'location' => 'Dwarka, New Delhi, Delhi, India',
            'geo' => '28.635308,77.22496',
            'reply_retweet_distance' => '0',
            'is_geo_encoded' => 1,
            'is_protected' => 0));
        //retweet 3
        $builders[] = FixtureBuilder::build( 'posts', array(
            'post_id' => 137,
            'author_user_id' => 19,
            'author_username' => 'linkbaiter',
            'author_fullname' => 'Link Baiter',
            'network' => 'twitter',
            'post_text' => 'RT @quoter Be liberal in what you accept and conservative in what you send',
            'source' => 'web',
            'pub_date' => '2006-03-01 00:03:00',
            'reply_count_cache' => 0,
            'retweet_count_cache' => 0,
            'old_retweet_count_cache' => 0,
            'in_rt_of_user_id' => 22,
            'in_retweet_of_post_id' => 134,
            'location' => 'Mumbai, Maharashtra, India',
            'geo' => '19.017656,72.856178',
            'reply_retweet_distance' => 1500,
            'is_geo_encoded' => 1,
            'is_protected' => 0));

        //retweet 4
        $builders[] = FixtureBuilder::build( 'posts', array(
            'post_id' => 149,
            'author_user_id' => 19,
            'author_username' => 'linkbaiter',
            'author_fullname' => 'Link Baiter',
            'network' => 'twitter',
            'post_text' => 'RT @quoter The cake is a lie.',
            'source' => 'web',
            'pub_date' => '2006-03-01 00:00:00',
            'reply_count_cache' => 0,
            'retweet_count_cache' => 0,
            'old_retweet_count_cache' => 0,
            'in_rt_of_user_id' => 22,
            'in_retweet_of_post_id' => 148,
            'location' => 'Mumbai, Maharashtra, India',
            'geo' => '19.017656,72.856178',
            'reply_retweet_distance' => 1500,
            'is_geo_encoded' => 1,
            'is_protected' => 0));

        //Add reply back
        $builders[] = FixtureBuilder::build( 'posts', array(
            'post_id' => 138,
            'author_user_id' => 18,
            'author_username' => 'shutterbug',
            'author_fullname' => 'Shutterbug',
            'network' => 'twitter',
            'post_text' => '@user2 Thanks!',
            'source' => 'web',
            'pub_date' => '2006-03-01 00:00:00',
            'old_retweet_count_cache' => 0,
            'in_rt_of_user_id' => null,
            'reply_count_cache' => 0,
            'retweet_count_cache' => 0,
            'in_reply_to_user_id' => 21,
            'in_reply_to_post_id' => 132,
            'is_protected'=>0));

        //Add user exchange
        $builders[] = FixtureBuilder::build( 'posts', array(
            'post_id' => 139,
            'author_user_id' => 20,
            'author_username' => 'user1',
            'author_fullname' => 'User 1',
            'network' => 'twitter',
            'post_text' => '@ev When will Twitter have a business model?',
            'source' => 'web',
            'pub_date' => '2006-03-01 00:00:00',
            'reply_count_cache' => 0,
            'retweet_count_cache' => 0,
            'old_retweet_count_cache' => 0,
            'in_rt_of_user_id' => null,
            'in_reply_to_post_id' => null,
            'in_reply_to_user_id' => 13,
            'is_protected' => 1));

        $builders[] = FixtureBuilder::build( 'posts', array(
            'post_id' => 140,
            'author_user_id' => 13,
            'author_username' => 'ev',
            'author_fullname' => 'Ev Williams',
            'network' => 'twitter',
            'post_text' => '@user1 Soon...',
            'source' => 'web',
            'pub_date' => '2006-03-01 00:00:00',
            'old_retweet_count_cache' => 0,
            'in_rt_of_user_id' => null,
            'reply_count_cache' => 0,
            'retweet_count_cache' => 0,
            'in_reply_to_user_id' => 20,
            'in_reply_to_post_id' => 139,
            'is_protected'=>0));

        //Add posts replying to post not in the system
        $builders[] = FixtureBuilder::build( 'posts', array(
            'post_id' => 141,
            'author_user_id' => 23,
            'author_username' => 'user3',
            'author_fullname' => 'User 3',
            'network' => 'twitter',
            'post_text' => '@user4 I\'m replying to a post not in the TT db',
            'source' => 'web',
            'pub_date' => '2006-03-01 00:00:00',
            'reply_count_cache' => 0,
            'retweet_count_cache' => 0,
            'old_retweet_count_cache' => 0,
            'in_rt_of_user_id' => null,
            'in_reply_to_user_id' => 20,
            'in_reply_to_post_id' => 250,
            'is_protected'=>0));

        $builders[] = FixtureBuilder::build( 'posts', array(
            'post_id' => 142,
            'author_user_id' => 23,
            'author_username' => 'user3',
            'author_fullname' => 'User 3',
            'network' => 'twitter',
            'post_text' => '@user4 I\'m replying to another post not in the TT db',
            'source' => 'web',
            'pub_date' => '2006-03-01 00:00:00',
            'reply_count_cache' => 0,
            'retweet_count_cache' => 0,
            'old_retweet_count_cache' => 0,
            'in_rt_of_user_id' => null,
            'in_reply_to_user_id' => 20,
            'in_reply_to_post_id' => 251,
            'is_protected'=>0));

        //Add post by instance not on public timeline
        $builders[] = FixtureBuilder::build( 'posts', array(
            'post_id' => 143,
            'author_user_id' => 24,
            'author_username' => 'notonpublictimeline',
            'author_fullname' => 'Not on public timeline',
            'network' => 'twitter',
            'post_text' => 'This post should not be on the public timeline',
            'old_retweet_count_cache' => 0,
            'in_rt_of_user_id' => null,
            'source' => 'web',
            'pub_date' => '2006-03-01 12:00:00'));

        //Add replies to specific post
        $builders[] = FixtureBuilder::build( 'posts', array(
            'post_id' => 144,
            'author_user_id' => 20,
            'author_username' => 'user1',
            'author_fullname' => 'User 1',
            'network' => 'twitter',
            'post_text' => '@quoter Indeed, Jon Postel.',
            'source' => 'web',
            'pub_date' => '2006-03-01 00:00:00',
            'old_retweet_count_cache' => 0,
            'in_rt_of_user_id' => null,
            'in_retweet_of_post_id' => null,
            'reply_count_cache' => 0,
            'retweet_count_cache' => 0,
            'is_reply_by_friend' => 1,
            'in_reply_to_post_id' => 134,
            'network' => 'twitter',
            'location' => 'New Delhi, Delhi, India',
            'geo' => '28.635308, 77.22496',
            'is_geo_encoded' => 1));

        $builders[] = FixtureBuilder::build( 'posts', array(
            'post_id' => 145,
            'author_user_id' => 20,
            'author_username' => 'user1',
            'author_fullname' => 'User 1',
            'network' => 'twitter',
            'post_text' => '@quoter Fo sho.',
            'source' => 'web',
            'pub_date' => '2006-03-01 00:00:00',
            'old_retweet_count_cache' => 0,
            'in_rt_of_user_id' => null,
            'in_retweet_of_post_id' => null,
            'reply_count_cache' => 0,
            'retweet_count_cache' => 0,
            'is_reply_by_friend' => 1,
            'in_reply_to_post_id' => 134,
            'network' => 'twitter',
            'location' => 'New Delhi, Delhi, India',
            'geo' => '28.635308, 77.22496',
            'is_geo_encoded' => 1));

        // add another post to user 22 to have a reply to
        $builders[] = FixtureBuilder::build( 'posts', array(
            'post_id' => 146,
            'author_user_id' => 22,
            'author_username' => 'quoter',
            'author_fullname' => 'Quoter of Quotables',
            'network' => 'twitter',
            'post_text' => 'I love cake.',
            'source' => 'web',
            'pub_date' => '2006-03-01 00:00:00',
            'reply_count_cache' => 1,
            'retweet_count_cache' => 0,
            'old_retweet_count_cache' => 0,
            'in_rt_of_user_id' => null,
            'in_retweet_of_post_id' => null,
            'location' => 'New Delhi, Delhi, India',
            'geo' => '28.635308,77.22496',
            'is_geo_encoded' => 1,
            'is_protected' => 0));


        $builders[] = FixtureBuilder::build( 'posts', array(
            'post_id' => 147,
            'author_user_id' => 20,
            'author_username' => 'user1',
            'author_fullname' => 'User 1',
            'network' => 'twitter',
            'post_text' => '@quoter YEAH CAKE.',
            'source' => 'web',
            'pub_date' => '2006-03-01 00:00:00',
            'old_retweet_count_cache' => 0,
            'in_rt_of_user_id' => null,
            'in_retweet_of_post_id' => null,
            'reply_count_cache' => 0,
            'retweet_count_cache' => 0,
            'is_reply_by_friend' => 1,
            'in_reply_to_post_id' => 146,
            'network' => 'twitter',
            'location' => 'New Delhi, Delhi, India',
            'geo' => '28.635308, 77.22496',
            'is_geo_encoded' => 1));

        $builders[] = FixtureBuilder::build( 'posts', array(
            'post_id' => 150,
            'author_user_id' => 20,
            'author_username' => 'user1',
            'author_fullname' => 'User 1',
            'network' => 'twitter',
            'post_text' => '@ev How soon? :p',
            'source' => 'web',
            'pub_date' => '2006-03-01 00:00:00',
            'reply_count_cache' => 0,
            'retweet_count_cache' => 0,
            'old_retweet_count_cache' => 0,
            'in_rt_of_user_id' => null,
            'in_reply_to_post_id' => null,
            'in_reply_to_user_id' => 13,
            'is_protected' => 0));

        $builders[] = FixtureBuilder::build( 'posts', array(
            'post_id' => 151,
            'author_user_id' => 20,
            'author_username' => 'user1',
            'author_fullname' => 'User 1',
            'network' => 'twitter',
            'post_text' => '@ev Tomorrow? :p',
            'source' => 'web',
            'pub_date' => '2006-03-01 00:02:00',
            'reply_count_cache' => 0,
            'retweet_count_cache' => 0,
            'old_retweet_count_cache' => 0,
            'in_rt_of_user_id' => null,
            'in_reply_to_post_id' => null,
            'in_reply_to_user_id' => 13,
            'is_protected' => 0));

        //protected post
        $builders[] = FixtureBuilder::build( 'posts', array(
            'post_id' => 152,
            'author_user_id' => 21,
            'author_username' => 'user2',
            'author_fullname' => 'User 2',
            'network' => 'twitter',
            'post_text' => 'Protect me',
            'source' => 'web',
            'pub_date' => '2006-03-01 00:02:00',
            'reply_count_cache' => 0,
            'retweet_count_cache' => 0,
            'old_retweet_count_cache' => 0,
            'in_rt_of_user_id' => null,
            'in_reply_to_post_id' => null,
            'in_reply_to_user_id' => null,
            'is_protected' => 1));

        // insights
        $time_now = date("Y-m-d H:i:s");
        $builders[] = FixtureBuilder::build('insights', array(
            'date'=>'2012-05-01',
            'slug'=>'avg_replies_per_week',
            'instance_id'=>'1',
            'text'=>'Retweet spike! Your post got retweeted 110 times',
            'emphasis'=>Insight::EMPHASIS_HIGH,
            'time_generated'=>$time_now,
            'related_data'=>$this->getRelatedDataListOfPosts()));

        $builders[] = FixtureBuilder::build('insights', array(
            'date'=>'2012-05-02',
            'slug'=>'avg_replies_per_week',
            'instance_id'=>'1',
            'text'=>'Retweet spike! Your post got retweeted 110 times',
            'emphasis'=>Insight::EMPHASIS_HIGH,
            'time_generated'=>$time_now,
            'related_data'=>$this->getRelatedDataListOfPosts()));

        $builders[] = FixtureBuilder::build('insights', array(
            'date'=>'2012-05-03',
            'slug'=>'another_slug',
            'instance_id'=>'1',
            'text'=>'Retweet spike! Your post got retweeted 110 times',
            'emphasis'=>Insight::EMPHASIS_HIGH,
            'time_generated'=>$time_now,
            'related_data'=>$this->getRelatedDataListOfPosts()));

        $builders[] = FixtureBuilder::build('insights', array(
            'date'=>'2012-05-04',
            'slug'=>'another_slug',
            'instance_id'=>'1',
            'text'=>'Retweet spike! Your post got retweeted 110 times',
            'emphasis'=>Insight::EMPHASIS_HIGH,
            'time_generated'=>$time_now,
            'related_data'=>$this->getRelatedDataListOfPosts()));

        return $builders;
    }

    public function testInsight() {
        $_GET['un'] = 'me@example.com';
        $_GET['as'] = 'c9089f3c9adaf0186f6ffb1ee8d6501c';

        $controller = new InsightAPIController(true);
        $output = $controller->go();
        $this->debug( Utils::varDumpToString($output));
        $output = JSONDecoder::decode($output);

        $this->debug(Utils::varDumpToString($output));
        // Test correct number of insights were retrieved
        $this->assertEqual(count($output), 4);
        $this->assertEqual(count($output[0]->related_data->posts), 3);
    }

    public function testAPIDisabled() {
        $_GET['un'] = 'me@example.com';
        $_GET['as'] = 'c9089f3c9adaf0186f6ffb1ee8d6501c';

        // test default option
        $controller = new InsightAPIController(true);
        $output = $controller->go();
        $this->debug($output);
        $output = json_decode($output);

        $this->assertFalse(isset($output->error));

        // test option true
        $option_dao = DAOFactory::getDAO('OptionDAO');
        $option_dao->insertOption(OptionDAO::APP_OPTIONS, 'is_api_disabled', 'true');

        $controller = new InsightAPIController(true);
        $output = $controller->go();
        $this->debug($output);
        $output = json_decode($output);

        $this->assertEqual($output->error->type, 'APIDisabledException');

        // test option false
        $option_dao->updateOptionByName(OptionDAO::APP_OPTIONS, 'is_api_disabled', 'false');

        $controller = new InsightAPIController(true);
        $output = $controller->go();
        $this->debug($output);
        $output = JSONDecoder::decode($output);

        $this->assertFalse(isset($output->error));
        $this->assertEqual(count($output[0]->related_data->posts), 3);
    }

    public function testAPIAuth() {
        $_GET['un'] = 'me@example.com';

        // test missing api_key
        $_GET['as'] = "add";

        $controller = new InsightAPIController(true);
        $output = $controller->go();
        $this->debug($output);
        $output = json_decode($output);

        $this->assertEqual($output->error->type, 'UnauthorizedUserException');

        // test incorrect api_key
        $_GET['api_key'] = 'abcd';

        $controller = new InsightAPIController(true);
        $output = $controller->go();
        $this->debug($output);
        $output = json_decode($output);

        $this->assertEqual($output->error->type, 'UnauthorizedUserException');

        // test correct api_key
        $_GET['as'] = 'c9089f3c9adaf0186f6ffb1ee8d6501c';

        $controller = new InsightAPIController(true);
        $output = $controller->go();
        $this->debug($output);
        $output = json_decode($output);

        $this->assertFalse(isset($output->error));
    }

    public function testInsightNotFound() {
        $_GET['un'] = 'me@example.com';
        $_GET['as'] = 'c9089f3c9adaf0186f6ffb1ee8d6501c';
        $_GET['since'] = time() + (7 * 24 * 60 * 60);
        $this->debug(date('D, d M Y H:i:s', time() + (7 * 24 * 60 * 60)));

        $controller = new InsightAPIController(true);
        $output = $controller->go();
        //sleep(1000);
        $this->debug($output);
        $output = json_decode($output);

        $this->assertEqual($output->error->type, "InsightNotFoundException");
    }
}