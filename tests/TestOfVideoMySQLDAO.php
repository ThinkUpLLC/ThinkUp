<?php
/**
 *
 * ThinkUp/tests/TestOfVideoMySQLDAO.php
 *
 * Copyright (c) 2009-2013 Aaron Kalair
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
 *
 * Test of VideoMySQL DAO implementation
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Aaron Kalair
 * @author Aaron Kalair <aaronkalair[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';


class TestOfVideoMySQLDAO extends ThinkUpUnitTestCase {

    /**
     * @var VideoMySQLDAO
     */
    protected $dao;

    public function setUp() {
        parent::setUp();
        $this->dao = new VideoMySQLDAO();
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    protected function buildData() {
        $builders = array();
        // Add a video to the database
        $builders[] = FixtureBuilder::build('posts', array(
            'id' => 1,
            'post_id' => 'tfgdf4es',
            'author_user_id' => '115383827382290096528',
            'author_username' => 'Aaron Kalair',
            'author_fullname' => 'Aaron Kalair',
            'author_avatar' => 'avatar.jpg',
            'post_text' => 'A random YouTube video',
            'source' => '',
            'pub_date' => '2013-03-05 16:45:00',
            'reply_count_cache' => 204,
            'network' => 'youtube',
            'is_protected'=>0
            ));
        $builders[] = FixtureBuilder::build('videos', array(
            'post_key' => 1,
            'description' => 'This is a youtube video ',
            'likes' => 10,
            'dislikes' => 20,
            'views' => 5452,
            'minutes_watched' => 125,
            'average_view_duration' => 50000124,
            'average_view_percentage' => 41,
            'favorites_added' => 204,
            'favorites_removed' => 52,
            'shares'=>0,
            'subscribers_gained' =>0,
            'subscribers_lost' => 2,

            ));
        return $builders;
    }

    /**
     * Test constructor
     */
    public function testConstructor() {
        $dao = new VideoMySQLDAO();
        $this->assertTrue(isset($dao));
    }

    public function testAddPost() {
        $video_attributes['description'] = "Watch my youtube video";
        $video_attributes['post_text'] = "My Great Video";
        $video_attributes['likes'] = 200;
        $video_attributes['dislikes'] = 25;
        $video_attributes['views'] = 54465;
        $video_attributes['pub_date'] = '2013-12-12 12:01:54';
        $video_attributes['post_id'] = 'G7fdAh4';
        $video_attributes['location'] = 'England';
        $video_attributes['place'] = 'England';
        $video_attributes['geo'] = '54.342, -5.65';
        $video_attributes['reply_count_cache'] = 20;
        $video_attributes['is_protected'] = 0;
        $video_attributes['favorites_added'] = 12;
        $video_attributes['favorites_removed'] = 2;
        $video_attributes['shares'] = 52;
        $video_attributes['subscribers_gained'] = 59;
        $video_attributes['subscribers_lost'] = 1;
        $video_attributes['minutes_watched'] = 850;
        $video_attributes['average_view_duration'] = 50000;
        $video_attributes['average_view_percentage'] = 25;
        $video_attributes['author_user_id'] = '4544554545710124';
        $video_attributes['author_username'] = 'Aaron Kalair';
        $video_attributes['author_fullname'] = 'Aaron Kalair';
        $video_attributes['author_avatar'] = 'http://www.google.com/avatars/mgfgfd';
        $video_attributes['source'] = '';
        $video_attributes['network'] = 'youtube';

        $this->dao->addVideo($video_attributes);

        // Check all the values were added
        $video = $this->dao->getVideoByID('G7fdAh4', 'youtube');
        $this->assertTrue(isset($video));
        $this->assertEqual($video->post_id, 'G7fdAh4');
        $this->assertEqual($video->description, 'Watch my youtube video');
        $this->assertEqual($video->post_text, 'My Great Video');
        $this->assertEqual($video->likes, 200);
        $this->assertEqual($video->dislikes, 25);
        $this->assertEqual($video->views, 54465);
        $this->assertEqual($video->pub_date, '2013-12-12 12:01:54');
        $this->assertEqual($video->location, 'England');
        $this->assertEqual($video->place, 'England');
        $this->assertEqual($video->geo, '54.342, -5.65');
        $this->assertEqual($video->reply_count_cache, 20);
        $this->assertEqual($video->is_protected, 0);
        $this->assertEqual($video->favorites_added, 12);
        $this->assertEqual($video->favorites_removed, 2);
        $this->assertEqual($video->shares, 52);
        $this->assertEqual($video->subscribers_gained, 59);
        $this->assertEqual($video->subscribers_lost, 1);
        $this->assertEqual($video->minutes_watched, 850);
        $this->assertEqual($video->average_view_duration, 50000);
        $this->assertEqual($video->average_view_percentage, 25);
        $this->assertEqual($video->author_user_id, '4544554545710124');
        $this->assertEqual($video->author_username, 'Aaron Kalair');
        $this->assertEqual($video->author_fullname, 'Aaron Kalair');
        $this->assertEqual($video->author_avatar, 'http://www.google.com/avatars/mgfgfd');
        $this->assertEqual($video->source, '');
        $this->assertEqual($video->network, 'youtube');
    }

    public function testGetVideoByIDExists() {
        $this->builders = self::buildData();
        $video = $this->dao->getVideoByID('tfgdf4es', 'youtube');
        $this->assertTrue(isset($video));
        $this->assertEqual($video->post_id, 'tfgdf4es');
        $this->assertEqual($video->description, 'This is a youtube video ');
        $this->assertEqual($video->post_text, 'A random YouTube video');
        $this->assertEqual($video->likes, 10);
        $this->assertEqual($video->dislikes, 20);
        $this->assertEqual($video->views, 5452);
        $this->assertEqual($video->pub_date, '2013-03-05 16:45:00');
        $this->assertEqual($video->reply_count_cache, 204);
        $this->assertEqual($video->is_protected, 0);
        $this->assertEqual($video->favorites_added, 204);
        $this->assertEqual($video->favorites_removed, 52);
        $this->assertEqual($video->shares, 0);
        $this->assertEqual($video->subscribers_gained, 0);
        $this->assertEqual($video->subscribers_lost, 2);
        $this->assertEqual($video->minutes_watched, 125);
        $this->assertEqual($video->average_view_duration, 50000124);
        $this->assertEqual($video->average_view_percentage, 41);
        $this->assertEqual($video->author_user_id, '115383827382290096528');
        $this->assertEqual($video->author_username, 'Aaron Kalair');
        $this->assertEqual($video->author_fullname, 'Aaron Kalair');
        $this->assertEqual($video->author_avatar, 'avatar.jpg');
        $this->assertEqual($video->source, '');
        $this->assertEqual($video->network, 'youtube');
    }

    public function testGetVideoByIDDoesNotExist() {
        $video = $this->dao->getVideoByID(100000001, 'youtube');
        $this->assertTrue(!isset($video));
    }

    public function testUpdateVideoCounts() {
        $this->builders = self::buildData();
        $video_attributes['post_id'] = 'tfgdf4es';
        $video_attributes['likes'] = 25;
        $video_attributes['dislikes'] = 40;
        $video_attributes['views'] = 8545;
        $video_attributes['favorites_added'] = 500;
        $video_attributes['favorites_removed'] = 245;
        $video_attributes['shares'] = 52;
        $video_attributes['subscribers_gained'] = 40;
        $video_attributes['subscribers_lost'] = 100;
        $video_attributes['minutes_watched'] = 824;
        $video_attributes['average_view_duration'] = 50004110;
        $video_attributes['average_view_percentage'] = 65;
        // Update the videos values
        $this->assertEqual($this->dao->updateVideoCounts($video_attributes), 1);

        // Check they were updated
        $video = $this->dao->getVideoByID('tfgdf4es', 'youtube');
        $this->assertEqual($video->likes, 25);
        $this->assertEqual($video->dislikes, 40);
        $this->assertEqual($video->views, 8545);
        $this->assertEqual($video->favorites_added, 500);
        $this->assertEqual($video->favorites_removed, 245);
        $this->assertEqual($video->shares, 52);
        $this->assertEqual($video->subscribers_gained, 40);
        $this->assertEqual($video->subscribers_lost, 100);
        $this->assertEqual($video->minutes_watched, 824);
        $this->assertEqual($video->average_view_duration, 50004110);
        $this->assertEqual($video->average_view_percentage, 65);
    }

    public function testGetHotVideos() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1',
        'author_username'=>'ev', 'post_text'=>'My Great Video', 'pub_date'=>'-40d', 'network'=>'youtube'));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'likes'=>90, 'dislikes'=>10, 'average_view_percentage'=>10));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2',
        'author_username'=>'ev', 'post_text'=>'My Great Video 2', 'pub_date'=>'-2d', 'network'=>'youtube'));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'likes'=>50, 'dislikes'=>50, 'average_view_percentage'=>10));

        $post_builder3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3',
        'author_username'=>'ev', 'post_text'=>'My Great Video 3', 'pub_date'=>'-2d', 'network'=>'youtube'));
        $video_builder3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3',
        'likes'=>50, 'dislikes'=>50, 'average_view_percentage'=>10));

        $video_dao = DAOFactory::getDAO('VideoDAO');
        $result = $video_dao->getHotVideos('ev', 'youtube', 10, 'likes');
        $result2 = $video_dao->getHotVideos('ev', 'youtube', 10, 'likes', 'Likes');

        $this->assertEqual(sizeof($result), 3);
        $this->assertEqual($result[0]['likes'], 50);
        $this->assertEqual($result[1]['likes'], 50);
        $this->assertEqual($result[2]['likes'], 90);

        $this->assertEqual(sizeof($result2), 3);
        $this->assertEqual($result2[0]['Likes'], 50);
        $this->assertEqual($result2[1]['Likes'], 50);
        $this->assertEqual($result2[2]['Likes'], 90);
    }

    public function testGetAverageOfAverageViewPercentage() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1',
        'author_username'=>'ev', 'post_text'=>'My Great Video', 'pub_date'=>'-40d', 'network'=>'youtube'));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'average_view_percentage'=>50));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2',
        'author_username'=>'ev', 'post_text'=>'My Great Video 2', 'pub_date'=>'-2d', 'network'=>'youtube'));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'average_view_percentage'=>20));

        $video_dao = DAOFactory::getDAO('VideoDAO');
        $result = $video_dao->getAverageOfAverageViewPercentage('ev', 'youtube');
        $this->assertEqual($result, 35);

        $result2 = $video_dao->getAverageOfAverageViewPercentage('ev', 'youtube', 5);
        $this->assertEqual($result2, 20);
    }

    public function testGetAverageViewPercentageLow() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1',
        'author_username'=>'ev', 'post_text'=>'My Great Video', 'pub_date'=>'-40d', 'network'=>'youtube'));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'average_view_percentage'=>10));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2',
        'author_username'=>'ev', 'post_text'=>'My Great Video 2', 'pub_date'=>'-2d', 'network'=>'youtube'));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'average_view_percentage'=>20));

        $video_dao = DAOFactory::getDAO('VideoDAO');
        $result = $video_dao->getAverageViewPercentageLow('ev', 'youtube');
        $this->assertEqual($result, 10);

        $result2 = $video_dao->getAverageViewPercentageLow('ev', 'youtube', 5);
        $this->assertEqual($result2, 20);
    }

    public function testGetAverageViewPercentageHigh() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1',
        'author_username'=>'ev', 'post_text'=>'My Great Video', 'pub_date'=>'-40d', 'network'=>'youtube'));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'average_view_percentage'=>50));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2',
        'author_username'=>'ev', 'post_text'=>'My Great Video 2', 'pub_date'=>'-2d', 'network'=>'youtube'));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'average_view_percentage'=>20));

        $video_dao = DAOFactory::getDAO('VideoDAO');
        $result = $video_dao->getAverageViewPercentageHigh('ev', 'youtube');
        $this->assertEqual($result, 50);

        $result2 = $video_dao->getAverageViewPercentageHigh('ev', 'youtube', 5);
        $this->assertEqual($result2, 20);
    }

    public function testGetHighestLikes() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1',
        'author_username'=>'ev', 'post_text'=>'My Great Video', 'pub_date'=>'-40d', 'network'=>'youtube'));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'likes'=>90, 'dislikes'=>10));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2',
        'author_username'=>'ev', 'post_text'=>'My Great Video 2', 'pub_date'=>'-2d', 'network'=>'youtube'));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'likes'=>50, 'dislikes'=>50));

        $video_dao = DAOFactory::getDAO('VideoDAO');
        $result = $video_dao->getHighestLikes('ev', 'youtube');
        $this->assertEqual($result, 90);

        $result2 = $video_dao->getHighestLikes('ev', 'youtube', 5, date('Y-m-d'));
        $this->assertEqual($result2, 50);
    }

    public function testGetAverageLikeCount() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1',
        'author_username'=>'ev', 'post_text'=>'My Great Video', 'pub_date'=>'-40d', 'network'=>'youtube'));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'likes'=>90, 'dislikes'=>10));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2',
        'author_username'=>'ev', 'post_text'=>'My Great Video 2', 'pub_date'=>'-2d', 'network'=>'youtube'));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'likes'=>50, 'dislikes'=>50));

        $video_dao = DAOFactory::getDAO('VideoDAO');
        $result = $video_dao->getAverageLikeCount('ev', 'youtube');
        $this->assertEqual($result, 70);

        $result2 = $video_dao->getAverageLikeCount('ev', 'youtube', 5);
        $this->assertEqual($result2, 50);
    }

    public function testDoesUserHaveVideosWithLikesSinceDate() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1',
        'author_username'=>'ev', 'post_text'=>'My Great Video', 'pub_date'=>'-40d', 'network'=>'youtube',
        'in_reply_to_user_id'=>null, 'in_reply_to_post_id'=>null));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'likes'=>90, 'dislikes'=>10));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2',
        'author_username'=>'ev', 'post_text'=>'My Great Video 2', 'pub_date'=>'-2d', 'network'=>'youtube',
        'in_reply_to_user_id'=>null, 'in_reply_to_post_id'=>null));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'likes'=>50, 'dislikes'=>50));

        $video_dao = DAOFactory::getDAO('VideoDAO');
        $result = $video_dao->doesUserHaveVideosWithLikesSinceDate('ev', 'youtube', 10, null);
        $this->assertTrue($result);

        $result2 = $video_dao->doesUserHaveVideosWithLikesSinceDate('ev', 'youtube', 5,
        date('Y-m-d', strtotime('+200 days')));
        $this->assertFalse($result2);
    }

    public function testGetHighestViews() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-41d', 'network'=>'youtube'));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'views'=>4,
        'average_view_percentage'=>54.6));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-1d', 'network'=>'youtube' ));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'views'=>2,
        'average_view_percentage'=>54.6));

        $video_dao = DAOFactory::getDAO('VideoDAO');
        $result = $video_dao->getHighestViews('ev', 'youtube');
        $this->assertEqual($result, 4);

        $result2 = $video_dao->getHighestViews('ev', 'youtube', 5);
        $this->assertEqual($result2, 2);
    }

    public function testGetAverageViews() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-41d', 'network'=>'youtube'));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'views'=>4,
        'average_view_percentage'=>54.6));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-1d', 'network'=>'youtube' ));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'views'=>2,
        'average_view_percentage'=>54.6));

        $video_dao = DAOFactory::getDAO('VideoDAO');
        $result = $video_dao->getAverageViews('ev', 'youtube');
        $this->assertEqual($result, 3);

        $result2 = $video_dao->getAverageViews('ev', 'youtube', 5);
        $this->assertEqual($result2, 2);
    }

    public function testDoesUserHaveVideosWithViewsSinceDate() {
        $video_dao = DAOFactory::getDAO('VideoDAO');
        $result = $video_dao->doesUserHaveVideosWithViewsSinceDate('user', 'youtube', 7);
        $this->assertFalse($result);

        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1', 'author_username'=>'ev',
        'post_text'=>'My Great Video', 'pub_date'=>'-3d', 'network'=>'youtube', 'in_reply_to_post_id'=>null,
        'in_reply_to_user_id'=>null));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1', 'views'=>4,
        'average_view_percentage'=>54.6));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2', 'author_username'=>'ev',
        'post_text'=>'My Great Video 2', 'pub_date'=>'-5d', 'network'=>'youtube', 'in_reply_to_post_id'=>null,
        'in_reply_to_user_id'=>null));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2', 'views'=>2,
        'average_view_percentage'=>54.6));

        // They do have replies from within 30 days
        $result = $video_dao->doesUserHaveVideosWithViewsSinceDate('ev', 'youtube', 30);
        $this->assertTrue($result);
        // Set date to some time 30+ days in the future and were guaranteed to have no replies since then
        $result = $video_dao->doesUserHaveVideosWithViewsSinceDate('ev', 'youtube', 30,
        date('Y-m-d', strtotime('+31 days')));
        $this->assertFalse($result);
    }

    public function testGetNetSubscriberChange() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1',
        'author_username'=>'ev', 'post_text'=>'My Great Video', 'pub_date'=>'-40d', 'network'=>'youtube'));
        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'subscribers_gained'=>10, 'subscribers_lost'=>5));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2',
        'author_username'=>'ev', 'post_text'=>'My Great Video 2', 'pub_date'=>'-2d', 'network'=>'youtube'));
        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'subscribers_gained'=>5, 'subscribers_lost'=>10));

        $post_builder3 = FixtureBuilder::build('posts', array('id'=>3, 'post_id'=>'3',
        'author_username'=>'ev', 'post_text'=>'My Great Video 3', 'pub_date'=>'-2d', 'network'=>'youtube'));
        $video_builder3 = FixtureBuilder::build('videos', array('id'=>3, 'post_key'=>'3',
        'subscribers_gained'=>20, 'subscribers_lost'=>5));

        $video_dao = new VideoMySQLDAO();
        $result = $video_dao->getNetSubscriberChange('ev', 'youtube', 3);
        $this->assertEqual(sizeof($result), 3);
        $this->assertEqual($result[0]['Subscriber Change'], 5);
        $this->assertEqual($result[1]['Subscriber Change'], -5);
        $this->assertEqual($result[2]['Subscriber Change'], 15);
    }

    public function testGetAverageMinutesViewed() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1',
        'author_user_id'=>'1', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'My Great Video',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'youtube', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));

        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'minutes_watched'=>2, 'title'=>'The first video', 'average_view_percentage'=>65.4));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2',
        'author_user_id'=>'1', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'My Great Video 2',
        'source'=>'web', 'pub_date'=>'-40d', 'reply_count_cache'=>0, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'youtube', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));

        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'minutes_watched'=>4, 'title'=>'The second video', 'average_view_percentage'=>65.4));

        $video_dao = DAOFactory::getDAO('VideoDAO');
        $result = $video_dao->getAverageMinutesViewed('ev', 'youtube');
        $this->assertEqual($result, 3);

        $result2 = $video_dao->getAverageMinutesViewed('ev', 'youtube', 5);
        $this->assertEqual($result2, 2);
    }

    public function testGetHighestMinutesViewed() {
        $post_builder = FixtureBuilder::build('posts', array('id'=>1, 'post_id'=>'1',
        'author_user_id'=>'1', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'My Great Video',
        'source'=>'web', 'pub_date'=>'-1d', 'reply_count_cache'=>0, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'youtube', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));

        $video_builder = FixtureBuilder::build('videos', array('id'=>1, 'post_key'=>'1',
        'minutes_watched'=>2, 'title'=>'The first video', 'average_view_percentage'=>65.4));

        $post_builder2 = FixtureBuilder::build('posts', array('id'=>2, 'post_id'=>'2',
        'author_user_id'=>'1', 'author_username'=>'ev', 'author_fullname'=>'Ev Williams',
        'author_avatar'=>'avatar.jpg', 'post_text'=>'My Great Video 2',
        'source'=>'web', 'pub_date'=>'-40d', 'reply_count_cache'=>0, 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'youtube', 'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));

        $video_builder2 = FixtureBuilder::build('videos', array('id'=>2, 'post_key'=>'2',
        'minutes_watched'=>4, 'title'=>'The second video', 'average_view_percentage'=>65.4));

        $video_dao = DAOFactory::getDAO('VideoDAO');
        $result = $video_dao->getHighestMinutesViewed('ev', 'youtube');
        $this->assertEqual($result, 4);

        $result2 = $video_dao->getHighestMinutesViewed('ev', 'youtube', 5);
        $this->assertEqual($result2, 2);
    }
}
