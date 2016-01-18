<?php
/**
 *
 * webapp/plugins/insightsgenerator/tests/TestOfEOYBestieInsight.php
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
 * TestOfEOYBestieInsight
 *
 * Copyright (c) 2014-2016 Gina Trapani
 *
 * @author Gina Trapani <ginatrapani at gmail dot com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014-2016 Gina Trapani
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/eoybestie.php';

class TestOfEOYBestieInsight extends ThinkUpInsightUnitTestCase {

    public function setUp() {
        parent::setUp();

        $this->instance = new Instance();
        $this->instance->id = 10;
        $this->instance->network_username = 'buffy';
        $this->instance->network = 'twitter';
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $insight_plugin = new EOYBestieInsight();
        $this->assertIsA($insight_plugin, 'EOYBestieInsight' );
    }

    public function testNoBestieTwitterIncompleteData() {
        // Set up and test no bestie Twitter case
        $builders = self::setUpPublicInsight($this->instance);
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_id' => '1001',
            'post_text' => 'This is very liked',
            'pub_date' => '2015-02-07',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'favlike_count_cache' => 100
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_id' => '1002',
            'post_text' => 'This is pretty well liked',
            'pub_date' => '2015-02-07',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'favlike_count_cache' => 50
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_id' => '1003',
            'post_text' => 'This is least liked',
            'pub_date' => '2015-02-07',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'favlike_count_cache' => 25
            )
        );
        $this->instance->last_post_id = '1001';

        $posts = array();
        $insight_plugin = new EOYBestieInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $year = date('Y');
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("@buffy's Twitter bestie of 2015", $result->headline);
        $this->assertEqual("@buffy didn't reply to any one person more than 3 times this year ".
            "(at least since February). That means no one can claim the title of @buffy's Twitter bestie. ".
            "Playing hard-to-get, huh?", $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "No bestie incomplete data, Twitter");
    }

    public function testNoBestieFacebookIncompleteData() {
        $this->instance->network_username = 'Buffy Summers';
        $this->instance->network = 'facebook';
        // Set up and test no bestie Facebook case
        $builders = self::setUpPublicInsight($this->instance);
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_id' => '1001',
            'post_text' => 'This is very liked',
            'pub_date' => '2015-02-07',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'favlike_count_cache' => 100
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_id' => '1002',
            'post_text' => 'This is pretty well liked',
            'pub_date' => '2015-02-07',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'favlike_count_cache' => 50
            )
        );
        $builders[] = FixtureBuilder::build('posts',
            array(
            'post_id' => '1003',
            'post_text' => 'This is least liked',
            'pub_date' => '2015-02-07',
            'author_username' => $this->instance->network_username,
            'network' => $this->instance->network,
            'favlike_count_cache' => 25
            )
        );
        $this->instance->last_post_id = '1001';

        $posts = array();
        $insight_plugin = new EOYBestieInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $year = date('Y');
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("Buffy Summers's Facebook bestie of 2015", $result->headline);
        $this->assertEqual("Buffy Summers's friends must consider Buffy Summers's words definitive - ".
            "no one replied more than three times to Buffy Summers's status updates all year ".
            "(at least since February).", $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "No bestie incomplete data, Twitter");
    }

    public function testNormalTwitterCompleteData() {
        $this->instance = new Instance();
        $this->instance->id = 1;
        $this->instance->network_user_id = '13';
        $this->instance->network_username = 'ev';
        $this->instance->network = 'twitter';

        $builders = self::buildTruckloadOfTwitterData();

         //User fixtures set up in buildTruckloadOfTwitterData
        /*
        $builders[] = FixtureBuilder::build('users', array('user_id'=>18, 'user_name'=>'shutterbug',
        'full_name'=>'Shutter Bug', 'avatar'=>'avatar.jpg', 'is_protected'=>0, 'follower_count'=>10,
        'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>19, 'user_name'=>'linkbaiter',
        'full_name'=>'Link Baiter', 'avatar'=>'avatar.jpg', 'is_protected'=>0, 'follower_count'=>70,
        'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>20, 'user_name'=>'user1',
        'full_name'=>'User 1', 'avatar'=>'avatar.jpg', 'is_protected'=>0, 'follower_count'=>90,
        'network'=>'twitter'));
         */
        //Add replies to friends
        $counter = 1;
        while ($counter < 10) {
            if (($counter % 2) == 1) {
                $in_reply_to_user_id = 18; //shutterbug
            } else {
                $in_reply_to_user_id = 19; //linkbaiter
            }
            $builders[] = FixtureBuilder::build('posts', array('post_id'=>$counter+34358,
            'author_user_id'=>'13', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
            'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post '.$counter,
            'source'=>$source, 'pub_date'=>'-12d'. $pseudo_minute.':00',
            'reply_count_cache'=>($counter==10)?0:rand(0, 4), 'is_protected'=>0,
            'retweet_count_cache'=>floor($counter/2), 'network'=>'twitter', 'in_reply_to_user_id'=>$in_reply_to_user_id,
            'old_retweet_count_cache' => floor($counter/3), 'in_rt_of_user_id' => null,
            'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
            $counter++;
        }

        //Add replies from friends
        $counter = 1;
        while ($counter < 10) {
            if (($counter % 2) == 1) {
                $author_user_id = 19;
                $author_username = 'linkbaiter';
            } else {
                $author_user_id = 20;
                $author_username = 'user1';
            }
            $builders[] = FixtureBuilder::build('posts', array('post_id'=>$counter+5467,
            'author_user_id'=>$author_user_id, 'author_username'=>$author_username,
            'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post '.$counter,
            'source'=>$source, 'pub_date'=>'-15d'. $pseudo_minute.':00',
            'reply_count_cache'=>($counter==10)?0:rand(0, 4), 'is_protected'=>0,
            'retweet_count_cache'=>floor($counter/2), 'network'=>'twitter', 'in_reply_to_user_id'=>'13',
            'old_retweet_count_cache' => floor($counter/3), 'in_rt_of_user_id' => null,
            'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
            $counter++;
        }

        $posts = array();
        $insight_plugin = new EOYBestieInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $year = date('Y');
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("@ev's Twitter bestie of 2015", $result->headline);
        $this->assertEqual("Nobody likes tweeting into the void. @ev and @linkbaiter made Twitter a void-free place ".
            "to tweet this year. @ev tweeted at @linkbaiter <strong>4 times</strong> in 2015, and @linkbaiter ".
            "replied <strong>5 times</strong>. ".
            "OMG you two!", $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "No bestie incomplete data, Twitter");
    }

    public function testNormalFacebookIncompleteData() {
        $this->instance = new Instance();
        $this->instance->id = 1;
        $this->instance->network_username = 'fbuser1';
        $this->instance->network_user_id = '30';
        $this->instance->network = 'facebook';

        $builders = self::buildTruckloadOfFacebookData();

        //User fixtures set up in buildTruckloadOfFacebookData
        /*
        $ub1 = FixtureBuilder::build('users', array('user_id'=>30, 'user_name'=>'fbuser1',
        'full_name'=>'Facebook User 1', 'is_protected'=>0, 'network'=>'facebook'));
        array_push($builders, $ub1);

        $ub2 = FixtureBuilder::build('users', array('user_id'=>31, 'user_name'=>'fbuser2',
        'full_name'=>'Facebook User 2', 'is_protected'=>0, 'network'=>'facebook'));
        array_push($builders, $ub2);

        $ub3 = FixtureBuilder::build('users', array('user_id'=>32, 'user_name'=>'fbuser3',
        'full_name'=>'Facebook User 3', 'is_protected'=>0, 'network'=>'facebook'));
        array_push($builders, $ub3);
         */

        //Add replies to friends
        $counter = 1;
        while ($counter < 10) {
            if (($counter % 2) == 1) {
                $in_reply_to_user_id = 31; //fbuser2
            } else {
                $in_reply_to_user_id = 32; //fbuser3
            }
            $builders[] = FixtureBuilder::build('posts', array('post_id'=>$counter+34358,
            'author_user_id'=>'30', 'author_username'=>'fbuser1',
            'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post '.$counter,
            'source'=>$source, 'pub_date'=>'-12d'. $pseudo_minute.':00',
            'reply_count_cache'=>($counter==10)?0:rand(0, 4), 'is_protected'=>0,
            'retweet_count_cache'=>floor($counter/2), 'network'=>'facebook', 'in_reply_to_user_id'=>$in_reply_to_user_id,
            'old_retweet_count_cache' => floor($counter/3), 'in_rt_of_user_id' => null,
            'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
            $counter++;
        }

        //Add replies from friends
        $counter = 1;
        while ($counter < 10) {
            if (($counter % 2) == 1) {
                $author_user_id = 31;
                $author_username = 'fbuser2';
            } else {
                $author_user_id = 32;
                $author_username = 'fbuser3';
            }
            $builders[] = FixtureBuilder::build('posts', array('post_id'=>$counter+5467,
            'author_user_id'=>$author_user_id, 'author_username'=>$author_username,
            'author_avatar'=>'avatar.jpg', 'post_text'=>'This is post '.$counter,
            'source'=>$source, 'pub_date'=>'-15d'. $pseudo_minute.':00',
            'reply_count_cache'=>($counter==10)?0:rand(0, 4), 'is_protected'=>0,
            'retweet_count_cache'=>floor($counter/2), 'network'=>'facebook', 'in_reply_to_user_id'=>'30',
            'old_retweet_count_cache' => floor($counter/3), 'in_rt_of_user_id' => null,
            'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
            $counter++;
        }

        $posts = array();
        $insight_plugin = new EOYBestieInsight();
        $insight_plugin->generateInsight($this->instance, null, $posts, 3);

        // Assert that insight got inserted
        $insight_dao = new InsightMySQLDAO();
        $year = date('Y');
        $result = $insight_dao->getInsight($insight_plugin->slug, $this->instance->id,
            $year.'-'.$insight_plugin->run_date);
        $this->assertNotNull($result);
        $this->assertIsA($result, "Insight");
        $this->assertEqual("fbuser1's Facebook bestie of 2015", $result->headline);
        $this->assertEqual("Everyone loves getting comments from their friends. In 2015, fbuser2 commented on ".
            "fbuser1's status updates <strong>5 times</strong>, more than anyone else (at least since November). ".
            "Best friends forever!",
            $result->text);

        $this->dumpRenderedInsight($result, $this->instance, "No bestie incomplete data, Twitter");
    }

    protected function buildTruckloadOfTwitterData() {
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

    private function buildTruckloadOfFacebookData() {
        $builders = array();
        $ub1 = FixtureBuilder::build('users', array('user_id'=>30, 'user_name'=>'fbuser1',
        'full_name'=>'Facebook User 1', 'is_protected'=>0, 'network'=>'facebook'));
        array_push($builders, $ub1);

        $ub2 = FixtureBuilder::build('users', array('user_id'=>31, 'user_name'=>'fbuser2',
        'full_name'=>'Facebook User 2', 'is_protected'=>0, 'network'=>'facebook', 'avatar'=>'fbpic.jpg'));
        array_push($builders, $ub2);

        $ub3 = FixtureBuilder::build('users', array('user_id'=>32, 'user_name'=>'fbuser3',
        'full_name'=>'Facebook User 3', 'is_protected'=>0, 'network'=>'facebook'));
        array_push($builders, $ub3);

        $pb1 = FixtureBuilder::build('posts', array('post_id'=>145, 'author_user_id'=>30,
        'author_full_name'=>'Facebook User 3', 'post_text'=>'This is a Facebook post', 'reply_count_cache'=>2,
        'network'=>'facebook', 'pub_date'=>'-3h', 'author_username'=>'Facebook User 3'));
        array_push($builders, $pb1);

        $pb2 = FixtureBuilder::build('posts', array('post_id'=>146, 'author_user_id'=>31,
        'author_full_name'=>'Facebook User 2', 'post_text'=>'@ev Cool!', 'reply_count_cache'=>0,
        'in_reply_to_post_id'=>145, 'network'=>'facebook', 'pub_date'=>'-2h', 'author_username'=>'Facebook User 2'));
        array_push($builders, $pb2);

        $pb3 = FixtureBuilder::build('posts', array('post_id'=>147, 'author_user_id'=>32,
        'author_full_name'=>'Facebook User 3', 'post_text'=>'@ev Rock on!', 'reply_count_cache'=>0,
        'in_reply_to_post_id'=>145, 'network'=>'facebook', 'pub_date'=>'-1h', 'author_username'=>'Facebook User 3'));
        array_push($builders, $pb3);

        return $builders;
    }
}
