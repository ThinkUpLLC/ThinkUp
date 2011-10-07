<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/tests/TestOfCrawlerTwitterAPIAccessorOAuth.php
 *
 * Copyright (c) 2011-2012 Mark Wilkie
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
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2012 Mark Wilkie
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'tests/classes/class.ThinkUpBasicUnitTestCase.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/tests/classes/mock.TwitterOAuth.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterAPIAccessorOAuth.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.CrawlerTwitterAPIAccessorOAuth.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterOAuthThinkUp.php';

class TestOfCrawlerTwitterAPIAccessorOAuth extends ThinkUpBasicUnitTestCase {
    var $logger;
     
    public function setUp() {
        $this->logger = Logger::getInstance();
    }

    public function tearDown() {
        $this->logger->close();
    }

    public function testAPILimit() {
        $api_calls_to_leave_unmade_per_minute = 100;
        $archive_limit = 100;
        $num_twitter_errors = 100;
        $max_api_calls_per_crawl = 100;
        $api = new CrawlerTwitterAPIAccessorOAuth('an_oauth_access_token','an_oauth_access_token_secret',
        'an_oauth_consumer_key', 'oauth_consumer_secret',
        $api_calls_to_leave_unmade_per_minute, $archive_limit, $num_twitter_errors, $max_api_calls_per_crawl);

        $api->init();
        // no caller limits;
        $i = 0;
        for ($i = 1; $i <= 10; $i++) {
            $api->apiRequest("/bad_url");
        }
        $this->assertEqual($i, 11);

        // with caller limits, 404 errors do count against limit
        $api->setCallerLimits(array( 'testAPILimit' => array('count' => 2, 'remaining' => 2) ) );
        $i = 0;
        try {
            for ($i = 0; $i <= 10; $i++) {
                $api->apiRequest("/bad_url");
            }
            $this->fail("should throw APICallLimitExceededException");
        } catch (APICallLimitExceededException $e) {
            $this->assertEqual($i,2);
        }

        // with caller limits, 403 errors do count against limit
        $api->setCallerLimits(array( 'testAPILimit' => array('count' => 2, 'remaining' => 2) ) );
        $i = 0;
        try {
            for ($i = 0; $i <= 10; $i++) {
                $api->apiRequest("403");
            }
            $this->fail("should throw APICallLimitExceededException");
        } catch (APICallLimitExceededException $e) {
            $this->assertEqual($i,2);
        }

        // all other errors shouldn't count againts caller limits
        foreach(array(405,500,502,504) as $status) {
            $api->setCallerLimits(array( 'testAPILimit' => array('count' => 1, 'remaining' => 1) ) );
            $i = 0;
            for ($i = 0; $i <= 1; $i++) {
                $api->apiRequest($status);
            }
            $this->assertEqual($i,2);
        }
    }
}
