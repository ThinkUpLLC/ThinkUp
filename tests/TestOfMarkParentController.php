<?php
/**
 *
 * ThinkUp/tests/TestOfMarkParentController.php
 *
 * Copyright (c) 2009-2011 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
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
 * @copyright 2009-2010 Gina Trapani
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfMarkParentController extends ThinkUpUnitTestCase {

    public function __construct() {
        $this->UnitTestCase('MarkParentController class test');
    }

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $controller = new MarkParentController(true);
        $this->assertTrue(isset($controller));
    }

    public function testNotLoggedIn() {
        $controller = new MarkParentController(true);
        $results = $controller->go();
        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('You must <a href="'.$config->getValue('site_root_path').
        'session/login.php">log in</a> to do this.', $v_mgr->getTemplateDataItem('errormsg'));
    }

    public function testMissingParams() {
        $this->simulateLogin('me@example.com');

        $controller = new MarkParentController(true);
        $results = $controller->go();
        $this->assertEqual($results, 'Missing required parameters.', $results);
    }

    public function testSuccessfulAssignment() {
        $this->simulateLogin('me@example.com');

        $builders = $this->buildPosts();

        $post_dao = DAOFactory::getDAO('PostDAO');
        $post = $post_dao->getPost(1, 'twitter');
        $this->assertEqual($post->in_reply_to_post_id, 0);

        $_GET["t"] = 'post.index.tpl';
        $_GET["ck"] = 'cachekey';
        $_GET["pid"] = 11;
        $_GET["oid"] = array(1);
        $_GET['n'] = 'twitter';

        $controller = new MarkParentController(true);
        $results = $controller->go();
        $this->assertPattern('/Assignment successful./', $results);

        $post = $post_dao->getPost(1, 'twitter');
        $this->assertEqual($post->in_reply_to_post_id, 11);

        // On second try, nothing changes
        $results = $controller->go();
        $this->assertPattern('/No data was changed./', $results);
    }

    private function buildPosts() {
        $parent_builder = FixtureBuilder::build('posts',
        array("post_id"=>1, 'network'=>'twitter', 'in_reply_to_post_id'=>0));
        $orphan_builder = FixtureBuilder::build('posts',
        array("post_id"=>11, 'network'=>'twitter', 'in_reply_to_post_id'=>0));
        return array($parent_builder, $orphan_builder);
    }
}