<?php
/**
 *
 * ThinkUp/tests/classes/class.ThinkUpWebTestCase.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
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
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 */
class ThinkUpWebTestCase extends WebTestCase {
    var $db;
    var $conn;
    var $testdb_helper;
    var $url;

    public function setUp() {
        require THINKUP_WEBAPP_PATH.'config.inc.php';
        global $TEST_DATABASE;
        global $TEST_SERVER_DOMAIN;

        $this->url = $TEST_SERVER_DOMAIN;

        //Override default CFG values
        $THINKUP_CFG['db_name'] = $TEST_DATABASE;

        $this->db = new Database($THINKUP_CFG);
        $this->conn = $this->db->getConnection();

        $this->testdb_helper = new ThinkUpTestDatabaseHelper();
        $this->testdb_helper->create($this->db);
    }

    public function tearDown() {
        $this->testdb_helper->drop($this->db);
        $this->db->closeConnection($this->conn);
    }

    /**
     * Insert some test data to navigate the app
     */
    protected function buildData() {
        $builders = array();
         
        //Add owner
        $session = new Session();
        $cryptpass = $session->pwdcrypt("secretpassword");
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'email'=>'me@example.com', 'pwd'=>$cryptpass,
        'is_activated'=>1,'is_admin'=>1));

        //Add instance
        $builders[] = FixtureBuilder::build('instances', array('id'=>1, 'network_user_id'=>17,
        'network_username'=>'thinkupapp', 'is_public'=>1, 'network'=>'twitter'));

        //Add instance_owner
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>1));

        //Insert test data into test table
        $builders[] = FixtureBuilder::build('users', array('user_id'=>12, 'user_name'=>'jack',
        'full_name'=>'Jack Dorsey', 'avatar'=>'avatar.jpg', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>13, 'user_name'=>'ev',
        'full_name'=>'Ev Williams', 'avatar'=>'avatar.jpg', 'last_updated'=>'1/1/2005', 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>16, 'user_name'=>'private',
        'full_name'=>'Private Poster', 'avatar'=>'avatar.jpg', 'is_protected'=>1, 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>17, 'user_name'=>'thinkupapp',
        'full_name'=>'ThinkUpers', 'avatar'=>'avatar.jpg', 'is_protected'=>0, 'follower_count'=>10, 
        'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>18, 'user_name'=>'shutterbug',
        'full_name'=>'Shutter Bug', 'avatar'=>'avatar.jpg', 'is_protected'=>0, 'follower_count'=>10,
        'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('users', array('user_id'=>19, 'user_name'=>'linkbaiter',
        'full_name'=>'Link Baiter', 'avatar'=>'avatar.jpg', 'is_protected'=>0, 'follower_count'=>10,
        'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('user_errors', array('user_id'=>15, 'error_code'=>404,
        'error_text'=>'User not found', 'error_issued_to_user_id'=>13));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>13, 'follower_id'=>12));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>13, 'follower_id'=>14));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>13, 'follower_id'=>15));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>13, 'follower_id'=>16));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>16, 'follower_id'=>12));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>19, 'follower_id'=>13));

        $builders[] = FixtureBuilder::build('follows', array('user_id'=>18, 'follower_id'=>13));

        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>13, 'network_username'=>'ev',
        'is_public'=>1, 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>18,
        'network_username'=>'shutterbug', 'is_public'=>1, 'network'=>'twitter'));

        $builders[] = FixtureBuilder::build('instances', array('network_user_id'=>19,
        'network_username'=>'linkbaiter', 'is_public'=>1, 'network'=>'twitter'));

        $counter = 0;
        while ($counter < 40) {
            $reply_or_forward_count = $counter + 200;
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
            $builders[] = FixtureBuilder::build('posts', array('post_id'=>$counter, 'author_user_id'=>13,
            'author_username'=>'ev', 'author_fullname'=>'Ev Williams', 'author_avatar'=>'avatar.jpg', 'source'=>'web', 
            'pub_date'=>'2006-01-01 00:$pseudo_minute:00', 'reply_count_cache'=>$reply_or_forward_count, 
            'retweet_count_cache'=>$reply_or_forward_count, 'post_text'=>'This is post '.$counter, 
            'network'=>'twitter'));

            $counter++;
        }

        $counter = 0;
        while ($counter < 40) {
            $post_id = $counter + 40;
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
            $builders[] = FixtureBuilder::build('posts', array('post_id'=>$post_id, 'author_user_id'=>18,
            'author_username'=>'shutterbug', 'author_fullname'=>'Shutter Bug', 'author_avatar'=>'avatar.jpg', 
            'source'=>'web', 'pub_date'=>'2006-01-02 00:$pseudo_minute:00', 'reply_count_cache'=>0, 
            'retweet_count_cache'=>0, 'post_text'=>'This is image post '.$counter, 'network'=>'twitter'));

            $builders[] = FixtureBuilder::build('links', array('url'=>'http://example.com/'.$counter,
            'expanded_url'=>'http://example.com/'.$counter.'jpg', 'title'=>'', 'clicks'=>0, 'post_id'=>$post_id, 
            'is_image'=>1));

            $counter++;
        }

        $counter = 0;
        while ($counter < 40) {
            $post_id = $counter + 80;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);
            $builders[] = FixtureBuilder::build('posts', array('post_id'=>$post_id, 'author_user_id'=>19,
            'author_username'=>'linkbaiter', 'author_fullname'=>'Link Baiter', 'author_avatar'=>'avatar.jpg', 
            'post_text'=>'This is link post '.$counter, 'source'=>'web', 'pub_date'=>'2006-03-01 00:$pseudo_minute:00',
            'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'twitter'));

            $builders[] = FixtureBuilder::build('links', array('url'=>'http://example.com/'.$counter,
            'expanded_url'=>'http://example.com/'.$counter.'html', 
            'title'=>'Link '.$counter, 'clicks'=>0, 'post_id'=>$post_id, 'is_image'=>0));

            $counter++;
        }
        $counter = 0;
        while ($counter < 10) {
            $post_id = $counter + 120;
            $pseudo_minute = str_pad(($counter), 2, "0", STR_PAD_LEFT);
            $builders[] = FixtureBuilder::build('posts', array('post_id'=>$post_id, 'author_user_id'=>1234,
            'author_username'=>'thinkupapp', 'author_fullname'=>'thinkupapp', 'author_avatar'=>'avatar.jpg', 
            'post_text'=>'This is test post '.$counter, 'source'=>'web', 'pub_date'=>'2006-03-01 00:$pseudo_minute:00',
            'reply_count_cache'=>0, 'retweet_count_cache'=>0, 'network'=>'twitter'));

            $counter++;
        }
        return $builders;
    }
}
