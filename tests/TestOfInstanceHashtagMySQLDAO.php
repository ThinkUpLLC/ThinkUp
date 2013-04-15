<?php
/**
 *
 * ThinkUp/tests/TestOfInstanceHashtagMySQLDAO.php
 *
 * Copyright (c) 2013 Eduard Cucurella
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013  Eduard Cucurella
 * @author Eduard Cucurella
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfInstanceHashtagMySQLDAO extends ThinkUpUnitTestCase {
    /**
     * @var InstanceHashtagMySQLDAO
     */
    protected $dao;

    public function setUp() {
        parent::setUp();
        $this->builders = self::buildData();
        $this->dao = new InstanceHashtagMySQLDAO();
    }

    protected function buildData() {
        $builders[] = FixtureBuilder::build('hashtags',
        array('hashtag' => 'first', 'network'=>'twitter', 'count_cache' => 0));
        $builders[] = FixtureBuilder::build('hashtags',
        array('hashtag' => 'second', 'network'=>'twitter', 'count_cache' => 0));
        $builders[] = FixtureBuilder::build('hashtags',
        array('hashtag' => 'third', 'network'=>'twitter', 'count_cache' => 0));
        $builders[] = FixtureBuilder::build('instances',
        array('network_user_id' => '1', 'network_viewer_id' => '1', 'network_username' => 'nun',
          'last_post_id'  => '1', 'crawler_last_run' => '2013-02-28 15:21:16', 'total_posts_by_owner' => 0,
          'total_posts_in_system' => 0, 'total_replies_in_system' => 0, 'total_follows_in_system' => 0,
          'posts_per_day' => 0, 'posts_per_week' => 0, 'percentage_replies' => 0, 'percentage_links' => 0,
          'earliest_post_in_system' => '2013-02-28 15:21:16',
          'earliest_reply_in_system' => '2013-02-28 15:21:16', 'is_archive_loaded_posts' => 0,
          'is_archive_loaded_replies' => 0, 'is_archive_loaded_follows' => 0, 'is_public' => 0,
          'is_active' => 0, 'network'  => 'twitter', 'favorites_profile' => 0, 'owner_favs_in_system' => 0));
        return $builders;
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    public function testInsert() {
        $res=$this->dao->getByInstance(1);
        $this->assertEqual(sizeof($res), 0);

        //successful insert
        $res=$this->dao->insert(1,1);
        $this->assertTrue($res);
        $res=$this->dao->getByInstance(1);
        $this->assertTrue(sizeof($res), 1);
        $this->assertEqual($res[0]->id, 1);
        $this->assertEqual($res[0]->instance_id, 1);
        $this->assertEqual($res[0]->hashtag_id, 1);
        $this->assertEqual($res[0]->last_post_id, '0');
        $this->assertEqual($res[0]->earliest_post_id, '0');

        //unsuccessful insert
        $res=$this->dao->insert(1,1);
        $this->assertFalse($res);
    }

    public function testGetByUsername() {
        //does not exist
        $res = $this->dao->getByUsername('ecucurella', 'facebook');
        $this->assertIsA($res, 'array');
        $this->assertEqual(sizeof($res), 0);

        //two exist
        $res = $this->dao->insert(1,1);
        $res = $this->dao->insert(1,2);
        $res = $this->dao->getByUsername('nun', 'twitter');
        $this->assertIsA($res, 'array');
        $this->assertEqual(sizeof($res) ,2);
        $this->assertIsA($res[0], 'Hashtag');
    }

    public function testDelete() {
        $res=$this->dao->getByInstance(1);
        $this->assertEqual(sizeof($res), 0);
        $res=$this->dao->insert(1,1);
        $this->assertEqual($res, 1);
        $res=$this->dao->getByInstance(1);
        $this->assertEqual(sizeof($res), 1);
        //successful delete
        $res=$this->dao->delete(1,1);
        $this->assertTrue($res);
        $res=$this->dao->getByInstance(1);
        $this->assertEqual(sizeof($res), 0);

        //unsuccessful delete
        $res=$this->dao->delete(1,10);
        $this->assertFalse($res);
    }

    public function testDeleteByInstance() {
        $res=$this->dao->getByInstance(1);
        $this->assertEqual(sizeof($res), 0);
        $res=$this->dao->insert(1,1);
        $this->assertTrue($res);
        $res=$this->dao->insert(1,2);
        $this->assertTrue($res);
        $res=$this->dao->insert(1,3);
        $this->assertTrue($res);
        $res=$this->dao->getByInstance(1);
        $this->assertEqual(sizeof($res), 3);

        //Delete 3 instance hashtags
        $res=$this->dao->deleteByInstance(1);
        $this->assertEqual($res, 3);
        $res=$this->dao->getByInstance(1);
        $this->assertEqual(sizeof($res), 0);

        //Delete 0 instance hashtags
        $res=$this->dao->deleteByInstance(2);
        $this->assertEqual($res, 0);
    }

    public function testUpdateLastPostID() {
        $res=$this->dao->getByInstance(1);
        $this->assertEqual(sizeof($res), 0);
        $res=$this->dao->insert(1,1);
        $this->assertTrue($res);
        $res=$this->dao->getByInstance(1);
        $this->assertEqual(sizeof($res), 1);
        $this->assertEqual($res[0]->id, 1);
        $this->assertEqual($res[0]->instance_id, 1);
        $this->assertEqual($res[0]->hashtag_id, 1);
        $this->assertEqual($res[0]->last_post_id, '0');
        $this->assertEqual($res[0]->earliest_post_id, '0');

        //successful update
        $res=$this->dao->updateLastPostID(1,1,'1001');
        $this->assertTrue($res);
        $res=$this->dao->getByInstance(1);
        $this->assertEqual(sizeof($res), 1);
        $this->assertEqual($res[0]->instance_id, 1);
        $this->assertEqual($res[0]->hashtag_id, 1);
        $this->assertEqual($res[0]->last_post_id, '1001');

        //unsuccessful update
        $res = $this->dao->updateLastPostID(10,10,'1001');
        $this->assertFalse($res);
    }

    public function testUpdateEarliestPostID() {
        $res=$this->dao->getByInstance(1);
        $this->assertEqual(sizeof($res), 0);
        $res=$this->dao->insert(1,1);
        $this->assertEqual($res, 1);
        $res=$this->dao->getByInstance(1);
        $this->assertEqual(sizeof($res), 1);
        $this->assertEqual($res[0]->id, 1);
        $this->assertEqual($res[0]->instance_id, 1);
        $this->assertEqual($res[0]->hashtag_id, 1);
        $this->assertEqual($res[0]->last_post_id, '0');
        $this->assertEqual($res[0]->earliest_post_id, '0');

        //successful update
        $res=$this->dao->updateEarliestPostID(1,1,'501');
        $this->assertTrue($res);
        $res=$this->dao->getByInstance(1);
        $this->assertEqual(sizeof($res), 1);
        $this->assertEqual($res[0]->instance_id, 1);
        $this->assertEqual($res[0]->hashtag_id, 1);
        $this->assertEqual($res[0]->earliest_post_id, '501');

        //unsuccessful update
        $res=$this->dao->updateEarliestPostID(15,15,'501');
        $this->assertFalse($res);
    }

    public function testDeleteInstanceHashtagsByHashtagID() {
        $res=$this->dao->getByInstance(1);
        $this->assertEqual(sizeof($res), 0);
        $res=$this->dao->insert(1,1);
        $this->assertTrue($res);
        $res=$this->dao->insert(1,2);
        $this->assertTrue($res);
        $res=$this->dao->insert(1,3);
        $this->assertTrue($res);
        $res=$this->dao->getByInstance(1);
        $this->assertEqual(sizeof($res), 3);

        //successful delete
        $res=$this->dao->deleteInstanceHashtagsByHashtagID(1);
        $this->assertEqual($res, 1);
        $res=$this->dao->getByInstance(1);
        $this->assertEqual(sizeof($res), 2);
        $res=$this->dao->deleteInstanceHashtagsByHashtagID(2);
        $this->assertEqual($res, 1);
        $res=$this->dao->getByInstance(1);
        $this->assertEqual(sizeof($res), 1);
        $res=$this->dao->deleteInstanceHashtagsByHashtagID(3);
        $this->assertEqual($res, 1);
        $res=$this->dao->getByInstance(1);
        $this->assertEqual(sizeof($res), 0);

        //unsuccessful delete
        $res=$this->dao->deleteInstanceHashtagsByHashtagID(100);
        $this->assertEqual($res, 0);
    }

    public function testIsHashtagSaved() {
        $res = $this->dao->isHashtagSaved(1);
        $this->assertFalse($res);

        $builder = FixtureBuilder::build('instances_hashtags', array('instance_id'=>'1', 'hashtag_id'=>'1'));
        $res = $this->dao->isHashtagSaved(1);
        $this->assertTrue($res);
    }
}
