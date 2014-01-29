<?php
/**
 *
 * ThinkUp/tests/TestOfOwnerInstanceMySQLDAO.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Mark Wilkie
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Mark Wilkie <mark[at]bitterpill[dot]org>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Mark Wilkie
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfOwnerInstanceMySQLDAO extends ThinkUpUnitTestCase {

    const TEST_TABLE_OI = 'owner_instances';
    const TEST_TABLE_I = 'instances';

    public function setUp() {
        parent::setUp();
        $this->logger = Logger::getInstance();
        $this->config = Config::getInstance();
    }

    public function tearDown() {
        parent::tearDown();
        $this->logger->close();
        //clear doesOwnerHaveAccessToPost query cache
        OwnerInstanceMySQLDAO::$post_access_query_cache = array();
    }

    public function testDelete() {
        $dao = new OwnerInstanceMySQLDAO();
        $builder = FixtureBuilder::build(self::TEST_TABLE_OI, array('instance_id' => 20, 'owner_id'=>50) );
        $owner_instance = $dao->get(50, 20);
        $this->assertNotNull($owner_instance);

        $result = $dao->delete(50, 20);
        $this->assertEqual($result, 1);
        $owner_instance = $dao->get(50, 20);
        $this->assertNull($owner_instance);
    }

    public function testDeleteByInstance() {
        $dao = new OwnerInstanceMySQLDAO();
        $builder1 = FixtureBuilder::build(self::TEST_TABLE_OI, array('instance_id' => 20, 'owner_id'=>50) );
        $builder2 = FixtureBuilder::build(self::TEST_TABLE_OI, array('instance_id' => 20, 'owner_id'=>51) );
        $builder3 = FixtureBuilder::build(self::TEST_TABLE_OI, array('instance_id' => 20, 'owner_id'=>52) );
        $owner_instance = $dao->get(50, 20);
        $this->assertNotNull($owner_instance);
        $owner_instance = $dao->get(51, 20);
        $this->assertNotNull($owner_instance);
        $owner_instance = $dao->get(52, 20);
        $this->assertNotNull($owner_instance);

        $result = $dao->deleteByInstance(20);
        $this->assertEqual($result, 3);
        $owner_instance = $dao->get(50, 20);
        $this->assertNull($owner_instance);
        $owner_instance = $dao->get(51, 20);
        $this->assertNull($owner_instance);
        $owner_instance = $dao->get(52, 20);
        $this->assertNull($owner_instance);
    }

    public function testGetByInstance() {
        $dao = new OwnerInstanceMySQLDAO();
        $builder1 = FixtureBuilder::build(self::TEST_TABLE_OI, array('instance_id' => 20, 'owner_id'=>50) );
        $builder2 = FixtureBuilder::build(self::TEST_TABLE_OI, array('instance_id' => 20, 'owner_id'=>51) );
        $builder3 = FixtureBuilder::build(self::TEST_TABLE_OI, array('instance_id' => 20, 'owner_id'=>52) );
        $owner_instances = $dao->getByInstance(20);
        $this->assertIsA($owner_instances, 'Array');
        $this->assertEqual(sizeof($owner_instances), 3);
    }

    public function testGetByOwner() {
        $dao = new OwnerInstanceMySQLDAO();
        $builder1 = FixtureBuilder::build(self::TEST_TABLE_OI, array('instance_id' => 20, 'owner_id'=>50) );
        $builder2 = FixtureBuilder::build(self::TEST_TABLE_OI, array('instance_id' => 20, 'owner_id'=>51) );
        $builder3 = FixtureBuilder::build(self::TEST_TABLE_OI, array('instance_id' => 20, 'owner_id'=>52) );
        $owner_instances = $dao->getByOwner(50);
        $this->assertIsA($owner_instances, 'Array');
        $this->assertEqual(sizeof($owner_instances), 1);
    }

    public function testInsertOwnerInstance() {
        $dao = new OwnerInstanceMySQLDAO();
        $result = $dao->insert(10, 20, 'aaa', 'bbb');
        $this->assertTrue($result);
        $stmt = OwnerInstanceMySQLDAO::$PDO->query( "select * from " . $this->table_prefix . 'owner_instances' );
        $data = $stmt->fetch();
        $this->assertEqual(10, $data['owner_id'], 'we have an owner_id of: 10');
        $this->assertEqual(20, $data['instance_id'], 'we have an instance_id of: 20');
        $this->assertEqual('aaa', $data['oauth_access_token'], 'we have an oauth_access_token of: aaa');
        $this->assertEqual('bbb', $data['oauth_access_token_secret'], 'we have an oauth_access_token_secret of: bbb');
        $this->assertFalse( $stmt->fetch(), 'we have only one record' );
    }

    public function testGetOAuthTokens() {

        $builder = FixtureBuilder::build(self::TEST_TABLE_OI, array('instance_id' => 20) );
        $dao = new OwnerInstanceMySQLDAO();

        // no record
        $tokens = $dao->getOAuthTokens(21);
        $this->assertNull($tokens);

        // valid record
        $tokens = $dao->getOAuthTokens(20);
        $this->assertEqual($tokens['oauth_access_token'], $builder->columns['oauth_access_token'],
        'we queried a valid oauth_access_token');
        $this->assertEqual($tokens['oauth_access_token_secret'], $builder->columns['oauth_access_token_secret'],
        'we queried a valid oauth_access_token_secret');
    }

    public function testGetOwnerInstance() {
        $builder = FixtureBuilder::build(self::TEST_TABLE_OI, array('owner_id' => 20, 'instance_id'=>20) );
        $dao = new OwnerInstanceMySQLDAO();

        // no record
        $owner_instance = $dao->get(1, 20);
        $this->assertNull($owner_instance);
        $owner_instance = $dao->get($builder->columns['owner_id'], 21);
        $this->assertNull($owner_instance);

        // valid record
        $owner_instance = $dao->get( $builder->columns['owner_id'], 20);
        $this->assertIsA($owner_instance, 'OwnerInstance');
        $columns = $builder->columns;
        $this->assertEqual($owner_instance->owner_id, $columns['owner_id'], 'valid owner id');
        $this->assertEqual($owner_instance->instance_id, $columns['instance_id'], 'valid instance id');
        $this->assertEqual($owner_instance->oauth_access_token, $columns['oauth_access_token'],
        'valid oauth_access_token');
        $this->assertEqual($owner_instance->oauth_access_token_secret, $columns['oauth_access_token_secret'],
        'valid oauth_access_token_secret');
    }


    public function testUpdateTokens() {
        $builder_data = array('owner_id' => 2, 'instance_id' => 20);
        $builder = FixtureBuilder::build(self::TEST_TABLE_OI,  $builder_data);
        $dao = new OwnerInstanceMySQLDAO();

        // invalid instance id
        $result = $dao->updateTokens(2, 21, 'ccc', 'ddd');
        $this->assertFalse($result);

        // invalid owner id
        $result = $dao->updateTokens(3, 20, 'ccc2', 'ddd2');
        $this->assertFalse($result);

        // valid update
        $result = $dao->updateTokens(2, 20, 'ccc3', 'ddd3');
        $sql = "select * from " . $this->table_prefix . 'owner_instances where instance_id = 20';
        $stmt = OwnerInstanceMySQLDAO::$PDO->query($sql);
        $data = $stmt->fetch();
        $this->assertEqual($data['oauth_access_token'], 'ccc3');
        $this->assertEqual($data['oauth_access_token_secret'], 'ddd3');
    }

    public function testDoesOwnerHaveAccessToInstance() {
        $oi_data = array('owner_id' => 2, 'instance_id' => 20);
        $oinstances_builder = FixtureBuilder::build(self::TEST_TABLE_OI,  $oi_data);
        $i_data = array('network_username' => 'mojojojo', 'id' => 20, 'network_user_id' =>'filler_data',
        'posts_per_day'=>10);
        $instances_builder = FixtureBuilder::build(self::TEST_TABLE_I,  $i_data);

        $dao = new OwnerInstanceMySQLDAO();

        // no owner id or instance id
        try {
            $dao->doesOwnerHaveAccessToInstance(new Owner(), new Instance());
            $this->fail("should throw BadArgumentException");
        } catch(BadArgumentException $e) {
            $this->assertPattern('/requires an/', $e->getMessage());
        }

        // no owner id
        try {
            $dao->doesOwnerHaveAccessToInstance(new Owner(), new Instance());
            $this->fail("should throw BadArgumentException");
        } catch(BadArgumentException $e) {
            $this->assertPattern('/requires an/', $e->getMessage());
        }

        // no match
        $owner = new Owner();
        $owner->id = 1;

        // no instance id
        try {
            $dao->doesOwnerHaveAccessToInstance($owner, new Instance());
            $this->fail("should throw BadArgumentException");
        } catch(BadArgumentException $e) {
            $this->assertPattern('/requires an/', $e->getMessage());
        }

        $instance = new Instance();
        $instance->id = 1;
        $this->assertFalse($dao->doesOwnerHaveAccessToInstance($owner, $instance), 'no access');
        $owner->id = 2;
        $this->assertFalse($dao->doesOwnerHaveAccessToInstance($owner, $instance), 'no access');

        // valid match
        $instance->id = 20;
        $this->assertTrue($dao->doesOwnerHaveAccessToInstance($owner, $instance), 'has access');
    }

    public function testDoesOwnerHaveAccessToPost() {
        $oi_data = array('owner_id' => 2, 'instance_id' => 20);
        $oinstances_builder = FixtureBuilder::build(self::TEST_TABLE_OI,  $oi_data);
        $i_data = array('id' => 20, 'network_username' => 'mojojojo', 'network_user_id' =>'10', 'network'=>'twitter',
        'posts_per_day'=>10);
        $instances_builder = FixtureBuilder::build(self::TEST_TABLE_I,  $i_data);

        $dao = new OwnerInstanceMySQLDAO();

        $post = new Post(array('id'=>1, 'author_user_id'=>'20', 'author_username'=>'no one',
        'author_fullname'=>"No One", 'author_avatar'=>'yo.jpg', 'source'=>'TweetDeck', 'pub_date'=>'',
        'adj_pub_date'=>'', 'in_reply_to_user_id'=>'',
        'in_reply_to_post_id'=>'', 'reply_count_cache'=>'', 'in_retweet_of_post_id'=>'', 'retweet_count_cache'=>'',
        'retweet_count_api' =>'', 'old_retweet_count_cache' => '', 'in_rt_of_user_id' =>'',
        'post_id'=>'9021481076', 'is_protected'=>1, 'place_id' => 'ece7b97d252718cc', 'favlike_count_cache'=>0,
        'post_text'=>'I like cookies', 'network'=>'twitter', 'geo'=>'', 'place'=>'', 'location'=>'',
        'is_geo_encoded'=>0, 'is_reply_by_friend'=>0, 'is_retweet_by_friend'=>0, 'reply_retweet_distance'=>0));

        // no owner id
        try {
            $dao->doesOwnerHaveAccessToPost(new Owner(), $post);
            $this->fail("should throw BadArgumentException");
        } catch(BadArgumentException $e) {
            $this->assertPattern('/requires an/', $e->getMessage());
        }

        // no match
        $owner = new Owner();
        $owner->id = 1;

        //public post and owner not admin
        $post->is_protected = false;
        $this->assertTrue($dao->doesOwnerHaveAccessToPost($owner, $post));

        //protected post and owner not admin
        $post->is_protected = true;
        $this->assertFalse($dao->doesOwnerHaveAccessToPost($owner, $post));

        // should have empty cache arrays
        $this->assertEqual(count(OwnerInstanceMySQLDAO::$post_access_query_cache['1-twitter-network_id_cache']), 0);
        $this->assertEqual(count(OwnerInstanceMySQLDAO::$post_access_query_cache['20-twitter-follower_id_cache']), 0);

        //protected post but owner is admin
        $owner->is_admin = true;
        $this->assertTrue($dao->doesOwnerHaveAccessToPost($owner, $post));

        //protected post, owner is not admin, and owner doesn't have an authed instance which follows author
        $owner->is_admin = false;
        $this->assertFalse($dao->doesOwnerHaveAccessToPost($owner, $post));

        //protected post, owner is not admin, and owner DOES have an authed instance which follows author
        OwnerInstanceMySQLDAO::$post_access_query_cache = array(); // clear cache
        $owner->id = 2;
        $follows_builder = FixtureBuilder::build('follows', array('user_id'=>'20', 'follower_id'=>'10',
        'network'=>'twitter'));
        $this->assertTrue($dao->doesOwnerHaveAccessToPost($owner, $post));

        // should have populated cache arrays
        $this->assertEqual(count(OwnerInstanceMySQLDAO::$post_access_query_cache['2-twitter-network_id_cache']), 1);
        $this->assertEqual(count(OwnerInstanceMySQLDAO::$post_access_query_cache['20-twitter-follower_id_cache']), 1);
        $this->assertEqual(
        OwnerInstanceMySQLDAO::$post_access_query_cache['2-twitter-network_id_cache'][0]['network_user_id'], 10);
        $this->assertEqual(
        OwnerInstanceMySQLDAO::$post_access_query_cache['20-twitter-follower_id_cache'][0]['follower_id'], 10);
    }

    public function testSetAuthErrorByTokens() {
        $builder = FixtureBuilder::build(self::TEST_TABLE_OI, array('instance_id' => 20, 'owner_id'=>50,
        'auth_error'=>'', 'oauth_access_token'=>'1234', 'oauth_access_token_secret'=>'') );

        $dao = new OwnerInstanceMySQLDAO();

        $owner_instance = $dao->get(50, 20);
        $this->assertNotNull($owner_instance);
        $this->assertIsA($owner_instance, 'OwnerInstance');
        $this->assertEqual($owner_instance->auth_error, '');

        $res = $dao->setAuthErrorByTokens(20, '1234', '',
        'Error validating access token: Session has expired at unix time SOME_TIME. '.
        'The current unix time is SOME_TIME.');
        $this->assertTrue($res);
        $owner_instance = $dao->get(50, 20);
        $this->assertNotNull($owner_instance);
        $this->assertIsA($owner_instance, 'OwnerInstance');
        $this->assertEqual($owner_instance->auth_error, 'Error validating access token: Session has expired at '.
        'unix time SOME_TIME. The current unix time is SOME_TIME.');

        $res = $dao->setAuthErrorByTokens(20, '1234', 'dfdfd', 'Error validating access token: Session has expired '.
        'at unix time SOME_TIME. The current unix time is SOME_TIME.');
        $this->assertFalse($res);

        $res = $dao->setAuthErrorByTokens(20, '1234', '');
        $this->assertTrue($res);
        $owner_instance = $dao->get(50, 20);
        $this->assertIsA($owner_instance, 'OwnerInstance');
        $this->assertEqual($owner_instance->auth_error, '');
    }

    public function testGetOwnerEmailByInstanceTokens() {
        $builders = array();
        $builders[] = FixtureBuilder::build(self::TEST_TABLE_OI, array('instance_id' => 20, 'owner_id'=>50,
        'auth_error'=>'', 'oauth_access_token'=>'1234', 'oauth_access_token_secret'=>'') );
        $builders[] = FixtureBuilder::build('owners', array('id' => 50, 'email'=>'tester@example.com' ));

        $dao = new OwnerInstanceMySQLDAO();

        $email = $dao->getOwnerEmailByInstanceTokens('20', '1234');
        $this->assertEqual($email, 'tester@example.com');

        $email = $dao->getOwnerEmailByInstanceTokens('20', 'abcd1234');
        $this->assertNull($email);

        $email = $dao->getOwnerEmailByInstanceTokens('21', '1234');
        $this->assertNull($email);
    }
}
