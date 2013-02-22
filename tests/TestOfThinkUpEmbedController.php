<?php
/**
 *
 * ThinkUp/webapp/plugins/embedthread/tests/TestOfThinkUpEmbedController.php
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

class TestOfThinkUpEmbedController extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function testConstructor() {
        $controller = new ThinkUpEmbedController(true);
        $this->assertTrue(isset($controller));
    }
    //Test missing parameters
    public function testMissingParameters() {
        //missing both query string parameters
        $controller = new ThinkUpEmbedController(true);
        $results = $controller->go();
        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('No ThinkUp thread specified.', $v_mgr->getTemplateDataItem('error_msg'));

        //missing n
        $_GET['p'] = '1001';
        $controller = new ThinkUpEmbedController(true);
        $results = $controller->go();
        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('No ThinkUp thread specified.', $v_mgr->getTemplateDataItem('error_msg'));

        //missing p
        $_GET['p'] = null;
        $_GET['n'] = 'twitter';
        $controller = new ThinkUpEmbedController(true);
        $results = $controller->go();
        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('No ThinkUp thread specified.', $v_mgr->getTemplateDataItem('error_msg'));
    }

    public function testGivenAllParameters() {
        $_SERVER['SERVER_NAME'] = 'mytestthinkup';
        $_GET['p'] = '1001';
        $_GET['n'] = 'twitter';
        $controller = new ThinkUpEmbedController(true);
        $results = $controller->go();
        $v_mgr = $controller->getViewManager();
        $this->assertPattern('/ThinkUp1001 = new function()/', $results, "Javascript embed code returned");
        $this->debug($results);

        $expected_pattern = '/var BASE_URL = \'http:\/\/mytestthinkup/';
        $this->assertPattern($expected_pattern, $results);

        $this->assertPattern('/thinkup_embed_1001/', $results, "Unique div name returned");
    }

    public function testGivenAllParametersWithSSL() {
        $_SERVER['SERVER_NAME'] = 'mytestthinkup';
        $_SERVER['HTTPS'] = true;
        $_GET['p'] = '1001';
        $_GET['n'] = 'twitter';
        $controller = new ThinkUpEmbedController(true);
        $results = $controller->go();
        $v_mgr = $controller->getViewManager();
        $this->assertPattern('/ThinkUp1001 = new function()/', $results, "Javascript embed code returned");
        $this->debug($results);

        $expected_pattern = '/var BASE_URL = \'https:\/\/mytestthinkup/';
        $this->assertPattern($expected_pattern, $results);

        $this->assertPattern('/thinkup_embed_1001/', $results, "Unique div name returned");
    }
}