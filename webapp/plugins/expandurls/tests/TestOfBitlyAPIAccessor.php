<?php
/**
 *
 * ThinkUp/webapp/plugins/expandedurls/tests/TestOfBitlyAPIAccessor.php
 *
 * Copyright (c) 2009-2011 Gina Trapani
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
 * @author Randi Miller <techrandy[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Gina Trapani
 */
require_once 'tests/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';

require_once THINKUP_ROOT_PATH.'webapp/plugins/expandurls/tests/classes/mock.BitlyAPIAccessor.php';

class TestOfBitlyAPIAccessor extends UnitTestCase {

    public function testGetBitlyLinkDataBitlyAPINonResponsive() {
        $logger = Logger::getInstance();
        $bitly_api = new BitlyAPIAccessor('dummykey', 'dummylogin', $logger);

        $link_details = $bitly_api->getBitlyLinkData('http://bit.ly/20');
        //this file does not exist so response will be false
        $this->assertEqual($link_details["expanded_url"], '');
        $this->assertEqual($link_details["error"], 'No response from Bitly API');
        $logger->close();
    }

    public function testGetBitlyLinkDataNoBitlyAPIKey() {
        $logger = Logger::getInstance();
        $bitly_api = new BitlyAPIAccessor('', 'dummylogin', $logger);

        $link_details = $bitly_api->getBitlyLinkData('http://bit.ly/dPOYo3');
        $this->assertEqual($link_details["expanded_url"], '');
        $this->assertEqual($link_details["error"], '');
        //logger will have logged that the API key was not set

        $logger->close();
    }
    
    public function testGetBitlyLinkDataNoBitlyAPILogin() {
        $logger = Logger::getInstance();
        $bitly_api = new BitlyAPIAccessor('dummykey', '', $logger);

        $link_details = $bitly_api->getBitlyLinkData('http://bit.ly/dPOYo3');
        $this->assertEqual($link_details["expanded_url"], '');
        $this->assertEqual($link_details["error"], '');
        //logger will have logged that the API key was not set

        $logger->close();
    }

    public function testGetBitlyLinkDataBitlyAPIReturnsError() {
        $logger = Logger::getInstance();
        $bitly_api = new BitlyAPIAccessor('dummykey', 'dummylogin', $logger);

        $link_details = $bitly_api->getBitlyLinkData('http://bit.ly/40/');
        $this->assertEqual($link_details["expanded_url"], '');
        $this->assertEqual($link_details["error"], 'No response from Bitly API');

        $logger->close();
    }

    public function testGetBitlyLinkDataSuccess() {
        $logger = Logger::getInstance();
        $bitly_api = new BitlyAPIAccessor('dummykey', 'dummylogin', $logger);

        $this->assertTrue(isset($bitly_api));

        $link_details = $bitly_api->getBitlyLinkData('http://bit.ly/dPOYo3');
        $this->assertEqual($link_details["expanded_url"],
        'http://static.ak.fbcdn.net/rsrc.php/zw/r/ZEKh4ZZQY74.png');

        $logger->close();
    }
}
