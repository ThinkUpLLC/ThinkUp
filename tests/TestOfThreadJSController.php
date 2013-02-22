<?php
/**
 *
 * ThinkUp/webapp/plugins/embedthread/tests/TestOfThreadJSController.php
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 */
require_once dirname(__FILE__) . '/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfThreadJSController extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        $webapp_plugin_registrar->registerPlugin('embedthread', 'EmbedThreadPlugin');
    }

    //test plugin not enabled
    public function testPluginDisabled() {
        $fixture = FixtureBuilder::build('options', array('namespace'=>'application_options',
        'option_name'=>'is_embed_disabled', 'option_value'=>'true'));

        $controller = new ThreadJSController(true);
        $results = $controller->go();
        $v_mgr = $controller->getViewManager();

        $this->assertPattern('/ThinkUp embedding is not enabled./', $results);
        $this->debug($results);
    }

    //test missing parameters
    public function testMissingParameters() {
        $b = $this->activateEmbedThread();
        $plugin_dao = new PluginMySQLDAO();
        $plugin_id = $plugin_dao->getPluginId('embedthread');
        $this->debug(($plugin_dao->isPluginActive($plugin_id))?"EmbedThread is active":"Not active");
        $controller = new ThreadJSController(true);
        $results = $controller->go();
        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertPattern('/No ThinkUp thread specified./', $results);
        $this->debug($results);
    }

    //test nonexistent post ID
    public function testNonexistentPost() {
        $b = $this->activateEmbedThread();
        $_GET['p'] = 1001;
        $_GET['n'] = 'YouFace';
        $plugin_dao = new PluginMySQLDAO();
        $plugin_id = $plugin_dao->getPluginId('embedthread');
        $this->debug(($plugin_dao->isPluginActive($plugin_id))?"EmbedThread is active":"Not active");
        $controller = new ThreadJSController(true);
        $results = $controller->go();
        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertPattern('/Post does not exist/', $results);
        $this->debug($results);
    }

    //test protected post
    public function testProtectedPost() {
        $b = $this->activateEmbedThread();
        $post_builder = FixtureBuilder::build('posts', array('post_id'=>'1001', 'author_user_id'=>'10',
        'author_username'=>'ev', 'post_text'=>'This is a test post', 'retweet_count_cache'=>'5', 
        'network'=>'twitter', 'is_protected'=>1));
        $user_builder = FixtureBuilder::build('users', array('user_id'=>'10', 'username'=>'ev', 'is_protected'=>'1',
        'network'=>'twitter'));

        $_GET['p'] = 1001;
        $_GET['n'] = 'twitter';
        $plugin_dao = new PluginMySQLDAO();
        $plugin_id = $plugin_dao->getPluginId('embedthread');
        $this->debug(($plugin_dao->isPluginActive($plugin_id))?"EmbedThread is active":"Not active");
        $controller = new ThreadJSController(true);
        $results = $controller->go();
        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertPattern('/Private post/', $results);
        $this->debug($results);
    }

    //test valid public post with all public replies
    public function testPublicPostWithAllPublicReplies() {
        $b = $this->activateEmbedThread();
        $post_builder = FixtureBuilder::build('posts', array('post_id'=>'1001', 'author_user_id'=>'10',
        'author_username'=>'ev', 'post_text'=>'This is a test post', 'retweet_count_cache'=>'5', 
        'network'=>'twitter', 'is_protected'=>0));
        $user_builder = FixtureBuilder::build('users', array('user_id'=>'10', 'username'=>'ev', 'is_protected'=>'0',
        'network'=>'twitter'));

        $public_reply_author_builder1 = FixtureBuilder::build('users', array('user_id'=>'11', 'username'=>'jack',
        'is_protected'=>'0', 'network'=>'twitter'));
        $reply_builder1 = FixtureBuilder::build('posts', array('post_id'=>'1002', 'author_user_id'=>'11',
        'author_username'=>'jack', 'post_text'=>'This is a public reply to 1001', 'network'=>'twitter', 
        'in_reply_to_post_id'=>1001, 'is_protected'=>'0'));

        $public_reply_author_builder2 = FixtureBuilder::build('users', array('user_id'=>'12', 'username'=>'jill',
        'is_protected'=>'0', 'network'=>'twitter'));
        $reply_builder2 = FixtureBuilder::build('posts', array('post_id'=>'1003', 'author_user_id'=>'12',
        'author_username'=>'jill', 'post_text'=>'This is another public reply to 1001', 'network'=>'twitter', 
        'in_reply_to_post_id'=>1001, 'is_protected'=>'0'));

        $public_reply_author_builder3 = FixtureBuilder::build('users', array('user_id'=>'13', 'username'=>'mary',
        'is_protected'=>'0', 'network'=>'twitter'));
        $reply_builder3 = FixtureBuilder::build('posts', array('post_id'=>'1004', 'author_user_id'=>'13',
        'author_username'=>'mary', 'post_text'=>'This is yet another public reply to 1001', 'network'=>'twitter', 
        'in_reply_to_post_id'=>1001, 'is_protected'=>0));

        $_GET['p'] = 1001;
        $_GET['n'] = 'twitter';
        $plugin_dao = new PluginMySQLDAO();
        $plugin_id = $plugin_dao->getPluginId('embedthread');
        $this->debug(($plugin_dao->isPluginActive($plugin_id))?"EmbedThread is active":"Not active");
        $controller = new ThreadJSController(true);
        $results = $controller->go();
        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertPattern('/This is a test post/', $results);
        $this->assertPattern('/This is a public reply to 1001/', $results);
        $this->assertPattern('/This is another public reply to 1001/', $results);
        $this->assertPattern('/This is yet another public reply to 1001/', $results);
        $this->debug($results);
    }

    //test valid public post with all protected replies
    public function testPublicPostWithProtectedReplies() {
        $b = $this->activateEmbedThread();
        $post_builder = FixtureBuilder::build('posts', array('post_id'=>'1001', 'author_user_id'=>'10',
        'author_username'=>'ev', 'post_text'=>'This is a test post', 'retweet_count_cache'=>'5', 
        'network'=>'twitter', 'is_protected'=>0));
        $user_builder = FixtureBuilder::build('users', array('user_id'=>'10', 'username'=>'ev', 'is_protected'=>'0',
        'network'=>'twitter'));

        $private_reply_author_builder1 = FixtureBuilder::build('users', array('user_id'=>'13', 'username'=>'mary',
        'is_protected'=>'1', 'network'=>'twitter'));
        $reply_builder3 = FixtureBuilder::build('posts', array('post_id'=>'1004', 'author_user_id'=>'13',
        'author_username'=>'mary', 'post_text'=>'This is a private reply to 1001', 'network'=>'twitter', 
        'in_reply_to_post_id'=>1001, 'is_protected'=>'1'));

        $_GET['p'] = 1001;
        $_GET['n'] = 'twitter';
        $plugin_dao = new PluginMySQLDAO();
        $plugin_id = $plugin_dao->getPluginId('embedthread');
        $this->debug(($plugin_dao->isPluginActive($plugin_id))?"EmbedThread is active":"Not active");
        $controller = new ThreadJSController(true);
        $results = $controller->go();
        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertPattern('/This is a test post/', $results);
        $this->assertNoPattern('/This is a private reply to 1001/', $results);
        $this->debug($results);
    }

    //test valid public post with some protected replies
    public function testPublicPostWithMixedAccessReplies() {
        $b = $this->activateEmbedThread();
        $post_builder = FixtureBuilder::build('posts', array('post_id'=>'1001', 'author_user_id'=>'10',
        'author_username'=>'ev', 'post_text'=>'This is a test post', 'retweet_count_cache'=>'5', 
        'network'=>'twitter', 'is_protected'=>0));
        $user_builder = FixtureBuilder::build('users', array('user_id'=>'10', 'username'=>'ev', 'is_protected'=>'0',
        'network'=>'twitter'));

        $public_reply_author_builder1 = FixtureBuilder::build('users', array('user_id'=>'11', 'username'=>'jack',
        'is_protected'=>'0', 'network'=>'twitter'));
        $reply_builder1 = FixtureBuilder::build('posts', array('post_id'=>'1002', 'author_user_id'=>'11',
        'author_username'=>'jack', 'post_text'=>'This is a public reply to 1001', 'network'=>'twitter', 
        'in_reply_to_post_id'=>1001, 'is_protected'=>'0'));

        $public_reply_author_builder2 = FixtureBuilder::build('users', array('user_id'=>'12', 'username'=>'jill',
        'is_protected'=>'0', 'network'=>'twitter'));
        $reply_builder2 = FixtureBuilder::build('posts', array('post_id'=>'1003', 'author_user_id'=>'12',
        'author_username'=>'jill', 'post_text'=>'This is another public reply to 1001', 'network'=>'twitter', 
        'in_reply_to_post_id'=>1001, 'is_protected'=>'0'));

        $private_reply_author_builder1 = FixtureBuilder::build('users', array('user_id'=>'13', 'username'=>'mary',
        'is_protected'=>'1', 'network'=>'twitter'));
        $reply_builder3 = FixtureBuilder::build('posts', array('post_id'=>'1004', 'author_user_id'=>'13',
        'author_username'=>'mary', 'post_text'=>'This is a private reply to 1001', 'network'=>'twitter', 
        'in_reply_to_post_id'=>1001, 'is_protected'=>'1'));

        $_GET['p'] = 1001;
        $_GET['n'] = 'twitter';
        $plugin_dao = new PluginMySQLDAO();
        $plugin_id = $plugin_dao->getPluginId('embedthread');
        $this->debug(($plugin_dao->isPluginActive($plugin_id))?"EmbedThread is active":"Not active");
        $controller = new ThreadJSController(true);
        $results = $controller->go();
        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertPattern('/This is a test post/', $results);
        $this->assertPattern('/This is a public reply to 1001/', $results);
        $this->assertPattern('/This is another public reply to 1001/', $results);
        $this->assertNoPattern('/This is a private reply to 1001/', $results);
        $this->debug($results);
    }

    private function activateEmbedThread(){
        $builder = FixtureBuilder::build('plugins', array('name'=>'Embed Thread', 'folder_name'=>'embedthread',
        'is_active'=>1));
        return $builder;
    }
}