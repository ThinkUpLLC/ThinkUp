<?php
/**
 *
 * ThinkUp/webapp/plugins/flickrthumbnails/tests/TestOfFlickrAPIAccessor.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';

require_once THINKUP_WEBAPP_PATH.'plugins/expandurls/tests/classes/mock.FlickrAPIAccessor.php';

class TestOfFlickrAPIAccessor extends UnitTestCase {

    public function testGetFlickrPhotoSourceFlickrAPINonResponsive() {
        $logger = Logger::getInstance();
        $flickr_api = new FlickrAPIAccessor('dummykey', $logger);

        $photo_details = $flickr_api->getFlickrPhotoSource('http://flic.kr/p/6YS7AEasdfasdfasdfasdfasdf');
        //this file does not exist so response will be false
        $this->assertEqual($photo_details["image_src"], '');
        $this->assertEqual($photo_details["error"], 'No response from Flickr API');
        $logger->close();
    }

    public function testGetFlickrPhotoSourceNoFlickrAPIKey() {
        $logger = Logger::getInstance();
        $flickr_api = new FlickrAPIAccessor('', $logger);

        $photo_details = $flickr_api->getFlickrPhotoSource('http://flic.kr/p/6YS7AE');
        $this->assertEqual($photo_details["image_src"], '');
        $this->assertEqual($photo_details["error"], '');
        //logger will have logged that the API key was not set

        $logger->close();
    }

    public function testGetFlickrPhotoSourceFlickrAPIReturnsError() {
        $logger = Logger::getInstance();
        $flickr_api = new FlickrAPIAccessor('dummykey', $logger);

        $photo_details = $flickr_api->getFlickrPhotoSource('http://flic.kr/p/6YS7AE');
        $this->assertEqual($photo_details["image_src"], '');
        $this->assertEqual($photo_details["error"], 'Photo not found');

        $logger->close();
    }

    public function testGetFlickrPhotoSourceSuccess() {
        $logger = Logger::getInstance();
        $flickr_api = new FlickrAPIAccessor('dummykey', $logger);

        $this->assertTrue(isset($flickr_api));

        $photo_details = $flickr_api->getFlickrPhotoSource('http://flic.kr/p/7QAWC7');
        $this->assertEqual($photo_details["image_src"],
        'http://farm3.static.flickr.com/2755/4488149974_04d9558212_m.jpg');

        $logger->close();
    }
}
