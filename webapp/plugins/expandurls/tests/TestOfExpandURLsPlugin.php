<?php
if ( !isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS ) {
    require_once '../../../../tests/init.tests.php';
}
require_once THINKUP_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';
require_once THINKUP_ROOT_PATH.'tests/classes/class.ThinkUpBasicUnitTestCase.php';

require_once THINKUP_ROOT_PATH.'webapp/plugins/expandurls/model/class.ExpandURLsPlugin.php';

/**
 * Test of ExpandURLs Crawler plugin
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfExpandURLsPlugin extends ThinkUpUnitTestCase {

    public function __construct() {
        $this->UnitTestCase('ExpandURLs plugin class test');
    }

    public function setUp() {
        parent::setUp();

        //Insert test links (not images, not expanded)
        $q = "INSERT INTO tu_links (url, title, clicks, post_id, is_image) VALUES ".
        "('http://bit.ly/a5VmbO', '', 0, 1, 0);";
        $this->db->exec($q);

        // An invalid link (will return 404 Not Found)
        $q = "INSERT INTO tu_links (url, title, clicks, post_id, is_image) VALUES ".
        "('http://bit.ly/01010010101', '', 0, 1, 0);";
        $this->db->exec($q);

        // A malformed URL
        $q = "INSERT INTO tu_links (url, title, clicks, post_id, is_image) VALUES ".
        "('http:///asdf.com', '', 0, 1, 0);";
        $this->db->exec($q);
        
        $crawler = Crawler::getInstance();

        $crawler->registerCrawlerPlugin('ExpandURLsPlugin');
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testExpandURLsCrawl() {
        $crawler = Crawler::getInstance();
        $crawler->crawl();

        //the crawler closes the log so we have to re-open it
        $logger = Logger::getInstance();
        $ldao = DAOFactory::getDAO('LinkDAO');

        $link = $ldao->getLinkById(1);
        $this->assertEqual($link->expanded_url, 'http://www.thewashingtonnote.com/archives/2010/04/communications/');
        $this->assertEqual($link->error, '');

        $link = $ldao->getLinkById(2);
        $this->assertEqual($link->expanded_url, '');
        $this->assertEqual($link->error, 'Error expanding URL');
    }
}