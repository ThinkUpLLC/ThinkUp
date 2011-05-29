<?php
/**
 *
 * ThinkUp/tests/TestOfPostMySQLDAO.php
 *
 * Copyright (c) 2009-2011 Gina Trapani, Mark Wilkie, Guillaume Boudreau
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
 * Test of PostMySQL DAO implementation
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Gina Trapani, Mark Wilkie, Guillaume Boudreau
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterPlugin.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/facebook/model/class.FacebookPlugin.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.URLProcessor.php';

class TestOfPostMySQLDAO extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
        $config = Config::getInstance();
        $this->prefix = $config->getValue('table_prefix');
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
        'last_updated'=>'1/1/2005', 'network'=>'twitter'));

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
            // issue #813 -build more of a range of retweet_count_cache and old_retweet_count_cache values for the
            // retweet testing.
            $builders[] = FixtureBuilder::build('posts', array('post_id'=>$counter, 'author_user_id'=>13,
            'author_username'=>'ev', 'author_fullname'=>'Ev Williams', 'author_avatar'=>'avatar.jpg', 
            'post_text'=>'This is post '.$counter, 'source'=>$source, 'pub_date'=>'2006-01-01 00:'.
            $pseudo_minute.':00', 'reply_count_cache'=>rand(0, 4), 'is_protected'=>0,
            'retweet_count_cache'=>floor($counter/2), 'network'=>'twitter',
            'old_retweet_count_cache' => floor($counter/3), 'in_rt_of_user_id' => null,
            'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
            $counter++;
        }

        //Add photo posts from Flickr
        $counter = 0;
        while ($counter < 40) {
            $post_id = $counter + 40;
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
            $builders[] = FixtureBuilder::build('posts', array('post_id'=>$post_id, 'author_user_id'=>18,
            'author_username'=>'shutterbug', 'author_fullname'=>'Shutter Bug', 'author_avatar'=>'avatar.jpg', 
            'post_text'=>'This is image post '.$counter, 'source'=>'Flickr', 'in_reply_to_post_id'=>null,
            'in_retweet_of_post_id'=>null, 'is_protected'=>0,
            'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
            'pub_date'=>'2006-01-02 00:'.$pseudo_minute.':00', 'network'=>'twitter', 'is_geo_encoded'=>0));

            $builders[] = FixtureBuilder::build('links', array('url'=>'http://example.com/'.$counter,
            'expanded_url'=>'http://example.com/'.$counter.'.jpg', 'title'=>'', 'clicks'=>0, 'post_id'=>$post_id, 
            'is_image'=>1));

            $counter++;
        }

        //Add link posts
        $counter = 0;
        while ($counter < 40) {
            $post_id = $counter + 80;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);
            $builders[] = FixtureBuilder::build('posts', array('post_id'=>$post_id, 'author_user_id'=>19,
            'author_username'=>'linkbaiter', 'author_fullname'=>'Link Baiter', 'is_geo_encoded'=>0,
            'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null, 'is_protected'=>0,
            'post_text'=>'This is link post '.$counter, 'source'=>'web', 'pub_date'=>'2006-03-01 00:'.
            $pseudo_minute.':00', 'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'twitter'));

            $builders[] = FixtureBuilder::build('links', array('url'=>'http://example.com/'.$counter,
            'explanded_url'=>'http://example.com/'.$counter.'.html', 'title'=>'Link $counter', 'clicks'=>0, 
            'post_id'=>$post_id, 'is_image'=>0));

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

        $builders[] = FixtureBuilder::build('posts', array('post_id'=>133, 'author_user_id'=>19,
        'author_username'=>'linkbaiter', 'author_fullname'=>'Link Baiter', 'network'=>'twitter', 
        'post_text'=>'@shutterbug This is a link post reply http://example.com/', 'source'=>'web', 
        'pub_date'=>'2006-03-01 00:00:00', 'reply_count_cache'=>0, 'retweet_count_cache'=>0, 
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>41, 'location'=>'Mumbai, Maharashtra, India', 'reply_retweet_distance'=>1500, 
        'is_geo_encoded'=>1));

        $builders[] = FixtureBuilder::build('links', array('url'=>'http://example.com',
        'expanded_url'=>'http://example.com/expanded-link.html', 'title'=>'Link 1', 'clicks'=>0, 'post_id'=>133, 
        'is_image'=>0));

        //Add retweets of a specific post
        //original post
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>134, 'author_user_id'=>22,
        'author_username'=>'quoter', 'author_fullname'=>'Quoter of Quotables', 'network'=>'twitter', 
        'post_text'=>'Be liberal in what you accept and conservative in what you send', 'source'=>'web', 
        'pub_date'=>'2006-03-01 00:00:00', 'reply_count_cache'=>0, 'retweet_count_cache'=>0,
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'location'=>'New Delhi, Delhi, India', 'geo'=>'28.635308,77.22496', 'is_geo_encoded'=>1));
        //retweet 1
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>135, 'author_user_id'=>20,
        'author_username'=>'user1', 'author_fullname'=>'User 1', 'network'=>'twitter', 
        'post_text'=>'RT @quoter Be liberal in what you accept and conservative in what you send', 'source'=>'web', 
        'pub_date'=>'2006-03-01 00:00:00', 'reply_count_cache'=>0, 'retweet_count_cache'=>0, 
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_retweet_of_post_id'=>134, 'location'=>'Chennai, Tamil Nadu, India', 'geo'=>'13.060416,80.249634', 
        'reply_retweet_distance'=>2000, 'is_geo_encoded'=>1, 'in_reply_to_post_id'=>null));
        //retweet 2
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>136, 'author_user_id'=>21,
        'author_username'=>'user2', 'author_fullname'=>'User 2', 'network'=>'twitter', 
        'post_text'=>'RT @quoter Be liberal in what you accept and conservative in what you send', 
        'source'=>'web', 'pub_date'=>'2006-03-01 00:00:00', 'reply_count_cache'=>0, 'retweet_count_cache'=>0, 
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_retweet_of_post_id'=>134, 'location'=>'Dwarka, New Delhi, Delhi, India', 'geo'=>'28.635308,77.22496', 
        'reply_retweet_distance'=>'0', 'is_geo_encoded'=>1));
        //retweet 3
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>137, 'author_user_id'=>19,
        'author_username'=>'linkbaiter', 'author_fullname'=>'Link Baiter', 'network'=>'twitter', 
        'post_text'=>'RT @quoter Be liberal in what you accept and conservative in what you send', 
        'source'=>'web', 'pub_date'=>'2006-03-01 00:00:00', 'reply_count_cache'=>0, 'retweet_count_cache'=>0, 
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
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
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_user_id'=>20, 'in_reply_to_post_id'=>251));

        //Add post by instance not on public timeline
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>143, 'author_user_id'=>24,
        'author_username'=>'notonpublictimeline', 'author_fullname'=>'Not on public timeline', 
        'network'=>'twitter', 'post_text'=>'This post should not be on the public timeline', 
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'source'=>'web', 'pub_date'=>'2006-03-01 00:00:00'));

        //Add replies to specific post
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>144, 'author_user_id'=>20,
        'author_username'=>'user1', 'author_fullname'=>'User 1', 'network'=>'twitter', 
        'post_text'=>'@quoter Indeed, Jon Postel.', 'source'=>'web', 'pub_date'=>'2006-03-01 00:00:00', 
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'is_reply_by_friend'=>1, 'in_reply_to_post_id'=>134, 
        'network'=>'twitter', 'location'=>'New Delhi, Delhi, India', 'geo'=>'28.635308,77.22496', 
        'is_geo_encoded'=>1));

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
        //Add a question
        $builder[] = FixtureBuilder::build('posts', array('author_user_id'=>13, 'author_username'=>'ev',
        'post_text'=>'I need a new cell phone. What should I buy?', 'network'=>'twitter', 'in_reply_to_post_id'=>0,
        'pub_date'=>'-1d'));

        $dao = new PostMySQLDAO();
        $questions = $dao->getAllQuestionPosts(13, 'twitter', 10);

        $this->debug('Questions: ' . $questions);

        $this->assertEqual(sizeof($questions), 1);
        $this->assertEqual($questions[0]->post_text, 'I need a new cell phone. What should I buy?' );

        //Add another question
        $builder[] = FixtureBuilder::build('posts', array('author_user_id'=>13, 'author_username'=>'ev',
        'post_text'=>'Best sushi in NY? downtown', 'network'=>'twitter', 'in_reply_to_post_id'=>0, 'pub_date'=>'-2d'));
        $questions = $dao->getAllQuestionPosts(13, 'twitter', 10);
        $this->assertEqual(sizeof($questions), 2);
        $this->assertEqual($questions[1]->post_text, 'Best sushi in NY? downtown' );
        $this->assertEqual($questions[0]->post_text, 'I need a new cell phone. What should I buy?' );

        //Messages with a question mark in between two characters (e.g. URLs) aren't necessarily questions
        $builder[] = FixtureBuilder::build('posts', array('author_user_id'=>13, 'author_username'=>'ev',
        'post_text'=>'Love this video: http://www.youtube.com/watch?v=PQu-zrE-k5s', 'network'=>'twitter', 
        'in_reply_to_post_id'=>0, 'pub_date'=>'-3d'));
        $questions = $dao->getAllQuestionPosts(13, 'twitter', 10);
        $this->assertEqual(sizeof($questions), 2);

        // test paging
        $questions = $dao->getAllQuestionPosts(13, 'twitter', $count = 1, $page = 1);
        $this->assertEqual($questions[0]->post_text, 'I need a new cell phone. What should I buy?');

        $questions = $dao->getAllQuestionPosts(13, 'twitter', $count = 1, $page = 2);
        $this->assertEqual($questions[0]->post_text, 'Best sushi in NY? downtown');

        // test count
        $questions = $dao->getAllQuestionPosts(13, 'twitter', $count = 1, $page = 1);
        $this->assertEqual(sizeof($questions), 1);

        $questions = $dao->getAllQuestionPosts(13, 'twitter', $count = 2, $page = 1);
        $this->assertEqual(sizeof($questions), 2);

        // test default order
        $questions = $dao->getAllQuestionPosts(13, 'twitter', $count = 1, $page = 1, "';-- SELECT");
        $this->assertEqual($questions[0]->post_text, 'I need a new cell phone. What should I buy?');
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
    }

    /**
     * Test getStrayRepliedToPosts
     */
    public function testGetStrayRepliedToPosts() {
        $dao = new PostMySQLDAO();
        $posts = $dao->getStrayRepliedToPosts(23, 'twitter');
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
        $replies = $dao->getAllReplies(13, 'twitter', 10);
        $this->assertEqual(sizeof($replies), 1);
        $this->assertEqual($replies[0]->post_text, "@ev When will Twitter have a business model?");

        // test paging
        $replies = $dao->getAllReplies(13, 'twitter', $count = 1, $page = 1);
        $this->assertEqual(sizeof($replies), 1);
        $this->assertEqual($replies[0]->post_text, "@ev When will Twitter have a business model?");

        // this query doesn't have a second page, so this should return nothing
        $replies = $dao->getAllReplies(13, 'twitter', $count = 1, $page = 2);
        $this->assertEqual(sizeof($replies), 0);

        // test count
        $replies = $dao->getAllReplies(13, 'twitter', $count = 0, $page = 1);
        $this->assertEqual(sizeof($replies), 0);

        $replies = $dao->getAllReplies(13, 'twitter', $count = 1, $page = 1);
        $this->assertEqual(sizeof($replies), 1);

        $replies = $dao->getAllReplies(18, 'twitter', 10);
        $this->assertEqual(sizeof($replies), 0);

        // test default order_by
        $replies = $dao->getAllReplies(13, 'twitter', 10, 1, "';-- SELECT");
        $this->assertEqual(sizeof($replies), 1);
        $this->assertEqual($replies[0]->post_text, "@ev When will Twitter have a business model?");
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
        $total = $dao->getTotalPostsByUser(18, 'twitter');
        $this->assertEqual($total, 41);

        //non-existent author
        $total = $dao->getTotalPostsByUser(51, 'twitter');
        $this->assertEqual($total, 0);
    }

    /**
     * Test getAllPosts
     */
    public function testGetAllPostsByUsername() {
        $dao = new PostMySQLDAO();
        $posts = $dao->getAllPostsByUsername('shutterbug', 'twitter');
        $this->assertEqual(sizeof($posts), 41);

        //non-existent author
        $posts = $dao->getAllPostsByUsername('idontexist', 'twitter');
        $this->assertEqual(sizeof($posts), 0);
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

    /**
     * Test getPost on a post that exists
     */
    public function testGetPostExists() {
        $dao = new PostMySQLDAO();
        $post = $dao->getPost(10, 'twitter');
        $this->assertTrue(isset($post));
        $this->assertEqual($post->post_text, 'This is post 10');
        //link gets set
        $this->assertTrue(isset($post->link));
        //no link, so link member variables do not get set
        $this->assertTrue(!isset($post->link->id));
    }

    /**
     * Test getPost on a post that does not exist
     */
    public function testGetPostDoesNotExist(){
        $dao = new PostMySQLDAO();
        $post = $dao->getPost(100000001, 'twitter');
        $this->assertTrue(!isset($post));
    }

    /**
     * Test getStandaloneReplies
     */
    public function testGetStandaloneReplies() {
        $dao = new PostMySQLDAO();
        $posts = $dao->getStandaloneReplies('jack', 'twitter', 15);

        $this->assertEqual(sizeof($posts), 10);
        $this->assertEqual($posts[0]->post_text, 'Hey @ev and @jack should fix Twitter - post 9',
        "Standalone mention");
        $this->assertEqual($posts[0]->author->username, 'user2', "Post author");

        $posts = $dao->getStandaloneReplies('ev', 'twitter', 15);

        $this->assertEqual(sizeof($posts), 11);
        $this->assertEqual($posts[0]->post_text, 'Hey @ev and @jack should fix Twitter - post 9',
        "Standalone mention");
        $this->assertEqual($posts[0]->author->username, 'user2', "Post author");
    }

    /**
     * Test getRepliesToPost
     */
    public function testGetRepliesToPost() {
        $dao = new PostMySQLDAO();
        // Default Sorting
        $posts = $dao->getRepliesToPost(41, 'twitter');
        $this->assertEqual(sizeof($posts), 3);
        $this->assertEqual($posts[0]->post_text, '@shutterbug Nice shot!', "post reply");
        $this->assertEqual($posts[0]->author->username, 'user1', "Post author");
        $this->assertEqual($posts[0]->location,'New Delhi, Delhi, India');

        $this->assertEqual($posts[1]->location,'Chennai, Tamil Nadu, India');
        $this->assertEqual($posts[2]->post_text, '@shutterbug This is a link post reply http://example.com/',
        "post reply");
        $this->assertEqual($posts[2]->post_id, 133, "post ID");
        $this->assertEqual($posts[2]->author->username, 'linkbaiter', "Post author");
        $this->assertEqual($posts[2]->link->expanded_url, 'http://example.com/expanded-link.html', "Expanded URL");

        $this->assertEqual($posts[2]->location,'Mumbai, Maharashtra, India');

        // Sorting By Proximity
        $posts = $dao->getRepliesToPost(41, 'twitter', 'location');
        $this->assertEqual(sizeof($posts), 3);
        $this->assertEqual($posts[0]->post_text, '@shutterbug Nice shot!', "post reply");
        $this->assertEqual($posts[0]->author->username, 'user1', "Post author");
        $this->assertEqual($posts[0]->location,'New Delhi, Delhi, India');

        $this->assertEqual($posts[1]->location,'Mumbai, Maharashtra, India');
        $this->assertEqual($posts[1]->post_text, '@shutterbug This is a link post reply http://example.com/',
        "post reply");
        $this->assertEqual($posts[1]->post_id, 133, "post ID");
        $this->assertEqual($posts[1]->author->username, 'linkbaiter', "Post author");
        $this->assertEqual($posts[1]->link->expanded_url, 'http://example.com/expanded-link.html', "Expanded URL");

        $this->assertEqual($posts[2]->location,'Chennai, Tamil Nadu, India');

        // Test date ordering for Facebook posts
        $builders = $this->buildFacebookPostAndReplies();
        $posts = $dao->getRepliesToPost(145, 'facebook');
        $this->assertEqual(sizeof($posts), 2);
        $this->assertEqual($posts[0]->post_text, '@ev Cool!', "post reply");
        $this->assertEqual($posts[1]->post_text, '@ev Rock on!', "post reply");

        // test paging
        $posts= $dao->getRepliesToPost(41, 'twitter', 'location', $unit = 'km', $is_public = false,
        $count = 1, $page = 1);
        $this->assertEqual(sizeof($posts), 1);
        $this->assertEqual($posts[0]->post_text, '@shutterbug Nice shot!', "post reply");
        $this->assertEqual($posts[0]->author->username, 'user1', "Post author");
        $this->assertEqual($posts[0]->location,'New Delhi, Delhi, India');

        $posts= $dao->getRepliesToPost(41, 'twitter', 'location', $unit = 'km', $is_public = false,
        $count = 1, $page = 2);
        $this->assertEqual(sizeof($posts), 1);
        $this->assertEqual($posts[0]->post_text, '@shutterbug This is a link post reply http://example.com/');
        $this->assertEqual($posts[0]->author->username, 'linkbaiter');
        $this->assertEqual($posts[0]->location,'Mumbai, Maharashtra, India');

        $posts= $dao->getRepliesToPost(41, 'twitter', 'location', $unit = 'km', $is_public = false,
        $count = 1, $page = 3);
        $this->assertEqual(sizeof($posts), 1);
        $this->assertEqual($posts[0]->location,'Chennai, Tamil Nadu, India');

        // test count
        $posts= $dao->getRepliesToPost(41, 'twitter', 'location', $unit = 'km', $is_public = false,
        $count = 1, $page = 1);
        $this->assertEqual(sizeof($posts), 1);
        $posts= $dao->getRepliesToPost(41, 'twitter', 'location', $unit = 'km', $is_public = false,
        $count = 2, $page = 1);
        $this->assertEqual(sizeof($posts), 2);
        $posts= $dao->getRepliesToPost(41, 'twitter', 'location', $unit = 'km', $is_public = false,
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
        'network'=>'facebook'));
        array_push($builders, $pb1);

        $pb2 = FixtureBuilder::build('posts', array('post_id'=>146, 'author_user_id'=>31,
        'author_full_name'=>'Facebook User 2', 'post_text'=>'@ev Cool!', 'reply_count_cache'=>0,
        'in_reply_to_post_id'=>145, 'network'=>'facebook'));
        array_push($builders, $pb2);

        $pb3 = FixtureBuilder::build('posts', array('post_id'=>147, 'author_user_id'=>32,
        'author_full_name'=>'Facebook User 3', 'post_text'=>'@ev Rock on!', 'reply_count_cache'=>0,
        'in_reply_to_post_id'=>145, 'network'=>'facebook'));
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
        'network'=>'facebook page'));
        array_push($builders, $pb1);

        $pb2 = FixtureBuilder::build('posts', array('post_id'=>146, 'author_user_id'=>31,
        'author_full_name'=>'Facebook User 2', 'post_text'=>'@ev Cool!', 'reply_count_cache'=>0,
        'in_reply_to_post_id'=>145, 'network'=>'facebook page'));
        array_push($builders, $pb2);

        $pb3 = FixtureBuilder::build('posts', array('post_id'=>147, 'author_user_id'=>32,
        'author_full_name'=>'Facebook User 3', 'post_text'=>'@ev Rock on!', 'reply_count_cache'=>0,
        'in_reply_to_post_id'=>145, 'network'=>'facebook page'));
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
            if($cnt == 1) { $post1 = $post; }
            if($cnt == 2) { $post2 = $post; }
            if($cnt == 3) { $post3 = $post; }
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
        $posts = $dao->getRelatedPostsArray(134, 'twitter');
        $this->assertEqual(count($posts), 5);
        $this->assertIsA($posts[0], 'Array');
        $posts = $dao->getRelatedPosts(1344545, 'twitter');
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
        $this->assertEqual($dao->addPost($vals), 0, "Post not inserted, not all values set");

        $vals['author_user_id']= 22;
        $vals['post_text']="Go confidently in the direction of your dreams! Live the life you've imagined.";
        $vals['pub_date']='3/1/2010';
        $vals['source']='web';
        $vals['network']= 'twitter';
        $vals['is_protected'] = 1;

        //add post with insufficient location data
        $this->assertEqual($dao->addPost($vals), 1, "Post inserted");
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
        $this->assertEqual($dao->addPost($vals), 1, "Post inserted");
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

        //test add post that does exist
        $vals['post_id']=129;
        $this->assertEqual($dao->addPost($vals), 0, "Post exists, nothing inserted");

        //test add reply, check cache count
        $vals['post_id']=251;
        $vals['in_reply_to_post_id']= 129;
        $this->assertEqual($dao->addPost($vals), 1, "Reply inserted");
        $post = $dao->getPost(129, 'twitter');
        $this->assertEqual($post->reply_count_cache, 1, "reply count got updated");

        //test add retweet, check cache count
        $vals['post_id']=252;
        $vals['in_reply_to_post_id']= '';
        $vals['in_retweet_of_post_id']= 128;
        $this->assertEqual($dao->addPost($vals), 1, "Retweet inserted");
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

        $this->assertEqual($dao->addPost($vals), 1, "Post inserted");
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

        $this->assertEqual($dao->addPost($vals), 1, "Post inserted");
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
        $stmt = PostMySQLDAO::$PDO->query( "select * from " . $this->prefix . 'posts where post_id=1000' );
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
        $stmt = PostMySQLDAO::$PDO->query( "select * from " . $this->prefix . 'posts where post_id=1000' );
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
            $vals['in_retweet_of_post_id'] = 13708601491193856;
            $vals['in_rt_of_user_id'] = 20542737;

            // for a native RT, the RT'd post info includes the original post
            $retweeted_post = array();
            $rtp = array();
            $rtp['post_id'] = 13708601491193856;
            $rtp['author_user_id'] = 20542737;
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
        $post = $dao->getPost(13708601491193856, 'twitter');
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
            $vals['in_retweet_of_post_id'] = 13708601491193856;
            $vals['in_rt_of_user_id'] = 20542737;

            // for a native RT, the RT'd post info includes the original post
            $retweeted_post = array();
            $rtp = array();
            $rtp['post_id'] = 13708601491193856;
            $rtp['author_user_id'] = 20542737;
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
        $vals['in_retweet_of_post_id'] = 13708601491193856;
        $vals['in_rt_of_user_id'] = 20542737;
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
        $vals['in_retweet_of_post_id'] = 13708601491193856;
        $vals['in_rt_of_user_id'] = 20542737;
        $dao->addPost($vals);
        $counter++;

        $post = $dao->getPost(13708601491193856, 'twitter');
        $this->assertEqual($post->retweet_count_cache, 10);
        $this->assertEqual($post->old_retweet_count_cache, 2);
        $this->assertEqual($post->retweet_count_api, 100);
        // this is the value displayed in the UI-- the sum should be the higher reported value from twitter
        // for the native RTs, plus the old-style rt count.
        $this->assertEqual($post->all_retweets, 102);
        $this->assertEqual($post->rt_threshold, 1);

    }

    /**
     * Test getTotalPostsByUser
     */
    public function testGetTotalPostsByUser() {
        $pdao = new PostMySQLDAO();
        $total_posts = $pdao->getTotalPostsByUser(13, 'twitter');

        $this->assertTrue($total_posts == 41);
    }

    /**
     * Test assignParent
     */
    public function testAssignParent() {
        //Add two "parent" posts
        $builders = array();
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>550, 'author_user_id'=>19,
        'author_username'=>'linkbaiter', 'author_fullname'=>'Link Baiter', 'post_text'=>'This is parent post 1',
        'reply_count_cache'=>1, 'retweet_count_cache'=>0));

        $builders[] = FixtureBuilder::build('posts', array('post_id'=>551, 'author_user_id'=>19,
        'author_fullname'=>'Link Baiter', 'post_text'=>'This is parent post 2', 'reply_count_cache'=>0,
        'retweet_count_cache'=>0));

        //Add a post with the parent post 550
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>552, 'author_user_id'=>19,
        'author_username'=>'linkbaiter', 'author_fullname'=>'Link Baiter',
        'post_text'=>'This is a reply with the wrong parent', 'reply_count_cache'=>0, 'retweet_count_cache'=>0,
        'in_reply_to_post_id'=>550));

        $pdao = new PostMySQLDAO();

        $post = $pdao->getPost(552, 'twitter');
        //Assert parent post is 550
        $this->assertEqual($post->in_reply_to_post_id, 550);

        //Change parent post to 551
        $pdao->assignParent(551, 552, 'twitter');
        $child_post = $pdao->getPost(552, 'twitter');
        //Assert parent post is now 551
        $this->assertEqual($child_post->in_reply_to_post_id, 551);

        //Assert old parent post has one fewer reply total
        $old_parent = $pdao->getPost(550, 'twitter');
        $this->assertEqual($old_parent->reply_count_cache, 0);

        //Assert new parent post has one more reply total
        $new_parent = $pdao->getPost(551, 'twitter');
        $this->assertEqual($new_parent->reply_count_cache, 1);
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
        $pdao = new PostMySQLDAO();
        $posts = $pdao->getMostRepliedToPostsInLastWeek('user3', 'twitter', 5);
        $this->assertEqual(sizeof($posts), 5);
        $this->assertEqual($posts[0]->reply_count_cache, 7);
        $this->assertEqual($posts[1]->reply_count_cache, 6);

        $posts = $pdao->getMostRepliedToPostsInLastWeek('user2', 'twitter', 5);
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
        $pdao = new PostMySQLDAO();
        $posts = $pdao->getMostRetweetedPostsInLastWeek('user3', 'twitter', 5);
        $this->assertEqual(sizeof($posts), 5);
        $this->assertEqual($posts[0]->retweet_count_cache, 7);
        $this->assertEqual($posts[1]->retweet_count_cache, 6);
        $this->assertTrue(($posts[0]->retweet_count_cache + $posts[0]->old_retweet_count_cache) >=
        ($posts[1]->retweet_count_cache + $posts[1]->old_retweet_count_cache));

        $posts = $pdao->getMostRetweetedPostsInLastWeek('user2', 'twitter', 5);
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
        $pdao = new PostMySQLDAO();

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

        $post = $pdao->getPost(1500, 'twitter');
        $this->assertEqual($post->rt_threshold, 0);
        $this->assertEqual($post->all_retweets, 155);
        $post = $pdao->getPost(1501, 'twitter');
        $this->assertEqual($post->rt_threshold, 0);
        $this->assertEqual($post->all_retweets, 97);
        $post = $pdao->getPost(1502, 'twitter');
        $this->assertEqual($post->rt_threshold, 1);
        $this->assertEqual($post->all_retweets, 105);
    }

    /**
     * Test getPostsToGeoencode
     */
    public function testGetPoststoGeoencode() {
        $dao = new PostMySQLDAO();
        $posts = $dao->getPoststoGeoencode();
        $this->assertEqual(count($posts), 136);
        $this->assertIsA($posts, "array");
    }

    /**
     * Test setGeoencodedPost
     */
    public function testSetGeoencodedPost() {
        $dao = new PostMySQLDAO();
        $setData = $dao->setGeoencodedPost(131, 1);
        $post = $dao->getPost(131, 'twitter');
        $this->assertEqual($post->is_geo_encoded, 1);
        $this->assertEqual($post->reply_retweet_distance, 0);

        $setData = $dao->setGeoencodedPost(131, 1, 'New Delhi', '78', 100);
        $post = $dao->getPost(131, 'twitter');
        $this->assertEqual($post->is_geo_encoded, 1);
        $this->assertEqual($post->geo, 78);
        $this->assertEqual($post->location, 'New Delhi');
        $this->assertEqual($post->reply_retweet_distance, 100);

        //Since both of $location and $geodata are not defined, only is_geo_encoded field is updated
        $setData = $dao->setGeoencodedPost(131, 2, '', 29);
        $post = $dao->getPost(131, 'twitter');
        $this->assertEqual($post->is_geo_encoded, 2);
        $this->assertEqual($post->geo, '78');
        $this->assertEqual($post->location, 'New Delhi');

        //Since both of $location and $geodata are not defined, only is_geo_encoded field is updated
        $setData = $dao->setGeoencodedPost(131, 1, 'Dwarka');
        $post = $dao->getPost(131, 'twitter');
        $this->assertEqual($post->geo, '78');
        $this->assertEqual($post->location, 'New Delhi');
    }

    /**
     * Test getClientsUsedByUserOnNetwork
     */
    public function testGetClientsUsedByUserOnNetwork() {
        $dao = new PostMySQLDAO();
        list($all_time_clients_usage, $latest_clients_usage) = $dao->getClientsUsedByUserOnNetwork(13, 'twitter');
        $this->assertIsA($all_time_clients_usage, 'array');
        $this->assertEqual(sizeof($all_time_clients_usage), 3);
        $this->assertEqual($all_time_clients_usage['Tweetie for Mac'], 14);
        $this->assertEqual($all_time_clients_usage['web'], 14);
        $this->assertEqual($all_time_clients_usage['Tweet Button'], 13);
        $keys = array_keys($all_time_clients_usage);
        $this->assertEqual($keys[0], 'Tweetie for Mac');
        $this->assertEqual($keys[1], 'web');
        $this->assertEqual($keys[2], 'Tweet Button');

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
        $counter = 1000;
        $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
        $source = '<a href="http://twitter.com" rel="nofollow">Tweetie for Mac</a>';
        $q = "INSERT IGNORE INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache, network) VALUES 
        ($counter, 13, 'ev', 'Ev Williams', 'avatar.jpg', 
        'This is post $counter', '$source', '2006-01-01 00:$pseudo_minute:00', ".rand(0, 4).", 5, 'twitter');";
        $res = PDODAO::$PDO->exec($q);
        $this->assertEqual($res, 1);

        $q = "INSERT IGNORE INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
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
        $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
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
    }
}