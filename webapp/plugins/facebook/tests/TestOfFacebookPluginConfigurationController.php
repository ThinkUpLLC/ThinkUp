<?php
/**
 *
 * ThinkUp/webapp/plugins/facebook/tests/TestOfFacebookPluginConfigurationController.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Guillaume Boudreau
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
 * Test of FacebookPluginConfigurationController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Guillaume Boudreau
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
if ( !isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS ) {
    require_once '../../../../tests/config.tests.inc.php';
}
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/facebook/model/class.FacebookPlugin.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/facebook/controller/class.FacebookPluginConfigurationController.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/facebook/tests/classes/mock.FacebookGraphAPIAccessor.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/facebook/tests/classes/mock.facebook.php';

class TestOfFacebookPluginConfigurationController extends ThinkUpUnitTestCase {

    /**
     * Data fixture builders
     * @var array
     */
    var $builders;
    /**
     * Constructor
     */
    public function __construct() {
        $this->UnitTestCase('FacebookPluginConfigurationController class test');
    }

    /**
     * Setup
     */
    public function setUp(){
        parent::setUp();
        $this->builders = array();

        $webapp = Webapp::getInstance();
        $webapp->registerPlugin('facebook', 'FacebookPlugin');

        //Add owner
        $owner_builder = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User',
        'email'=>'me@example.com', 'is_activated'=>1));
        array_push($this->builders, $owner_builder);

        //Add instance
        $instance_builder = FixtureBuilder::build('instances', array('id'=>1, 'network_user_id'=>606837591,
        'network_username'=>'Gina Trapani', 'network'=>'facebook', 'is_active'=>1));
        array_push($this->builders, $instance_builder);

        //Add owner instance_owner
        $owner_instance_builder = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>1));
        array_push($this->builders, $owner_instance_builder);

        //Add second owner
        $owner2_builder = FixtureBuilder::build('owners', array('id'=>2, 'full_name'=>'ThinkUp J. User 2',
        'email'=>'me2@example.com', 'is_activated'=>1));
        array_push($this->builders, $owner2_builder);

        //Add second instance
        $instance2_builder = FixtureBuilder::build('instances', array('id'=>2, 'network_user_id'=>668406218,
        'network_username'=>'Penelope Caridad', 'network'=>'facebook', 'is_active'=>1));
        array_push($this->builders, $instance2_builder);

        //Add second owner instance_owner
        $owner_instance2_builder = FixtureBuilder::build('owner_instances', array('owner_id'=>2, 'instance_id'=>2));
        array_push($this->builders, $owner_instance2_builder);

        $_SERVER['SERVER_NAME'] = 'dev.thinkup.com';
        $_SERVER['HTTP_HOST'] = 'http://';
        $_SERVER['REQUEST_URI'] = '';
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    /**
     * Test constructor
     */
    public function testConstructor() {
        $controller = new FacebookPluginConfigurationController(null);
        $this->assertTrue(isset($controller), 'constructor test');
    }

    public function testConfigNotSet() {
        $plugin_options_dao = DAOFactory::getDAO("PluginOptionDAO");
        PluginOptionMySQLDAO::$cached_options = array();
        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new FacebookPluginConfigurationController($owner);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('errormsg'),
        'Please set your Facebook API key, application ID and secret.');
    }

    /**
     * Test output
     */
    public function testOutputNoParams() {
        //not logged in, no owner set
        $builders = $this->buildPluginOptions();
        $controller = new FacebookPluginConfigurationController(null);
        $output = $controller->go();
        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('You must <a href="'.$config->getValue('site_root_path').
        'session/login.php">log in</a> to do this.', $v_mgr->getTemplateDataItem('errormsg'));

        //logged in
        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new FacebookPluginConfigurationController($owner);
        $output = $controller->go();
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('owner_instances'), 'array', 'Owner instances set');
        $this->assertTrue($v_mgr->getTemplateDataItem('fbconnect_link') != '', 'Authorization link set');
    }

    /**
     * Test config not admin
     */
    public function testConfigOptionsNotAdmin() {
        // build some options data
        $options_arry = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new FacebookPluginConfigurationController($owner, 'facebook');
        $output = $controller->go();
        // we have a text form element with proper data
        $this->assertNoPattern('/save options/', $output); // should have no submit option
        $this->assertNoPattern('/plugin_options_error_facebook_api_key/', $output); // should have no api key
        $this->assertNoPattern('/plugin_options_error_message_facebook_api_secret/', $output); // no secret
        $this->assertPattern('/var is_admin = false/', $output); // not a js admin
        $this->assertPattern('/var required_values_set = true/', $output); // is configured

        //app not configured
        $options_arry[0]->truncateTable('plugin_options');
        $controller = new FacebookPluginConfigurationController($owner, 'facebook');
        $output = $controller->go();
        $this->assertPattern('/var required_values_set = false/', $output); // is not configured
    }

    /**
     * Test config isa admin
     */
    public function testConfigOptionsIsAdmin() {
        // build some options data
        $options_arry = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com', true);
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new FacebookPluginConfigurationController($owner, 'facebook');
        $output = $controller->go();
        // we have a text form element with proper data
        $this->assertPattern('/save options/', $output); // should have submit option
        $this->assertPattern('/plugin_options_error_facebook_api_key/', $output); // should have api key option
        $this->assertPattern('/plugin_options_error_message_facebook_api_secret/', $output); // secret option
        $this->assertPattern('/var is_admin = true/', $output); // is a js admin
        $this->assertPattern('/var required_values_set = true/', $output); // is configured

        //app not configured
        $options_arry[0]->truncateTable('plugin_options');
        $controller = new FacebookPluginConfigurationController($owner, 'facebook');
        $output = $controller->go();
        $this->assertPattern('/var required_values_set = false/', $output); // is not configured
    }

    public function testConfiguredPluginWithOneFacebookUserWithSeveralLikedPages() {
        // build some options data
        $options_arry = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com', true);
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new FacebookPluginConfigurationController($owner, 'facebook');
        $output = $controller->go();

        //The mock API accessor reads the page likes JSON from the testdata/606837591_likes file
        $v_mgr = $controller->getViewManager();
        $liked_pages = $v_mgr->getTemplateDataItem('user_pages');
        $this->assertIsA($liked_pages, 'Array');
        $this->assertEqual($liked_pages[606837591][0]->name, 'jenny o.');
        $this->assertIsA($v_mgr->getTemplateDataItem('owner_instance_pages'), 'Array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('owner_instance_pages')), 0);
        $this->assertIsA($v_mgr->getTemplateDataItem('owner_instances'), 'Array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('owner_instances')), 1);
        $this->assertPattern("/The Wire/", $output);
        $this->assertPattern("/Glee/", $output);
        $this->assertPattern("/Brooklyn, New York/", $output);
    }

    public function testConfiguredPluginWithOneFacebookUserNoLikedPages() {
        // build some options data
        $options_arry = $this->buildPluginOptions();
        $this->simulateLogin('me2@example.com', true);
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new FacebookPluginConfigurationController($owner, 'facebook');
        $output = $controller->go();

        //The mock API accessor reads the page likes JSON from the testdata/668406218_likes file
        $v_mgr = $controller->getViewManager();
        $liked_pages = $v_mgr->getTemplateDataItem('user_pages');
        $this->assertIsA($liked_pages, 'Array');
        $this->assertEqual(sizeof($liked_pages), 0);
        $this->assertIsA($v_mgr->getTemplateDataItem('owner_instance_pages'), 'Array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('owner_instance_pages')), 0);
        $this->assertIsA($v_mgr->getTemplateDataItem('owner_instances'), 'Array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('owner_instances')), 1);
    }

    /**
     * build plugin option values
     */
    private function buildPluginOptions() {
        $plugin1 = FixtureBuilder::build('plugins', array('id'=>2, 'folder_name'=>'facebook'));
        $plugin_opt1 = FixtureBuilder::build('plugin_options',
        array('plugin_id' => 2, 'option_name' => 'facebook_api_key', 'option_value' => "dummy_key") );
        $plugin_opt2 = FixtureBuilder::build('plugin_options',
        array('plugin_id' => 2, 'option_name' => 'facebook_api_secret', 'option_value' => "dummy_secret") );
        $plugin_opt3 = FixtureBuilder::build('plugin_options',
        array('plugin_id' => 2, 'option_name' => 'facebook_app_id', 'option_value' => "12345") );
        return array($plugin1, $plugin_opt1, $plugin_opt2, $plugin_opt3);
    }
}
