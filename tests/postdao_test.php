<?php 
require_once (dirname(__FILE__).'/simpletest/autorun.php');
require_once (dirname(__FILE__).'/simpletest/web_tester.php');

require_once (dirname(__FILE__).'/config.tests.inc.php');

ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once ("classes/class.ThinkTankTestCase.php");
require_once ("common/class.Post.php");
require_once ("common/class.Link.php");

class TestOfPostDAO extends ThinkTankUnitTestCase {
    function TestOfPostDAO() {
        $this->UnitTestCase('PostDAO class test');
    }
    
    function setUp() {
        parent::setUp();
        
        //TODO: Insert test data into post table
        //$this->db->exec($q);
        //Add instance_owner
        $q = "INSERT INTO tt_owner_instances (owner_id, instance_id) VALUES (1, 1)";
        $this->db->exec($q);
        
        //Insert test data into test table
        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar, last_updated) VALUES (13, 'ev', 'Ev Williams', 'avatar.jpg', '1/1/2005');";
        $this->db->exec($q);
        
        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar, is_protected, follower_count) VALUES (18, 'shutterbug', 'Shutter Bug', 'avatar.jpg', 0, 10);";
        $this->db->exec($q);
        
        $q = "INSERT INTO tt_users (user_id, user_name, full_name, avatar, is_protected, follower_count) VALUES (19, 'linkbaiter', 'Link Baiter', 'avatar.jpg', 0, 10);";
        $this->db->exec($q);
        
        //Make public
        $q = "INSERT INTO tt_instances (network_user_id, network_username, is_public) VALUES (13, 'ev', 1);";
        $this->db->exec($q);
        
        $q = "INSERT INTO tt_instances (network_user_id, network_username, is_public) VALUES (18, 'shutterbug', 1);";
        $this->db->exec($q);
        
        $q = "INSERT INTO tt_instances (network_user_id, network_username, is_public) VALUES (19, 'linkbaiter', 1);";
        $this->db->exec($q);
        
        //Add a bunch of posts
        $counter = 0;
        while ($counter < 40) {
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
            $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar, post_text, source, pub_date, mention_count_cache, retweet_count_cache) VALUES ($counter, 13, 'ev', 'Ev Williams', 'avatar.jpg', 'This is post $counter', 'web', '2006-01-01 00:$pseudo_minute:00', ".rand(0, 4).", 5);";
            $this->db->exec($q);
            $counter++;
        }
        
        //Add some photo posts
        $counter = 0;
        while ($counter < 40) {
            $post_id = $counter + 40;
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
            $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar, post_text, source, pub_date, mention_count_cache, retweet_count_cache) VALUES ($post_id, 18, 'shutterbug', 'Shutter Bug', 'avatar.jpg', 'This is image post $counter', 'web', '2006-01-02 00:$pseudo_minute:00', 0, 0);";
            $this->db->exec($q);
            
            $q = "INSERT INTO tt_links (url, expanded_url, title, clicks, post_id, is_image) VALUES ('http://example.com/".$counter."', 'http://example.com/".$counter.".jpg', '', 0, $post_id, 1);";
            $this->db->exec($q);
            
            $counter++;
        }
        
        //Add some link posts
        $counter = 0;
        while ($counter < 40) {
            $post_id = $counter + 80;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);
            $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar, post_text, source, pub_date, mention_count_cache, retweet_count_cache) VALUES ($post_id, 19, 'linkbaiter', 'Link Baiter', 'avatar.jpg', 'This is link post $counter', 'web', '2006-03-01 00:$pseudo_minute:00', 0, 0);";
            $this->db->exec($q);
            
            $q = "INSERT INTO tt_links (url, expanded_url, title, clicks, post_id, is_image) VALUES ('http://example.com/".$counter."', 'http://example.com/".$counter.".html', 'Link $counter', 0, $post_id, 0);";
            $this->db->exec($q);
            
            $counter++;
        }
        
    }
    
    function tearDown() {
        parent::tearDown();
    }
    
    function testIsRetweet() {
    
        $startwithcolon = "RT @ginatrapani: how to do (almost) everything in Google Buzz, including turn it off http://bit.ly/bfQTQH";
        $nostartnocolon = "Agreed: RT @ginatrapani guilty pleasure: dropping the &quot;my wife&quot; bomb on unsuspecting straight people, mid-conversation";
        $startwithcolonspaces = "RT @ginatrapani    how to do (almost) everything in Google Buzz, including turn it off http://bit.ly/bfQTQH";
        $startwithcoloncutoff = "RT @ginatrapani: one of the most fun photo shoots &amp; interviews I've ever done http://bit.ly/9ldYNw thx.";
        $lowwercase = "rt @ginatrapani: one of the most fun photo shoots &amp; interviews I've ever done http://bit.ly/9ldYNw thx.";
        
        $o = 'ginatrapani';
        $this->assertTrue(RetweetDetector::isRetweet($startwithcolon, 'ginatrapani'));
        $this->assertTrue(RetweetDetector::isRetweet($nostartnocolon, 'ginatrapani'));
        $this->assertTrue(RetweetDetector::isRetweet($startwithcolonspaces, 'ginatrapani'));
        $this->assertTrue(RetweetDetector::isRetweet($startwithcoloncutoff, 'ginatrapani'));
        $this->assertTrue(RetweetDetector::isRetweet($lowwercase, 'ginatrapani'));
    }
    
    function testDetectRetweets() {

    
        $recent_tweets = array( new Post(array('id'=>1, 'author_user_id'=>10, 'author_username'=>'no one', 'author_avatar'=>'yo.jpg', 'source'=>'TweetDeck', 'pub_date'=>'', 'adj_pub_date'=>'', 'in_reply_to_user_id'=>'', 'in_reply_to_post_id'=>'', 'mention_count_cache'=>'', 'in_retweet_of_post_id'=>'', 'retweet_count_cache'=>'', 'post_id'=>9021481076, 'post_text'=>'guilty pleasure: dropping the "my wife" bomb on unsuspecting straight people, mid-conversation', 'network'=>'twitter')), new Post(array('id'=>1, 'author_user_id'=>10, 'author_username'=>'no one', 'author_avatar'=>'yo.jpg', 'source'=>'TweetDeck', 'pub_date'=>'', 'adj_pub_date'=>'', 'in_reply_to_user_id'=>'', 'in_reply_to_post_id'=>'', 'mention_count_cache'=>'', 'in_retweet_of_post_id'=>'', 'retweet_count_cache'=>'', 'post_id'=>9020176425, 'post_text'=>"a Google fangirl's take: no doubt Buzz's privacy issues are seriously problematic, but at least they're iterating quickly and openly.", 'network'=>'twitter')), new Post(array('id'=>1, 'author_user_id'=>10, 'author_username'=>'no one', 'author_avatar'=>'yo.jpg', 'source'=>'TweetDeck', 'pub_date'=>'', 'adj_pub_date'=>'', 'in_reply_to_user_id'=>'', 'in_reply_to_post_id'=>'', 'mention_count_cache'=>'', 'in_retweet_of_post_id'=>'', 'retweet_count_cache'=>'', 'post_id'=>9031523906, 'post_text'=>"one of the most fun photo shoots &amp; interviews I've ever done http://bit.ly/9ldYNw thx @voiceofsandiego, @dagnysalas, & @samuelhodgson", 'network'=>'twitter')), new Post(array('id'=>1, 'author_user_id'=>10, 'author_username'=>'no one', 'author_avatar'=>'yo.jpg', 'source'=>'TweetDeck', 'pub_date'=>'', 'adj_pub_date'=>'', 'in_reply_to_user_id'=>'', 'in_reply_to_post_id'=>'', 'mention_count_cache'=>'', 'in_retweet_of_post_id'=>'', 'retweet_count_cache'=>'', 'post_id'=>8925077246, 'post_text'=>"how to do (almost) everything in Google Buzz, including turn it off http://bit.ly/bfQTQH", 'network'=>'twitter')));
        
        $startwithcolon = "RT @ginatrapani: how to do (almost) everything in Google Buzz, including turn it off http://bit.ly/bfQTQH";
        $nostartnocolon = "Agreed: RT @ginatrapani guilty pleasure: dropping the &quot;my wife&quot; bomb on unsuspecting straight people, mid-conversation";
        $startwithcolonspaces = "RT @ginatrapani    how to do (almost) everything in Google Buzz, including turn it off http://bit.ly/bfQTQH";
        $startwithcoloncutoff = "RT @ginatrapani: one of the most fun photo shoots &amp; interviews I've ever done http://bit.ly/9ldYNw thx.";
        $lowwercase = "rt @ginatrapani: one of the most fun photo shoots &amp; interviews I've ever done http://bit.ly/9ldYNw thx.";
        $nonexistent = "rt @ginatrapani this is a non-existent tweet";

        
        $this->assertTrue(RetweetDetector::detectOriginalTweet($nostartnocolon, $recent_tweets) == 9021481076);
        $this->assertTrue(RetweetDetector::detectOriginalTweet($startwithcolonspaces, $recent_tweets) == 8925077246);
        $this->assertTrue(RetweetDetector::detectOriginalTweet($startwithcoloncutoff, $recent_tweets) == 9031523906);
        $this->assertTrue(RetweetDetector::detectOriginalTweet($startwithcolon, $recent_tweets) == 8925077246);
        $this->assertTrue(RetweetDetector::detectOriginalTweet($nonexistent, $recent_tweets) === false);
    }
    
    function testGetPageOneOfPublicPosts() {
        //Instantiate DAO
        $pdao = new PostDAO($this->db, $this->logger);
        
        //Get page 1 containing 15 public posts
        $page_of_posts = $pdao->getPostsByPublicInstances(1, 15);
        
        //Assert DAO returns 15 posts
        $this->assertTrue(sizeof($page_of_posts) == 15);
        
        //Assert first post 1 contains the right text
        $this->assertTrue($page_of_posts[0]->post_text == "This is post 39");
        
        //Asert last post 15 contains the right text
        $this->assertTrue($page_of_posts[14]->post_text == "This is post 25");
    }
    
    function testGetPageTwoOfPublicPosts() {
        $pdao = new PostDAO($this->db, $this->logger);
        
        $page_of_posts = $pdao->getPostsByPublicInstances(2, 15);
        
        $this->assertTrue(sizeof($page_of_posts) == 15);
        $this->assertTrue($page_of_posts[0]->post_text == "This is post 24");
        $this->assertTrue($page_of_posts[14]->post_text == "This is post 10");
    }
    
    function testGetPageThreeOfPublicPosts() {
        $pdao = new PostDAO($this->db, $this->logger);
        
        $page_of_posts = $pdao->getPostsByPublicInstances(3, 15);
        
        //Assert DAO returns 10 posts
        $this->assertTrue(sizeof($page_of_posts) == 10);
        
        $this->assertTrue($page_of_posts[0]->post_text == "This is post 9");
        $this->assertTrue($page_of_posts[9]->post_text == "This is post 0");
    }
    
    function testGetTotalPagesAndPostsByPublicInstances() {
        $pdao = new PostDAO($this->db, $this->logger);
        
        $totals = $pdao->getTotalPagesAndPostsByPublicInstances(15);
        
        $this->assertTrue($totals["total_posts"] == 40);
        $this->assertTrue($totals["total_pages"] == 3);
    }
    
    //Start Public Photo Tests
    function testGetPageOneOfPhotoPublicPosts() {
        $pdao = new PostDAO($this->db, $this->logger);
        
        $page_of_posts = $pdao->getPhotoPostsByPublicInstances(1, 15);
        
        $this->assertTrue(sizeof($page_of_posts) == 15);
        
        $this->assertTrue($page_of_posts[0]->post_text == "This is image post 39");
        
        $this->assertTrue($page_of_posts[14]->post_text == "This is image post 25");
        
    }
    
    function testGetPageTwoOfPhotoPublicPosts() {
        $pdao = new PostDAO($this->db, $this->logger);
        
        $page_of_posts = $pdao->getPhotoPostsByPublicInstances(2, 15);
        
        $this->assertTrue(sizeof($page_of_posts) == 15);
        
        $this->assertTrue($page_of_posts[0]->post_text == "This is image post 24");
        
        $this->assertTrue($page_of_posts[14]->post_text == "This is image post 10");
        
    }
    
    function testGetPageThreeOfPhotoPublicPosts() {
        $pdao = new PostDAO($this->db, $this->logger);
        
        $page_of_posts = $pdao->getPhotoPostsByPublicInstances(3, 15);
        
        $this->assertTrue(sizeof($page_of_posts) == 10);
        
        $this->assertTrue($page_of_posts[0]->post_text == "This is image post 9");
        
        $this->assertTrue($page_of_posts[9]->post_text == "This is image post 0");
        
    }
    
    function testGetTotalPhotoPagesAndPostsByPublicInstances() {
        $pdao = new PostDAO($this->db, $this->logger);
        $totals = $pdao->getTotalPhotoPagesAndPostsByPublicInstances(15);
        
        $this->assertTrue($totals["total_posts"] == 40);
        $this->assertTrue($totals["total_pages"] == 3);
    }
    
    //Start Public Link Tests
    function testGetPageOneOfLinkPublicPosts() {
        $pdao = new PostDAO($this->db, $this->logger);
        
        $page_of_posts = $pdao->getLinkPostsByPublicInstances(1, 15);
        
        $this->assertTrue(sizeof($page_of_posts) == 15);
        
        $this->assertTrue($page_of_posts[0]->post_text == "This is link post 39");
        
        $this->assertTrue($page_of_posts[14]->post_text == "This is link post 25");
        
    }
    
    function testGetPageTwoOfLinkPublicPosts() {
        $pdao = new PostDAO($this->db, $this->logger);
        
        $page_of_posts = $pdao->getLinkPostsByPublicInstances(2, 15);
        
        $this->assertTrue(sizeof($page_of_posts) == 15);
        
        $this->assertTrue($page_of_posts[0]->post_text == "This is link post 24");
        
        $this->assertTrue($page_of_posts[14]->post_text == "This is link post 10");
        
    }
    
    function testGetPageThreeOfLinkPublicPosts() {
        $pdao = new PostDAO($this->db, $this->logger);
        
        $page_of_posts = $pdao->getLinkPostsByPublicInstances(3, 15);
        
        $this->assertTrue(sizeof($page_of_posts) == 10);
        
        $this->assertTrue($page_of_posts[0]->post_text == "This is link post 9");
        
        $this->assertTrue($page_of_posts[9]->post_text == "This is link post 0");
        
    }
    
    function testGetTotalLinkPagesAndPostsByPublicInstances() {
        $pdao = new PostDAO($this->db, $this->logger);
        $totals = $pdao->getTotalLinkPagesAndPostsByPublicInstances(15);
        
        $this->assertTrue($totals["total_posts"] == 40);
        $this->assertTrue($totals["total_pages"] == 3);
    }
    
    function testGetTotalPostsByUser() {
        $pdao = new PostDAO($this->db, $this->logger);
        $total_posts = $pdao->getTotalPostsByUser(13);
        $this->assertTrue($total_posts == 40);
    }
    
    function testAssignParent() {
        //Add two "parent" posts
        $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar, post_text, source, pub_date, mention_count_cache, retweet_count_cache) VALUES (550, 19, 'linkbaiter', 'Link Baiter', 'avatar.jpg', 'This is parent post 1', 'web', '2006-03-01 00:01:00', 1, 0);";
        $this->db->exec($q);
        $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar, post_text, source, pub_date, mention_count_cache, retweet_count_cache) VALUES (551, 19, 'linkbaiter', 'Link Baiter', 'avatar.jpg', 'This is parent post 2', 'web', '2006-03-01 00:01:00', 0, 0);";
        $this->db->exec($q);
        
        //Add a post with the parent post 550
        $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar, post_text, source, pub_date, mention_count_cache, retweet_count_cache, in_reply_to_post_id) VALUES (552, 19, 'linkbaiter', 'Link Baiter', 'avatar.jpg', 'This is a reply with the wrong parent', 'web', '2006-03-01 00:01:00', 0, 0, 550);";
        $this->db->exec($q);
        
        $pdao = new PostDAO($this->db, $this->logger);
        
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
        $this->assertEqual($old_parent->mention_count_cache, 0);
        
		//Assert new parent post has one more reply total
        $new_parent = $pdao->getPost(551);
        $this->assertEqual($new_parent->mention_count_cache, 1);
        
    }
}
?>
