<?php
/**
 *
 * ThinkUp/tests/TestOfPostMySQLDAO.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Mark Wilkie, Guillaume Boudreau
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
 * Test of PostMySQL DAO implementation
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Mark Wilkie, Guillaume Boudreau
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterPlugin.php';
require_once THINKUP_WEBAPP_PATH.'plugins/facebook/model/class.FacebookPlugin.php';

class TestOfPostMySQLDAO extends ThinkUpUnitTestCase {

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
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>1));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>13, 'user_name'=>'ev',
        'full_name'=>'Ev Williams', 'avatar'=>'avatar.jpg', 'is_protected'=>0, 'follower_count'=>10,
        'last_updated'=>'2005-01-01 13:01:00', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>18, 'user_name'=>'shutterbug',
        'full_name'=>'Shutter Bug', 'avatar'=>'avatar.jpg', 'is_protected'=>0, 'follower_count'=>10,
        'network'=>'twitter'));

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

        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>18, 'network_username'=>'shutterbug',
        'is_public'=>1, 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>19, 'network_username'=>'linkbaiter',
        'is_public'=>1, 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>23, 'network_username'=>'user3',
        'is_public'=>1, 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>24,
        'network_username'=>'notonpublictimeline', 'is_public'=>0, 'network'=>'twitter'));

        //Add straight text posts
        $counter = 1;
        while ($counter < 40) {
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
            if ($counter % 3 == 0) {
                $source = '<a href="http://twitter.com" rel="nofollow">Tweetie for Mac</a>';
            } else if ($counter % 3 == 1) {
                $source = '<a href="http://twitter.com/tweetbutton" rel="nofollow">Tweet Button</a>';
            } else {
                $source = 'web';
            }
            // issue #813 -build more of a range of retweet_count_cache and old_retweet_count_cache values for the
            // retweet testing.
            $builders[] = FixtureBuilder::build('posts', array('id'=>$counter, 'post_id'=>$counter,
            'author_user_id'=>'13', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
            'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post '.$counter,
            'source'=>$source, 'pub_date'=>'2006-01-01 00:'. $pseudo_minute.':00',
            'reply_count_cache'=>($counter==10)?0:rand(0, 4), 'is_protected'=>0,
            'retweet_count_cache'=>floor($counter/2), 'network'=>'twitter', 'in_reply_to_user_id'=>null,
            'old_retweet_count_cache' => floor($counter/3), 'in_rt_of_user_id' => null,
            'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
            $counter++;
        }

        //Add photo posts from Flickr
        $counter = 0;
        while ($counter < 40) {
            $post_id = $counter + 40;
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
            $builders[] = FixtureBuilder::build('posts', array('id'=>$post_id, 'post_id'=>$post_id,
            'author_user_id'=>18,
            'author_username'=>'shutterbug', 'author_fullname'=>'Shutter Bug', 'author_avatar'=>'avatar.jpg',
            'post_text'=>'This is image post '.$counter, 'source'=>'Flickr', 'in_reply_to_post_id'=>null,
            'in_retweet_of_post_id'=>null, 'is_protected'=>0,
            'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
            'pub_date'=>'2006-01-02 00:'.$pseudo_minute.':00', 'network'=>'twitter', 'is_geo_encoded'=>0));

            $builders[] = FixtureBuilder::build('links', array('url'=>'http://example.com/'.$counter,
            'expanded_url'=>'http://example.com/'.$counter.'.jpg', 'title'=>'', 'clicks'=>0, 'post_key'=>$post_id,
            'image_src'=>'image.png'));

            $counter++;
        }

        //Add link posts
        $counter = 0;
        while ($counter < 40) {
            $post_id = $counter + 80;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);
            $builders[] = FixtureBuilder::build('posts', array('id'=>$post_id, 'post_id'=>$post_id,
            'author_user_id'=>19,
            'author_username'=>'linkbaiter', 'author_fullname'=>'Link Baiter', 'is_geo_encoded'=>0,
            'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
            'post_text'=>'This is link post '.$counter, 'source'=>'web', 'pub_date'=>'2006-03-01 00:'.
            $pseudo_minute.':00', 'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'twitter'));

            $builders[] = FixtureBuilder::build('links', array('url'=>'http://example.com/'.$counter,
            'explanded_url'=>'http://example.com/'.$counter.'.html', 'title'=>'Link $counter', 'clicks'=>0,
            'post_key'=>$post_id, 'image_src'=>''));

            $counter++;
        }

        //Add mentions
        $counter = 0;
        while ($counter < 10) {
            $post_id = $counter + 120;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);
            if ( ($counter/2) == 0 ) {
                $builders[] = FixtureBuilder::build('posts', array('post_id'=>$post_id, 'author_user_id'=>20,
                'author_username'=>'user1', 'author_fullname'=>'User 1', 'in_reply_to_post_id'=>null,
                'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0, 'network'=>'twitter',
                'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
                'post_text'=>'Hey @ev and @jack thanks for founding Twitter post '.$counter,
                'pub_date'=>'2006-03-01 00:'.$pseudo_minute.':00', 'location'=>'New Delhi'));
            } else {
                $builders[] = FixtureBuilder::build('posts', array('post_id'=>$post_id, 'author_user_id'=>21,
                'author_username'=>'user2', 'author_fullname'=>'User 2', 'in_reply_to_post_id'=>null,
                'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0, 'network'=>'twitter',
                'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
                'post_text'=>'Hey @ev and @jack should fix Twitter - post '.$counter,
                'pub_date'=>'2006-03-01 00:'.$pseudo_minute.':00', 'place'=>'New Delhi'));
            }
            $counter++;
        }

        // Add some protected posts
        $counter = 0;
        while ($counter < 5) {
            $post_id = $counter + 20000;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);
            $builders[] = FixtureBuilder::build('posts', array('id'=>$post_id, 'post_id'=>$post_id,
            'author_user_id'=>123456,
            'author_username'=>'user_123456', 'author_fullname'=>'User 123456', 'is_geo_encoded'=>0,
            'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>1,
            'post_text'=>'This is link post '.$counter, 'source'=>'web', 'pub_date'=>'2006-03-01 00:'.
            $pseudo_minute.':00', 'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'twitter'));
            $counter++;
        }

        //Add replies to specific post
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>131, 'author_user_id'=>20,
        'author_username'=>'user1', 'author_fullname'=>'User 1', 'network'=>'twitter',
        'post_text'=>'@shutterbug Nice shot!', 'source'=>'web', 'pub_date'=>'2006-03-01 00:00:00',
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'in_reply_to_post_id'=>41,
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
        'location'=>'New Delhi, Delhi, India', 'reply_retweet_distance'=>0, 'is_geo_encoded'=>1));

        $builders[] = FixtureBuilder::build('posts', array('post_id'=>132, 'author_user_id'=>21,
        'author_username'=>'user2', 'author_fullname'=>'User 2', 'network'=>'twitter',
        'post_text'=>'@shutterbug Nice shot!', 'source'=>'web', 'pub_date'=>'2006-03-01 00:00:00',
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'in_reply_to_post_id'=>41,
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
        'location'=>'Chennai, Tamil Nadu, India', 'reply_retweet_distance'=>2000, 'is_geo_encoded'=>1));

        $builders[] = FixtureBuilder::build('posts', array('id'=>133, 'post_id'=>133, 'author_user_id'=>19,
        'author_username'=>'linkbaiter', 'author_fullname'=>'Link Baiter', 'network'=>'twitter',
        'post_text'=>'@shutterbug This is a link post reply http://example.com/', 'source'=>'web',
        'pub_date'=>'2006-03-03 00:00:00', 'reply_count_cache'=>0, 'retweet_count_cache'=>0,
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
        'in_reply_to_post_id'=>41, 'location'=>'Mumbai, Maharashtra, India', 'reply_retweet_distance'=>1500,
        'is_geo_encoded'=>1));

        $builders[] = FixtureBuilder::build('links', array('url'=>'http://example.com',
        'expanded_url'=>'http://example.com/expanded-link.html', 'title'=>'Link 1', 'clicks'=>0, 'post_key'=>133,
        'image_src'=>''));

        //Add retweets of a specific post
        //original post
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>134, 'author_user_id'=>22,
        'author_username'=>'quoter', 'author_fullname'=>'Quoter of Quotables', 'network'=>'twitter',
        'post_text'=>'Be liberal in what you accept and conservative in what you send', 'source'=>'web',
        'pub_date'=>'2006-03-01 00:00:00', 'reply_count_cache'=>0, 'retweet_count_cache'=>0,
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
        'location'=>'New Delhi, Delhi, India', 'geo'=>'28.635308,77.22496', 'is_geo_encoded'=>1));
        //retweet 1
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>135, 'author_user_id'=>20,
        'author_username'=>'user1', 'author_fullname'=>'User 1', 'network'=>'twitter',
        'post_text'=>'RT @quoter Be liberal in what you accept and conservative in what you send', 'source'=>'web',
        'pub_date'=>'2006-03-01 00:00:00', 'reply_count_cache'=>0, 'retweet_count_cache'=>0,
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
        'in_retweet_of_post_id'=>134, 'location'=>'Chennai, Tamil Nadu, India', 'geo'=>'13.060416,80.249634',
        'reply_retweet_distance'=>2000, 'is_geo_encoded'=>1, 'in_reply_to_post_id'=>null));
        //retweet 2
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>136, 'author_user_id'=>21,
        'author_username'=>'user2', 'author_fullname'=>'User 2', 'network'=>'twitter',
        'post_text'=>'RT @quoter Be liberal in what you accept and conservative in what you send',
        'source'=>'web', 'pub_date'=>'2006-03-01 00:00:00', 'reply_count_cache'=>0, 'retweet_count_cache'=>0,
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
        'in_retweet_of_post_id'=>134, 'location'=>'Dwarka, New Delhi, Delhi, India', 'geo'=>'28.635308,77.22496',
        'reply_retweet_distance'=>'0', 'is_geo_encoded'=>1));
        //retweet 3
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>137, 'author_user_id'=>19,
        'author_username'=>'linkbaiter', 'author_fullname'=>'Link Baiter', 'network'=>'twitter',
        'post_text'=>'RT @quoter Be liberal in what you accept and conservative in what you send',
        'source'=>'web', 'pub_date'=>'2006-03-01 00:00:00', 'reply_count_cache'=>0, 'retweet_count_cache'=>0,
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
        'in_retweet_of_post_id'=>134, 'location'=>'Mumbai, Maharashtra, India', 'geo'=>'19.017656,72.856178',
        'reply_retweet_distance'=>1500, 'is_geo_encoded'=>1));

        //Add reply back
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>138, 'author_user_id'=>18,
        'author_username'=>'shutterbug', 'author_fullname'=>'Shutterbug', 'network'=>'twitter',
        'post_text'=>'@user2 Thanks!', 'source'=>'web', 'pub_date'=>'2006-03-01 00:00:00',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'in_reply_to_user_id'=>21, 'in_reply_to_post_id'=>132));

        //Add user exchange
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>139, 'author_user_id'=>20,
        'author_username'=>'user1', 'author_fullname'=>'User 1', 'network'=>'twitter',
        'post_text'=>'@ev When will Twitter have a business model?', 'source'=>'web',
        'pub_date'=>'2006-03-01 00:00:00', 'reply_count_cache'=>0, 'retweet_count_cache'=>0,
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id'=>13, 'is_protected'=>0 ));

        $builders[] = FixtureBuilder::build('posts', array('post_id'=>140, 'author_user_id'=>13,
        'author_username'=>'ev', 'author_fullname'=>'Ev Williams', 'network'=>'twitter',
        'post_text'=>'@user1 Soon...', 'source'=>'web', 'pub_date'=>'2006-03-01 00:00:00',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'in_reply_to_user_id'=>20, 'in_reply_to_post_id'=>139));

        //Add posts replying to post not in the system
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>141, 'author_user_id'=>23,
        'author_username'=>'user3', 'author_fullname'=>'User 3', 'network'=>'twitter',
        'post_text'=>'@user4 I\'m replying to a post not in the TT db', 'source'=>'web',
        'pub_date'=>'2006-03-01 00:00:00', 'reply_count_cache'=>0, 'retweet_count_cache'=>0,
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_user_id'=>20, 'in_reply_to_post_id'=>250));

        $builders[] = FixtureBuilder::build('posts', array('post_id'=>142, 'author_user_id'=>23,
        'author_username'=>'user3', 'author_fullname'=>'User 3', 'network'=>'twitter',
        'post_text'=>'@user4 I\'m replying to another post not in the TT db', 'source'=>'web',
        'pub_date'=>'2006-03-01 00:00:00', 'reply_count_cache'=>0, 'retweet_count_cache'=>0,
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
        'in_reply_to_user_id'=>20, 'in_reply_to_post_id'=>251));

        //Add post by instance not on public timeline
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>143, 'author_user_id'=>24,
        'author_username'=>'notonpublictimeline', 'author_fullname'=>'Not on public timeline',
        'network'=>'twitter', 'post_text'=>'This post should not be on the public timeline',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
        'source'=>'web', 'pub_date'=>'2006-03-01 00:00:00'));

        //Add replies to specific post
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>144, 'author_user_id'=>20,
        'author_username'=>'user1', 'author_fullname'=>'User 1', 'network'=>'twitter',
        'post_text'=>'@quoter Indeed, Jon Postel.', 'source'=>'web', 'pub_date'=>'2006-03-01 00:00:00',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'is_reply_by_friend'=>1, 'in_reply_to_post_id'=>134,
        'network'=>'twitter', 'location'=>'New Delhi, Delhi, India', 'geo'=>'28.635308,77.22496',
        'is_geo_encoded'=>1));

        //Add message to specific user
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>145, 'author_user_id'=>20,
        'author_username'=>'user1', 'author_fullname'=>'User 1', 'network'=>'twitter',
        'post_text'=>'@user3, you are rad.', 'source'=>'web', 'pub_date'=>'2006-03-01 00:00:00',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'twitter',
        'in_reply_to_user_id' =>'23', 'in_reply_to_post_id' => null));

        //Add another message to specific user with a couple of links
        $post_builder = FixtureBuilder::build('posts', array('post_id'=>'146', 'author_user_id'=>'20',
        'author_username'=>'user1', 'author_fullname'=>'User 1', 'network'=>'twitter',
        'post_text'=>'@user3, you are rad.', 'source'=>'web', 'pub_date'=>'2006-03-01 00:00:00',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'twitter',
        'in_reply_to_user_id' =>'23', 'in_reply_to_post_id' => null));

        array_push($builders, $post_builder);
        $post_key = $post_builder->columns['last_insert_id'];

        $builders[] = FixtureBuilder::build('links', array('post_key'=>$post_key, 'url'=>'http://alink1.com'));
        $builders[] = FixtureBuilder::build('links', array('post_key'=>$post_key, 'url'=>'http://alink2.com'));

        // Add a foursquare checkin
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>'147', 'author_user_id'=>'20',
        'author_username'=>'user1', 'author_fullname'=>'User 1', 'network'=>'foursquare',
        'post_text'=>'I just checked in', 'source'=>'', 'pub_date'=>'2011-02-21 09:50:00', 'location'=>'England',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'place'=>'The Park', 'place_id'=>'12345a',
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'foursquare',
        'in_reply_to_user_id' =>'23', 'in_reply_to_post_id' => null,
        'geo'=>'52.477192843264,-1.484333726346'));

        $builders[] = FixtureBuilder::build('posts', array('post_id'=>'149', 'author_user_id'=>'21',
        'author_username'=>'user1', 'author_fullname'=>'User 1', 'network'=>'foursquare',
        'post_text'=>'I just checked in again', 'source'=>'', 'pub_date'=>'2011-02-21 22:00:00', 'location'=>'England',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'place'=>'The Garage', 'place_id'=>'12346',
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'foursquare',
        'in_reply_to_user_id' =>'23', 'in_reply_to_post_id' => null,
        'geo'=>'52.477192843264,-1.484333726346'));

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

    /**
     * Test constructor
     */
    public function testConstructor() {
        $dao = new PostMySQLDAO();
        $this->assertTrue(isset($dao));
    }

    public function testGetAllQuestionPosts() {
        $builders = array();
        //Add a question
        $post_builder = FixtureBuilder::build('posts', array('author_user_id'=>'13', 'author_username'=>'ev',
        'post_text'=>'I need a new cell phone. Not this http://bit.ly/blah or this http://bit.ly/blah2 '.
        'What should I buy?', 'network'=>'twitter', 'in_reply_to_post_id'=>0,
        'pub_date'=>'-1d'));
        array_push($builders, $post_builder);
        $post_key = $post_builder->columns['last_insert_id'];

        $builders[] = FixtureBuilder::build('links', array('post_key'=>$post_key, 'url'=>'http://bit.ly/blah'));
        $builders[] = FixtureBuilder::build('links', array('post_key'=>$post_key, 'url'=>'http://bit.ly/blah2'));

        $dao = new PostMySQLDAO();
        $questions = $dao->getAllQuestionPosts('13', 'twitter', '10');

        $this->debug('Questions: ' . $questions);

        $this->assertEqual(sizeof($questions), 1);
        $this->assertEqual($questions[0]->post_text,
        'I need a new cell phone. Not this http://bit.ly/blah or this http://bit.ly/blah2 What should I buy?' );
        $this->assertEqual(sizeof($questions[0]->links), 2 );
        $this->assertEqual($questions[0]->links[0]->url, 'http://bit.ly/blah' );
        $this->assertEqual($questions[0]->links[1]->url, 'http://bit.ly/blah2' );

        //Add another question
        $builder[] = FixtureBuilder::build('posts', array('author_user_id'=>13, 'author_username'=>'ev',
        'post_text'=>'Best sushi in NY? downtown', 'network'=>'twitter', 'in_reply_to_post_id'=>0, 'pub_date'=>'-2d'));
        $questions = $dao->getAllQuestionPosts(13, 'twitter', 10);
        $this->assertEqual(sizeof($questions), 2);
        $this->assertEqual($questions[1]->post_text, 'Best sushi in NY? downtown' );
        $this->assertEqual($questions[0]->post_text,
        'I need a new cell phone. Not this http://bit.ly/blah or this http://bit.ly/blah2 What should I buy?' );

        //Messages with a question mark in between two characters (e.g. URLs) aren't necessarily questions
        $builder[] = FixtureBuilder::build('posts', array('author_user_id'=>13, 'author_username'=>'ev',
        'post_text'=>'Love this video: http://www.youtube.com/watch?v=PQu-zrE-k5s', 'network'=>'twitter',
        'in_reply_to_post_id'=>0, 'pub_date'=>'-3d'));
        $questions = $dao->getAllQuestionPosts(13, 'twitter', 10);
        $this->assertEqual(sizeof($questions), 2);

        // test paging
        $questions = $dao->getAllQuestionPosts(13, 'twitter', $count = 1, $page = 1);
        $this->assertEqual($questions[0]->post_text,
        'I need a new cell phone. Not this http://bit.ly/blah or this http://bit.ly/blah2 What should I buy?');

        $questions = $dao->getAllQuestionPosts(13, 'twitter', $count = 1, $page = 2);
        $this->assertEqual($questions[0]->post_text, 'Best sushi in NY? downtown');

        // test count
        $questions = $dao->getAllQuestionPosts(13, 'twitter', $count = 1, $page = 1);
        $this->assertEqual(sizeof($questions), 1);

        $questions = $dao->getAllQuestionPosts(13, 'twitter', $count = 2, $page = 1);
        $this->assertEqual(sizeof($questions), 2);

        // test default order
        $questions = $dao->getAllQuestionPosts(13, 'twitter', $count = 1, $page = 1, "';-- SELECT");
        $this->assertEqual($questions[0]->post_text,
        'I need a new cell phone. Not this http://bit.ly/blah or this http://bit.ly/blah2 What should I buy?');
    }

    /**
     * Test getAllQuestionPostosInRange
     */
    public function testgetAllQuestionPostsInRange() {
        $builders = array();
        //Add a question
        $post_builder = FixtureBuilder::build('posts', array('author_user_id'=>'13', 'author_username'=>'ev',
        'post_text'=>'I need a new cell phone. Not this http://bit.ly/blah or this http://bit.ly/blah2 '.
        'What should I buy?', 'network'=>'twitter', 'in_reply_to_post_id'=>0,
        'pub_date'=>'2006-02-01 00:05:00'));

        array_push($builders, $post_builder);
        $post_key = $post_builder->columns['last_insert_id'];

        $builders[] = FixtureBuilder::build('links', array('post_key'=>$post_key, 'url'=>'http://bit.ly/blah'));
        $builders[] = FixtureBuilder::build('links', array('post_key'=>$post_key, 'url'=>'http://bit.ly/blah2'));

        $dao = new PostMySQLDAO();
        $questions = $dao->getAllQuestionPostsInRange('13', 'twitter', '10', $from = '2006-02-01 00:04:00',
        $until= '2006-02-02 00:10:00');

        $this->debug('Questions: ' . $questions);

        $this->assertEqual(sizeof($questions), 1);
        $this->assertEqual($questions[0]->post_text,
        'I need a new cell phone. Not this http://bit.ly/blah or this http://bit.ly/blah2 What should I buy?' );
        $this->assertEqual(sizeof($questions[0]->links), 2 );
        $this->assertEqual($questions[0]->links[0]->url, 'http://bit.ly/blah' );
        $this->assertEqual($questions[0]->links[1]->url, 'http://bit.ly/blah2' );

        //Add another question
        $builder[] = FixtureBuilder::build('posts', array('author_user_id'=>13, 'author_username'=>'ev',
        'post_text'=>'Best sushi in NY? downtown', 'network'=>'twitter', 'in_reply_to_post_id'=>0,
	'pub_date'=>'2006-02-01 00:06:00'));

        $questions = $dao->getAllQuestionPostsInRange('13', 'twitter', '10', $from = '2006-02-01 00:04:00',
        $until= '2006-02-01 00:10:00');
        $this->assertEqual(sizeof($questions), 2);
        $this->assertEqual($questions[0]->post_text, 'Best sushi in NY? downtown' );
        $this->assertEqual($questions[1]->post_text,
        'I need a new cell phone. Not this http://bit.ly/blah or this http://bit.ly/blah2 What should I buy?' );

        //Messages with a question mark in between two characters (e.g. URLs) aren't necessarily questions
        $builder[] = FixtureBuilder::build('posts', array('author_user_id'=>13, 'author_username'=>'ev',
        'post_text'=>'Love this video: http://www.youtube.com/watch?v=PQu-zrE-k5s', 'network'=>'twitter',
        'in_reply_to_post_id'=>0, 'pub_date'=>'2006-02-01 00:07:00'));
        $questions = $dao->getAllQuestionPostsInRange('13', 'twitter', '10', $from = '2006-02-01 00:04:00',
        $until= '2006-02-01 00:10:00');
        $this->assertEqual(sizeof($questions), 2);

        // test ascending order
        $posts = $dao->getAllQuestionPostsInRange('13', 'twitter', '10', $from = '2006-02-01 00:01:00',
        $until = '2006-02-01 00:10:00',$page=1, $order_by = 'pub_date', $direction = 'ASC');
        $this->assertEqual(sizeof($posts), 2);
        foreach($posts as $post) {
            $this->assertTrue(strtotime($post->pub_date) >= $date);
            $date = strtotime($post->pub_date);
        }


        // test range with no posts
        $questions = $dao->getAllQuestionPostsInRange('13', 'twitter', '10', $from = '2006-02-01 00:10:00',
        $until= '2006-02-01 00:15:00');
        $this->assertEqual(sizeof($questions), 0);

        // test from greater than until
        $questions = $dao->getAllQuestionPostsInRange('13', 'twitter', '10', $from = '2006-02-01 00:10:00',
        $until= '2006-01-01 00:15:00');
        $this->assertEqual(sizeof($questions), 0);
    }

    /**
     * Test getOrphanReplies
     */
    public function testGetOrphanReplies() {
        $dao = new PostMySQLDAO();
        $replies = $dao ->getOrphanReplies('ev', 10, 'twitter');
        $this->assertEqual(sizeof($replies), 10);
        $this->assertEqual($replies[0]->post_text, "Hey @ev and @jack should fix Twitter - post 9");

        $replies = $dao ->getOrphanReplies('jack', 10, 'twitter');
        $this->assertEqual(sizeof($replies), 10);
        $this->assertEqual($replies[0]->post_text, "Hey @ev and @jack should fix Twitter - post 9");

        //test paging
        $replies = $dao ->getOrphanReplies('ev', 1, 'twitter', $page = 1);
        $this->assertEqual($replies[0]->post_text, "Hey @ev and @jack should fix Twitter - post 9");

        $replies = $dao ->getOrphanReplies('ev', 1, 'twitter', $page = 2);
        $this->assertEqual($replies[0]->post_text, "Hey @ev and @jack should fix Twitter - post 8");
    }

    /**
     * Test getStrayRepliedToPosts
     */
    public function testGetStrayRepliedToPosts() {
        $dao = new PostMySQLDAO();
        $posts = $dao->getStrayRepliedToPosts(23, 'twitter');
        $this->debug(Utils::varDumpToString($posts));
        $this->assertEqual(sizeof($posts), 2);
        $this->assertEqual($posts[0]["in_reply_to_post_id"], 250);
        $this->assertEqual($posts[1]["in_reply_to_post_id"], 251);
    }

    /**
     * Test getMostRepliedToPosts
     */
    public function testGetMostRepliedToPosts() {
        $dao = new PostMySQLDAO();
        $posts = $dao->getMostRepliedToPosts(13, 'twitter', 10);
        $prev_count = $posts[0]->reply_count_cache;
        foreach ($posts as $post) {
            $this->assertTrue($post->reply_count_cache <= $prev_count, "previous count ".$prev_count.
            " should be less than or equal to this post's count of ".$post->reply_count_cache);
            $prev_count = $post->reply_count_cache;
        }

        // test paging
        $posts = $dao->getMostRepliedToPosts(13, 'twitter', $count = 1, $page = 1);
        $prev_count = $posts[0]->reply_count_cache;
        for ($i = 2; $i <= 10; $i++) {
            $posts = $dao->getMostRepliedToPosts(13, 'twitter', $count = 1, $page = $i);
            $this->assertTrue($posts[0]->reply_count_cache <= $prev_count, "previous count ".$prev_count.
            " should be less than or equal to this post's count of ".$posts[0]->reply_count_cache);
            $prev_count = $posts[0]->reply_count_cache;
        }

        // test count
        for ($i = 2; $i <= 10; $i++) {
            $posts = $dao->getMostRepliedToPosts(13, 'twitter', $count = $i, $page = 1);
            $this->assertTrue(count($posts) == $i);
        }
    }

    /**
     * Test getMostRetweetedPosts
     */
    public function testGetMostRetweetedPosts() {
        $dao = new PostMySQLDAO();
        $posts = $dao->getMostRetweetedPosts(13, 'twitter', 10);
        // track the sum of retweet_count_cache and old_retweet_count_cache, which is the criteria
        // by which this query should have been sorted.
        // flip the logic in this first test clause as per issue #813.
        $prev_count = $posts[0]->retweet_count_cache + $posts[0]->old_retweet_count_cache;
        foreach ($posts as $post) {
            $this->assertTrue($post->retweet_count_cache + $post->old_retweet_count_cache <= $prev_count);
            $prev_count = $post->retweet_count_cache + $post->old_retweet_count_cache;
        }

        // test paging
        $posts = $dao->getMostRetweetedPosts(13, 'twitter', $count = 1, $page = 1);
        $prev_count = $posts[0]->retweet_count_cache + $posts[0]->old_retweet_count_cache;
        for ($i = 2; $i <= 10; $i++) {
            $posts = $dao->getMostRetweetedPosts(13, 'twitter', $count = 1, $page = $i);
            $this->assertTrue($posts[0]->retweet_count_cache + $posts[0]->old_retweet_count_cache <= $prev_count);
            $prev_count = $posts[0]->retweet_count_cache + $posts[0]->old_retweet_count_cache;
        }

        // test count
        for ($i = 2; $i <= 10; $i++) {
            $posts = $dao->getMostRetweetedPosts(13, 'twitter', $count = $i, $page = 1);
            $this->assertTrue(count($posts) == $i);
        }
    }

    /**
     * Test getAllReplies
     */
    public function testGetAllReplies() {
        $dao = new PostMySQLDAO();
        $replies = $dao->getAllReplies('13', 'twitter', 10);
        $this->assertEqual(sizeof($replies), 1);
        $this->assertEqual($replies[0]->post_text, "@ev When will Twitter have a business model?");

        // test paging
        $replies = $dao->getAllReplies('13', 'twitter', $count = 1, $page = 1);
        $this->assertEqual(sizeof($replies), 1);
        $this->assertEqual($replies[0]->post_text, "@ev When will Twitter have a business model?");

        // this query doesn't have a second page, so this should return nothing
        $replies = $dao->getAllReplies('13', 'twitter', $count = 1, $page = 2);
        $this->assertEqual(sizeof($replies), 0);

        // test count
        $replies = $dao->getAllReplies('13', 'twitter', $count = 0, $page = 1);
        $this->assertEqual(sizeof($replies), 0);

        $replies = $dao->getAllReplies('13', 'twitter', $count = 1, $page = 1);
        $this->assertEqual(sizeof($replies), 1);

        $replies = $dao->getAllReplies('18', 'twitter', 10);
        if (sizeof($replies)>0) {
            print_r($replies);
        }
        $this->assertEqual(sizeof($replies), 0);

        // test default order_by
        $replies = $dao->getAllReplies('13', 'twitter', 10, 1, "';-- SELECT");
        $this->assertEqual(sizeof($replies), 1);
        $this->assertEqual($replies[0]->post_text, "@ev When will Twitter have a business model?");
    }
    /**
     * Test getAllRepliesInRange
     */
    public function testgetAllRepliesInRange() {
        $dao = new PostMySQLDAO();
        $posts = $dao->getAllRepliesInRange(23, 'twitter', 200, $from = '2006-02-28 23:50:00',
        $until = '2006-03-02 00:30:59', $order_by="pub_date", $direction="DESC");
        $this->assertEqual(sizeof($posts),2);
        // test date ordering and time range check
        $date = strtotime($posts[0]->pub_date);
        foreach($posts as $post) {
            $this->assertEqual($post->in_reply_to_user_id, 23);
            $this->assertTrue(strtotime($post->pub_date) >= strtotime('2000-02-28 23:50:00'));
            $this->assertTrue(strtotime($post->pub_date) < strtotime('2010-03-01 00:30:59'));
            $this->assertTrue(strtotime($post->pub_date) <= $date);
            $date = strtotime($post->pub_date);
        }

        // test ascending order
        $posts = $dao->getAllRepliesInRange(13, 'twitter', 500,$from = '2006-02-28 23:50:00',
        $until = '2006-03-01 00:30:59',  $order_by="pub_date", $direction="ASC");

        $date = strtotime($posts[0]->pub_date);
        foreach($posts as $post) {
            $this->assertTrue(strtotime($post->pub_date) >= $date);
            $date = strtotime($post->pub_date);
        }

        // test filter protected posts
        $posts = $dao->getAllRepliesInRange(13, 'twitter', 500, $from = '2006-02-28 23:50:00',
        $until = '2006-03-01 00:30:59',  $order_by="pub_date", $direction="DESC", $iterator=false,
        $is_public = true);

        foreach($posts as $post) {
            $this->assertEqual($post->is_protected, false);
        }

        // test range with no posts
        $posts = $dao->getAllRepliesInRange(13, 'twitter', 500, $from = '2006-02-25 23:50:00',
        $until = '2006-02-28 23:50:00',  $order_by="pub_date", $direction="DESC", $iterator=false,
        $is_public = false);

        $this->assertEqual(sizeof($posts), 0);

        // test from greater than until
        $posts = $dao->getAllRepliesInRange(13, 'twitter', 500, $from = '2006-03-01 23:50:00',
        $until = '2006-02-28 23:50:00',  $order_by="pub_date", $direction="DESC", $iterator=false,
        $is_public = false);;

        $this->assertEqual(sizeof($posts), 0);
    }

    /**
     * Test getAllMentions
     */
    public function testGetAllMentions() {
        $dao = new PostMySQLDAO();
        $mentions = $dao->getAllMentions("ev", 10, 'twitter');
        $this->assertTrue(sizeof($mentions), 10);
        $this->assertEqual($mentions[0]->post_text, "Hey @ev and @jack should fix Twitter - post 9");

        $mentions = $dao->getAllMentions("jack", 10, 'twitter');
        $this->assertTrue(sizeof($mentions), 10);
        $this->assertEqual($mentions[0]->post_text, "Hey @ev and @jack should fix Twitter - post 9");

        // test paging
        $mentions = $dao->getAllMentions("jack", $count = 1, 'twitter', $page = 1);
        $this->assertEqual($mentions[0]->post_text, 'Hey @ev and @jack should fix Twitter - post 9');

        $mentions = $dao->getAllMentions("jack", $count = 1, 'twitter', $page = 2);
        $this->assertEqual($mentions[0]->post_text, 'Hey @ev and @jack should fix Twitter - post 8');

        $mentions = $dao->getAllMentions("jack", $count = 1, 'twitter', $page = 3);
        $this->assertEqual($mentions[0]->post_text, 'Hey @ev and @jack should fix Twitter - post 7');

        // test count
        $mentions = $dao->getAllMentions("jack", $count = 1, 'twitter', $page = 1);
        $this->assertEqual(count($mentions), 1);

        $mentions = $dao->getAllMentions("jack", $count = 2, 'twitter', $page = 1);
        $this->assertEqual(count($mentions), 2);

        $mentions = $dao->getAllMentions("jack", $count = 3, 'twitter', $page = 1);
        $this->assertEqual(count($mentions), 3);

        // insert a retweet
        $builders[] = FixtureBuilder::build('posts', array('author_user_id'=>20,
                'author_username'=>'user1', 'author_fullname'=>'User 1', 'in_reply_to_post_id'=>null,
                'in_retweet_of_post_id'=>121, 'is_geo_encoded'=>0, 'network'=>'twitter',
                'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => 13,
                'post_text'=>'Hey @ev and @jack thanks for founding Twitter retweet 1',
                'pub_date'=>'2006-03-01 00:01:00', 'location'=>'New Delhi'));

        // test no retweets
        $mentions = $dao->getAllMentions("jack", 10, 'twitter', $page = 1, $public = false,
        $include_rts = false);
        $this->assertEqual(sizeof($mentions), 10);

        foreach ($mentions as $mention) {
            $this->assertTrue($mention->in_retweet_of_post_id == null, "Retweet included in a call to getAllMentions
                that specifies no retweets.");
        }

        // test default order_by
        $mentions = $dao->getAllMentions("jack", $count = 1, 'twitter', $page = 1, false, true, "';-- SELECT");
        $this->assertEqual($mentions[0]->post_text, 'Hey @ev and @jack should fix Twitter - post 9');
    }

    /**
     * Test getAllMentionsInRange
     */
    public function testgetAllMentionsInRange() {
        $dao = new PostMySQLDAO();
        $mentions = $dao->getAllMentionsInRange("ev", $count = 200, $network = 'twitter', $from = '2006-03-01 00:00:00',
        $until = '2006-03-01 01:00:00', $page=1, $public=false, $include_rts = true, $order_by="pub_date",
        $direction="DESC");

        $this->assertEqual($mentions[0]->post_text, "Hey @ev and @jack should fix Twitter - post 9");
        $this->assertEqual($mentions[2]->post_text, "Hey @ev and @jack should fix Twitter - post 7");

        $mentions = $dao->getAllMentionsInRange("jack", $count = 200, $network = 'twitter',
        $from = '2006-03-01 00:00:00',
        $until = '2006-03-01 01:00:00', $page=1, $public=false, $include_rts = true, $order_by="pub_date",
        $direction="DESC");

        $this->assertEqual(sizeof($mentions), 10);


        // test ascending order
        $posts = $dao->getAllMentionsInRange("jack", $count = 200, $network = 'twitter', $from = '2006-02-28 23:59:00',
        $until = '2006-03-01 01:00:00', $page=1, $public=false, $include_rts = true, $order_by="pub_date",
        $direction="ASC");

        $date = strtotime($posts[0]->pub_date);
        foreach($posts as $post) {
            $this->assertTrue(strtotime($post->pub_date) >= $date);
            $date = strtotime($post->pub_date);
        }

        // test range with no posts
        $posts = $dao->getAllMentionsInRange("jack", $count = 200, $network = 'twitter', $from = '2006-03-01 13:59:00',
        $until = '2006-03-01 15:00:00', $page=1, $public=false, $include_rts = true, $order_by="pub_date",
        $direction="DESC");

        $this->assertEqual(sizeof($posts), 0);

        // test from greater than until
        $posts = $dao->getAllMentionsInRange("jack", $count = 200, $network = 'twitter', $from = '2006-03-01 23:59:00',
        $until = '2006-03-01 01:00:00', $page=1, $public=false, $include_rts = true, $order_by="pub_date",
        $direction="DESC");

        $this->assertEqual(sizeof($posts), 0);
    }

    /**
     * Test getAllMentionsIterator
     */
    public function testGetAllMentionsIterator() {
        $dao = new PostMySQLDAO();
        $mentions = $dao->getAllMentions("ev", 10, 'twitter');
        $mentions_it = $dao->getAllMentionsIterator("ev", 10, 'twitter');
        $cnt = 0;
        foreach($mentions_it as $key => $value) {
            $this->assertEqual($value->post_text,$mentions[$cnt]->post_text);
            $cnt++;
        }
        $this->assertEqual($cnt, 10);

        $mentions = $dao->getAllMentions("jack", 10, 'twitter');
        $mentions_it = $dao->getAllMentionsIterator("jack", 10, 'twitter');
        $cnt = 0;
        foreach($mentions_it as $key => $value) {
            $this->assertEqual($value->post_text,$mentions[$cnt]->post_text);
            $cnt++;
        }
        $this->assertEqual($cnt, 10);

        // test paging
        $mentions = $dao->getAllMentionsIterator("ev", $count = 1, 'twitter', $page = 1);
        $mentions->valid();
        $this->assertEqual($mentions->current()->post_text, "Hey @ev and @jack should fix Twitter - post 9");

        $mentions = $dao->getAllMentionsIterator("ev", $count = 1, 'twitter', $page = 2);
        $mentions->valid();
        $this->assertEqual($mentions->current()->post_text, "Hey @ev and @jack should fix Twitter - post 8");

        $mentions = $dao->getAllMentionsIterator("ev", $count = 1, 'twitter', $page = 3);
        $mentions->valid();
        $this->assertEqual($mentions->current()->post_text, "Hey @ev and @jack should fix Twitter - post 7");

        // insert a retweet
        $builders[] = FixtureBuilder::build('posts', array('author_user_id'=>20,
                'author_username'=>'user1', 'author_fullname'=>'User 1', 'in_reply_to_post_id'=>null,
                'in_retweet_of_post_id'=>121, 'is_geo_encoded'=>0, 'network'=>'twitter',
                'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => 13,
                'post_text'=>'Hey @ev and @jack thanks for founding Twitter retweet 1',
                'pub_date'=>'2006-03-01 00:01:00', 'location'=>'New Delhi'));

        // test count and no retweets
        $mentions = $dao->getAllMentionsIterator("ev", $count = 10, 'twitter', $page = 1, $public = false,
        $include_rts = false);
        $count = 0;
        foreach ($mentions as $mention) {
            $this->assertEqual($mention->in_retweet_of_post_id, null);
            $count++;
        }
        $this->assertEqual($count, 10);

        // test default order_by
        $mentions = $dao->getAllMentionsIterator("ev", $count = 1, 'twitter', $page = 1, false, true, "';-- SELECT");
        $mentions->valid();
        $this->assertEqual($mentions->current()->post_text, "Hey @ev and @jack should fix Twitter - post 9");
    }

    /**
     * Test getStatusSources
     */
    public function testGetStatusSources() {
        $dao = new PostMySQLDAO();
        $sources = $dao->getStatusSources(18, 'twitter');
        $this->assertEqual(sizeof($sources), 2);
        $this->assertEqual($sources[0]["source"], "Flickr");
        $this->assertEqual($sources[0]["total"], 40);
        $this->assertEqual($sources[1]["source"], "web");
        $this->assertEqual($sources[1]["total"], 1);

        //non-existent author
        $sources = $dao->getStatusSources(51, 'twitter');
        $this->assertEqual(sizeof($sources), 0);
    }

    /**
     * Test getAllPostsByUser
     */
    public function testGetAllPostsByUser() {
        $dao = new PostMySQLDAO();
        $total = $dao->getTotalPostsByUser('shutterbug', 'twitter');
        $this->assertEqual($total, 41);

        //non-existent author
        $total = $dao->getTotalPostsByUser('nonexistentusername', 'twitter');
        $this->assertEqual($total, 0);
    }

    public function testGetAllPostsByUsername() {
        $dao = new PostMySQLDAO();
        $posts = $dao->getAllPostsByUsername('shutterbug', 'twitter');
        $this->assertEqual(sizeof($posts), 41);

        //non-existent author
        $posts = $dao->getAllPostsByUsername('idontexist', 'twitter');
        $this->assertEqual(sizeof($posts), 0);
    }

    public function testGetHotPosts() {
        $dao = new PostMySQLDAO();
        $posts = $dao->getHotPosts(13, 'twitter', 5);
        $this->assertEqual(sizeof($posts), 5);

        foreach ($posts as $post) {
            $this->assertTrue(($post->reply_count_cache + $post->retweet_count_cache + $post->favlike_count_cache)>0);
            $this->assertEqual($post->in_reply_to_post_id, 0);
        }

        //non-existent author
        $posts = $dao->getHotPosts(1000000, 'twitter', 5);
        $this->assertEqual(sizeof($posts), 0);
    }

    public function testGetHotPostsWithMultipleLinks() {
        $builders = $this->buildHotPostsWithMultipleLinks();
        $dao = new PostMySQLDAO();
        $posts = $dao->getHotPosts('30', 'twitter', 5);
        $this->assertEqual(sizeof($posts), 1);
        $links = $posts[0]->links;
        $this->assertEqual(sizeof($links), 2);
    }
    /**
     * Test getAllPosts via iterator
     */
    public function testGetAllPostsByUsernameIterator() {
        $dao = new PostMySQLDAO();
        $iterator = true;
        $posts_it = $dao->getAllPostsByUsernameIterator('shutterbug', 'twitter');
        $cnt = 0;
        foreach($posts_it as $key => $value) {
            $this->assertIsA($value, 'Post');
            $cnt++;
        }
        $this->assertEqual($cnt, 41);

        // non-existent author
        $posts = $dao->getAllPostsByUsernameIterator('idontexist', 'twitter');
        $cnt = 0;
        foreach($posts_it as $key => $value) {
            $cnt++;
        }
        $this->assertEqual($cnt, 0);

    }
    /**
     * Test getAllPosts
     */
    public function testGetAllPosts() {
        $dao = new PostMySQLDAO();
        //more than count
        $posts = $dao->getAllPosts(18, 'twitter', 10);
        $this->assertEqual(sizeof($posts), 10);

        //less than count
        $posts = $dao->getAllPosts(18, 'twitter', 50);
        $this->assertEqual(sizeof($posts), 41);

        //page 2
        $posts = $dao->getAllPosts(18, 'twitter', 10, 2);
        $this->assertEqual(sizeof($posts), 10);

        //less than count, no replies --there is 1 reply, so 41-1=40
        $posts = $dao->getAllPosts(18, 'twitter', 50, 1, false);
        $this->assertEqual(sizeof($posts), 40);

        //non-existent author
        $posts = $dao->getAllPosts(30, 'twitter', 10);
        $this->assertEqual(sizeof($posts), 0);

        // test order by
        $posts = $dao->getAllPosts(18, 'twitter', 10, $page = 1, $include_replies = true, $order_by = 'pub_date',
        $direction = 'DESC');
        $this->assertEqual(sizeof($posts), 10);

        $this->assertEqual($posts[0]->post_id, 138);
        $this->assertEqual($posts[1]->post_id, 79);
        $this->assertEqual($posts[2]->post_id, 78);

        // test default order_by
        $posts = $dao->getAllPosts(18, 'twitter', 10, $page = 1, $include_replies = true, $order_by = "';-- SELECT",
        $direction = 'DESC');
        $this->assertEqual(sizeof($posts), 10);

        $this->assertEqual($posts[0]->post_id, 138);
        $this->assertEqual($posts[1]->post_id, 79);
        $this->assertEqual($posts[2]->post_id, 78);
    }

    /**
     * Test getAllPostsIterator
     */
    public function testGetAllPostIterators() {
        $dao = new PostMySQLDAO();
        $posts_it = $dao->getAllPostsIterator(18, 'twitter', 10);
        $cnt = 0;
        $this->assertIsA($posts_it,'PostIterator');
        foreach($posts_it as $key => $value) {
            $cnt++;
        }
        $this->assertEqual($cnt, 10);

        // test order by
        $posts = $dao->getAllPostsIterator(18, 'twitter', 10, $include_replies = true,
        $order_by = 'pub_date', $direction = 'DESC');

        $posts->valid();
        $this->assertEqual($posts->current()->post_id, 138);
        $posts->valid();
        $this->assertEqual($posts->current()->post_id, 79);
        $posts->valid();
        $this->assertEqual($posts->current()->post_id, 78);
    }

    /**
     * Test setting count to 0 to set no post row return limit
     */
    public function testGetAllPostIteratorsNoLimit() {
        $dao = new PostMySQLDAO();
        $posts_it = $dao->getAllPostsIterator(18, 'twitter', 0);
        $cnt = 0;
        $this->assertIsA($posts_it,'PostIterator');
        foreach($posts_it as $key => $value) {
            $cnt++;
        }
        $this->assertEqual($cnt, 41);
    }

    public function testGetPostExists() {
        $dao = new PostMySQLDAO();

        //no links
        $post = $dao->getPost('10', 'twitter');
        $this->assertTrue(isset($post));
        $this->assertEqual($post->post_text, 'This is post 10');
        //no link
        $this->assertEqual(sizeof($post->links), 0);
        // our post primary key id
        $this->assertEqual($post->id, 10);
        $this->assertNotNull($post->author);
        $this->assertEqual($post->author->username, 'ev');
        $this->assertEqual($post->author->last_updated, '2005-01-01 13:01:00');

        //links
        $post = $dao->getPost('40', 'twitter');
        $this->assertTrue(isset($post));

        $this->assertEqual($post->post_text, 'This is image post 0');
        //no link
        $this->assertEqual(sizeof($post->links), 1);
        $this->assertEqual($post->links[0]->url, 'http://example.com/0');
        // our post primary key id
        $this->assertEqual($post->id, 40);
        $this->assertNotNull($post->author);
        $this->assertEqual($post->author->username, 'shutterbug');
    }

    public function testGetPostDoesNotExist(){
        $dao = new PostMySQLDAO();
        $post = $dao->getPost(100000001, 'twitter');
        $this->assertTrue(!isset($post));
    }

    public function testGetRepliesToPost() {
        $dao = new PostMySQLDAO();
        // Default Sorting
        $posts = $dao->getRepliesToPost('41', 'twitter');
        $this->assertEqual(sizeof($posts), 3);
        $this->assertEqual($posts[0]->post_text, '@shutterbug Nice shot!', "post reply");
        $this->assertEqual($posts[0]->author->username, 'user1', "Post author");
        $this->assertEqual($posts[0]->location,'New Delhi, Delhi, India');

        $this->assertEqual($posts[1]->location,'Chennai, Tamil Nadu, India');
        $this->assertEqual($posts[2]->post_text, '@shutterbug This is a link post reply http://example.com/',
        "post reply");
        $this->assertEqual($posts[2]->post_id, '133', "post ID");
        $this->assertEqual($posts[2]->author->username, 'linkbaiter', "Post author");
        $this->assertEqual($posts[2]->links[0]->expanded_url, 'http://example.com/expanded-link.html', "Expanded URL");

        $this->assertEqual($posts[2]->location,'Mumbai, Maharashtra, India');

        // Sorting By Proximity
        $posts = $dao->getRepliesToPost('41', 'twitter', 'location');
        $this->assertEqual(sizeof($posts), 3);
        $this->assertEqual($posts[0]->post_text, '@shutterbug Nice shot!', "post reply");
        $this->assertEqual($posts[0]->author->username, 'user1', "Post author");
        $this->assertEqual($posts[0]->location,'New Delhi, Delhi, India');

        $this->assertEqual($posts[1]->location,'Mumbai, Maharashtra, India');
        $this->assertEqual($posts[1]->post_text, '@shutterbug This is a link post reply http://example.com/',
        "post reply");
        $this->assertEqual($posts[1]->post_id, '133', "post ID");
        $this->assertEqual($posts[1]->author->username, 'linkbaiter', "Post author");
        $this->assertEqual($posts[1]->links[0]->expanded_url, 'http://example.com/expanded-link.html', "Expanded URL");

        $this->assertEqual($posts[2]->location,'Chennai, Tamil Nadu, India');

        // Test date ordering for Facebook posts
        $builders = $this->buildFacebookPostAndReplies();
        $posts = $dao->getRepliesToPost('145', 'facebook');
        $this->assertEqual(sizeof($posts), 2);
        $this->assertEqual($posts[0]->post_text, '@ev Cool!');
        $this->assertEqual($posts[1]->post_text, '@ev Rock on!');

        // test paging
        $posts= $dao->getRepliesToPost('41', 'twitter', 'location', $unit = 'km', $is_public = false,
        $count = 1, $page = 1);
        $this->assertEqual(sizeof($posts), 1);
        $this->assertEqual($posts[0]->post_text, '@shutterbug Nice shot!', "post reply");
        $this->assertEqual($posts[0]->author->username, 'user1', "Post author");
        $this->assertEqual($posts[0]->location,'New Delhi, Delhi, India');

        $posts= $dao->getRepliesToPost('41', 'twitter', 'location', $unit = 'km', $is_public = false,
        $count = 1, $page = 2);
        $this->assertEqual(sizeof($posts), 1);
        $this->assertEqual($posts[0]->post_text, '@shutterbug This is a link post reply http://example.com/');
        $this->assertEqual($posts[0]->author->username, 'linkbaiter');
        $this->assertEqual($posts[0]->location,'Mumbai, Maharashtra, India');

        $posts= $dao->getRepliesToPost('41', 'twitter', 'location', $unit = 'km', $is_public = false,
        $count = 1, $page = 3);
        $this->assertEqual(sizeof($posts), 1);
        $this->assertEqual($posts[0]->location,'Chennai, Tamil Nadu, India');

        // test count
        $posts= $dao->getRepliesToPost('41', 'twitter', 'location', $unit = 'km', $is_public = false,
        $count = 1, $page = 1);
        $this->assertEqual(sizeof($posts), 1);
        $posts= $dao->getRepliesToPost('41', 'twitter', 'location', $unit = 'km', $is_public = false,
        $count = 2, $page = 1);
        $this->assertEqual(sizeof($posts), 2);
        $posts= $dao->getRepliesToPost('41', 'twitter', 'location', $unit = 'km', $is_public = false,
        $count = 3, $page = 1);
        $this->assertEqual(sizeof($posts), 3);

        // test get all
        $posts= $dao->getRepliesToPost(41, 'twitter', 'location', $unit = 'km', $is_public = false,
        $count = 0, $page = 1);
        $this->assertEqual(sizeof($posts), 3);

        // test default order_by
        $posts= $dao->getRepliesToPost(41, 'twitter', 'location', $unit = 'km', $is_public = false,
        $count = 3, $page = 1, "';-- SELECT");
        $this->assertEqual(sizeof($posts), 3);
        $this->assertEqual($posts[0]->post_text, '@shutterbug Nice shot!', "post reply");
        $this->assertEqual($posts[0]->author->username, 'user1', "Post author");
        $this->assertEqual($posts[0]->location,'New Delhi, Delhi, India');

    }

    /**
     * Test getRepliesToPostInRange
     */
    public function testgetRepliesToPostInRange() {
        $dao = new PostMySQLDAO();
        // Default Sorting
        $posts = $dao->getRepliesToPostInRange('41', 'twitter', $from = '2006-03-01 00:00:00',
        $until = '2006-03-02 23:30:59',
        $order_by="pub_date");
        $this->assertEqual(sizeof($posts), 2);
        $this->assertEqual($posts[0]->post_text, '@shutterbug Nice shot!', "post reply");
        $this->assertEqual($posts[0]->author->username, 'user1', "Post author");
        $this->assertEqual($posts[0]->location,'New Delhi, Delhi, India');
        $this->assertEqual($posts[1]->location,'Chennai, Tamil Nadu, India');
        $this->assertEqual($posts[1]->post_id, '132', "post ID");


        // test date ordering and time range check
        $date = strtotime($posts[0]->pub_date);
        foreach($posts as $post) {
            $this->assertEqual($post->in_reply_to_post_id, 41);
            $this->assertTrue(strtotime($post->pub_date) >= strtotime('2006-03-01 00:00:00'));
            $this->assertTrue(strtotime($post->pub_date) < strtotime('2006-03-03 00:30:59'));
            $this->assertTrue(strtotime($post->pub_date) <= $date);
            $date = strtotime($post->pub_date);
        }

        // test ascending order
        $posts = $dao->getRepliesToPostInRange(41, 'twitter', $from = '2006-03-01 00:00:00',
        $until = '2006-03-02 00:30:59',  $order_by="pub_date", $direction="ASC", $iterator=false,
        $is_public = false);

        $date = strtotime($posts[0]->pub_date);
        foreach($posts as $post) {
            $this->assertTrue(strtotime($post->pub_date) >= $date);
            $date = strtotime($post->pub_date);
        }

        // test range with no posts
        $posts = $dao->getRepliesToPostInRange(41, 'twitter', $from = '1970-01-02 00:00:00',
        $until = '1971-01-02 00:59:59',  $order_by="pub_date");

        $this->assertEqual(sizeof($posts), 0);

        // test from greater than until
        $posts = $dao->getRepliesToPostInRange(41, 'twitter', $from = '2007-01-02 00:00:00',
        $until = '2006-01-02 00:59:59',  $order_by="pub_date");

        $this->assertEqual(sizeof($posts), 0);
    }

    private function buildHotPostsWithMultipleLinks() {
        $builders = array();
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'30', 'user_name'=>'user1',
        'full_name'=>'User 1', 'is_protected'=>0, 'network'=>'twitter'));

        $post_builder = FixtureBuilder::build('posts', array('post_id'=>'1145', 'author_user_id'=>'30',
        'author_full_name'=>'User 1', 'post_text'=>'Tweet w/ 2 links http://yay.com http://example.com',
        'reply_count_cache'=>250, 'network'=>'twitter', 'pub_date'=>'-3h', 'in_reply_to_post_id'=>0,
        'in_reply_to_user_id'=>0));
        array_push($builders, $post_builder);
        $post_key = $post_builder->columns['last_insert_id'];

        $builders[] = FixtureBuilder::build('links', array('post_key'=>$post_key, 'url'=>'http://yay.com'));
        $builders[] = FixtureBuilder::build('links', array('post_key'=>$post_key, 'url'=>'http://example.com'));

        return $builders;
    }

    private function buildFacebookPostAndReplies() {
        $builders = array();
        $ub1 = FixtureBuilder::build('users', array('user_id'=>30, 'user_name'=>'fbuser1',
        'full_name'=>'Facebook User 1', 'is_protected'=>0, 'network'=>'facebook'));
        array_push($builders, $ub1);

        $ub2 = FixtureBuilder::build('users', array('user_id'=>31, 'user_name'=>'fbuser2',
        'full_name'=>'Facebook User 2', 'is_protected'=>0, 'network'=>'facebook'));
        array_push($builders, $ub2);

        $ub3 = FixtureBuilder::build('users', array('user_id'=>32, 'user_name'=>'fbuser3',
        'full_name'=>'Facebook User 3', 'is_protected'=>0, 'network'=>'facebook'));
        array_push($builders, $ub3);

        $pb1 = FixtureBuilder::build('posts', array('post_id'=>145, 'author_user_id'=>30,
        'author_full_name'=>'Facebook User 3', 'post_text'=>'This is a Facebook post', 'reply_count_cache'=>2,
        'network'=>'facebook', 'pub_date'=>'-3h'));
        array_push($builders, $pb1);

        $pb2 = FixtureBuilder::build('posts', array('post_id'=>146, 'author_user_id'=>31,
        'author_full_name'=>'Facebook User 2', 'post_text'=>'@ev Cool!', 'reply_count_cache'=>0,
        'in_reply_to_post_id'=>145, 'network'=>'facebook', 'pub_date'=>'-2h'));
        array_push($builders, $pb2);

        $pb3 = FixtureBuilder::build('posts', array('post_id'=>147, 'author_user_id'=>32,
        'author_full_name'=>'Facebook User 3', 'post_text'=>'@ev Rock on!', 'reply_count_cache'=>0,
        'in_reply_to_post_id'=>145, 'network'=>'facebook', 'pub_date'=>'-1h'));
        array_push($builders, $pb3);

        return $builders;
    }

    public function testGetRepliesToFacebookPagePost() {
        //Facebook page posts are a special case, because the users have their network set to 'facebook', but the post
        //network is 'facebook page'
        $dao = new PostMySQLDAO();
        $builders = $this->buildFacebookPagePostAndReplies();
        $posts = $dao->getRepliesToPost(145, 'facebook page');
        $this->assertEqual(sizeof($posts), 2);
        $this->assertEqual($posts[0]->post_text, '@ev Cool!', "post reply");
        $this->assertEqual($posts[1]->post_text, '@ev Rock on!', "post reply");

    }

    private function buildFacebookPagePostAndReplies() {
        $builders = array();
        $ub1 = FixtureBuilder::build('users', array('user_id'=>30, 'user_name'=>'fbuser1',
        'full_name'=>'Facebook User 1', 'is_protected'=>0, 'network'=>'facebook'));
        array_push($builders, $ub1);

        $ub2 = FixtureBuilder::build('users', array('user_id'=>31, 'user_name'=>'fbuser2',
        'full_name'=>'Facebook User 2', 'is_protected'=>0, 'network'=>'facebook'));
        array_push($builders, $ub2);

        $ub3 = FixtureBuilder::build('users', array('user_id'=>32, 'user_name'=>'fbuser3',
        'full_name'=>'Facebook User 3', 'is_protected'=>0, 'network'=>'facebook'));
        array_push($builders, $ub3);

        $pb1 = FixtureBuilder::build('posts', array('post_id'=>145, 'author_user_id'=>30,
        'author_full_name'=>'Facebook User 3', 'post_text'=>'This is a Facebook post', 'reply_count_cache'=>2,
        'network'=>'facebook page', 'pub_date'=>'-3h'));
        array_push($builders, $pb1);

        $pb2 = FixtureBuilder::build('posts', array('post_id'=>146, 'author_user_id'=>31,
        'author_full_name'=>'Facebook User 2', 'post_text'=>'@ev Cool!', 'reply_count_cache'=>0,
        'in_reply_to_post_id'=>145, 'network'=>'facebook page', 'pub_date'=>'-2h'));
        array_push($builders, $pb2);

        $pb3 = FixtureBuilder::build('posts', array('post_id'=>147, 'author_user_id'=>32,
        'author_full_name'=>'Facebook User 3', 'post_text'=>'@ev Rock on!', 'reply_count_cache'=>0,
        'in_reply_to_post_id'=>145, 'network'=>'facebook page', 'pub_date'=>'-1h'));
        array_push($builders, $pb3);

        return $builders;
    }

    /**
     * Test getRepliesToPostIterator
     */
    public function testGetRepliesToPostIterator() {
        $dao = new PostMySQLDAO();
        // Default Sorting
        $posts_it = $dao->getRepliesToPostIterator(41, 'twitter');
        $post1 = null; $post2 = null; $post3 = null;
        $cnt = 0;
        foreach ($posts_it as $post) {
            $cnt++;
            if ($cnt == 1) { $post1 = $post; }
            if ($cnt == 2) { $post2 = $post; }
            if ($cnt == 3) { $post3 = $post; }
        }
        $this->assertEqual($cnt, 3);
        $this->assertEqual($post1->post_text, '@shutterbug Nice shot!', "post reply");
        $this->assertEqual($post1->location,'New Delhi, Delhi, India');

        $this->assertEqual($post2->location,'Chennai, Tamil Nadu, India');
        $this->assertEqual($post3->post_text, '@shutterbug This is a link post reply http://example.com/',
                "post reply");
        $this->assertEqual($post3->post_id, 133, "post ID");

        // test paging
        $posts_it = $dao->getRepliesToPostIterator(41, 'twitter', $order_by = 'default', $unit = 'km',
        $is_public = false, $count = 1, $page = 1);
        $posts_it->valid();
        $this->assertEqual($posts_it->current()->post_text, '@shutterbug Nice shot!');

        $posts_it = $dao->getRepliesToPostIterator(41, 'twitter', $order_by = 'default', $unit = 'km',
        $is_public = false, $count = 1, $page = 2);
        $posts_it->valid();
        $this->assertEqual($posts_it->current()->location, 'Chennai, Tamil Nadu, India');

        $posts_it = $dao->getRepliesToPostIterator(41, 'twitter', $order_by = 'default', $unit = 'km',
        $is_public = false, $count = 1, $page = 3);
        $posts_it->valid();
        $this->assertEqual($posts_it->current()->post_text,
                '@shutterbug This is a link post reply http://example.com/');

        // test count
        $posts = array();
        $posts_it = $dao->getRepliesToPostIterator(41, 'twitter', $order_by = 'default', $unit = 'km',
        $is_public = false, $count = 1, $page = 1);
        foreach($posts_it as $post) {
            $posts[] = $post;
        }
        $this->assertEqual(sizeof($posts), 1);

        $posts = array();
        $posts_it = $dao->getRepliesToPostIterator(41, 'twitter', $order_by = 'default', $unit = 'km',
        $is_public = false, $count = 2, $page = 1);
        foreach($posts_it as $post) {
            $posts[] = $post;
        }
        $this->assertEqual(sizeof($posts), 2);

        $posts = array();
        $posts_it = $dao->getRepliesToPostIterator(41, 'twitter', $order_by = 'default', $unit = 'km',
        $is_public = false, $count = 3, $page = 1);
        foreach($posts_it as $post) {
            $posts[] = $post;
        }
        $this->assertEqual(sizeof($posts), 3);
    }

    /**
     * Test getRetweetsOfPost
     */
    public function testGetRetweetsOfPost() {
        $dao = new PostMySQLDAO();

        // Default Sorting
        $posts = $dao->getRetweetsOfPost(134, 'twitter');
        $this->assertEqual(sizeof($posts), 3);
        $this->assertEqual($posts[0]->location,'Chennai, Tamil Nadu, India');
        $this->assertEqual($posts[1]->location,'Dwarka, New Delhi, Delhi, India');
        $this->assertEqual($posts[2]->location,'Mumbai, Maharashtra, India');
        $this->assertEqual($posts[0]->post_text,
        'RT @quoter Be liberal in what you accept and conservative in what you send', "post reply");
        $this->assertEqual($posts[0]->author->username, 'user1', "Post author");

        // Sorting By Proximity
        $posts = $dao->getRetweetsOfPost(134, 'twitter', 'location');
        $this->assertEqual(sizeof($posts), 3);
        $this->assertEqual($posts[0]->location,'Dwarka, New Delhi, Delhi, India');
        $this->assertEqual($posts[1]->location,'Mumbai, Maharashtra, India');
        $this->assertEqual($posts[1]->post_text,
        'RT @quoter Be liberal in what you accept and conservative in what you send', "post reply");
        $this->assertEqual($posts[2]->location,'Chennai, Tamil Nadu, India');
        $this->assertEqual($posts[2]->author->username, 'user1', "Post author");

        // Sorting by Date
        $posts = $dao->getRetweetsOfPost(134, 'twitter', $order_by = 'pub_date', $unit = 'km', $is_public = false,
        $count = 10, $page = 1);
        $pub_date = strtotime($posts[0]->pub_date);
        foreach ($posts as $post) {
            $this->assertTrue(strtotime($post->pub_date) <= $pub_date);
            $pub_date = strtotime($post->pub_date);
        }

        // test paging
        $posts = $dao->getRetweetsOfPost(134, 'twitter', $order_by = 'default', $unit = 'km', $is_public = false,
        $count = 1, $page = 1);
        $this->assertEqual(sizeof($posts), 1);
        $this->assertEqual($posts[0]->location,'Chennai, Tamil Nadu, India');

        $posts = $dao->getRetweetsOfPost(134, 'twitter', $order_by = 'default', $unit = 'km', $is_public = false,
        $count = 1, $page = 2);
        $this->assertEqual(sizeof($posts), 1);
        $this->assertEqual($posts[0]->location,'Dwarka, New Delhi, Delhi, India');

        $posts = $dao->getRetweetsOfPost(134, 'twitter', $order_by = 'default', $unit = 'km', $is_public = false,
        $count = 1, $page = 3);
        $this->assertEqual(sizeof($posts), 1);
        $this->assertEqual($posts[0]->location,'Mumbai, Maharashtra, India');

        // test count
        $posts = $dao->getRetweetsOfPost(134, 'twitter', $order_by = 'default', $unit = 'km', $is_public = false,
        $count = 1, $page = 1);
        $this->assertEqual(sizeof($posts), 1);

        $posts = $dao->getRetweetsOfPost(134, 'twitter', $order_by = 'default', $unit = 'km', $is_public = false,
        $count = 2, $page = 1);
        $this->assertEqual(sizeof($posts), 2);

        $posts = $dao->getRetweetsOfPost(134, 'twitter', $order_by = 'default', $unit = 'km', $is_public = false,
        $count = 3, $page = 1);
        $this->assertEqual(sizeof($posts), 3);
    }

    /**
     * Test the sanitizeOrderBy() method.
     */
    public function testSanitizeOrderBy() {
        $dao = new PostMySQLDAO();
        $order_by = "p.post_id";
        $order_by = $dao->sanitizeOrderBy($order_by);
        $this->assertEqual($order_by, "p.post_id");

        $order_by = "post_id";
        $order_by = $dao->sanitizeOrderBy($order_by);
        $this->assertEqual($order_by, "post_id");

        $order_by = "non-existent-table-name";
        $order_by = $dao->sanitizeOrderBy($order_by);
        $this->assertEqual($order_by, "pub_date");

        $order_by = "'; DROP TABLE tu_posts;--";
        $order_by = $dao->sanitizeOrderBy($order_by);
        $this->assertEqual($order_by, "pub_date");
    }

    /**
     * Test getRelatedPosts
     */
    public function testGetRelatedPosts() {
        $dao = new PostMySQLDAO();
        $posts = $dao->getRelatedPosts(134, 'twitter');
        $this->assertEqual(count($posts), 5);
        $this->assertIsA($posts[0], 'Post');
        $posts = $dao->getRelatedPosts(1344545, 'twitter');
        $this->assertEqual(count($posts), 0);

        //test paging
        $posts = $dao->getRelatedPosts(134, 'twitter', $is_public = false, $count = 1, $page = 1,
        $geo_encoded_only = true, $include_original_post = false);
        $this->assertEqual($posts[0]->post_id, 136);
        $this->assertEqual($posts[0]->post_text,
                'RT @quoter Be liberal in what you accept and conservative in what you send');

        $posts = $dao->getRelatedPosts(134, 'twitter', $is_public = false, $count = 1, $page = 2,
        $geo_encoded_only = true, $include_original_post = false);
        $this->assertEqual($posts[0]->post_id, 144);
        $this->assertEqual($posts[0]->post_text,
                '@quoter Indeed, Jon Postel.');

        $posts = $dao->getRelatedPosts(134, 'twitter', $is_public = false, $count = 1, $page = 3,
        $geo_encoded_only = true, $include_original_post = false);
        $this->assertEqual($posts[0]->post_id, 137);
        $this->assertEqual($posts[0]->post_text,
                'RT @quoter Be liberal in what you accept and conservative in what you send');

        //test count
        $posts = $dao->getRelatedPosts(134, 'twitter', $is_public = false, $count = 1, $page = 1,
        $geo_encoded_only = true, $include_original_post = false);
        $this->assertEqual(count($posts), 1);
        $posts = $dao->getRelatedPosts(134, 'twitter', $is_public = false, $count = 2, $page = 1,
        $geo_encoded_only = true, $include_original_post = false);
        $this->assertEqual(count($posts), 2);
        $posts = $dao->getRelatedPosts(134, 'twitter', $is_public = false, $count = 3, $page = 1,
        $geo_encoded_only = true, $include_original_post = false);
        $this->assertEqual(count($posts), 3);

        // test geocoded only
        $posts = $dao->getRelatedPosts(134, 'twitter', $is_public = false, $count = 5, $page = 1,
        $geo_encoded_only = true, $include_original_post = false);
        foreach($posts as $post) {
            $this->assertEqual($post->is_geo_encoded, 1);
        }

        // test don't include original post
        $posts = $dao->getRelatedPosts(134, 'twitter', $is_public = false, $count = 500, $page = 1,
        $geo_encoded_only = true, $include_original_post = false);
        $this->assertTrue(count($posts) < 500, "Didn't fetch all posts for original post test. Change count.");
        foreach($posts as $post) {
            $this->assertNotEqual($post->post_id, 134, "Fetched original post when not meant to.");
        }
    }

    /**
     * Test getRelatedPosts
     */
    public function testGetRelatedPostsArray() {
        $dao = new PostMySQLDAO();
        $posts = $dao->getRelatedPostsArray('134', 'twitter', $is_public=false, $count=350, $page=1,
        $geo_encoded_only=false, $include_original_post=true);
        //print_r($posts);
        $this->assertEqual(count($posts), 5);
        $this->assertIsA($posts[0], 'Array');
        //assert original post is included
        $included_original_post = false;
        foreach ($posts as $post) {
            if ($post['post_id'] == '134') {
                $included_original_post = true;
            }
        }
        $this->assertTrue($included_original_post);

        $posts = $dao->getRelatedPostsArray('134', 'twitter', $is_public=true, 350, 1, $geo_encoded_only=false,
        $include_original_post=false);
        $this->assertEqual(count($posts), 3);
        //print_r($posts);
        //assert original post is NOT included
        $included_original_post = false;
        foreach ($posts as $post) {
            if ($post['post_id'] == '134') {
                $included_original_post = true;
            }
        }
        $this->assertFalse($included_original_post);

        $posts = $dao->getRelatedPostsArray('134', 'twitter', $is_public=true, 350, 1, $geo_encoded_only=false,
        $include_original_post=true);
        //assert there are no protected posts
        $included_protected_posts = false;
        foreach ($posts as $post) {
            if ($post['is_protected'] == 1) {
                $included_protected_posts = true;
            }
        }
        $this->assertFalse($included_protected_posts);

        $posts = $dao->getRelatedPostsArray('134', 'twitter', $is_public=false, 350, 1, $geo_encoded_only=true,
        $include_original_post=true);
        //assert there are no posts NOT geoencoded
        $included_ungeoencoded_posts = false;
        foreach ($posts as $post) {
            if ($post['is_geo_encoded'] == 0) {
                $included_ungeoencoded_posts = true;
            }
        }
        $this->assertFalse($included_ungeoencoded_posts);

        $posts = $dao->getRelatedPosts('1344545', 'twitter');
        $this->assertEqual(count($posts), 0);
    }

    /**
     * Test function getPostsAuthorHasRepliedTo
     */
    public function testGetPostsAuthorHasRepliedTo(){
        //Public exchanges only
        $dao = new PostMySQLDAO();
        $posts_replied_to = $dao->getPostsAuthorHasRepliedTo(18, 10, 'twitter', 1, true);
        $this->assertEqual($posts_replied_to[0]["questioner_username"], "user2");
        $this->assertEqual($posts_replied_to[0]["question"], "@shutterbug Nice shot!");
        $this->assertEqual($posts_replied_to[0]["answerer_username"], "shutterbug");
        $this->assertEqual($posts_replied_to[0]["answer"], "@user2 Thanks!");


        //set up a private exchange
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>1000, 'author_user_id'=>20,
        'author_username'=>'user1', 'author_fullname'=>'User 1', 'network'=>'twitter',
        'post_text'=>'@ev Privately, when will Twitter have a business model?', 'source'=>'web',
        'pub_date'=>'2010-03-01 00:00:00', 'reply_count_cache'=>0, 'retweet_count_cache'=>0,
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null, 'in_reply_to_user_id'=>13, 'is_protected'=>1 ));

        $builders[] = FixtureBuilder::build('posts', array('post_id'=>1001, 'author_user_id'=>13,
        'author_username'=>'ev', 'author_fullname'=>'Ev Williams', 'network'=>'twitter',
        'post_text'=>'@user1 Privately? Soon...', 'source'=>'web', 'pub_date'=>'2010-03-01 01:00:00',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>1,
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'in_reply_to_user_id'=>20, 'in_reply_to_post_id'=>1000));

        $posts_replied_to = $dao->getPostsAuthorHasRepliedTo(13, 10, 'twitter', 1, true);
        $this->assertEqual(sizeof($posts_replied_to), 1);
        $this->assertEqual($posts_replied_to[0]["question_post_id"], 139);
        $this->assertEqual($posts_replied_to[0]["questioner_username"], "user1");
        $this->assertEqual($posts_replied_to[0]["question"], "@ev When will Twitter have a business model?");
        $this->assertFalse($posts_replied_to[0]["question_is_protected"]);
        $this->assertEqual($posts_replied_to[0]['answer_post_id'], 140);
        $this->assertEqual($posts_replied_to[0]["answerer_username"], "ev");
        $this->assertEqual($posts_replied_to[0]["answer"], "@user1 Soon...");
        $this->assertFalse($posts_replied_to[0]["answer_is_protected"]);

        $posts_replied_to = $dao->getPostsAuthorHasRepliedTo(13, 10, 'twitter', 1, false);
        $this->assertEqual(sizeof($posts_replied_to), 2);
        $this->debug(Utils::varDumpToString($posts_replied_to));

        $this->assertEqual($posts_replied_to[0]["question_post_id"], 1000);
        $this->assertEqual($posts_replied_to[0]["questioner_username"], "user1");
        $this->assertEqual($posts_replied_to[0]["question"], "@ev Privately, when will Twitter have a business model?");
        $this->assertTrue($posts_replied_to[0]["question_is_protected"]);
        $this->assertEqual($posts_replied_to[0]['answer_post_id'], 1001);
        $this->assertEqual($posts_replied_to[0]["answerer_username"], "ev");
        $this->assertEqual($posts_replied_to[0]["answer"], "@user1 Privately? Soon...");
        $this->assertTrue($posts_replied_to[0]["answer_is_protected"]);

        $this->assertEqual($posts_replied_to[1]["question_post_id"], 139);
        $this->assertEqual($posts_replied_to[1]["questioner_username"], "user1");
        $this->assertEqual($posts_replied_to[1]["question"], "@ev When will Twitter have a business model?");
        $this->assertEqual($posts_replied_to[1]['answer_post_id'], 140);
        $this->assertEqual($posts_replied_to[1]["answerer_username"], "ev");
        $this->assertEqual($posts_replied_to[1]["answer"], "@user1 Soon...");
    }

    /**
     * Test getExchangesBetweenUsers
     */
    public function testGetExchangesBetweenUsers() {
        $dao = new PostMySQLDAO();
        $posts_replied_to = $dao->getExchangesBetweenUsers(18, 21, 'twitter');

        $this->assertEqual(sizeof($posts_replied_to), 2);
        $this->assertEqual($posts_replied_to[0]["questioner_username"], "shutterbug");
        $this->assertEqual($posts_replied_to[0]["question"], "This is image post 1");
        $this->assertEqual($posts_replied_to[0]["answerer_username"], "user2");
        $this->assertEqual($posts_replied_to[0]["answer"], "@shutterbug Nice shot!");

        $this->assertEqual($posts_replied_to[1]["questioner_username"], "user2");
        $this->assertEqual($posts_replied_to[1]["question"], "@shutterbug Nice shot!");
        $this->assertEqual($posts_replied_to[1]["answerer_username"], "shutterbug");
        $this->assertEqual($posts_replied_to[1]["answer"], "@user2 Thanks!");

        $this->debug(Utils::varDumpToString($posts_replied_to));

        $posts_replied_to = $dao->getExchangesBetweenUsers(13, 20, 'twitter');
        $this->assertEqual(sizeof($posts_replied_to), 1);

        $this->assertEqual($posts_replied_to[0]["question_post_id"], 139);
        $this->assertEqual($posts_replied_to[0]["questioner_username"], "user1");
        $this->assertEqual($posts_replied_to[0]["question"], "@ev When will Twitter have a business model?");
        $this->assertEqual($posts_replied_to[0]['answer_post_id'], 140);
        $this->assertEqual($posts_replied_to[0]["answerer_username"], "ev");
        $this->assertEqual($posts_replied_to[0]["answer"], "@user1 Soon...");
    }

    /**
     * Test isPostInDB
     */
    public function testIsPostInDB() {
        $dao = new PostMySQLDAO();
        $this->assertTrue($dao->isPostInDB(129, 'twitter'));

        $this->assertTrue(!$dao->isPostInDB(250, 'twitter'));
    }

    /**
     * Test isReplyInDB
     */
    public function testIsReplyInDB() {
        $dao = new PostMySQLDAO();
        $this->assertTrue($dao->isReplyInDB(138, 'twitter'));

        $this->assertTrue(!$dao->isReplyInDB(250, 'twitter'));
    }

    public function testAddPost() {
        $dao = new PostMySQLDAO();
        $vals = array();

        $vals['post_id']=2904;
        $vals['author_username']='quoter';
        $vals['author_fullname']="Quoter of Quotables";
        $vals['author_avatar']='avatar.jpg';

        //test add post without all the req'd fields set
        $this->assertFalse($dao->addPost($vals), "Post not inserted, not all values set");

        $vals['author_user_id']= 22;
        $vals['post_text']="Go confidently in the direction of your dreams! Live the life you've imagined.";
        $vals['pub_date']='3/1/2010';
        $vals['source']='web';
        $vals['network']= 'twitter';
        $vals['is_protected'] = 1;

        //add post with insufficient location data
        $this->assertEqual($dao->addPost($vals), 20082);
        $post = $dao->getPost(2904, 'twitter');
        $this->assertEqual($post->post_id, 2904);
        $this->assertEqual($post->location, NULL);
        $this->assertEqual($post->place, NULL);
        $this->assertEqual($post->geo, NULL);
        $this->assertEqual($post->is_geo_encoded, 6);

        $vals['post_id'] = 250;
        $vals['location']="New Delhi";
        $vals['place']="Dwarka, New Delhi";
        $vals['geo']="10.0000 20.0000";
        $vals['in_reply_to_post_id']= '';

        //test add straight post that doesn't exist
        $this->assertEqual($dao->addPost($vals), 20083);
        $post = $dao->getPost(250, 'twitter');
        $this->assertEqual($post->post_id, 250);
        $this->assertEqual($post->author_user_id, 22);
        $this->assertEqual($post->author_username, 'quoter');
        $this->assertEqual($post->author_fullname, 'Quoter of Quotables');
        $this->assertEqual($post->author_avatar, 'avatar.jpg');
        $this->assertEqual($post->post_text,
        "Go confidently in the direction of your dreams! Live the life you've imagined.");
        $this->assertEqual($post->location, "New Delhi");
        $this->assertEqual($post->place, "Dwarka, New Delhi");
        $this->assertEqual($post->geo, "10.0000 20.0000");
        $this->assertEqual($post->source, 'web');
        $this->assertEqual($post->network, 'twitter');
        $this->assertEqual($post->reply_count_cache, 0);
        $this->assertEqual($post->retweet_count_cache, 0);
        $this->assertEqual($post->retweet_count_api, 0);
        $this->assertEqual($post->old_retweet_count_cache, 0);
        $this->assertEqual($post->in_reply_to_post_id, null);
        $this->assertFalse($post->is_reply_by_friend);
        $this->assertEqual($post->is_geo_encoded, 0);
        $this->assertTrue($post->is_protected);
        $this->assertEqual($post->favlike_count_cache, 0);

        //test add post that does exist
        $vals['post_id']=129;
        $this->assertFalse($dao->addPost($vals), "Post exists, nothing inserted");

        //test add post with new favorite_count
        $vals['post_id']=250;
        $vals['favlike_count_cache']=67;
        $this->assertFalse($dao->addPost($vals), "Post exists, nothing inserted");
        $post = $dao->getPost(250, 'twitter');
        $this->assertEqual($post->favlike_count_cache, 67);

        //test add reply, check cache count
        $vals['post_id']=251;
        $vals['in_reply_to_post_id']= 129;
        $this->assertEqual($dao->addPost($vals), 20084);
        $post = $dao->getPost(129, 'twitter');
        $this->assertEqual($post->reply_count_cache, 1, "reply count got updated");

        //test add retweet, check cache count
        $vals['post_id']=252;
        $vals['in_reply_to_post_id']= '';
        $vals['in_retweet_of_post_id']= 128;
        $this->assertEqual($dao->addPost($vals), 20085);
        $post = $dao->getPost(128, 'twitter');
        $this->assertEqual($post->old_retweet_count_cache, 1, "old-style retweet count got updated");
        $this->assertEqual($post->retweet_count_cache, 0);
        $this->assertEqual($post->retweet_count_api, 0);
    }

    public function testAddPostNotProtected() {
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

        $this->assertEqual($dao->addPost($vals), 20082);
        $post = $dao->getPost(2904, 'twitter');
        $this->assertEqual($post->post_id, 2904);
        $this->assertEqual($post->location, NULL);
        $this->assertEqual($post->place, NULL);
        $this->assertEqual($post->geo, NULL);
        $this->assertEqual($post->is_geo_encoded, 6);
        $this->assertFalse($post->is_protected);
    }

    public function testAddPostProtected() {
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
        $vals['is_protected'] = 1;

        $this->assertEqual($dao->addPost($vals), 20082);
        $post = $dao->getPost(2904, 'twitter');
        $this->assertEqual($post->post_id, 2904);
        $this->assertEqual($post->location, NULL);
        $this->assertEqual($post->place, NULL);
        $this->assertEqual($post->geo, NULL);
        $this->assertEqual($post->is_geo_encoded, 6);
        $this->assertTrue($post->is_protected);
    }

    public function testAddReplyToPostByFriend() {
        //@ev ID 13, @shutterbug ID 18
        $builder = FixtureBuilder::build('follows', array('user_id'=>13, 'follower_id'=>18));

        //reply to shutterbug by ev
        // post id 41 is by shutterbug
        $vals['post_id']=1000;
        $vals['author_username']='ev';
        $vals['author_fullname']="Ev Williams";
        $vals['author_avatar']='avatar.jpg';
        $vals['author_user_id']= 13;
        $vals['post_text']="@shutterbug Nice shot";
        $vals['pub_date']='3/1/2010';
        $vals['source']='web';
        $vals['network']= 'twitter';
        $vals['in_reply_to_post_id']= 41;
        $vals['is_protected'] = 0;

        $dao = new PostMySQLDAO();
        $dao->addPost($vals);
        $stmt = PostMySQLDAO::$PDO->query( "select * from " . $this->table_prefix . 'posts where post_id=1000' );
        $data = $stmt->fetch();
        $this->assertEqual($data['is_reply_by_friend'], 1);
    }

    public function testAddRetweetOfPostByFriend() {
        //@ev ID 13, @shutterbug ID 18
        $builder = FixtureBuilder::build('follows', array('user_id'=>13, 'follower_id'=>18));

        //reply to shutterbug by ev
        // post id 41 is by shutterbug
        $vals['post_id']=1000;
        $vals['author_username']='ev';
        $vals['author_fullname']="Ev Williams";
        $vals['author_avatar']='avatar.jpg';
        $vals['author_user_id']= 13;
        $vals['post_text']="RT @shutterbug Nice shot";
        $vals['pub_date']='3/1/2010';
        $vals['source']='web';
        $vals['network']= 'twitter';
        $vals['in_retweet_of_post_id']= 41;
        $vals['is_protected'] = 0;

        $dao = new PostMySQLDAO();
        $dao->addPost($vals);
        $stmt = PostMySQLDAO::$PDO->query( "select * from " . $this->table_prefix . 'posts where post_id=1000' );
        $data = $stmt->fetch();
        $this->assertEqual($data['is_retweet_by_friend'], 1);
    }

    /**
     * Test RT and RT count processing.  This test builds native RTs only, with the actual number in the database
     * higher than the twitter max reporting threshold of 100.
     * The $vals array is what would be generated from the xml parsing (or JSON parsing in the case of the streaming
     * plugin). For a native RT it includes the original post as a sub-array.
     * In processing a native RT'd post, the original should be added to the db if it is not there
     * already.
     */
    public function testAddManyNativeRetweetsOfPost() {

        $counter = 0;
        $postbase = 100000;
        $userbase = 1000;
        $dao = new PostMySQLDAO();
        while ($counter < 105) {
            $vals = array();
            $vals['post_id'] = $postbase + $counter;
            $vals['author_user_id'] = $userbase + $counter;
            $vals['user_id'] = $userbase + $counter;
            $vals['author_username'] = "user" . $userbase + $counter;
            $vals['user_name'] = "user" . $userbase + $counter;
            $vals['author_fullname'] = "User " . $userbase + $counter;
            $vals['full_name'] = "User " . $userbase + $counter;
            $vals['author_avatar'] = 'http://a2.twimg.com/profile_images/1146326394/ears_crosshatch_normal.jpg';
            $vals['avatar'] = 'http://a2.twimg.com/profile_images/1146326394/ears_crosshatch_normal.jpg';
            $vals['location'] = 'Austin, TX';
            $vals['description'] = 'this is a bio';
            $vals['url'] = '';
            $vals['is_protected'] = 0;
            $vals['follower_count'] = 1000;
            $vals['friend_count'] = 1000;
            $vals['post_count'] = 2000;
            $vals['joined'] = '2007-03-29 02:13:08';
            $vals['post_text'] = "RT @user100: People in non-gender typical jobs judged " .
              "more harshly for their mistakes. http://is.gd/izUl5";
            $vals['pub_date'] = '2010-12-12 14:15:27';
            $vals['favorites_count'] = 1500;
            $vals['in_reply_to_post_id'] = '';
            $vals['in_reply_to_user_id'] = '';
            $vals['source'] = '<a href="http://twitter.com/" rel="nofollow">Twitter for iPhone</a>';
            $vals['geo'] = '';
            $vals['place'] = '';
            $vals['network'] = 'twitter';
            $vals['in_retweet_of_post_id'] = '13708601491193856';
            $vals['in_rt_of_user_id'] = 20542737;

            // for a native RT, the RT'd post info includes the original post
            $retweeted_post = array();
            $rtp = array();
            $rtp['post_id'] = '13708601491193856';
            $rtp['author_user_id'] = 13;//20542737;
            $rtp['user_id'] = 20542737;
            $rtp['author_username']= 'user100';
            $rtp['user_name']= 'user100';
            $rtp['author_fullname'] = 'User 100';
            $rtp['full_name'] = 'User 100';
            $rtp['author_avatar'] = 'http://a3.twimg.com/profile_images/86835447/10947_normal.jpg';
            $rtp['avatar']= 'http://a3.twimg.com/profile_images/86835447/10947_normal.jpg';
            $rtp['location'] = 'San Jose, CA';
            $rtp['description'] = '';
            $rtp['url'] = '';
            $rtp['is_protected'] = 0;
            $rtp['follower_count'] = 3376;
            $rtp['friend_count'] =248;
            $rtp['post_count'] = 3681;
            $rtp['joined'] = '2009-02-10 20:30:11';
            $rtp['post_text'] = "People in non-gender typical jobs judged " .
              "more harshly for their mistakes. http://is.gd/izUl5";
            $rtp['pub_date'] = '2010-12-11 21:35:59';
            $rtp['favorites_count'] = 2;
            $rtp['in_reply_to_post_id'] = '';
            $rtp['in_reply_to_user_id'] = '';
            $rtp['source'] = '<a href="http://www.tweetdeck.com" rel="nofollow">TweetDeck</a>';
            $rtp['geo'] = '';
            $rtp['place'] = '';
            $rtp['network'] = 'twitter';
            $rtp['retweet_count_api'] = 100;

            $retweeted_post['content'] = $rtp;
            $vals['retweeted_post'] = $retweeted_post;
            $dao->addPost($vals);
            $counter++;
        }
        $post = $dao->getPost('13708601491193856', 'twitter');
        $this->assertEqual($post->retweet_count_cache, 105);
        $this->assertEqual($post->old_retweet_count_cache, 0);
        $this->assertEqual($post->retweet_count_api, 100);
        // this is the value displayed in the UI
        $this->assertEqual($post->all_retweets, 105);
        $this->assertEqual($post->rt_threshold, 0);

    }

    /**
     * Test RT and RT count processing.
     * in this test the API RT count is higher than the cached database count, and is maxed out at threshold.
     * This test includes 2 old-style RTs as well as native RTs.
     */
    public function testAddManyNativeRetweetsOfPost2() {

        $counter = 0;
        $postbase = 100000;
        $userbase = 1000;
        $dao = new PostMySQLDAO();
        while ($counter < 10) {
            $vals = array();
            $vals['post_id'] = $postbase + $counter;
            $vals['author_user_id'] = $userbase + $counter;
            $vals['user_id'] = $userbase + $counter;
            $vals['author_username'] = "user" . $userbase + $counter;
            $vals['user_name'] = "user" . $userbase + $counter;
            $vals['author_fullname'] = "User " . $userbase + $counter;
            $vals['full_name'] = "User " . $userbase + $counter;
            $vals['author_avatar'] = 'http://a2.twimg.com/profile_images/1146326394/ears_crosshatch_normal.jpg';
            $vals['avatar'] = 'http://a2.twimg.com/profile_images/1146326394/ears_crosshatch_normal.jpg';
            $vals['location'] = 'Austin, TX';
            $vals['description'] = 'this is a bio';
            $vals['url'] = '';
            $vals['is_protected'] = 0;
            $vals['follower_count'] = 1000;
            $vals['friend_count'] = 1000;
            $vals['post_count'] = 2000;
            $vals['joined'] = '2007-03-29 02:13:08';
            $vals['post_text'] = "RT @user100: People in non-gender typical jobs judged " .
              "more harshly for their mistakes. http://is.gd/izUl5";
            $vals['pub_date'] = '2010-12-12 14:15:27';
            $vals['favorites_count'] = 1500;
            $vals['in_reply_to_post_id'] = '';
            $vals['in_reply_to_user_id'] = '';
            $vals['source'] = '<a href="http://twitter.com/" rel="nofollow">Twitter for iPhone</a>';
            $vals['geo'] = '';
            $vals['place'] = '';
            $vals['network'] = 'twitter';
            $vals['in_retweet_of_post_id'] = '13708601491193856';
            $vals['in_rt_of_user_id'] = 20542737;

            // for a native RT, the RT'd post info includes the original post
            $retweeted_post = array();
            $rtp = array();
            $rtp['post_id'] = '13708601491193856';
            $rtp['author_user_id'] = 13; //20542737;
            $rtp['user_id'] = 20542737;
            $rtp['author_username']= 'user100';
            $rtp['user_name']= 'user100';
            $rtp['author_fullname'] = 'User 100';
            $rtp['full_name'] = 'User 100';
            $rtp['author_avatar'] = 'http://a3.twimg.com/profile_images/86835447/10947_normal.jpg';
            $rtp['avatar']= 'http://a3.twimg.com/profile_images/86835447/10947_normal.jpg';
            $rtp['location'] = 'San Jose, CA';
            $rtp['description'] = '';
            $rtp['url'] = '';
            $rtp['is_protected'] = 0;
            $rtp['follower_count'] = 3376;
            $rtp['friend_count'] =248;
            $rtp['post_count'] = 3681;
            $rtp['joined'] = '2009-02-10 20:30:11';
            $rtp['post_text'] = "People in non-gender typical jobs judged " .
              "more harshly for their mistakes. http://is.gd/izUl5";
            $rtp['pub_date'] = '2010-12-11 21:35:59';
            $rtp['favorites_count'] = 2;
            $rtp['in_reply_to_post_id'] = '';
            $rtp['in_reply_to_user_id'] = '';
            $rtp['source'] = '<a href="http://www.tweetdeck.com" rel="nofollow">TweetDeck</a>';
            $rtp['geo'] = '';
            $rtp['place'] = '';
            $rtp['network'] = 'twitter';
            $rtp['retweet_count_api'] = 100;

            $retweeted_post['content'] = $rtp;
            $vals['retweeted_post'] = $retweeted_post;
            $dao->addPost($vals);
            $counter++;
        }
        // now add a couple of non-native RTs.
        $vals = array();
        $vals['post_id'] = $postbase + $counter;
        $vals['author_user_id'] = $userbase + $counter;
        $vals['user_id'] = $userbase + $counter;
        $vals['author_username'] = "user" . $userbase + $counter;
        $vals['user_name'] = "user" . $userbase + $counter;
        $vals['author_fullname'] = "User " . $userbase + $counter;
        $vals['full_name'] = "User " . $userbase + $counter;
        $vals['author_avatar'] = 'http://a2.twimg.com/profile_images/1146326394/ears_crosshatch_normal.jpg';
        $vals['avatar'] = 'http://a2.twimg.com/profile_images/1146326394/ears_crosshatch_normal.jpg';
        $vals['location'] = 'Austin, TX';
        $vals['description'] = 'this is a bio';
        $vals['url'] = '';
        $vals['is_protected'] = 0;
        $vals['follower_count'] = 1000;
        $vals['friend_count'] = 1000;
        $vals['post_count'] = 2000;
        $vals['joined'] = '2007-03-29 02:13:08';
        $vals['post_text'] = "RT @user100: People in non-gender typical jobs judged " .
          "more harshly for their mistakes. http://is.gd/izUl5";
        $vals['pub_date'] = '2010-12-12 14:15:27';
        $vals['favorites_count'] = 1500;
        $vals['in_reply_to_post_id'] = '';
        $vals['in_reply_to_user_id'] = '';
        $vals['source'] = '<a href="http://twitter.com/" rel="nofollow">Twitter for iPhone</a>';
        $vals['geo'] = '';
        $vals['place'] = '';
        $vals['network'] = 'twitter';
        $vals['in_retweet_of_post_id'] = '13708601491193856';
        $vals['in_rt_of_user_id'] = '20542737';
        $dao->addPost($vals);
        $counter++;

        $vals = array();
        $vals['post_id'] = $postbase + $counter;
        $vals['author_user_id'] = $userbase + $counter;
        $vals['user_id'] = $userbase + $counter;
        $vals['author_username'] = "user" . $userbase + $counter;
        $vals['user_name'] = "user" . $userbase + $counter;
        $vals['author_fullname'] = "User " . $userbase + $counter;
        $vals['full_name'] = "User " . $userbase + $counter;
        $vals['author_avatar'] = 'http://a2.twimg.com/profile_images/1146326394/ears_crosshatch_normal.jpg';
        $vals['avatar'] = 'http://a2.twimg.com/profile_images/1146326394/ears_crosshatch_normal.jpg';
        $vals['location'] = 'Austin, TX';
        $vals['description'] = 'this is a bio';
        $vals['url'] = '';
        $vals['is_protected'] = 0;
        $vals['follower_count'] = 1000;
        $vals['friend_count'] = 1000;
        $vals['post_count'] = 2000;
        $vals['joined'] = '2007-03-29 02:13:08';
        $vals['post_text'] = "RT @user100: People in non-gender typical jobs judged " .
          "more harshly for their mistakes. http://is.gd/izUl5";
        $vals['pub_date'] = '2010-12-12 14:15:27';
        $vals['favorites_count'] = 1500;
        $vals['in_reply_to_post_id'] = '';
        $vals['in_reply_to_user_id'] = '';
        $vals['source'] = '<a href="http://twitter.com/" rel="nofollow">Twitter for iPhone</a>';
        $vals['geo'] = '';
        $vals['place'] = '';
        $vals['network'] = 'twitter';
        $vals['in_retweet_of_post_id'] = '13708601491193856';
        $vals['in_rt_of_user_id'] = 20542737;
        $dao->addPost($vals);
        $counter++;

        $post = $dao->getPost('13708601491193856', 'twitter');
        $this->assertEqual($post->retweet_count_cache, 10);
        $this->assertEqual($post->old_retweet_count_cache, 2);
        $this->assertEqual($post->retweet_count_api, 100);
        // this is the value displayed in the UI-- the sum should be the higher reported value from twitter
        // for the native RTs, plus the old-style rt count.
        $this->assertEqual($post->all_retweets, 102);
        $this->assertEqual($post->rt_threshold, 1);

    }

    /**
     * Test deletePost
     */
    public function testDeletePost() {
        $post_dao = new PostMySQLDAO();

        // no such post
        $this->assertEqual(0, $post_dao->deletePost(-99));

        // post deleted
        $this->assertEqual(1, $post_dao->deletePost(1));
        $sql = "select * from " . $this->table_prefix . 'posts where id = 1';
        $stmt = PostMySQLDAO::$PDO->query($sql);
        $this->assertFalse($stmt->fetch(PDO::FETCH_ASSOC));
    }

    /**
     * Test getTotalPostsByUser
     */
    public function testGetTotalPostsByUser() {
        $post_dao = new PostMySQLDAO();
        $total_posts = $post_dao->getTotalPostsByUser('ev', 'twitter');

        $this->assertEqual($total_posts, 40);
    }

    public function testGetMostRepliedToPostsInLastWeek() {
        //Add posts with replies by user3, who is on the public timeline with retweet counts in the last 9 days
        $counter = 0;
        $id = 200;
        $builders = array();
        while ($counter < 40) {
            $id += $counter;
            $builders[] = FixtureBuilder::build('posts', array(
                'id'=>$id,
                'post_id'=>(147+$counter),
                'author_user_id'=>23,
                'author_username'=>'user3',
                'pub_date'=>'-'.$counter.'d',
                'reply_count_cache'=>$counter));
            $counter++;
        }
        $post_dao = new PostMySQLDAO();
        $posts = $post_dao->getMostRepliedToPostsInLastWeek('user3', 'twitter', 5);
        $this->assertEqual(sizeof($posts), 5);
        $this->assertEqual($posts[0]->reply_count_cache, 7);
        $this->assertEqual($posts[1]->reply_count_cache, 6);

        $posts = $post_dao->getMostRepliedToPostsInLastWeek('user2', 'twitter', 5);
        $this->assertEqual(sizeof($posts), 0);
    }

    public function testGetMostRetweetedPostsInLastWeek() {
        //Add posts with replies by user3, who is on the public timeline with retweet counts in the last 9 days
        $counter = 0;
        $id = 200;
        $builders = array();
        while ($counter < 40) {
            $id += $counter;
            $builders[] = FixtureBuilder::build('posts', array(
            'id'=>$id,
            'post_id'=>(147+$counter),
            'author_user_id'=>23,
            'author_username'=>'user3',
            'pub_date'=>'-'.$counter.'d',
            'retweet_count_cache'=>$counter,
            'old_retweet_count_cache' => floor($counter/2)
            ));
            $counter++;
        }
        $post_dao = new PostMySQLDAO();
        $posts = $post_dao->getMostRetweetedPostsInLastWeek('user3', 'twitter', 5);
        $this->assertEqual(sizeof($posts), 5);
        $this->assertEqual($posts[0]->reply_count_cache, 0);
        $this->assertEqual($posts[1]->reply_count_cache, 0);
        $this->assertTrue(($posts[0]->retweet_count_cache + $posts[0]->old_retweet_count_cache) >=
        ($posts[1]->retweet_count_cache + $posts[1]->old_retweet_count_cache));

        $posts = $post_dao->getMostRetweetedPostsInLastWeek('user2', 'twitter', 5);
        $this->assertEqual(sizeof($posts), 0);
    }

    /**
     * test that the non-persistent RT-related fields are getting populated properly as the
     * Post objects are constructed.
     */
    public function testPostRetweetFields() {
        $counter = 0;
        $id = 1500;
        $builders = array();
        $post_dao = new PostMySQLDAO();

        $builders[] = FixtureBuilder::build('posts', array(
        'id'=>$id,
        'post_id'=>$id++,
        'author_user_id'=>23,
        'author_username'=>'user3',
        'retweet_count_cache'=>150,
        'retweet_count_api' => 100,
        'old_retweet_count_cache' => 5
        ));
        $builders[] = FixtureBuilder::build('posts', array(
        'id'=>$id,
        'post_id'=>$id++,
        'author_user_id'=>23,
        'author_username'=>'user3',
        'retweet_count_cache'=>90,
        'retweet_count_api' => 92,
        'old_retweet_count_cache' => 5
        ));
        $builders[] = FixtureBuilder::build('posts', array(
        'id'=>$id,
        'post_id'=>$id++,
        'author_user_id'=>23,
        'author_username'=>'user3',
        'retweet_count_cache'=>90,
        'retweet_count_api' => 100,
        'old_retweet_count_cache' => 5
        ));

        $post = $post_dao->getPost(1500, 'twitter');
        $this->assertEqual($post->rt_threshold, 0);
        $this->assertEqual($post->all_retweets, 155);
        $post = $post_dao->getPost(1501, 'twitter');
        $this->assertEqual($post->rt_threshold, 0);
        $this->assertEqual($post->all_retweets, 97);
        $post = $post_dao->getPost(1502, 'twitter');
        $this->assertEqual($post->rt_threshold, 1);
        $this->assertEqual($post->all_retweets, 105);
    }

    /**
     * Test that detection of an old-style RT for an already-stored post is properly processed
     */
    public function testCatchOldStyleRT() {
        $post_dao = new PostMySQLDAO();
        $builders = array();
        $builders[] = FixtureBuilder::build('posts', array(
        'id' => 5000,
        'post_id'=> '13708601491193856',
        'author_user_id'=>13,//100,
        'author_username'=>'user100',
        'retweet_count_cache'=>2,
        'retweet_count_api' => 5,
        'old_retweet_count_cache' => 0,
        'post_text' => "People in non-gender typical jobs judged " .
          "more harshly for their mistakes. http://is.gd/izUl5",
        'network' => 'twitter',
        'in_retweet_of_post_id' => null
        ));
        // store an old-style RT post w/out the in_retweet_of_post_id field set.
        $builders[] = FixtureBuilder::build('posts', array(
        'id' => 5001,
        'post_id'=> 12345,
        'author_user_id'=>13,//1234,
        'author_username'=>'user1234',
        'post_text' => "RT @user100: People in non-gender typical jobs judged " .
          "more harshly for their mistakes. http://is.gd/izUl5",
        'network' => 'twitter',
        'in_retweet_of_post_id' => null
        ));

        $post = $post_dao->getPost(12345, 'twitter');
        $this->assertEqual($post->in_retweet_of_post_id, null);
        $post = $post_dao->getPost('13708601491193856', 'twitter');
        $this->assertEqual($post->old_retweet_count_cache, 0);

        // now try adding that post again with the in_retweet_of_post_id field set.
        // the old_retweet_count_cache value of the original should be updated in
        // the process
        $vals = array();
        $vals['post_id'] = 12345;
        $vals['author_user_id'] = 13;//1234;
        $vals['user_id'] = 1234;
        $vals['author_username'] = "user1234";
        $vals['user_name'] = "user1234";
        $vals['author_fullname'] = "User 1234";
        $vals['full_name'] = "User 1234";
        $vals['author_avatar'] = 'http://a2.twimg.com/profile_images/1146326394/ears_crosshatch_normal.jpg';
        $vals['avatar'] = 'http://a2.twimg.com/profile_images/1146326394/ears_crosshatch_normal.jpg';
        $vals['location'] = 'Austin, TX';
        $vals['description'] = 'this is a bio';
        $vals['url'] = '';
        $vals['is_protected'] = 0;
        $vals['follower_count'] = 1000;
        $vals['friend_count'] = 1000;
        $vals['post_count'] = 2000;
        $vals['joined'] = '2007-03-29 02:13:08';
        $vals['post_text'] = "RT @user100: People in non-gender typical jobs judged " .
          "more harshly for their mistakes. http://is.gd/izUl5";
        $vals['pub_date'] = '2010-12-12 14:15:27';
        $vals['favorites_count'] = 1500;
        $vals['in_reply_to_post_id'] = '';
        $vals['in_reply_to_user_id'] = '';
        $vals['source'] = '<a href="http://twitter.com/" rel="nofollow">Twitter for iPhone</a>';
        $vals['geo'] = '';
        $vals['place'] = '';
        $vals['network'] = 'twitter';
        $vals['in_retweet_of_post_id'] = '13708601491193856';
        $vals['in_rt_of_user_id'] = 100;

        $post_dao->addPost($vals);
        $post = $post_dao->getPost(12345, 'twitter');
        $this->assertEqual($post->in_retweet_of_post_id, '13708601491193856');
        $post = $post_dao->getPost('13708601491193856', 'twitter');
        $this->assertEqual($post->old_retweet_count_cache, 1);
        $this->assertEqual($post->retweet_count_cache, 2);
        // repeat, make sure no duplication now that the in_retweet_of_post_id IS set
        $post_dao->addPost($vals);
        $post = $post_dao->getPost('13708601491193856', 'twitter');
        $this->assertEqual($post->old_retweet_count_cache, 1);
        $this->assertEqual($post->retweet_count_cache, 2);
    }

    /**
     * Test getPostsToGeoencode
     */
    public function testGetPoststoGeoencode() {
        $dao = new PostMySQLDAO();
        $posts = $dao->getPoststoGeoencode();
        $this->assertEqual(count($posts), 204);
        $this->assertIsA($posts, "array");
    }

    /**
     * Test setGeoencodedPost
     */
    public function testSetGeoencodedPost() {
        $dao = new PostMySQLDAO();
        $result = $dao->setGeoencodedPost(131, 'twitter', 1);
        //already set to 1
        $this->assertFalse($result);
        $post = $dao->getPost(131, 'twitter');
        $this->assertEqual($post->is_geo_encoded, 1);
        $this->assertEqual($post->reply_retweet_distance, 0);

        $result = $dao->setGeoencodedPost(131, 'twitter', 1, 'New Delhi', '78', 100);
        $this->assertTrue($result);
        $post = $dao->getPost(131, 'twitter');
        $this->assertEqual($post->is_geo_encoded, 1);
        $this->assertEqual($post->geo, 78);
        $this->assertEqual($post->location, 'New Delhi');
        $this->assertEqual($post->reply_retweet_distance, 100);

        //Since both of $location and $geodata are not defined, only is_geo_encoded field is updated
        $result = $dao->setGeoencodedPost(131, 'twitter', 2, '', 29);
        $this->assertTrue($result);
        $post = $dao->getPost(131, 'twitter');
        $this->assertEqual($post->is_geo_encoded, 2);
        $this->assertEqual($post->geo, '78');
        $this->assertEqual($post->location, 'New Delhi');

        //Since both of $location and $geodata are not defined, only is_geo_encoded field is updated
        $result = $dao->setGeoencodedPost(131, 'twitter', 1, 'Dwarka');
        $this->assertTrue($result);
        $post = $dao->getPost(131, 'twitter');
        $this->assertEqual($post->geo, '78');
        $this->assertEqual($post->location, 'New Delhi');

        //bad post ID
        $result = $dao->setGeoencodedPost('1314452345243', 'twitter', 1, 'Dwarka');
        $this->assertFalse($result);

        //bad network
        $result = $dao->setGeoencodedPost('131', 'testnetwork', 1, 'Dwarka');
        $this->assertFalse($result);
    }

    /**
     * Test getClientsUsedByUserOnNetwork
     */
    public function testGetClientsUsedByUserOnNetwork() {
        $dao = new PostMySQLDAO();
        list($all_time_clients_usage, $latest_clients_usage) = $dao->getClientsUsedByUserOnNetwork(13, 'twitter');
        $this->assertIsA($all_time_clients_usage, 'array');
        $this->assertEqual(sizeof($all_time_clients_usage), 3);
        $this->assertEqual($all_time_clients_usage['Tweetie for Mac'], 13);
        $this->assertEqual($all_time_clients_usage['web'], 14);
        $this->assertEqual($all_time_clients_usage['Tweet Button'], 13);
        $keys = array_keys($all_time_clients_usage);
        $this->assertEqual($keys[2], 'Tweetie for Mac');
        $this->assertEqual($keys[0], 'web');
        $this->assertEqual($keys[1], 'Tweet Button');

        $this->assertIsA($latest_clients_usage, 'array');
        $this->assertEqual(sizeof($latest_clients_usage), 3);
        $this->assertEqual($latest_clients_usage['Tweetie for Mac'], 8);
        $this->assertEqual($latest_clients_usage['web'], 9);
        $this->assertEqual($latest_clients_usage['Tweet Button'], 8);
        $keys = array_keys($latest_clients_usage);
        $this->assertEqual($keys[0], 'web');
        $this->assertEqual($keys[1], 'Tweet Button');
        $this->assertEqual($keys[2], 'Tweetie for Mac');
    }

    /**
     * test adding a dup, with the IGNORE modifier, check the result.
     * Set counter higher to avoid clashes w/ prev post inserts.
     */
    public function testUniqueConstraint1() {
        $config = Config::getInstance();
        $config_array = $config->getValuesArray();
        $counter = 1000;
        $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
        $source = '<a href="http://twitter.com" rel="nofollow">Tweetie for Mac</a>';
        $q = "INSERT IGNORE INTO " . $config_array['table_prefix'] .
        "posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache, network) VALUES
        ($counter, 13, 'ev', 'Ev Williams', 'avatar.jpg',
        'This is post $counter', '$source', '2006-01-01 00:$pseudo_minute:00', ".rand(0, 4).", 5, 'twitter');";
        $res = PDODAO::$PDO->exec($q);
        $this->assertEqual($res, 1);

        $q = "INSERT IGNORE INTO " . $config_array['table_prefix'] .
        "posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache, network) VALUES
        ($counter, 13, 'ev', 'Ev Williams', 'avatar.jpg',
        'This is post $counter', '$source', '2006-01-01 00:$pseudo_minute:00', ".rand(0, 4).", 5, 'twitter');";
        $res = PDODAO::$PDO->exec($q);
        $this->assertEqual($res, 0);
    }

    /**
     * test adding a dup w/out the IGNORE modifier; should throw exception on second insert
     */
    public function testUniqueConstraint2() {
        $counter = 1002;
        $pseudo_minute = str_pad(($counter-1000), 2, "0", STR_PAD_LEFT);
        $source = '<a href="http://twitter.com" rel="nofollow">Tweetie for Mac</a>';
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>$counter, 'author_user_id'=>13,
        'author_username'=>'ev', 'author_fullname'=>'Ev Williams', 'author_avatar'=>'avatar.jpg',
        'post_text'=>'This is post'.$counter, 'source'=>$source, 'pub_date'=>'2006-01-01 00:'.$pseudo_minute.':00',
        'reply_count_cache'=>rand(0, 4), 'retweet_count_cache'=>5, 'network'=>'twitter'));

        try {
            $builders[] = FixtureBuilder::build('posts', array('post_id'=>$counter, 'author_user_id'=>13,
            'author_username'=>'ev', 'author_fullname'=>'Ev Williams', 'author_avatar'=>'avatar.jpg',
            'post_text'=>'This is post'.$counter, 'source'=>$source, 'pub_date'=>'2006-01-01 00:'.$pseudo_minute.':00',
            'reply_count_cache'=>rand(0, 4), 'retweet_count_cache'=>5, 'network'=>'twitter'));
        } catch(PDOException $e) {
            $this->assertPattern('/Integrity constraint violation/', $e->getMessage());
        }
    }

    public function testGetUserPostsInRange() {
        $dao = new PostMySQLDAO();
        $posts = $dao->getPostsByUserInRange(18, 'twitter', $from = '2006-01-02 00:00:00',
        $until = '2006-01-02 00:30:59',  $order_by="pub_date", $direction="DESC", $iterator=false,
        $is_public = false);

        // test date ordering and time range check
        $date = strtotime($posts[0]->pub_date);
        foreach($posts as $post) {
            $this->assertEqual($post->author_user_id, 18);
            $this->assertTrue(strtotime($post->pub_date) >= strtotime('2006-01-02 00:00:00'));
            $this->assertTrue(strtotime($post->pub_date) < strtotime('2006-01-02 00:30:59'));
            $this->assertTrue(strtotime($post->pub_date) <= $date);
            $date = strtotime($post->pub_date);
        }

        // test ascending order
        $posts = $dao->getPostsByUserInRange(18, 'twitter', $from = '2006-01-02 00:00:00',
        $until = '2006-01-02 00:30:59',  $order_by="pub_date", $direction="ASC", $iterator=false,
        $is_public = false);

        $date = strtotime($posts[0]->pub_date);
        foreach($posts as $post) {
            $this->assertTrue(strtotime($post->pub_date) >= $date);
            $date = strtotime($post->pub_date);
        }

        // test filter protected posts
        $posts = $dao->getPostsByUserInRange(18, 'twitter', $from = '2006-01-02 00:00:00',
        $until = '2006-01-02 00:59:59',  $order_by="pub_date", $direction="DESC", $iterator=false,
        $is_public = true);

        foreach($posts as $post) {
            $this->assertEqual($post->is_protected, false);
        }

        // test range with no posts
        $posts = $dao->getPostsByUserInRange(18, 'twitter', $from = '1970-01-02 00:00:00',
        $until = '1971-01-02 00:59:59',  $order_by="pub_date", $direction="DESC", $iterator=false,
        $is_public = true);

        $this->assertEqual(sizeof($posts), 0);

        // test from greater than until
        $posts = $dao->getPostsByUserInRange(18, 'twitter', $from = '2008-01-02 00:00:00',
        $until = '2006-01-02 00:59:59',  $order_by="pub_date", $direction="DESC", $iterator=false,
        $is_public = true);

        $this->assertEqual(sizeof($posts), 0);

        // test post with links
        $posts = $dao->getPostsByUserInRange(19, 'twitter', $from = '2006-03-01 00:01:00',
        $until = '2006-03-01 00:01:01', $order_by="pub_date", $direction="DESC", $iterator=false,
        $is_public = true);
        foreach ($posts as $post) {
            $this->assertEqual(sizeof($post->links), 1);
        }

        // test post with no links
        $posts = $dao->getPostsByUserInRange(18, 'twitter', $from = '2006-03-01 00:00:00',
        $until = '2006-03-01 00:00:01', $order_by="pub_date", $direction="DESC", $iterator=false,
        $is_public = true);
        foreach ($posts as $post) {
            $this->assertEqual(sizeof($post->links), 0);
        }
    }

    /**
     * This method tests basic handling of the data structures generated by the json parser, including entity and
     * user information. In add'n, it tests new-style rt handling.
     */
    public function testAddPostAndAssociatedInfo() {
        list($post, $entities, $user_array) = $this->buildStreamPostArray1();
        $dao = new PostMySQLDAO();
        $dao->addPostAndAssociatedInfo($post, $entities, $user_array);

        $post_orig = $dao->getPost('39088587140108288', 'twitter');
        $this->assertEqual($post_orig->post_text,
        '@joanwalsh RT @AntDeRosa Hillary #Clinton provides perhaps the best argument defending Planned ' .
        'Parenthood (Video, 2009) http://j.mp/eZbWh0');
        $this->assertEqual($post_orig->post_id, '39088587140108288');
        $this->assertEqual($post_orig->retweet_count_cache, 1);
        $this->assertEqual($post_orig->old_retweet_count_cache, 0);
        $this->assertEqual($post_orig->in_retweet_of_post_id, null);

        $post_rt = $dao->getPost('39089424620978176', 'twitter');
        $this->assertEqual($post_rt->post_id, '39089424620978176');
        $this->assertEqual($post_rt->post_text, 'RT @HeyJacquiDey: @joanwalsh RT @AntDeRosa ' .
        'Hillary #Clinton provides perhaps the best argument defending Planned Parenthood (Video, 2009) ...');
        $this->assertEqual($post_rt->retweet_count_cache, 0);
        $this->assertEqual($post_rt->old_retweet_count_cache, 0);
        $this->assertEqual($post_rt->in_retweet_of_post_id, '39088587140108288');
        $this->assertEqual($post_rt->in_rt_of_user_id, '136881432');
        $hashtag_dao = new HashtagMySQLDAO();
        $hashtagpost_dao = new HashtagPostMySQLDAO();
        $m_dao = new MentionMySQLDAO();
        $h = $hashtag_dao->getHashtag('Clinton', 'twitter');
        $this->assertEqual($h->count_cache, 2);
        $hp = $hashtagpost_dao->getHashtagsForPost('39088587140108288', 'twitter');
        $this->assertEqual(sizeof($hp), 1);
        $this->assertEqual($hp[0]['post_id'], '39088587140108288');
        $hp = $hashtagpost_dao->getHashtagsForPost('39089424620978176', 'twitter');
        $this->assertEqual($hp[0]['post_id'], '39089424620978176');
        $this->assertEqual($hp[0]['hashtag_id'], 3);
        $hph = $hashtagpost_dao->getHashtagPostsByHashtagID(3);
        $this->assertEqual(sizeof($hph), 2);
        $this->assertEqual($hph[1]['post_id'], '39089424620978176');

        $m = $m_dao->getMentionInfoUserName('joanwalsh');
        $this->assertEqual($m['count_cache'], 2);
        $mp = $m_dao->getMentionsForPost('39089424620978176');
        $this->assertEqual(sizeof($mp), 3);
        $this->assertEqual($mp[1]['mention_id'], 2);
        $mpm = $m_dao->getMentionsForPostMID(2);
        $this->assertEqual(sizeof($mpm), 2);
        $this->assertEqual($mpm[0]['post_id'], '39088587140108288');
    }

    /**
     * This test checks 'place' information handling
     */
    public function testAddPostAndAssociatedInfoPlace() {
        list($post, $entities, $user_array) = $this->buildStreamPostArray2();
        $dao = new PostMySQLDAO();
        $dao->addPostAndAssociatedInfo($post, $entities, $user_array);
        $post = $dao->getPost('39451255650648064', 'twitter');
        $this->assertEqual($post->place_id, '1a16a1d70500c27d');
        $p_dao = new PlaceMySQLDAO();
        $pinfo = $p_dao->getPlaceByID('1a16a1d70500c27d');
        $this->assertEqual($pinfo['bounding_box'],
        'POLYGON((-97.73818308 30.29930703,-97.710741 30.29930703,-97.710741 30.31480602,-97.73818308 ' .
        '30.31480602,-97.73818308 30.29930703))');
        // $this->assertEqual($pinfo['longlat'], 'POINT(-97.72446204 30.307056525)');
        // due to rounding diffs, do 'contains' tests rather than string equality.
        // The result should be approx that of above
        $this->assertPattern('/POINT\(-97.72446/', $pinfo['longlat']);
        $this->assertPattern('/ 30.3070565/', $pinfo['longlat']);
        $this->assertEqual($pinfo['place_id'], '1a16a1d70500c27d');
        $ploc = $p_dao->getPostPlace('39451255650648064');
        $this->assertEqual($ploc['place_id'], '1a16a1d70500c27d');
        $this->assertEqual($ploc['post_id'], '39451255650648064');
        $this->assertEqual($ploc['longlat'], 'POINT(-97.723366 30.296095)');
    }

    /**
     * Same as above, but no point coord info
     */
    public function testAddPostAndAssociatedInfoPlaceBBOnly() {
        list($post, $entities, $user_array) = $this->buildStreamPostArray2();
        // remove the point coord info-- post data now has just bounding box info
        unset($entities['place']['point_coords']);
        $dao = new PostMySQLDAO();
        $dao->addPostAndAssociatedInfo($post, $entities, $user_array);
        $post = $dao->getPost('39451255650648064', 'twitter');
        $this->assertEqual($post->place_id, '1a16a1d70500c27d');
        $p_dao = new PlaceMySQLDAO();
        $pinfo = $p_dao->getPlaceByID('1a16a1d70500c27d');
        $this->assertEqual($pinfo['bounding_box'],
        'POLYGON((-97.73818308 30.29930703,-97.710741 30.29930703,-97.710741 30.31480602,-97.73818308 ' .
        '30.31480602,-97.73818308 30.29930703))');
        // $this->assertEqual($pinfo['longlat'], 'POINT(-97.72446204 30.307056525)');
        // due to rounding diffs, do 'contains' tests rather than string equality.
        // The result should be approx that of above
        $this->assertPattern('/POINT\(-97.72446/', $pinfo['longlat']);
        $this->assertPattern('/ 30.3070565/', $pinfo['longlat']);
        $this->assertEqual($pinfo['place_id'], '1a16a1d70500c27d');
        $ploc = $p_dao->getPostPlace('39451255650648064');
        $this->assertEqual($ploc, null);
    }

    /**
     * This test checks 'old-style' rt handling and mentions
     */
    public function testAddPostOldStyleRTNoPostID() {
        list($post, $entities, $user_array) = $this->buildStreamPostArray3();
        $dao = new PostMySQLDAO();
        $user_dao = new UserMySQLDAO();
        $dao->addPostAndAssociatedInfo($post, $entities, $user_array);
        $post = $dao->getPost('39088041628934144', 'twitter');
        $this->assertEqual($post->in_rt_of_user_id, 40025121);
        $user = $user_dao->getDetails(140955302, 'twitter');
        $this->assertEqual($user->username, 'DAaronovitch');
        $m_dao = new MentionMySQLDAO();
        $mention = $m_dao->getMentionInfoUserID(40025121);
        $mp = $m_dao->getMentionsForPost('39088041628934144');
        $this->assertTrue($mention != null);
        $this->assertEqual($mention['user_name'], 'RSAMatthew');
        $this->assertEqual($mention['count_cache'], 1);
        $this->assertEqual($mp[0]['author_user_id'], 140955302);
    }

    /**
     * test of getPostsByFriends method
     */
    public function testGetPostsByFriends() {
        $builders = array();
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>13, 'follower_id'=>18));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>19, 'follower_id'=>18));
        $dao = new PostMySQLDAO();
        $res = $dao->getPostsByFriends(18, 'twitter', 10);
        $this->assertEqual(count($res), 10);
        $this->assertEqual($res[0]->author_user_id,13);
        $this->assertEqual($res[1]->author_user_id,19);
    }

    /**
     * test of getPostsToUser method
     */
    public function testGetPostsToUser() {
        $dao = new PostMySQLDAO();
        $res = $dao->getPostsToUser('23', 'twitter', 10);
        $this->assertEqual(count($res), 2);
        $this->assertEqual($res[0]->author_user_id,'20');
        $this->assertEqual(sizeof($res[0]->links), 2);
        $this->assertEqual($res[0]->links[0]->url, 'http://alink1.com');
        $this->assertEqual($res[0]->links[1]->url, 'http://alink2.com');
        $this->assertEqual($res[1]->author_user_id,'20');
        $this->assertEqual(sizeof($res[1]->links), 0);
    }

    /**
     * test of getPostsByFriends method. Checking that protected posts aren't included when the
     * 'public' flag is set.
     */
    public function testGetPostsByFriends2() {
        $builders = array();
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'13', 'follower_id'=>'18'));
        $builders[] = FixtureBuilder::build('follows', array('user_id'=>'123456', 'follower_id'=>'18'));
        $dao = new PostMySQLDAO();

        $res = $dao->getPostsByFriends(18, 'twitter', 5, 1, false); // not public
        $this->assertEqual(count($res), 5);
        $this->assertEqual($res[0]->author_user_id, 13);

        $res = $dao->getPostsByFriends(18, 'twitter', 5, 1, true); // public
        $this->assertEqual(count($res), 5);
        $this->assertEqual($res[0]->author_user_id,13);
    }

    /**
     * Test update author_username on a series of posts
     */
    public function testUpdateAuthorUsernameByAuthorUserId() {
        $dao = new PostMySQLDAO();
        $post = $dao->getPost(10, 'twitter');
        $authorid = $post->author_user_id;

        // bad id
        $update_cnt = $dao->updateAuthorUsername(-99, 'twitter', 'newname');
        $this->assertEqual($update_cnt, 0);

        // bad network
        $update_cnt = $dao->updateAuthorUsername($authorid, 'no-net', 'newname');
        $this->assertEqual($update_cnt, 0);

        // good id
        $update_cnt = $dao->updateAuthorUsername($authorid, 'twitter', 'newname');
        $stmt = PostMySQLDAO::$PDO->query(
        "select * from " . $this->table_prefix . "posts where author_user_id = $authorid and network = 'twitter'");
        $data_returned = false;
        $cnt = 0;
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->assertEqual($row['author_username'], 'newname');
            $data_returned = true;
            $cnt++;
        }
        $stmt->closeCursor();
        $this->assertTrue($data_returned);
        $this->assertEqual($update_cnt, $cnt);
    }

    /**
     * Begin helper methods that simulate the data structures generated by the JSON parser, which can
     * grab post additional information from the stream API.
     */

    /**
     * Helper method to build a post with entities and user information associated with it.
     */
    private function buildStreamPostArray1() {
        $post = array (
            'in_rt_of_user_id' => 136881432,
            'post_id' => '39089424620978176',
            'author_user_id' => 1106501,
            'author_username' => 'joanwalsh',
            'author_fullname' => 'Joan Walsh',
            'author_avatar' => 'http://a3.twimg.com/profile_images/1190090715/JW_1_inch_high_normal.png',
            'author_follower_count' => 21786,
            'post_text' => 'RT @HeyJacquiDey: @joanwalsh RT @AntDeRosa Hillary #Clinton provides perhaps ' .
             'the best argument defending Planned Parenthood (Video, 2009) ...',
            'is_protected' => false,
            'source' => 'web',
            'location' => 'San Francisco',
            'description' => 'Editor-at-large, Salon.com',
            'url' => 'http://www.salon.com/opinion/walsh/',
            'author_friends_count' => 919,
            'author_post_count' => 6130,
            'author_joined' => '2007-03-13 18:38:05',
            'favorited' => null,
            'place' =>  null,
            'place_id' => null,
            'pub_date' => '2011-02-19 22:30:19',
            'in_reply_to_user_id' => null,
            'in_reply_to_post_id' => null,
            'network' => 'twitter',
            'reply_count_cache' => 0,
            'retweeted_post' => array (
               'content' => array (
                    'is_rt' => false,
                    'in_rt_of_user_id' => '',
                    'post_id' => '39088587140108288',
                    'author_user_id' => 136881432,
                    'author_username' => 'HeyJacquiDey',
                    'author_fullname' => 'Jacqueline Frances',
                    'author_avatar' => 'http://a0.twimg.com/profile_images/1216639266/lube_normal.jpg',
                    'author_follower_count' => 136,
                    'post_text' => '@joanwalsh RT @AntDeRosa Hillary #Clinton provides perhaps the best argument ' .
                        'defending Planned Parenthood (Video, 2009) http://j.mp/eZbWh0',
                    'is_protected' => false,
                    'source' => 'web',
                    'location' => 'The Space Between',
                    'description' => 'I love turkey bacon, TCM, Duke, Radiohead, and my cat. That is all.',
                    'url' => 'http://www.facebook.com/jacqueline.dey',
                    'author_friends_count' => 414,
                    'author_post_count' => 5364,
                    'author_joined' => '2010-04-25 05:04:38',
                    'favorited' => null,
                    'place' => null,
                    'place_id' => null,
                    'pub_date' => '2011-02-19 22:27:00',
                    'in_reply_to_user_id' => 1106501,
                    'in_reply_to_post_id' => null,
                    'network' => 'twitter',
                    'reply_count_cache' => 0,
                    'retweet_count_cache' => 0,
                    'retweet_count_api' => 1
        ),

                  'entities' => array (
                      'urls' => array ('http://j.mp/eZbWh0'),
                      'mentions' => array ( 0 => array (
                                      'user_id' => 1106501,
                                      'user_name' => 'joanwalsh'
                                      ),
                                      1 => array (
                                      'user_id' => 1140451,
                                      'user_name' => 'AntDeRosa'
                                      )),
                      'hashtags' => array ('Clinton'),
                      'place' => null,
                                      ),

                  'user_array' => array (
                      'user_id' => 136881432,
                      'user_name' => 'HeyJacquiDey',
                      'full_name' => 'Jacqueline Frances',
                      'avatar' => 'http://a0.twimg.com/profile_images/1216639266/lube_normal.jpg',
                      'follower_count' => 136,
                      'is_protected' => false,
                      'location' => 'The Space Between',
                      'description' => 'I love turkey bacon, TCM, Duke, Radiohead, and my cat. That is all.',
                      'friend_count' => 414,
                      'post_count' => 5364,
                      'joined' => '2010-04-25 05:04:38',
                      'url' => 'http://www.facebook.com/jacqueline.dey',
                      'network' => 'twitter',
                      'last_post' => '2011-02-19 22:27:00',
                      'last_post_id'=>'abc'
                      )
                      ),
            'in_retweet_of_post_id' => '39088587140108288'
            );
            $entities = array (
            'urls' => array ( ),
            'mentions' => array (
            0 => array (
                    'user_id' => 136881432,
                    'user_name' => 'HeyJacquiDey',
            ),
            1 => array (
                    'user_id' => 1106501,
                    'user_name' => 'joanwalsh',
            ),
            2 => array(
                    'user_id' => 1140451,
                    'user_name' => 'AntDeRosa'
                    )
                    ),
            'hashtags' => array ('Clinton'),
            'place' => null
                    );
                    $user_array = array (
            'user_id' => '1106501',
            'user_name' => 'joanwalsh',
            'full_name' => 'Joan Walsh',
            'avatar' => 'http://a3.twimg.com/profile_images/1190090715/JW_1_inch_high_normal.png',
            'follower_count' => 21786,
            'is_protected' => false,
            'location' => 'San Francisco',
            'description' => 'Editor-at-large, Salon.com',
            'friend_count' => 919,
            'post_count' => 6130,
            'joined' => '2007-03-13 18:38:05',
            'url' => 'http://www.salon.com/opinion/walsh/',
            'network' => 'twitter',
            'last_post' => '2011-02-19 22:30:19',
             'last_post_id'=>'abc'
             );
             return array($post, $entities, $user_array);
    }

    /**
     * Helper method to build a post with entities, including place, and user information associated with it
     */
    private function buildStreamPostArray2() {
        $post = array(
            'is_rt' => false,
            'in_rt_of_user_id' => '',
            'post_id' => '39451255650648064',
            'author_user_id' => 201709909,
            'author_username' => 'bob',
            'author_fullname' => 'bob dole',
            'author_avatar' => 'http://a0.twimg.com/profile_images/1245220365/prof_normal.png',
            'author_follower_count' => 14,
            'post_text' => 'Aurora Borealis over Norway : ' .
                'http://www.flickr.com/photos/tittentem/4501029462/lightbox/ (via @openculture)',
            'is_protected' => false,
            'source' => 'web',
            'location' => null,
            'description' => 'â€¦ ',
            'url' => 'http://www.therainforestsite.com',
            'author_friends_count' => 239,
            'author_post_count' => 26,
            'author_joined' => '2010-10-12 13:12:48',
            'favorited' => null,
            'place' => 'Hyde Park, Austin',
            'place_id' => '1a16a1d70500c27d',
            'pub_date' => '2011-02-20 22:28:06',
            'in_reply_to_user_id' => null,
            'in_reply_to_post_id' => null,
            'network' => 'twitter',
            'reply_count_cache' => 0,
            'retweet_count_cache' => 0,
            'retweet_count_api' => 0,
        );

        $entities = array (
            'urls' => array  ('http://www.flickr.com/photos/tittentem/4501029462/lightbox/'),
            'mentions' => array  (
        array  (
                      'user_id' => 19826509,
                      'user_name' => 'openculture'
                      )),
            'hashtags' => array(),
            'place' => array (
                'bounding_box' => array (
                    'type' => 'Polygon',
                    'coordinates' => array  (
                      array (
                      array(-97.73818308, 30.29930703),
                      array(-97.710741, 30.29930703),
                      array(-97.710741, 30.31480602),
                      array(-97.73818308, 30.31480602),
                      ))
                      ),
                    'country_code' => 'US',
                    'country' => 'United States',
                    'url' => 'http://api.twitter.com/1/geo/id/1a16a1d70500c27d.json',
                    'name' => 'Hyde Park',
                    'place_type' => 'neighborhood',
                    'attributes' => array(),
                    'id' => '1a16a1d70500c27d',
                    'full_name' => 'Hyde Park, Austin',
                    'point_coords' => array(
                         'type' => 'Point',
                         'coordinates' => array(-97.723366,30.296095)
                      )
                      )
                      );

                      $user_array = array (
            'user_id' => 201709909,
            'user_name' => 'bob',
            'full_name' => 'bob dole',
            'avatar' => 'http://a0.twimg.com/profile_images/1245220365/prof_normal.png',
            'follower_count' => 14,
            'is_protected' => false,
            'location' => null,
            'description' => 'â€¦ ',
            'friend_count' => 239,
            'post_count' => 26,
            'joined' => '2010-10-12 13:12:48',
            'url' => 'http://www.therainforestsite.com',
            'network' => 'twitter',
            'last_post' => '2011-02-20 22:28:06',
            'last_post_id'=>'abc'
            );
            return array($post, $entities, $user_array);
    }

    /**
     * Helper method to build a post with an original retweeted post associated with it.
     */
    private function buildStreamPostArray3() {
        $post = array (
            'is_rt' => 1,
            'in_rt_of_user_id' => 40025121,
            'post_id' => '39088041628934144',
            'author_user_id' => 140955302,
            'author_username' => 'DAaronovitch',
            'author_fullname' => 'David Aaronovitch',
            'author_avatar' => 'http://a2.twimg.com/profile_images/1145463210/75.manchester_normal.jpg',
            'author_follower_count' => 10377,
            'post_text' => 'RT @rsamatthew Short term #choices and long term well-being http://tinyurl.com/4qwvfzq',
            'is_protected' => false,
            'source' => '<a href="http://tweetmeme.com" rel="nofollow">TweetMeme</a>',
            'location' => 'beside my Macbook',
            'description' => 'Times columnist, broadcaster, dad, dog-owner and author of Voodoo Histories',
            'url' => 'http://www.davidaaronovitch.com/',
            'author_friends_count' => 465,
            'author_post_count' => 7064,
            'author_joined' => '2010-05-06 20:12:34',
            'favorited' => null,
            'place' => null,
            'place_id' => null,
            'pub_date' => '2011-02-19 22:24:50',
            'in_reply_to_user_id' => null,
            'in_reply_to_post_id' => null,
            'network' => 'twitter',
            'reply_count_cache' => 0,
            'retweet_count_cache' => 0,
            'retweet_count_api' => 0
        );
        $entities = array (
            'urls' => array ('http://tinyurl.com/4qwvfzq'),
            'mentions' => array (
        array (
                            'user_id' => 40025121,
                            'user_name' => 'RSAMatthew'
                            ),
                            ),
            'hashtags' => array('choices'),
            'place' => null
                            );
                            $user_array = array (
            'user_id' => '140955302',
            'user_name' => 'DAaronovitch',
            'full_name' => 'David Aaronovitch',
            'avatar' => 'http://a2.twimg.com/profile_images/1145463210/75.manchester_normal.jpg',
            'follower_count' => 10377,
            'is_protected' => false,
            'location' => 'beside my Macbook',
            'description' => 'Times columnist, broadcaster, dad, dog-owner and author of Voodoo Histories',
            'friend_count' => 465,
            'post_count' => 7064,
            'joined' => '2010-05-06 20:12:34',
            'url' => 'http://www.davidaaronovitch.com/',
            'network' => 'twitter',
            'last_post' => '2011-02-19 22:24:50',
            'last_post_id'=>'abc'
            );
            return array($post, $entities, $user_array);
    }

    private function buildStreamPostArray4() {
        $post = json_decode('{
           "post_id":"462955667167662080",
           "author_user_id":"12145232",
           "user_id":"12145232",
           "pub_date":"2014-05-04 14:03:27",
           "post_text":"RT @pourmecoffee: Twenty-eight men answered Shackleton\'s Antarctic expedition ad http:\/\/t.co\/uGLsKU8Qkc http:\/\/t.co\/wpdAD4iQzB",
           "author_username":"CDMoyer",
           "user_name":"CDMoyer",
           "in_reply_to_user_id":"",
           "author_avatar":"http:\/\/pbs.twimg.com\/profile_images\/421641487051272192\/IfEg0PgG_normal.jpeg",
           "avatar":"http:\/\/pbs.twimg.com\/profile_images\/421641487051272192\/IfEg0PgG_normal.jpeg",
           "in_reply_to_post_id":"",
           "author_fullname":"Chris Moyer",
           "full_name":"Chris Moyer",
           "source":"<a href=\"http:\/\/twitter.com\/download\/iphone\" rel=\"nofollow\">Twitter for iPhone<\/a>",
           "location":"Buffalo, NY",
           "url":"http:\/\/t.co\/8NCBQ5WBQU",
           "description":"I love building things on the internet. \nFan of books, video games, music. \nI might tweet puppy or kid pics.",
           "is_verified":0,
           "is_protected":0,
           "follower_count":443,
           "post_count":937,
           "geo":"",
           "place":"",
           "friend_count":245,
           "joined":"2008-01-12 05:50:44",
           "favorites_count":169,
           "favlike_count_cache":0,
           "network":"twitter",
           "retweeted_post":{
              "content":{
                 "post_id":"462748167357091840",
                 "author_user_id":"16906137",
                 "user_id":"16906137",
                 "pub_date":"2014-05-04 00:18:55",
                 "post_text":"Twenty-eight men answered Shackleton\'s Antarctic expedition ad http:\/\/t.co\/uGLsKU8Qkc http:\/\/t.co\/wpdAD4iQzB",
                 "author_username":"pourmecoffee",
                 "user_name":"pourmecoffee",
                 "in_reply_to_user_id":"",
                 "author_avatar":"http:\/\/pbs.twimg.com\/profile_images\/421566216\/coffee1242220886_normal.jpg",
                 "avatar":"http:\/\/pbs.twimg.com\/profile_images\/421566216\/coffee1242220886_normal.jpg",
                 "in_reply_to_post_id":"",
                 "author_fullname":"pourmecoffee",
                 "full_name":"pourmecoffee",
                 "source":"<a href=\"https:\/\/about.twitter.com\/products\/tweetdeck\" rel=\"nofollow\">TweetDeck<\/a>",
                 "location":"USA",
                 "url":"http:\/\/t.co\/FzcppA8OBt",
                 "description":"Muttering sarcasm to power http:\/\/t.co\/CD3GxwBQD8",
                 "is_verified":1,
                 "is_protected":0,
                 "follower_count":161508,
                 "post_count":32683,
                 "geo":"",
                 "place":"",
                 "friend_count":1176,
                 "joined":"2008-10-22 14:33:38",
                 "favorites_count":0,
                 "favlike_count_cache":431,
                 "network":"twitter",
                 "retweet_count_api":556,
                 "photos":[
                    {
                       "id":462748165783822340,
                       "id_str":"462748165783822336",
                       "indices":[
                          86,
                          108
                       ],
                       "media_url":"http:\/\/pbs.twimg.com\/media\/BmwDAUoCIAAM_H4.jpg",
                       "media_url_https":"https:\/\/pbs.twimg.com\/media\/BmwDAUoCIAAM_H4.jpg",
                       "url":"http:\/\/t.co\/wpdAD4iQzB",
                       "display_url":"pic.twitter.com\/wpdAD4iQzB",
                       "expanded_url":"http:\/\/twitter.com\/pourmecoffee\/status\/462748167357091840\/photo\/1",
                       "type":"photo",
                       "sizes":{
                          "large":{
                             "w":464,
                             "h":269,
                             "resize":"fit"
                          },
                          "thumb":{
                             "w":150,
                             "h":150,
                             "resize":"crop"
                          },
                          "medium":{
                             "w":464,
                             "h":269,
                             "resize":"fit"
                          },
                          "small":{
                             "w":340,
                             "h":197,
                             "resize":"fit"
                          }
                       }
                    }
                 ]
              }
           },
           "in_retweet_of_post_id":"462748167357091840",
           "in_rt_of_user_id":"16906137",
           "photos":[
              {
                 "id":462748165783822340,
                 "id_str":"462748165783822336",
                 "indices":[
                    104,
                    126
                 ],
                 "media_url":"http:\/\/pbs.twimg.com\/media\/BmwDAUoCIAAM_H4.jpg",
                 "media_url_https":"https:\/\/pbs.twimg.com\/media\/BmwDAUoCIAAM_H4.jpg",
                 "url":"http:\/\/t.co\/wpdAD4iQzB",
                 "display_url":"pic.twitter.com\/wpdAD4iQzB",
                 "expanded_url":"http:\/\/twitter.com\/pourmecoffee\/status\/462748167357091840\/photo\/1",
                 "type":"photo",
                 "sizes":{
                    "large":{
                       "w":464,
                       "h":269,
                       "resize":"fit"
                    },
                    "thumb":{
                       "w":150,
                       "h":150,
                       "resize":"crop"
                    },
                    "medium":{
                       "w":464,
                       "h":269,
                       "resize":"fit"
                    },
                    "small":{
                       "w":340,
                       "h":197,
                       "resize":"fit"
                    }
                 },
                 "source_status_id":462748167357091840,
                 "source_status_id_str":"462748167357091840"
              }
           ]
        }', true);
        return $post;
    }

    public function testOfLinksInRetweet() {
        $post = self::buildStreamPostArray4();
        $dao = DAOFactory::getDAO('PostDAO');
        $id = $dao->addPost($post);

        $result = $dao->getPost($post['post_id'], 'twitter');
        $this->assertEqual(0, count($result->links));
        $this->assertEqual('462748167357091840', $result->in_retweet_of_post_id);

        $result = $dao->getPost('462748167357091840', 'twitter');
        $this->assertEqual(2, count($result->links));
        $this->assertEqual('http://t.co/uGLsKU8Qkc', $result->links[0]->url);
        $this->assertEqual('', $result->links[0]->image_src);
        $this->assertEqual('http://t.co/wpdAD4iQzB', $result->links[1]->url);
        $this->assertEqual('http://pbs.twimg.com/media/BmwDAUoCIAAM_H4.jpg', $result->links[1]->image_src);
    }

    public function testUpdateFavLikeCount() {
        $dao = new PostMySQLDAO();
        $post = $dao->getPost(10, 'twitter');
        $this->assertEqual($post->favlike_count_cache, 0);

        // bad id
        $update_cnt = $dao->updateFavLikeCount(-99, 'twitter', 25);
        $this->assertEqual($update_cnt, 0);

        // bad network
        $update_cnt = $dao->updateFavLikeCount(10, 'no-net', 25);
        $this->assertEqual($update_cnt, 0);

        // good id
        $update_cnt = $dao->updateFavLikeCount(10, 'twitter', 25);
        $this->assertEqual($update_cnt, 1);

        $post = $dao->getPost(10, 'twitter');
        $this->assertEqual($post->favlike_count_cache, 25);
    }

    public function testUpdateReplyCount() {
        $dao = new PostMySQLDAO();
        $post = $dao->getPost(10, 'twitter');
        $this->assertEqual($post->reply_count_cache, 0);

        // bad id
        $update_cnt = $dao->updateReplyCount(-99, 'twitter', 25);
        $this->assertEqual($update_cnt, 0);

        // bad network
        $update_cnt = $dao->updateReplyCount(10, 'no-net', 25);
        $this->assertEqual($update_cnt, 0);

        // good id
        $update_cnt = $dao->updateReplyCount(10, 'twitter', 25);
        $this->assertEqual($update_cnt, 1);

        $post = $dao->getPost(10, 'twitter');
        $this->assertEqual($post->reply_count_cache, 25);
    }

    public function testUpdateRetweetCount() {
        $dao = new PostMySQLDAO();
        $post = $dao->getPost(10, 'twitter');
        $this->assertEqual($post->retweet_count_cache, 5);

        // bad id
        $update_cnt = $dao->updateRetweetCount(-99, 'twitter', 25);
        $this->assertEqual($update_cnt, 0);

        // bad network
        $update_cnt = $dao->updateRetweetCount(10, 'no-net', 25);
        $this->assertEqual($update_cnt, 0);

        // good id
        $update_cnt = $dao->updateRetweetCount(10, 'twitter', 25);
        $this->assertEqual($update_cnt, 1);

        $post = $dao->getPost(10, 'twitter');
        $this->assertEqual($post->retweet_count_cache, 25);
    }

    public function testUpdatePostText() {
        $dao = new PostMySQLDAO();
        $post = $dao->getPost(10, 'twitter');
        $this->assertEqual($post->post_text, "This is post 10");

        // bad id
        $update_cnt = $dao->updatePostText(-99, 'twitter', 'updated post text');
        $this->assertEqual($update_cnt, 0);

        // bad network
        $update_cnt = $dao->updatePostText(10, 'no-net', 'updated post text');
        $this->assertEqual($update_cnt, 0);

        // good id
        $update_cnt = $dao->updatePostText(10, 'twitter', 'This is updated post 10');
        $this->assertEqual($update_cnt, 1);

        $post = $dao->getPost(10, 'twitter');
        $this->assertEqual($post->post_text, 'This is updated post 10');
    }

    public function testGetOnThisDayFlashbackPostsNoFromDate(){
        // Generate the date string for 1 year ago today
        $year_ago_date = date(date( 'Y-m-d 10:10:10' , strtotime("today -1 year")));

        // Add a post from a year ago that's not a reply or retweet
        $post_builder = FixtureBuilder::build('posts', array('post_id'=>'150', 'author_user_id'=>'20',
        'author_username'=>'user1', 'author_fullname'=>'User 1', 'network'=>'foursquare',
        'post_text'=>'I just checked in', 'source'=>'', 'pub_date'=>$year_ago_date, 'location'=>'England',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'place'=>'The Park', 'place_id'=>'12345a',
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'foursquare',
        'in_reply_to_user_id' =>null, 'in_reply_to_post_id' => null, 'in_retweet_of_post_id'=>null,
        'geo'=>'52.477192843264,-1.484333726346'));

        $post_key = $post_builder->columns['last_insert_id'];
        // Add a link for this post
        $link_builder = FixtureBuilder::build('links', array('post_key'=>$post_key, 'url'=>'http://bit.ly/blah'));

        // Add a post from 2 years ago that's not a reply or retweet
        $two_years_ago_date = date(date( 'Y-m-d 11:11:11' , strtotime("today -2 year")));
        $post_builder2 = FixtureBuilder::build('posts', array('post_id'=>'151', 'author_user_id'=>'20',
        'author_username'=>'user1', 'author_fullname'=>'User 1', 'network'=>'foursquare',
        'post_text'=>'I just checked in', 'source'=>'', 'pub_date'=>$two_years_ago_date, 'location'=>'England',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'place'=>'The Park', 'place_id'=>'12345a',
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'foursquare',
        'in_reply_to_user_id' =>null, 'in_reply_to_post_id' => null, 'in_retweet_of_post_id'=>null,
        'geo'=>'52.477192843264,-1.484333726346'));

        $post_key = $post_builder2->columns['last_insert_id'];
        // Add a link for this post
        $link_builder2 = FixtureBuilder::build('links', array('post_key'=>$post_key, 'url'=>'http://bit.ly/blahb'));

        // Add a post from today that's not a reply or retweet
        $today_date = date( 'Y-m-d 09:00:09' , strtotime("today"));

        $post_builder3 = FixtureBuilder::build('posts', array('post_id'=>'152', 'author_user_id'=>'20',
        'author_username'=>'user1', 'author_fullname'=>'User 1', 'network'=>'foursquare',
        'post_text'=>'I just checked in', 'source'=>'', 'pub_date'=>$today_date, 'location'=>'England',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'place'=>'The Park', 'place_id'=>'12345a',
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'foursquare',
        'in_reply_to_user_id' =>null, 'in_reply_to_post_id' => null, 'in_retweet_of_post_id'=>null,
        'geo'=>'52.477192843264,-1.484333726346'));

        $post_key = $post_builder3->columns['last_insert_id'];
        // Add a link for this post
        $link_builder3 = FixtureBuilder::build('links', array('post_key'=>$post_key, 'url'=>'http://bit.ly/blahb'));

        // Add the place information
        $place['place_id'] = '12345a';
        $place['place_type'] = "Park";
        $place['name'] = "A Park";
        $place['full_name'] = "The Greatest Park";
        $place['country_code'] = "UK";
        $place['country'] = "United Kingdom";
        $place['icon'] = "http://www.iconlocation.com";
        $place['network'] = "foursquare";
        $place['longlat'] = "GeometryFromText( 'Point(51.514 -0.1167)' )";
        $place['bounding_box'] = "PolygonFromText( 'Polygon(-0.213503 51.512805,-0.105303 51.512805,".
        "-0.105303 51.572068,-0.213503 51.572068, -0.213503 51.512805)')";
        $place['map_image'] = "http://www.mapimage.com";

        // Insert the place
        $place_builder = FixtureBuilder::build('places', $place);

        // Query the database for last year's post
        $post_dao = new PostMySQLDAO();
        // Get the year to query for
        $res = $post_dao->getOnThisDayFlashbackPosts(20, 'foursquare');

        // Check only the 1 checkin we inserted is returned
        $this->assertEqual(sizeof($res), 2);
        // Check the author user id was set correctly
        $this->assertEqual($res[0]->author_user_id, '20');
        // Check the username was set correctly
        $this->assertEqual($res[0]->author_username, 'user1');
        // Check the author fullname was set correctly
        $this->assertEqual($res[0]->author_fullname, 'User 1');
        // Check the network was set correctly
        $this->assertEqual($res[0]->network, 'foursquare');
        // Check the post text was set correctly
        $this->assertEqual($res[0]->post_text, 'I just checked in');
        // Check the pub date was set correctly
        $this->assertEqual($res[0]->pub_date, $two_years_ago_date);
        // Check the location was set correctly
        $this->assertEqual($res[0]->location, 'England');
        // Check the place was set correctly
        $this->assertEqual($res[0]->place, 'The Park');
        // Check the place id was set correctly
        $this->assertEqual($res[0]->place_id, '12345a');
        // Check the geo co ordinates were set correctly
        $this->assertEqual($res[0]->geo, '52.477192843264,-1.484333726346');

        // Check the place id was set correctly
        $this->assertEqual($res[0]->place_obj->place_id, '12345a');
        // Check the place type was set correctly
        $this->assertEqual($res[0]->place_obj->place_type, 'Park');
        // Check the place name was set correctly
        $this->assertEqual($res[0]->place_obj->name, 'A Park');
        // Check the full name was set correctly
        $this->assertEqual($res[0]->place_obj->full_name, 'The Greatest Park');
        // Check the country code was set correctly
        $this->assertEqual($res[0]->place_obj->country_code, 'UK');
        // Check the country was set correctly
        $this->assertEqual($res[0]->place_obj->country, 'United Kingdom');
        // Check the icon was set correctly
        $this->assertEqual($res[0]->place_obj->icon, 'http://www.iconlocation.com');
        // Check the map image was set correctly
        $this->assertEqual($res[0]->place_obj->map_image, 'http://www.mapimage.com');

        // Check the link URL was set correctly
        $this->assertEqual($res[0]->links[0]->url, 'http://bit.ly/blahb');
    }

    public function testGetOnThisDayFlashbackPostsWithFromDate(){
        // Generate the date string for 1 year and 1 day ago today
        $year_and_day_ago_date = date(date( 'Y-m-d H:i:s' , strtotime("today -1 day", strtotime("today -1 year"))));

        // Add a post from a year ago that's not a reply or retweet
        $post_builder = FixtureBuilder::build('posts', array('post_id'=>'150', 'author_user_id'=>'20',
        'author_username'=>'user1', 'author_fullname'=>'User 1', 'network'=>'foursquare',
        'post_text'=>'I just checked in', 'source'=>'', 'pub_date'=>$year_and_day_ago_date, 'location'=>'England',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'place'=>'The Park', 'place_id'=>'12345a',
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'foursquare',
        'in_reply_to_user_id' =>null, 'in_reply_to_post_id' => null, 'in_retweet_of_post_id'=>null,
        'geo'=>'52.477192843264,-1.484333726346'));

        $post_key = $post_builder->columns['last_insert_id'];
        // Add a link for this post
        $link_builder = FixtureBuilder::build('links', array('post_key'=>$post_key, 'url'=>'http://bit.ly/blah'));

        // Add a post from 2 years ago that's not a reply or retweet
        $two_years_and_day_ago_date = date( 'Y-m-d H:i:s' , strtotime('today -1 day', strtotime("today -2 year")));
        $post_builder2 = FixtureBuilder::build('posts', array('post_id'=>'151', 'author_user_id'=>'20',
        'author_username'=>'user1', 'author_fullname'=>'User 1', 'network'=>'foursquare',
        'post_text'=>'I just checked in', 'source'=>'', 'pub_date'=>$two_years_and_day_ago_date, 'location'=>'England',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'place'=>'The Park', 'place_id'=>'12345a',
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'foursquare',
        'in_reply_to_user_id' =>null, 'in_reply_to_post_id' => null, 'in_retweet_of_post_id'=>null,
        'geo'=>'52.477192843264,-1.484333726346'));

        $post_key = $post_builder2->columns['last_insert_id'];
        // Add a link for this post
        $link_builder2 = FixtureBuilder::build('links', array('post_key'=>$post_key, 'url'=>'http://bit.ly/blahb'));

        // Add a post from today that's not a reply or retweet
        $yesterday_date = date(date( 'Y-m-d H:i:s' , strtotime("today -1 day")));

        $post_builder3 = FixtureBuilder::build('posts', array('post_id'=>'152', 'author_user_id'=>'20',
        'author_username'=>'user1', 'author_fullname'=>'User 1', 'network'=>'foursquare',
        'post_text'=>'I just checked in', 'source'=>'', 'pub_date'=>$yesterday_date, 'location'=>'England',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'place'=>'The Park', 'place_id'=>'12345a',
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'foursquare',
        'in_reply_to_user_id' =>null, 'in_reply_to_post_id' => null, 'in_retweet_of_post_id'=>null,
        'geo'=>'52.477192843264,-1.484333726346'));

        $post_key = $post_builder3->columns['last_insert_id'];
        // Add a link for this post
        $link_builder3 = FixtureBuilder::build('links', array('post_key'=>$post_key, 'url'=>'http://bit.ly/blahb'));

        // Add the place information
        $place['place_id'] = '12345a';
        $place['place_type'] = "Park";
        $place['name'] = "A Park";
        $place['full_name'] = "The Greatest Park";
        $place['country_code'] = "UK";
        $place['country'] = "United Kingdom";
        $place['icon'] = "http://www.iconlocation.com";
        $place['network'] = "foursquare";
        $place['longlat'] = "GeometryFromText( 'Point(51.514 -0.1167)' )";
        $place['bounding_box'] = "PolygonFromText( 'Polygon(-0.213503 51.512805,-0.105303 51.512805,".
        "-0.105303 51.572068,-0.213503 51.572068, -0.213503 51.512805)')";
        $place['map_image'] = "http://www.mapimage.com";

        // Insert the place
        $place_builder = FixtureBuilder::build('places', $place);

        // Query the database for last year's post
        $post_dao = new PostMySQLDAO();
        // Get the year to query for
        $res = $post_dao->getOnThisDayFlashbackPosts(20, 'foursquare', $yesterday_date);

        // Check the 2 checkins we inserted are returned
        $this->assertEqual(sizeof($res), 2);
        // Check the author user id was set correctly
        $this->assertEqual($res[0]->author_user_id, '20');
        // Check the username was set correctly
        $this->assertEqual($res[0]->author_username, 'user1');
        // Check the author fullname was set correctly
        $this->assertEqual($res[0]->author_fullname, 'User 1');
        // Check the network was set correctly
        $this->assertEqual($res[0]->network, 'foursquare');
        // Check the post text was set correctly
        $this->assertEqual($res[0]->post_text, 'I just checked in');
        // Check the pub date was set correctly
        $this->assertEqual($res[0]->pub_date, $two_years_and_day_ago_date);
        // Check the location was set correctly
        $this->assertEqual($res[0]->location, 'England');
        // Check the place was set correctly
        $this->assertEqual($res[0]->place, 'The Park');
        // Check the place id was set correctly
        $this->assertEqual($res[0]->place_id, '12345a');
        // Check the geo co ordinates were set correctly
        $this->assertEqual($res[0]->geo, '52.477192843264,-1.484333726346');

        // Check the place id was set correctly
        $this->assertEqual($res[0]->place_obj->place_id, '12345a');
        // Check the place type was set correctly
        $this->assertEqual($res[0]->place_obj->place_type, 'Park');
        // Check the place name was set correctly
        $this->assertEqual($res[0]->place_obj->name, 'A Park');
        // Check the full name was set correctly
        $this->assertEqual($res[0]->place_obj->full_name, 'The Greatest Park');
        // Check the country code was set correctly
        $this->assertEqual($res[0]->place_obj->country_code, 'UK');
        // Check the country was set correctly
        $this->assertEqual($res[0]->place_obj->country, 'United Kingdom');
        // Check the icon was set correctly
        $this->assertEqual($res[0]->place_obj->icon, 'http://www.iconlocation.com');
        // Check the map image was set correctly
        $this->assertEqual($res[0]->place_obj->map_image, 'http://www.mapimage.com');

        // Check the link URL was set correctly
        $this->assertEqual($res[0]->links[0]->url, 'http://bit.ly/blahb');
    }

    public function testGetOnThisDayFlashbackPostsWithoutPostTextOrPlace(){
        // Generate the date string for 1 year and 1 day ago today
        $year_and_day_ago_date = date(date( 'Y-m-d H:i:s' , strtotime("today -1 day", strtotime("today -1 year"))));

        // Add a post from a year ago that's not a reply or retweet with empty post text and place
        $post_builder = FixtureBuilder::build('posts', array('post_id'=>'150', 'author_user_id'=>'20',
        'author_username'=>'user1', 'author_fullname'=>'User 1', 'network'=>'foursquare',
        'post_text'=>'', 'source'=>'', 'pub_date'=>$year_and_day_ago_date, 'location'=>'England',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'place'=>'', 'place_id'=>'12345a',
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'foursquare',
        'in_reply_to_user_id' =>null, 'in_reply_to_post_id' => null, 'in_retweet_of_post_id'=>null,
        'geo'=>'52.477192843264,-1.484333726346'));

        $post_key = $post_builder->columns['last_insert_id'];
        // Add a link for this post
        $link_builder = FixtureBuilder::build('links', array('post_key'=>$post_key, 'url'=>'http://bit.ly/blah'));

        // Add a post from 2 years ago that's not a reply or retweet
        $two_years_and_day_ago_date = date( 'Y-m-d H:i:s' , strtotime('today -1 day', strtotime("today -2 year")));
        $post_builder2 = FixtureBuilder::build('posts', array('post_id'=>'151', 'author_user_id'=>'20',
        'author_username'=>'user1', 'author_fullname'=>'User 1', 'network'=>'foursquare',
        'post_text'=>'I just checked in', 'source'=>'', 'pub_date'=>$two_years_and_day_ago_date, 'location'=>'England',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'place'=>'The Park', 'place_id'=>'12345a',
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'foursquare',
        'in_reply_to_user_id' =>null, 'in_reply_to_post_id' => null, 'in_retweet_of_post_id'=>null,
        'geo'=>'52.477192843264,-1.484333726346'));

        $post_key = $post_builder2->columns['last_insert_id'];
        // Add a link for this post
        $link_builder2 = FixtureBuilder::build('links', array('post_key'=>$post_key, 'url'=>'http://bit.ly/blahb'));

        // Add a post from today that's not a reply or retweet
        $yesterday_date = date(date( 'Y-m-d H:i:s' , strtotime("today -1 day")));

        $post_builder3 = FixtureBuilder::build('posts', array('post_id'=>'152', 'author_user_id'=>'20',
        'author_username'=>'user1', 'author_fullname'=>'User 1', 'network'=>'foursquare',
        'post_text'=>'I just checked in', 'source'=>'', 'pub_date'=>$yesterday_date, 'location'=>'England',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'place'=>'The Park', 'place_id'=>'12345a',
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'foursquare',
        'in_reply_to_user_id' =>null, 'in_reply_to_post_id' => null, 'in_retweet_of_post_id'=>null,
        'geo'=>'52.477192843264,-1.484333726346'));

        $post_key = $post_builder3->columns['last_insert_id'];
        // Add a link for this post
        $link_builder3 = FixtureBuilder::build('links', array('post_key'=>$post_key, 'url'=>'http://bit.ly/blahb'));

        // Add the place information
        $place['place_id'] = '12345a';
        $place['place_type'] = "Park";
        $place['name'] = "A Park";
        $place['full_name'] = "The Greatest Park";
        $place['country_code'] = "UK";
        $place['country'] = "United Kingdom";
        $place['icon'] = "http://www.iconlocation.com";
        $place['network'] = "foursquare";
        $place['longlat'] = "GeometryFromText( 'Point(51.514 -0.1167)' )";
        $place['bounding_box'] = "PolygonFromText( 'Polygon(-0.213503 51.512805,-0.105303 51.512805,".
        "-0.105303 51.572068,-0.213503 51.572068, -0.213503 51.512805)')";
        $place['map_image'] = "http://www.mapimage.com";

        // Insert the place
        $place_builder = FixtureBuilder::build('places', $place);

        // Query the database for last year's post
        $post_dao = new PostMySQLDAO();
        // Get the year to query for
        $res = $post_dao->getOnThisDayFlashbackPosts(20, 'foursquare', $yesterday_date);

        // Check only the 1 checkin we inserted is returned
        $this->assertEqual(sizeof($res), 1);
        // Check the author user id was set correctly
        $this->assertEqual($res[0]->author_user_id, '20');
        // Check the username was set correctly
        $this->assertEqual($res[0]->author_username, 'user1');
        // Check the author fullname was set correctly
        $this->assertEqual($res[0]->author_fullname, 'User 1');
        // Check the network was set correctly
        $this->assertEqual($res[0]->network, 'foursquare');
        // Check the post text was set correctly
        $this->assertEqual($res[0]->post_text, 'I just checked in');
        // Check the pub date was set correctly
        $this->assertEqual($res[0]->pub_date, $two_years_and_day_ago_date);
        // Check the location was set correctly
        $this->assertEqual($res[0]->location, 'England');
        // Check the place was set correctly
        $this->assertEqual($res[0]->place, 'The Park');
        // Check the place id was set correctly
        $this->assertEqual($res[0]->place_id, '12345a');
        // Check the geo co ordinates were set correctly
        $this->assertEqual($res[0]->geo, '52.477192843264,-1.484333726346');

        // Check the place id was set correctly
        $this->assertEqual($res[0]->place_obj->place_id, '12345a');
        // Check the place type was set correctly
        $this->assertEqual($res[0]->place_obj->place_type, 'Park');
        // Check the place name was set correctly
        $this->assertEqual($res[0]->place_obj->name, 'A Park');
        // Check the full name was set correctly
        $this->assertEqual($res[0]->place_obj->full_name, 'The Greatest Park');
        // Check the country code was set correctly
        $this->assertEqual($res[0]->place_obj->country_code, 'UK');
        // Check the country was set correctly
        $this->assertEqual($res[0]->place_obj->country, 'United Kingdom');
        // Check the icon was set correctly
        $this->assertEqual($res[0]->place_obj->icon, 'http://www.iconlocation.com');
        // Check the map image was set correctly
        $this->assertEqual($res[0]->place_obj->map_image, 'http://www.mapimage.com');

        // Check the link URL was set correctly
        $this->assertEqual($res[0]->links[0]->url, 'http://bit.ly/blahb');
    }

    public function testGetAllCheckins(){
        // Add place information for  foursquare checkins
        $place['place_id'] = '12345a';
        $place['place_type'] = "Park";
        $place['name'] = "A Park";
        $place['full_name'] = "The Greatest Park";
        $place['country_code'] = "UK";
        $place['country'] = "United Kingdom";
        $place['icon'] = "http://www.iconlocation.com";
        $place['network'] = "foursquare";
        $place['longlat'] = "GeometryFromText( 'Point(51.514 -0.1167)' )";
        $place['bounding_box'] = "PolygonFromText( 'Polygon(-0.213503 51.512805,-0.105303 51.512805,".
        "-0.105303 51.572068,-0.213503 51.572068, -0.213503 51.512805)')";
        $place['map_image'] = "http://www.mapimage.com";

        // Insert the place
        $place_builder = FixtureBuilder::build('places', $place);

        // Add A link for this checkin
        $checkin_builder[] = FixtureBuilder::build('links', array('post_key'=>'20020', 'url'=>'http://bit.ly/blah'));

        // Query the database for the checkin and check its returned
        $post_dao = new PostMySQLDAO();
        $res = $post_dao->getAllCheckins(20, 'foursquare');

        // Check only the 2 checkins we inserted are returned
        $this->assertEqual(sizeof($res), 1);
        // Check the author user id was set correctly
        $this->assertEqual($res[0]->author_user_id, '20');
        // Check the username was set correctly
        $this->assertEqual($res[0]->author_username, 'user1');
        // Check the author fullname was set correctly
        $this->assertEqual($res[0]->author_fullname, 'User 1');
        // Check the network was set correctly
        $this->assertEqual($res[0]->network, 'foursquare');
        // Check the post text was set correctly
        $this->assertEqual($res[0]->post_text, 'I just checked in');
        // We use relative dates so we need to work out how manys are between the checkin date and the date the test
        // is run

        $this->assertEqual($res[0]->pub_date, "2011-02-21 09:50:00");
        // Check the location was set correctly
        $this->assertEqual($res[0]->location, 'England');
        // Check the place was set correctly
        $this->assertEqual($res[0]->place, 'The Park');
        // Check the place id was set correctly
        $this->assertEqual($res[0]->place_id, '12345a');
        // Check the geo co ordinates were set correctly
        $this->assertEqual($res[0]->geo, '52.477192843264,-1.484333726346');

        // Check the place id was set correctly
        $this->assertEqual($res[0]->place_obj->place_id, '12345a');
        // Check the place type was set correctly
        $this->assertEqual($res[0]->place_obj->place_type, 'Park');
        // Check the place name was set correctly
        $this->assertEqual($res[0]->place_obj->name, 'A Park');
        // Check the full name was set correctly
        $this->assertEqual($res[0]->place_obj->full_name, 'The Greatest Park');
        // Check the country code was set correctly
        $this->assertEqual($res[0]->place_obj->country_code, 'UK');
        // Check the country was set correctly
        $this->assertEqual($res[0]->place_obj->country, 'United Kingdom');
        // Check the icon was set correctly
        $this->assertEqual($res[0]->place_obj->icon, 'http://www.iconlocation.com');
        // Check the map image was set correctly
        $this->assertEqual($res[0]->place_obj->map_image, 'http://www.mapimage.com');

        // Check the URL was set correctly
        $this->assertEqual($res[0]->links[0]->url, 'http://bit.ly/blah');
    }

    public function testCountCheckinsToPlaceTypes(){
        // Add place information for checkins
        $place['place_id'] = '12345a';
        $place['place_type'] = "Park";
        $place['name'] = "A Park";
        $place['full_name'] = "The Greatest Park";
        $place['country_code'] = "UK";
        $place['country'] = "United Kingdom";
        $place['icon'] = "http://www.iconlocation.com";
        $place['network'] = "foursquare";
        $place['longlat'] = "GeometryFromText( 'Point(51.514 -0.1167)' )";
        $place['bounding_box'] = "PolygonFromText( 'Polygon(-0.213503 51.512805,-0.105303 51.512805,".
        "-0.105303 51.572068,-0.213503 51.572068, -0.213503 51.512805)')";
        $place['map_image'] = "http://www.mapimage.com";

        // Insert the place
        $place_builder = FixtureBuilder::build('places', $place);

        // Query the database for the number of checkins per type of place
        $post_dao = new PostMySQLDAO();
        $res = $post_dao->countCheckinsToPlaceTypes('20', 'foursquare');

        $valid_json = '{"rows":[{"c":[{"v":"Park","f":"Park"},{"v":1}]}],"cols":[{"type":"string","label":"Place Type"';
        $valid_json .= '},{"type":"number","label":"Number of Checkins to this place type"}]}';

        $this->assertEqual($res, $valid_json);
    }

    public function testCountCheckinsByPlaceType(){
        $pub1 = date(date( 'Y-m-d H:i:s' , strtotime("now -2 week")));
        $pub2 = date(date( 'Y-m-d H:i:s' , strtotime("now +1 hour")));

        $hour1 = date('H',  strtotime("now") );
        $hour2 = date('H',  strtotime("now +1 hour") );

        // Add some foursquare checkins (done here due to time dependenacy of test)
        $checkin_builder[] = FixtureBuilder::build('posts', array('post_id'=>'1000', 'author_user_id'=>'31',
        'author_username'=>'user1', 'author_fullname'=>'User 1', 'network'=>'foursquare',
        'post_text'=>'I just checked in', 'source'=>'', 'pub_date'=>$pub1, 'location'=>'England',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'place'=>'The Park', 'place_id'=>'12345b',
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'foursquare',
        'in_reply_to_user_id' =>null, 'in_reply_to_post_id' => null,
        'geo'=>'52.477192843264,-1.484333726346'));

        $checkin_builder[] = FixtureBuilder::build('posts', array('post_id'=>'999', 'author_user_id'=>'31',
        'author_username'=>'user1', 'author_fullname'=>'User 1', 'network'=>'foursquare',
        'post_text'=>'I just checked in again', 'source'=>'', 'pub_date'=>$pub2, 'location'=>'England',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'place'=>'The Garage', 'place_id'=>'12345c',
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'foursquare',
        'in_reply_to_user_id' =>null, 'in_reply_to_post_id' => null,
        'geo'=>'52.477192843264,-1.484333726346'));

        // Add place information
        $place1['place_id'] = '12345b';
        $place1['place_type'] = "Park";
        $place1['name'] = "A Park";
        $place1['full_name'] = "The Greatest Park";
        $place1['country_code'] = "UK";
        $place1['country'] = "United Kingdom";
        $place1['icon'] = "http://www.iconlocation.com";
        $place1['network'] = "foursquare";
        $place1['longlat'] = "GeometryFromText( 'Point(51.514 -0.1167)' )";
        $place1['bounding_box'] = "PolygonFromText( 'Polygon(-0.213503 51.512805,-0.105303 51.512805,".
        "-0.105303 51.572068,-0.213503 51.572068, -0.213503 51.512805)')";
        $place1['map_image'] = "http://www.mapimage.com";

        // Insert the place
        $place_builder1 = FixtureBuilder::build('places', $place1);

        // Set all possible fields
        $place2['place_id'] = '12345c';
        $place2['place_type'] = "Garage";
        $place2['network'] = "foursquare";
        $place2['name'] = "A Garage";
        $place2['full_name'] = "The Greatest Garage";
        $place2['country_code'] = "UK";
        $place2['country'] = "United Kingdom";
        $place2['icon'] = "http://www.iconlocation.com";
        $place2['longlat'] = "GeometryFromText( 'Point(51.514 -0.1167)' )";
        $place2['bounding_box'] = "PolygonFromText( 'Polygon((-0.213503 51.512805,-0.105303 51.512805,".
        "-0.105303 51.572068,-0.213503 51.572068, -0.213503 51.512805)))')";

        // Insert the place
        $place_builder2 = FixtureBuilder::build('places', $place2);

        // Query the database for the number of checkins per type of place in the last week
        $post_dao = new PostMySQLDAO();
        $res = $post_dao->countCheckinsToPlaceTypes('31', 'foursquare');

        $valid_json = '{"rows":[{"c":[{"v":"Garage","f":"Garage"},{"v":1}]},{"c":[{"v":"Park","f":"Park"},{"v":1}]}],';
        $valid_json .= '"cols":[{"type":"string","label":"Place Type"},{"type":"number","label":"Number of Checkins to';
        $valid_json .= ' this place type"}]}';

        $this->assertEqual($res, $valid_json);
    }

    public function testGetPostsPerHourDataVis(){
        $pub1 = date(date( 'Y-m-d H:i:s' , strtotime("now")));
        $pub2 = date(date( 'Y-m-d H:i:s' , strtotime("now +1 hour")));

        $hour1 = date('G',  strtotime("now") );
        $hour2 = date('G',  strtotime("now +1 hour") );

        // Add some foursquare checkins (done here due to time dependency of test)
        $checkin_builder[] = FixtureBuilder::build('posts', array('post_id'=>'998', 'author_user_id'=>'30',
        'author_username'=>'user1', 'author_fullname'=>'User 1', 'network'=>'foursquare',
        'post_text'=>'I just checked in', 'source'=>'', 'pub_date'=>$pub1, 'location'=>'England',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'place'=>'The Park', 'place_id'=>'12345a',
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'foursquare',
        'in_reply_to_user_id' =>null, 'in_reply_to_post_id' => null,
        'geo'=>'52.477192843264,-1.484333726346'));

        $checkin_builder[] = FixtureBuilder::build('posts', array('post_id'=>'999', 'author_user_id'=>'30',
        'author_username'=>'user1', 'author_fullname'=>'User 1', 'network'=>'foursquare',
        'post_text'=>'I just checked in again', 'source'=>'', 'pub_date'=>$pub2, 'location'=>'England',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'place'=>'The Garage', 'place_id'=>'12346',
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'foursquare',
        'in_reply_to_user_id' =>null, 'in_reply_to_post_id' => null,
        'geo'=>'52.477192843264,-1.484333726346'));

        // Query the database for the number of checkins per hour
        $post_dao = new PostMySQLDAO();
        $result = $post_dao->getPostsPerHourDataVis('30', 'foursquare');

        $valid_json = '{"rows":[';
        $i = 0;
        while ($i < 24) {
            if ( $i == $hour1 || $i == $hour2 ) {
                $valid_json .= '{"c":[{"v":'.$i.'},{"v":1},{"v":1}]}';
            } else {
                $valid_json .= '{"c":[{"v":'.$i.'},{"v":0},{"v":0}]}';
            }
            if ($i < 23 ) {
                $valid_json .= ',';
            } else {
                $valid_json .= '],"cols":[{"type":"string","label":"Hour of Day"},{"type":"number","label":'.
                '"Checkins Last Week"},{"type":"number","label":"Checkins All Time"}]}';
            }
            $i++;
        }
        $this->assertEqual($result, $valid_json);
    }

    public function testGetAllCheckinsInLastWeekAsGoogleMap() {
        // Build the pub_date string which needs to be a date within the last week
        $pub1 = date(date( 'Y-m-d H:i:s' , strtotime("now")));
        $pub2 = date(date( 'Y-m-d H:i:s' , strtotime("now +1 hour")));

        $hour1 = date('H',  strtotime("now") );
        $hour2 = date('H',  strtotime("now +1 hour") );

        // Add some foursquare checkins (done here due to time dependenacy of test)
        $checkin_builder[] = FixtureBuilder::build('posts', array('post_id'=>'1001', 'author_user_id'=>'30',
        'author_username'=>'user1', 'author_fullname'=>'User 1', 'network'=>'foursquare',
        'post_text'=>'I just checked in', 'source'=>'', 'pub_date'=>$pub1, 'location'=>'England',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'place'=>'The Park', 'place_id'=>'12345a',
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'foursquare',
        'in_reply_to_user_id' =>null, 'in_reply_to_post_id' => null,
        'geo'=>'52.477192843264,-1.484333726346'));

        $checkin_builder[] = FixtureBuilder::build('posts', array('post_id'=>'1000', 'author_user_id'=>'30',
        'author_username'=>'user1', 'author_fullname'=>'User 1', 'network'=>'foursquare',
        'post_text'=>'I just checked in again', 'source'=>'', 'pub_date'=>$pub2, 'location'=>'England',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'place'=>'The Garage', 'place_id'=>'12346',
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'foursquare',
        'in_reply_to_user_id' =>null, 'in_reply_to_post_id' => null,
        'geo'=>'52.477192843264,-1.484333726346'));

        // Query the database for the number of checkins per hour
        $post_dao = new PostMySQLDAO();
        $res = $post_dao->GetAllCheckinsInLastWeekAsGoogleMap('30', 'foursquare');

        $valid_url = 'http://maps.googleapis.com/maps/api/staticmap?size=708x500&maptype=roadmap&markers=color:';
        $valid_url .= 'blue%7C|52.477192843264,-1.484333726346|52.477192843264,-1.484333726346&sensor=false';

        $this->assertEqual($res, $valid_url);
    }

    public function testGetMostPopularPostsOfTheYear() {
        $post_dao = new PostMySQLDAO();
        $posts = $post_dao->getMostPopularPostsOfTheYear('13', 'twitter', '2006');

        $this->assertEqual(sizeof($posts), 25);
        foreach ($posts as $post) {
            $this->assertIsA($post, 'Post');
            $this->assertNotNull($post->id);
            $this->assertEqual($post->author_user_id, '13');
            $this->assertEqual($post->author_username, 'ev');
            $this->assertNull($post->in_reply_to_user_id);
            $this->assertNull($post->in_retweet_of_post_id);
            $this->assertNull($post->in_rt_of_user_id);
            $this->assertEqual(date('Y', strtotime($post->pub_date)), '2006');
        }
    }
    public function testGetAverageRetweetCount() {
        $builders = array();
        //Add straight text posts
        $counter = 1;
        while ($counter < 40) {
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
            if ($counter % 3 == 0) {
                $source = '<a href="http://twitter.com" rel="nofollow">Tweetie for Mac</a>';
            } else if ($counter % 3 == 1) {
                $source = '<a href="http://twitter.com/tweetbutton" rel="nofollow">Tweet Button</a>';
            } else {
                $source = 'web';
            }
            $builders[] = FixtureBuilder::build('posts', array('id'=>$counter+256, 'post_id'=>$counter+256,
            'author_user_id'=>'13', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
            'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post '.$counter,
            'source'=>$source, 'pub_date'=>'-'.$counter.'d', 'in_reply_to_user_id'=>null,
            'reply_count_cache'=>($counter==10)?0:rand(0, 4), 'is_protected'=>0,
            'retweet_count_cache'=>floor($counter/2), 'network'=>'twitter',
            'old_retweet_count_cache' => floor($counter/3), 'in_rt_of_user_id' => null,
            'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
            $counter++;
        }

        $dao = new PostMySQLDAO();
        //without date (today)
        $average_retweet_count = $dao->getAverageRetweetCount('ev', 'twitter', 7);
        $this->assertEqual($average_retweet_count, 3);

        //yesterday
        $average_retweet_count = $dao->getAverageRetweetCount('ev', 'twitter', 7, date("Y-m-d", strtotime("-1 day")));
        $this->assertEqual($average_retweet_count, 4);

        //40 days ago
        $average_retweet_count = $dao->getAverageRetweetCount('ev', 'twitter', 7, date("Y-m-d", strtotime("-40 day")));
        $this->assertEqual($average_retweet_count, 17);
    }

    public function testGetAverageFaveCount() {
        $builders = array();
        //Add straight text posts
        $counter = 1;
        while ($counter < 40) {
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
            if ($counter % 3 == 0) {
                $source = '<a href="http://twitter.com" rel="nofollow">Tweetie for Mac</a>';
            } else if ($counter % 3 == 1) {
                $source = '<a href="http://twitter.com/tweetbutton" rel="nofollow">Tweet Button</a>';
            } else {
                $source = 'web';
            }
            $builders[] = FixtureBuilder::build('posts', array('id'=>$counter+256, 'post_id'=>$counter+256,
            'author_user_id'=>'13', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
            'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post '.$counter,
            'source'=>$source, 'pub_date'=>'-'.$counter.'d', 'in_reply_to_user_id'=>null,
            'reply_count_cache'=>($counter==10)?0:rand(0, 4), 'is_protected'=>0,
            'retweet_count_cache'=>floor($counter/2), 'network'=>'twitter', 'favlike_count_cache'=> ($counter % 7),
            'old_retweet_count_cache' => floor($counter/3), 'in_rt_of_user_id' => null,
            'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
            $counter++;
        }

        $dao = new PostMySQLDAO();
        //without date (today)
        $average_fave_count = $dao->getAverageFaveCount('ev', 'twitter', 7);
        $this->assertEqual($average_fave_count, 4);

        //yesterday
        $average_fave_count = $dao->getAverageFaveCount('ev', 'twitter', 7, date("Y-m-d", strtotime("-1 day")));
        $this->assertEqual($average_fave_count, 3);

        //40 days ago
        $average_fave_count = $dao->getAverageFaveCount('ev', 'twitter', 7, date("Y-m-d", strtotime("-40 day")));
        $this->assertEqual($average_fave_count, 3);
    }

    public function testGetAverageReplyCount() {
        $builders = array();
        //Add straight text posts
        $counter = 1;
        while ($counter < 40) {
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
            if ($counter % 3 == 0) {
                $source = '<a href="http://twitter.com" rel="nofollow">Tweetie for Mac</a>';
            } else if ($counter % 3 == 1) {
                $source = '<a href="http://twitter.com/tweetbutton" rel="nofollow">Tweet Button</a>';
            } else {
                $source = 'web';
            }
            $builders[] = FixtureBuilder::build('posts', array('id'=>$counter+256, 'post_id'=>$counter+256,
            'author_user_id'=>'13', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
            'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post '.$counter,
            'source'=>$source, 'pub_date'=>'-'.$counter.'d', 'in_reply_to_user_id'=>null,
            'reply_count_cache'=>$counter, 'is_protected'=>0,
            'retweet_count_cache'=>0, 'network'=>'twitter', 'favlike_count_cache'=> 0,
            'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
            'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
            $counter++;
        }

        $dao = new PostMySQLDAO();
        //without date (today)
        $average_reply_count = $dao->getAverageReplyCount('ev', 'twitter', 7);
        $this->assertEqual($average_reply_count, 4);

        //yesterday
        $average_reply_count = $dao->getAverageReplyCount('ev', 'twitter', 7, date("Y-m-d", strtotime("-1 day")));
        $this->assertEqual($average_reply_count, 5);

        //40 days ago
        $average_reply_count = $dao->getAverageReplyCount('ev', 'twitter', 7, date("Y-m-d", strtotime("-40 day")));
        $this->assertEqual($average_reply_count, 20);
    }

    public function testDoesUserHavePostsWithRetweetsSinceDate() {
        $post_dao = new PostMySQLDAO();
        $result = $post_dao->doesUserHavePostsWithRetweetsSinceDate('user3', 'twitter', 7);
        $this->assertFalse($result);

        $counter = 0;
        $id = 200;
        $builders = array();
        while ($counter < 40) {
            $id += $counter;
            $builders[] = FixtureBuilder::build('posts', array(
            'id'=>$id,
            'post_id'=>(147+$counter),
            'author_user_id'=>23,
            'author_username'=>'user3',
            'pub_date'=>'-'.$counter.'d',
            'retweet_count_cache'=>$counter+1,
            'old_retweet_count_cache' => floor($counter/2),
            'network'=>'twitter',
            'in_reply_to_user_id'=>null,
            'in_reply_to_post_id'=>null,
            'in_retweet_of_post_id'=>null
            ));
            $counter++;
        }

        $result = $post_dao->doesUserHavePostsWithRetweetsSinceDate('user3', 'twitter', 30);
        $this->assertTrue($result);

        $result = $post_dao->doesUserHavePostsWithRetweetsSinceDate('user3', 'twitter', 30,
        date('Y-m-d', strtotime('+256 days')));
        $this->assertFalse($result);
    }

    public function testDoesUserHavePostsWithFavesSinceDate() {
        $post_dao = new PostMySQLDAO();
        $result = $post_dao->doesUserHavePostsWithFavesSinceDate('user3', 'twitter', 7);
        $this->assertFalse($result);

        $counter = 0;
        $id = 200;
        $builders = array();
        while ($counter < 40) {
            $id += $counter;
            $builders[] = FixtureBuilder::build('posts', array(
            'id'=>$id,
            'post_id'=>(147+$counter),
            'author_user_id'=>23,
            'author_username'=>'user3',
            'pub_date'=>'-'.$counter.'d',
            'retweet_count_cache'=>0,
            'old_retweet_count_cache' => floor($counter/2),
            'favlike_count_cache'=>$counter+1,
            'network'=>'twitter',
            'in_reply_to_user_id'=>null,
            'in_reply_to_post_id'=>null,
            'in_retweet_of_post_id'=>null
            ));
            $counter++;
        }

        $result = $post_dao->doesUserHavePostsWithFavesSinceDate('user3', 'twitter', 30);
        $this->assertTrue($result);
        $result = $post_dao->doesUserHavePostsWithFavesSinceDate('user3', 'twitter', 30,
        date('Y-m-d', strtotime('+256 days')));
        $this->assertFalse($result);
    }

    public function testDoesUserHavePostsWithRepliesSinceDate() {
        $post_dao = new PostMySQLDAO();
        $result = $post_dao->doesUserHavePostsWithRepliesSinceDate('user4', 'youtube', 7);
        $this->assertFalse($result);

        $counter = 0;
        $id = 200;
        $builders = array();
        while ($counter < 40) {
            $id += $counter;
            $builders[] = FixtureBuilder::build('posts', array(
            'id'=>$id,
            'post_id'=>(147+$counter),
            'author_user_id'=>23,
            'author_username'=>'user4',
            'pub_date'=>'-'.$counter.'d',
            'retweet_count_cache'=> 0,
            'old_retweet_count_cache' => 0,
            'favlike_count_cache'=>0,
            'network'=>'youtube',
            'reply_count_cache'=>$counter,
            'in_reply_to_user_id'=>null,
            'in_reply_to_post_id'=>null,
            'in_retweet_of_post_id'=>null
            ));
            $counter++;
        }
        // They do have replies from within 30 days
        $result = $post_dao->doesUserHavePostsWithRepliesSinceDate('user4', 'youtube', 30);
        $this->assertTrue($result);
        // Set date to some time 30+ days in the future and were guaranteed to have no replies since then
        $result = $post_dao->doesUserHavePostsWithRepliesSinceDate('user4', 'youtube', 30,
        date('Y-m-d', strtotime('+31 days')));
        $this->assertFalse($result);
    }

    public function testGetRetweetsByAuthorsOverFollowerCount() {
        $post_dao = new PostMySQLDAO();
        $big_retweeters = $post_dao->getRetweetsByAuthorsOverFollowerCount('134', 'twitter', 10);
        $this->assertEqual(sizeof($big_retweeters), 3);
        $this->assertIsA($big_retweeters[0], 'User');
        $this->assertEqual($big_retweeters[0]->follower_count, 90);
        $this->assertEqual($big_retweeters[0]->username, 'user1');
        $this->assertEqual($big_retweeters[1]->follower_count, 80);
        $this->assertEqual($big_retweeters[1]->username, 'user2');
        $this->assertEqual($big_retweeters[2]->follower_count, 70);
        $this->assertEqual($big_retweeters[2]->username, 'linkbaiter');
    }

    public function testGetDaysAgoSinceUserRepliedToRecipient() {
        $time_ago = array(
            date('Y-m-d H:i:s', strtotime('-12 days')),
            date('Y-m-d H:i:s', strtotime('-14 days')),
            date('Y-m-d H:i:s', strtotime('-17 days'))
        );

        for ($i = 0; $i < 3; $i++) {
            $builders[] = FixtureBuilder::build('posts', array(
                'id'=>(760+$i),
                'post_id'=>(760+$i),
                'author_user_id'=>9912345,
                'author_username'=>'user123',
                'network'=>'twitter',
                'pub_date'=>$time_ago[$i],
                'in_reply_to_user_id'=>9912346
            ));
        }

        $builders[] = FixtureBuilder::build('posts', array(
            'id'=>763,
            'post_id'=>763,
            'author_user_id'=>9912345,
            'author_username'=>'user123',
            'network'=>'twitter',
            'pub_date'=>$time_ago[2],
            'in_reply_to_user_id'=>9912347
        ));

        $dao = new PostMySQLDAO();
        $result_1 = $dao->getDaysAgoSinceUserRepliedToRecipient(9912345, 9912346, 'twitter');
        $result_2 = $dao->getDaysAgoSinceUserRepliedToRecipient(9912345, 9912347, 'twitter');
        $result_3 = $dao->getDaysAgoSinceUserRepliedToRecipient(9912345, 9912348, 'twitter'); // no replies

        $this->assertEqual($result_1, 12);
        $this->assertEqual($result_2, 17);
        $this->assertNull($result_3);
    }

    public function testCountAllPostsByUserSinceDaysAgo() {
        $builders = array();
        $user_id = 7654321;
        $counter = 0;
        while ($counter < 53) {
            $post_key = 1760 + $counter;
            $post_date = date('Y-m-d H:i:s', strtotime('-'.$counter.' day'));

            $builders[] = FixtureBuilder::build('posts', array('id'=>$post_key, 'post_id'=>$post_key,
            'network'=>'twitter', 'author_user_id'=>$user_id, 'author_username'=>'user',
            'in_reply_to_post_id'=>0, 'is_protected' => 0, 'author_fullname'=>'User',
            'post_text'=>'Sample post '.$counter, 'pub_date'=>$post_date));

            $counter++;
        }

        $post_dao = new PostMySQLDAO();
        $result = $post_dao->countAllPostsByUserSinceDaysAgo($user_id, 'twitter', 31);

        $this->assertEqual($result, 32);
    }

    public function testGetMostFavCommentPostsByUserId() {
    	$builders = array();
    	$user_id=7654321;
    	$now =  date('Y-m-d H:i:s');
    	$yesterday = date('Y-m-d H:i:s', strtotime('yesterday'));
    	$builders[] = FixtureBuilder::build('posts', array('id'=> 331, 'post_id'=> 1331, 'author_user_id'=>$user_id,
    			'author_username'=>'user', 'author_fullname'=>'User', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple post.',
    			'pub_date'=>$now, 'reply_count_cache'=> 1,'favlike_count_cache' => 6));

    	$builders[] = FixtureBuilder::build('posts', array('id'=> 341, 'post_id'=> 1341, 'author_user_id'=>$user_id,
    			'author_username'=>'user', 'author_fullname'=>'User', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple comment.',
    			'pub_date'=>$now, 'reply_count_cache'=> 0,'favlike_count_cache' => 5));

    	$builders[] = FixtureBuilder::build('posts', array('id'=> 351, 'post_id'=> 1351, 'author_user_id'=>$user_id,
    			'author_username'=>'user', 'author_fullname'=>'User', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple cooment.',
    			'pub_date'=>$yesterday, 'reply_count_cache'=> 0,'favlike_count_cache' => 4));

    	$builders[] = FixtureBuilder::build('posts', array('id'=> 361, 'post_id'=> 1361, 'author_user_id'=>$user_id,
    			'author_username'=>'user', 'author_fullname'=>'User', 'author_avatar'=>'avatar.jpg',
    			'network'=>'facebook', 'post_text'=>'This is a simple comment.',
    			'pub_date'=>$now, 'reply_count_cache'=> 0,'favlike_count_cache' => 0));

    	$post_dao = new PostMySQLDAO();
    	$posts = $post_dao->getMostFavCommentPostsByUserId($user_id, 'facebook');
    	$this-> assertNotNull($posts);
    	foreach($posts as $post) {
    		$this->assertTrue($post instanceof Post);
    		$this->assertEqual($post->post_id, '1331');
    	}

    }

    public function testSearchPostsByUsername() {
        $post_dao = new PostMySQLDAO();
        //should be first page of 20
        $results = $post_dao->searchPostsByUser(array('post'), 'twitter', 'ev');
        $this->assertEqual(sizeof($results), 20);

        //page 2
        $results = $post_dao->searchPostsByUser(array('post'), 'twitter', 'ev', 2);
        $this->assertEqual(sizeof($results), 19);

        //empty page 3
        $results = $post_dao->searchPostsByUser(array('post'), 'twitter', 'ev', 3);
        $this->assertEqual(sizeof($results), 0);

        //test and
        $results = $post_dao->searchPostsByUser(array('post', 'asdf'), 'twitter', 'ev');
        $this->assertEqual(sizeof($results), 0);

        $results = $post_dao->searchPostsByUser('keyword', 'twitter', 'ev');
        $this->assertEqual(sizeof($results), 0);

        //test with repeated keywords
        $results = $post_dao->searchPostsByUser(array('post','post'), 'twitter', 'ev');
        $this->assertEqual(sizeof($results), 0);
    }
    /**
     * Test getAllPostsByHashtagId
     */
    public function testGetAllPostsByHashtagId() {
        $dao = new PostMySQLDAO();
        $output = $dao->getAllPostsByHashtagId(1, 'twitter', 20);
        $this->assertEqual(sizeof($output), 20);

        $this->assertTrue(is_array($output));
        foreach($output as $post) {
            $this->assertTrue($post instanceof Post);
            $this->assertEqual($post->protected, false);
        }
        //test page
        $output = $dao->getAllPostsByHashtagId(1, 'twitter', 20,'post_id', 'ASC',1);
        $this->assertEqual(sizeof($output), 20);
        $counter=301;
        foreach($output as $post) {
            $this->assertEqual($post->post_id, $counter);
            $counter = $counter+2;
        }
        $output = $dao->getAllPostsByHashtagId(1, 'twitter', 20,'post_id', 'ASC',2);
        $this->assertEqual(sizeof($output), 10);
        $counter=341;
        foreach($output as $post) {
            $this->assertEqual($post->post_id, $counter);
            $counter = $counter+2;
        }

        $output = $dao->getAllPostsByHashtagId(2, 'twitter', 20);
        $this->assertEqual(sizeof($output), 20);

        // test the object type is correct
        $this->assertTrue(is_array($output));
        foreach($output as $post) {
            $this->assertTrue($post instanceof Post);
            $this->assertEqual($post->protected, false);
        }

        // test count
        for ($count = 1; $count <= 20; $count++) {
            $output = $dao->getAllPostsByHashtagId(2, 'twitter', $count);
            $this->assertEqual(sizeof($output), $count);
        }

        // test order_by
        $output = $dao->getAllPostsByHashtagId(2, 'twitter', 20,'date','DESC');
        $this->assertEqual(sizeof($output), 20);
        $date = strtotime($output[0]->pub_date);
        foreach ($output as $post) {
            $this->assertTrue(strtotime($post->pub_date) <= $date);
            $date = strtotime($post->pub_date);
        }

        $output = $dao->getAllPostsByHashtagId(2, 'twitter', 20,'date','ASC');
        $this->assertEqual(sizeof($output), 20);
        $date = strtotime($output[0]->pub_date);
        foreach ($output as $post) {
            $this->assertTrue(strtotime($post->pub_date) >= $date);
            $date = strtotime($post->pub_date);
        }

        $output = $dao->getAllPostsByHashtagId(2, 'twitter', 20,'source','DESC');
        $this->assertEqual(sizeof($output), 20);
        $str = $output[0]->source;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->source, $str) <= 0);
            $str = $post->source;
        }

        $output = $dao->getAllPostsByHashtagId(2, 'twitter', 20,'source','ASC');
        $this->assertEqual(sizeof($output), 20);
        $str = $output[0]->source;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->source, $str) >= 0);
            $str = $post->source;
        }

        $output = $dao->getAllPostsByHashtagId(2, 'twitter', 20,'post_text','DESC');
        $this->assertEqual(sizeof($output), 20);
        $str = $output[0]->post_text;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->post_text, $str) <= 0);
            $str = $post->post_text;
        }

        $output = $dao->getAllPostsByHashtagId(2, 'twitter', 20,'post_text','ASC');
        $this->assertEqual(sizeof($output), 20);
        $str = $output[0]->post_text;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->post_text, $str) >= 0);
            $str = $post->post_text;
        }

        $output = $dao->getAllPostsByHashtagId(2, 'twitter', 20,'author_username','DESC');
        $this->assertEqual(sizeof($output), 20);
        $str = $output[0]->author_username;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->author_username, $str) <= 0);
            $str = $post->author_username;
        }

        $output = $dao->getAllPostsByHashtagId(2, 'twitter', 20,'author_username','ASC');
        $this->assertEqual(sizeof($output), 20);
        $str = $output[0]->author_username;
        foreach ($output as $post) {
            $this->assertTrue(strcmp($post->author_username, $str) >= 0);
            $str = $post->author_username;
        }
    }

    public function testDeletePostsByHashtagId() {
        $dao = new PostMySQLDAO();

        $output = $dao->getAllPostsByHashtagId(1, 'twitter', 20);
        $this->assertEqual(sizeof($output), 20);

        $output = $dao->getAllPostsByHashtagId(2, 'twitter', 20);
        $this->assertEqual(sizeof($output), 20);

        $output = $dao->deletePostsByHashtagId(1);
        $this->assertEqual($output, 30);

        $output = $dao->deletePostsByHashtagId(2);
        $this->assertEqual($output, 30);

        $output = $dao->getAllPostsByHashtagId(1, 'twitter', 20);
        $this->assertEqual(sizeof($output), 0);

        $output = $dao->getAllPostsByHashtagId(2, 'twitter', 20);
        $this->assertEqual(sizeof($output), 0);
    }

    public function testSearchPostsByHashtag() {
        $dao = new PostMySQLDAO();

        $hashtag = new Hashtag();
        $hashtag->id = 1;

        //First search hashtag that exists
        $output = $dao->searchPostsByHashtag(array(), $hashtag, 'twitter');
        $this->assertEqual(sizeof($output), 20);
        $this->assertTrue(is_array($output));
        $counter=359;
        foreach($output as $post) {
            $this->assertTrue($post instanceof Post);
            $this->assertEqual($post->post_id, $counter);
            $counter = $counter-2;
        }

        //Test page_count
        for ($i=30;$i>1;$i--) {
            $output = $dao->searchPostsByHashtag(array(), $hashtag, 'twitter', 1, $i);
            $this->assertEqual(sizeof($output), $i);
            $counter=359;
            foreach($output as $post) {
                $this->assertEqual($post->post_id, $counter);
                $counter = $counter-2;
            }
        }

        //Test page
        for ($i=1;$i<4;$i++) {
            $output = $dao->searchPostsByHashtag(array(), $hashtag, 'twitter',$i);
            switch ($i) {
                case 1:
                    $this->assertEqual(sizeof($output), 20);
                    break;
                case 2:
                    $this->assertEqual(sizeof($output), 10);
                    break;
                case 3:
                    $this->assertEqual(sizeof($output), 0);
                    break;
            }
        }

        //test second hashtag search that exists
        $hashtag = new Hashtag();
        $hashtag->id = 2;
        $output = $dao->searchPostsByHashtag(array(), $hashtag, 'twitter',1,60);
        $this->assertEqual(sizeof($output), 30);
        $this->assertTrue(is_array($output));
        $counter=358;
        foreach($output as $post) {
            $this->assertEqual($post->post_id, $counter);
            $counter = $counter-2;
        }
    }

    public function testCountCheckinsToPlaceTypesLastWeek() {
        $dao = new PostMySQLDAO();
        // Add place information for checkins
        $place = array();
        $place['place_id'] = '12345a';
        $place['place_type'] = "Park";
        $place['name'] = "A Park";
        $place['full_name'] = "The Greatest Park";
        $place['country_code'] = "UK";
        $place['country'] = "United Kingdom";
        $place['icon'] = "http://www.iconlocation.com";
        $place['network'] = "foursquare";
        $place['longlat'] = "GeometryFromText( 'Point(51.514 -0.1167)' )";
        $place['bounding_box'] = "PolygonFromText( 'Polygon(-0.213503 51.512805,-0.105303 51.512805,".
        "-0.105303 51.572068,-0.213503 51.572068, -0.213503 51.512805)')";
        $place['map_image'] = "http://www.mapimage.com";
        $this->builders[] = FixtureBuilder::build('places', $place);

        $place = array();
        $place['place_id'] = '12345b';
        $place['place_type'] = "Museum";
        $place['name'] = "A Park";
        $place['full_name'] = "The Greatest Park";
        $place['country_code'] = "UK";
        $place['country'] = "United Kingdom";
        $place['icon'] = "http://www.iconlocation.com";
        $place['network'] = "foursquare";
        $place['longlat'] = "GeometryFromText( 'Point(51.514 -0.1167)' )";
        $place['bounding_box'] = "PolygonFromText( 'Polygon(-0.213503 51.512805,-0.105303 51.512805,".
        "-0.105303 51.572068,-0.213503 51.572068, -0.213503 51.512805)')";
        $place['map_image'] = "http://www.mapimage.com";
        $this->builders[] = FixtureBuilder::build('places', $place);

        $place = array();
        $place['place_id'] = '12345c';
        $place['place_type'] = "Museum";
        $place['name'] = "A Park";
        $place['full_name'] = "The Greatest Park";
        $place['country_code'] = "UK";
        $place['country'] = "United Kingdom";
        $place['icon'] = "http://www.iconlocation.com";
        $place['network'] = "foursquare";
        $place['longlat'] = "GeometryFromText( 'Point(51.514 -0.1167)' )";
        $place['bounding_box'] = "PolygonFromText( 'Polygon(-0.213503 51.512805,-0.105303 51.512805,".
        "-0.105303 51.572068,-0.213503 51.572068, -0.213503 51.512805)')";
        $place['map_image'] = "http://www.mapimage.com";
        $this->builders[] = FixtureBuilder::build('places', $place);

        // ensure this is not this week
        $this->builders[] = FixtureBuilder::build('posts', array('post_id'=>'249', 'author_user_id'=>'20',
        'author_username'=>'user1', 'author_fullname'=>'User 1', 'network'=>'foursquare',
        'post_text'=>'I just checked in', 'source'=>'', 'pub_date'=>'2011-12-1 09:50:00', 'location'=>'England',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'place'=>'The Park', 'place_id'=>'12345a',
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'foursquare',
        'in_reply_to_user_id' =>'23', 'in_reply_to_post_id' => null,
        'geo'=>'52.477192843264,-1.484333726346'));

        $res = $dao->countCheckinsToPlaceTypesLastWeek(20, 'foursquare');
        // We now have posts, but they are way in the past.  Nothing this week
        $this->assertEqual($res, '');

        // now we add some this week.
        $this->builders[] = FixtureBuilder::build('posts', array('post_id'=>'250', 'author_user_id'=>'20',
        'author_username'=>'user1', 'author_fullname'=>'User 1', 'network'=>'foursquare',
        'post_text'=>'I just checked in', 'source'=>'', 'pub_date'=>date('Y-m-d').' 09:50:00', 'location'=>'England',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'place'=>'The Park', 'place_id'=>'12345a',
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'foursquare',
        'in_reply_to_user_id' =>'23', 'in_reply_to_post_id' => null,
        'geo'=>'52.477192843264,-1.484333726346'));

        $this->builders[] = FixtureBuilder::build('posts', array('post_id'=>'251', 'author_user_id'=>'20',
        'author_username'=>'user1', 'author_fullname'=>'User 1', 'network'=>'foursquare',
        'post_text'=>'I just checked in', 'source'=>'', 'pub_date'=>date('Y-m-d').' 09:50:00', 'location'=>'England',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'place'=>'The Park', 'place_id'=>'12345c',
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'foursquare',
        'in_reply_to_user_id' =>'23', 'in_reply_to_post_id' => null,
        'geo'=>'52.477192843264,-1.484333726346'));

        $this->builders[] = FixtureBuilder::build('posts', array('post_id'=>'253', 'author_user_id'=>'20',
        'author_username'=>'user1', 'author_fullname'=>'User 1', 'network'=>'foursquare',
        'post_text'=>'I just checked in', 'source'=>'', 'pub_date'=>date('Y-m-d').' 09:51:00', 'location'=>'England',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'place'=>'The Park', 'place_id'=>'12345b',
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'foursquare',
        'in_reply_to_user_id' =>'23', 'in_reply_to_post_id' => null,
        'geo'=>'52.477192843264,-1.484333726346'));

        // Now we have actual checkins this week.
        // 2 Museums, 1 Park
        // And we can verify the formatting for the charts.
        $res = $dao->countCheckinsToPlaceTypesLastWeek(20, 'foursquare');
        $this->assertNotEqual($res, '');
        $res = json_decode($res);

        $this->assertEqual($res->rows[0]->c[0]->v, 'Museum');
        $this->assertEqual($res->rows[0]->c[0]->f, 'Museum');
        $this->assertEqual($res->rows[0]->c[1]->v, 2);
        $this->assertEqual($res->rows[1]->c[0]->v, 'Park');
        $this->assertEqual($res->rows[1]->c[0]->f, 'Park');
        $this->assertEqual($res->rows[1]->c[1]->v, 1);
        $this->assertEqual($res->cols[0]->type, 'string');
        $this->assertEqual($res->cols[0]->label, 'Place Type');
        $this->assertEqual($res->cols[1]->type, 'number');
        $this->assertEqual($res->cols[1]->label, 'Number of Checkins to this place type');
    }
}
