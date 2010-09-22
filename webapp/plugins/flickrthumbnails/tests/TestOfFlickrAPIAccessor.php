<?php
/**
 *
 * ThinkUp/webapp/plugins/flickrthumbnails/tests/TestOfFlickrAPIAccessor.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Dwi Widiastuti
 *
 * LICENSE:
 *
 * This file is part of ThinkUp.
 * 
 * ThinkUp is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * ThinkUp is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with ThinkUp.  If not, see <http://www.gnu.org/licenses/>.
 *
*/
/**
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Dwi Widiastuti
*/
if ( !isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS ) {
    require_once '../../../../tests/config.tests.inc.php';
}
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';

require_once THINKUP_ROOT_PATH.'webapp/plugins/flickrthumbnails/tests/classes/mock.FlickrAPIAccessor.php';

class TestOfFlickrAPIAccessor extends UnitTestCase {

    function TestOfFlickrAPIAccessor() {
        $this->UnitTestCase('FlickrAPIAccessor class test');
    }

    function setUp() {
    }

    function tearDown() {
    }

    function testGetFlickrPhotoSourceFlickrAPINonResponsive() {
        global $THINKUP_CFG;
        $logger = new Logger($THINKUP_CFG['log_location']);
        $fa = new FlickrAPIAccessor('dummykey', $logger);

        $eurl = $fa->getFlickrPhotoSource('http://flic.kr/p/6YS7AEasdfasdfasdfasdfasdf');
        //this file does not exist so response will be false
        $this->assertEqual($eurl["expanded_url"], '');
        $this->assertEqual($eurl["error"], 'No response from Flickr API');
        $logger->close();
    }

    function testGetFlickrPhotoSourceNoFlickrAPIKey() {
        global $THINKUP_CFG;
        $logger = new Logger($THINKUP_CFG['log_location']);
        $fa = new FlickrAPIAccessor('', $logger);

        $eurl = $fa->getFlickrPhotoSource('http://flic.kr/p/6YS7AE');
        $this->assertEqual($eurl["expanded_url"], '');
        $this->assertEqual($eurl["error"], '');
        //logger will have logged that the API key was not set

        $logger->close();
    }

    function testGetFlickrPhotoSourceFlickrAPIReturnsError() {
        global $THINKUP_CFG;
        $logger = new Logger($THINKUP_CFG['log_location']);
        $fa = new FlickrAPIAccessor('dummykey', $logger);

        $eurl = $fa->getFlickrPhotoSource('http://flic.kr/p/6YS7AE');
        $this->assertEqual($eurl["expanded_url"], '');
        $this->assertEqual($eurl["error"], 'Photo not found');

        $logger->close();
    }

    function testGetFlickrPhotoSourceSuccess() {
        global $THINKUP_CFG;
        $logger = new Logger($THINKUP_CFG['log_location']);
        $fa = new FlickrAPIAccessor('dummykey', $logger);

        $this->assertTrue(isset($fa));

        $eurl = $fa->getFlickrPhotoSource('http://flic.kr/p/7QAWC7');
        $this->assertEqual($eurl["expanded_url"], 'http://farm3.static.flickr.com/2755/4488149974_04d9558212_m.jpg');

        $logger->close();
    }

}
?>
