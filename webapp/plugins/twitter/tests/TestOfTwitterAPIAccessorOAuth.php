<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/tests/TestOfTwitterAPIAccessorOAuth.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Dwi Widiastuti, Mark Wilkie
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
*/
/**
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 * @author Mark Wilkie <mark[at]bitterpill[dot]org>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Dwi Widiastuti, Mark Wilkie
*/
if ( !isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS ) {
    require_once '../../../../tests/init.tests.php';
}
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';

require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/tests/classes/mock.TwitterOAuth.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterAPIAccessorOAuth.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.CrawlerTwitterAPIAccessorOAuth.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterOAuthThinkUp.php';

class TestOfTwitterAPIAccessorOAuth extends ThinkUpBasicUnitTestCase {
    public function __construct() {
        $this->UnitTestCase('Test of TwitterAPIAccessorOAuth');
    }

    private function getTestInstance() {
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

        return new Instance($r);
    }

    public function testFriendsList() {
        global $THINKUP_CFG;

        $to = new TwitterOAuth('', '', '', '');
        $result = $to->oAuthRequest('https://twitter.com/statuses/friends.xml', 'GET', array());
        $this->assertWantedPattern('/A or B/', $result);

        $api = new CrawlerTwitterAPIAccessorOAuth('111', '222', 1234,
        1234, $this->getTestInstance(), 3200);
        $users = $api->parseXML($result);
        $next_cursor = $api->getNextCursor();
        //echo 'Next cursor is ' . $next_cursor;
        $this->assertTrue($next_cursor == '1305768756249357127');
    }

    public function testIDsList() {
        global $THINKUP_CFG;

        $to = new TwitterOAuth('', '', '', '');
        $result = $to->oAuthRequest('https://twitter.com/followers/ids.xml', 'GET', array());

        $api = new CrawlerTwitterAPIAccessorOAuth('111', '222', 1234,
        1234, $this->getTestInstance(), 3200);
        $users = $api->parseXML($result);
        $next_cursor = $api->getNextCursor();
        //echo 'Next cursor is ' . $next_cursor;
        $this->assertTrue($next_cursor == '1326272872342936860');
    }

    public function testSearchResults() {
        global $THINKUP_CFG;

        $to = new TwitterOAuth('', '', '', '');
        $twitter_data = $to->http('http://search.twitter.com/search.json?q=%40whitehouse&result_type=recent');

        $api = new CrawlerTwitterAPIAccessorOAuth('111', '222', 1234,
        1234, $this->getTestInstance(), 3200);

        $results = $api->parseJSON($twitter_data);

        //print_r($results);

        $this->assertEqual($results[0]['post_id'], 11837318124);
    }

    public function testCreateParserFromStringMalformedMarkup() {
        $data = <<<XML
        <?xml version='1.0'?> 
<document>
 <title>Forty What?</title>
 <from>Joe</from>
 <to>Jane</to>
 <body>
  I know that's the answer -- <but what's the question?
 </body>
</document>
XML;
        $api = new TwitterAPIAccessorOAuth('111', '222', 'test-oauth_consumer_key', 'test-oauth_consumer_secret');

        $this->assertFalse($api->createParserFromString(utf8_encode($data)));
    }
}