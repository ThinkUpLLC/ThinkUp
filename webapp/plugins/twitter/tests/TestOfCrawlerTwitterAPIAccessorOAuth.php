<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/tests/TestOfCrawlerTwitterAPIAccessorOAuth.php
 *
 * Copyright (c) 2011-2013 Mark Wilkie
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkup.com).
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
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Mark Wilkie
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'tests/classes/class.ThinkUpBasicUnitTestCase.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/tests/classes/mock.TwitterOAuth.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterAPIAccessorOAuth.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.CrawlerTwitterAPIAccessorOAuth.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterOAuthThinkUp.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterAPIEndpoint.php';

class TestOfCrawlerTwitterAPIAccessorOAuth extends ThinkUpBasicUnitTestCase {
    public function setUp() {
    }

    public function tearDown() {
    }

    public function testConstructor() {
        $this->debug(__METHOD__);
        $api = new CrawlerTwitterAPIAccessorOAuth($oauth_token='111', $oauth_token_secret='222',
        $oauth_consumer_key=1234, $oauth_consumer_secret=4567, $archive_limit=3200, $num_twitter_errors=5);
        $api->to->setDataPathFolder('toctaaoauth/initendpointratelimits/');
        $this->assertNotNull($api);
        $this->assertIsA($api, 'CrawlerTwitterAPIAccessorOAuth');
    }

    public function testInitializeEndpointRateLimits() {
        $this->debug(__METHOD__);
        $api = new CrawlerTwitterAPIAccessorOAuth($oauth_token='111', $oauth_token_secret='222',
        $oauth_consumer_key=1234, $oauth_consumer_secret=4567, $archive_limit=3200, $num_twitter_errors=5);
        $api->to->setDataPathFolder('toctaaoauth/initendpointratelimits/');

        $api->initializeEndpointRateLimits();
        $this->assertEqual($api->endpoints["mentions"]->getRemaining(), 15);
        $this->assertEqual($api->endpoints["mentions"]->getReset(), 1361069069);
    }
}
