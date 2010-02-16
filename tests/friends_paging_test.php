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
        $this->assertTrue($next_cursor == '1305768756249357127');

        
    }
}
?>
