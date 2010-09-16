<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test of PostMySQL DAO implementation
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfPostMySQLDAO extends ThinkUpUnitTestCase {
    /**
     *
     * @var PostMySQLDAO
     */
    protected $dao;
    /**
     * Constructor
     */
    public function __construct() {
        $this->UnitTestCase('PostMySQLDAO class test');
    }

    public function setUp() {
        parent::setUp();
        $config = Config::getInstance();
        $this->prefix = $config->getValue('table_prefix');

        $this->DAO = new PostMySQLDAO();
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

        //Add photo posts from Flickr
        $counter = 0;
        while ($counter < 40) {
            $post_id = $counter + 40;
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
            $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
            post_text, source, pub_date, reply_count_cache, retweet_count_cache, network) 
            VALUES ($post_id, 18, 'shutterbug', 'Shutter Bug', 'avatar.jpg', 'This is image post $counter', 'Flickr', 
            '2006-01-02 00:$pseudo_minute:00', 0, 0, 'twitter');";
            PDODAO::$PDO->exec($q);

            $q = "INSERT INTO tu_links (url, expanded_url, title, clicks, post_id, is_image)
            VALUES ('http://example.com/".$counter."', 'http://example.com/".$counter.".jpg', '', 0, $post_id, 1);";
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
            'This is link post $counter', 'web', '2006-03-01 00:$pseudo_minute:00', 0, 0, 'twitter');";
            PDODAO::$PDO->exec($q);

            $q = "INSERT INTO tu_links (url, expanded_url, title, clicks, post_id, is_image)
            VALUES ('http://example.com/".$counter."', 'http://example.com/".$counter.".html', 
            'Link $counter', 0, $post_id, 0);";
            PDODAO::$PDO->exec($q);

            $counter++;
        }

        //Add mentions
        $counter = 0;
        while ($counter < 10) {
            $post_id = $counter + 120;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);
            if ( ($counter/2) == 0 ) {
                $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
                post_text, source, pub_date, reply_count_cache, retweet_count_cache, location, network) 
                VALUES ($post_id, 20, 'user1', 'User 1', 'avatar.jpg', 
                'Hey @ev and @jack thanks for founding Twitter  post $counter', 'web', 
                '2006-03-01 00:$pseudo_minute:00', 0, 0, 'New Delhi', 'twitter');";
            } else {
                $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname,
                author_avatar, post_text, source, pub_date, reply_count_cache, retweet_count_cache, place, network) 
                VALUES ($post_id, 21, 'user2', 'User 2', 'avatar.jpg', 
                'Hey @ev and @jack should fix Twitter - post $counter', 'web', 
                '2006-03-01 00:$pseudo_minute:00', 0, 0, 'New Delhi', 'twitter');";
            }
            PDODAO::$PDO->exec($q);

            $counter++;
        }


        //Add replies to specific post
        $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache, in_reply_to_post_id, location,
        reply_retweet_distance, is_geo_encoded) 
        VALUES (131, 20, 'user1', 'User 1', 'avatar.jpg', '@shutterbug Nice shot!', 'web', 
        '2006-03-01 00:00:00', 0, 0, 41, 'New Delhi, Delhi, India', 0, 1);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache, in_reply_to_post_id, location,
        reply_retweet_distance, is_geo_encoded) 
        VALUES (132, 21, 'user2', 'User 2', 'avatar.jpg', '@shutterbug Nice shot!', 'web', 
        '2006-03-01 00:00:00', 0, 0, 41, 'Chennai, Tamil Nadu, India', 2000, 1);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache, in_reply_to_post_id, location,
        reply_retweet_distance, is_geo_encoded) 
        VALUES (133, 19, 'linkbaiter', 'Link Baiter', 'avatar.jpg', 
        '@shutterbug This is a link post reply http://example.com/', 'web', '2006-03-01 00:00:00', 0, 0, 41,
        'Mumbai, Maharashtra, India', 1500, 1);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_links (url, expanded_url, title, clicks, post_id, is_image)
        VALUES ('http://example.com/', 'http://example.com/expanded-link.html', 'Link 1', 0, 133, 0);";
        PDODAO::$PDO->exec($q);

        //Add replies to specific post
        $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache, is_reply_by_friend, in_reply_to_post_id,
        network, location, geo, is_geo_encoded) VALUES (144, 20, 'user1', 'User 1', 'avatar.jpg',
        '@shutterbug Nice shot!', 'web', '2006-03-01 00:00:00', 0, 0, 1, 134, 'twitter', 'New Delhi, Delhi, India',
        '28.635308,77.22496', 1);";
        PDODAO::$PDO->exec($q);

        //Add retweets of a specific post
        //original post
        $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache, location, geo, is_geo_encoded) 
        VALUES (134, 22, 'quoter', 'Quoter of Quotables', 'avatar.jpg', 
        'Be liberal in what you accept and conservative in what you send', 'web', '2006-03-01 00:00:00', 0, 0,
        'New Delhi, Delhi, India' , '28.635308,77.22496', 1);";
        PDODAO::$PDO->exec($q);
        //retweet 1
        $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache, in_retweet_of_post_id,
        location, geo, reply_retweet_distance, is_geo_encoded) 
        VALUES (135, 20, 'user1', 'User 1', 'avatar.jpg', 
        'RT @quoter Be liberal in what you accept and conservative in what you send', 'web',
        '2006-03-01 00:00:00', 0, 0, 134, 'Chennai, Tamil Nadu, India', '13.060416,80.249634', 2000, 1);";
        PDODAO::$PDO->exec($q);
        //retweet 2
        $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache, in_retweet_of_post_id,
        location, geo, reply_retweet_distance, is_geo_encoded) 
        VALUES (136, 21, 'user2', 'User 2', 'avatar.jpg', 
        'RT @quoter Be liberal in what you accept and conservative in what you send', 'web', 
        '2006-03-01 00:00:00', 0, 0, 134, 'Dwarka, New Delhi, Delhi, India', '28.635308,77.22496', 0, 1);";
        PDODAO::$PDO->exec($q);
        //retweet 3
        $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache, in_retweet_of_post_id,
        location, geo, reply_retweet_distance, is_geo_encoded) 
        VALUES (137, 19, 'linkbaiter', 'Link Baiter', 'avatar.jpg', 
        'RT @quoter Be liberal in what you accept and conservative in what you send', 'web', 
        '2006-03-01 00:00:00', 0, 0, 134, 'Mumbai, Maharashtra, India', '19.017656,72.856178', 1500, 1);";
        PDODAO::$PDO->exec($q);

        //Add reply back
        $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache, in_reply_to_user_id, 
        in_reply_to_post_id) VALUES (138, 18, 'shutterbug', 'Shutterbug', 'avatar.jpg', 
        '@user2 Thanks!', 'web', '2006-03-01 00:00:00', 0, 0, 21, 132);";
        PDODAO::$PDO->exec($q);

        //Add user exchange
        $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache, in_reply_to_user_id) 
        VALUES (139, 20, 'user1', 'User 1', 'avatar.jpg', '@ev When will Twitter have a business model?', 
        'web', '2006-03-01 00:00:00', 0, 0, 13);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache, 
        in_reply_to_user_id, in_reply_to_post_id) VALUES (140, 13, 'ev', 'Ev Williams', 'avatar.jpg', 
        '@user1 Soon....', 'web', '2006-03-01 00:00:00', 0, 0, 20, 139);";
        PDODAO::$PDO->exec($q);

        //Add posts replying to post not in the system
        $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache, in_reply_to_user_id, 
        in_reply_to_post_id) VALUES (141, 23, 'user3', 'User 3', 'avatar.jpg', 
        '@user4 I\'m replying to a post not in the TT db', 'web', '2006-03-01 00:00:00', 0, 0, 20, 150);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache, in_reply_to_user_id, in_reply_to_post_id) 
        VALUES (142, 23, 'user3', 'User 3', 'avatar.jpg', 
        '@user4 I\'m replying to another post not in the TT db', 'web', '2006-03-01 00:00:00', 0, 0, 20, 151);";
        PDODAO::$PDO->exec($q);

        //Add post by instance not on public timeline
        $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date) VALUES (143, 24, 'notonpublictimeline', 'Not on public timeline', 'avatar.jpg', 
        'This post should not be on the public timeline', 'web', '2006-03-01 00:00:00');";
        PDODAO::$PDO->exec($q);

    }

    public function tearDown() {
        parent::tearDown();
    }

    /**
     * Test constructor
     */
    public function testConstructor() {
        $dao = new PostMySQLDAO();
        $this->assertTrue(isset($dao));
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
     * Test getLikelyOrphansForParent
     */
    public function testGetLikelyOrphansForParent() {
        $dao = new PostMySQLDAO();
        $posts = $dao->getLikelyOrphansForParent('2006-03-01', 13, 'ev', 'twitter', 10);
        $this->assertEqual(sizeof($posts), 9);
        $this->assertEqual($posts[0]->post_text, "Hey @ev and @jack should fix Twitter - post 1");
    }
     
    /**
     * Test getStrayRepliedToPosts
     */
    public function testGetStrayRepliedToPosts() {
        $dao = new PostMySQLDAO();
        $posts = $dao->getStrayRepliedToPosts(23, 'twitter');
        $this->assertEqual(sizeof($posts), 2);
        $this->assertEqual($posts[0]["in_reply_to_post_id"], 150);
        $this->assertEqual($posts[1]["in_reply_to_post_id"], 151);
    }
     
    /**
     * Test isPostByPublicInstance
     */
    public function testIsPostByPublicInstance() {
        $dao = new PostMySQLDAO();
        //post by ev (public instance)
        $this->assertTrue($dao->isPostByPublicInstance(140, 'twitter'));
        //post by notapublicinstance
        $this->assertTrue(!$dao->isPostByPublicInstance(143, 'twitter'));
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
    }

    /**
     * Test getMostRetweetedPosts
     */
    public function testGetMostRetweetedPosts() {
        $dao = new PostMySQLDAO();
        $posts = $dao->getMostRetweetedPosts(13, 'twitter', 10);
        $prev_count = $posts[0]->retweet_count_cache;
        foreach ($posts as $post) {
            $this->assertTrue($post->retweet_count_cache >= $prev_count, "previous count ".$prev_count.
            " should be less than or equal to this post's count of ".$post->retweet_count_cache);
            $prev_count = $post->reply_count_cache;
        }
    }

    /**
     * Test getAllReplies
     */
    public function testGetAllReplies() {
        $dao = new PostMySQLDAO();
        $replies = $dao->getAllReplies(13, 'twitter', 10);
        $this->assertTrue(sizeof($replies), 10);
        $this->assertEqual(sizeof($replies), 1);
        $this->assertEqual($replies[0]->post_text, "@ev When will Twitter have a business model?");

        $replies = $dao->getAllReplies(18, 'twitter', 10);
        $this->assertEqual(sizeof($replies), 0);
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
    }

    /**
     * Test getMostRetweetedPostsIterator
     */
    public function testGetMostRetweetedPostsIterator() {
        $dao = new PostMySQLDAO();
        //Add posts with replies by user3, who is on the public timeline with retweet counts in the last 9 days
        $counter = 0;
        $id = 200;
        $builders = array();
        while ($counter < 40) {
            $id += $counter;
            $builders[] = FixtureBuilder::build('posts', array(
                'id'=>$id, 
                'post_id'=>(144+$counter),
                'author_user_id'=>23,
                'author_username'=>'user3',
                'pub_date'=>'-'.$counter.'d',
                'retweet_count_cache'=>$counter));
            $counter++;
        }
        $posts_it = $dao->getMostRetweetedPostsIterator('user3', 'twitter', 5, 7);
        $cnt = 0;
        foreach($posts_it as $key => $value) {
            $cnt++;
        }
        $this->assertEqual($cnt, 5);
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

        //less than count, no replies --there is 1 reply, so 41-1=40
        $posts = $dao->getAllPosts(18, 'twitter', 50, false);
        $this->assertEqual(sizeof($posts), 40);

        //non-existent author
        $posts = $dao->getAllPosts(30, 'twitter', 10);
        $this->assertEqual(sizeof($posts), 0);
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
    }

    /**
     * Test getPublicRepliesToPost
     */
    public function testGetPublicRepliesToPost() {
        $dao = new PostMySQLDAO();
        $posts = $dao->getPublicRepliesToPost(41, 'twitter');
        $this->assertEqual(sizeof($posts), 2);
        $this->assertEqual($posts[0]->post_text, '@shutterbug Nice shot!', "post reply");
        $this->assertEqual($posts[0]->author->username, 'user1', "Post author");

        $this->assertEqual($posts[1]->post_text, '@shutterbug This is a link post reply http://example.com/',
        "post reply");
        $this->assertEqual($posts[1]->post_id, 133, "post ID");
        $this->assertEqual($posts[1]->author->username, 'linkbaiter', "Post author");
        $this->assertEqual($posts[1]->link->expanded_url, 'http://example.com/expanded-link.html', "Expanded URL");
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
    }

    /**
     * Test getRelatedPosts
     */
    public function testGetRelatedPosts() {
        $dao = new PostMySQLDAO();
        $posts = $dao->getRelatedPosts(134, 'twitter');
        $this->assertEqual(count($posts), 5);
        $posts = $dao->getRelatedPosts(1344545, 'twitter');
        $this->assertEqual(count($posts), 0);
    }

    /**
     * Test getPostReachViaRetweets
     */
    public function testGetPostReachViaRetweets() {
        $dao = new PostMySQLDAO();
        $total = $dao->getPostReachViaRetweets(134, 'twitter');
        $this->assertEqual($total, (90+80+70));

        $total = $dao->getPostReachViaRetweets(130, 'twitter');
        $this->assertEqual($total, 0);
    }

    /**
     * Test function getPostsAuthorHasRepliedTo
     */
    public function testGetPostsAuthorHasRepliedTo(){
        $dao = new PostMySQLDAO();
        $posts_replied_to = $dao->getPostsAuthorHasRepliedTo(18, 10, 'twitter');
        $this->assertEqual($posts_replied_to[0]["questioner_username"], "user2");
        $this->assertEqual($posts_replied_to[0]["question"], "@shutterbug Nice shot!");
        $this->assertEqual($posts_replied_to[0]["answerer_username"], "shutterbug");
        $this->assertEqual($posts_replied_to[0]["answer"], "@user2 Thanks!");

        $posts_replied_to = $dao->getPostsAuthorHasRepliedTo(13, 10, 'twitter');
        $this->assertEqual($posts_replied_to[0]["questioner_username"], "user1");
        $this->assertEqual($posts_replied_to[0]["question"], "@ev When will Twitter have a business model?");
        $this->assertEqual($posts_replied_to[0]["answerer_username"], "ev");
        $this->assertEqual($posts_replied_to[0]["answer"], "@user1 Soon....");
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

        $posts_replied_to = $dao->getPostsAuthorHasRepliedTo(13, 20, 'twitter');
        $this->assertEqual(sizeof($posts_replied_to), 1);
        $this->assertEqual($posts_replied_to[0]["questioner_username"], "user1");
        $this->assertEqual($posts_replied_to[0]["question"], "@ev When will Twitter have a business model?");
        $this->assertEqual($posts_replied_to[0]["answerer_username"], "ev");
        $this->assertEqual($posts_replied_to[0]["answer"], "@user1 Soon....");
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

    /**
     * Test addPost
     */
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
        $vals['is_protected'] = 0;

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
        $this->assertEqual($post->in_reply_to_post_id, null);
        $this->assertFalse($post->is_reply_by_friend);
        $this->assertEqual($post->is_geo_encoded, 0);

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
        $this->assertEqual($post->retweet_count_cache, 1, "retweet count got updated");
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
     * Test get pages 1 of posts by public instances
     */
    public function testGetPageOneOfPublicPosts() {
        //Instantiate DAO
        $pdao = new PostMySQLDAO();

        //Get page 1 containing 15 public posts
        $page_of_posts = $pdao->getPostsByPublicInstances(1, 15);

        //Assert DAO returns 15 posts
        $this->assertTrue(sizeof($page_of_posts) == 15);

        //Assert first post 1 contains the right text
        $this->assertTrue($page_of_posts[0]->post_text == "This is post 39");

        //Asert last post 15 contains the right text
        $this->assertTrue($page_of_posts[14]->post_text == "This is post 25");
    }

    /**
     * Test get page 2 of posts by public instances
     */
    public function testGetPageTwoOfPublicPosts() {
        $pdao = new PostMySQLDAO();

        $page_of_posts = $pdao->getPostsByPublicInstances(2, 15);

        $this->assertTrue(sizeof($page_of_posts) == 15);
        $this->assertTrue($page_of_posts[0]->post_text == "This is post 24");
        $this->assertTrue($page_of_posts[14]->post_text == "This is post 10");
    }

    /**
     * Test get pages 3 of posts by public instances
     */
    public function testGetPageThreeOfPublicPosts() {
        $pdao = new PostMySQLDAO();

        $page_of_posts = $pdao->getPostsByPublicInstances(3, 15);

        //Assert DAO returns 10 posts
        $this->assertTrue(sizeof($page_of_posts) == 10);

        $this->assertTrue($page_of_posts[0]->post_text == "This is post 9");
        $this->assertTrue($page_of_posts[9]->post_text == "This is post 0");
    }

    /**
     * Test get the total number of posts and pages by public instance
     */
    public function testGetTotalPagesAndPostsByPublicInstances() {
        $pdao = new PostMySQLDAO();

        $totals = $pdao->getTotalPagesAndPostsByPublicInstances(15);

        $this->assertTrue($totals["total_posts"] == 40);
        $this->assertTrue($totals["total_pages"] == 3);
    }

    /**
     * Test get a page of photos posts by public instances
     */
    public function testGetPageOneOfPhotoPublicPosts() {
        $pdao = new PostMySQLDAO();

        $page_of_posts = $pdao->getPhotoPostsByPublicInstances(1, 15);
        $this->assertTrue(sizeof($page_of_posts) == 15);
        $this->assertTrue($page_of_posts[0]->post_text == "This is image post 39");
        $this->assertTrue($page_of_posts[14]->post_text == "This is image post 25");
    }
    /**
     * Test get a page of photos posts by public instances
     */
    public function testGetPageTwoOfPhotoPublicPosts() {
        $pdao = new PostMySQLDAO();

        $page_of_posts = $pdao->getPhotoPostsByPublicInstances(2, 15);
        $this->assertTrue(sizeof($page_of_posts) == 15);
        $this->assertTrue($page_of_posts[0]->post_text == "This is image post 24");
        $this->assertTrue($page_of_posts[14]->post_text == "This is image post 10");
    }

    /**
     * Test get a page of photo posts by public instances
     */
    public function testGetPageThreeOfPhotoPublicPosts() {
        $pdao = new PostMySQLDAO();

        $page_of_posts = $pdao->getPhotoPostsByPublicInstances(3, 15);
        $this->assertTrue(sizeof($page_of_posts) == 10);
        $this->assertTrue($page_of_posts[0]->post_text == "This is image post 9");
        $this->assertTrue($page_of_posts[9]->post_text == "This is image post 0");
    }

    /**
     * Test getTotalPhotoPagesAndPostsByPublicInstances
     */
    public function testGetTotalPhotoPagesAndPostsByPublicInstances() {
        $pdao = new PostMySQLDAO();
        $totals = $pdao->getTotalPhotoPagesAndPostsByPublicInstances(15);

        $this->assertTrue($totals["total_posts"] == 40);
        $this->assertTrue($totals["total_pages"] == 3);
    }

    /**
     * Test getLinkPostsByPublicInstances, page 1
     */
    public function testGetPageOneOfLinkPublicPosts() {
        $pdao = new PostMySQLDAO();

        $page_of_posts = $pdao->getLinkPostsByPublicInstances(1, 15);
        $this->assertTrue(sizeof($page_of_posts) == 15);
        $this->assertTrue($page_of_posts[0]->post_text == "This is link post 39");
        $this->assertTrue($page_of_posts[14]->post_text == "This is link post 25");
    }
    /**
     * Test getLinkPostsByPublicInstances, page 2
     */
    public function testGetPageTwoOfLinkPublicPosts() {
        $pdao = new PostMySQLDAO();

        $page_of_posts = $pdao->getLinkPostsByPublicInstances(2, 15);
        $this->assertTrue(sizeof($page_of_posts) == 15);
        $this->assertTrue($page_of_posts[0]->post_text == "This is link post 24");
        $this->assertTrue($page_of_posts[14]->post_text == "This is link post 10");
    }

    /**
     * Test getLinkPostsByPublicInstances, page 3
     */

    public function testGetPageThreeOfLinkPublicPosts() {
        $pdao = new PostMySQLDAO();
        $page_of_posts = $pdao->getLinkPostsByPublicInstances(3, 15);

        $this->assertEqual(sizeof($page_of_posts), 11, "Should be ".sizeof($page_of_posts));
        $this->assertEqual($page_of_posts[0]->post_text, "This is link post 9", $page_of_posts[0]->post_text .
        " == This is link post 9");
        //$this->assertEqual($page_of_posts[9]->post_text, "This is link post 0", $page_of_posts[9]->post_text . " == This is link post 0");
    }

    /**
     * Test getTotalLinkPagesAndPostsByPublicInstances
     */

    public function testGetTotalLinkPagesAndPostsByPublicInstances() {
        $pdao = new PostMySQLDAO();
        $totals = $pdao->getTotalLinkPagesAndPostsByPublicInstances(15);

        $this->assertTrue($totals["total_posts"] == 41);
        $this->assertTrue($totals["total_pages"] == 3);
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
        $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache) VALUES (550, 19, 'linkbaiter', 
        'Link Baiter', 'avatar.jpg', 'This is parent post 1', 'web', '2006-03-01 00:01:00', 1, 0);";
        PDODAO::$PDO->exec($q);
        $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache) VALUES (551, 19, 'linkbaiter', 
        'Link Baiter', 'avatar.jpg', 'This is parent post 2', 'web', '2006-03-01 00:01:00', 0, 0);";
        PDODAO::$PDO->exec($q);

        //Add a post with the parent post 550
        $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache, in_reply_to_post_id) 
        VALUES (552, 19, 'linkbaiter', 'Link Baiter', 'avatar.jpg', 'This is a reply with the wrong parent', 
        'web', '2006-03-01 00:01:00', 0, 0, 550);";
        PDODAO::$PDO->exec($q);

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

    /**
     * Test getMostRetweetedPostsByPublicInstancesInLastWeek
     */
    public function testGetMostRetweetedPostsByPublicInstancesInLastWeek() {
        //Add posts with retweets by user3, who is on the public timeline with retweet counts in the last 9 days
        $counter = 0;
        $id = 200;
        while ($counter < 40) {
            $id += $counter;
            $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
            post_text, source, pub_date, reply_count_cache, retweet_count_cache) VALUES ($id, 23, 'user3', 
            'User 3', 'avatar.jpg', 'This is post with $counter retweets', 'web', DATE_SUB(NOW(), 
            INTERVAL ".$counter." DAY), 0, ".$counter.");";
            //echo $q;
            PDODAO::$PDO->exec($q);
            $counter++;
        }
        $pdao = new PostMySQLDAO();
        $page1ofposts = $pdao->getMostRetweetedPostsByPublicInstancesInLastWeek(1, 5);
        $this->assertEqual(sizeof($page1ofposts), 5);
        $this->assertEqual($page1ofposts[0]->retweet_count_cache, 7);
        $this->assertEqual($page1ofposts[1]->retweet_count_cache, 6);

        $page2ofposts = $pdao->getMostRetweetedPostsByPublicInstancesInLastWeek(2, 5);
        $this->assertEqual(sizeof($page2ofposts), 2);
        $this->assertEqual($page2ofposts[0]->retweet_count_cache, 2);
        $this->assertEqual($page2ofposts[1]->retweet_count_cache, 1);

        $totals = $pdao->getTotalPagesAndPostsByPublicInstances(5, 7);
        $this->assertEqual($totals["total_posts"], 7);
        $this->assertEqual($totals["total_pages"], 2);
    }
    /**
     * Test getMostRepliedToPostsByPublicInstancesInLastWeek
     */
    public function testGetMostRepliedToPostsByPublicInstancesInLastWeek() {
        //Add posts with retweets by user3, who is on the public timeline with retweet counts in the last 9 days
        $counter = 0;
        $id = 200;
        while ($counter < 40) {
            $id += $counter;
            $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
            post_text, source, pub_date, reply_count_cache, retweet_count_cache) VALUES ($id, 23, 'user3', 'User 3', 
            'avatar.jpg', 'This is post with $counter replies', 'web', DATE_SUB(NOW(), INTERVAL ".$counter." DAY), 
            ".$counter.", 0 );";
            //echo $q;
            PDODAO::$PDO->exec($q);
            $counter++;
        }
        $pdao = new PostMySQLDAO();
        $page1ofposts = $pdao->getMostRepliedToPostsByPublicInstancesInLastWeek(1, 5);
        $this->assertEqual(sizeof($page1ofposts), 5);
        $this->assertEqual($page1ofposts[0]->reply_count_cache, 7);
        $this->assertEqual($page1ofposts[1]->reply_count_cache, 6);

        $page2ofposts = $pdao->getMostRepliedToPostsByPublicInstancesInLastWeek(2, 5);
        $this->assertEqual(sizeof($page2ofposts), 2);
        $this->assertEqual($page2ofposts[0]->reply_count_cache, 2);
        $this->assertEqual($page2ofposts[1]->reply_count_cache, 1);

        $totals = $pdao->getTotalPagesAndPostsByPublicInstances(5, 7);
        $this->assertEqual($totals["total_posts"], 7);
        $this->assertEqual($totals["total_pages"], 2);
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
                'post_id'=>(144+$counter),
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
                'post_id'=>(144+$counter),
                'author_user_id'=>23,
                'author_username'=>'user3',
                'pub_date'=>'-'.$counter.'d',
                'retweet_count_cache'=>$counter));
            $counter++;
        }
        $pdao = new PostMySQLDAO();
        $posts = $pdao->getMostRetweetedPostsInLastWeek('user3', 'twitter', 5);
        $this->assertEqual(sizeof($posts), 5);
        $this->assertEqual($posts[0]->retweet_count_cache, 7);
        $this->assertEqual($posts[1]->retweet_count_cache, 6);

        $posts = $pdao->getMostRetweetedPostsInLastWeek('user2', 'twitter', 5);
        $this->assertEqual(sizeof($posts), 0);
    }

    /**
     * Test getPostsToGeoencode
     */
    public function testGetPoststoGeoencode() {
        $dao = new PostMySQLDAO();
        $posts = $dao->getPoststoGeoencode();
        $this->assertEqual(count($posts), 10);
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
}