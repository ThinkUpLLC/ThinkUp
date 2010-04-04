<?php 
require_once (dirname(__FILE__).'/simpletest/autorun.php');


require_once (dirname(__FILE__).'/config.tests.inc.php');

ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once ("common/class.Logger.php");
require_once ("common/class.Utils.php");
require_once ("webapp/plugins/flickr/lib/class.FlickrAPIAccessor.php");
require_once ("config.inc.php");


class TestOfFlickrAPIAccessor extends UnitTestCase {

    function TestOfFlickrAPIAccessor() {
        $this->UnitTestCase('FlickrAPIAccessor class test');
    }
    
    function setUp() {
    }
    
    function tearDown() {
    }
    
    function testGetFlickrPhotoSource() {
        global $THINKTANK_CFG;
        $logger = new Logger($THINKTANK_CFG['log_location']);
        $fa = new FlickrAPIAccessor($THINKTANK_CFG['flickr_api_key'], $logger);
        
        $this->assertTrue(isset($fa));
        
        $eurl = $fa->getFlickrPhotoSource('http://flic.kr/p/7QAWC7');
        $this->assertEqual($eurl, 'http://farm3.static.flickr.com/2755/4488149974_04d9558212_m.jpg');
        
        $logger->close();
        
    }
    
}
?>
