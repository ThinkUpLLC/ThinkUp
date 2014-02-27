<?php
/**
 *
 * ThinkUp/tests/TestOfSessionCache.php
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
 * Test of SessionCache
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';


class TestOfSessionCache extends ThinkUpUnitTestCase {

    public function setUp(){
        $config = Config::getInstance();
        $config->setValue('use_db_sessions', false);
        parent::setUp();
    }

    public function tearDown() {
        $config = Config::getInstance();
        $config->setValue('use_db_sessions', true);
        parent::tearDown();
    }

    public function testPutGetIsset() {
        $config = Config::getInstance();

        //nothing is set
        $this->assertNull(SessionCache::get('my_key'));
        $this->assertFalse(SessionCache::isKeySet('my_key'));

        //set a key
        SessionCache::put('my_key', 'my_value');

        $this->assertTrue(isset($_SESSION[$config->getValue('source_root_path')]));
        $this->assertEqual($_SESSION[$config->getValue('source_root_path')]['my_key'], 'my_value');

        $this->assertEqual(SessionCache::get('my_key'), 'my_value');

        //overwrite existing key
        SessionCache::put('my_key', 'my_value2');
        $this->assertTrue($_SESSION[$config->getValue('source_root_path')]['my_key'] != 'my_value');
        $this->assertEqual($_SESSION[$config->getValue('source_root_path')]['my_key'], 'my_value2');

        //set another key
        SessionCache::put('my_key2', 'my_other_value');
        $this->assertEqual($_SESSION[$config->getValue('source_root_path')]['my_key2'], 'my_other_value');

        //unset first key
        SessionCache::unsetKey('my_key');
        $this->assertNull(SessionCache::get('my_key'));
        $this->assertFalse(SessionCache::isKeySet('my_key'));
    }

    public function testInit() {
        $this->assertEqual(session_id(), '');
        SessionCache::init();

        // We should be started now
        $this->assertNotEqual(session_id(), '');
        session_destroy();
    }

    public function testUseDBSetting() {
        session_id(md5(time()));
        SessionCache::init();
        SessionCache::put('my_key', 'my_value2');
        $dao = DAOFactory::getDAO('SessionDAO');
        session_write_close();

        $data = $dao->read(session_id());
        $this->assertEqual('', $data);
    }

    public function testVerifyDBness() {
        $config = Config::getInstance();
        $config->setValue('use_db_sessions', true);
        session_id(md5(time()));
        SessionCache::init();
        SessionCache::put('my_key', 'my_value2');

        $dao = DAOFactory::getDAO('SessionDAO');
        $data = $dao->read(session_id());
        $this->assertEqual('', $data);

        session_write_close();
        $data = $dao->read(session_id());
        $this->assertPattern('/my_key/', $data);
        $this->assertPattern('/my_value2/', $data);
        $this->assertNotEqual('', $data);

        // Retrieve it manually just to make sure
        $sql = "SELECT * FROM tu_sessions";
        $stmt = SessionMySQLDAO::$PDO->query($sql);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertPattern('/my_key/', $row['data']);
        $this->assertPattern('/my_value2/', $row['data']);
    }
}
