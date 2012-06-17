<?php
/**
 *
 * ThinkUp/webapp/plugins/expandurls/tests/TestOfExpandURLsPlugin.php
 *
 * Copyright (c) 2009-2012 Gina Trapani, Guillaume Boudreau, Christoffer Viken
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * Test of ExpandURLs Crawler plugin
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2012 Gina Trapani, Guillaume Boudreau, Christoffer Viken
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

require_once THINKUP_WEBAPP_PATH.'plugins/expandurls/tests/classes/mock.FlickrAPIAccessor.php';
require_once THINKUP_WEBAPP_PATH.'plugins/expandurls/tests/classes/mock.BitlyAPIAccessor.php';
require_once THINKUP_WEBAPP_PATH.'plugins/expandurls/tests/classes/mock.URLExpander.php';
//require_once THINKUP_WEBAPP_PATH.'plugins/expandurls/model/class.URLExpander.php';
require_once THINKUP_WEBAPP_PATH.'plugins/expandurls/model/class.ExpandURLsPlugin.php';
//require_once THINKUP_WEBAPP_PATH.'plugins/expandurls/model/class.BitlyAPIAccessor.php';

class TestOfExpandURLsPlugin extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
        $crawler = Crawler::getInstance();
        $crawler->registerCrawlerPlugin('ExpandURLsPlugin');
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $plugin = new ExpandURLsPlugin();
        $this->assertIsA($plugin, 'ExpandURLsPlugin');
        $this->assertEqual(count($plugin->required_settings), 0);
        $this->assertTrue($plugin->isConfigured());
    }

    public function testExpandURLsCrawl() {
        $builders = $this->buildData();

        //use fake Bitly API key
        $builders[] = FixtureBuilder::build('options', array('namespace' => OptionDAO::PLUGIN_OPTIONS . '-4',
        'option_name' => 'bitly_api_key', 'option_value' => 'dummykey'));

        //use fake Bitly login name
        $builder[] = FixtureBuilder::build('options', array('namespace' => OptionDAO::PLUGIN_OPTIONS . '-4',
        'option_name' => 'bitly_login', 'option_value' => 'bitly123'));

        $this->simulateLogin('admin@example.com', true);
        $crawler = Crawler::getInstance();
        $crawler->crawl();

        //the crawler closes the log so we have to re-open it
        $logger = Logger::getInstance();
        $link_dao = DAOFactory::getDAO('LinkDAO');

        $link = $link_dao->getLinkById(1);
        $this->debug($link->url);
        $this->assertEqual($link->expanded_url, 'http://www.thewashingtonnote.com/archives/2010/04/communications/');

        $link = $link_dao->getLinkById(2);
        $this->debug($link->url);
        $this->assertEqual($link->expanded_url, '');

        $link = $link_dao->getLinkById(3);
        $this->assertEqual($link->expanded_url, '');
        $this->assertEqual($link->error, 'Invalid URL');

        $link = $link_dao->getLinkById(4);
        $this->assertEqual($link->expanded_url, 'http://bit.ly');
        $this->assertEqual($link->error, '');

        $link = $link_dao->getLinkById(5);
        $this->debug($link->url);
        $this->assertEqual($link->expanded_url, 'http://thinkupapp.com/');
        $this->assertEqual($link->error, '');

        $link = $link_dao->getLinkById(6);
        $this->debug($link->url);
        $this->assertEqual($link->expanded_url,
        'http://www.macworld.com/article/161927/2011/08/steve_jobs_resigns_as_apple_ceo.html#lsrc.twt_danfrakes');
        $this->assertEqual($link->error, '');

        $link = $link_dao->getLinkById(7);
        $this->debug($link->url);
        $this->assertEqual($link->expanded_url, 'http://vimeo.com/27427184');
        $this->assertEqual($link->error, '');

        $link = $link_dao->getLinkById(8);
        $this->debug($link->url);
        $this->assertEqual($link->expanded_url, 'http://twitpic.com/6bheho');
        $this->assertEqual($link->image_src, 'http://twitpic.com/show/thumb/6bheho');

        $link = $link_dao->getLinkById(9);
        $this->debug($link->url);
        $this->assertEqual($link->expanded_url, 'https://secure.aclu.org/site/Advocacy?'.
        'cmd=display&page=UserAction&id=3561&s_subsrc=110819_CAyouth_tw');
        $this->assertEqual($link->image_src, '');
        $this->assertEqual($link->error, '');

        $link = $link_dao->getLinkById(10);
        $this->debug($link->url);
        $this->assertEqual($link->url, 'http://t.co/oDI8D34');
        $this->assertEqual($link->expanded_url, 'http://yfrog.com/gz2inwrj');
        $this->assertEqual($link->image_src, 'http://yfrog.com/gz2inwrj.th.jpg');
        $this->assertEqual($link->error, '');

        $link = $link_dao->getLinkById(11);
        $this->debug($link->url);
        $this->assertEqual($link->url, 'http://wp.me/p1fxNB-2F');
        $this->assertEqual($link->expanded_url, '');
        $this->assertEqual($link->image_src, '');
        $this->assertEqual($link->error, 'Invalid URL - relocates to nowhere');

        $link = $link_dao->getLinkById(12);
        $this->debug($link->url);
        $this->assertEqual($link->url, 'http://flic.kr/p/8T8ZyA');
        $this->assertEqual($link->expanded_url, 'http://www.flickr.com/photos/swirlee/5173198094/');
        $this->assertEqual($link->image_src, '');
        $this->assertEqual($link->error, '');

        //check that short URLs were saved
        $sql = "SELECT * FROM " . $this->table_prefix . 'links_short';
        $stmt = ShortLinkMySQLDAO::$PDO->query($sql);
        $data = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($data, $row);
        }
        $stmt->closeCursor();
        $this->assertEqual(count($data), 8);
        $this->assertEqual($data[0]['id'], 1);
        $this->assertEqual($data[0]['link_id'], 1);
        $this->assertEqual($data[0]['short_url'], 'http://bit.ly/a5VmbO');
        $this->assertEqual($data[0]['click_count'], 0);
    }

    private function buildData() {
        $builders[] = FixtureBuilder::build('owners', array(
            'id' => 1,
            'email' => 'admin@example.com',
            'pwd' => 'XXX',
            'is_activated' => 1,
            'is_admin' => 1
        ));

        //Insert test links (not images, not expanded)
        $builders[] = FixtureBuilder::build('links', array(
            'id' => 1,
            'url' => 'http://bit.ly/a5VmbO',
            'expanded_url' => null,
            'title' => '',
            'clicks' => 0,
            'post_id' => 1,
            'image_src' => '',
            'error' => null
        ));

        // An invalid link (will return 404 Not Found)
        $builders[] = FixtureBuilder::build('links', array(
            'id' => 2,
            'url' => 'http://bit.ly/0101001010',
            'expanded_url' => null,
            'title' => '',
            'clicks' => 0,
            'post_id' => 1,
            'image_src' => '',
            'error' => null
        ));

        // A malformed URL
        $builders[] = FixtureBuilder::build('links', array(
            'id' => 3,
            'url' => 'http:///asdf.com',
            'expanded_url' => null,
            'title' => '',
            'clicks' => 0,
            'post_id' => 1,
            'image_src' => '',
            'error' => null
        ));

        // Valid URL with no path
        $builders[] = FixtureBuilder::build('links', array(
            'id' => 4,
            'url' => 'http://bit.ly',
            'expanded_url' => null,
            'title' => '',
            'clicks' => 0,
            'post_id' => 1,
            'image_src' => '',
            'error' => null
        ));

        // Valid URL with no path ending in a slash
        $builders[] = FixtureBuilder::build('links', array(
            'id' => 5,
            'url' => 'http://thinkupapp.com/',
            'expanded_url' => null,
            'title' => '',
            'clicks' => 0,
            'post_id' => 1,
            'image_src' => '',
            'error' => null
        ));

        // A double-shortened URL, t.co pointing to valid short URL macw.us/qpivQF
        $builders[] = FixtureBuilder::build('links', array(
            'id' => 6,
            'url' => 'http://t.co/xRRz4lk',
            'expanded_url' => null,
            'title' => '',
            'clicks' => 0,
            'post_id' => 1,
            'image_src' => '',
            'error' => null
        ));

        // A double-shortened URL, t.co pointing to an invalid URL
        $builders[] = FixtureBuilder::build('links', array(
            'id' => 7,
            'url' => 'http://t.co/LfN0PXm',
            'expanded_url' => null,
            'title' => '',
            'clicks' => 0,
            'post_id' => 1,
            'image_src' => '',
            'error' => null
        ));

        // Shortened known image URL (Twitpic)
        $builders[] = FixtureBuilder::build('links', array(
            'id' => 8,
            'url' => 'http://bit.ly/qpBNce',
            'expanded_url' => null,
            'title' => '',
            'clicks' => 0,
            'post_id' => 1,
            'image_src' => '',
            'error' => null
        ));

        // Shortened https URL
        $builders[] = FixtureBuilder::build('links', array(
            'id' => 9,
            'url' => 'http://t.co/MZrNmBc',
            'expanded_url' => null,
            'title' => '',
            'clicks' => 0,
            'post_id' => 1,
            'image_src' => '',
            'error' => null
        ));

        // Double-shortened known image URL (Yfrog)
        $builders[] = FixtureBuilder::build('links', array(
            'id' => 10,
            'url' => 'http://t.co/oDI8D34',
            'expanded_url' => null,
            'title' => '',
            'clicks' => 0,
            'post_id' => 1,
            'image_src' => '',
            'error' => null
        ));

        // Expanded URL is empty
        $builders[] = FixtureBuilder::build('links', array(
            'id' => 11,
            'url' => 'http://wp.me/p1fxNB-2F',
            'expanded_url' => null,
            'title' => '',
            'clicks' => 0,
            'post_id' => 1,
            'image_src' => '',
            'error' => null
        ));

        // Flickr URL with relative redirect to path
        $builders[] = FixtureBuilder::build('links', array(
            'id' => 12,
            'url' => 'http://flic.kr/p/8T8ZyA',
            'expanded_url' => null,
            'title' => '',
            'clicks' => 0,
            'post_id' => 1,
            'image_src' => '',
            'error' => null
        ));

        return $builders;
    }

    public function  testFlickrCrawl() {
        $builders = $this->buildFlickrData();

        $crawler = Crawler::getInstance();
        $config = Config::getInstance();

        //use fake Flickr API key
        $option_builder = FixtureBuilder::build('options', array('namespace' => OptionDAO::PLUGIN_OPTIONS . '-4',
        'option_name' => 'flickr_api_key', 'option_value' => 'dummykey') );

        $this->simulateLogin('admin@example.com', true);
        $crawler->crawl();

        $link_dao = DAOFactory::getDAO('LinkDAO');

        $link = $link_dao->getLinkById(43);
        //        $this->debug(Utils::varDumpToString($link));
        $this->assertEqual($link->image_src, 'http://farm3.static.flickr.com/2755/4488149974_04d9558212_m.jpg');
        $this->assertEqual($link->expanded_url, 'http://flic.kr/p/7QQBy7');
        $this->assertEqual($link->error, '');

        $link = $link_dao->getLinkById(42);
        $this->assertEqual($link->expanded_url, '');
        $this->assertEqual($link->error, 'No response from Flickr API');

        $link = $link_dao->getLinkById(41);
        $this->assertEqual($link->expanded_url, '');
        $this->assertEqual($link->error, 'No response from Flickr API');
    }

    private function buildFlickrData() {
        $builders = array();

        $builders[] = FixtureBuilder::build('owners', array(
            'id' => 1,
            'email' => 'admin@example.com',
            'pwd' => 'XXX',
            'is_activated' => 1,
            'is_admin' => 1
        ));

        //Insert test links (not images, not expanded)
        $counter = 0;
        while ($counter < 40) {
            $post_id = $counter + 80;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);

            $builders[] = FixtureBuilder::build('links', array(
                'url' => "http://example.com/$counter",
                'expanded_url' => null,
                'title' => "Link $counter",
                'clicks' => 0,
                'post_id' => $post_id,
                'image_src' => '',
                'error' => null
            ));
            $counter++;
        }

        //Insert test links (images on Flickr that don't exist, not expanded)
        $counter = 40;
        while ($counter < 42) {
            $post_id = $counter + 80;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);

            $builders[] = FixtureBuilder::build('links', array(
                'url' => "http://flic.kr/p/$counter",
                'expanded_url' => null,
                'title' => "Link $counter",
                'clicks' => 0,
                'post_id' => $post_id,
                'image_src' => 'image.png',
                'error' => null
            ));
            $counter++;
        }

        // Insert legit Flickr shortened link, not expanded
        $builders[] = FixtureBuilder::build('links', array(
            'url' => "http://flic.kr/p/7QQBy7",
            'expanded_url' => null,
            'title' => "Link 0",
            'clicks' => 0,
            'post_id' => 200,
            'image_src' => 'thumbnail.png',
            'error' => null
        ));

        //Insert test links with errors (images from Flickr, not expanded)
        $counter = 0;
        while ($counter < 5) {
            $post_id = $counter + 80;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);

            $builders[] = FixtureBuilder::build('links', array(
                'url' => "http://flic.kr/p/$counter",
                'expanded_url' => null,
                'title' => "Link $counter",
                'clicks' => 0,
                'post_id' => $post_id,
                'image_src' => 'image.jpg',
                'error' => 'Photo not found'
                ));
                $counter++;
        }
        return $builders;
    }

    private function buildInstagramData() {
        $builders = array();

        $builders[] = FixtureBuilder::build('owners', array(
            'id' => 1,
            'email' => 'admin@example.com',
            'pwd' => 'XXX',
            'is_activated' => 1,
            'is_admin' => 1
        ));

        //Insert test links (not images, not expanded)
        $counter = 0;
        while ($counter < 40) {
            $post_id = $counter + 80;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);

            $builders[] = FixtureBuilder::build('links', array(
                'url' => "http://example.com/$counter",
                'expanded_url' => null,
                'title' => "Link $counter",
                'clicks' => 0,
                'post_id' => $post_id,
                'image_src' => '',
                'error' => null
            ));
            $counter++;
        }

        //Insert test links (images on Instagram that don't exist, not expanded)
        $counter = 40;
        while ($counter < 42) {
            $post_id = $counter + 80;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);

            $builders[] = FixtureBuilder::build('links', array(
                'url' => "http://instagr.am/$counter",
                'expanded_url' => "http://instagr.am/$counter",
                'title' => "Link $counter",
                'clicks' => 0,
                'post_id' => $post_id,
                'image_src' => "http://instagr.am/$counter/media/",
                'error' => null
            ));
            $counter++;
        }

        // Insert legit Instagram shortened link, not expanded
        $builders[] = FixtureBuilder::build('links', array(
            'url' => "http://instagr.am/p/oyQ6/",
            'expanded_url' => null,
            'title' => "Link 0",
            'clicks' => 0,
            'post_id' => 200,
            'image_src' => 'image.png',
            'error' => null
        ));

        //Insert test links with errors (images from Instagram, not expanded)
        $counter = 0;
        while ($counter < 5) {
            $post_id = $counter + 80;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);

            $builders[] = FixtureBuilder::build('links', array(
                'url' => "http://instagr.am/p/$counter",
                'expanded_url' => null,
                'title' => "Link $counter",
                'clicks' => 0,
                'post_id' => $post_id,
                'image_src' => 'photo.png',
                'error' => 'Photo not found'
                ));
                $counter++;
        }
        return $builders;
    }

    public function testExpandInstagramImageURLs() {
        $builders = $this->buildInstagramData();
        $crawler = Crawler::getInstance();
        $config = Config::getInstance();

        $option_builder = FixtureBuilder::build('options', array( 'namespace' => OptionDAO::PLUGIN_OPTIONS . '-4',
        'option_name' => 'flickr_api_key', 'option_value' => 'dummykey') );

        $this->simulateLogin('admin@example.com', true);
        $crawler->crawl();

        $link_dao = DAOFactory::getDAO('LinkDAO');

        $link = $link_dao->getLinkById(43);
        $this->debug(Utils::varDumpToString($link));
        //Instagr.am constantly changes the location of their images so it's an unpredictable assertion
        //        $this->assertEqual($link->expanded_url,
        //        'http://images.instagram.com/media/2010/12/20/f0f411210cc54353be07cf74ceb79f3b_7.jpg');
        $this->assertEqual($link->error, '');

        $link = $link_dao->getLinkById(42);
        $this->assertEqual($link->expanded_url, 'http://instagr.am/41');
        $this->assertEqual($link->image_src, 'http://instagr.am/41/media/');
        $this->assertEqual($link->error, '');

        $link = $link_dao->getLinkById(41);
        $this->assertEqual($link->expanded_url, 'http://instagr.am/40');
        $this->assertEqual($link->image_src, 'http://instagr.am/40/media/');
        $this->assertEqual($link->error, '');
    }

    public function  testBitlyCrawl() {
        $builders = $this->buildBitlyData();

        $crawler = Crawler::getInstance();
        $config = Config::getInstance();

        //use fake Bitly API key
        $builders[] = FixtureBuilder::build('options', array('namespace' => OptionDAO::PLUGIN_OPTIONS . '-4',
        'option_name' => 'bitly_api_key', 'option_value' => 'dummykey'));

        //use fake Bitly login name
        $builder[] = FixtureBuilder::build('options', array('namespace' => OptionDAO::PLUGIN_OPTIONS . '-4',
        'option_name' => 'bitly_login', 'option_value' => 'bitly123'));

        $this->simulateLogin('admin@example.com', true);
        $crawler->crawl();

        $link_dao = DAOFactory::getDAO('LinkDAO');

        $link = $link_dao->getLinkById(43);

        $this->assertEqual($link->expanded_url, 'http://static.ak.fbcdn.net/rsrc.php/zw/r/ZEKh4ZZQY74.png');
        $this->assertEqual($link->title, 'Bitly Test URL');
        $this->assertEqual($link->error, '');

        $link = $link_dao->getLinkById(42);
        $this->assertEqual($link->expanded_url,
        'http://bitly.com/a/warning?url=http%3a%2f%2fwww%2ealideas%2ecom%2f&hash=41');
        $this->assertEqual($link->error, 'No response from http://bit.ly API');

        $link = $link_dao->getLinkById(41);
        $this->debug($link->url);
        $this->assertEqual($link->expanded_url, 'http://static.ak.fbcdn.net/rsrc.php/zw/r/ZEKh4ZZQY74.png');
        $this->assertEqual($link->error, 'No response from http://bit.ly API');
    }

    private function buildBitlyData() {
        $builders = array();

        $builders[] = FixtureBuilder::build('owners', array(
            'id' => 1,
            'email' => 'admin@example.com',
            'pwd' => 'XXX',
            'is_activated' => 1,
            'is_admin' => 1
        ));

        //Insert test links (not images, not expanded)
        $counter = 0;
        while ($counter < 40) {
            $post_id = $counter + 80;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);

            $builders[] = FixtureBuilder::build('links', array(
                'url' => "http://example.com/$counter",
                'expanded_url' => null,
                'title' => "Link $counter",
                'clicks' => 0,
                'post_id' => $post_id,
                'image_src' => '',
                'error' => null
            ));
            $counter++;
        }

        //Insert test links (links that don't exist, not expanded)
        $counter = 40;
        while ($counter < 42) {
            $post_id = $counter + 80;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);

            $builders[] = FixtureBuilder::build('links', array(
                'url' => "http://bit.ly/$counter",
                'expanded_url' => null,
                'title' => "Link $counter",
                'clicks' => 0,
                'post_id' => $post_id,
                'image_src' => 'image.png',
                'error' => null
            ));
            $counter++;
        }

        // Insert legit Bitly shortened link, not expanded
        $builders[] = FixtureBuilder::build('links', array(
            'url' => "http://bit.ly/dPOYo3",
            'expanded_url' => null,
            'title' => "Link 0",
            'clicks' => 0,
            'post_id' => 200,
            'image_src' => '',
            'error' => null
        ));

        //Insert test links with errors (images from Flickr, not expanded)
        $counter = 0;
        while ($counter < 5) {
            $post_id = $counter + 80;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);

            $builders[] = FixtureBuilder::build('links', array(
                'url' => "http://bit.ly/$counter",
                'expanded_url' => null,
                'title' => "Link $counter",
                'clicks' => 0,
                'post_id' => $post_id,
                'image_src' => '',
                'error' => 'Photo not found'
                ));
                $counter++;
        }
        return $builders;
    }

    //To test this with live URLs (which are endless loops as of 6/17/2012) comment out mock URLExpander
    //and comment in live URLExpander
    //    public function testURLExpansionWithEndlessLoop() {
    //        $builders[] = FixtureBuilder::build('owners', array(
    //            'id' => 1,
    //            'email' => 'admin@example.com',
    //            'pwd' => 'XXX',
    //            'is_activated' => 1,
    //            'is_admin' => 1
    //        ));
    //
    //        $builders[] = FixtureBuilder::build('links', array(
    //            'id' => 250,
    //            'url' => 'http://t.co/If5llJOb',
    //            'expanded_url' => null,
    //            'title' => '',
    //            'clicks' => 0,
    //            'post_id' => 1,
    //            'image_src' => '',
    //            'error' => null
    //        ));
    //
    //        $builders[] = FixtureBuilder::build('links', array(
    //            'id' => 251,
    //            'url' => 'http://t.co/V7NDaubm',
    //            'expanded_url' => null,
    //            'title' => '',
    //            'clicks' => 0,
    //            'post_id' => 1,
    //            'image_src' => '',
    //            'error' => null
    //        ));
    //        $this->simulateLogin('admin@example.com', true);
    //        $crawler = Crawler::getInstance();
    //        $crawler->crawl();
    //    }
}
