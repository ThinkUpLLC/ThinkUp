<?php 
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Utils.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.OwnerInstance.php';

class TestOfOwnerInstanceDAO extends ThinkTankUnitTestCase {
    var $logger;
    
    function TestOfOwnerInstanceDAO() {
        $this->UnitTestCase('OwnerInstanceDAO class test');
    }
    
    function setUp() {
        parent::setUp();
        $this->logger = Logger::getInstance();
    }
    
    function tearDown() {
        parent::tearDown();
        $this->logger->close();
        
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
