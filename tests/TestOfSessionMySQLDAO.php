<?php
/**
 *
 * ThinkUp/tests/TestOfSessionMySQLDAO.php
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
 * Test of SessionMySQL DAO implementation
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 * @author Chris Moyer
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfSessionMySQLDAO extends ThinkUpUnitTestCase {
    /**
     * @var SessionMySQLDAO
     */
    protected $dao;

    public function setUp() {
        parent::setUp();
        $this->DAO = new SessionMySQLDAO();
        $this->builders = self::buildData();
        $this->config = Config::getInstance();
    }

    protected function buildData() {
        $builders = array();

        $builders[] = FixtureBuilder::build('sessions', array('session_id' => 'foobar', 'updated' => date('c'),
        'data' => serialize(array('foo' => 'bar'))));

        $builders[] = FixtureBuilder::build('sessions', array('session_id' => 'nofoo', 'updated' => date('c'),
        'data' => serialize(array('blah' => 'baz'))));

        $builders[] = FixtureBuilder::build('sessions', array('session_id' => 'oldsession',
        'updated' => date('c', time() - 200),
        'data' => 'still here'));

        return $builders;
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    public function testRead() {
        $res = $this->DAO->read('foobar');
        $data = unserialize($res);
        $this->assertEqual('bar', $data['foo']);

        $res = $this->DAO->read('nofoo');
        $data = unserialize($res);
        $this->assertNull($data['foo']);

        $res = $this->DAO->read('nonexist');
        $this->assertEqual('', $res);
    }

    public function testWrite() {
        $this->DAO->write('newsession', 'mydata');
        $res = $this->DAO->read('newsession');
        $this->assertEqual('mydata', $res);

        $this->DAO->write('newsession', 'new data');
        $res = $this->DAO->read('newsession');
        $this->assertNotEqual('mydata', $res);
        $this->assertEqual('new data', $res);
    }

    public function testDestroy() {
        $this->DAO->write('newsession', 'mydata');
        $res = $this->DAO->read('newsession');
        $this->assertEqual('mydata', $res);

        $this->DAO->destroy('newsession');
        $res = $this->DAO->read('newsession');
        $this->assertNotEqual('mydata', $res);
        $this->assertEqual('', $res);
    }

    public function testGc() {
        $res = $this->DAO->read('oldsession');
        $this->assertEqual('still here', $res);
        $this->assertNotEqual('', $res);

        $this->DAO->gc(100);

        $res = $this->DAO->read('oldsession');
        $this->assertEqual('', $res);
        $this->assertNotEqual('still here', $res);

        $res = $this->DAO->read('nofoo');
        $this->assertNotEqual('', $res);
        $res = $this->DAO->read('foobar');
        $this->assertNotEqual('', $res);
    }
}
