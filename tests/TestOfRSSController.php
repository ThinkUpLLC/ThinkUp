<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test of RSSController
 *
 * @author Guillaume Boudreau <gboudreau@pommepause.com>
 *
 */
class TestOfRSSController extends ThinkUpUnitTestCase {
    public function __construct() {
        $this->UnitTestCase('RSSController class test');
    }

    public function setUp() {
        parent::setUp();
        $_SERVER['HTTP_HOST'] = 'http://localhost';
    }

    public function testConstructor() {
        $controller = new RSSController(true);
        $this->assertTrue(isset($controller));
    }

    public function testGo() {
        $builders = $this->buildData();
        $controller = new RSSController(true);
        $_GET['un'] = 'me@example.com';
        $_GET['as'] = Session::getAPISecretFromPassword('XXX');
        $results = $controller->go();
        $this->assertPattern("/ThinkUp crawl started/", $results);
        $this->assertPattern("/<rss version=\"2.0\"/", $results);
    }
    
    public function testGetAdditionalItems() {
        $builders = $this->buildData();
        // Test that an item is added in the RSS feed when the crawler log is not writable
        $controller = new RSSController(true);
        $config = Config::getInstance();
        $config->setValue('log_location', '/something/that/doesnt/exits');
        $_GET['un'] = 'me@example.com';
        $_GET['as'] = Session::getAPISecretFromPassword('XXX');
        $_SERVER['HTTP_HOST'] = 'http://localhost';
        $results = $controller->go();
        $this->assertPattern("/Error: crawler log is not writable/", $results);
    }

    private function buildData() {
        $owner_builder = FixtureBuilder::build('owners', array(
            'id' => 1, 
            'email' => 'me@example.com', 
            'pwd' => 'XXX', 
            'is_activated' => 1
        ));
        
        $instance_builder = FixtureBuilder::build('instances', array(
            'id' => 1,
            'network_username' => 'jack',
            'network' => 'twitter'
        ));

        $owner_instance_builder = FixtureBuilder::build('owner_instances', array(
            'owner_id' => 1, 
            'instance_id' => 1
        ));
        
        return array($owner_builder, $instance_builder, $owner_instance_builder);
    }
}
