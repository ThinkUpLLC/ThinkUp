<?php
if ( !isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS ) {
    require_once '../../../../tests/config.tests.inc.php';
}
require_once THINKUP_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'extlib/simpletest/web_tester.php';

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
