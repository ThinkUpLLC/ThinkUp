<?php
/**
 *
 * ThinkUp/tests/TestOfExportMySQLDAO.php
 *
 * Copyright (c) 2011-2013 Gina Trapani
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
 * Test TestOfExportMySQLDAO
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfExportMySQLDAO extends ThinkUpUnitTestCase {

    /**
     * FixtureBuilder array
     * @var arr
     */
    var $builders;

    /**
     * DAO instance
     * @var ExportMySQLDAO
     */
    var $dao;

    public function setUp() {
        parent::setUp();
        $this->dao = new ExportMySQLDAO();
        $this->builders = $this->buildData();
    }

    public function tearDown() {
        $this->dao = null;
        $this->builders = null;
        parent::tearDown();

    }

    protected function buildData() {
        $builders = array();
        $builders[] = FixtureBuilder::build('users', array('user_id'=>13, 'user_name'=>'ev',
        'full_name'=>'Ev Williams', 'avatar'=>'avatar.jpg', 'is_protected'=>0, 'follower_count'=>10,
        'last_updated'=>'2005-01-01 13:48:05', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>10, 'user_name'=>'ev_replies',
        'full_name'=>'Ev Williams\' replier', 'avatar'=>'avatar.jpg', 'is_protected'=>0, 'follower_count'=>10,
        'last_updated'=>'2005-01-01 13:48:05', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>9, 'user_name'=>'ev_replies2',
        'full_name'=>'Ev Williams\' replier 2', 'avatar'=>'avatar.jpg', 'is_protected'=>0, 'follower_count'=>10,
        'last_updated'=>'2005-01-01 13:48:05', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>8, 'user_name'=>'ev_retweeter',
        'full_name'=>'Ev Williams\' retweeter', 'avatar'=>'avatar.jpg', 'is_protected'=>0, 'follower_count'=>10,
        'last_updated'=>'2005-01-01 13:48:05', 'network'=>'twitter'));

        //Add straight text posts
        $counter = 0;
        while ($counter < 40) {
            if ($counter % 3 == 0) {
                $source = '<a href="http://twitter.com" rel="nofollow">Tweetie for Mac</a>';
            } else if ($counter % 3 == 1) {
                $source = '<a href="http://twitter.com/tweetbutton" rel="nofollow">Tweet Button</a>';
            } else {
                $source = 'web';
            }
            $builders[] = FixtureBuilder::build('posts', array('post_id'=>$counter, 'author_user_id'=>13,
            'author_username'=>'ev', 'author_fullname'=>'Ev Williams', 'author_avatar'=>'avatar.jpg',
            'post_text'=>'This is post '.$counter, 'source'=>$source, 'pub_date'=>'-1h',
            'reply_count_cache'=>rand(0, 4), 'is_protected'=>0,
            'retweet_count_cache'=>floor($counter/2), 'network'=>'twitter',
            'old_retweet_count_cache' => floor($counter/3), 'in_rt_of_user_id' => null,
            'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));
            $counter++;
        }

        //add 2 replies to post 2
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>41, 'author_user_id'=>10,
        'author_username'=>'ev_replies', 'author_fullname'=>'Ev Williams\' replier', 'author_avatar'=>'avatar.jpg',
        'post_text'=>'@ev This is a reply to post 2', 'source'=>'web', 'pub_date'=>'-10m',
        'reply_count_cache'=>rand(0, 4), 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>2, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));

        $builders[] = FixtureBuilder::build('posts', array('post_id'=>42, 'author_user_id'=>9,
        'author_username'=>'ev_replies2', 'author_fullname'=>'Ev Williams\' replier 2', 'author_avatar'=>'avatar.jpg',
        'post_text'=>'@ev This is another reply to post 2', 'source'=>'web', 'pub_date'=>'-11m',
        'reply_count_cache'=>rand(0, 4), 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>2, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));

        //add a retweet of post 3
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>43, 'author_user_id'=>8,
        'author_username'=>'ev_replies2', 'author_fullname'=>'Ev Williams\' retweeter', 'author_avatar'=>'avatar.jpg',
        'post_text'=>'RT @ev: This is post 3', 'source'=>'web', 'pub_date'=>'-11m',
        'reply_count_cache'=>rand(0, 4), 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => 13,
        'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>3, 'is_geo_encoded'=>0));

        //add a mention
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>44, 'author_user_id'=>8,
        'author_username'=>'ev_replies2', 'author_fullname'=>'Ev Williams\' retweeter', 'author_avatar'=>'avatar.jpg',
        'post_text'=>'Yo, @ev is da man!', 'source'=>'web', 'pub_date'=>'-11m',
        'reply_count_cache'=>rand(0, 4), 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));

        //add a non-Ev post
        $builders[] = FixtureBuilder::build('posts', array('post_id'=>45, 'author_user_id'=>8,
        'author_username'=>'ev_replies2', 'author_fullname'=>'Ev Williams\' retweeter', 'author_avatar'=>'avatar.jpg',
        'post_text'=>'Who\'s da man?', 'source'=>'web', 'pub_date'=>'-11m',
        'reply_count_cache'=>rand(0, 4), 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter',
        'old_retweet_count_cache' => 0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>null, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));

        return $builders;
    }

    public function testConstructor(){
        $my_dao = new ExportMySQLDAO();
        $this->assertTrue(isset($my_dao));
    }

    public function testCreateExportedPostsTable(){
        $result = $this->dao->createExportedPostsTable();
        $this->assertTrue($result);
    }

    public function testDoesExportedPostsTableExist(){
        $result = $this->dao->doesExportedPostsTableExist();
        $this->assertFalse($result);
        $result = $this->dao->createExportedPostsTable();
        $this->assertTrue($result);
        $result = $this->dao->doesExportedPostsTableExist();
        $this->assertTrue($result);
    }

    public function testDropExportedPostsTable(){
        $result = $this->dao->doesExportedPostsTableExist();
        $this->assertFalse($result);
        $result = $this->dao->createExportedPostsTable();
        $this->assertTrue($result);
        $result = $this->dao->doesExportedPostsTableExist();
        $this->assertTrue($result);
        $result = $this->dao->dropExportedPostsTable();
        $this->assertTrue($result);
        $result = $this->dao->doesExportedPostsTableExist();
        $this->assertFalse($result);
    }

    public function testCreateExportedFollowsTable(){
        $result = $this->dao->createExportedFollowsTable();
        $this->assertTrue($result);
    }

    public function testDoesExportedFollowsTableExist(){
        $result = $this->dao->doesExportedFollowsTableExist();
        $this->assertFalse($result);
        $result = $this->dao->createExportedFollowsTable();
        $this->assertTrue($result);
        $result = $this->dao->doesExportedFollowsTableExist();
        $this->assertTrue($result);
    }

    public function testDropExportedFollowsTable(){
        $result = $this->dao->doesExportedFollowsTableExist();
        $this->assertFalse($result);
        $result = $this->dao->createExportedFollowsTable();
        $this->assertTrue($result);
        $result = $this->dao->doesExportedFollowsTableExist();
        $this->assertTrue($result);
        $result = $this->dao->dropExportedFollowsTable();
        $this->assertTrue($result);
        $result = $this->dao->doesExportedFollowsTableExist();
        $this->assertFalse($result);
    }

    public function testExportPostsByServiceUser(){
        $result = $this->dao->exportPostsByServiceUser('ev', 'twitter');
        $this->assertEqual($result, 40);
    }

    public function testExportRepliesToRetweetsOfServiceUser() {
        $post_dao = new PostMySQLDAO();
        $posts_to_process = $post_dao->getAllPosts(13, 'twitter', 500, 1);
        $replies_rts_exported = $this->dao->exportRepliesRetweetsOfPosts($posts_to_process);
        //2 replies and 1 retweet
        $this->assertEqual($replies_rts_exported, 3);
    }

    public function testExportMentionsOfServiceUser() {
        $mentions_exported = $this->dao->exportMentionsOfServiceUser('ev', 'twitter');
        //4 mentions exported
        $this->assertEqual($mentions_exported, 4);
    }

    public function testExportPostsServiceUserRepliedTo() {
        $more_data = array();
        $more_data[] = FixtureBuilder::build('posts', array('post_id'=>46, 'author_user_id'=>13,
        'author_username'=>'ev', 'author_fullname'=>'Ev Williams', 'author_avatar'=>'avatar.jpg',
        'post_text'=>'I am?', 'source'=>'web', 'pub_date'=>'-1h',
        'reply_count_cache'=>rand(0, 4), 'is_protected'=>0,
        'retweet_count_cache'=>0, 'network'=>'twitter',
        'old_retweet_count_cache' =>0, 'in_rt_of_user_id' => null,
        'in_reply_to_post_id'=>45, 'in_retweet_of_post_id'=>null, 'is_geo_encoded'=>0));

        $replied_to_posts_exported = $this->dao->exportPostsServiceUserRepliedTo('ev', 'twitter');
        //1 replied-to posts exported
        $this->assertEqual($replied_to_posts_exported, 1);
    }

    public function testExportFavoritesOfServiceUser() {
        $more_data = array();
        $more_data[] = FixtureBuilder::build('favorites', array('post_id'=>44, 'fav_of_user_id'=>13,
        'author_user_id'=>8, 'network'=>'twitter'));

        $bkdir = FileDataManager::getBackupPath();

        $favorites_file = FileDataManager::getBackupPath('favorites.tmp');
        if (file_exists($favorites_file)) {
            unlink($favorites_file);
        }
        $this->assertFalse(file_exists($favorites_file));
        $favorite_posts_exported = $this->dao->exportFavoritesOfServiceUser(13, 'twitter', $favorites_file);
        //1 favorite posts exported
        $this->assertEqual($favorite_posts_exported, 1);
        $this->assertTrue(file_exists($favorites_file));
    }

    public function testExportGeoToFile() {
        $file = FileDataManager::getBackupPath('encoded_locations.tmp');
        if (file_exists($file)) {
            unlink($file);
        }
        $this->assertFalse(file_exists($file));
        $this->dao->exportGeoToFile($file);
        $this->assertTrue(file_exists($file));
    }

    public function testExportToFile() {
        $posts_table_file = FileDataManager::getBackupPath('posts.tmp');
        $links_table_file = FileDataManager::getBackupPath('links.tmp');
        $users_table_file = FileDataManager::getBackupPath('users.tmp');
        if (file_exists($posts_table_file)) {
            unlink($posts_table_file);
        }
        if (file_exists($links_table_file)) {
            unlink($links_table_file);
        }
        if (file_exists($users_table_file)) {
            unlink($users_table_file);
        }
        $this->assertFalse(file_exists($posts_table_file));
        $this->assertFalse(file_exists($users_table_file));
        $this->assertFalse(file_exists($links_table_file));

        $this->dao->exportPostsLinksUsersToFile($posts_table_file, $links_table_file, $users_table_file);

        $this->assertTrue(file_exists($posts_table_file));
        $this->assertTrue(file_exists($users_table_file));
        $this->assertTrue(file_exists($links_table_file));
    }

    public function testGetExportFields() {
        $config = Config::getInstance();
        $config_array = $config->getValuesArray();
        $test_table_sql = 'CREATE TABLE ' . $config_array['table_prefix'] . 'test_table(' .
            'id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,' .
            'test_name varchar(20),' .
            'test_id int(11),' .
            'unique key test_id_idx (test_id)' .
            ')';
        $this->testdb_helper->runSQL($test_table_sql);

        $post_export_dao = DAOFactory::getDAO('ExportDAO');

        $result = $post_export_dao->getExportFields('test_table');
        $this->assertEqual($result, 'test_name, test_id');

        $result = $post_export_dao->getExportFields('test_table', 't');
        $this->assertEqual($result, 't.test_name, t.test_id');
    }
}
