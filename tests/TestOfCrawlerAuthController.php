<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfCrawlerAuthController extends ThinkUpUnitTestCase {

    public function __construct() {
        $this->UnitTestCase('CrawlerAuthController class test');
    }

    public function testInvalidLogin() {
        $controller = new CrawlerAuthController(1, array('you@example.com', 'password'));
        $this->assertTrue(isset($controller));
        $results = $controller->go();
        $this->assertPattern('/ERROR: Invalid or missing username and password./', $results);
    }
}