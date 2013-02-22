<?php
/**
 *
 * ThinkUp/tests/TestOfUserController.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfUserController extends ThinkUpUnitTestCase {

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
        'session/login.php">log in</a> to do this.', $v_mgr->getTemplateDataItem('error_msg'));
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
        //not enough posts to warrant a next page link
        $this->assertEqual($v_mgr->getTemplateDataItem('next_page'), null);
        $this->assertEqual($v_mgr->getTemplateDataItem('last_page'), 0);

        $this->assertEqual($controller->getCacheKeyString(), '.htuser.index.tpl-me@example.com-someuser1-twitter');
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

        //not enough posts to warrant a next page link
        $this->assertEqual($v_mgr->getTemplateDataItem('next_page'), null);
        //we're on the first page by default, so no last page
        $this->assertEqual($v_mgr->getTemplateDataItem('last_page'), null);
        $this->assertEqual($controller->getCacheKeyString(),
        '.htuser.index.tpl-me@example.com-someuser1-twitter-instancetestuser');
    }

    public function testUserPostPaging() {
        $builders = $this->buildData();

        $i=0;
        while ($i < 43) { //3 pages of posts, 2 pages of 20 + 1 page of 3
            $builders[] = FixtureBuilder::build('posts', array('author_username'=>'someuser1', 'author_user_id'=>10,
             'network'=>'twitter', 'post_id'=>(200+$i)));
            $i++;
        }

        $this->simulateLogin('me@example.com');
        $_GET['u'] = 'someuser1';
        $_GET['n'] = 'twitter';
        $_GET['i'] = 'instancetestuser';

        //First page
        $controller = new UserController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->control();
        $this->assertPattern("/someuser1/", $results);

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'User Details: someuser1');
        //enough posts to warrant a next page link
        $this->assertEqual($v_mgr->getTemplateDataItem('next_page'), 2);
        $this->assertEqual($v_mgr->getTemplateDataItem('last_page'), null);
        $this->assertEqual($controller->getCacheKeyString(),
        '.htuser.index.tpl-me@example.com-someuser1-twitter-instancetestuser');

        //Second page
        $_GET['page'] = '2';
        $controller = new UserController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->control();
        $this->assertPattern("/someuser1/", $results);

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'User Details: someuser1');
        //enough posts to warrant a next page link
        $this->assertEqual($v_mgr->getTemplateDataItem('next_page'), 3);
        $this->assertEqual($v_mgr->getTemplateDataItem('last_page'), 1);
        $this->assertEqual($controller->getCacheKeyString(),
        '.htuser.index.tpl-me@example.com-someuser1-twitter-instancetestuser-2');

        //Third (last) page
        $_GET['page'] = '3';
        $controller = new UserController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->control();
        $this->assertPattern("/someuser1/", $results);

        //test if view variables were set correctly
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'User Details: someuser1');
        //enough posts to warrant a next page link
        $this->assertEqual($v_mgr->getTemplateDataItem('next_page'), null);
        $this->assertEqual($v_mgr->getTemplateDataItem('last_page'), 2);
        $this->assertEqual($controller->getCacheKeyString(),
        '.htuser.index.tpl-me@example.com-someuser1-twitter-instancetestuser-3');
    }

    private function buildData() {
        $owner_builder = FixtureBuilder::build('owners', array('id'=>1, 'email'=>'me@example.com'));
        $instance_builder = FixtureBuilder::build('instances', array('id'=>1, 'network_username'=>'someuser1',
        'network'=>'twitter'));
        $instance1_builder = FixtureBuilder::build('instances', array('id'=>2, 'network_username'=>'someuser2',
        'network'=>'twitter'));
        $owner_instance_builder = FixtureBuilder::build('owner_instances', array('instance_id'=>1, 'owner_id'=>1));
        $posts1_builder = FixtureBuilder::build('posts', array('author_username'=>'someuser1', 'author_user_id'=>10,
        'post_text'=>'My first post', 'network'=>'twitter', 'post_id'=>101));
        $posts2_builder = FixtureBuilder::build('posts', array('author_username'=>'someuser1',
        'post_text'=>'My second post', 'network'=>'twitter', 'post_id'=>102));
        $user1_builder = FixtureBuilder::build('users', array('user_id'=>10, 'user_name'=>'someuser1',
        'network'=>'twitter', 'post_id'=>103));

        return array($owner_builder, $instance_builder, $instance1_builder, $owner_instance_builder, $posts1_builder,
        $posts2_builder, $user1_builder);
    }
}