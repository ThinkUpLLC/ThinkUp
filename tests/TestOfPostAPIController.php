<?php

/**
 *
 * ThinkUp/tests/TestOfPostAPIController.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
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
 * Test of PostAPIController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Sam Rose
 * @author Sam Rose <samwho@lbak.co.uk>
 */
require_once dirname(__FILE__) . '/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfPostAPIController extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
        $this->builders = self::buildData();
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    protected static function buildData() {
        $builders = array();
        $builders[] = FixtureBuilder::build('owner_instances', array(
                            'owner_id' => 1,
                            'instance_id' => 1));

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
            $builders[] = FixtureBuilder::build(
                            'posts', array( 'post_id' => $post_id,
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
        // add instance 7
        $builders[] = FixtureBuilder::build('instances',
                array('network_user_id' => '100', 'network_viewer_id' => '100', 'network_username' => 'userhashtag',
                        'last_post_id'  => '1', 'crawler_last_run' => '2013-02-28 15:21:16', 'total_posts_by_owner' => 0,
                        'total_posts_in_system' => 0, 'total_replies_in_system' => 0, 'total_follows_in_system' => 0,
                        'posts_per_day' => 0, 'posts_per_week' => 0, 'percentage_replies' => 0, 'percentage_links' => 0,
                        'earliest_post_in_system' => '2013-02-28 15:21:16',
                        'earliest_reply_in_system' => '2013-02-28 15:21:16', 'is_archive_loaded_posts' => 0,
                        'is_archive_loaded_replies' => 0, 'is_archive_loaded_follows' => 0, 'is_public' => 0,
                        'is_active' => 0, 'network' => 'twitter', 'favorites_profile' => 0, 'owner_favs_in_system' => 0));

        // add instance_twitter
        $builders[] = FixtureBuilder::build('instances_twitter',
                array());

        // add hashtags 1 i 2
        $builders[] = FixtureBuilder::build('hashtags',
                array('hashtag' => 'first', 'network'=>'twitter', 'count_cache' => 0));
        $builders[] = FixtureBuilder::build('hashtags',
                array('hashtag' => '#second', 'network'=>'twitter', 'count_cache' => 0));

        // add instances_hashtags 1
        $builders[] = FixtureBuilder::build('instances_hashtags',
                array('instance_id' => 7, 'hashtag_id'=>1, 'last_post_id' => 0, 'earliest_post_id' => 0));

        // add users
        $builders[] = FixtureBuilder::build( 'users', array(
                'user_id' => 101,
                'user_name' => 'userhashtag1',
                'full_name' => 'User Hashtag1',
                'is_protected' => 0,
                'network' => 'twitter',
                'follower_count' => 101));
        $builders[] = FixtureBuilder::build( 'users', array(
                'user_id' => 102,
                'user_name' => 'userhashtag2',
                'full_name' => 'User Hashtag2',
                'is_protected' => 0,
                'network' => 'twitter',
                'follower_count' => 102));
        $builders[] = FixtureBuilder::build( 'users', array(
                'user_id' => 103,
                'user_name' => 'userhashtag3',
                'full_name' => 'User Hashtag3',
                'is_protected' => 0,
                'network' => 'twitter',
                'follower_count' => 103));

        $counter = 300;
        while ($counter <= 359) {
            $pseudo_minute = substr($counter, 1,2);
            if ($counter % 3 == 0) {
                $source = '<a href="http://twitter.com" rel="nofollow">Tweetie for Mac</a>';
                $userid = 1;
            } else if ($counter % 3 == 1) {
                $source = '<a href="http://twitter.com/tweetbutton" rel="nofollow">Tweet Button</a>';
                $userid = 2;
            } else {
                $source = 'web';
                $userid = 3;
            }
            $username = 'userhashtag'.$userid;
            $userfullname = 'User Hashtag'.$userid;
            $builders[] = FixtureBuilder::build( 'posts', array(
                    'post_id' => $counter,
                    'author_user_id' => $userid,
                    'author_username' => $username,
                    'author_fullname' => $userfullname,
                    'author_avatar' => 'avatar.jpg',
                    'post_text' => 'This is post ' . $counter,
                    'source' => $source,
                    'pub_date' => '2013-03-05 16:' . $pseudo_minute . ':00',
                    'reply_count_cache' => rand(0, 4),
                    'retweet_count_cache' => 5,
                    'network' => 'twitter',
                    'old_retweet_count_cache' => 0,
                    'in_rt_of_user_id' => null,
                    'in_reply_to_post_id' => null,
                    'in_retweet_of_post_id' => null,
                    'is_geo_encoded' => 0,
                    'is_protected'=>0));

            if ($counter % 2 == 0) {
                $builders[] = FixtureBuilder::build( 'hashtags_posts', array(
                        'post_id' => $counter, 'hashtag_id' => 2, 'network' => 'twitter'));
            }
            else {
                $builders[] = FixtureBuilder::build( 'hashtags_posts', array(
                        'post_id' => $counter, 'hashtag_id' => 1, 'network' => 'twitter'));
            }
            $counter++;
        }
        return $builders;
    }

    public function testPost() {
        $_GET['type'] = 'post';
        $_GET['post_id'] = '137';
        $_GET['network'] = 'twitter';
        $controller = new PostAPIController(true);
        $output = $controller->go();
        $this->debug($output);
        //sleep(1000);
        $output = json_decode($output);
        // test the object type is correct
        $this->assertTrue($output instanceof stdClass);
        $this->assertEqual($output->protected, false);

        // test that the correct tweet was retrieved
        $this->assertEqual($output->id, '137', "Incorrect post fetched.");

        $this->assertEqual(sizeof($output->coordinates->coordinates), 2,
         "Size of coordinates is too big or too small. Is " . sizeof($output->coordinates->coordinates) .
         " when it should be 2.");

        $this->assertEqual($output->thinkup->is_geo_encoded, 1);
        $this->assertEqual($output->coordinates, $output->geo, "Geo and coordinates are meant to be exactly the same.");

        //This date is stored in storage as 2010-03-02 08:45:55
        /**
         * This assertion evaluates differently depending on whether your MySQL server supports
         * SET timezone statement in PDODAO::connect function
         * $this->assertEqual($output->user->last_updated, '2010-03-02 13:45:55');
         */
        $this->assertTrue(strtotime($output->user->last_updated) > strtotime('2010-03-02 00:00:00'));
        $this->assertTrue(strtotime($output->user->last_updated) < strtotime('2010-03-03 00:00:00'));

        // test trim user
        $_GET['trim_user'] = true;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 1);

        $this->assertEqual(sizeof($output->user), 1);

        // test sql injection
        $_GET = array('type' => 'post');
        $prefix = Config::getInstance()->getValue('table_prefix');
        foreach(get_object_vars($controller) as $key => $value) {
            if ($key == 'type' || $key == 'app_session') continue;
            $_GET[$key] = "'; DROP TABLE " . $prefix . "posts--";
            $controller = new PostAPIController(true);
            $output = json_decode($controller->go());
            unset($_GET[$key]);
        }
        $installer_dao = DAOFactory::getDAO('InstallerDAO');
        $this->assertTrue(array_search($prefix . "posts", $installer_dao->getTables()) !== false);
    }

    public function testPostProtectedOnNetwork() {
        $_GET['type'] = 'post';
        $_GET['post_id'] = '152';
        $_GET['network'] = 'twitter';
        $controller = new PostAPIController(true);
        $output = $controller->go();
        $this->debug($output);
        //sleep(1000);
        $output = json_decode($output);
        // test that 0 data was returned
        $this->assertEqual(sizeof($output), 1);
        $this->assertEqual($output->error->type, "PostNotFoundException");
        $this->assertEqual($output->error->message, "The requested post data is not available.");
    }

    public function testPostRetweets() {
        $_GET['type'] = 'post_retweets';
        $_GET['post_id'] = '134';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());

        // test the object type is correct
        $this->assertTrue(is_array($output));
        foreach($output as $post) {
            $this->assertTrue($post instanceof stdClass);
            $this->assertEqual($post->protected, false);
            $this->assertEqual($post->retweeted_status->id, '134');
        }

        $this->assertEqual(sizeof($output), 3);
        if (sizeof($output) != 3) {
            print_r($output);
        }

        // test order_by
        $_GET['order_by'] = 'location';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());

        $distance = $output[0]->reply_retweet_distance;
        foreach ($output as $post) {
            $this->assertTrue($post->reply_retweet_distance >= $distance, "Retweets not correctly ordered by ".
            "distance. " . $post->reply_retweet_distance . " is not greater than " . $distance);
            $distance = $post->reply_retweet_distance;
        }

        // test order_by
        $_GET['order_by'] = 'date';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());

        $pub_date = strtotime($output[0]->created_at);
        foreach ($output as $post) {
            $this->assertTrue(strtotime($post->created_at) <= $pub_date);
            $pub_date = strtotime($post->created_at);
        }

        // test unit (lol?)
        $_GET['unit'] = 'mi';
        $controller = new PostAPIController(true);
        $output_mi = json_decode($controller->go());
        $_GET['unit'] = 'km';
        $controller = new PostAPIController(true);
        $output_km = json_decode($controller->go());

        foreach ($output_km as $key=>$post) {
            $this->assertEqual($output_mi[$key]->reply_retweet_distance,
            round($output_km[$key]->reply_retweet_distance/1.609));
        }

        // test count
        $_GET['count'] = 1;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 1);

        $_GET['count'] = 2;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 2);

        $_GET['count'] = 3;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 3);

        // test page
        $_GET['count'] = 1;
        $_GET['page'] = 1;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 1);
        $this->assertEqual($output[0]->id, 137);

        $_GET['page'] = 2;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 1);
        $this->assertEqual($output[0]->id, 136);

        $_GET['page'] = 3;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 1);
        $this->assertEqual($output[0]->id, 135);

        // test trim user
        unset($_GET['count'], $_GET['page']);
        $_GET['trim_user'] = true;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 3);

        foreach($output as $post) {
            $this->assertEqual(sizeof($post->user), 1);
        }

        // test sql injection
        $_GET = array('type' => 'post_retweets');
        $prefix = Config::getInstance()->getValue('table_prefix');
        foreach(get_object_vars($controller) as $key => $value) {
            if ($key == 'type' || $key == 'app_session') continue;
            $_GET[$key] = "'; DROP TABLE " . $prefix . "posts--";
            $controller = new PostAPIController(true);
            $output = json_decode($controller->go());
            unset($_GET[$key]);
        }
        $installer_dao = DAOFactory::getDAO('InstallerDAO');
        $this->assertTrue(array_search($prefix . "posts", $installer_dao->getTables()) !== false);
    }

    public function testPostReplies() {
        $_GET['type'] = 'post_replies';
        $_GET['post_id'] = 41;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());

        // test the object type is correct
        $this->assertTrue(is_array($output));
        foreach($output as $post) {
            $this->assertTrue($post instanceof stdClass);
            $this->assertEqual($post->protected, false);
        }

        $this->assertEqual(sizeof($output), 2);
        $this->assertEqual($output[0]->id, 131);
        $this->assertEqual($output[1]->id, 133);

        // test count
        $_GET['count'] = 1;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 1);

        $_GET['count'] = 2;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 2);

        // test paging
        $_GET['count'] = 1;
        $_GET['page'] = 1;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 1);
        $this->assertEqual($output[0]->id, 131);

        $_GET['count'] = 1;
        $_GET['page'] = 2;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 1);
        $this->assertEqual($output[0]->id, 133);

        // test order_by
        $_GET['order_by'] = 'location';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());

        $distance = $output[0]->reply_retweet_distance;
        foreach ($output as $post) {
            $this->assertTrue($post->reply_retweet_distance >= $distance);
            $distance = $post->reply_retweet_distance;
        }

        // test unit
        $_GET['post_id'] = 41;
        $_GET['unit'] = 'mi';
        $controller = new PostAPIController(true);
        $output_mi = json_decode($controller->go());
        $_GET['unit'] = 'km';
        $controller = new PostAPIController(true);
        $output_km = json_decode($controller->go());

        foreach ($output_km as $key=>$post) {
            $this->assertEqual($output_mi[$key]->reply_retweet_distance,
            round($output_km[$key]->reply_retweet_distance/1.609));
        }

        // test trim user
        unset($_GET['count'], $_GET['page']);
        $_GET['trim_user'] = true;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 2);

        foreach($output as $post) {
            $this->assertEqual(sizeof($post->user), 1);
        }

        // test sql injection
        $_GET = array('type' => 'post_replies');
        $prefix = Config::getInstance()->getValue('table_prefix');
        foreach(get_object_vars($controller) as $key => $value) {
            if ($key == 'type' || $key == 'app_session') continue;
            $_GET[$key] = "'; DROP TABLE " . $prefix . "posts--";
            $controller = new PostAPIController(true);
            $output = json_decode($controller->go());
            unset($_GET[$key]);
        }
        $installer_dao = DAOFactory::getDAO('InstallerDAO');
        $this->assertTrue(array_search($prefix . "posts", $installer_dao->getTables()) !== false);
    }

    public function testPostRepliesInRange() {
        $_GET['type'] = 'post_replies_in_range';
        $_GET['post_id'] = 41;
        $_GET['from'] = '2006-02-01 00:00:00';
        $_GET['until'] = '2006-03-02 00:59:59';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());

        // test the object type is correct
        $this->assertTrue(is_array($output));
        foreach($output as $post) {
            $this->assertTrue($post instanceof stdClass);
            $this->assertEqual($post->protected, false);
            /**
             * The following two assertions evaluate differently depending on whether your MySQL server supports
             * SET timezone statement in PDODAO::connect function
             */
            $this->assertTrue(strtotime($post->created_at) >= strtotime($_GET['from']));
            $this->assertTrue(strtotime($post->created_at) < strtotime($_GET['until']));
        }

        $this->assertEqual(sizeof($output), 2);
        $this->assertEqual($output[0]->id, 131);
        $this->assertEqual($output[1]->id, 133);

        // test order_by
        $_GET['order_by'] = 'location';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());

        $distance = $output[0]->reply_retweet_distance;
        foreach ($output as $post) {
            $this->assertTrue($post->reply_retweet_distance >= $distance);
            $distance = $post->reply_retweet_distance;
        }

        // test unit
        $_GET['post_id'] = 41;
        $_GET['unit'] = 'mi';
        $controller = new PostAPIController(true);
        $output_mi = json_decode($controller->go());
        $_GET['unit'] = 'km';
        $controller = new PostAPIController(true);
        $output_km = json_decode($controller->go());

        foreach ($output_km as $key=>$post) {
            $this->assertEqual($output_mi[$key]->reply_retweet_distance,
            round($output_km[$key]->reply_retweet_distance/1.609));
        }

        // test trim user
        unset($_GET['count'], $_GET['page']);
        $_GET['trim_user'] = true;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 2);

        foreach($output as $post) {
            $this->assertEqual(sizeof($post->user), 1);
        }

        // test sql injection
        $_GET = array('type' => 'post_replies');
        $prefix = Config::getInstance()->getValue('table_prefix');
        foreach(get_object_vars($controller) as $key => $value) {
            if ($key == 'type' || $key == 'app_session') continue;
            $_GET[$key] = "'; DROP TABLE " . $prefix . "posts--";
            $controller = new PostAPIController(true);
            $output = json_decode($controller->go());
            unset($_GET[$key]);
        }
        $installer_dao = DAOFactory::getDAO('InstallerDAO');
        $this->assertTrue(array_search($prefix . "posts", $installer_dao->getTables()) !== false);
    }

    public function testRelatedPosts() {
        $_GET['type'] = 'related_posts';
        $_GET['post_id'] = 41;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());

        // test the object type is correct
        $this->assertTrue(is_array($output));
        foreach($output as $post) {
            $this->assertTrue($post instanceof stdClass);
            $this->assertEqual($post->protected, false);
        }

        $this->assertEqual(sizeof($output), 2);

        foreach($output as $post) {
            $this->assertTrue($post->in_reply_to_post_id == 41 || @$post->retweeted_status->id == 41);
        }

        $_GET['post_id'] = 134;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());

        $this->assertEqual(sizeof($output), 3);

        foreach($output as $post) {
            $this->assertTrue($post->in_reply_to_post_id == 134 || @$post->retweeted_status->id == 134);
        }

        // test count
        $_GET['post_id'] = 134;
        $_GET['count'] = 1;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 1);

        $_GET['count'] = 2;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 2);

        $_GET['count'] = 3;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 3);

        // test paging
        $_GET['count'] = 1;
        $_GET['page'] = 1;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 1);
        $this->assertEqual($output[0]->id, 136);

        $_GET['count'] = 1;
        $_GET['page'] = 2;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 1);
        $this->assertEqual($output[0]->id, 137);

        $_GET['count'] = 1;
        $_GET['page'] = 3;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 1);
        $this->assertEqual($output[0]->id, 135);

        // test order_by
        unset($_GET['count'], $_GET['page']);
        $_GET['order_by'] = 'location';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());

        $distance = $output[0]->reply_retweet_distance;
        foreach ($output as $post) {
            $this->assertTrue($post->reply_retweet_distance >= $distance);
            $distance = $post->reply_retweet_distance;
        }

        // test trim user
        $_GET['trim_user'] = true;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 3);

        foreach($output as $post) {
            $this->assertEqual(sizeof($post->user), 1);
        }

        // test sql injection
        $_GET = array('type' => 'related_posts');
        $prefix = Config::getInstance()->getValue('table_prefix');
        foreach(get_object_vars($controller) as $key => $value) {
            if ($key == 'type' || $key == 'app_session') continue;
            $_GET[$key] = "'; DROP TABLE " . $prefix . "posts--";
            $controller = new PostAPIController(true);
            $output = json_decode($controller->go());
            unset($_GET[$key]);
        }
        $installer_dao = DAOFactory::getDAO('InstallerDAO');
        $this->assertTrue(array_search($prefix . "posts", $installer_dao->getTables()) !== false);
    }

    public function testUserPostsMostRepliedTo() {
        $_GET['type'] = 'user_posts_most_replied_to';
        $_GET['user_id'] = 22;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());

        // test the object type is correct
        $this->assertTrue(is_array($output));
        foreach($output as $post) {
            $this->assertTrue($post instanceof stdClass);
            $this->assertEqual($post->protected, false);
        }

        $this->assertEqual(sizeof($output), 3);

        $reply_count = $output[0]->thinkup->reply_count_cache;
        foreach ($output as $post) {
            $this->assertTrue($post->thinkup->reply_count_cache <= $reply_count);
            $reply_count = $post->thinkup->reply_count_cache;
        }

        // test count
        $_GET['count'] = 1;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 1);

        $_GET['count'] = 2;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 2);

        //test paging
        $_GET['count'] = 1;
        $_GET['page'] = 1;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 1);
        $this->assertEqual($output[0]->id, 134);

        $_GET['page'] = 2;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 1);
        $this->assertEqual($output[0]->id, 148);

        $_GET['page'] = 3;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 1);
        $this->assertEqual($output[0]->id, 146);

        // test trim user
        unset($_GET['count'], $_GET['page']);
        $_GET['trim_user'] = true;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 3);

        foreach($output as $post) {
            $this->assertEqual(sizeof($post->user), 1);
        }

        // test sql injection
        $_GET = array('type' => 'user_posts_most_replied_to');
        $prefix = Config::getInstance()->getValue('table_prefix');
        foreach(get_object_vars($controller) as $key => $value) {
            if ($key == 'type' || $key == 'app_session') continue;
            $_GET[$key] = "'; DROP TABLE " . $prefix . "posts--";
            $controller = new PostAPIController(true);
            $output = json_decode($controller->go());
            unset($_GET[$key]);
        }
        $installer_dao = DAOFactory::getDAO('InstallerDAO');
        $this->assertTrue(array_search($prefix . "posts", $installer_dao->getTables()) !== false);
    }

    public function testUserPostMostRetweeted() {
        $_GET['type'] = 'user_posts_most_retweeted';
        $_GET['user_id'] = 22;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 2);

        // test the object type is correct
        $this->assertTrue(is_array($output));
        foreach($output as $post) {
            $this->assertTrue($post instanceof stdClass);
            $this->assertEqual($post->protected, false);
        }

        foreach ($output as $post) {
            $this->assertEqual($post->user->id, 22);
            $this->assertTrue($post->thinkup->retweet_count_cache > 0);
        }

        // test order
        $retweet_count = $output[0]->thinkup->retweet_count_cache;
        foreach ($output as $post) {
            $this->assertTrue($post->thinkup->retweet_count_cache <= $retweet_count);
            $retweet_count = $post->thinkup->retweet_count_cache;
        }

        // test count
        $_GET['count'] = 1;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 1);

        $_GET['count'] = 2;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 2);

        //test paging
        $_GET['count'] = 1;
        $_GET['page'] = 1;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 1);
        $this->assertEqual($output[0]->id, 134);

        $_GET['page'] = 2;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 1);
        $this->assertEqual($output[0]->id, 148);

        // test trim user
        unset($_GET['order_by'], $_GET['direction'], $_GET['count'], $_GET['page']);
        $_GET['trim_user'] = true;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 2);

        foreach($output as $post) {
            $this->assertEqual(sizeof($post->user), 1);
        }

        // test sql injection
        $_GET = array('type' => 'user_posts_most_retweeted');
        $prefix = Config::getInstance()->getValue('table_prefix');
        foreach(get_object_vars($controller) as $key => $value) {
            if ($key == 'type' || $key == 'app_session') continue;
            $_GET[$key] = "'; DROP TABLE " . $prefix . "posts--";
            $controller = new PostAPIController(true);
            $output = json_decode($controller->go());
            unset($_GET[$key]);
        }
        $installer_dao = DAOFactory::getDAO('InstallerDAO');
        $this->assertTrue(array_search($prefix . "posts", $installer_dao->getTables()) !== false);
    }

    public function testUserPosts() {
        $_GET['type'] = 'user_posts';
        $_GET['user_id'] = 18;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 20);

        // test the object type is correct
        $this->assertTrue(is_array($output));
        foreach($output as $post) {
            $this->assertTrue($post instanceof stdClass);
            $this->assertEqual($post->protected, false);
        }

        // test all posts are from correct user
        foreach ($output as $post) {
            $this->assertEqual($post->user->id, 18);
        }

        // test count
        for ($count = 1; $count <= 20; $count++) {
            $_GET['count'] = $count;
            $controller = new PostAPIController(true);
            $output = json_decode($controller->go());
            $this->assertEqual(sizeof($output), $count);
        }

        unset($_GET['count']);

        // test order_by
        $_GET['order_by'] = 'date';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $date = strtotime($output[0]->created_at);
        foreach ($output as $post) {
            $this->assertTrue(strtotime($post->created_at) <= $date);
            $date = strtotime($post->created_at);
        }

        $_GET['order_by'] = 'date';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $date = strtotime($output[0]->created_at);
        foreach ($output as $post) {
            $this->assertTrue(strtotime($post->created_at) >= $date);
            $date = strtotime($post->created_at);
        }

        $_GET['order_by'] = 'source';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->source;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->source, $str) <= 0);
            $str = $post->source;
        }

        $_GET['order_by'] = 'source';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->source;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->source, $str) >= 0);
            $str = $post->source;
        }

        $_GET['order_by'] = 'follower_count';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $count = $output[0]->user->followers_count;
        foreach ($output as $post) {
            $this->assertTrue($post->user->followers_count <= $count);
            $count = $post->user->followers_count;
        }

        $_GET['order_by'] = 'follower_count';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $count = $output[0]->user->followers_count;
        foreach ($output as $post) {
            $this->assertTrue($post->user->followers_count >= $count);
            $count = $post->user->followers_count;
        }

        $_GET['order_by'] = 'post_text';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->text;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->text, $str) <= 0);
            $str = $post->text;
        }

        $_GET['order_by'] = 'post_text';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->text;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->text, $str) >= 0);
            $str = $post->text;
        }

        $_GET['order_by'] = 'author_username';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->user->screen_name;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->user->screen_name, $str) <= 0);
            $str = $post->user->screen_name;
        }

        $_GET['order_by'] = 'author_username';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->user->screen_name;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->user->screen_name, $str) >= 0);
            $str = $post->user->screen_name;
        }

        // test trim user
        unset($_GET['order_by'], $_GET['direction']);
        $_GET['trim_user'] = true;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 20);

        $this->assertEqual(sizeof($output[0]->user), 1);

        // test sql injection
        $_GET = array('type' => 'user_posts');
        $prefix = Config::getInstance()->getValue('table_prefix');
        foreach(get_object_vars($controller) as $key => $value) {
            if ($key == 'type' || $key == 'app_session') continue;
            $_GET[$key] = "'; DROP TABLE " . $prefix . "posts--";
            $controller = new PostAPIController(true);
            $output = json_decode($controller->go());
            unset($_GET[$key]);
        }
        $installer_dao = DAOFactory::getDAO('InstallerDAO');
        $this->assertTrue(array_search($prefix . "posts", $installer_dao->getTables()) !== false);
    }

    public function testUserPostsProtectedOnNetwork() {
        $_GET['type'] = 'user_posts';
        $_GET['user_id'] = 24;
        $controller = new PostAPIController(true);
        $output = $controller->go();
        $this->debug($output);
        $output = json_decode($output);
        $this->assertEqual(sizeof($output), 1);
        $this->assertEqual($output->error->type, "UserNotFoundException");
        $this->assertEqual($output->error->message, "The requested user data is not available.");
    }

    public function testUserPostsProtectedInThinkUp() {
        //user is public in users table, protected in instances table
        $_GET['type'] = 'user_posts';
        $_GET['user_id'] = 14;
        $controller = new PostAPIController(true);
        $output = $controller->go();
        $this->debug($output);
        $output = json_decode($output);
        $this->assertEqual(sizeof($output), 1);
        $this->assertEqual($output->error->type, "UserNotFoundException");
        $this->assertEqual($output->error->message, "The requested user data is not available.");
    }

    public function testUserMentions() {
        $_GET['type'] = 'user_mentions';
        $_GET['user_id'] = 18;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 2);

        // test the object type is correct
        $this->assertTrue(is_array($output));
        foreach($output as $post) {
            $this->assertTrue($post instanceof stdClass);
            $this->assertEqual($post->protected, false);
        }

        // test count
        $_GET['count'] = 1;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 1);

        $_GET['count'] = 2;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 2);

        //test paging
        $_GET['count'] = 1;
        $_GET['page'] = 1;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 1);
        $this->assertEqual($output[0]->id, 131);

        $_GET['page'] = 2;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 1);
        $this->assertEqual($output[0]->id, 133);

        unset($_GET['count'], $_GET['page']);

        // test order_by
        $_GET['order_by'] = 'date';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $date = strtotime($output[0]->created_at);
        foreach ($output as $post) {
            $this->assertTrue(strtotime($post->created_at) <= $date);
            $date = strtotime($post->created_at);
        }

        $_GET['order_by'] = 'date';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $date = strtotime($output[0]->created_at);
        foreach ($output as $post) {
            $this->assertTrue(strtotime($post->created_at) >= $date);
            $date = strtotime($post->created_at);
        }

        $_GET['order_by'] = 'source';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->source;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->source, $str) <= 0);
            $str = $post->source;
        }

        $_GET['order_by'] = 'source';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->source;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->source, $str) >= 0);
            $str = $post->source;
        }

        $_GET['order_by'] = 'follower_count';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $count = $output[0]->user->followers_count;
        foreach ($output as $post) {
            $this->debug("Count ".$post->user->followers_count . ' <= ' . $count);
            $this->assertTrue($post->user->followers_count <= $count);
            $count = $post->user->followers_count;
        }

        $_GET['order_by'] = 'follower_count';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $count = $output[0]->user->followers_count;
        foreach ($output as $post) {
            $this->debug($post->id . " - Count ".$post->user->followers_count . ' >= ' . $count);
            $this->assertTrue($post->user->followers_count >= $count);
            $count = $post->user->followers_count;
        }

        $_GET['order_by'] = 'post_text';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->text;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->text, $str) <= 0);
            $str = $post->text;
        }

        $_GET['order_by'] = 'post_text';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->text;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->text, $str) >= 0);
            $str = $post->text;
        }

        $_GET['order_by'] = 'author_username';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->user->screen_name;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->user->screen_name, $str) <= 0);
            $str = $post->user->screen_name;
        }

        $_GET['order_by'] = 'author_username';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->user->screen_name;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->user->screen_name, $str) >= 0);
            $str = $post->user->screen_name;
        }

        // test tweet entities
        $_GET['include_entities'] = true;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 2);

        foreach ($output as $post) {
            $this->assertEqual($post->entities->user_mentions[0]->name, 'Shutter Bug');
        }

        // test trim user
        unset($_GET['include_entities']);
        $_GET['trim_user'] = true;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 2);

        $this->assertEqual(sizeof($output[0]->user), 1);

        // test sql injection
        $_GET = array('type' => 'user_mentions');
        $prefix = Config::getInstance()->getValue('table_prefix');
        foreach(get_object_vars($controller) as $key => $value) {
            if ($key == 'type' || $key == 'app_session') continue;
            $_GET[$key] = "'; DROP TABLE " . $prefix . "posts--";
            $controller = new PostAPIController(true);
            $output = json_decode($controller->go());
            unset($_GET[$key]);
        }
        $installer_dao = DAOFactory::getDAO('InstallerDAO');
        $this->assertTrue(array_search($prefix . "posts", $installer_dao->getTables()) !== false);
    }

    public function testUserMentionsInRange() {
        $_GET['type'] = 'user_mentions_in_range';
        $_GET['user_id'] = 18;
        $_GET['from'] = '2006-03-01 00:00:00';
        $_GET['until'] = '2006-03-02 00:59:59';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());

        // test the object type is correct
        $this->assertTrue(is_array($output));
        foreach($output as $post) {
            $this->assertTrue($post instanceof stdClass);
            $this->assertEqual($post->protected, false);
            /**
             * The following two assertions evaluate differently depending on whether your MySQL server supports
             * SET timezone statement in PDODAO::connect function
             */
            $this->assertTrue(strtotime($post->created_at) >= strtotime($_GET['from']));
            $this->assertTrue(strtotime($post->created_at) < strtotime($_GET['until']));
        }

        // test order_by
        $_GET['order_by'] = 'date';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $date = strtotime($output[0]->created_at);
        foreach ($output as $post) {
            $this->assertTrue(strtotime($post->created_at) <= $date);
            $date = strtotime($post->created_at);
        }

        $_GET['order_by'] = 'date';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $date = strtotime($output[0]->created_at);
        foreach ($output as $post) {
            $this->assertTrue(strtotime($post->created_at) >= $date);
            $date = strtotime($post->created_at);
        }

        $_GET['order_by'] = 'source';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->source;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->source, $str) <= 0);
            $str = $post->source;
        }

        $_GET['order_by'] = 'source';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->source;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->source, $str) >= 0);
            $str = $post->source;
        }

        $_GET['order_by'] = 'follower_count';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $count = $output[0]->user->followers_count;
        foreach ($output as $post) {
            $this->debug("Count ".$post->user->followers_count . ' <= ' . $count);
            $this->assertTrue($post->user->followers_count <= $count);
            $count = $post->user->followers_count;
        }

        $_GET['order_by'] = 'follower_count';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $count = $output[0]->user->followers_count;
        foreach ($output as $post) {
            $this->debug($post->id . " - Count ".$post->user->followers_count . ' >= ' . $count);
            $this->assertTrue($post->user->followers_count >= $count);
            $count = $post->user->followers_count;
        }

        $_GET['order_by'] = 'post_text';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->text;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->text, $str) <= 0);
            $str = $post->text;
        }

        $_GET['order_by'] = 'post_text';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->text;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->text, $str) >= 0);
            $str = $post->text;
        }

        $_GET['order_by'] = 'author_username';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->user->screen_name;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->user->screen_name, $str) <= 0);
            $str = $post->user->screen_name;
        }

        $_GET['order_by'] = 'author_username';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->user->screen_name;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->user->screen_name, $str) >= 0);
            $str = $post->user->screen_name;
        }

        // test tweet entities
        $_GET['include_entities'] = true;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 2);

        // test trim user
        unset($_GET['include_entities']);
        $_GET['trim_user'] = true;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 2);
        $this->assertEqual(sizeof($output[0]->user), 1);

        // test sql injection
        $_GET = array('type' => 'user_mentions_in_range');
        $prefix = Config::getInstance()->getValue('table_prefix');
        foreach(get_object_vars($controller) as $key => $value) {
            if ($key == 'type' || $key == 'app_session') continue;
            $_GET[$key] = "'; DROP TABLE " . $prefix . "posts--";
            $controller = new PostAPIController(true);
            $output = json_decode($controller->go());
            unset($_GET[$key]);
        }
        $installer_dao = DAOFactory::getDAO('InstallerDAO');
        $this->assertTrue(array_search($prefix . "posts", $installer_dao->getTables()) !== false);

        // test posts contain a links object
        $_GET['type'] = 'user_mentions_in_range';
        $_GET['user_id'] = 18;
        $_GET['from'] = '2006-03-01 00:01:00';
        $_GET['until'] = '2006-03-01 00:23:01';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        foreach($output as $post) {
            $this->assertTrue($post->links instanceof stdClass);
        }
    }

    public function testUserMentionsProtectedOnNetwork() {
        $_GET['type'] = 'user_mentions';
        $_GET['user_id'] = 24;
        $controller = new PostAPIController(true);
        $output = $controller->go();
        $this->debug($output);
        $output = json_decode($output);
        $this->assertEqual(sizeof($output), 1);
        $this->assertEqual($output->error->type, "UserNotFoundException");
        $this->assertEqual($output->error->message, "The requested user data is not available.");
    }

    public function testUserMentionsProtectedInThinkUp() {
        $_GET['type'] = 'user_mentions';
        $_GET['user_id'] = 14;
        $controller = new PostAPIController(true);
        $output = $controller->go();
        $this->debug($output);
        $output = json_decode($output);
        $this->assertEqual(sizeof($output), 1);
        $this->assertEqual($output->error->type, "UserNotFoundException");
        $this->assertEqual($output->error->message, "The requested user data is not available.");
    }

    public function testUserReplies() {
        $_GET['type'] = 'user_replies';
        $_GET['user_id'] = 18;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());

        // test the object type is correct
        $this->assertTrue(is_array($output));
        foreach($output as $post) {
            $this->assertTrue($post instanceof stdClass);
            $this->assertEqual($post->protected, false);
            $this->assertEqual($post->in_reply_to_user_id, 18);
        }

        $this->assertEqual(sizeof($output), 2);

        // test count
        $_GET['count'] = 1;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 1);

        $_GET['count'] = 2;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 2);

        //test paging
        $_GET['count'] = 1;
        $_GET['page'] = 1;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 1);
        $this->assertEqual($output[0]->id, 131);

        $_GET['page'] = 2;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 1);
        $this->assertEqual($output[0]->id, 133);

        unset($_GET['count'], $_GET['page']);

        // test order_by
        $_GET['order_by'] = 'date';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $date = strtotime($output[0]->created_at);
        foreach ($output as $post) {
            $this->assertTrue(strtotime($post->created_at) <= $date);
            $date = strtotime($post->created_at);
        }

        $_GET['order_by'] = 'date';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $date = strtotime($output[0]->created_at);
        foreach ($output as $post) {
            $this->assertTrue(strtotime($post->created_at) >= $date);
            $date = strtotime($post->created_at);
        }

        $_GET['order_by'] = 'source';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->source;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->source, $str) <= 0);
            $str = $post->source;
        }

        $_GET['order_by'] = 'source';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->source;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->source, $str) >= 0);
            $str = $post->source;
        }

        $_GET['order_by'] = 'follower_count';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $count = $output[0]->user->followers_count;
        foreach ($output as $post) {
            $this->debug("Count ".$post->user->followers_count);
            $this->assertTrue($post->user->followers_count <= $count);
            $count = $post->user->followers_count;
        }

        $_GET['order_by'] = 'follower_count';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $count = $output[0]->user->followers_count;
        foreach ($output as $post) {
            $this->assertTrue($post->user->followers_count >= $count);
            $count = $post->user->followers_count;
        }

        $_GET['order_by'] = 'post_text';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->text;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->text, $str) <= 0);
            $str = $post->text;
        }

        $_GET['order_by'] = 'post_text';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->text;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->text, $str) >= 0);
            $str = $post->text;
        }

        $_GET['order_by'] = 'author_username';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->user->screen_name;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->user->screen_name, $str) <= 0);
            $str = $post->user->screen_name;
        }

        $_GET['order_by'] = 'author_username';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->user->screen_name;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->user->screen_name, $str) >= 0);
            $str = $post->user->screen_name;
        }

        // test sql injection
        $_GET = array('type' => 'user_replies');
        $prefix = Config::getInstance()->getValue('table_prefix');
        foreach(get_object_vars($controller) as $key => $value) {
            if ($key == 'type' || $key == 'app_session') continue;
            $_GET[$key] = "'; DROP TABLE " . $prefix . "posts--";
            $controller = new PostAPIController(true);
            $output = json_decode($controller->go());
            unset($_GET[$key]);
        }
        $installer_dao = DAOFactory::getDAO('InstallerDAO');
        $this->assertTrue(array_search($prefix . "posts", $installer_dao->getTables()) !== false);
    }

    public function testUserRepliesProtectedOnNetwork() {
        $_GET['type'] = 'user_replies';
        $_GET['user_id'] = 24;
        $controller = new PostAPIController(true);
        $output = $controller->go();
        $this->debug($output);
        $output = json_decode($output);
        $this->assertEqual(sizeof($output), 1);
        $this->assertEqual($output->error->type, "UserNotFoundException");
        $this->assertEqual($output->error->message, "The requested user data is not available.");
    }

    public function testUserRepliesProtectedInThinkUp() {
        $_GET['type'] = 'user_replies';
        $_GET['user_id'] = 14;
        $controller = new PostAPIController(true);
        $output = $controller->go();
        $this->debug($output);
        $output = json_decode($output);
        $this->assertEqual(sizeof($output), 1);
        $this->assertEqual($output->error->type, "UserNotFoundException");
        $this->assertEqual($output->error->message, "The requested user data is not available.");
    }

    public function testUserRepliesInRange() {
        $_GET['type'] = 'user_replies_in_range';
        $_GET['user_id'] = 18;
        $_GET['from'] = '2006-02-01 00:00:00';
        $_GET['until'] = '2006-03-02 00:59:59';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        // test the object type is correct
        $this->assertTrue(is_array($output));
        foreach($output as $post) {
            $this->assertTrue($post instanceof stdClass);
            $this->assertEqual($post->protected, false);
            $this->assertEqual($post->in_reply_to_user_id, 18);
            /**
             * The following two assertions evaluate differently depending on whether your MySQL server supports
             * SET timezone statement in PDODAO::connect function
             */
            $this->assertTrue(strtotime($post->created_at) >= strtotime($_GET['from']));
            $this->assertTrue(strtotime($post->created_at) < strtotime($_GET['until']));
        }

        $this->assertEqual(sizeof($output), 2);

        // test order_by
        $_GET['order_by'] = 'date';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $date = strtotime($output[0]->created_at);
        foreach ($output as $post) {
            $this->assertTrue(strtotime($post->created_at) <= $date);
            $date = strtotime($post->created_at);
        }

        $_GET['order_by'] = 'date';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $date = strtotime($output[0]->created_at);
        foreach ($output as $post) {
            $this->assertTrue(strtotime($post->created_at) >= $date);
            $date = strtotime($post->created_at);
        }

        $_GET['order_by'] = 'source';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->source;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->source, $str) <= 0);
            $str = $post->source;
        }

        $_GET['order_by'] = 'source';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->source;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->source, $str) >= 0);
            $str = $post->source;
        }

        $_GET['order_by'] = 'follower_count';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $count = $output[0]->user->followers_count;
        foreach ($output as $post) {
            $this->debug("Count ".$post->user->followers_count);
            $this->assertTrue($post->user->followers_count <= $count);
            $count = $post->user->followers_count;
        }

        $_GET['order_by'] = 'follower_count';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $count = $output[0]->user->followers_count;
        foreach ($output as $post) {
            $this->assertTrue($post->user->followers_count >= $count);
            $count = $post->user->followers_count;
        }

        $_GET['order_by'] = 'post_text';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->text;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->text, $str) <= 0);
            $str = $post->text;
        }

        $_GET['order_by'] = 'post_text';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->text;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->text, $str) >= 0);
            $str = $post->text;
        }

        $_GET['order_by'] = 'author_username';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->user->screen_name;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->user->screen_name, $str) <= 0);
            $str = $post->user->screen_name;
        }

        $_GET['order_by'] = 'author_username';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->user->screen_name;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->user->screen_name, $str) >= 0);
            $str = $post->user->screen_name;
        }

        // test sql injection
        $_GET = array('type' => 'user_replies');
        $prefix = Config::getInstance()->getValue('table_prefix');
        foreach(get_object_vars($controller) as $key => $value) {
            if ($key == 'type' || $key == 'app_session') continue;
            $_GET[$key] = "'; DROP TABLE " . $prefix . "posts--";
            $controller = new PostAPIController(true);
            $output = json_decode($controller->go());
            unset($_GET[$key]);
        }
        $installer_dao = DAOFactory::getDAO('InstallerDAO');
        $this->assertTrue(array_search($prefix . "posts", $installer_dao->getTables()) !== false);
    }

    public function testUserQuestions() {
        $_GET['type'] = 'user_questions';
        $_GET['user_id'] = 20;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());

        // test the object type is correct
        $this->assertTrue(is_array($output));
        foreach($output as $post) {
            $this->assertTrue($post instanceof stdClass);
            $this->assertEqual($post->protected, false);
            $this->assertEqual(preg_match('/\?/', $post->text), 1);
        }

        // test count
        $_GET['count'] = 1;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 1);

        $_GET['count'] = 2;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 2);

        //test paging
        $_GET['count'] = 1;
        $_GET['page'] = 1;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 1);
        $this->assertEqual($output[0]->id, 151);

        $_GET['page'] = 2;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 1);
        $this->assertEqual($output[0]->id, 150);

        unset($_GET['count'], $_GET['page']);

        // test order_by
        $_GET['order_by'] = 'date';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $date = strtotime($output[0]->created_at);
        foreach ($output as $post) {
            $this->assertTrue(strtotime($post->created_at) <= $date);
            $date = strtotime($post->created_at);
        }

        $_GET['order_by'] = 'date';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $date = strtotime($output[0]->created_at);
        foreach ($output as $post) {
            $this->assertTrue(strtotime($post->created_at) >= $date);
            $date = strtotime($post->created_at);
        }

        $_GET['order_by'] = 'source';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->source;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->source, $str) <= 0);
            $str = $post->source;
        }

        $_GET['order_by'] = 'source';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->source;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->source, $str) >= 0);
            $str = $post->source;
        }

        $_GET['order_by'] = 'follower_count';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $count = $output[0]->user->followers_count;
        foreach ($output as $post) {
            $this->assertTrue($post->user->followers_count <= $count);
            $count = $post->user->followers_count;
        }

        $_GET['order_by'] = 'follower_count';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $count = $output[0]->user->followers_count;
        foreach ($output as $post) {
            $this->assertTrue($post->user->followers_count >= $count);
            $count = $post->user->followers_count;
        }

        $_GET['order_by'] = 'post_text';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->text;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->text, $str) <= 0);
            $str = $post->text;
        }

        $_GET['order_by'] = 'post_text';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->text;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->text, $str) >= 0);
            $str = $post->text;
        }

        $_GET['order_by'] = 'author_username';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->user->screen_name;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->user->screen_name, $str) <= 0);
            $str = $post->user->screen_name;
        }

        $_GET['order_by'] = 'author_username';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->user->screen_name;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->user->screen_name, $str) >= 0);
            $str = $post->user->screen_name;
        }

        // test sql injection
        $_GET = array('type' => 'user_questions');
        $prefix = Config::getInstance()->getValue('table_prefix');
        foreach(get_object_vars($controller) as $key => $value) {
            if ($key == 'type' || $key == 'app_session') continue;
            $_GET[$key] = "'; DROP TABLE " . $prefix . "posts--";
            $controller = new PostAPIController(true);
            $output = json_decode($controller->go());
            unset($_GET[$key]);
        }
        $installer_dao = DAOFactory::getDAO('InstallerDAO');
        $this->assertTrue(array_search($prefix . "posts", $installer_dao->getTables()) !== false);
    }

    public function testUserQuestionsInRange() {
        $_GET['type'] = 'user_questions_in_range';
        $_GET['user_id'] = 20;
        $_GET['from'] = '2006-03-01 00:00:00';
        $_GET['until'] = '2006-03-02 00:59:59';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());

        // test the object type is correct
        $this->assertTrue(is_array($output));
        foreach($output as $post) {
            $this->assertTrue($post instanceof stdClass);
            $this->assertEqual($post->protected, false);
            $this->assertEqual(preg_match('/\?/', $post->text), 1);
            /**
             * The following two assertions evaluate differently depending on whether your MySQL server supports
             * SET timezone statement in PDODAO::connect function
             */
            $this->assertTrue(strtotime($post->created_at) >= strtotime($_GET['from']));
            $this->assertTrue(strtotime($post->created_at) < strtotime($_GET['until']));
        }

        // test order_by
        $_GET['order_by'] = 'date';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $date = strtotime($output[0]->created_at);
        foreach ($output as $post) {
            $this->assertTrue(strtotime($post->created_at) <= $date);
            $date = strtotime($post->created_at);
        }

        $_GET['order_by'] = 'date';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $date = strtotime($output[0]->created_at);
        foreach ($output as $post) {
            $this->assertTrue(strtotime($post->created_at) >= $date);
            $date = strtotime($post->created_at);
        }

        $_GET['order_by'] = 'source';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->source;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->source, $str) <= 0);
            $str = $post->source;
        }

        $_GET['order_by'] = 'source';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->source;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->source, $str) >= 0);
            $str = $post->source;
        }

        $_GET['order_by'] = 'follower_count';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $count = $output[0]->user->followers_count;
        foreach ($output as $post) {
            $this->assertTrue($post->user->followers_count <= $count);
            $count = $post->user->followers_count;
        }

        $_GET['order_by'] = 'follower_count';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $count = $output[0]->user->followers_count;
        foreach ($output as $post) {
            $this->assertTrue($post->user->followers_count >= $count);
            $count = $post->user->followers_count;
        }

        $_GET['order_by'] = 'post_text';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->text;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->text, $str) <= 0);
            $str = $post->text;
        }

        $_GET['order_by'] = 'post_text';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->text;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->text, $str) >= 0);
            $str = $post->text;
        }

        $_GET['order_by'] = 'author_username';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->user->screen_name;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->user->screen_name, $str) <= 0);
            $str = $post->user->screen_name;
        }

        $_GET['order_by'] = 'author_username';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->user->screen_name;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->user->screen_name, $str) >= 0);
            $str = $post->user->screen_name;
        }

        // test sql injection
        $_GET = array('type' => 'user_questions');
        $prefix = Config::getInstance()->getValue('table_prefix');
        foreach(get_object_vars($controller) as $key => $value) {
            if ($key == 'type' || $key == 'app_session') continue;
            $_GET[$key] = "'; DROP TABLE " . $prefix . "posts--";
            $controller = new PostAPIController(true);
            $output = json_decode($controller->go());
            unset($_GET[$key]);
        }
        $installer_dao = DAOFactory::getDAO('InstallerDAO');
        $this->assertTrue(array_search($prefix . "posts", $installer_dao->getTables()) !== false);
    }

    public function testUserQuestionsProtectedOnNetwork() {
        $_GET['type'] = 'user_questions';
        $_GET['user_id'] = 24;
        $controller = new PostAPIController(true);
        $output = $controller->go();
        $this->debug($output);
        $output = json_decode($output);
        $this->assertEqual(sizeof($output), 1);
        $this->assertEqual($output->error->type, "UserNotFoundException");
        $this->assertEqual($output->error->message, "The requested user data is not available.");
    }

    public function testUserQuestionsProtectedInThinkUp() {
        $_GET['type'] = 'user_questions';
        $_GET['user_id'] = 14;
        $controller = new PostAPIController(true);
        $output = $controller->go();
        $this->debug($output);
        $output = json_decode($output);
        $this->assertEqual(sizeof($output), 1);
        $this->assertEqual($output->error->type, "UserNotFoundException");
        $this->assertEqual($output->error->message, "The requested user data is not available.");
    }

    public function testUserPostsInRange() {
        $_GET['type'] = 'user_posts_in_range';
        $_GET['user_id'] = 18;
        $_GET['from'] = '2006-01-02 00:00:00';
        $_GET['until'] = '2006-01-02 00:59:59';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());

        // test the object type is correct
        $this->assertTrue(is_array($output));
        foreach($output as $post) {
            $this->assertTrue($post instanceof stdClass);
            $this->assertFalse($post->protected);
            /**
             * The following two assertions evaluate differently depending on whether your MySQL server supports
             * SET timezone statement in PDODAO::connect function
             */
            //$this->assertTrue(strtotime($post->created_at) >= strtotime($_GET['from']));
            //$this->assertTrue(strtotime($post->created_at) < strtotime($_GET['until']));
        }

        // test order_by
        $_GET['order_by'] = 'date';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $date = strtotime($output[0]->created_at);
        foreach ($output as $post) {
            $this->assertTrue(strtotime($post->created_at) <= $date);
            $date = strtotime($post->created_at);
        }

        $_GET['order_by'] = 'date';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $date = strtotime($output[0]->created_at);
        foreach ($output as $post) {
            $this->assertTrue(strtotime($post->created_at) >= $date);
            $date = strtotime($post->created_at);
        }

        $_GET['order_by'] = 'source';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->source;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->source, $str) <= 0);
            $str = $post->source;
        }

        $_GET['order_by'] = 'source';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->source;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->source, $str) >= 0);
            $str = $post->source;
        }

        $_GET['order_by'] = 'follower_count';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $count = $output[0]->user->followers_count;
        foreach ($output as $post) {
            $this->assertTrue($post->user->followers_count <= $count);
            $count = $post->user->followers_count;
        }

        $_GET['order_by'] = 'follower_count';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $count = $output[0]->user->followers_count;
        foreach ($output as $post) {
            $this->assertTrue($post->user->followers_count >= $count);
            $count = $post->user->followers_count;
        }

        $_GET['order_by'] = 'post_text';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->text;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->text, $str) <= 0);
            $str = $post->text;
        }

        $_GET['order_by'] = 'post_text';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->text;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->text, $str) >= 0);
            $str = $post->text;
        }

        $_GET['order_by'] = 'author_username';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->user->screen_name;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->user->screen_name, $str) <= 0);
            $str = $post->user->screen_name;
        }

        $_GET['order_by'] = 'author_username';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->user->screen_name;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->user->screen_name, $str) >= 0);
            $str = $post->user->screen_name;
        }

        // test sql injection
        $_GET = array('type' => 'user_posts_in_range');
        $prefix = Config::getInstance()->getValue('table_prefix');
        foreach(get_object_vars($controller) as $key => $value) {
            if ($key == 'type' || $key == 'app_session') continue;
            $_GET[$key] = "'; DROP TABLE " . $prefix . "posts--";
            $controller = new PostAPIController(true);
            $output = json_decode($controller->go());
            unset($_GET[$key]);
        }
        $installer_dao = DAOFactory::getDAO('InstallerDAO');
        $this->assertTrue(array_search($prefix . "posts", $installer_dao->getTables()) !== false);

        // test posts contain a links object
        $_GET['type'] = 'user_posts_in_range';
        $_GET['user_id'] = '19';
        $_GET['from'] = '2006-03-01 00:01:00';
        $_GET['until'] = '2006-03-01 00:01:01';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        foreach($output as $post) {
            $this->assertIsA($post, 'stdClass');
        }
    }

    public function testUserPostsInRangeProtectedOnNetwork() {
        $_GET['type'] = 'user_posts_in_range';
        $_GET['user_id'] = 24;
        $_GET['from'] = '2006-01-02 00:00:00';
        $_GET['until'] = '2006-01-02 00:59:59';
        $controller = new PostAPIController(true);
        $output = $controller->go();
        $this->debug($output);
        $output = json_decode($output);
        $this->assertEqual(sizeof($output), 1);
        $this->assertEqual($output->error->type, "UserNotFoundException");
        $this->assertEqual($output->error->message, "The requested user data is not available.");
    }

    public function testUserPostsInRangeProtectedInThinkUp() {
        $_GET['type'] = 'user_posts_in_range';
        $_GET['user_id'] = 14;
        $_GET['from'] = '2006-01-02 00:00:00';
        $_GET['until'] = '2006-01-02 00:59:59';
        $controller = new PostAPIController(true);
        $output = $controller->go();
        $this->debug($output);
        $output = json_decode($output);
        $this->assertEqual(sizeof($output), 1);
        $this->assertEqual($output->error->type, "UserNotFoundException");
        $this->assertEqual($output->error->message, "The requested user data is not available.");
    }

    public function testAPIDisabled() {
        // test option does not exist (default is true)
        $_GET['type'] = 'user_posts_in_range';
        $_GET['user_id'] = 18;
        $_GET['from'] = '2006-01-02 00:00:00';
        $_GET['until'] = '2006-01-02 00:59:59';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());

        $this->assertFalse(isset($output->error));

        // test option true
        $option_dao = DAOFactory::getDAO('OptionDAO');
        $option_dao->insertOption(OptionDAO::APP_OPTIONS, 'is_api_disabled', 'true');

        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());

        $this->assertEqual($output->error->type, 'APIDisabledException');

        // test option false
        $option_dao->updateOptionByName(OptionDAO::APP_OPTIONS, 'is_api_disabled', 'false');
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());

        $this->assertFalse(isset($output->error));
    }

    public function testKeywordPosts() {
        $_GET['type'] = 'keyword_posts';
        $_GET['keyword'] = 'first';
        $_GET['network'] = 'twitter';
        $controller = new PostAPIController();
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 20);

        // test the object type is correct
        $this->assertTrue(is_array($output));
        foreach($output as $post) {
            $this->assertTrue($post instanceof stdClass);
            $this->assertEqual($post->protected, false);
        }

        // test all posts are from correct user
        foreach ($output as $post) {
            $this->assertWithinMargin($post->user->id, 102,1);
        }

        //test page
        $_GET['page'] = 1;
        $_GET['order_by'] = 'post_id';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController();
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 20);
        $counter=301;
        foreach($output as $post) {
            $this->assertEqual($post->id, $counter);
            $counter = $counter+2;
        }

        $_GET['page'] = 2;
        $controller = new PostAPIController();
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 10);
        $counter=341;
        foreach($output as $post) {
            $this->assertEqual($post->id, $counter);
            $counter = $counter+2;
        }

        unset($_GET['page']);
        unset($_GET['order_by']);
        unset($_GET['direction']);

        //test #second
        $_GET['keyword'] = '#second';
        $controller = new PostAPIController();
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 20);

        // test the object type is correct
        $this->assertTrue(is_array($output));
        foreach($output as $post) {
            $this->assertTrue($post instanceof stdClass);
            $this->assertEqual($post->protected, false);
        }

        // test all posts are from correct user
        foreach ($output as $post) {
            $this->assertWithinMargin($post->user->id, 102,1);
        }

        // test count
        for ($count = 1; $count <= 20; $count++) {
            $_GET['count'] = $count;
            $controller = new PostAPIController(true);
            $output = json_decode($controller->go());
            $this->assertEqual(sizeof($output), $count);
        }
        unset($_GET['count']);

        // test order_by
        $_GET['order_by'] = 'date';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $date = strtotime($output[0]->created_at);
        foreach ($output as $post) {
            $this->assertTrue(strtotime($post->created_at) <= $date);
            $date = strtotime($post->created_at);
        }

        $_GET['order_by'] = 'date';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $date = strtotime($output[0]->created_at);
        foreach ($output as $post) {
            $this->assertTrue(strtotime($post->created_at) >= $date);
            $date = strtotime($post->created_at);
        }

        $_GET['order_by'] = 'source';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->source;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->source, $str) <= 0);
            $str = $post->source;
        }

        $_GET['order_by'] = 'source';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->source;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->source, $str) >= 0);
            $str = $post->source;
        }

        $_GET['order_by'] = 'post_text';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->text;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->text, $str) <= 0);
            $str = $post->text;
        }

        $_GET['order_by'] = 'post_text';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->text;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->text, $str) >= 0);
            $str = $post->text;
        }

        $_GET['order_by'] = 'author_username';
        $_GET['direction'] = 'DESC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->user->screen_name;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->user->screen_name, $str) <= 0);
            $str = $post->user->screen_name;
        }

        $_GET['order_by'] = 'author_username';
        $_GET['direction'] = 'ASC';
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $str = $output[0]->user->screen_name;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->user->screen_name, $str) >= 0);
            $str = $post->user->screen_name;
        }

        // test trim user
        unset($_GET['order_by'], $_GET['direction']);
        $_GET['trim_user'] = true;
        $controller = new PostAPIController(true);
        $output = json_decode($controller->go());
        $this->assertEqual(sizeof($output), 20);

        $this->assertEqual(sizeof($output[0]->user), 1);

        // test sql injection
        $_GET = array('type' => 'keyword_posts');
        $prefix = Config::getInstance()->getValue('table_prefix');
        foreach(get_object_vars($controller) as $key => $value) {
            if ($key == 'type' || $key == 'app_session') { continue; }
            $_GET[$key] = "'; DROP TABLE " . $prefix . "posts--";
            $controller = new PostAPIController(true);
            $output = json_decode($controller->go());
            unset($_GET[$key]);
        }
        $installer_dao = DAOFactory::getDAO('InstallerDAO');
        $this->assertTrue(array_search($prefix . "posts", $installer_dao->getTables()) !== false);
    }
}