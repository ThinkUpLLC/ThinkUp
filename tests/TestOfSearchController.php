<?php
/**
 *
 * ThinkUp/tests/TestOfSearchController.php
 *
 * Copyright (c) 2013 Gina Trapani
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
 * Test of SearchController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfSearchController extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testNotLoggedIn() {
        $controller = new SearchController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->go();
        $this->assertPattern('/You must/', $results);
        $this->assertPattern('/log in/', $results);
        $this->assertPattern('/to do this/', $results);
    }

    public function testConstructor() {
        $this->simulateLogin('admin@example.com', true, true);

        $controller = new SearchController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->go();
        $this->assertPattern('/Uh-oh. Your search terms are missing. Please try again/', $results);
    }

    public function testSearchPosts() {
        $this->simulateLogin('admin@example.com', true, true);

        $_GET['c'] = "posts";
        $_GET['u'] = 'ev';
        $_GET['n'] = 'twitter';
        $_GET['q'] = "Apple";
        $controller = new SearchController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->go();
        $this->assertPattern('/No posts found/', $results);
    }

    public function testSearchFollowers() {
        $this->simulateLogin('admin@example.com', true, true);

        $_GET['c'] = "followers";
        $_GET['u'] = 'ev';
        $_GET['n'] = 'twitter';
        $_GET['q'] = "name:Apple";
        $controller = new SearchController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->go();
        $this->assertPattern('/No followers found/', $results);
    }

    protected function buildData() {
        $builders = array();

        //Add owner
        $hashed_pass = ThinkUpTestLoginHelper::hashPasswordUsingDeprecatedMethod("oldpassword");

        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User',
        'email'=>'me@example.com', 'is_activated'=>1, 'pwd'=>$hashed_pass,
        'pwd_salt'=> OwnerMySQLDAO::$default_salt, 'api_key' => 'c9089f3c9adaf0186f6ffb1ee8d6501c'));

        $builders[] = FixtureBuilder::build('owners', array('id'=>2, 'full_name'=>'ThinkUp J. Admin',
        'email'=>'admin@example.com', 'is_activated'=>1, 'is_admin'=>1));

        //Add instance_owner
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>1));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>2, 'instance_id'=>1));

        //Insert test data into test table
        $builders[] = FixtureBuilder::build('users', array('user_id'=>'13', 'user_name'=>'ev',
        'full_name'=>'Ev Williams'));

        //Make public
        //Insert test data into test table
        $builders[] = FixtureBuilder::build('instances', array('id'=>1, 'network_user_id'=>'13',
        'network_username'=>'ev', 'is_public'=>1, 'network'=>'twitter'));

        return $builders;
    }

}