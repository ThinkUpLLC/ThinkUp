<?php 
require_once (dirname(__FILE__).'/simpletest/autorun.php');
require_once (dirname(__FILE__).'/simpletest/web_tester.php');

require_once (dirname(__FILE__).'/config.tests.inc.php');

ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once ("classes/class.ThinkTankTestCase.php");
require_once ("common/class.Utils.php");
require_once ("common/class.OwnerInstance.php");


class TestOfOwnerInstanceDAO extends ThinkTankUnitTestCase {

    function TestOfOwnerInstanceDAO() {
        $this->UnitTestCase('OwnerInstanceDAO class test');
    }
    
    function setUp() {
        parent::setUp();
    }
    
    function tearDown() {
        parent::tearDown();
    }
    
    function testInsertOwnerInstance() {
        global $THINKTANK_CFG;
        $dao = new OwnerInstanceDAO($this->db, $this->logger);
        
        $result = $dao->insert(10, 20, 'aaa', 'bbb');
        
        $this->assertTrue($result);
    }
    
    function testGetOAuthTokens() {
        global $THINKTANK_CFG;
        $dao = new OwnerInstanceDAO($this->db, $this->logger);
        
        $result = $dao->insert(10, 20, 'aaa', 'bbb');
        
        $this->assertTrue($result);
        
        $tokens = $dao->getOAuthTokens(20);
        $this->assertEqual($tokens['oauth_access_token'], 'aaa');
        $this->assertEqual($tokens['oauth_access_token_secret'], 'bbb');
    }
    
    function testUpdateTokens() {
        global $THINKTANK_CFG;
        $dao = new OwnerInstanceDAO($this->db, $this->logger);
        
        $result = $dao->insert(10, 20, 'aaa', 'bbb');
        
        $this->assertTrue($result);
        
        $result = $dao->updateTokens(10, 20, 'ccc', 'ddd');
        $this->assertTrue($result);
        
        $tokens = $dao->getOAuthTokens(20);
        $this->assertEqual($tokens['oauth_access_token'], 'ccc');
        $this->assertEqual($tokens['oauth_access_token_secret'], 'ddd');
    }

    
}
