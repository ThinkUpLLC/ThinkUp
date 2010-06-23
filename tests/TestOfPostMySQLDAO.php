<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'tests/fixtures/class.FixtureBuilder.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Post.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/interface.PostDAO.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.PostMySQLDAO.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Link.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.User.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Profiler.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Session.php';

/**
 * Test of PostMySQL DAO implementation
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfPostMySQLDAO extends ThinkTankUnitTestCase {
    /**
     *
     * @var PostMySQLDAO
     */
    protected $dao;
    /**
     * Constructor
     */
    function __construct() {
        $this->UnitTestCase('PostMySQLDAO class test');
    }

    function setUp() {
        parent::setUp();
        $config = Config::getInstance();
        $this->prefix = $config->getValue('table_prefix');

        $this->DAO = new PostMySQLDAO();
        $q = "INSERT INTO tt_owner_instances (owner_id, instance_id) VALUES (1, 1)";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar, last_updated)
        VALUES (13, 'ev', 'Ev Williams', 'avatar.jpg', '1/1/2005');";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar, is_protected, follower_count)
        VALUES (18, 'shutterbug', 'Shutter Bug', 'avatar.jpg', 0, 10);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar, is_protected, follower_count)
        VALUES (19, 'linkbaiter', 'Link Baiter', 'avatar.jpg', 0, 70);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar, is_protected, follower_count)
        VALUES (20, 'user1', 'User 1', 'avatar.jpg', 0, 90);";
        PDODAO::$PDO->exec($q);

        //protected user
        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar, is_protected, follower_count)
        VALUES (21, 'user2', 'User 2', 'avatar.jpg', 1, 80);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar, is_protected, follower_count)
        VALUES (22, 'quoter', 'Quotables', 'avatar.jpg', 0, 80);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar, is_protected, follower_count)
        VALUES (23, 'user3', 'User 3', 'avatar.jpg', 0, 100);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar, is_protected, follower_count)
        VALUES (24, 'notonpublictimeline', 'Not on Public Timeline', 'avatar.jpg', 1, 100);";
        PDODAO::$PDO->exec($q);

        //Make public
        $q = "INSERT INTO tt_instances (network_user_id, network_username, is_public) VALUES (13, 'ev', 1);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_instances (network_user_id, network_username, is_public) VALUES (18, 'shutterbug', 1);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_instances (network_user_id, network_username, is_public) VALUES (19, 'linkbaiter', 1);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_instances (network_user_id, network_username, is_public) VALUES (23, 'user3', 1);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_instances (network_user_id, network_username, is_public)
        VALUES (24, 'notonpublictimeline', 0);";
        PDODAO::$PDO->exec($q);

        //Add straight text posts
        $counter = 0;
        while ($counter < 40) {
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
            $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
            post_text, source, pub_date, reply_count_cache, retweet_count_cache) VALUES 
            ($counter, 13, 'ev', 'Ev Williams', 'avatar.jpg', 
            'This is post $counter', 'web', '2006-01-01 00:$pseudo_minute:00', ".rand(0, 4).", 5);";
            PDODAO::$PDO->exec($q);
            $counter++;
        }

        //Add photo posts from Flickr
        $counter = 0;
        while ($counter < 40) {
            $post_id = $counter + 40;
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
            $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
            post_text, source, pub_date, reply_count_cache, retweet_count_cache) 
            VALUES ($post_id, 18, 'shutterbug', 'Shutter Bug', 'avatar.jpg', 'This is image post $counter', 'Flickr', 
            '2006-01-02 00:$pseudo_minute:00', 0, 0);";
            PDODAO::$PDO->exec($q);

            $q = "INSERT INTO tt_links (url, expanded_url, title, clicks, post_id, is_image)
            VALUES ('http://example.com/".$counter."', 'http://example.com/".$counter.".jpg', '', 0, $post_id, 1);";
            PDODAO::$PDO->exec($q);

            $counter++;
        }

        //Add link posts
        $counter = 0;
        while ($counter < 40) {
            $post_id = $counter + 80;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);
            $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
            post_text, source, pub_date, reply_count_cache, retweet_count_cache) 
            VALUES ($post_id, 19, 'linkbaiter', 'Link Baiter', 'avatar.jpg', 
            'This is link post $counter', 'web', '2006-03-01 00:$pseudo_minute:00', 0, 0);";
            PDODAO::$PDO->exec($q);

            $q = "INSERT INTO tt_links (url, expanded_url, title, clicks, post_id, is_image)
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
                $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
                post_text, source, pub_date, reply_count_cache, retweet_count_cache) 
                VALUES ($post_id, 20, 'user1', 'User 1', 'avatar.jpg', 
                'Hey @ev and @jack thanks for founding Twitter  post $counter', 'web', 
                '2006-03-01 00:$pseudo_minute:00', 0, 0);";
            } else {
                $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname,
                author_avatar, post_text, source, pub_date, reply_count_cache, retweet_count_cache) 
                VALUES ($post_id, 21, 'user2', 'User 2', 'avatar.jpg', 
                'Hey @ev and @jack should fix Twitter - post $counter', 'web', 
                '2006-03-01 00:$pseudo_minute:00', 0, 0);";
            }
            PDODAO::$PDO->exec($q);

            $counter++;
        }


        //Add replies to specific post
        $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache, in_reply_to_post_id) 
        VALUES (131, 20, 'user1', 'User 1', 'avatar.jpg', '@shutterbug Nice shot!', 'web', 
        '2006-03-01 00:00:00', 0, 0, 41);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache, in_reply_to_post_id) 
        VALUES (132, 21, 'user2', 'User 2', 'avatar.jpg', '@shutterbug Nice shot!', 'web', 
        '2006-03-01 00:00:00', 0, 0, 41);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache, in_reply_to_post_id) 
        VALUES (133, 19, 'linkbaiter', 'Link Baiter', 'avatar.jpg', 
        '@shutterbug This is a link post reply http://example.com/', 'web', '2006-03-01 00:00:00', 0, 0, 41);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_links (url, expanded_url, title, clicks, post_id, is_image)
        VALUES ('http://example.com/', 'http://example.com/expanded-link.html', 'Link 1', 0, 133, 0);";
        PDODAO::$PDO->exec($q);

        //Add retweets of a specific post
        //original post
        $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache) 
        VALUES (134, 22, 'quoter', 'Quoter of Quotables', 'avatar.jpg', 
        'Be liberal in what you accept and conservative in what you send', 'web', '2006-03-01 00:00:00', 0, 0);";
        PDODAO::$PDO->exec($q);
        //retweet 1
        $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache, in_retweet_of_post_id) 
        VALUES (135, 20, 'user1', 'User 1', 'avatar.jpg', 
        'RT @quoter Be liberal in what you accept and conservative in what you send', 'web', '2006-03-01 00:00:00', 0, 0, 134);";
        PDODAO::$PDO->exec($q);
        //retweet 2
        $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache, in_retweet_of_post_id) 
        VALUES (136, 21, 'user2', 'User 2', 'avatar.jpg', 
        'RT @quoter Be liberal in what you accept and conservative in what you send', 'web', 
        '2006-03-01 00:00:00', 0, 0, 134);";
        PDODAO::$PDO->exec($q);
        //retweet 3
        $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache, in_retweet_of_post_id) 
        VALUES (137, 19, 'linkbaiter', 'Link Baiter', 'avatar.jpg', 
        'RT @quoter Be liberal in what you accept and conservative in what you send', 'web', 
        '2006-03-01 00:00:00', 0, 0, 134);";
        PDODAO::$PDO->exec($q);

        //Add reply back
        $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache, in_reply_to_user_id, 
        in_reply_to_post_id) VALUES (138, 18, 'shutterbug', 'Shutterbug', 'avatar.jpg', 
        '@user2 Thanks!', 'web', '2006-03-01 00:00:00', 0, 0, 21, 132);";
        PDODAO::$PDO->exec($q);

        //Add user exchange
        $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache, in_reply_to_user_id) 
        VALUES (139, 20, 'user1', 'User 1', 'avatar.jpg', '@ev When will Twitter have a business model?', 
        'web', '2006-03-01 00:00:00', 0, 0, 13);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache, 
        in_reply_to_user_id, in_reply_to_post_id) VALUES (140, 13, 'ev', 'Ev Williams', 'avatar.jpg', 
        '@user1 Soon....', 'web', '2006-03-01 00:00:00', 0, 0, 20, 139);";
        PDODAO::$PDO->exec($q);

        //Add posts replying to post not in the system
        $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache, in_reply_to_user_id, 
        in_reply_to_post_id) VALUES (141, 23, 'user3', 'User 3', 'avatar.jpg', 
        '@user4 I\'m replying to a post not in the TT db', 'web', '2006-03-01 00:00:00', 0, 0, 20, 150);";
        PDODAO::$PDO->exec($q);

        $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache, in_reply_to_user_id, in_reply_to_post_id) 
        VALUES (142, 23, 'user3', 'User 3', 'avatar.jpg', 
        '@user4 I\'m replying to another post not in the TT db', 'web', '2006-03-01 00:00:00', 0, 0, 20, 151);";
        PDODAO::$PDO->exec($q);

        //Add post by instance not on public timeline
        $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date) VALUES (143, 24, 'notonpublictimeline', 'Not on public timeline', 'avatar.jpg', 
        'This post should not be on the public timeline', 'web', '2006-03-01 00:00:00');";
        PDODAO::$PDO->exec($q);

    }

    function tearDown() {
        parent::tearDown();
    }

    /**
     * Test constructor
     */
    function testConstructor() {
        $dao = new PostMySQLDAO();
        $this->assertTrue(isset($dao));
    }

    /**
     * Test getOrphanReplies
     */
    function testGetOrphanReplies() {
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
    function testGetLikelyOrphansForParent() {
        $dao = new PostMySQLDAO();
        $posts = $dao->getLikelyOrphansForParent('2006-03-01', 13, 'ev', 10);
        $this->assertEqual(sizeof($posts), 9);
        $this->assertEqual($posts[0]->post_text, "Hey @ev and @jack should fix Twitter - post 1");
    }
     
    /**
     * Test getStrayRepliedToPosts
     */
    function testGetStrayRepliedToPosts() {
        $dao = new PostMySQLDAO();
        $posts = $dao->getStrayRepliedToPosts(23);
        $this->assertEqual(sizeof($posts), 2);
        $this->assertEqual($posts[0]["in_reply_to_post_id"], 150);
        $this->assertEqual($posts[1]["in_reply_to_post_id"], 151);
    }
     
    /**
     * Test isPostByPublicInstance
     */
    function testIsPostByPublicInstance() {
        $dao = new PostMySQLDAO();
        //post by ev (public instance)
        $this->assertTrue($dao->isPostByPublicInstance(140));
        //post by notapublicinstance
        $this->assertTrue(!$dao->isPostByPublicInstance(143));
    }
     
    /**
     * Test getMostRepliedToPosts
     */
    function testGetMostRepliedToPosts() {
        $dao = new PostMySQLDAO();
        $posts = $dao->getMostRepliedToPosts(13, 10);
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
    function testGetMostRetweetedPosts() {
        $dao = new PostMySQLDAO();
        $posts = $dao->getMostRetweetedPosts(13, 10);
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
    function testGetAllReplies() {
        $dao = new PostMySQLDAO();
        $replies = $dao->getAllReplies(13, 10);
        $this->assertTrue(sizeof($replies), 10);
        $this->assertEqual(sizeof($replies), 1);
        $this->assertEqual($replies[0]->post_text, "@ev When will Twitter have a business model?");

        $replies = $dao->getAllReplies(18, 10);
        $this->assertEqual(sizeof($replies), 0);
    }

    /**
     * Test getAllMentions
     */
    function testGetAllMentions() {
        $dao = new PostMySQLDAO();
        $mentions = $dao->getAllMentions("ev", 10);
        $this->assertTrue(sizeof($mentions), 10);
        $this->assertEqual($mentions[0]->post_text, "Hey @ev and @jack should fix Twitter - post 9");

        $mentions = $dao->getAllMentions("jack", 10);
        $this->assertTrue(sizeof($mentions), 10);
        $this->assertEqual($mentions[0]->post_text, "Hey @ev and @jack should fix Twitter - post 9");
    }

    /**
     * Test getStatusSources
     */
    function testGetStatusSources() {
        $dao = new PostMySQLDAO();
        $sources = $dao->getStatusSources(18);
        $this->assertEqual(sizeof($sources), 2);
        $this->assertEqual($sources[0]["source"], "Flickr");
        $this->assertEqual($sources[0]["total"], 40);
        $this->assertEqual($sources[1]["source"], "web");
        $this->assertEqual($sources[1]["total"], 1);

        //non-existent author
        $sources = $dao->getStatusSources(51);
        $this->assertEqual(sizeof($sources), 0);
    }

    /**
     * Test getAllPostsByUser
     */
    function testGetAllPostsByUser() {
        $dao = new PostMySQLDAO();
        $total = $dao->getTotalPostsByUser(18);
        $this->assertEqual($total, 41);

        //non-existent author
        $total = $dao->getTotalPostsByUser(51);
        $this->assertEqual($total, 0);
    }

    /**
     * Test getAllPosts
     */
    function testGetAllPostsByUsername() {
        $dao = new PostMySQLDAO();
        $posts = $dao->getAllPostsByUsername('shutterbug');
        $this->assertEqual(sizeof($posts), 41);

        //non-existent author
        $posts = $dao->getAllPostsByUsername('idontexist');
        $this->assertEqual(sizeof($posts), 0);
    }

    /**
     * Test getAllPosts
     */
    function testGetAllPosts() {
        $dao = new PostMySQLDAO();
        //more than count
        $posts = $dao->getAllPosts(18, 10);
        $this->assertEqual(sizeof($posts), 10);

        //less than count
        $posts = $dao->getAllPosts(18, 50);
        $this->assertEqual(sizeof($posts), 41);

        //less than count, no replies --there is 1 reply, so 41-1=40
        $posts = $dao->getAllPosts(18, 50, false);
        $this->assertEqual(sizeof($posts), 40);

        //non-existent author
        $posts = $dao->getAllPosts(30, 10);
        $this->assertEqual(sizeof($posts), 0);
    }

    /**
     * Test getPost on a post that exists
     */
    function testGetPostExists() {
        $dao = new PostMySQLDAO();
        $post = $dao->getPost(10);
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
    function testGetPostDoesNotExist(){
        $dao = new PostMySQLDAO();
        $post = $dao->getPost(100000001);
        $this->assertTrue(!isset($post));
    }

    /**
     * Test getStandaloneReplies
     */
    function testGetStandaloneReplies() {
        $dao = new PostMySQLDAO();
        $posts = $dao->getStandaloneReplies('jack', 15);

        $this->assertEqual(sizeof($posts), 10);
        $this->assertEqual($posts[0]->post_text, 'Hey @ev and @jack should fix Twitter - post 9',
        "Standalone mention");
        $this->assertEqual($posts[0]->author->username, 'user2', "Post author");

        $posts = $dao->getStandaloneReplies('ev', 15);

        $this->assertEqual(sizeof($posts), 11);
        $this->assertEqual($posts[0]->post_text, 'Hey @ev and @jack should fix Twitter - post 9',
        "Standalone mention");
        $this->assertEqual($posts[0]->author->username, 'user2', "Post author");
    }

    /**
     * Test getRepliesToPost
     */
    function testGetRepliesToPost() {
        $dao = new PostMySQLDAO();
        $posts = $dao->getRepliesToPost(41);
        $this->assertEqual(sizeof($posts), 3);
        $this->assertEqual($posts[0]->post_text, '@shutterbug Nice shot!', "post reply");
        $this->assertEqual($posts[0]->author->username, 'user1', "Post author");

        $this->assertEqual($posts[2]->post_text, '@shutterbug This is a link post reply http://example.com/',
        "post reply");
        $this->assertEqual($posts[2]->post_id, 133, "post ID");
        $this->assertEqual($posts[2]->author->username, 'linkbaiter', "Post author");
        $this->assertEqual($posts[2]->link->expanded_url, 'http://example.com/expanded-link.html', "Expanded URL");
    }

    /**
     * Test getPublicRepliesToPost
     */
    function testGetPublicRepliesToPost() {
        $dao = new PostMySQLDAO();
        $posts = $dao->getPublicRepliesToPost(41);
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
    function testGetRetweetsOfPost() {
        $dao = new PostMySQLDAO();
        $posts = $dao->getRetweetsOfPost(134);
        $this->assertEqual(sizeof($posts), 3);
        $this->assertEqual($posts[0]->post_text,
        'RT @quoter Be liberal in what you accept and conservative in what you send', "post reply");
        $this->assertEqual($posts[0]->author->username, 'user1', "Post author");
    }

    /**
     * Test getPostReachViaRetweets
     */
    function testGetPostReachViaRetweets() {
        $dao = new PostMySQLDAO();
        $total = $dao->getPostReachViaRetweets(134);
        $this->assertEqual($total, (90+80+70));

        $total = $dao->getPostReachViaRetweets(130);
        $this->assertEqual($total, 0);
    }

    /**
     * Test function getPostsAuthorHasRepliedTo
     */
    function testGetPostsAuthorHasRepliedTo(){
        $dao = new PostMySQLDAO();
        $posts_replied_to = $dao->getPostsAuthorHasRepliedTo(18, 10);
        $this->assertEqual($posts_replied_to[0]["questioner_username"], "user2");
        $this->assertEqual($posts_replied_to[0]["question"], "@shutterbug Nice shot!");
        $this->assertEqual($posts_replied_to[0]["answerer_username"], "shutterbug");
        $this->assertEqual($posts_replied_to[0]["answer"], "@user2 Thanks!");

        $posts_replied_to = $dao->getPostsAuthorHasRepliedTo(13, 10);
        $this->assertEqual($posts_replied_to[0]["questioner_username"], "user1");
        $this->assertEqual($posts_replied_to[0]["question"], "@ev When will Twitter have a business model?");
        $this->assertEqual($posts_replied_to[0]["answerer_username"], "ev");
        $this->assertEqual($posts_replied_to[0]["answer"], "@user1 Soon....");
    }

    /**
     * Test getExchangesBetweenUsers
     */
    function testGetExchangesBetweenUsers() {
        $dao = new PostMySQLDAO();
        $posts_replied_to = $dao->getExchangesBetweenUsers(18, 21);
        $this->assertEqual(sizeof($posts_replied_to), 2);
        $this->assertEqual($posts_replied_to[0]["questioner_username"], "shutterbug");
        $this->assertEqual($posts_replied_to[0]["question"], "This is image post 1");
        $this->assertEqual($posts_replied_to[0]["answerer_username"], "user2");
        $this->assertEqual($posts_replied_to[0]["answer"], "@shutterbug Nice shot!");

        $this->assertEqual($posts_replied_to[1]["questioner_username"], "user2");
        $this->assertEqual($posts_replied_to[1]["question"], "@shutterbug Nice shot!");
        $this->assertEqual($posts_replied_to[1]["answerer_username"], "shutterbug");
        $this->assertEqual($posts_replied_to[1]["answer"], "@user2 Thanks!");

        $posts_replied_to = $dao->getPostsAuthorHasRepliedTo(13, 20);
        $this->assertEqual(sizeof($posts_replied_to), 1);
        $this->assertEqual($posts_replied_to[0]["questioner_username"], "user1");
        $this->assertEqual($posts_replied_to[0]["question"], "@ev When will Twitter have a business model?");
        $this->assertEqual($posts_replied_to[0]["answerer_username"], "ev");
        $this->assertEqual($posts_replied_to[0]["answer"], "@user1 Soon....");
    }

    /**
     * Test isPostInDB
     */
    function testIsPostInDB() {
        $dao = new PostMySQLDAO();
        $this->assertTrue($dao->isPostInDB(129));

        $this->assertTrue(!$dao->isPostInDB(250));
    }

    /**
     * Test isReplyInDB
     */
    function testIsReplyInDB() {
        $dao = new PostMySQLDAO();
        $this->assertTrue($dao->isReplyInDB(138));

        $this->assertTrue(!$dao->isReplyInDB(250));
    }

    /**
     * Test addPost
     */
    function testAddPost() {
        $dao = new PostMySQLDAO();
        $vals = array();

        $vals['post_id']=250;
        $vals['author_username']='quoter';
        $vals['author_fullname']="Quoter of Quotables";
        $vals['author_avatar']='avatar.jpg';

        //test add post without all the req'd fields set
        $this->assertEqual($dao->addPost($vals), 0, "Post not inserted, not all values set");

        $vals['author_user_id']= 22;
        $vals['post_text']="Go confidently in the direction of your dreams! Live the life you've imagined.";
        $vals['location']="New Delhi";
        $vals['place']="Dwarka, New Delhi";
        $vals['geo']="10.0000 20.0000";
        $vals['pub_date']='3/1/2010';
        $vals['source']='web';
        $vals['network']= 'twitter';
        $vals['in_reply_to_post_id']= '';

        //test add straight post that doesn't exist
        $this->assertEqual($dao->addPost($vals), 1, "Post inserted");
        $post = $dao->getPost(250);
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

        //test add post that does exist
        $vals['post_id']=129;
        $this->assertEqual($dao->addPost($vals), 0, "Post exists, nothing inserted");

        //test add reply, check cache count
        $vals['post_id']=251;
        $vals['in_reply_to_post_id']= 129;
        $this->assertEqual($dao->addPost($vals), 1, "Reply inserted");
        $post = $dao->getPost(129);
        $this->assertEqual($post->reply_count_cache, 1, "reply count got updated");

        //test add retweet, check cache count
        $vals['post_id']=252;
        $vals['in_reply_to_post_id']= '';
        $vals['in_retweet_of_post_id']= 128;
        $this->assertEqual($dao->addPost($vals), 1, "Retweet inserted");
        $post = $dao->getPost(128);
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

        $dao = new PostMySQLDAO();
        $dao->addPost($vals);
        $stmt = PostMySQLDAO::$PDO->query( "select * from " . $this->prefix . 'posts where post_id=1000' );
        $data = $stmt->fetch();
        $this->assertEqual($data['is_retweet_by_friend'], 1);
    }

    /**
     * Test get pages 1 of posts by public instances
     */
    function testGetPageOneOfPublicPosts() {
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
    function testGetPageTwoOfPublicPosts() {
        $pdao = new PostMySQLDAO();

        $page_of_posts = $pdao->getPostsByPublicInstances(2, 15);

        $this->assertTrue(sizeof($page_of_posts) == 15);
        $this->assertTrue($page_of_posts[0]->post_text == "This is post 24");
        $this->assertTrue($page_of_posts[14]->post_text == "This is post 10");
    }

    /**
     * Test get pages 3 of posts by public instances
     */
    function testGetPageThreeOfPublicPosts() {
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
    function testGetTotalPagesAndPostsByPublicInstances() {
        $pdao = new PostMySQLDAO();

        $totals = $pdao->getTotalPagesAndPostsByPublicInstances(15);

        $this->assertTrue($totals["total_posts"] == 40);
        $this->assertTrue($totals["total_pages"] == 3);
    }

    /**
     * Test get a page of photos posts by public instances
     */
    function testGetPageOneOfPhotoPublicPosts() {
        $pdao = new PostMySQLDAO();

        $page_of_posts = $pdao->getPhotoPostsByPublicInstances(1, 15);
        $this->assertTrue(sizeof($page_of_posts) == 15);
        $this->assertTrue($page_of_posts[0]->post_text == "This is image post 39");
        $this->assertTrue($page_of_posts[14]->post_text == "This is image post 25");
    }
    /**
     * Test get a page of photos posts by public instances
     */
    function testGetPageTwoOfPhotoPublicPosts() {
        $pdao = new PostMySQLDAO();

        $page_of_posts = $pdao->getPhotoPostsByPublicInstances(2, 15);
        $this->assertTrue(sizeof($page_of_posts) == 15);
        $this->assertTrue($page_of_posts[0]->post_text == "This is image post 24");
        $this->assertTrue($page_of_posts[14]->post_text == "This is image post 10");
    }

    /**
     * Test get a page of photo posts by public instances
     */
    function testGetPageThreeOfPhotoPublicPosts() {
        $pdao = new PostMySQLDAO();

        $page_of_posts = $pdao->getPhotoPostsByPublicInstances(3, 15);
        $this->assertTrue(sizeof($page_of_posts) == 10);
        $this->assertTrue($page_of_posts[0]->post_text == "This is image post 9");
        $this->assertTrue($page_of_posts[9]->post_text == "This is image post 0");
    }

    /**
     * Test getTotalPhotoPagesAndPostsByPublicInstances
     */
    function testGetTotalPhotoPagesAndPostsByPublicInstances() {
        $pdao = new PostMySQLDAO();
        $totals = $pdao->getTotalPhotoPagesAndPostsByPublicInstances(15);

        $this->assertTrue($totals["total_posts"] == 40);
        $this->assertTrue($totals["total_pages"] == 3);
    }

    /**
     * Test getLinkPostsByPublicInstances, page 1
     */
    function testGetPageOneOfLinkPublicPosts() {
        $pdao = new PostMySQLDAO();

        $page_of_posts = $pdao->getLinkPostsByPublicInstances(1, 15);
        $this->assertTrue(sizeof($page_of_posts) == 15);
        $this->assertTrue($page_of_posts[0]->post_text == "This is link post 39");
        $this->assertTrue($page_of_posts[14]->post_text == "This is link post 25");
    }
    /**
     * Test getLinkPostsByPublicInstances, page 2
     */
    function testGetPageTwoOfLinkPublicPosts() {
        $pdao = new PostMySQLDAO();

        $page_of_posts = $pdao->getLinkPostsByPublicInstances(2, 15);
        $this->assertTrue(sizeof($page_of_posts) == 15);
        $this->assertTrue($page_of_posts[0]->post_text == "This is link post 24");
        $this->assertTrue($page_of_posts[14]->post_text == "This is link post 10");
    }

    /**
     * Test getLinkPostsByPublicInstances, page 3
     */

    function testGetPageThreeOfLinkPublicPosts() {
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

    function testGetTotalLinkPagesAndPostsByPublicInstances() {
        $pdao = new PostMySQLDAO();
        $totals = $pdao->getTotalLinkPagesAndPostsByPublicInstances(15);

        $this->assertTrue($totals["total_posts"] == 41);
        $this->assertTrue($totals["total_pages"] == 3);
    }
    /**
     * Test getTotalPostsByUser
     */
    function testGetTotalPostsByUser() {
        $pdao = new PostMySQLDAO();
        $total_posts = $pdao->getTotalPostsByUser(13);

        $this->assertTrue($total_posts == 41);
    }

    /**
     * Test assignParent
     */
    function testAssignParent() {
        //Add two "parent" posts
        $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache) VALUES (550, 19, 'linkbaiter', 
        'Link Baiter', 'avatar.jpg', 'This is parent post 1', 'web', '2006-03-01 00:01:00', 1, 0);";
        PDODAO::$PDO->exec($q);
        $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache) VALUES (551, 19, 'linkbaiter', 
        'Link Baiter', 'avatar.jpg', 'This is parent post 2', 'web', '2006-03-01 00:01:00', 0, 0);";
        PDODAO::$PDO->exec($q);

        //Add a post with the parent post 550
        $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
        post_text, source, pub_date, reply_count_cache, retweet_count_cache, in_reply_to_post_id) 
        VALUES (552, 19, 'linkbaiter', 'Link Baiter', 'avatar.jpg', 'This is a reply with the wrong parent', 
        'web', '2006-03-01 00:01:00', 0, 0, 550);";
        PDODAO::$PDO->exec($q);

        $pdao = new PostMySQLDAO();

        $post = $pdao->getPost(552);
        //Assert parent post is 550
        $this->assertEqual($post->in_reply_to_post_id, 550);

        //Change parent post to 551
        $pdao->assignParent(551, 552);
        $child_post = $pdao->getPost(552);
        //Assert parent post is now 551
        $this->assertEqual($child_post->in_reply_to_post_id, 551);

        //Assert old parent post has one fewer reply total
        $old_parent = $pdao->getPost(550);
        $this->assertEqual($old_parent->reply_count_cache, 0);

        //Assert new parent post has one more reply total
        $new_parent = $pdao->getPost(551);
        $this->assertEqual($new_parent->reply_count_cache, 1);
    }

    /**
     * Test getMostRetweetedPostsByPublicInstancesInLastWeek
     */
    function testGetMostRetweetedPostsByPublicInstancesInLastWeek() {
        //Add posts with retweets by user3, who is on the public timeline with retweet counts in the last 9 days
        $counter = 0;
        $id = 200;
        while ($counter < 40) {
            $id += $counter;
            $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
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
    function testGetMostRepliedToPostsByPublicInstancesInLastWeek() {
        //Add posts with retweets by user3, who is on the public timeline with retweet counts in the last 9 days
        $counter = 0;
        $id = 200;
        while ($counter < 40) {
            $id += $counter;
            $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
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
}