<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test PostErrorMySQLDAO
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfPostErrorMySQLDAO extends ThinkUpUnitTestCase {

    public function _construct() {
        $this->UnitTestCase('PostErrorMySQLDAO class test');
    }

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    /**
     * Test constructor
     */
    public function testConstructor(){
        $dao = new PostErrorMySQLDAO();
        $this->assertTrue(isset($dao));

    }

    /**
     * Test error insertion
     */
    public function testInsert() {
        $dao = new PostErrorMySQLDAO();
        $result = $dao->insertError(10, 'twitter', 404, 'Status not found', 930061);
        $this->assertEqual($result, 1);

        $result = $dao->insertError(11, 'twitter', 403, 'You are not autorized to see this status', 930061);
        $this->assertEqual($result, 2);
    }
}