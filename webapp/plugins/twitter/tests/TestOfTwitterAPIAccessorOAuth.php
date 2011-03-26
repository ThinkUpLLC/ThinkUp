<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/tests/TestOfTwitterAPIAccessorOAuth.php
 *
 * Copyright (c) 2009-2011 Gina Trapani, Mark Wilkie
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Mark Wilkie <mark[at]bitterpill[dot]org>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Gina Trapani, Mark Wilkie
 */
require_once 'tests/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/tests/classes/mock.TwitterOAuth.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterAPIAccessorOAuth.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.CrawlerTwitterAPIAccessorOAuth.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterOAuthThinkUp.php';

class TestOfTwitterAPIAccessorOAuth extends ThinkUpBasicUnitTestCase {
    public function __construct() {
        $this->UnitTestCase('Test of TwitterAPIAccessorOAuth');
    }

    public function testFriendsList() {
        global $THINKUP_CFG;

        $to = new TwitterOAuth('', '', '', '');
        $result = $to->oAuthRequest('https://twitter.com/statuses/friends.xml', 'GET', array());
        $this->assertWantedPattern('/A or B/', $result);

        $api = new CrawlerTwitterAPIAccessorOAuth('111', '222', 1234, 1234, 5, 3200, 5, 350);
        $users = $api->parseXML($result);
        $next_cursor = $api->getNextCursor();
        //echo 'Next cursor is ' . $next_cursor;
        $this->assertTrue($next_cursor == '1305768756249357127');
    }

    public function testIDsList() {
        global $THINKUP_CFG;

        $to = new TwitterOAuth('', '', '', '');
        $result = $to->oAuthRequest('https://twitter.com/followers/ids.xml', 'GET', array());

        $api = new CrawlerTwitterAPIAccessorOAuth('111', '222', 1234, 1234, 5, 3200, 5, 350);
        $users = $api->parseXML($result);
        $next_cursor = $api->getNextCursor();
        //echo 'Next cursor is ' . $next_cursor;
        $this->assertTrue($next_cursor == '1326272872342936860');
    }

    public function testSearchResults() {
        global $THINKUP_CFG;

        $to = new TwitterOAuth('', '', '', '');
        $twitter_data = $to->http('http://search.twitter.com/search.json?q=%40whitehouse&result_type=recent');

        $api = new CrawlerTwitterAPIAccessorOAuth('111', '222', 1234, 1234, 5, 3200, 5, 350);

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
        $api = new TwitterAPIAccessorOAuth('111', '222', 'test-oauth_consumer_key', 'test-oauth_consumer_secret', 5,
        350);

        $this->assertFalse($api->createParserFromString(utf8_encode($data)));
    }
}