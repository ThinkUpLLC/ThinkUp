<?php
/**
 *
 * ThinkUp/tests/TestOfExportController.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Mark Wilkie, Michael Louis Thaler
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
 * @author Michael Louis Thaler <michael[dot]louis[dot]thaler[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Mark Wilkie, Michael Louis Thaler
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfExportController extends ThinkUpUnitTestCase {

    public function testConstructor() {
        $controller = new ExportController(true);
        $this->assertTrue(isset($controller));
    }

    public function testNotLoggedIn() {
        $controller = new ExportController(true);
        $this->assertTrue(isset($controller));
        $results = $controller->go();
        $this->assertPattern( '/session\/login.php\?redirect\=/', $controller->redirect_destination);
    }

    public function testMissingParams() {
        $this->simulateLogin('me@example.com');
        $controller = new ExportController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->control();
        $this->assertPattern("/No user to retrieve./", $results);
    }

    public function testNonExistentUser() {
        $this->simulateLogin('me@example.com');
        $_GET['u'] = 'idontexist';
        $_GET['n'] = 'idontexist';
        $controller = new ExportController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->control();
        $this->assertPattern("/User idontexist on idontexist is not in ThinkUp./", $results);
    }

    public function testOwnerWithoutAccess() {
        $builders = $this->buildData();
        $this->simulateLogin('me@example.com');
        $_GET['u'] = 'someuser2';
        $_GET['n'] = 'twitter';
        $controller = new ExportController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->control();
        $this->assertPattern("/Insufficient privileges/", $results);
    }

    public function testOwnerWithAccess() {
        $builders = $this->buildData();
        $builders2 = $this->buildFacebookData();

        $this->simulateLogin('me@example.com');
        $_GET['u'] = 'someuser1';
        $_GET['n'] = 'twitter';
        $controller = new ExportController(true);
        $this->assertTrue(isset($controller));

        ob_start();
        $controller->control();
        $results = ob_get_contents();
        ob_end_clean();
        $this->assertPattern("/My first post/", $results);
        $this->assertPattern("/My second post/", $results);
        $this->assertNoPattern("/My first Facebook post/", $results);
    }

    public function testOwnerWithAccessFacebookPageData() {
        $builders = $this->buildFacebookData();
        $builders2 = $this->buildData();

        $this->simulateLogin('me2@example.com');
        $_GET['u'] = 'someuser1';
        $_GET['n'] = 'facebook page';
        $controller = new ExportController(true);
        $this->assertTrue(isset($controller));

        ob_start();
        $controller->control();
        $results = ob_get_contents();
        ob_end_clean();
        $this->assertPattern("/My first Facebook post/", $results);
        $this->assertPattern("/My second Facebook post/", $results);
        $this->assertNoPattern("/My first post/", $results);
    }

    public function testExplicitPostsExport() {
        $builders = $this->buildData();

        $this->simulateLogin('me@example.com');
        $_GET['u'] = 'someuser1';
        $_GET['n'] = 'twitter';
        $_GET['type'] = 'posts';
        $controller = new ExportController(true);
        $this->assertTrue(isset($controller));

        ob_start();
        $controller->control();
        $results = ob_get_contents();
        ob_end_clean();
        $this->assertPattern("/My first post/", $results);
    }

    public function testRepliesExport() {
        $builders = $this->buildData();

        $this->simulateLogin('me@example.com');
        $_GET['u'] = 'someuser1';
        $_GET['n'] = 'twitter';
        $_GET['type'] = 'replies';
        $_GET['post_id'] = '1';
        $controller = new ExportController(true);
        $this->assertTrue(isset($controller));

        ob_start();
        $controller->control();
        $results = ob_get_contents();
        ob_end_clean();
        $this->assertPattern("/Reply to first post/", $results);
    }

    private function buildData() {
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'email'=>'me@example.com'));
        $builders[] = FixtureBuilder::build('instances', array('id'=>1, 'network_username'=>'someuser1',
        'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('instances', array('id'=>2, 'network_username'=>'someuser2',
        'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('owner_instances', array('instance_id'=>1, 'owner_id'=>1));
        $builders[] = FixtureBuilder::build('posts', array('post_id' => '1', 'author_username'=>'someuser1',
        'post_text'=>'My first post', 'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('posts', array('post_id' => '2', 'author_username'=>'someuser1',
        'post_text'=>'My second post', 'network'=>'twitter'));
        $builders[] = FixtureBuilder::build('posts', array('post_id' => '3', 'author_username'=>'someuser2',
        'post_text'=>'Reply to first post', 'network'=>'twitter', 'in_reply_to_post_id' => '1', 
        'author_user_id'=>'15'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'15', 'network_username'=>'someuser2',
        'network'=>'twitter'));

        return $builders;
    }

    private function buildFacebookData() {
        $builders[] = FixtureBuilder::build('owners', array('id'=>2, 'email'=>'me2@example.com'));
        $builders[] = FixtureBuilder::build('instances', array('id'=>3, 'network_username'=>'someuser1',
        'network'=>'facebook page'));
        $builders[] = FixtureBuilder::build('instances', array('id'=>4, 'network_username'=>'someuser2',
        'network'=>'facebeook page'));
        $builders[] = FixtureBuilder::build('owner_instances', array('instance_id'=>3, 'owner_id'=>2));
        $builders[] = FixtureBuilder::build('posts', array('post_id' => '1', 'author_username'=>'someuser1',
        'post_text'=>'My first Facebook post', 'network'=>'facebook page'));
        $builders[] = FixtureBuilder::build('posts', array('post_id' => '2', 'author_username'=>'someuser1',
        'post_text'=>'My second Facebook post', 'network'=>'facebook page'));
        $builders[] = FixtureBuilder::build('posts', array('post_id' => '3', 'author_username'=>'someuser2',
        'post_text'=>'Reply to first Facebook post', 'network'=>'facebook page', 'in_reply_to_post_id' => '1', 
        'author_user_id'=>'15'));
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'15', 'network_username'=>'someuser2',
        'network'=>'facebook'));

        return $builders;
    }
}
