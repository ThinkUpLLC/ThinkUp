<?php
if ( !isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS ) {
    require_once '../../../../tests/config.tests.inc.php';
}
require_once THINKUP_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'extlib/simpletest/web_tester.php';

require_once THINKUP_ROOT_PATH.'webapp/plugins/flickrthumbnails/tests/classes/mock.FlickrAPIAccessor.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/flickrthumbnails/model/class.FlickrThumbnailsPlugin.php';

class TestOfFlickrThumbnailsPlugin extends ThinkUpUnitTestCase {

    public function  __construct() {
        $this->UnitTestCase('FlickrThumbnailsPlugin class test');
    }

    public function  setUp() {
        parent::setUp();
        $webapp = Webapp::getInstance();
        $crawler = Crawler::getInstance();
        $webapp->registerPlugin('flickrthumbnails', 'FlickrThumbnailsPlugin');
        $crawler->registerCrawlerPlugin('FlickrThumbnailsPlugin');

        //Insert test links (not images, not expanded)
        $counter = 0;
        while ($counter < 40) {
            $post_id = $counter + 80;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);

            $q = "INSERT INTO tu_links (url, title, clicks, post_id, is_image) VALUES ('http://example.com/".$counter.
            "', 'Link $counter', 0, $post_id, 0);";
            $this->db->exec($q);

            $counter++;
        }

        //Insert test links (images on Flickr that don't exist, not expanded)
        $counter = 0;
        while ($counter < 2) {
            $post_id = $counter + 80;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);

            $q = "INSERT INTO tu_links (url, title, clicks, post_id, is_image) VALUES ('http://flic.kr/p/".$counter.
            "', 'Link $counter', 0, $post_id, 1);";
            $this->db->exec($q);

            $counter++;
        }

        // Insert legit Flickr shortened link, not expanded
        $q = "INSERT INTO tu_links (url, title, clicks, post_id, is_image) VALUES ('http://flic.kr/p/7QQBy7', 'Link',
         0, 200, 1);";
        $this->db->exec($q);


        //Insert test links with errors (images from Flickr, not expanded)
        $counter = 0;
        while ($counter < 5) {
            $post_id = $counter + 80;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);

            $q = "INSERT INTO tu_links (url, title, clicks, post_id, is_image, error) VALUES ('http://flic.kr/p/".
            $counter."', 'Link $counter', 0, $post_id, 1, 'Generic test error message, Photo not found');";
            $this->db->exec($q);

            $counter++;
        }

    }

    public function  tearDown() {
        parent::tearDown();
    }

    public function  testFlickrCrawl() {
        $crawler = Crawler::getInstance();
        $config = Config::getInstance();

        //use fake Flickr API key
        $plugin_builder = FixtureBuilder::build('plugins', array('id'=>'2', 'folder_name'=>'flickrthumbnails'));
        $option_builder = FixtureBuilder::build('plugin_options', array(
            'plugin_id' => '2',
            'option_name' => 'flickr_api_key',
            'option_value' => 'dummykey') );
        //$config->setValue('flickr_api_key', 'dummykey');

        $crawler->crawl();

        $ldao = DAOFactory::getDAO('LinkDAO');

        $link = $ldao->getLinkById(43);
        $this->assertEqual($link->expanded_url, 'http://farm3.static.flickr.com/2755/4488149974_04d9558212_m.jpg');
        $this->assertEqual($link->error, '');

        $link = $ldao->getLinkById(42);
        $this->assertEqual($link->expanded_url, '');
        $this->assertEqual($link->error, 'No response from Flickr API');

        $link = $ldao->getLinkById(41);
        $this->assertEqual($link->expanded_url, '');
        $this->assertEqual($link->error, 'No response from Flickr API');
    }
}