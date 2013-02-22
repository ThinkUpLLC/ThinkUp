<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/tests/TestOfTwitterRealtimePluginConfigurationController
 *
 * Copyright (c) 2011-2013 Mark Wilkie
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
 * Test of TwitterRealtimePluginConfigurationController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Mark Wilkie
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/model/class.TwitterRealtimePlugin.php';
require_once THINKUP_ROOT_PATH.
'webapp/plugins/twitterrealtime/controller/class.TwitterRealtimePluginConfigurationController.php';

class TestOfTwitterRealtimePluginConfigurationController extends ThinkUpUnitTestCase {
    public function setUp(){
        parent::setUp();
        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        $webapp_plugin_registrar->registerPlugin('twitterrealtime', 'TwitterRealtimePlugin');
    }

    public function tearDown(){
        parent::tearDown();
    }

    public function testConstructor() {
        $controller = new TwitterRealtimePluginConfigurationController(null, 'twitterrealtime');
        $this->assertTrue(isset($controller), 'constructor test');
    }

    public function testConfigOptionsNotAdmin() {
        // build some options data
        $options_arry = $this->buildPluginData();
        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new TwitterRealtimePluginConfigurationController($owner, 'twitterrealtime');
        $output = $controller->go();
        $this->debug($output);
        $this->assertPattern('/var is_admin = false/', $output); // not a js admin
        $controller = new TwitterRealtimePluginConfigurationController($owner, 'twitterrealtime');
        $output = $controller->go();
        $this->assertPattern('/var required_values_set = false/', $output); // is not configured
    }

    public function testConfigOptionsIsAdmin() {
        // build some options data
        $builders = $this->buildPluginData();
        $this->simulateLogin('me@example.com', true);
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new TwitterRealtimePluginConfigurationController($owner, 'twitterrealtime');
        $output = $controller->go();
        $this->assertPattern('/Save Settings/', $output); // should have no submit option
        $this->assertPattern('/php_path/', $output);
        $this->assertPattern('/redis/', $output); // should have secret option
    }

    private function buildPluginData() {
        $builder_owner = FixtureBuilder::build('owners', array('email' => 'me@example.com', 'user_activated' => 1) );
        $builder_plugin = FixtureBuilder::build('plugins', array('folder_name'=>'twitterrealtime', 'is_active'=>1) );

        $namespace = OptionDAO::PLUGIN_OPTIONS . '-1';
        //        $plugin_options1 =
        //        FixtureBuilder::build('options',
        //        array('namespace' => $namespace, 'option_name' => 'oauth_consumer_key', 'option_value' => "1234") );
        //        $plugin_options2 =
        //        FixtureBuilder::build('options',
        //        array('namespace' => $namespace, 'option_name' => 'oauth_consumer_secret', 'option_value' => "12345") );
        $plugin_options3 =
        FixtureBuilder::build('options',
        array('namespace' => $namespace, 'option_name' => 'num_twitter_errors', 'option_value' => "5") );
        return array($builder_owner, $builder_plugin, $plugin_options3);
    }
}
