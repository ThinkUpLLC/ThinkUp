<?php
if (!isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS) {
    require_once '../../../../tests/config.tests.inc.php';
}
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Link.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Logger.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Utils.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/interface.ThinkTankPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/interface.CrawlerPlugin.php';
require_once $SOURCE_ROOT_PATH.'webapp/plugins/expandurls/model/class.ExpandURLsPlugin.php';

/**
 * Test of ExpandURLs Crawler plugin
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfExpandURLsPlugin extends ThinkTankUnitTestCase {

    function __construct() {
        $this->UnitTestCase('ExpandURLs plugin class test');
    }

    function setUp() {
        parent::setUp();

        //Insert test links (not images, not expanded)
        $q = "INSERT INTO tt_links (url, title, clicks, post_id, is_image) VALUES ('http://bit.ly/a5VmbO', '', 0, 1, 0);";
        $this->db->exec($q);

        $crawler = Crawler::getInstance();

        $crawler->registerCrawlerPlugin('ExpandURLsPlugin');
    }

    function tearDown() {
        parent::tearDown();
    }

    function testExpandURLsCrawl() {
        $crawler = Crawler::getInstance();
        $crawler->crawl();

        //the crawler closes the log so we have to re-open it
        $logger = Logger::getInstance();
        $ldao = DAOFactory::getDAO('LinkDAO');

        $link = $ldao->getLinkById(1);
        $this->assertEqual($link->expanded_url, 'http://www.thewashingtonnote.com/archives/2010/04/communications/');
        $this->assertEqual($link->error, '');
    }
}