<?php
/**
 *
 * ThinkUp/webapp/plugins/expandurls/tests/TestOfExpandURLsPlugin.php
 *
 * Copyright (c) 2009-2011 Gina Trapani, Guillaume Boudreau, Christoffer Viken
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
 * @copyright 2009-2011 Gina Trapani, Guillaume Boudreau, Christoffer Viken
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
require_once 'tests/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

require_once THINKUP_ROOT_PATH.'webapp/plugins/expandurls/tests/classes/mock.FlickrAPIAccessor.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/expandurls/model/class.ExpandURLsPlugin.php';

class TestOfExpandURLsPlugin extends ThinkUpUnitTestCase {

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
        $link_dao = DAOFactory::getDAO('LinkDAO');

        $link = $link_dao->getLinkById(1);
        $this->assertEqual($link->expanded_url, 'http://www.thewashingtonnote.com/archives/2010/04/communications/');
        $this->assertEqual($link->error, '');

        $link = $link_dao->getLinkById(2);
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

    public function  testFlickrCrawl() {
        $builders = $this->buildFlickrData();

        $crawler = Crawler::getInstance();
        $config = Config::getInstance();

        //use fake Flickr API key
        $plugin_builder = FixtureBuilder::build('plugins', array('id'=>'2', 'folder_name'=>'expandurls'));
        $option_builder = FixtureBuilder::build('options', array(
            'namespace' => OptionDAO::PLUGIN_OPTIONS . '-2',
            'option_name' => 'flickr_api_key',
            'option_value' => 'dummykey') );

        $this->simulateLogin('admin@example.com', true);
        $crawler->crawl();

        $link_dao = DAOFactory::getDAO('LinkDAO');

        $link = $link_dao->getLinkById(43);
        $this->assertEqual($link->expanded_url, 'http://farm3.static.flickr.com/2755/4488149974_04d9558212_m.jpg');
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
                'is_image' => 0,
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
                'is_image' => 1,
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
            'is_image' => 1,
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
                'is_image' => 1,
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
                'is_image' => 0,
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
                'expanded_url' => null,
                'title' => "Link $counter",
                'clicks' => 0,
                'post_id' => $post_id,
                'is_image' => 1,
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
            'is_image' => 1,
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
                'is_image' => 1,
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

        $plugin_builder = FixtureBuilder::build('plugins', array('id'=>'2', 'folder_name'=>'expandurls'));
        $option_builder = FixtureBuilder::build('options', array(
            'namespace' => OptionDAO::PLUGIN_OPTIONS . '-2',
            'option_name' => 'flickr_api_key',
            'option_value' => 'dummykey') );

        $this->simulateLogin('admin@example.com', true);
        $crawler->crawl();

        $link_dao = DAOFactory::getDAO('LinkDAO');

        $link = $link_dao->getLinkById(43);
        //Instagr.am constantly changes the location of their images so it's an unpredictable assertion
        //        $this->assertEqual($link->expanded_url,
        //        'http://images.instagram.com/media/2010/12/20/f0f411210cc54353be07cf74ceb79f3b_7.jpg');
        $this->assertEqual($link->error, '');

        $link = $link_dao->getLinkById(42);
        $this->assertEqual($link->expanded_url, 'http://instagr.am/41/media/');

        $link = $link_dao->getLinkById(41);
        $this->assertEqual($link->expanded_url, 'http://instagr.am/40/media/');
    }
}