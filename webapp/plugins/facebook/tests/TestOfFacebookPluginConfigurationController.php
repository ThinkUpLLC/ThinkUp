<?php
/**
 *
 * ThinkUp/webapp/plugins/facebook/tests/TestOfFacebookPluginConfigurationController.php
 *
 * Copyright (c) 2009-2011 Gina Trapani, Guillaume Boudreau
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
 * @copyright 2009-2011 Gina Trapani, Guillaume Boudreau
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
require_once 'tests/init.tests.php';
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
        $owner_instance_builder = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>1,
        'oauth_access_token'=>'faux-access-token1'));
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
        $owner_instance2_builder = FixtureBuilder::build('owner_instances', array('owner_id'=>2, 'instance_id'=>2,
        'oauth_access_token'=>'faux-access-token2'));
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
        $this->assertEqual($v_mgr->getTemplateDataItem('error_msg'),
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
        'session/login.php">log in</a> to do this.', $v_mgr->getTemplateDataItem('error_msg'));

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
        $namespace = OptionDAO::PLUGIN_OPTIONS . '-2';
        $prefix = Config::getInstance()->getValue('table_prefix');
        OwnerMysqlDAO::$PDO->query("delete from " . $prefix . "options where namespace = '$namespace'");
        $controller = new FacebookPluginConfigurationController($owner, 'facebook');
        $output = $controller->go();
        $this->assertPattern('/var required_values_set = false/', $output); // is not configured
    }

    /**
     * Test config is a admin
     */
    public function testConfigOptionsIsAdmin() {
        // build some options data
        $options_arry = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com', true);
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new FacebookPluginConfigurationController($owner, 'facebook');
        $output = $controller->go();

        $this->debug($output);

        // we have a text form element with proper data
        $this->assertPattern('/save options/', $output); // should have submit option
        $this->assertPattern('/plugin_options_error_facebook_api_key/', $output); // should have api key option
        $this->assertPattern('/plugin_options_error_message_facebook_api_secret/', $output); // secret option
        $this->assertPattern('/var is_admin = true/', $output); // is a js admin
        $this->assertPattern('/var required_values_set = true/', $output); // is configured

        //app not configured
        $namespace = OptionDAO::PLUGIN_OPTIONS . '-2';
        $prefix = Config::getInstance()->getValue('table_prefix');
        OwnerMysqlDAO::$PDO->query("delete from " . $prefix . "options where namespace = '$namespace'");
        $controller = new FacebookPluginConfigurationController($owner, 'facebook');
        $output = $controller->go();
        $this->assertPattern('/var required_values_set = false/', $output); // is not configured
    }

    /**
     * Test SSL links
     */
    public function testConfigOptionsIsAdminWithSSL() {
        // build some options data
        $_SERVER['HTTPS'] = true;
        $options_arry = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com', true);
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new FacebookPluginConfigurationController($owner, 'facebook');
        $output = $controller->go();

        $this->debug($output);

        $expected_pattern = '/Set the Web Site &gt; Site URL to <pre>https:\/\//';
        $this->assertPattern($expected_pattern, $output);
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
        $this->assertNull($v_mgr->getTemplateDataItem('owner_instance_pages'));
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
        $this->assertNull($v_mgr->getTemplateDataItem('owner_instance_pages'), 'Array');
        $this->assertIsA($v_mgr->getTemplateDataItem('owner_instances'), 'Array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('owner_instances')), 1);
    }

    /**
     * build plugin option values
     */
    private function buildPluginOptions() {
        $plugin1 = FixtureBuilder::build('plugins', array('id'=>2, 'folder_name'=>'facebook'));
        $namespace = OptionDAO::PLUGIN_OPTIONS . '-2';
        $plugin_opt1 = FixtureBuilder::build('options',
        array('namespace' => $namespace, 'option_name' => 'facebook_api_key', 'option_value' => "dummy_key") );
        $plugin_opt2 = FixtureBuilder::build('options',
        array('namespace' => $namespace, 'option_name' => 'facebook_api_secret', 'option_value' => "dummy_secret") );
        $plugin_opt3 = FixtureBuilder::build('options',
        array('namespace' => $namespace, 'option_name' => 'facebook_app_id', 'option_value' => "12345") );
        return array($plugin1, $plugin_opt1, $plugin_opt2, $plugin_opt3);
    }

    public function testAddPage() {
        $instance_dao = new InstanceMySQLDAO();
        $owner_instance_dao = new OwnerInstanceMySQLDAO();
        $owner_dao = new OwnerMySQLDAO();

        //page doesn't exist
        $_GET['action'] = 'add page';
        $_GET['instance_id'] = 1;
        $_GET['viewer_id'] = '606837591';
        $_GET['facebook_page_id'] = '162504567094163';
        $_GET['p'] = 'facebook';
        $_GET['owner_id'] = '';

        $options_arry = $this->buildPluginOptions();
        $this->simulateLogin('me2@example.com', true);
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new FacebookPluginConfigurationController($owner, 'facebook');
        $output = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('owner_instances'), 'array', 'Owner instances set');
        $this->assertTrue($v_mgr->getTemplateDataItem('fbconnect_link') != '', 'Authorization link set');
        $this->assertEqual($v_mgr->getTemplateDataItem('success_msg'), 'Success! Your Facebook page has been added.');
        $this->assertEqual($v_mgr->getTemplateDataItem('error_msg'), null, $v_mgr->getTemplateDataItem('error_msg'));
        $instance = $instance_dao->getByUserIdOnNetwork('162504567094163', 'facebook page');
        $this->assertTrue(isset($instance));
        $this->assertEqual($instance->id, 3);
        $oinstance = $owner_instance_dao->get( $owner->id, 3);
        $this->assertTrue(isset($oinstance));

        //page exists
        $controller = new FacebookPluginConfigurationController($owner, 'facebook');
        $output = $controller->go();
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('success_msg'), null);
        $this->assertEqual($v_mgr->getTemplateDataItem('error_msg'), 'This Facebook Page is already in ThinkUp.',
        $v_mgr->getTemplateDataItem('error_msg'));
    }

    public function testReconnectAccount()  {
        $owner_instance_dao = new OwnerInstanceMySQLDAO();
        $instance_dao = new InstanceMySQLDAO();
        $owner_dao = new OwnerMySQLDAO();

        $_GET['p'] = 'facebook';
        $_GET['perms'] = 'offline_access,read_stream,user_likes,user_location,user_website,read_friendlists';
        $_GET['selected_profiles'] = '606837591';
        $_GET['session'] = '{"session_key":"new-faux-access-token","uid":"606837591","expires":0,"secret":'.
        '"itsasecret","access_token":"new-faux-access-token","sig":"siggysigsig"}';

        $options_arry = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com', true);
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new FacebookPluginConfigurationController($owner, 'facebook');
        $output = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('success_msg'), "Success! You've reconnected your Facebook ".
        "account.");

        $instance = $instance_dao->getByUserIdOnNetwork('606837591', 'facebook');
        $this->assertTrue(isset($instance));

        $oinstance = $owner_instance_dao->get($owner->id, $instance->id);
        $this->assertTrue(isset($oinstance));
        $this->assertEqual($oinstance->oauth_access_token, 'new-faux-access-token');
    }

    /**
     * Test csrf token
     */
    public function testForDeleteCSRFToken() {
        $owner_instance_dao = new OwnerInstanceMySQLDAO();
        $instance_dao = new InstanceMySQLDAO();
        $owner_dao = new OwnerMySQLDAO();

        $options_arry = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com', true, true);
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new FacebookPluginConfigurationController($owner, 'facebook');

        // add mock page data to view
        $owner_instance_pages = array(
            '123456' => 
                array(
                'id' => '123456',
                'network_username' => 'test_username',
                'network' => 'facebook',
                )
        );
        $view = $controller->getViewManager();
        $view->assign('owner_instance_pages', $owner_instance_pages);

        $output = $controller->go();
        // looks for account delete token
        $this->assertPattern('/name="csrf_token" value="'. self::CSRF_TOKEN . '" \/><!\-\- delete account csrf token \-\->/', $output);

        // looks for page delete token
        $this->assertPattern('/name="csrf_token" value="'. self::CSRF_TOKEN . '" \/><!\-\- delete page csrf token \-\->/', $output);
    }

}
