<?php
/**
 *
 * ThinkUp/tests/TestOfGridController.php
 *
 * Copyright (c) 2009-2013 Mark Wilkie, Gina Trapani, Guillaume Boudreau
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
 * @author Mark Wilkie <mark[at]bitterpill[dot]org>
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Guillaume Boudreau <gboudreau[at]pommepause[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Mark Wilkie, Gina Trapani, Guillaume Boudreau
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfGridController extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        $webapp_plugin_registrar->registerPlugin('twitter', 'TwitterPlugin');
    }

    public function tearDown() {
        parent::tearDown();
        GridController::$MAX_ROWS = 5000;
    }

    public function testConstructor() {
        $controller = new GridController(true);
        $this->assertTrue(isset($controller));
    }

    public function testNotLoggedIn() {
        $controller = new GridController(true);
        $results = $controller->go();
        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('You must <a href="'.$config->getValue('site_root_path').
        'session/login.php">log in</a> to do this.', $v_mgr->getTemplateDataItem('error_msg'));
    }

    public function testMissingParams() {
        $this->simulateLogin('me@example.com');
        $controller = new GridController(true);
        ob_start();
        $controller->control();
        $results = ob_get_contents();
        ob_end_clean();
        $ob = json_decode($results);
        $this->assertEqual($ob->status, 'failed');
        $this->assertEqual($ob->message, 'Missing Parameters');
    }

    public function testNonExistentUser() {
        $this->simulateLogin('me@example.com');
        $_GET['u'] = 'idontexist';
        $_GET['n'] = 'idontexist';
        $controller = new GridController(true);

        ob_start();
        $controller->control();
        $results = ob_get_contents();
        ob_end_clean();
        $ob = json_decode($results);
        $this->assertEqual($ob->status, 'failed');
        $this->assertEqual($ob->message, 'idontexistis not configured.');
    }

    public function testOwnerWithoutAccess() {
        $builders = $this->buildData();
        $this->simulateLogin('me@example.com');
        $_GET['u'] = 'someuser2';
        $_GET['n'] = 'twitter';
        ob_start();
        $controller = new GridController(true);
        $controller->control();
        $results = ob_get_contents();
        ob_end_clean();
        $ob = json_decode($results);
        $this->assertEqual($ob->status, 'failed');
        $this->assertEqual($ob->message, 'Insufficient privileges.');
    }

    public function testOwnerWithAccessTweetsAll() {
        $builders = $this->buildData();
        $this->simulateLogin('me@example.com');
        $_GET['u'] = 'someuser1';
        $_GET['n'] = 'twitter';
        $_GET['d'] = 'tweets-all';
        $controller = new GridController(true);
        $this->assertTrue(isset($controller));
        ob_start();
        $controller->control();
        $results = ob_get_contents();
        ob_end_clean();
        $json = substr($results, 29, strrpos($results, ';') - 30);
        $ob = json_decode( $json );
        $this->assertEqual($ob->status, 'success');
        $this->assertEqual(count($ob->posts), 3);
    }

    public function testOwnerWithAccessTweetsAllMaxLimit() {
        $builders = $this->buildData();
        GridController::$MAX_ROWS = 1;
        $this->simulateLogin('me@example.com');
        $_GET['u'] = 'someuser1';
        $_GET['n'] = 'twitter';
        $_GET['d'] = 'tweets-all';
        $controller = new GridController(true);
        $this->assertTrue(isset($controller));
        ob_start();
        $controller->control();
        $results = ob_get_contents();
        ob_end_clean();
        $json = substr($results, 29, strrpos($results, ';') - 30);
        $ob = json_decode( $json );
        $this->assertEqual($ob->status, 'success');
        $this->assertEqual(count($ob->posts), 2);
    }

    public function testOwnerWithAccessTweetsAllMaxNoLimit() {
        $builders = $this->buildData();
        GridController::$MAX_ROWS = 0;
        $this->simulateLogin('me@example.com');
        $_GET['u'] = 'someuser1';
        $_GET['n'] = 'twitter';
        $_GET['d'] = 'tweets-all';
        $_GET['nolimit'] = '1';
        $controller = new GridController(true);
        $this->assertTrue(isset($controller));
        ob_start();
        $controller->control();
        $results = ob_get_contents();
        ob_end_clean();
        $json = substr($results, 29, strrpos($results, ';') - 30);
        $ob = json_decode( $json );
        $this->assertEqual($ob->status, 'success');
        $this->assertEqual(count($ob->posts), 3);
    }

    public function testReplyToSearch() {
        $builders = $this->buildData(0,0);
        $this->simulateLogin('me@example.com');
        $_GET['u'] = 'someuser1';
        $_GET['n'] = 'twitter';
        $_GET['t'] = '10765432100123456781';
        $controller = new GridController(true);
        $this->assertTrue(isset($controller));
        ob_start();
        $controller->control();
        $results = ob_get_contents();
        ob_end_clean();
        $json = substr($results, 29, strrpos($results, ';') - 30);
        $ob = json_decode( $json );
        $this->assertEqual($ob->status, 'success');
        $this->assertEqual(count($ob->posts), 2);
        $this->assertEqual($ob->posts[0]->text, 'Reply to a post');
        $this->assertEqual($ob->posts[0]->post_id_str, '10765432100123456783_str');

    }

    public function testReplyToSearchNotLoggedIn() {
        $builders = $this->buildData();
        //$this->simulateLogin('me@example.com');
        $_GET['u'] = 'someuser1';
        $_GET['n'] = 'twitter';
        $_GET['t'] = '10765432100123456781';

        // a private instance
        $controller = new GridController(true);
        try {
            $controller->control();
            $this->fail("should throw auth exception");
        } catch(Exception $e) {
            $this->assertPattern('/You must.*log in/',$e->getMessage());
        }

        // public instance, but protected posts
        $builders = null;
        $public = 1;
        $builders = $this->buildData($public);
        $this->assertTrue(isset($controller));
        ob_start();
        $controller->control();
        $results = ob_get_contents();
        ob_end_clean();
        $json = substr($results, 29, strrpos($results, ';') - 30);
        $ob = json_decode( $json );
        $this->assertEqual($ob->status, 'success');
        $this->assertEqual(count($ob->posts), 1);

        // public instance, and not protected posts
        $builders = null;
        $public = 1;
        $protected = 0;
        $builders = $this->buildData($public, $protected);
        $this->assertTrue(isset($controller));
        ob_start();
        $controller->control();
        $results = ob_get_contents();
        ob_end_clean();
        $json = substr($results, 29, strrpos($results, ';') - 30);
        $ob = json_decode( $json );
        $this->assertEqual($ob->status, 'success');
        $this->assertEqual(count($ob->posts), 2);
        $this->assertEqual($ob->posts[0]->text, 'Reply to a post');
        $this->assertEqual($ob->posts[0]->post_id_str, '10765432100123456783_str');

    }

    public function testReplyToSearchFilterOutProtected() {
        $builders = $this->buildData();
        $this->simulateLogin('me@example.com');
        $_GET['u'] = 'someuser1';
        $_GET['n'] = 'twitter';
        $_GET['t'] = '10765432100123456781';
        $controller = new GridController(true);
        $this->assertTrue(isset($controller));
        ob_start();
        $controller->control();
        $results = ob_get_contents();
        ob_end_clean();
        $json = substr($results, 29, strrpos($results, ';') - 30);
        $ob = json_decode( $json );
        $this->assertEqual($ob->status, 'success');
        $this->assertEqual(count($ob->posts), 1);
    }

    public function testNoProfilerOutput() {
        // Enable profiler
        $config = Config::getInstance();
        $config->setValue('enable_profiler', true);
        $_SERVER['HTTP_HOST'] = 'something';

        $builders = $this->buildData();
        $this->simulateLogin('me@example.com');
        $_GET['u'] = 'someuser1';
        $_GET['n'] = 'twitter';
        $_GET['d'] = 'tweets-all';
        $controller = new GridController(true);
        $this->assertTrue(isset($controller));

        ob_start();
        $results = $controller->go();
        $results .= ob_get_contents();
        ob_end_clean();
        $json = substr($results, 29, strrpos($results, ';') - 30);
        $ob = json_decode($json);
        // If the profiler outputs HTML (it shouldn't), the following will fail
        $this->assertIsA($ob, 'stdClass');
        unset($_SERVER['HTTP_HOST']);
    }

    private function buildData($is_public = 0, $is_protected = 1) {
        $owner_builder = FixtureBuilder::build('owners', array('id'=>1, 'email'=>'me@example.com'));

        $user_builder = FixtureBuilder::build('users', array('user_id'=>123, 'user_name'=>'someuser1',
        'network'=>'twitter'));

        $user_builder2 = FixtureBuilder::build('users', array('user_id'=>1234, 'user_name'=>'someuser2',
        'network'=>'twitter'));

        $instance_builder = FixtureBuilder::build('instances', array('id'=>1, 'network_username'=>'someuser1',
        'network'=>'twitter', 'network_user_id' => 123, 'is_public' => $is_public));

        $instance_builder2 = FixtureBuilder::build('instances', array('id'=>2, 'network_username'=>'someuser2',
        'network'=>'twitter'));

        $owner_instance_builder = FixtureBuilder::build('owner_instances', array('instance_id'=>1, 'owner_id'=>1));

        $posts1_builder = FixtureBuilder::build('posts', array('author_username'=>'someuser1','author_user_id' => 123,
        'post_text'=>'@someuser1 My first post', 'network'=>'twitter', 'post_id' => '10765432100123456781'));

        $posts2_builder = FixtureBuilder::build('posts', array('author_username'=>'someuser1','author_user_id' => 123,
        'post_text'=>'My second @someuser1 post', 'network'=>'twitter', 'post_id' => '10765432100123456782'));

        $reply_builder = FixtureBuilder::build('posts', array('post_id' => '10765432100123456783',
        'author_username'=>'reply_user', 'post_text'=>'Reply to a post', 'network'=>'twitter',
        'in_reply_to_post_id' => '10765432100123456781', 'author_user_id'=>'1234','is_protected' => $is_protected));

        return array($owner_builder, $instance_builder, $owner_instance_builder, $posts1_builder,
        $posts2_builder, $user_builder, $user_builder2, $instance_builder2, $reply_builder);
    }
}