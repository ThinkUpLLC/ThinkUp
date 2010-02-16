<?php 
require_once (dirname(__FILE__).'/simpletest/autorun.php');


require_once (dirname(__FILE__).'/config.tests.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$TEST_CLASS_PATH);

require_once ("config.inc.php");
require_once ("mock.TwitterOAuth.php");
require_once ("class.TwitterAPIAccessorOAuth.php");

class TestOfFriendsPaging extends UnitTestCase {
    function TestOfFriendsPaging() {
        $this->UnitTestCase('Friends Paging test');
    }
    
    function setUp() {
        global $THINKTANK_CFG;
    }
    
    function tearDown() {
    
    }
    
    function testMakingAPICall() {
        $to = new TwitterOAuth('', '', '', '');
        $result = $to->oAuthRequest('https://twitter.com/statuses/friends.xml', array(), 'GET');
        $this->assertWantedPattern('/A or B/', $result);
		
		$api = new CrawlerTwitterAPIAccessorOAuth('111', '222', $THINKTANK_CFG['oauth_consumer_key'], $THINKTANK_CFG['oauth_consumer_secret'], $i, $THINKTANK_CFG['archive_limit']);
		$users = $api->parseXML($result);
		$next_cursor = $api->getNextCursor();
		//echo 'Next cursor is ' . $next_cursor;
		$this->assertTrue($next_cursor=='1305768756249357127');		

        
    }	
	/*
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
        $recent_tweets = array(new Tweet(array('status_id'=>9021481076, 'tweet_text'=>'guilty pleasure: dropping the "my wife" bomb on unsuspecting straight people, mid-conversation')), new Tweet(array('status_id'=>9020176425, 'tweet_text'=>"a Google fangirl's take: no doubt Buzz's privacy issues are seriously problematic, but at least they're iterating quickly and openly.")), new Tweet(array('status_id'=>9031523906, 'tweet_text'=>"one of the most fun photo shoots &amp; interviews I've ever done http://bit.ly/9ldYNw thx @voiceofsandiego, @dagnysalas, & @samuelhodgson")), new Tweet(array('status_id'=>8925077246, 'tweet_text'=>"how to do (almost) everything in Google Buzz, including turn it off http://bit.ly/bfQTQH")));
        
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
    */
}
?>
