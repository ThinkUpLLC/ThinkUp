<?php
if ( !isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS ) {
    require_once '../../../../tests/config.tests.inc.php';
}

require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'webapp/common/class.MySQLDAO.php';
require_once $SOURCE_ROOT_PATH.'webapp/common/class.Instance.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/tests/classes/mock.TwitterOAuth.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterAPIAccessorOAuth.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterOAuthThinkTank.php';
require_once $SOURCE_ROOT_PATH.'webapp/config.inc.php';

class TestOfTwitterAPIAccessorOAuth extends UnitTestCase {
    function TestOfTwitterAPIAccessorOAuth() {
        $this->UnitTestCase('Test of TwitterAPIAccessorOAuth');
    }

    function setUp() {
    }

    function tearDown() {

    }

    function testFriendsList() {
        global $THINKTANK_CFG;

        $to = new TwitterOAuth('', '', '', '');
        $result = $to->oAuthRequest('https://twitter.com/statuses/friends.xml', array(), 'GET');
        $this->assertWantedPattern('/A or B/', $result);

        $r = array();
        $r["id"] = 0;
        $r['network_username'] = 'user';
        $r['network_user_id'] = 0;
        $r['network_viewer_id'] = 0;
        $r['last_status_id'] = 0;
        $r['last_page_fetched_replies'] = 0;
        $r['last_page_fetched_tweets'] = 0;
        $r['total_posts_in_system'] = 0;
        $r['total_replies_in_system'] = 0;
        $r['total_follows_in_system'] = 0;
        $r['total_users_in_system'] = 0;
        $r['is_archive_loaded_replies'] = 0;
        $r['is_archive_loaded_follows'] = 0;
        $r['crawler_last_run'] = '1/1/2007';
        $r['earliest_reply_in_system'] = 0;
        $r['api_calls_to_leave_unmade_per_minute'] = 5;
        $r['avg_replies_per_day'] = 0;
        $r['is_public'] = 1;
        $r['is_active'] = 1;
        $r['network'] = 'twitter';

        $i = new Instance($r);

        $api = new CrawlerTwitterAPIAccessorOAuth('111', '222', $THINKTANK_CFG['oauth_consumer_key'], $THINKTANK_CFG['oauth_consumer_secret'], $i, $THINKTANK_CFG['archive_limit']);
        $users = $api->parseXML($result);
        $next_cursor = $api->getNextCursor();
        //echo 'Next cursor is ' . $next_cursor;
        $this->assertTrue($next_cursor == '1305768756249357127');
    }

    function testIDsList() {
        global $THINKTANK_CFG;

        $to = new TwitterOAuth('', '', '', '');
        $result = $to->oAuthRequest('https://twitter.com/followers/ids.xml', array(), 'GET');

        $r = array();
        $r["id"] = 0;
        $r['network_username'] = 'user';
        $r['network_user_id'] = 0;
        $r['network_viewer_id'] = 0;
        $r['last_status_id'] = 0;
        $r['last_page_fetched_replies'] = 0;
        $r['last_page_fetched_tweets'] = 0;
        $r['total_posts_in_system'] = 0;
        $r['total_replies_in_system'] = 0;
        $r['total_follows_in_system'] = 0;
        $r['total_users_in_system'] = 0;
        $r['is_archive_loaded_replies'] = 0;
        $r['is_archive_loaded_follows'] = 0;
        $r['crawler_last_run'] = '1/1/2007';
        $r['earliest_reply_in_system'] = 0;
        $r['api_calls_to_leave_unmade_per_minute'] = 5;
        $r['avg_replies_per_day'] = 0;
        $r['is_public'] = 1;
        $r['is_active'] = 1;
        $r['network'] = 'twitter';


        $i = new Instance($r);

        $api = new CrawlerTwitterAPIAccessorOAuth('111', '222', $THINKTANK_CFG['oauth_consumer_key'], $THINKTANK_CFG['oauth_consumer_secret'], $i, $THINKTANK_CFG['archive_limit']);
        $users = $api->parseXML($result);
        $next_cursor = $api->getNextCursor();
        //echo 'Next cursor is ' . $next_cursor;
        $this->assertTrue($next_cursor == '1326272872342936860');
    }

    function testSearchResults() {
        global $THINKTANK_CFG;

        $r = array();
        $r["id"] = 0;
        $r['network_username'] = 'user';
        $r['network_user_id'] = 0;
        $r['network_viewer_id'] = 0;
        $r['last_status_id'] = 0;
        $r['last_page_fetched_replies'] = 0;
        $r['last_page_fetched_tweets'] = 0;
        $r['total_posts_in_system'] = 0;
        $r['total_replies_in_system'] = 0;
        $r['total_follows_in_system'] = 0;
        $r['total_users_in_system'] = 0;
        $r['is_archive_loaded_replies'] = 0;
        $r['is_archive_loaded_follows'] = 0;
        $r['crawler_last_run'] = '1/1/2007';
        $r['earliest_reply_in_system'] = 0;
        $r['api_calls_to_leave_unmade_per_minute'] = 5;
        $r['avg_replies_per_day'] = 0;
        $r['is_public'] = 1;
        $r['is_active'] = 1;
        $r['network'] = 'twitter';



        $i = new Instance($r);

        $to = new TwitterOAuth('', '', '', '');
        $twitter_data = $to->http('http://search.twitter.com/search.json?q=%40whitehouse&result_type=recent');

        $api = new CrawlerTwitterAPIAccessorOAuth('111', '222', $THINKTANK_CFG['oauth_consumer_key'], $THINKTANK_CFG['oauth_consumer_secret'], $i, $THINKTANK_CFG['archive_limit']);

        $results = $api->parseJSON($twitter_data);

        //print_r($results);

        $this->assertEqual($results[0]['post_id'], 11837318124);

    }

}
?>
