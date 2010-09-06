<?php
if (!isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS) {
    require_once '../../../../tests/config.tests.inc.php';
}
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';

require_once THINKUP_ROOT_PATH.'webapp/plugins/geoencoder/model/class.GeoEncoderPlugin.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/geoencoder/tests/classes/mock.GeoEncoderCrawler.php';

/**
 * Test of GeoEncoder ThinkUp plugin
 *
 * @author Ekansh Preet Singh <ekanshpreet[at]gmail[dot]com>
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
class TestOfGeoEncoderPlugin extends ThinkUpUnitTestCase {

    public function __construct() {
        $this->UnitTestCase('GeoEncoder plugin class test');
    }

    public function setUp() {
        parent::setUp();
        $crawler = Crawler::getInstance();
        $crawler->registerCrawlerPlugin('GeoEncoderPlugin');
    }

    function tearDown() {
        parent::tearDown();
    }

    function testGeoEncoderCrawl() {
        $builders = $this->buildData();
        
        $_SESSION['user'] = 'admin@example.com';
        $crawler = Crawler::getInstance();
        $crawler->crawl();

        //the crawler closes the log so we have to re-open it
        $logger = Logger::getInstance();
        $pdao = DAOFactory::getDAO('PostDAO');
        $ldao = DAOFactory::getDAO('LocationDAO');

        // Test 1: Checking Post for Successful Reverse Geoencoding
        $this->assertTrue($pdao->isPostInDB(15645300636, 'twitter'));

        $post = $pdao->getPost(15645300636, 'twitter');
        $this->assertEqual($post->is_geo_encoded, 1);
        $this->assertEqual($post->geo, '28.602815,77.049136');
        $this->assertEqual($post->location, 'Sector 4, New Delhi, Haryana, India');
        // Since this is just a post, reply_retweet_distance is 0
        $this->assertEqual($post->reply_retweet_distance, 0);

        // Test 2: Checking Post for successful Reverse Geoencoding
        $post = $pdao->getPost(15219161227, 'twitter');
        $this->assertEqual($post->is_geo_encoded, 1);
        $this->assertEqual($post->geo, '28.56213,77.165297');
        $this->assertEqual($post->location, 'Vasant Vihar, Munirka, New Delhi, Delhi, India');

        // Test: Example of unsuccessful geoencoding resulting out of INVALID_REQUEST.
        // NOTE: Not a test case encountered in actual crawl
        $post = $pdao->getPost(15331235880, 'twitter');
        $this->assertEqual($post->is_geo_encoded, 5);
        $this->assertEqual($post->geo, '28.60abc2815 77.049136');

        // Test 1: Checking Post for successful Geoencoding using "place" field
        $post = $pdao->getPost(15052338902, 'twitter');
        $this->assertEqual($post->is_geo_encoded, 1);
        $this->assertEqual($post->geo,'28.6889398,77.1618859');
        $this->assertEqual($post->place, 'Sector 8, R.K. Puram, New Delhi');
        $this->assertEqual($post->location,
        'Keshav Puram Metro Station, Maharaja Nahar Singh Marg, New Delhi, Delhi, India');
        // Distance between main post and its reply (Geocoding Process)
        $this->assertEqual($post->reply_retweet_distance, 1161);

        // Test 2: Checking Post for successful Geoencoding using "place" field
        // This post is retrieved from tu_encoded_locations
        $post = $pdao->getPost(14914043658, 'twitter');
        $this->assertEqual($post->is_geo_encoded, 1);
        $this->assertEqual($post->place, 'Sector 8, R.K. Puram, New Delhi');
        $this->assertEqual($post->location,
        'Keshav Puram Metro Station, Maharaja Nahar Singh Marg, New Delhi, Delhi, India');
        // When reply is Not in DB, reply_retweet_distance is -1
        $this->assertFalse($pdao->isPostInDB(999999, 'twitter'));
        $this->assertEqual($post->reply_retweet_distance, -1);

        // Test 1: Checking Post for successful Geoencoding using "location" field (post had is_geo_encoded set to 3)
        $post = $pdao->getPost(15338041815, 'twitter');
        $this->assertEqual($post->geo, '19.017656,72.856178');
        $this->assertEqual($post->place, NULL);
        $this->assertEqual($post->location, 'Mumbai, Maharashtra, India');
        $this->assertEqual($post->is_geo_encoded, 1);

        // Test 2: Checking Post for successful Geoencoding using "location" field
        $post = $pdao->getPost(15344199472, 'twitter');
        $this->assertEqual($post->location, 'New Delhi, Delhi, India');
        $this->assertEqual($post->is_geo_encoded, 1);
        // Distance between Post and Retweet (Geocoding Process)
        $this->assertEqual($post->reply_retweet_distance, 18);

        // When all three fields are filled, <geo> is given the most preference
        $post = $pdao->getPost(11259110570, 'twitter');
        $this->assertEqual($post->geo, '28.56213,77.165297');
        $this->assertEqual($post->place, 'Sector 8, R.K. Puram, New Delhi');
        $this->assertEqual($post->location, 'Vasant Vihar, Munirka, New Delhi, Delhi, India');
        $this->assertEqual($post->is_geo_encoded, 1);
        // Distance between reply and post (Reverse Geocoding Process)
        $this->assertEqual($post->reply_retweet_distance, 14);

        // When only place and location are filled, <place> is given preference
        $post = $pdao->getPost(15052338902, 'twitter');
        $this->assertEqual($post->geo, '28.6889398,77.1618859');
        $this->assertEqual($post->place, 'Sector 8, R.K. Puram, New Delhi');
        $this->assertEqual($post->location,
        'Keshav Puram Metro Station, Maharaja Nahar Singh Marg, New Delhi, Delhi, India');
        $this->assertEqual($post->is_geo_encoded, 1);

        // Unsuccessful Geoencoding due to place field
        // NOTE: Not a test case encountered in real crawl
        $post = $pdao->getPost(14913946516, 'twitter');
        $this->assertEqual($post->geo, NULL);
        $this->assertEqual($post->place, 'abc');
        $this->assertEqual($post->location, 'New Delhi');
        $this->assertEqual($post->is_geo_encoded, 2);
        $this->assertEqual($post->reply_retweet_distance, 0);

        //Unsuccessful Geoencoding due to location field
        $post = $pdao->getPost(15268690400, 'twitter');
        $this->assertEqual($post->geo, NULL);
        $this->assertEqual($post->place, NULL);
        $this->assertEqual($post->location, 'abc');
        $this->assertEqual($post->is_geo_encoded, 2);

        //Unsuccessful Geoencoding due to location field resulting in INVALID_REQUEST
        $post = $pdao->getPost(15244973830, 'twitter');
        $this->assertEqual($post->location, 'Ü');
        $this->assertEqual($post->is_geo_encoded, 5);

        //Unsuccessful Geoencoding due to all three fields being empty
        $post = $pdao->getPost(15435434230, 'twitter');
        $this->assertEqual($post->geo, NULL);
        $this->assertEqual($post->place, NULL);
        $this->assertEqual($post->location, NULL);
        $this->assertEqual($post->is_geo_encoded, 6);

        //Reverse Geoencoding when latitude and longitude are found in location field instead of geo field
        $post = $pdao->getPost(13212618909, 'twitter');
        $this->assertEqual($post->geo, '40.681839,-73.983734');
        $this->assertEqual($post->place, NULL);
        $this->assertEqual($post->location, 'Boerum Hill, Brooklyn, NY, USA');
        $this->assertEqual($post->is_geo_encoded, 1);
        // Retweet Distance in case of Reverse Geocoding Process
        $this->assertEqual($post->reply_retweet_distance, 11760);

        //Unsuccessful Geoencoding due to REQUEST_DENIED
        $post = $pdao->getPost(12259110570, 'twitter');
        $this->assertEqual($post->place, 'request_denied');
        $this->assertEqual($post->is_geo_encoded, 4);

        //Unsuccessful Geoencoding due to OVER_QUERY_LIMIT
        $post = $pdao->getPost(13259110570, 'twitter');
        $this->assertEqual($post->place, 'over_query_limit');
        $this->assertEqual($post->is_geo_encoded, 3);

        //After reaching OVER_QUERY_LIMIT, next posts are not geoencoded
        $post = $pdao->getPost(15645301636, 'twitter');
        $this->assertEqual($post->is_geo_encoded, 0);
        $post = $pdao->getPost(11331235880, 'twitter');
        $this->assertEqual($post->is_geo_encoded, 0);

        // Check up filling of tu_encoded_locations table
        $locations = $ldao->getAllLocations();
        $this->assertEqual(count($locations), 6);
        $this->assertEqual($locations[0]['short_name'], "28.602815 77.049136");
        $this->assertEqual($locations[2]['short_name'], "Mumbai");
        $this->assertEqual($locations[5]['short_name'], "40.681839 -73.983734");
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

        // TODO Convert the inserts below to use FixtureBuilder

        //Insert test posts
        $q = "INSERT INTO tu_posts (id, post_id, author_user_id, author_username, ";
        $q .= "post_text, location, place, geo, is_geo_encoded) VALUES (1, 15645300636, 127567137, 'ekanshpreet', ";
        $q .= "'thinking....', 'New Delhi', NULL, '28.602815 77.049136', 0)";
        $this->db->exec($q);

        $q = "INSERT INTO tu_posts (id, post_id, author_user_id, author_username, ";
        $q .= "post_text, location, place, geo, is_geo_encoded) VALUES (2, 15435434230, 127567137, 'ekanshpreet', ";
        $q .= "'i think its working now :D...', NULL, NULL, NULL, 6)";
        $this->db->exec($q);

        $q = "INSERT INTO tu_posts (id, post_id, author_user_id, author_username, ";
        $q .= "post_text, location, place, geo, is_retweet_by_friend, in_retweet_of_post_id, is_geo_encoded) ";
        $q .= "VALUES (3, 15344199472, 127567137, 'ekanshpreet', 'lets try again ...', 'New Delhi', NULL, NULL, ";
        $q .= "1, 15645300636, 0)";
        $this->db->exec($q);

        $q = "INSERT INTO tu_posts (id, post_id, author_user_id, author_username, ";
        $q .= "post_text, location, place, geo, is_geo_encoded) VALUES (4, 15338041815, 127567137, 'ekanshpreet', ";
        $q .= "'howdy ???', 'Mumbai', NULL, NULL, 3)";
        $this->db->exec($q);

        $q = "INSERT INTO tu_posts (id, post_id, author_user_id, author_username, ";
        $q .= "post_text, location, place, geo, is_geo_encoded) VALUES (5, 15331235880, 127567137, 'ekanshpreet', ";
        $q .= "':)', 'New Delhi', NULL, '28.60abc2815 77.049136', 0)";
        $this->db->exec($q);

        $q = "INSERT INTO tu_posts (id, post_id, author_user_id, author_username, ";
        $q .= "post_text, location, place, geo, is_geo_encoded) VALUES (6, 15268690400, 127567137, 'ekanshpreet', ";
        $q .= "'hmm... lets c...', 'abc', NULL, NULL, 0)";
        $this->db->exec($q);

        $q = "INSERT INTO tu_posts (id, post_id, author_user_id, author_username, ";
        $q .= "post_text, location, place, geo, is_geo_encoded) VALUES (7, 15244973830, 127567137, 'ekanshpreet', ";
        $q .= "'hmmm....', 'Ü', NULL, NULL, 0)";
        $this->db->exec($q);

        $q = "INSERT INTO tu_posts (id, post_id, author_user_id, author_username, ";
        $q .= "post_text, location, place, geo, is_geo_encoded) VALUES (8, 15219161227, 127567137, 'ekanshpreet', ";
        $q .= "'RT @jerrybrito: New Podcast: Gina Trapani and Anil Dash on Expert Labs and ThinkUp ow.ly/17zfrX', ";
        $q .= "'New Delhi', NULL, '28.56213 77.165297', 0)";
        $this->db->exec($q);

        $q = "INSERT INTO tu_posts (id, post_id, author_user_id, author_username, ";
        $q .= "post_text, location, place, geo, is_reply_by_friend, in_reply_to_post_id, is_geo_encoded) VALUES ";
        $q .= "(12, 15052338902, 127567137, 'ekanshpreet', '@imnishantg thats the problem.... :P', 'New Delhi', ";
        $q .= "'Sector 8, R.K. Puram, New Delhi', NULL, 1, '15338041815', 0)";
        $this->db->exec($q);

        $q = "INSERT INTO tu_posts (id, post_id, author_user_id, author_username, ";
        $q .= "post_text, location, place, geo, is_reply_by_friend, in_reply_to_post_id, is_geo_encoded) VALUES ";
        $q .= "(13, 14914043658, 127567137, 'ekanshpreet', 'is done with exams !!!', 'New Delhi', ";
        $q .= "'Sector 8, R.K. Puram, New Delhi', NULL, 1, 999999, 0)";
        $this->db->exec($q);

        $q = "INSERT INTO tu_posts (id, post_id, author_user_id, author_username, ";
        $q .= "post_text, location, place, geo, is_geo_encoded) VALUES (14, 14913946516, 127567137, 'ekanshpreet', ";
        $q .= "'is done with exams !!! :-)', 'New Delhi', 'abc', NULL, 0)";
        $this->db->exec($q);

        $q = "INSERT INTO tu_posts (id, post_id, author_user_id, author_username, ";
        $q .= "post_text, location, place, geo, is_reply_by_friend, in_reply_to_post_id, is_geo_encoded) VALUES ";
        $q .= "(15, 11259110570, 127567137, 'ekanshpreet', 'im here finally ;)....', 'New Delhi', ";
        $q .= "'Sector 8, R.K. Puram, New Delhi', '28.56213 77.165297', 1, 14914043658, 0)";
        $this->db->exec($q);

        $q = "INSERT INTO tu_posts (id, post_id, author_user_id, author_username, ";
        $q .= "post_text, location, place, geo, is_geo_encoded) VALUES (16, 12259110570, 127567137, 'ekanshpreet', ";
        $q .= "'im here finally ;)....', 'New Delhi', 'request_denied', NULL, 0)";
        $this->db->exec($q);

        $q = "INSERT INTO tu_posts (id, post_id, author_user_id, author_username, ";
        $q .= "post_text, location, place, geo, is_retweet_by_friend, in_retweet_of_post_id, is_geo_encoded) ";
        $q .= "VALUES (18, 13212618909, 772673, 'mwilkie', 'Just watched chris corn cob a sheep.', 
        'iPhone: 40.681839,-73.983734', ";
        $q .= "NULL, NULL, 1, '11259110570', 0)";
        $this->db->exec($q);

        $q = "INSERT INTO tu_posts (id, post_id, author_user_id, author_username, ";
        $q .= "post_text, location, place, geo, is_reply_by_friend, in_reply_to_post_id, is_geo_encoded) VALUES ";
        $q .= "(19, 1231210570, 127567137, 'ekanshpreet', 'im here finally ;)....', 'New Delhi', ";
        $q .= "'Sector 8, R.K. Puram, New Delhi', '28.56213 77.165297', 1, 14914043658, 0)";
        $this->db->exec($q);

        $q = "INSERT INTO tu_posts (id, post_id, author_user_id, author_username, ";
        $q .= "post_text, location, place, geo, is_geo_encoded) VALUES (20, 13259110570, 127567137, 'ekanshpreet', ";
        $q .= "'im here finally ;)....', 'New Delhi', 'over_query_limit', NULL, 0)";
        $this->db->exec($q);

        $q = "INSERT INTO tu_posts (id, post_id, author_user_id, author_username, ";
        $q .= "post_text, location, place, geo, is_geo_encoded) VALUES (21, 15645301636, 127567137, 'ekanshpreet', ";
        $q .= "'thinking....', 'New Delhi', NULL, '28.602815 77.049136', 0)";
        $this->db->exec($q);

        $q = "INSERT INTO tu_posts (id, post_id, author_user_id, author_username, ";
        $q .= "post_text, location, place, geo, is_geo_encoded) VALUES (22, 11331235880, 127567137, 'ekanshpreet', ";
        $q .= "':)', 'New Delhi', NULL, '28.60abc2815 77.049136', 0)";
        $this->db->exec($q);
        
        return $builders;
    }
}