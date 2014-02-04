<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/tests/TestOfTwitterPluginHashtagConfigurationController.php
 *
 * Copyright (c) 2013 Gina Trapani
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
 * Test of TwitterPluginHashtagConfigurationController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
//require_once THINKUP_WEBAPP_PATH.'plugins/twitter/tests/classes/mock.TwitterOAuth.php';
//require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterOAuthThinkUp.php';
//require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterAPIAccessorOAuth.php';
//require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterPlugin.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/controller/class.TwitterPluginHashtagConfigurationController.php';

class TestOfTwitterPluginHashtagConfigurationController extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown(){
        parent::tearDown();
    }

    public function testConstructor() {
        $controller = new TwitterPluginHashtagConfigurationController(null, 'twitter', 'ginatrapani');
        $this->assertTrue(isset($controller), 'constructor test');
    }

    public function testOutput() {
        //not logged in
        $controller = new TwitterPluginHashtagConfigurationController(null, 'twitter', 'ginatrapani');
        $output = $controller->go();
        $this->assertPattern( '/session\/login.php\?redirect\=/', $controller->redirect_destination);

        //logged in, no user set up
        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new TwitterPluginHashtagConfigurationController(null, 'twitter', 'ginatrapani');
        $output = $controller->go();
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('user'), 'string');
        $this->assertEqual('Twitter user @ginatrapani does not exist.', $v_mgr->getTemplateDataItem('error_msg'));

        //logged in, user set up
        $builders = array();
        $builders[] = FixtureBuilder::build('instances', array('network_username'=>'ginatrapani',
        'network'=>'twitter'));
        $controller = new TwitterPluginHashtagConfigurationController(null, 'twitter', 'ginatrapani');
        $output = $controller->go();
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('user'), 'string');
        $this->assertIsA($v_mgr->getTemplateDataItem('hashtags'), 'array');
    }
}
