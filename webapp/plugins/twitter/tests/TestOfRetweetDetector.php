<?php
if ( !isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS ) {
    require_once '../../../../tests/config.tests.inc.php';
}

require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkUpBasicUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Post.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.PDODAO.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Config.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Logger.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/model/class.RetweetDetector.php';
require_once $SOURCE_ROOT_PATH.'webapp/config.inc.php';

class TestOfRetweetDetector extends ThinkUpBasicUnitTestCase {
    var $logger;

    function __construct() {
        $this->UnitTestCase('RetweetDetector class test');
    }

    function setUp() {
        $this->logger = Logger::getInstance();
    }

    function tearDown() {
        $this->logger->close();
    }

    function testIsRetweet() {
        $startwithcolon =
        "RT @ginatrapani: how to do (almost) everything in Google Buzz, including turn it off http://bit.ly/bfQTQH";
        $nostartnocolon =
        "Agreed: RT @ginatrapani guilty pleasure: dropping the &quot;my wife&quot; bomb on unsuspecting straight";
        $startwithcolonspaces =
        "RT @ginatrapani    how to do (almost) everything in Google Buzz, including turn it off http://bit.ly/bfQTQH";
        $startwithcoloncutoff =
        "RT @ginatrapani: one of the most fun photo shoots &amp; interviews I've ever done http://bit.ly/9ldYNw thx.";
        $lowwercase =
        "rt @ginatrapani: one of the most fun photo shoots &amp; interviews I've ever done http://bit.ly/9ldYNw thx.";

        $o = 'ginatrapani';
        $this->assertTrue(RetweetDetector::isRetweet($startwithcolon, 'ginatrapani'));
        $this->assertTrue(RetweetDetector::isRetweet($nostartnocolon, 'ginatrapani'));
        $this->assertTrue(RetweetDetector::isRetweet($startwithcolonspaces, 'ginatrapani'));
        $this->assertTrue(RetweetDetector::isRetweet($startwithcoloncutoff, 'ginatrapani'));
        $this->assertTrue(RetweetDetector::isRetweet($lowwercase, 'ginatrapani'));
    }

    function testDetectRetweets() {
        $recent_tweets = array(
        new Post(array('id'=>1, 'author_user_id'=>10, 'author_username'=>'no one', 'author_fullname'=>"No One",
        'author_avatar'=>'yo.jpg', 'source'=>'TweetDeck', 'pub_date'=>'', 'adj_pub_date'=>'', 'in_reply_to_user_id'=>'', 
        'in_reply_to_post_id'=>'', 'reply_count_cache'=>'', 'in_retweet_of_post_id'=>'', 'retweet_count_cache'=>'', 
        'post_id'=>9021481076, 
        'post_text'=>'guilty pleasure: dropping the "my wife" bomb on unsuspecting straight people, mid-conversation', 
        'network'=>'twitter', 'geo'=>'', 'place'=>'', 'location'=>'', 'is_geo_encoded'=>0, 
        'is_reply_by_friend'=>0, 'is_retweet_by_friend'=>0, 'reply_retweet_distance'=>0)),
        new Post(array('id'=>1, 'author_user_id'=>10, 'author_username'=>'no one', 'author_fullname'=>"No One",
        'author_avatar'=>'yo.jpg', 'source'=>'TweetDeck', 'pub_date'=>'', 'adj_pub_date'=>'', 'in_reply_to_user_id'=>'', 
        'in_reply_to_post_id'=>'', 'reply_count_cache'=>'', 'in_retweet_of_post_id'=>'', 'retweet_count_cache'=>'', 
        'post_id'=>9020176425, 
        'post_text'=>"a Google fangirl's take: no doubt Buzz's privacy issues are seriously problematic, but at least they're iterating quickly and openly.", 
        'network'=>'twitter', 'geo'=>'', 'place'=>'', 'location'=>'', 'is_geo_encoded'=>0, 'is_reply_by_friend'=>0, 
        'is_retweet_by_friend'=>0, 'reply_retweet_distance'=>0)),
        new Post(array('id'=>1, 'author_user_id'=>10, 'author_username'=>'no one', 'author_fullname'=>"No One",
        'author_avatar'=>'yo.jpg', 'source'=>'TweetDeck', 'pub_date'=>'', 'adj_pub_date'=>'', 'in_reply_to_user_id'=>'', 
        'in_reply_to_post_id'=>'', 'reply_count_cache'=>'', 'in_retweet_of_post_id'=>'', 'retweet_count_cache'=>'', 
        'post_id'=>9031523906, 
        'post_text'=>"one of the most fun photo shoots &amp; interviews I've ever done http://bit.ly/9ldYNw thx @voiceofsandiego, @dagnysalas, & @samuelhodgson", 
        'network'=>'twitter', 'geo'=>'', 'place'=>'', 'location'=>'', 'is_geo_encoded'=>0, 'is_reply_by_friend'=>0, 
        'is_retweet_by_friend'=>0, 'reply_retweet_distance'=>0)),
        new Post(array('id'=>1, 'author_user_id'=>10, 'author_username'=>'no one', 'author_fullname'=>"No One",
        'author_avatar'=>'yo.jpg', 'source'=>'TweetDeck', 'pub_date'=>'', 'adj_pub_date'=>'', 'in_reply_to_user_id'=>'', 
        'in_reply_to_post_id'=>'', 'reply_count_cache'=>'', 'in_retweet_of_post_id'=>'', 'retweet_count_cache'=>'', 
        'post_id'=>8925077246, 
        'post_text'=>"how to do (almost) everything in Google Buzz, including turn it off http://bit.ly/bfQTQH", 
        'network'=>'twitter', 'geo'=>'', 'place'=>'', 'location'=>'', 'is_geo_encoded'=>0, 'is_reply_by_friend'=>0, 
        'is_retweet_by_friend'=>0, 'reply_retweet_distance'=>0)));

        $startwithcolon =
        "RT @ginatrapani: how to do (almost) everything in Google Buzz, including turn it off http://bit.ly/bfQTQH";
        $nostartnocolon =
        "Agreed: RT @ginatrapani guilty pleasure: dropping the &quot;my wife&quot; bomb on unsuspecting straight people, mid-conversation";
        $startwithcolonspaces =
        "RT @ginatrapani    how to do (almost) everything in Google Buzz, including turn it off http://bit.ly/bfQTQH";
        $startwithcoloncutoff =
        "RT @ginatrapani: one of the most fun photo shoots &amp; interviews I've ever done http://bit.ly/9ldYNw thx.";
        $lowwercase =
        "rt @ginatrapani: one of the most fun photo shoots &amp; interviews I've ever done http://bit.ly/9ldYNw thx.";
        $nonexistent = "rt @ginatrapani this is a non-existent tweet";

        $this->assertTrue(RetweetDetector::detectOriginalTweet($nostartnocolon, $recent_tweets) == 9021481076);
        $this->assertTrue(RetweetDetector::detectOriginalTweet($startwithcolonspaces, $recent_tweets) == 8925077246);
        $this->assertTrue(RetweetDetector::detectOriginalTweet($startwithcoloncutoff, $recent_tweets) == 9031523906);
        $this->assertTrue(RetweetDetector::detectOriginalTweet($startwithcolon, $recent_tweets) == 8925077246);
        $this->assertTrue(RetweetDetector::detectOriginalTweet($nonexistent, $recent_tweets) === false);
    }
}