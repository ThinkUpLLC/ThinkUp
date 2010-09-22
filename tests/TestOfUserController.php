<?php
/**
 *
 * ThinkUp/tests/TestOfUserController.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Dwi Widiastuti
 *
 * LICENSE:
 *
 * This file is part of ThinkUp.
 * 
 * ThinkUp is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * ThinkUp is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with ThinkUp.  If not, see <http://www.gnu.org/licenses/>.
 *
*/
/**
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Dwi Widiastuti
*/
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfUserController extends ThinkUpUnitTestCase {

    public function __construct() {
        $this->UnitTestCase('UserController class test');
    }

    public function testConstructor() {
        $controller = new UserController(true);
        $this->assertTrue(isset($controller));
    }

    public function testNotLoggedIn() {
        $controller = new UserController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->go();
        $v_mgr = $controller->getViewManager();

        $config = Config::getInstance();
        $this->assertEqual('You must <a href="'.$config->getValue('site_root_path').
        'session/login.php">log in</a> to do this.', $v_mgr->getTemplateDataItem('errormsg'));
    }

    public function testMissingParams() {
        $this->simulateLogin('me@example.com');
        $controller = new UserController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->control();
        $this->assertPattern("/User and network not specified./", $results);
    }

    public function testNonExistentUser() {
        $this->simulateLogin('me@example.com');
        $_GET['u'] = 'idontexist';
        $_GET['n'] = 'idontexist';
        $controller = new UserController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->control();
        $this->assertPattern("/idontexist is not in the system./", $results);
    }

    public function testExistentUserWithoutInstance() {
        $builders = $this->buildData();

        $this->simulateLogin('me@example.com');
        $_GET['u'] = 'someuser1';
        $_GET['n'] = 'twitter';
        $controller = new UserController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->control();
        $this->assertPattern("/someuser1/", $results);

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('user_statuses'), 'array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('user_statuses')), 1 );
        $this->assertIsA($v_mgr->getTemplateDataItem('instances'), 'array');
        $this->assertIsA($v_mgr->getTemplateDataItem('profile'), 'User');

        $this->assertEqual($controller->getCacheKeyString(), 'user.index.tpl-me@example.com-someuser1-twitter');
    }

    public function testExistentUserWithInstance() {
        $builders = $this->buildData();

        $this->simulateLogin('me@example.com');
        $_GET['u'] = 'someuser1';
        $_GET['n'] = 'twitter';
        $_GET['i'] = 'instancetestuser';
        $controller = new UserController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->control();
        $this->assertPattern("/someuser1/", $results);

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'User Details: someuser1');
        $this->assertEqual($v_mgr->getTemplateDataItem('logo_link'), 'index.php');

        $this->assertEqual($controller->getCacheKeyString(),
        'user.index.tpl-me@example.com-someuser1-twitter-instancetestuser');
    }

    private function buildData() {
        $owner_builder = FixtureBuilder::build('owners', array('id'=>1, 'email'=>'me@example.com'));
        $instance_builder = FixtureBuilder::build('instances', array('id'=>1, 'network_username'=>'someuser1',
        'network'=>'twitter'));
        $instance1_builder = FixtureBuilder::build('instances', array('id'=>2, 'network_username'=>'someuser2',
        'network'=>'twitter'));
        $owner_instance_builder = FixtureBuilder::build('owner_instances', array('instance_id'=>1, 'owner_id'=>1));
        $posts1_builder = FixtureBuilder::build('posts', array('author_username'=>'someuser1', 'author_user_id'=>10,
        'post_text'=>'My first post', 'network'=>'twitter'));
        $posts2_builder = FixtureBuilder::build('posts', array('author_username'=>'someuser1',
        'post_text'=>'My second post', 'network'=>'twitter'));
        $user1_builder = FixtureBuilder::build('users', array('user_id'=>10, 'user_name'=>'someuser1',
        'network'=>'twitter'));

        return array($owner_builder, $instance_builder, $instance1_builder, $owner_instance_builder, $posts1_builder,
        $posts2_builder, $user1_builder);
    }
}