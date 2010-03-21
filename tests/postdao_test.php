<?php 
require_once (dirname(__FILE__).'/simpletest/autorun.php');
require_once (dirname(__FILE__).'/simpletest/web_tester.php');

require_once (dirname(__FILE__).'/config.tests.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once ("classes/class.ThinkTankTestCase.php");
require_once ("class.Post.php");
require_once ("class.Link.php");

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
        
        //Make public
        $q = "INSERT INTO tt_instances (network_user_id, network_username, is_public) VALUES (13, 'ev', 1);";
        $this->db->exec($q);
        
        //Add a bunch of posts
        $counter = 0;
        while ($counter < 40) {
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
            $q = "INSERT INTO tt_posts (post_id, author_user_id, author_username, author_fullname, author_avatar, post_text, source, pub_date, mention_count_cache, retweet_count_cache) VALUES ($counter, 13, 'ev', 'Ev Williams', 'avatar.jpg', 'This is post $counter', 'web', '2006-01-01 00:$pseudo_minute:00', ".rand(0, 4).", 5);";
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

    
        $recent_tweets = array( new Post(array('id'=>1, 'author_user_id'=>10, 'author_username'=>'no one', 'author_avatar'=>'yo.jpg', 'source'=>'TweetDeck', 'pub_date'=>'', 'adj_pub_date'=>'', 'in_reply_to_user_id'=>'', 'in_reply_to_post_id'=>'', 'mention_count_cache'=>'', 'in_retweet_of_post_id'=>'', 'retweet_count_cache'=>'', 'post_id'=>9021481076, 'post_text'=>'guilty pleasure: dropping the "my wife" bomb on unsuspecting straight people, mid-conversation')), new Post(array('id'=>1, 'author_user_id'=>10, 'author_username'=>'no one', 'author_avatar'=>'yo.jpg', 'source'=>'TweetDeck', 'pub_date'=>'', 'adj_pub_date'=>'', 'in_reply_to_user_id'=>'', 'in_reply_to_post_id'=>'', 'mention_count_cache'=>'', 'in_retweet_of_post_id'=>'', 'retweet_count_cache'=>'', 'post_id'=>9020176425, 'post_text'=>"a Google fangirl's take: no doubt Buzz's privacy issues are seriously problematic, but at least they're iterating quickly and openly.")), new Post(array('id'=>1, 'author_user_id'=>10, 'author_username'=>'no one', 'author_avatar'=>'yo.jpg', 'source'=>'TweetDeck', 'pub_date'=>'', 'adj_pub_date'=>'', 'in_reply_to_user_id'=>'', 'in_reply_to_post_id'=>'', 'mention_count_cache'=>'', 'in_retweet_of_post_id'=>'', 'retweet_count_cache'=>'', 'post_id'=>9031523906, 'post_text'=>"one of the most fun photo shoots &amp; interviews I've ever done http://bit.ly/9ldYNw thx @voiceofsandiego, @dagnysalas, & @samuelhodgson")), new Post(array('id'=>1, 'author_user_id'=>10, 'author_username'=>'no one', 'author_avatar'=>'yo.jpg', 'source'=>'TweetDeck', 'pub_date'=>'', 'adj_pub_date'=>'', 'in_reply_to_user_id'=>'', 'in_reply_to_post_id'=>'', 'mention_count_cache'=>'', 'in_retweet_of_post_id'=>'', 'retweet_count_cache'=>'', 'post_id'=>8925077246, 'post_text'=>"how to do (almost) everything in Google Buzz, including turn it off http://bit.ly/bfQTQH")));
        
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
<<<<<<< HEAD:tests/postdao_test.php

        //Asert last post 15 contains the right text
=======
        
        //Asert last post 14 contains the right text
>>>>>>> upstream/master:tests/postdao_test.php
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
}
?>
