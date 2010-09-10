<?php
if ( !isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS ) {
    require_once '../../../../tests/init.tests.php';
}
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
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
        $crawler = Crawler::getInstance();
        $crawler->registerCrawlerPlugin('ExpandURLsPlugin');
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testExpandURLsCrawl() {
        $builders = $this->buildData();
        
        $this->simulateLogin('admin@example.com', true);
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

    private function buildData() {
        $owner_builder = FixtureBuilder::build('owners', array(
            'id' => 1, 
            'email' => 'admin@example.com', 
            'pwd' => 'XXX', 
            'is_activated' => 1,
            'is_admin' => 1 
        ));

        //Insert test links (not images, not expanded)
        $link1_builder = FixtureBuilder::build('links', array(
            'id' => 1,
            'url' => 'http://bit.ly/a5VmbO',
            'expanded_url' => null,
            'title' => '',
            'clicks' => 0,
            'post_id' => 1,
            'is_image' => 0,
            'error' => null
        ));

        // An invalid link (will return 404 Not Found)
        $link2_builder = FixtureBuilder::build('links', array(
            'id' => 2,
            'url' => 'http://bit.ly/0101001010',
            'expanded_url' => null,
            'title' => '',
            'clicks' => 0,
            'post_id' => 1,
            'is_image' => 0,
            'error' => null
        ));
        
        // A malformed URL
        $link3_builder = FixtureBuilder::build('links', array(
            'id' => 3,
            'url' => 'http:///asdf.com',
            'expanded_url' => null,
            'title' => '',
            'clicks' => 0,
            'post_id' => 1,
            'is_image' => 0,
            'error' => null
        ));
        
        return array($owner_builder, $link1_builder, $link2_builder, $link3_builder);
    }
}