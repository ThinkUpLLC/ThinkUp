<?php
if ( !isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS ) {
    require_once '../../../../tests/config.tests.inc.php';
}
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'webapp/model/class.Logger.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Utils.php';
//require_once $SOURCE_ROOT_PATH.'webapp/plugins/flickrthumbnails/lib/class.FlickrAPIAccessor.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/flickrthumbnails/tests/classes/mock.FlickrAPIAccessor.php';
require_once $SOURCE_ROOT_PATH.'webapp/config.inc.php';

class TestOfFlickrAPIAccessor extends UnitTestCase {

    function TestOfFlickrAPIAccessor() {
        $this->UnitTestCase('FlickrAPIAccessor class test');
    }

    function setUp() {
    }

    function tearDown() {
    }

    function testGetFlickrPhotoSourceFlickrAPINonResponsive() {
        global $THINKTANK_CFG;
        $logger = new Logger($THINKTANK_CFG['log_location']);
        $fa = new FlickrAPIAccessor('dummykey', $logger);

        $eurl = $fa->getFlickrPhotoSource('http://flic.kr/p/6YS7AEasdfasdfasdfasdfasdf');
        //this file does not exist so response will be false
        $this->assertEqual($eurl["expanded_url"], '');
        $this->assertEqual($eurl["error"], 'No response from Flickr API');
        $logger->close();
    }

    function testGetFlickrPhotoSourceNoFlickrAPIKey() {
        global $THINKTANK_CFG;
        $logger = new Logger($THINKTANK_CFG['log_location']);
        $fa = new FlickrAPIAccessor('', $logger);

        $eurl = $fa->getFlickrPhotoSource('http://flic.kr/p/6YS7AE');
        $this->assertEqual($eurl["expanded_url"], '');
        $this->assertEqual($eurl["error"], '');
        //logger will have logged that the API key was not set

        $logger->close();
    }

    function testGetFlickrPhotoSourceFlickrAPIReturnsError() {
        global $THINKTANK_CFG;
        $logger = new Logger($THINKTANK_CFG['log_location']);
        $fa = new FlickrAPIAccessor('dummykey', $logger);

        $eurl = $fa->getFlickrPhotoSource('http://flic.kr/p/6YS7AE');
        $this->assertEqual($eurl["expanded_url"], '');
        $this->assertEqual($eurl["error"], 'Photo not found');

        $logger->close();
    }

    function testGetFlickrPhotoSourceSuccess() {
        global $THINKTANK_CFG;
        $logger = new Logger($THINKTANK_CFG['log_location']);
        $fa = new FlickrAPIAccessor('dummykey', $logger);

        $this->assertTrue(isset($fa));

        $eurl = $fa->getFlickrPhotoSource('http://flic.kr/p/7QAWC7');
        $this->assertEqual($eurl["expanded_url"], 'http://farm3.static.flickr.com/2755/4488149974_04d9558212_m.jpg');

        $logger->close();
    }

}
?>
