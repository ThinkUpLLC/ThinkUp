<?php
/**
 *
 * ThinkUp/tests/TestOfUserVersionsMySQLDAO.php
 *
 * Copyright (c) 2014 Chris Moyer
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
 * Test of UserVersionsMySQL DAO implementation
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfUserVersionsMySQLDAO extends ThinkUpUnitTestCase {
    /**
     * @var UserVersionsMySQLDAO
     */
    protected $dao;

    public function setUp() {
        parent::setUp();
        $this->DAO = new UserVersionsMySQLDAO();
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    public function testGetRecentVersions() {
        $builders = array();
        $builders[] = FixtureBuilder::build('user_versions', array('user_key' => 1, 'field_name' => 'a',
            'field_value' => 'a1', 'crawl_time' => '-1d'));
        $builders[] = FixtureBuilder::build('user_versions', array('user_key' => 1, 'field_name' => 'a',
            'field_value' => 'a2', 'crawl_time' => '-2h'));
        $builders[] = FixtureBuilder::build('user_versions', array('user_key' => 1, 'field_name' => 'b',
            'field_value' => 'a3', 'crawl_time' => '-3h'));
        $builders[] = FixtureBuilder::build('user_versions', array('user_key' => 2, 'field_name' => 'a',
            'field_value' => 'a4', 'crawl_time' => '-0d'));

        $res = $this->DAO->getRecentVersions(1, 2);
        $this->assertIsA($res, 'Array');
        $this->assertEqual(count($res), 3);
        $this->assertEqual($res[0]['field_name'], 'a');
        $this->assertEqual($res[0]['field_value'], 'a2');
        $this->assertEqual($res[0]['user_key'], 1);
        $this->assertEqual($res[1]['field_name'], 'b');
        $this->assertEqual($res[1]['field_value'], 'a3');
        $this->assertEqual($res[1]['user_key'], 1);
        $this->assertEqual($res[2]['field_name'], 'a');
        $this->assertEqual($res[2]['field_value'], 'a1');
        $this->assertEqual($res[2]['user_key'], 1);

        $res = $this->DAO->getRecentVersions(1, 1, array('b'));
        $this->assertIsA($res, 'Array');
        $this->assertEqual(count($res), 1);
        $this->assertEqual($res[0]['field_name'], 'b');
        $this->assertEqual($res[0]['field_value'], 'a3');
        $this->assertEqual($res[0]['user_key'], 1);

        $res = $this->DAO->getRecentVersions(1, 1, array('a'));
        $this->assertIsA($res, 'Array');
        $this->assertEqual(count($res), 1);
        $this->assertEqual($res[0]['field_name'], 'a');
        $this->assertEqual($res[0]['field_value'], 'a2');
        $this->assertEqual($res[0]['user_key'], 1);
    }

    public function testAddVersionOfField() {
        $res = $this->DAO->getRecentVersions(1, 99999);
        $this->assertEqual(count($res), 0);

        $this->DAO->addVersionOfField(1, 'myfield', 'something changed!');
        $res = $this->DAO->getRecentVersions(1, 99999);
        $this->assertEqual(count($res), 1);
        $this->assertEqual($res[0]['field_name'], 'myfield');
        $this->assertEqual($res[0]['field_value'], 'something changed!');
        $this->assertEqual($res[0]['user_key'], 1);

        $this->DAO->addVersionOfField(1, 'myfield', 'something else changed!');
        $res = $this->DAO->getRecentVersions(1, 99999);
        $this->assertEqual(count($res), 2);
        $this->assertEqual($res[0]['field_name'], 'myfield');
        $this->assertEqual($res[0]['field_value'], 'something else changed!');
        $this->assertEqual($res[0]['user_key'], 1);
        $this->assertEqual($res[1]['field_name'], 'myfield');
        $this->assertEqual($res[1]['field_value'], 'something changed!');
        $this->assertEqual($res[1]['user_key'], 1);
    }

    public function testGetRecentFriendsVersions() {
        $builders = array();
        $builders[] = FixtureBuilder::build('user_versions', array('user_key' => 2, 'field_name' => 'bio',
            'field_value' => 'I work at CompanyCo!', 'crawl_time' => '-1d'));
        $builders[] = FixtureBuilder::build('user_versions', array('user_key' => 2, 'field_name' => 'bio',
            'field_value' => 'I am unemployed.', 'crawl_time' => '-0d'));
        $builders[] = FixtureBuilder::build('user_versions', array('user_key' => 3, 'field_name' => 'bio',
            'field_value' => 'My bio.', 'crawl_time' => '-0d'));
        $builders[] = FixtureBuilder::build('user_versions', array('user_key' => 4, 'field_name' => 'url',
            'field_value' => 'http://foo.bar', 'crawl_time' => '-28h'));
        $builders[] = FixtureBuilder::build('follows', array('active' => 1, 'user_id' => 22,
            'follower_id' => 11, 'network' => 'twitter'));
        $builders[] = FixtureBuilder::build('follows', array('active' => 0, 'user_id' => 33,
            'follower_id' => 11, 'network' => 'twitter'));
        $builders[] = FixtureBuilder::build('users', array('network'=>'twitter','id'=>1,'user_id'=>11));
        $builders[] = FixtureBuilder::build('users', array('network'=>'twitter','id'=>2,'user_id'=>22));
        $builders[] = FixtureBuilder::build('users', array('network'=>'twitter','id'=>3,'user_id'=>33));
        $builders[] = FixtureBuilder::build('users', array('network'=>'twitter','id'=>4,'user_id'=>44));

        $res = $this->DAO->getRecentFriendsVersions(1, 2);
        $this->assertEqual(count($res), 2);
        $this->assertEqual($res[0]['user_key'], 2);
        $this->assertEqual($res[0]['field_name'], 'bio');
        $this->assertEqual($res[0]['field_value'], 'I am unemployed.');
        $this->assertEqual($res[1]['user_key'], 2);
        $this->assertEqual($res[1]['field_name'], 'bio');
        $this->assertEqual($res[1]['field_value'], 'I work at CompanyCo!');

        $res = $this->DAO->getRecentFriendsVersions(1, 1);
        $this->assertEqual(count($res), 1);
        $this->assertEqual($res[0]['user_key'], 2);
        $this->assertEqual($res[0]['field_name'], 'bio');
        $this->assertEqual($res[0]['field_value'], 'I am unemployed.');

        $res = $this->DAO->getRecentFriendsVersions(1, 1, array('url'));
        $this->assertEqual(count($res), 0);

        $res = $this->DAO->getRecentFriendsVersions(1, 1, array('bio'));
        $this->assertEqual(count($res), 1);
        $this->assertEqual($res[0]['user_key'], 2);
        $this->assertEqual($res[0]['field_name'], 'bio');
        $this->assertEqual($res[0]['field_value'], 'I am unemployed.');

        $res = $this->DAO->getRecentFriendsVersions(1, 1, array('bio','url'));
        $this->assertEqual(count($res), 1);
        $this->assertEqual($res[0]['user_key'], 2);
        $this->assertEqual($res[0]['field_name'], 'bio');
        $this->assertEqual($res[0]['field_value'], 'I am unemployed.');

        $builders[] = FixtureBuilder::build('user_versions', array('user_key' => 2, 'field_name' => 'url',
            'field_value' => 'http://company.co', 'crawl_time' => '-2d'));

        $res = $this->DAO->getRecentFriendsVersions(1, 1, array('bio','url'));
        $this->assertEqual(count($res), 1);
        $this->assertEqual($res[0]['user_key'], 2);
        $this->assertEqual($res[0]['field_name'], 'bio');
        $this->assertEqual($res[0]['field_value'], 'I am unemployed.');

        $res = $this->DAO->getRecentFriendsVersions(1, 3, array('bio','url'));
        $this->assertEqual(count($res), 3);
        $this->assertEqual($res[0]['user_key'], 2);
        $this->assertEqual($res[0]['field_name'], 'bio');
        $this->assertEqual($res[0]['field_value'], 'I am unemployed.');
        $this->assertEqual($res[1]['user_key'], 2);
        $this->assertEqual($res[1]['field_name'], 'bio');
        $this->assertEqual($res[1]['field_value'], 'I work at CompanyCo!');
        $this->assertEqual($res[2]['user_key'], 2);
        $this->assertEqual($res[2]['field_name'], 'url');
        $this->assertEqual($res[2]['field_value'], 'http://company.co');

        $builders[] = FixtureBuilder::build('follows', array('active' => 1, 'user_id' => 44,
            'follower_id' => 11, 'network' => 'twitter'));
        $res = $this->DAO->getRecentFriendsVersions(1, 3, array('bio','url'));
        $this->assertEqual(count($res), 4);
        $this->assertEqual($res[0]['user_key'], 2);
        $this->assertEqual($res[0]['field_name'], 'bio');
        $this->assertEqual($res[0]['field_value'], 'I am unemployed.');
        $this->assertEqual($res[1]['user_key'], 2);
        $this->assertEqual($res[1]['field_name'], 'bio');
        $this->assertEqual($res[1]['field_value'], 'I work at CompanyCo!');
        $this->assertEqual($res[2]['user_key'], 4);
        $this->assertEqual($res[2]['field_name'], 'url');
        $this->assertEqual($res[2]['field_value'], 'http://foo.bar');
        $this->assertEqual($res[3]['user_key'], 2);
        $this->assertEqual($res[3]['field_name'], 'url');
        $this->assertEqual($res[3]['field_value'], 'http://company.co');

        $res = $this->DAO->getRecentFriendsVersions(2, 2);
        $this->assertEqual(count($res), 0);
    }

    public function testGetVersionBeforeDay() {
        $builders = array();
        $builders[] = FixtureBuilder::build('user_versions', array('user_key' => 1, 'field_name' => 'a',
            'field_value' => 'yesterday', 'crawl_time' => '-1d'));
        $builders[] = FixtureBuilder::build('user_versions', array('user_key' => 1, 'field_name' => 'a',
            'field_value' => 'today', 'crawl_time' => '-0d'));

        $res = $this->DAO->getVersionBeforeDay(1, date('Y-m-d', time() - (60*60*24)), 'a');
        $this->assertNull($res);

        $builders[] = FixtureBuilder::build('user_versions', array('user_key' => 1, 'field_name' => 'a',
            'field_value' => 'two days ago', 'crawl_time' => '-2d'));

        $res = $this->DAO->getVersionBeforeDay(1, date('Y-m-d', time() - (60*60*24)), 'a');
        $this->assertEqual($res['user_key'], 1);
        $this->assertEqual($res['field_name'], 'a');
        $this->assertEqual($res['field_value'], 'two days ago');

        $res = $this->DAO->getVersionBeforeDay(1, date('Y-m-d', time() - (60*60*24)), 'b');
        $this->assertNull($res);

        $res = $this->DAO->getVersionBeforeDay(1, date('Y-m-d'), 'a');
        $this->assertEqual($res['user_key'], 1);
        $this->assertEqual($res['field_name'], 'a');
        $this->assertEqual($res['field_value'], 'yesterday');
    }
}
