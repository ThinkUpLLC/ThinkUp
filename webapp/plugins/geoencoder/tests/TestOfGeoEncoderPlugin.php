<?php
/**
 *
 * ThinkUp/webapp/plugins/geoencoder/tests/TestOfGeoEncoderPlugin.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Ekansh Preet Singh, Guillaume Boudreau
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkup.com).
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
 * Test of GeoEncoder ThinkUp plugin
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Ekansh Preet Singh, Guillaume Boudreau
 * @author Ekansh Preet Singh <ekanshpreet[at]gmail[dot]com>
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';

require_once THINKUP_WEBAPP_PATH.'plugins/geoencoder/model/class.GeoEncoderPlugin.php';
require_once THINKUP_WEBAPP_PATH.'plugins/geoencoder/tests/classes/mock.GeoEncoderCrawler.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterPlugin.php';

class TestOfGeoEncoderPlugin extends ThinkUpUnitTestCase {
    var $webapp_plugin_registrar;

    public function setUp() {
        parent::setUp();
        $this->webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        $this->webapp_plugin_registrar->registerPlugin('geoencoder', 'GeoEncoderPlugin');
        $this->webapp_plugin_registrar->registerPlugin('twitter', 'TwitterPlugin');
        $crawler_plugin_registrar = PluginRegistrarCrawler::getInstance();
        $crawler_plugin_registrar->registerCrawlerPlugin('GeoEncoderPlugin');
        $this->builders = self::buildData();
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    public function testConstructor() {
        $plugin = new GeoEncoderPlugin();
        $this->assertIsA($plugin, 'GeoEncoderPlugin');
        $this->assertEqual(count($plugin->required_settings), 1);
        $this->assertFalse($plugin->isConfigured());
    }

    public function testGeoEncoderCrawl() {
        $this->simulateLogin('admin@example.com', true);
        $crawler_plugin_registrar = PluginRegistrarCrawler::getInstance();
        $crawler_plugin_registrar->runRegisteredPluginsCrawl();

        //the crawler closes the log so we have to re-open it
        $logger = Logger::getInstance();
        $post_dao = DAOFactory::getDAO('PostDAO');
        $location_dao = DAOFactory::getDAO('LocationDAO');

        // Test 1: Checking Post for Successful Reverse Geoencoding
        $this->assertTrue($post_dao->isPostInDB('15645300636', 'twitter'));

        $post = $post_dao->getPost('15645300636', 'twitter');
        $this->assertEqual($post->is_geo_encoded, 1);
        $this->assertEqual($post->geo, '28.602815,77.049136');
        $this->assertEqual($post->location, 'Sector 4, New Delhi, Haryana, India');
        // Since this is just a post, reply_retweet_distance is 0
        $this->assertEqual($post->reply_retweet_distance, 0);

        // Test 2: Checking Post for successful Reverse Geoencoding
        $post = $post_dao->getPost('15219161227', 'twitter');
        $this->assertEqual($post->is_geo_encoded, 1);
        $this->assertEqual($post->geo, '28.56213,77.165297');
        $this->assertEqual($post->location, 'Vasant Vihar, Munirka, New Delhi, Delhi, India');

        // Test: Example of unsuccessful geoencoding resulting out of INVALID_REQUEST.
        // NOTE: Not a test case encountered in actual crawl
        $post = $post_dao->getPost('15331235880', 'twitter');
        $this->assertEqual($post->is_geo_encoded, 5);
        $this->assertEqual($post->geo, '28.60abc2815 77.049136');

        // Test 1: Checking Post for successful Geoencoding using "place" field
        $post = $post_dao->getPost('15052338902', 'twitter');
        $this->assertEqual($post->is_geo_encoded, 1);
        $this->assertEqual($post->geo,'28.6889398,77.1618859');
        $this->assertEqual($post->place, 'Sector 8, R.K. Puram, New Delhi');
        $this->assertEqual($post->location,
        'Keshav Puram Metro Station, Maharaja Nahar Singh Marg, New Delhi, Delhi, India');
        // Distance between main post and its reply (Geocoding Process)
        $this->assertEqual($post->reply_retweet_distance, 1161);

        // Test 2: Checking Post for successful Geoencoding using "place" field
        // This post is retrieved from tu_encoded_locations
        $post = $post_dao->getPost('14914043658', 'twitter');
        $this->assertEqual($post->is_geo_encoded, 1);
        $this->assertEqual($post->place, 'Sector 8, R.K. Puram, New Delhi');
        $this->assertEqual($post->location,
        'Keshav Puram Metro Station, Maharaja Nahar Singh Marg, New Delhi, Delhi, India');
        // When reply is Not in DB, reply_retweet_distance is -1
        $this->assertFalse($post_dao->isPostInDB('999999', 'twitter'));
        $this->assertEqual($post->reply_retweet_distance, -1);

        // Test 1: Checking Post for successful Geoencoding using "location" field (post had is_geo_encoded set to 3)
        $post = $post_dao->getPost('15338041815', 'twitter');
        $this->assertEqual($post->geo, '19.017656,72.856178');
        $this->assertEqual($post->place, NULL);
        $this->assertEqual($post->location, 'Mumbai, Maharashtra, India');
        $this->assertEqual($post->is_geo_encoded, 1);

        // Test 2: Checking Post for successful Geoencoding using "location" field
        $post = $post_dao->getPost('15344199472', 'twitter');
        $this->assertEqual($post->location, 'New Delhi, Delhi, India');
        $this->assertEqual($post->is_geo_encoded, 1);
        // Distance between Post and Retweet (Geocoding Process)
        $this->assertEqual($post->reply_retweet_distance, 18);

        // When all three fields are filled, <geo> is given the most preference
        $post = $post_dao->getPost('11259110570', 'twitter');
        $this->assertEqual($post->geo, '28.56213,77.165297');
        $this->assertEqual($post->place, 'Sector 8, R.K. Puram, New Delhi');
        $this->assertEqual($post->location, 'Vasant Vihar, Munirka, New Delhi, Delhi, India');
        $this->assertEqual($post->is_geo_encoded, 1);
        // Distance between reply and post (Reverse Geocoding Process)
        $this->assertEqual($post->reply_retweet_distance, 14);

        // When only place and location are filled, <place> is given preference
        $post = $post_dao->getPost('15052338902', 'twitter');
        $this->assertEqual($post->geo, '28.6889398,77.1618859');
        $this->assertEqual($post->place, 'Sector 8, R.K. Puram, New Delhi');
        $this->assertEqual($post->location,
        'Keshav Puram Metro Station, Maharaja Nahar Singh Marg, New Delhi, Delhi, India');
        $this->assertEqual($post->is_geo_encoded, 1);

        // Unsuccessful Geoencoding due to place field
        // NOTE: Not a test case encountered in real crawl
        $post = $post_dao->getPost('14913946516', 'twitter');
        $this->assertEqual($post->geo, NULL);
        $this->assertEqual($post->place, 'abc');
        $this->assertEqual($post->location, 'New Delhi');
        $this->assertEqual($post->is_geo_encoded, 2);
        $this->assertEqual($post->reply_retweet_distance, 0);

        //Unsuccessful Geoencoding due to location field
        $post = $post_dao->getPost('15268690400', 'twitter');
        $this->assertEqual($post->geo, NULL);
        $this->assertEqual($post->place, NULL);
        $this->assertEqual($post->location, 'abc');
        $this->assertEqual($post->is_geo_encoded, 2);

        //Unsuccessful Geoencoding due to location field resulting in INVALID_REQUEST
        // Removing this test due to inconsistent behavior between testing environments for non-UTF8 chars and errors
        // like:
        // General error: 1366 Incorrect string value: '\xDC' for column 'location' at row 1
        //$post = $post_dao->getPost('15244973830', 'twitter');
        //$this->assertEqual($post->location, 'Ü');
        //$this->assertEqual($post->is_geo_encoded, 5);

        //Unsuccessful Geoencoding due to all three fields being empty
        $post = $post_dao->getPost('15435434230', 'twitter');
        $this->assertEqual($post->geo, NULL);
        $this->assertEqual($post->place, NULL);
        $this->assertEqual($post->location, NULL);
        $this->assertEqual($post->is_geo_encoded, 6);

        //Reverse Geoencoding when latitude and longitude are found in location field instead of geo field
        $post = $post_dao->getPost('13212618909', 'twitter');
        $this->assertEqual($post->geo, '40.681839,-73.983734');
        $this->assertEqual($post->place, NULL);
        $this->assertEqual($post->location, 'Boerum Hill, Brooklyn, NY, USA');
        $this->assertEqual($post->is_geo_encoded, 1);
        // Retweet Distance in case of Reverse Geocoding Process
        $this->assertEqual($post->reply_retweet_distance, 11760);

        //Unsuccessful Geoencoding due to REQUEST_DENIED
        $post = $post_dao->getPost('12259110570', 'twitter');
        $this->assertEqual($post->place, 'request_denied');
        $this->assertEqual($post->is_geo_encoded, 4);

        //Unsuccessful Geoencoding due to OVER_QUERY_LIMIT
        $post = $post_dao->getPost('13259110570', 'twitter');
        $this->assertEqual($post->place, 'over_query_limit');
        $this->assertEqual($post->is_geo_encoded, 3);

        //After reaching OVER_QUERY_LIMIT, next posts are not geoencoded
        $post = $post_dao->getPost('15645301636', 'twitter');
        $this->assertEqual($post->is_geo_encoded, 0);
        $post = $post_dao->getPost('11331235880', 'twitter');
        $this->assertEqual($post->is_geo_encoded, 0);

        // Check up filling of tu_encoded_locations table
        $locations = $location_dao->getAllLocations();
        $this->assertEqual(count($locations), 6);
        $this->assertEqual($locations[0]['short_name'], "28.602815 77.049136");
        $this->assertEqual($locations[2]['short_name'], "Mumbai");
        $this->assertEqual($locations[5]['short_name'], "40.681839 -73.983734");
    }

    private function buildData() {
        $builders = array();

        $builders[] = FixtureBuilder::build('owners', array('id' => 1, 'email' => 'admin@example.com',
        'pwd' => 'XXX', 'is_activated' => 1, 'is_admin' => 1));

        $builders[] = FixtureBuilder::build('plugins', array('name'=>'Geoencoder', 'folder_name'=>'geoencoder',
        'is_active'=>1));

        //Insert test authors
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'127567137', 'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'772673', 'network'=>'twitter'));

        //Insert test posts
        $builders[] = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'15645300636', 'network'=>'twitter',
        'author_user_id'=>'127567137', 'author_username'=>'ekanshpreet', 'post_test'=>'thinking....',
        'location'=>'New Delhi', 'place'=>NULL, 'geo'=>'28.602815 77.049136', 'is_geo_encoded'=>0,
        'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null));

        $builders[] = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'15435434230', 'network'=>'twitter',
        'author_user_id'=>'127567137', 'author_username'=>'ekanshpreet', 'post_text'=>'i think its working now :D...',
        'location'=>NULL, 'place'=>NULL, 'geo'=>NULL, 'is_geo_encoded'=>6));

        $builders[] = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'15344199472', 'network'=>'twitter',
        'author_user_id'=>'127567137', 'author_username'=>'ekanshpreet', 'post_text'=>'lets try again ...',
        'location'=>'New Delhi', 'place'=>NULL, 'geo'=>NULL, 'is_retweet_by_friend'=>1,
        'in_retweet_of_post_id'=>'15645300636', 'is_geo_encoded'=>0, 'in_reply_to_post_id'=>null));

        $builders[] = FixtureBuilder::build('posts', array('id'=>4, 'post_id'=>'15338041815', 'network'=>'twitter',
        'author_user_id'=>'127567137', 'author_username'=>'ekanshpreet', 'post_text'=>'howdy ???',
        'location'=>'Mumbai', 'place'=>NULL, 'geo'=>NULL, 'is_geo_encoded'=>3));

        $builders[] = FixtureBuilder::build('posts', array('id'=>5, 'post_id'=>'15331235880', 'network'=>'twitter',
        'author_user_id'=>'127567137', 'author_username'=>'ekanshpreet', 'post_text'=>':)', 'location'=>'New Delhi',
        'place'=>NULL, 'geo'=>'28.60abc2815 77.049136', 'is_geo_encoded'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>6, 'post_id'=>'15268690400', 'network'=>'twitter',
        'author_user_id'=>'127567137', 'author_username'=>'ekanshpreet', 'post_text'=>'hmm... lets c...',
        'location'=>'abc', 'place'=>NULL, 'geo'=>NULL, 'is_geo_encoded'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>7, 'post_id'=>'15244973830', 'network'=>'twitter',
        'author_user_id'=>'127567137', 'author_username'=>'ekanshpreet', 'post_text'=>'hmmm....',
        /*location'=>'Ü',*/ 'place'=>NULL, 'geo'=>NULL, 'is_geo_encoded'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>8, 'post_id'=>'15219161227', 'network'=>'twitter',
        'author_user_id'=>'127567137', 'author_username'=>'ekanshpreet',
        'post_text'=>'RT @jerrybrito: New Podcast: Gina Trapani and Anil Dash on Expert Labs and ThinkUp ow.ly/17zfrX',
        'location'=>'New Delhi', 'place'=>NULL, 'geo'=>'28.56213 77.165297', 'is_geo_encoded'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>12, 'post_id'=>'15052338902', 'network'=>'twitter',
        'author_user_id'=>'127567137', 'author_username'=>'ekanshpreet',
        'post_text'=>'@imnishantg thats the problem.... :P', 'location'=>'New Delhi',
        'place'=>'Sector 8, R.K. Puram, New Delhi', 'geo'=>NULL, 'is_reply_by_friend'=>1,
        'in_reply_to_post_id'=>'15338041815', 'is_geo_encoded'=>0, 'in_retweet_of_post_id'=>null));

        $builders[] = FixtureBuilder::build('posts', array('id'=>13, 'post_id'=>'14914043658', 'network'=>'twitter',
        'author_user_id'=>'127567137', 'author_username'=>'ekanshpreet', 'post_text'=>'is done with exams !!!',
        'location'=>'New Delhi', 'place'=>'Sector 8, R.K. Puram, New Delhi', 'geo'=>NULL, 'is_geo_encoded'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>14, 'post_id'=>'14913946516', 'network'=>'twitter',
        'author_user_id'=>'127567137', 'author_username'=>'ekanshpreet', 'post_text'=>'is done with exams !!! :-)',
        'location'=>'New Delhi', 'place'=>'abc', 'geo'=>NULL, 'is_geo_encoded'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>15, 'post_id'=>'11259110570', 'network'=>'twitter',
        'author_user_id'=>'127567137', 'author_username'=>'ekanshpreet', 'post_text'=>'im here finally ;)....',
        'location'=>'New Delhi', 'place'=>'Sector 8, R.K. Puram, New Delhi', 'geo'=>'28.56213 77.165297',
        'is_reply_by_friend'=>1, 'in_reply_to_post_id'=>'14914043658', 'is_geo_encoded'=>0,
        'in_retweet_of_post_id'=>null));

        $builders[] = FixtureBuilder::build('posts', array('id'=>16, 'post_id'=>'12259110570', 'network'=>'twitter',
        'author_user_id'=>'127567137', 'author_username'=>'ekanshpreet', 'post_text'=>'im here finally ;)....',
        'location'=>'New Delhi', 'place'=>'request_denied', 'geo'=>NULL, 'is_geo_encoded'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>18, 'post_id'=>'13212618909', 'network'=>'twitter',
        'author_user_id'=>772673, 'author_username'=>'mwilkie', 'post_text'=>'Just watched chris corn cob a sheep.',
        'location'=>'iPhone: 40.681839,-73.983734', 'place'=>NULL, 'geo'=>NULL, 'is_retweet_by_friend'=>1,
        'in_retweet_of_post_id'=>'11259110570', 'is_geo_encoded'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>19, 'post_id'=>'1231210570', 'network'=>'twitter',
        'author_user_id'=>'127567137', 'author_username'=>'ekanshpreet', 'post_text'=>'im here finally ;)....',
        'location'=>'New Delhi', 'place'=>'Sector 8, R.K. Puram, New Delhi', 'geo'=>'28.56213 77.165297',
        'is_reply_by_friend'=>1, 'in_reply_to_post_id'=>14914043658, 'is_geo_encoded'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>20, 'post_id'=>'13259110570', 'network'=>'twitter',
        'author_user_id'=>'127567137', 'author_username'=>'ekanshpreet', 'post_text'=>'im here finally ;)....',
        'location'=>'New Delhi', 'place'=>'over_query_limit', 'geo'=>NULL, 'is_geo_encoded'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>21, 'post_id'=>'15645301636', 'network'=>'twitter',
        'author_user_id'=>'127567137', 'author_username'=>'ekanshpreet', 'post_text'=>'thinking....',
        'location'=>'New Delhi', 'place'=>NULL, 'geo'=>'28.602815 77.049136', 'is_geo_encoded'=>0));

        $builders[] = FixtureBuilder::build('posts', array('id'=>22, 'post_id'=>'11331235880', 'network'=>'twitter',
        'author_user_id'=>'127567137', 'author_username'=>'ekanshpreet', 'post_text'=>':)', 'location'=>'New Delhi',
        'geo'=>'28.60abc2815 77.049136', 'is_geo_encoded'=>0));

        return $builders;
    }
}
