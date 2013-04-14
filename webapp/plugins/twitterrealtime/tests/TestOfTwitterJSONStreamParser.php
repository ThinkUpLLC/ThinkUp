<?php
/**
 * ThinkUp/webapp/plugins/twitterrealtime/tests/TestOfTwitterJSONStreamParser.php
 *
 * Copyright (c) 2011-2013 Amy Unruh
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
 * Test of TestOfTwitterJSONStreamParser
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013  Amy Unruh
 * @author Amy Unruh
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';

require_once THINKUP_ROOT_PATH.'tests/classes/class.ThinkUpUnitTestCase.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/model/class.TwitterJSONStreamParser.php';

class TestOfTwitterJSONStreamParser extends ThinkUpUnitTestCase {
    /**
     * @var CrawlerTwitterAPIAccessorOAuth API accessor object
     */
    var $api;
    /**
     *
     * @var Instance
     */
    var $instance;
    /**
     *
     * @var Logger
     */
    var $logger;
    /**
     * @var string
     */
    var $test_dir;
    /**
     * var TwitterJSONStreamParser
     */
    var $json_parser;
    /**
     * @var PostDAO
     */
    var $post_dao;
    /**
     * @var FavoriteDAO
     */
    var $favs_dao;
    /**
     * @var UserDAO
     */
    var $user_dao;
    /**
     * @var PlaceDAO
     */
    var $place_dao;
    /**
     * @var MentionDAO
     */
    var $mention_dao;
    /**
     * @var HashtagDAO
     */
    var $hashtag_dao;
    /**
     * @var HashtagPostDAO
     */
    var $hashtagpost_dao;

    public function setUp() {
        parent::setUp();

        $this->test_dir = THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/tests/testdata/';
        $this->json_parser = new TwitterJSONStreamParser();

        $this->logger = Logger::getInstance('stream_log_location');
        $this->post_dao = DAOFactory::getDAO('PostDAO');
        $this->post_dao->setLoggerInstance($this->logger);
        $this->favs_dao = DAOFactory::getDAO('FavoritePostDAO');
        $this->favs_dao->setLoggerInstance($this->logger);
        $this->user_dao = DAOFactory::getDAO('UserDAO');
        $this->user_dao->setLoggerInstance($this->logger);
        $this->place_dao = DAOFactory::getDAO('PlaceDAO');
        $this->place_dao->setLoggerInstance($this->logger);
        $this->mention_dao = DAOFactory::getDAO('MentionDAO');
        $this->mention_dao->setLoggerInstance($this->logger);
        $this->hashtag_dao = DAOFactory::getDAO('HashtagDAO');
        $this->hashtagpost_dao = DAOFactory::getDAO('HashtagPostDAO');
        $this->hashtag_dao->setLoggerInstance($this->logger);
    }

    public function tearDown() {
        parent::tearDown();
    }

    private function getJSONStringFromFile($fname) {
        return file_get_contents($this->test_dir . $fname);
    }

    public function testPostHandling() {
        $item = $this->getJSONStringFromFile("s1.json");
        $this->json_parser->parseJSON($item);
        $post = $this->post_dao->getPost('36480893434609664', 'twitter');
        $this->assertEqual($post->post_id, '36480893434609664');
        $user = $this->user_dao->getDetails(586, 'twitter');
        $this->assertEqual($user->user_id, 586);
    }

    public function testDeleteHandling() {
        /*
         * post to delete
         * {"delete":{"status":{"user_id_str":"972651","id_str":"36558145308332032","id":36558145308332032,
         * "user_id":972651}}}
         */
        $builder1 = FixtureBuilder::build('posts', array('post_id' => '36558145308332032', 'network' => 'twitter'));

        $item = $this->getJSONStringFromFile("delete.json");
        $this->json_parser->parseJSON($item);

        $post = $this->post_dao->getPost('36558145308332032', 'twitter');
        $this->assertNull($post);
    }

    /**
     * 1 rt
     */
    public function testRT() {
        $item = $this->getJSONStringFromFile("retweet1.json");
        $this->json_parser->parseJSON($item);
        // now test that both users have been added
        $user = $this->user_dao->getDetails(19202541, 'twitter');
        $this->assertEqual($user->user_id, 19202541);
        $user = $this->user_dao->getDetails(17567533, 'twitter');
        $this->assertEqual($user->user_id, 17567533);

        // check post RT count
        $post = $this->post_dao->getPost('36479682404687872', 'twitter');
        $this->assertEqual($post->retweet_count_api, 1);
    }

    /**
     * over 100 rts
     */
    public function testRTManyRTs() {
        $item = $this->getJSONStringFromFile("retweet2.json");
        $this->json_parser->parseJSON($item);
        // now test that both users have been added
        $user = $this->user_dao->getDetails(2768241, 'twitter');
        $this->assertEqual($user->user_id, 2768241);
        $this->assertEqual($user->username, 'amygdala');
        $user = $this->user_dao->getDetails(3827771, 'twitter');
        $this->assertEqual($user->user_id, 3827771);
        $this->assertEqual($user->username, 'joshuamneff');

        $post = $this->post_dao->getPost('36564758891073536', 'twitter');
        $this->assertEqual($post->old_retweet_count_cache, 0);
        $this->assertEqual($post->in_retweet_of_post_id, '36189093822074880');
        $this->assertEqual($post->in_rt_of_user_id, 3827771);

        // check post RT count
        $post = $this->post_dao->getPost('36189093822074880', 'twitter');
        $this->assertEqual($post->retweet_count_api, 100);
    }

    public function testOldStyleRT() {
        $item = $this->getJSONStringFromFile("old_style_rt.json");
        $this->json_parser->parseJSON($item);
        $user = $this->user_dao->getDetails(13524182, 'twitter');
        $this->assertEqual($user->user_id, 13524182);
        $post = $this->post_dao->getPost('36110238474047490', 'twitter');
        $this->assertEqual($post->in_rt_of_user_id, 15196372);
        $this->assertEqual($post->in_retweet_of_post_id, null);
    }

    public function testReply(){
        $item = $this->getJSONStringFromFile("s1.json");
        $this->json_parser->parseJSON($item);
        $item = $this->getJSONStringFromFile("reply_to_stored.json");
        $this->json_parser->parseJSON($item);
        // now test that both users have been added
        $user = $this->user_dao->getDetails(586, 'twitter');
        $this->assertEqual($user->user_id, 586);
        $user = $this->user_dao->getDetails(1717291, 'twitter');
        $this->assertEqual($user->user_id, 1717291);

        $post = $this->post_dao->getPost('36480893434609664', 'twitter');
        $this->assertEqual($post->reply_count_cache, 1);
        $this->assertEqual($post->retweet_count_api, 0);
        $post = $this->post_dao->getPost('36481023361421312', 'twitter');
        $this->assertEqual($post->reply_count_cache, 0);
        $this->assertEqual($post->retweet_count_api, 0);
    }

    public function testReplyOrigNotStored() {
        $item = $this->getJSONStringFromFile("reply_to_unstored.json");
        $this->json_parser->parseJSON($item);
        $post = $this->post_dao->getPost('36482330163945472', 'twitter');
        $this->assertEqual($post->in_reply_to_post_id, '36465132594794497');
        $this->assertEqual($post->in_reply_to_user_id, 14834340);
    }

    public function testMentions() {
        $item = $this->getJSONStringFromFile("mentions.json");
        $this->json_parser->parseJSON($item);
        $res = $this->mention_dao->getMentionInfoUserName("vaughanbell");
        $this->assertEqual($res['count_cache'], 1);
        $item = $this->getJSONStringFromFile("mentions2.json");
        $this->json_parser->parseJSON($item);
        $res = $this->mention_dao->getMentionInfoUserName("vaughanbell");
        $this->assertEqual($res['count_cache'], 2);
        $res = $this->mention_dao->getMentionInfoUserID(20542737);
        $this->assertEqual($res['count_cache'], 2);
        $res = $this->mention_dao->getMentionsForPost('36839419868614656');
        $this->assertEqual(sizeof($res),4);
        $res = $this->mention_dao->getMentionsForPost('36803537182662657');
        $this->assertEqual(sizeof($res),4);
    }

    public function testHashtagsMultPostsOfSame() {
        $item = $this->getJSONStringFromFile("hashtags3.json");
        $this->json_parser->parseJSON($item);
        $item = $this->getJSONStringFromFile("hashtags4.json");
        $this->json_parser->parseJSON($item);
        $res = $this->hashtag_dao->getHashtag("egypt", 'twitter');
        $this->assertEqual($res->count_cache, 2);
        $res = $this->hashtagpost_dao->getHashtagsForPost('36111321078439936', 'twitter');
        $this->assertEqual(sizeof($res), 2);
    }

    public function testHashtagsMult() {
        $item = $this->getJSONStringFromFile("hashtags.json");
        $this->json_parser->parseJSON($item);
        $res = $this->hashtag_dao->getHashtag("kanban", 'twitter');
        $this->assertEqual($res->count_cache, 2);
        $res = $this->hashtag_dao->getHashtag("scrum", 'twitter');
        $this->assertEqual($res->count_cache, 2);
        // the original post
        $res = $this->hashtagpost_dao->getHashtagsForPost('36601038500925440', 'twitter');
        $this->assertEqual(sizeof($res), 8);
        $res = $this->hashtagpost_dao->getHashtagsForPost('36601109074157568', 'twitter');
        // the retweet drops one hashtag, truncates another...
        $this->assertEqual(sizeof($res), 7);
    }

    public function testHashtagsMultInSamePost() {
        // heh
        $item = $this->getJSONStringFromFile("hashtags2.json");
        $this->json_parser->parseJSON($item);
        // this will check case-insensitivity also
        $res = $this->hashtag_dao->getHashtag("ribs", 'twitter');
        $this->assertEqual($res->count_cache, 1);
        $res = $this->hashtagpost_dao->getHashtagsForPost('36555750721458176', 'twitter');
        $this->assertEqual(sizeof($res), 1);
    }

    /**
     * check the case where there are > 1 urls in the entity information associated with the post.
     */
    public function testMultURLs() {
        $item = $this->getJSONStringFromFile("urls1.json");
        $this->json_parser->parseJSON($item);
        $link_dao = DAOFactory::getDAO('LinkDAO');
        $link_dao->setLoggerInstance($this->logger);
        $links = $link_dao->getLinksForPost('36571640280125440');
        $this->assertEqual(sizeof($links), 2);
        $this->assertEqual($links[0]->url, 'http://tinyurl.com/5svx3w5');
        $this->assertEqual($links[1]->url, 'http://say.ly/aUK8DW');
    }

    public function testSingleURL() {
        $item = $this->getJSONStringFromFile("urls2.json");
        $this->json_parser->parseJSON($item);
        $link_dao = DAOFactory::getDAO('LinkDAO');
        $link_dao->setLoggerInstance($this->logger);
        $links = $link_dao->getLinksForPost('36571652540071936');
        $this->assertEqual(sizeof($links), 1);
    }

    // the 'place information can include a polygon, a point, or both.
    // This information goes into diff tables.

    public function testPlacePolygonPointCoords() {
        $item = $this->getJSONStringFromFile("place1.json");
        $this->json_parser->parseJSON($item);

        // check post information
        $post = $this->post_dao->getPost('36573081459757056', 'twitter');
        $this->assertEqual($post->post_id, '36573081459757056');
        // check places table information
        $place_array = $this->place_dao->getPlaceByID('f4377e058bd5e6b0');
        $this->assertEqual($place_array['bounding_box'],
            "POLYGON((-97.78447692 30.24380799,-97.75398312 30.24380799,-97.75398312 30.26775771," .
            "-97.78447692 30.26775771,-97.78447692 30.24380799))");
        $this->assertEqual($place_array['place_id'], 'f4377e058bd5e6b0');
        // check post_locations table information
        $post_loc_arr = $this->place_dao->getPostPlace('36573081459757056');
        $this->assertEqual($post_loc_arr['post_id'], '36573081459757056');
        $this->assertEqual($post_loc_arr['place_id'], 'f4377e058bd5e6b0');

        $link_dao = new LinkMySQLDAO();
        $result = $link_dao->getLinkByUrl('http://twitpic.com/3z50uy');
        $this->assertIsA($result, "Link");
        $this->assertEqual($result->url, 'http://twitpic.com/3z50uy');
        $this->assertEqual($result->expanded_url, 'http://twitpic.com/3z50uy');
        $this->assertEqual($result->image_src, 'http://twitpic.com/show/thumb/3z50uy');
    }

    public function testPlacePolygon() {
        $item = $this->getJSONStringFromFile("place2.json");
        $this->json_parser->parseJSON($item);

        // check post information
        $post = $this->post_dao->getPost('36855908965294080', 'twitter');
        $this->assertEqual($post->post_id, '36855908965294080');
        // check places table information
        $place_array = $this->place_dao->getPlaceByID('213d0db04acb674d');
        $this->assertEqual($place_array['bounding_box'],
            "POLYGON((-120.301592 39.31637,-120.077464 39.31637,-120.077464 39.387325,-120.301592 " .
            "39.387325,-120.301592 39.31637))");
        $this->assertEqual($place_array['place_id'], '213d0db04acb674d');
        // should be no point coord info
        $post_loc_arr = $this->place_dao->getPostPlace('36855908965294080');
        $this->assertEqual($post_loc_arr, null);
    }

    public function testPointCoordsNoPolygon() {
        $item = $this->getJSONStringFromFile("place3.json");
        $this->json_parser->parseJSON($item);

        // check post information
        $post = $this->post_dao->getPost('36358312584806400', 'twitter');
        $this->assertEqual($post->post_id, '36358312584806400');
        // should be no places info
        // check post_locations table information
        $post_loc_arr = $this->place_dao->getPostPlace('36358312584806400');
        $this->assertEqual($post_loc_arr['post_id'], '36358312584806400');
        $this->assertEqual($post_loc_arr['place_id'], null);
        $this->assertEqual($post_loc_arr['longlat'], 'POINT(139.722 35.6596)');
    }

    public function testFavOfOwnerPost() {

        $item = $this->getJSONStringFromFile("fav_of_owner_post.json");
        $this->json_parser->parseJSON($item);

        $favds = $this->favs_dao->getUsersWhoFavedPost('34978953638711297');
        $this->assertEqual(sizeof($favds), 1);
        // test user added
        $user = $this->user_dao->getDetails(201709909, 'twitter');
        $this->assertEqual($user->user_id, 201709909);
    }

    public function testFavByOwner() {

        $item = $this->getJSONStringFromFile("fav_by_owner.json");
        $this->json_parser->parseJSON($item);
        $posts = $this->favs_dao->getAllFavoritePosts(2768241, 'twitter', 10);
        $this->assertEqual(sizeof($posts), 1);
        // test users added
        $user = $this->user_dao->getDetails(9207632, 'twitter');
        $this->assertEqual($user->user_id, 9207632);
        $user = $this->user_dao->getDetails(2768241, 'twitter');
        $this->assertEqual($user->user_id, 2768241);
    }

    /**
     * non-parseable data (non-JSON) or empty string
     */
    public function testNonParsedData() {
        $retval = $this->json_parser->parseJSON("");
        $this->assertEqual($retval, false);
        $retval = $this->json_parser->parseJSON("xyz");
        $this->assertEqual($retval, false);
    }

    /**
     * JSON, but not the expected schema
     */
    public function testNonSchemaJSON() {
        $item = '{"a":1, "b": 2, "c": "hi"}';
        $retval = $this->json_parser->parseJSON($item);
        $this->assertEqual($retval, false);
    }

    /**
     * for some of the events, no action is currently taken (aside from maybe information to stdout).
     * just check this case is handled gracefully.
     */
    public function testNonHandledEvent() {
        $item = $this->getJSONStringFromFile("follow_event.json");
        $retval = $this->json_parser->parseJSON($item);
        $this->assertEqual($retval, true);
    }
}
