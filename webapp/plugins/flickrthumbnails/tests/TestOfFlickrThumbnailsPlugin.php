<?php
/**
 *
 * ThinkUp/webapp/plugins/flickrthumbnails/tests/TestOfFlickrThumbnailsPlugin.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Christoffer Viken, Dwi Widiastuti, Guillaume Boudreau
 *
 * LICENSE:
 *
 * This file is part of ThinkUp.
 * 
 * ThinkUp is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * ThinkUp is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with ThinkUp.  If not, see <http://www.gnu.org/licenses/>.
 *
*/
/**
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Christoffer Viken <christoffer[at]viken[dot]me>
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 * @author Guillaume Boudreau <gboudreau[at]pommepause[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Christoffer Viken, Dwi Widiastuti, Guillaume Boudreau
*/
if ( !isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS ) {
    require_once '../../../../tests/config.tests.inc.php';
}
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';

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
    }

    public function  tearDown() {
        parent::tearDown();
    }

    public function  testFlickrCrawl() {
        $builders = $this->buildData();

        $crawler = Crawler::getInstance();
        $config = Config::getInstance();

        //use fake Flickr API key
        $plugin_builder = FixtureBuilder::build('plugins', array('id'=>'2', 'folder_name'=>'flickrthumbnails'));
        $option_builder = FixtureBuilder::build('plugin_options', array(
            'plugin_id' => '2',
            'option_name' => 'flickr_api_key',
            'option_value' => 'dummykey') );
        //$config->setValue('flickr_api_key', 'dummykey');

        $this->simulateLogin('admin@example.com', true);
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

    private function buildData() {
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
        $counter = 0;
        while ($counter < 2) {
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
}