<?php
/**
 *
 * webapp/plugins/youtube/tests/TestOfYouTubeCrawler.php
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
 * Test of YouTube Crawler
 *
 * Tests the YouTube Crawler class
 *
 * Copyright (c) 2013 Aaron Kalair
 *
 * @author Aaron Kalair <aaronkalair[at]gmail[dot][com]>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Aaron Kalair
 */

require_once 'tests/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/youtube/controller/class.YouTubePluginConfigurationController.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/youtube/model/class.YouTubeCrawler.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/youtube/model/class.YouTubePlugin.php';
// Handle API queries locally
require_once THINKUP_ROOT_PATH.'webapp/plugins/youtube/tests/classes/mock.YouTubeAPIAccessor.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/googleplus/tests/classes/mock.GooglePlusAPIAccessor.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/youtube/tests/classes/mock.YouTubeAnalyticsAPIAccessor.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/youtube/tests/classes/mock.YouTubeAPIV2Accessor.php';

class TestOfYouTubeCrawler extends ThinkUpUnitTestCase {

    /**
     *
     * @var Instance
     */
    var $instance;

    public function setUp() {
        parent::setUp();
        $r = array('id'=>1, 'network_username'=>'Gina Trapani', 'network_user_id'=>'113612142759476883204',
        'network_viewer_id'=>'113612142759476883204', 'last_post_id'=>'0', 'last_page_fetched_replies'=>0,
        'last_page_fetched_tweets'=>'0', 'total_posts_in_system'=>'0', 'total_replies_in_system'=>'0',
        'total_follows_in_system'=>'0', 'is_archive_loaded_replies'=>'0',
        'is_archive_loaded_follows'=>'0', 'crawler_last_run'=>'', 'earliest_reply_in_system'=>'',
        'avg_replies_per_day'=>'0', 'is_public'=>'0', 'is_active'=>'0', 'network'=>'youtube',
        'last_favorite_id' => '0', 'owner_favs_in_system' => '0', 'total_posts_by_owner'=>0,
        'posts_per_day'=>0, 'posts_per_week'=>0, 'percentage_replies'=>0, 'percentage_links'=>0,
        'earliest_post_in_system'=>'2009-01-01 13:48:05', 'favorites_profile' => '0'
        );
        $this->instance = new Instance($r);
    }

    private function buildData() {
        $builders = array();
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'113612142759476883204', 'network'=>'youtube',
        'full_name'=>'Gina Trapani', 'avatar'=>'http://www.myavatar.com'));
        return $builders;
    }
    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $ytc = new YouTubeCrawler($this->instance, 'fauxaccesstoken');
        $this->assertEqual($ytc->access_token, 'fauxaccesstoken');
    }

    public function testFetchUser() {
        $ytc = new YouTubeCrawler($this->instance, 'fauxaccesstoken', 10);
        $ytc->fetchUser($this->instance->network_user_id, $this->instance->network, true);
        $user_dao = new UserMySQLDAO();
        $user = $user_dao->getUserByName('Gina Trapani', 'youtube');

        $this->assertTrue(isset($user));
        $this->assertEqual($user->username, 'Gina Trapani');
        $this->assertEqual($user->full_name, 'Gina Trapani');
        $this->assertEqual($user->user_id, 1136121427);
        $this->assertEqual($user->location, "San Diego");
        $this->assertEqual($user->description,
        'ThinkUp lead developer, This Week in Google co-host, Todo.txt apps creator, founding editor of Lifehacker');
        $this->assertEqual($user->url, 'https://plus.google.com/1136121427');
        $this->assertFalse($user->is_protected);
    }

    public function testInitializeInstanceUserFreshToken() {
        $ytc = new YouTubeCrawler($this->instance, 'faux-access-token', 10);
        $ytc->initializeInstanceUser('ci', 'cs', 'valid_token', 'test_refresh_token', 1);
        $user_dao = new UserMySQLDAO();
        $user = $user_dao->getUserByName('Gina Trapani', 'youtube');

        $this->assertTrue(isset($user));
        $this->assertEqual($user->username, 'Gina Trapani');
        $this->assertEqual($user->full_name, 'Gina Trapani');
        $this->assertEqual($user->user_id, '113612142759476883204');
        $this->assertEqual($user->location, "San Diego");
        $this->assertEqual($user->description,
        'ThinkUp lead developer, This Week in Google co-host, Todo.txt apps creator, founding editor of Lifehacker');
        $this->assertEqual($user->url, 'https://plus.google.com/113612142759476883204');
        $this->assertFalse($user->is_protected);
    }

    public function testInitializeInstanceUserExpiredToken() {
        $ytc = new YouTubeCrawler($this->instance, 'faux-expired-access-token', 10);
        $ytc->initializeInstanceUser('ci', 'cs', 'valid_token', 'test_refresh_token', 1);

        $user_dao = new UserMySQLDAO();
        $user = $user_dao->getUserByName('Gina Trapani', 'youtube');

        $this->assertTrue(isset($user));
        $this->assertEqual($user->username, 'Gina Trapani');
        $this->assertEqual($user->full_name, 'Gina Trapani');
        $this->assertEqual($user->user_id, '113612142759476883204');
        $this->assertEqual($user->location, "San Diego");
        $this->assertEqual($user->description,
        'ThinkUp lead developer, This Week in Google co-host, Todo.txt apps creator, founding editor of Lifehacker');
        $this->assertEqual($user->url, 'https://plus.google.com/113612142759476883204');
        $this->assertFalse($user->is_protected);
    }

    public function testGetOAuthTokens() {
        $ytc = new YouTubeCrawler($this->instance, 'fauxaccesstoken', 10);

        //test getting initial token
        $tokens = $ytc->getOAuthTokens('ci', 'cs', 'tc1', 'authorization_code');
        $this->assertEqual($tokens->access_token, 'faux-access-token');
        $this->assertEqual($tokens->refresh_token, 'faux-refresh-token');

        //test refreshing token
        $tokens = $ytc->getOAuthTokens('ci', 'cs', 'test-refresh_token1',
        'refresh_token');
        $this->assertEqual($tokens->access_token, 'faux-access-token');
        $this->assertEqual($tokens->refresh_token, 'faux-refresh-token');
    }

    public function testGetOAuthTokensWithAndWithoutSSL() {
        $ytc = new YouTubeCrawler($this->instance, 'fauxaccesstoken', 10);

        //test getting token with HTTPS
        $_SERVER['SERVER_NAME'] = 'test';
        $_SERVER['HTTPS'] = 'y';
        $cfg = Config::getInstance();
        $cfg->setValue('site_root_path', '/');
        $redirect_uri = urlencode(Utils::getApplicationURL().'account/?p=youtube');

        $tokens = $ytc->getOAuthTokens('ci', 'cs', 'tc1', 'authorization_code',
        $redirect_uri);
        $this->assertEqual($tokens->access_token, 'faux-access-token-with-https');
        $this->assertEqual($tokens->refresh_token, 'faux-refresh-token-with-https');

        //test getting token without HTTPS
        $_SERVER['HTTPS'] = null;
        $redirect_uri = urlencode(Utils::getApplicationURL().'account/?p=youtube');

        $tokens = $ytc->getOAuthTokens('ci', 'cs', 'tc1', 'authorization_code',
        $redirect_uri);
        $this->assertEqual($tokens->access_token, 'faux-access-token-without-https');
        $this->assertEqual($tokens->refresh_token, 'faux-refresh-token-without-https');
    }

    public function testFetchInstanceUserVideos() {
        $builders = self::buildData();
        $ytc = new YouTubeCrawler($this->instance, 'at', 20);
        $ytc->fetchInstanceUserVideos();
        $video_dao = new VideoMySQLDAO();
        $post_dao = new PostMySQLDAO();
        $count_history_dao = new CountHistoryMySQLDAO();

        // Check the first video got added
        $video_one = $video_dao->getVideoByID('H', 'youtube');
        $this->assertEqual($video_one->post_text, 'My Slideshow');
        $this->assertEqual($video_one->description,
         'I created this video with the YouTube Slideshow Creator (http://www.youtube.com/upload)');
        $this->assertEqual($video_one->likes, 1);
        $this->assertEqual($video_one->dislikes, 0);
        $this->assertEqual($video_one->views, 6);
        $this->assertEqual($video_one->post_id, 'H');
        $this->assertEqual($video_one->is_protected, 0);
        $this->assertEqual($video_one->favorites_added, 0);
        $this->assertEqual($video_one->favorites_removed, 0);
        $this->assertEqual($video_one->shares, 0);
        $this->assertEqual($video_one->subscribers_gained, 0);
        $this->assertEqual($video_one->subscribers_lost, 0);
        $this->assertEqual($video_one->minutes_watched, 4);
        $this->assertEqual($video_one->average_view_duration, 52);
        $this->assertEqual($video_one->average_view_percentage, 24.8284);
        $this->assertEqual($video_one->author_user_id, '113612142759476883204');
        $this->assertEqual($video_one->author_username, 'Gina Trapani');
        $this->assertEqual($video_one->author_fullname, 'Gina Trapani');
        $this->assertEqual($video_one->author_avatar, 'http://www.myavatar.com');
        $this->assertEqual($video_one->source, '');
        $this->assertEqual($video_one->network, 'youtube');

        // Check the all time counts were added correctly
        $favs_added_all_time = $count_history_dao->getLatestCountByPostIDAndType('H', 'favorites_added_all_time');
        $favs_removed_all_time = $count_history_dao->getLatestCountByPostIDAndType('H', 'favorites_removed_all_time');
        $shares_all_time = $count_history_dao->getLatestCountByPostIDAndType('H', 'shares_all_time');
        $subs_gained_all_time = $count_history_dao->getLatestCountByPostIDAndType('H', 'subscribers_gained_all_time');
        $subs_lost_all_time = $count_history_dao->getLatestCountByPostIDAndType('H', 'subscribers_lost_all_time');
        $mins_watched_all_time = $count_history_dao->getLatestCountByPostIDAndType('H', 'minutes_watched_all_time');
        $avg_view_percent_all_time = $count_history_dao->getLatestCountByPostIDAndType('H',
        'average_view_percentage_all_time');
        $avg_view_dur_all_time = $count_history_dao->getLatestCountByPostIDAndType('H',
        'average_view_duration_all_time');
        $views_all_time = $count_history_dao->getLatestCountByPostIDAndType('H', 'views_all_time');
        $likes_all_time = $count_history_dao->getLatestCountByPostIDAndType('H', 'likes_all_time');
        $dislikes_all_time = $count_history_dao->getLatestCountByPostIDAndType('H', 'dislikes_all_time');

        $this->assertEqual($favs_added_all_time['count'], 0);
        $this->assertEqual($favs_removed_all_time['count'], 0);
        $this->assertEqual($shares_all_time['count'], 0);
        $this->assertEqual($subs_gained_all_time['count'], 0);
        $this->assertEqual($subs_lost_all_time['count'], 0);
        $this->assertEqual($mins_watched_all_time['count'], 4);
        $this->assertEqual($avg_view_percent_all_time['count'], 25);
        $this->assertEqual($avg_view_dur_all_time['count'], 52);
        $this->assertEqual($views_all_time['count'], 6);
        $this->assertEqual($likes_all_time['count'], 1);
        $this->assertEqual($dislikes_all_time['count'], 0);

        // Check the replies to it got added
        // Basic 1st reply
        $reply_one = $post_dao->getPost('jm_SGXNfF6AmF20tsHqF_2h_S_fV_0l2DU3AfqjbsNc', 'youtube');
        $this->assertEqual($reply_one->post_id, 'jm_SGXNfF6AmF20tsHqF_2h_S_fV_0l2DU3AfqjbsNc');
        $this->assertEqual($reply_one->post_text, 'Test comment');
        $this->assertEqual($reply_one->author_username, 'Aaron Kalair');
        $this->assertEqual($reply_one->author_fullname, 'Aaron Kalair');
        $this->assertEqual($reply_one->author_avatar,
        'https://lh5.googleusercontent.com/-Z2vFxu2wO6E/AAAAAAAAAAI/AAAAAAAAANQ/Pp0EB7dNKLY/photo.jpg?sz=50');
        $this->assertEqual($reply_one->author_user_id, '115383827382290096528');
        $this->assertEqual($reply_one->pub_date, '2013-06-05 08:13:43');
        $this->assertEqual($reply_one->source, '');
        $this->assertEqual($reply_one->is_protected, 0);
        $this->assertEqual($reply_one->network, 'youtube');
        $this->assertEqual($reply_one->in_reply_to_user_id, '113612142759476883204');
        $this->assertEqual($reply_one->in_reply_to_post_id, 'H');
        // Check we can itterate over replies in a single page
        $reply_two = $post_dao->getPost('gm_SGXNffMJT58F20tsHqF_2h_S_fV_0l2DU3AfqjbsNc', 'youtube');
        $this->assertEqual($reply_two->post_id, 'gm_SGXNffMJT58F20tsHqF_2h_S_fV_0l2DU3AfqjbsNc');
        $this->assertEqual($reply_two->post_text, 'yet another comment');
        $this->assertEqual($reply_two->author_username, 'Aaron Kalair');
        $this->assertEqual($reply_two->author_fullname, 'Aaron Kalair');
        $this->assertEqual($reply_two->author_avatar,
        'https://lh5.googleusercontent.com/-Z2vFxu2wO6E/AAAAAAAAAAI/AAAAAAAAANQ/Pp0EB7dNKLY/photo.jpg?sz=50');
        $this->assertEqual($reply_two->author_user_id, '115383827382290096528');
        $this->assertEqual($reply_two->pub_date, '2013-06-15 07:45:11');
        $this->assertEqual($reply_two->source, '');
        $this->assertEqual($reply_two->is_protected, 0);
        $this->assertEqual($reply_two->network, 'youtube');
        $this->assertEqual($reply_two->in_reply_to_user_id, '113612142759476883204');
        $this->assertEqual($reply_two->in_reply_to_post_id, 'H');
        // Check we can get a reply from another page
        $reply_three = $post_dao->getPost('hg_HF75HJSNY38JH_ht5_fh', 'youtube');
        $this->assertEqual($reply_three->post_id, 'hg_HF75HJSNY38JH_ht5_fh');
        $this->assertEqual($reply_three->post_text, 'A comment on the second page');
        $this->assertEqual($reply_three->author_username, 'Aaron Kalair');
        $this->assertEqual($reply_three->author_fullname, 'Aaron Kalair');
        $this->assertEqual($reply_three->author_avatar,
        'https://lh5.googleusercontent.com/-Z2vFxu2wO6E/AAAAAAAAAAI/AAAAAAAAANQ/Pp0EB7dNKLY/photo.jpg?sz=50');
        $this->assertEqual($reply_three->author_user_id, '115383827382290096528');
        $this->assertEqual($reply_three->pub_date, '2013-06-10 18:14:33');
        $this->assertEqual($reply_three->source, '');
        $this->assertEqual($reply_three->is_protected, 0);
        $this->assertEqual($reply_three->network, 'youtube');
        $this->assertEqual($reply_three->in_reply_to_user_id, '113612142759476883204');
        $this->assertEqual($reply_three->in_reply_to_post_id, 'H');
        // Check we can itterate over videos on the same page of replies
        $video_two = $video_dao->getVideoByID('a', 'youtube');
        $this->assertEqual($video_two->post_text, 'Same Page Video');
        $this->assertEqual($video_two->description, 'This video is on the same page');
        $this->assertEqual($video_two->likes, 10);
        $this->assertEqual($video_two->dislikes, 110);
        $this->assertEqual($video_two->views, 50);
        $this->assertEqual($video_two->post_id, 'a');
        $this->assertEqual($video_two->is_protected, 0);
        $this->assertEqual($video_two->favorites_added, 5);
        $this->assertEqual($video_two->favorites_removed, 1);
        $this->assertEqual($video_two->shares, 0);
        $this->assertEqual($video_two->subscribers_gained, 0);
        $this->assertEqual($video_two->subscribers_lost, 0);
        $this->assertEqual($video_two->minutes_watched, 1);
        $this->assertEqual($video_two->average_view_duration, 2);
        $this->assertEqual($video_two->average_view_percentage, 24.8284);
        $this->assertEqual($video_two->author_user_id, '113612142759476883204');
        $this->assertEqual($video_two->author_username, 'Gina Trapani');
        $this->assertEqual($video_two->author_fullname, 'Gina Trapani');
        $this->assertEqual($video_two->author_avatar, 'http://www.myavatar.com');
        $this->assertEqual($video_two->source, '');
        $this->assertEqual($video_two->network, 'youtube');

        // Check the per day counts got added correctly
        $favs_added = $count_history_dao->getLatestCountByPostIDAndType('a', 'favorites_added');
        $favs_removed = $count_history_dao->getLatestCountByPostIDAndType('a', 'favorites_removed');
        $shares = $count_history_dao->getLatestCountByPostIDAndType('a', 'shares');
        $subs_gained = $count_history_dao->getLatestCountByPostIDAndType('a', 'subscribers_gained');
        $subs_lost = $count_history_dao->getLatestCountByPostIDAndType('a', 'subscribers_lost');
        $mins_watched = $count_history_dao->getLatestCountByPostIDAndType('a', 'minutes_watched');
        $avg_view_percent = $count_history_dao->getLatestCountByPostIDAndType('a', 'average_view_percentage');
        $avg_view_dur = $count_history_dao->getLatestCountByPostIDAndType('a', 'average_view_duration');
        $views = $count_history_dao->getLatestCountByPostIDAndType('a', 'views');
        $likes = $count_history_dao->getLatestCountByPostIDAndType('a', 'likes');
        $dislikes = $count_history_dao->getLatestCountByPostIDAndType('a', 'dislikes');

        $this->assertEqual($favs_added['count'], 4);
        $this->assertEqual($favs_removed['count'], 2);
        $this->assertEqual($shares['count'], 3);
        $this->assertEqual($subs_gained['count'], 10);
        $this->assertEqual($subs_lost['count'], 11);
        $this->assertEqual($mins_watched['count'], 11);
        $this->assertEqual($avg_view_percent['count'], 14);
        $this->assertEqual($avg_view_dur['count'], 15);
        $this->assertEqual($views['count'], 100);
        $this->assertEqual($likes['count'], 0);
        $this->assertEqual($dislikes['count'], 0);

        // Check the all time counts were added correctly
        $favs_added_all_time = $count_history_dao->getLatestCountByPostIDAndType('a', 'favorites_added_all_time');
        $favs_removed_all_time = $count_history_dao->getLatestCountByPostIDAndType('a', 'favorites_removed_all_time');
        $shares_all_time = $count_history_dao->getLatestCountByPostIDAndType('a', 'shares_all_time');
        $subs_gained_all_time = $count_history_dao->getLatestCountByPostIDAndType('a', 'subscribers_gained_all_time');
        $subs_lost_all_time = $count_history_dao->getLatestCountByPostIDAndType('a', 'subscribers_lost_all_time');
        $mins_watched_all_time = $count_history_dao->getLatestCountByPostIDAndType('a', 'minutes_watched_all_time');
        $avg_view_percent_all_time = $count_history_dao->getLatestCountByPostIDAndType('a',
        'average_view_percentage_all_time');
        $avg_view_dur_all_time = $count_history_dao->getLatestCountByPostIDAndType('a',
        'average_view_duration_all_time');
        $views_all_time = $count_history_dao->getLatestCountByPostIDAndType('a', 'views_all_time');
        $likes_all_time = $count_history_dao->getLatestCountByPostIDAndType('a', 'likes_all_time');
        $dislikes_all_time = $count_history_dao->getLatestCountByPostIDAndType('a', 'dislikes_all_time');

        $this->assertEqual($favs_added_all_time['count'], 5);
        $this->assertEqual($favs_removed_all_time['count'], 1);
        $this->assertEqual($shares_all_time['count'], 0);
        $this->assertEqual($subs_gained_all_time['count'], 0);
        $this->assertEqual($subs_lost_all_time['count'], 0);
        $this->assertEqual($mins_watched_all_time['count'], 1);
        $this->assertEqual($avg_view_percent_all_time['count'], 25);
        $this->assertEqual($avg_view_dur_all_time['count'], 2);
        $this->assertEqual($views_all_time['count'], 50);
        $this->assertEqual($likes_all_time['count'], 10);
        $this->assertEqual($dislikes_all_time['count'], 110);

        // Check we can get a video from the next page of videos
        $video_three = $video_dao->getVideoByID('g', 'youtube');
        $this->assertEqual($video_three->post_text, 'My Slideshow');
        $this->assertEqual($video_three->description,
        'I created this video with the YouTube Slideshow Creator (http://www.youtube.com/upload)');
        $this->assertEqual($video_three->likes, 0);
        $this->assertEqual($video_three->dislikes, 0);
        $this->assertEqual($video_three->views, 5);
        $this->assertEqual($video_three->post_id, 'g');
        $this->assertEqual($video_three->is_protected, 0);
        $this->assertEqual($video_three->favorites_added, 0);
        $this->assertEqual($video_three->favorites_removed, 0);
        $this->assertEqual($video_three->shares, 0);
        $this->assertEqual($video_three->subscribers_gained, 0);
        $this->assertEqual($video_three->subscribers_lost, 0);
        $this->assertEqual($video_three->minutes_watched, 0);
        $this->assertEqual($video_three->average_view_duration, 15);
        $this->assertEqual($video_three->average_view_percentage, 42.7689);
        $this->assertEqual($video_three->author_user_id, '113612142759476883204');
        $this->assertEqual($video_three->author_username, 'Gina Trapani');
        $this->assertEqual($video_three->author_fullname, 'Gina Trapani');
        $this->assertEqual($video_three->author_avatar, 'http://www.myavatar.com');
        $this->assertEqual($video_three->source, '');
        $this->assertEqual($video_three->network, 'youtube');

        // Check the all time counts were added correctly
        $favs_added_all_time = $count_history_dao->getLatestCountByPostIDAndType('g', 'favorites_added_all_time');
        $favs_removed_all_time = $count_history_dao->getLatestCountByPostIDAndType('g', 'favorites_removed_all_time');
        $shares_all_time = $count_history_dao->getLatestCountByPostIDAndType('g', 'shares_all_time');
        $subs_gained_all_time = $count_history_dao->getLatestCountByPostIDAndType('g', 'subscribers_gained_all_time');
        $subs_lost_all_time = $count_history_dao->getLatestCountByPostIDAndType('g', 'subscribers_lost_all_time');
        $mins_watched_all_time = $count_history_dao->getLatestCountByPostIDAndType('g', 'minutes_watched_all_time');
        $avg_view_percent_all_time = $count_history_dao->getLatestCountByPostIDAndType('g',
        'average_view_percentage_all_time');
        $avg_view_dur_all_time = $count_history_dao->getLatestCountByPostIDAndType('g',
        'average_view_duration_all_time');
        $views_all_time = $count_history_dao->getLatestCountByPostIDAndType('g', 'views_all_time');
        $likes_all_time = $count_history_dao->getLatestCountByPostIDAndType('g', 'likes_all_time');
        $dislikes_all_time = $count_history_dao->getLatestCountByPostIDAndType('g', 'dislikes_all_time');

        $this->assertEqual($favs_added_all_time['count'], 0);
        $this->assertEqual($favs_removed_all_time['count'], 0);
        $this->assertEqual($shares_all_time['count'], 0);
        $this->assertEqual($subs_gained_all_time['count'], 0);
        $this->assertEqual($subs_lost_all_time['count'], 0);
        $this->assertEqual($mins_watched_all_time['count'], 0);
        $this->assertEqual($avg_view_percent_all_time['count'], 43);
        $this->assertEqual($avg_view_dur_all_time['count'], 15);
        $this->assertEqual($views_all_time['count'], 5);
        $this->assertEqual($likes_all_time['count'], 0);
        $this->assertEqual($dislikes_all_time['count'], 0);
    }
}
