<?php 
require_once (dirname(__FILE__).'/simpletest/autorun.php');
require_once (dirname(__FILE__).'/simpletest/web_tester.php');

require_once (dirname(__FILE__).'/config.tests.inc.php');

ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once ("classes/class.ThinkTankTestCase.php");
require_once ("common/class.Link.php");
require_once ("common/class.Logger.php");
require_once ("common/class.PluginHook.php");
require_once ("common/class.Crawler.php");
require_once ("common/class.Webapp.php");
require_once ("common/class.Utils.php");

/* Replicate all the global objects a plugin depends on; normally this is done in init.php */
// TODO Figure out a better way to do all this than global objects in init.php
$crawler = new Crawler();
$webapp = new Webapp();
// Instantiate global database variable
try {
    $db = new Database($THINKTANK_CFG);
    $conn = $db->getConnection();
}
catch(Exception $e) {
    echo $e->getMessage();
}
require_once ("plugins/expandurls/expandurls.php");


class TestOfExpandURLsPlugin extends ThinkTankUnitTestCase {

    function TestOfExpandURLsPlugin() {
        $this->UnitTestCase('ExpandURLs plugin class test');
    }
    
    function setUp() {
        parent::setUp();
        
        //Insert test links (not images, not expanded)
        
        $q = "INSERT INTO tt_links (url, title, clicks, post_id, is_image) VALUES ('http://bit.ly/a5VmbO', '', 0, 1, 0);";
        $this->db->exec($q);
        
    }
    
    function tearDown() {
        parent::tearDown();
    }
    
    function testExpandURLsCrawl() {
        global $crawler;
        $crawler->emit("crawl");
        
        $ldao = new LinkDAO($this->db, $this->logger);
        
        $link = $ldao->getLinkById(1);
        $this->assertEqual($link->expanded_url, 'http://www.thewashingtonnote.com/archives/2010/04/communications/');
        $this->assertEqual($link->error, '');
    }
    
}
?>
