<?php 
require_once (dirname(__FILE__).'/simpletest/autorun.php');


require_once (dirname(__FILE__).'/config.tests.inc.php');

ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once ("common/class.Utils.php");
require_once ("webapp/plugins/longurl/lib/class.LongUrlAPIAccessor.php");
require_once ("config.inc.php");


class TestOfLongUrlAPIAccessor extends UnitTestCase {

    function TestOfLongUrlAPIAccessor() {
        $this->UnitTestCase('LongURLAPIAccessor class test');
    }
    
    function setUp() {
    }
    
    function tearDown() {
    }
    
    function testExpandingLongURL() {
        global $THINKTANK_CFG;
        $logger = new Logger($THINKTANK_CFG['log_location']);
        $lur = new LongUrlAPIAccessor('ThinkTank', $logger);
        
        //TODO Mock LongURL API; we shouldn't hit the LongURL service live with every test
        //$eurl = $lur->expandURL('http://bit.ly/cZu3sF');
        //$this->assertEqual($eurl['long-url'], 'http://www.voiceofsandiego.org/credentialed/article_db5908a4-182f-11df-a2be-001cc4c002e0.html');
        
        $logger->close();
        
    }
    
}
?>
