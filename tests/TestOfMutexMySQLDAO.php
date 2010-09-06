<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test of MutexDAO
 *
 * @author Guillaume Boudreau <gboudreau@pommepause.com>
 *
 */
class TestOfMutexMySQLDAO extends ThinkUpUnitTestCase {

    /**
     * Constructor
     * @return TestOfMutexMySQLDAO
     */
    public function __construct() {
        $this->UnitTestCase('MutexMySQLDAO class test');
    }

    /**
     * Set Up
     */
    public function setUp() {
        parent::setUp();
    }

    /**
     * Tear down
     */
    public function tearDown() {
        parent::tearDown();
    }

    /**
     * Test DAO constructor
     */
    public function testCreateNewMutexDAO() {
        $dao = DAOFactory::getDAO('MutexDAO');
        $this->assertTrue(isset($dao));
    }

    /**
     * Test getMutex
     */
    public function testGetMutex() {
        $mdao = DAOFactory::getDAO('MutexDAO');
        $lock_obtained = $mdao->getMutex('something');
        $this->assertTrue($lock_obtained);
        $lock_obtained = $mdao->getMutex('something_else');
        $this->assertTrue($lock_obtained);
        $lock_released = $mdao->releaseMutex('something_else');
        $this->assertTrue($lock_released);
        // Lock for something is gone, since we locked something_else
        $lock_released = $mdao->releaseMutex('something');
        $this->assertFalse($lock_released);
        // Lock for something_else was already released
        $lock_released = $mdao->releaseMutex('something_else');
        $this->assertFalse($lock_released);
    }

    /**
     * Test releaseMutex
     */
    public function testReleaseMutex() {
        $mdao = DAOFactory::getDAO('MutexDAO');
        $lock_obtained = $mdao->getMutex('something');
        $this->assertTrue($lock_obtained);
        
        // Checking release works
        $lock_released = $mdao->releaseMutex('something');
        $this->assertTrue($lock_released);

        // There is no lock to release
        $lock_released = $mdao->releaseMutex('something');
        $this->assertFalse($lock_released);
    }
}
