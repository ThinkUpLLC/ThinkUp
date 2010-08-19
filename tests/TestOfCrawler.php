<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

require_once THINKUP_ROOT_PATH.'webapp/plugins/hellothinkup/model/class.HelloThinkUpPlugin.php';

/**
 * Test Crawler object
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfCrawler extends ThinkUpBasicUnitTestCase {

    /**
     * Constructor
     */
    public function __construct() {
        $this->UnitTestCase('Crawler class test');
    }

    /**
     * Set up test
     */
    public function setUp() {
        parent::setUp();
    }

    /**
     * Tear down test
     */
    public function tearDown() {
        parent::tearDown();
    }

    /**
     * Test Crawler singleton instantiation
     */
    public function testCrawlerSingleton() {
        $crawler = Crawler::getInstance();
        $this->assertTrue(isset($crawler));
        //clean copy of crawler, no registered plugins, will throw exception
        $this->expectException( new Exception("No plugin object defined for: hellothinkup") );
        $this->assertEqual($crawler->getPluginObject("hellothinkup"), "HelloThinkUpPlugin");
        //register a plugin
        $crawler->registerPlugin('hellothinkup', 'HelloThinkUpPlugin');
        $this->assertEqual($crawler->getPluginObject("hellothinkup"), "HelloThinkUpPlugin");

        //make sure singleton still has those values
        $crawler_two = Crawler::getInstance();
        $this->assertEqual($crawler->getPluginObject("hellothinkup"), "HelloThinkUpPlugin");
    }

    /**
     * Test Crawler->crawl
     */
    public function testCrawl() {
        $crawler = Crawler::getInstance();

        //        $crawler->registerPlugin('nonexistent', 'TestFauxPluginOne');
        //        $crawler->registerCrawlerPlugin('TestFauxPluginOne');
        //        $this->expectException( new Exception("The TestFauxPluginOne object does not have a crawl method.") );
        //        $crawler->crawl();

        $crawler->registerPlugin('hellothinkup', 'HelloThinkUpPlugin');
        $crawler->registerCrawlerPlugin('HelloThinkUpPlugin');
        $this->assertEqual($crawler->getPluginObject("hellothinkup"), "HelloThinkUpPlugin");
        $crawler->crawl();

    }
}
