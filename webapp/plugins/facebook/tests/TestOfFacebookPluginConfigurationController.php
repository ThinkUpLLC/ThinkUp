<?php
/**
 *
 * ThinkUp/webapp/plugins/facebook/tests/TestOfFacebookPluginConfigurationController.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Guillaume Boudreau
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
 * Test of FacebookPluginConfigurationController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Guillaume Boudreau
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'plugins/facebook/model/class.FacebookPlugin.php';
require_once THINKUP_WEBAPP_PATH.'plugins/facebook/controller/class.FacebookPluginConfigurationController.php';
require_once THINKUP_WEBAPP_PATH.'plugins/facebook/tests/classes/mock.FacebookGraphAPIAccessor.php';
require_once THINKUP_WEBAPP_PATH.'plugins/facebook/tests/classes/mock.facebook.php';

class TestOfFacebookPluginConfigurationController extends ThinkUpUnitTestCase {

    /**
     * Data fixture builders
     * @var array
     */
    var $builders;

    public function setUp(){
        parent::setUp();
        $this->builders = array();

        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        $webapp_plugin_registrar->registerPlugin('facebook', 'FacebookPlugin');

        $_SERVER['SERVER_NAME'] = 'dev.thinkup.com';
        $_SERVER['HTTP_HOST'] = 'dev.thinkup.com';
        $_SERVER['REQUEST_URI'] = '';

        //Add owners
        $owner_builder = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User',
        'email'=>'me@example.com', 'is_activated'=>1));
        array_push($this->builders, $owner_builder);

        //Add second owner
        $owner2_builder = FixtureBuilder::build('owners', array('id'=>2, 'full_name'=>'ThinkUp J. User 2',
        'email'=>'me2@example.com', 'is_activated'=>1));
        array_push($this->builders, $owner2_builder);
    }

    private function buildInstanceData() {
        //Add instance
        $instance_builder = FixtureBuilder::build('instances', array('id'=>1, 'network_user_id'=>'606837591',
        'network_username'=>'Gina Trapani', 'network'=>'facebook', 'is_active'=>1));
        array_push($this->builders, $instance_builder);

        //Add owner instance_owner
        $owner_instance_builder = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>1,
        'oauth_access_token'=>'faux-access-token1', 'auth_error'=>'Token has expired.'));
        array_push($this->builders, $owner_instance_builder);

        //Add second instance
        $instance2_builder = FixtureBuilder::build('instances', array('id'=>2, 'network_user_id'=>'668406218',
        'network_username'=>'Penelope Caridad', 'network'=>'facebook', 'is_active'=>1));
        array_push($this->builders, $instance2_builder);

        //Add second owner instance_owner
        $owner_instance2_builder = FixtureBuilder::build('owner_instances', array('owner_id'=>2, 'instance_id'=>2,
        'oauth_access_token'=>'faux-access-token2', 'auth_error'=>''));
        array_push($this->builders, $owner_instance2_builder);
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    public function testConstructor() {
        $controller = new FacebookPluginConfigurationController(null);
        $this->assertNotNull($controller, 'constructor test');
        $this->assertIsA($controller, 'FacebookPluginConfigurationController');
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
        $info = $v_mgr->getTemplateDataItem('info_msgs');
        $this->assertEqual($info['setup'], 'Please complete plugin setup to start using it.');
        $this->debug(Utils::varDumpToString($info));
        //assert configuration URL is showing
        $site_url = $v_mgr->getTemplateDataItem('thinkup_site_url');
        $this->assertEqual($site_url, Utils::getApplicationURL());
    }

    public function testOutputNoParams() {
        self::buildInstanceData();
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
        $this->assertIsA($v_mgr->getTemplateDataItem('instances'), 'array', 'Owner instances set');
        $this->assertTrue($v_mgr->getTemplateDataItem('fbconnect_link') != '', 'Authorization link set');
    }

    public function testConfigOptionsNotAdmin() {
        self::buildInstanceData();
        // build some options data
        $options_array = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new FacebookPluginConfigurationController($owner, 'facebook');
        $output = $controller->go();
        // we have a text form element with proper data
        $this->assertNoPattern('/Pause crawling/', $output);
        $this->assertNoPattern('/Start crawling/', $output);
        $this->assertNoPattern('/Save Settings/', $output); // should have no submit option
        $this->assertNoPattern('/plugin_options_error_facebook_app_id/', $output); // should have no app id
        $this->assertNoPattern('/plugin_options_error_message_facebook_api_secret/', $output); // no secret
        $this->assertNoPattern('/plugin_options_max_crawl_time/', $output); // no advanced option
        $this->assertPattern('/var is_admin = false/', $output); // not a js admin
        $this->assertPattern('/var required_values_set = true/', $output); // is configured

        //app not configured
        $namespace = OptionDAO::PLUGIN_OPTIONS . '-2';
        $prefix = Config::getInstance()->getValue('table_prefix');
        OwnerMySQLDAO::$PDO->query("delete from " . $prefix . "options where namespace = '$namespace'");
        $controller = new FacebookPluginConfigurationController($owner, 'facebook');
        $output = $controller->go();
        $this->assertPattern('/var required_values_set = false/', $output); // is not configured
    }

    public function testConfigOptionsIsAdmin() {
        self::buildInstanceData();
        // build some options data
        $options_array = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com', true);
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new FacebookPluginConfigurationController($owner, 'facebook');
        $output = $controller->go();

        $this->debug($output);

        // we have a text form element with proper data
        $this->assertPattern('/Pause crawling/', $output);
        $this->assertPattern('/Save Settings/', $output); // should have submit option
        $this->assertPattern('/plugin_options_error_message_facebook_api_secret/', $output); // secret option
        $this->assertPattern('/plugin_options_max_crawl_time/', $output); // advanced option
        $this->assertPattern('/var is_admin = true/', $output); // is a js admin
        $this->assertPattern('/var required_values_set = true/', $output); // is configured

        //app not configured
        $namespace = OptionDAO::PLUGIN_OPTIONS . '-2';
        $prefix = Config::getInstance()->getValue('table_prefix');
        OwnerMySQLDAO::$PDO->query("delete from " . $prefix . "options where namespace = '$namespace'");
        $controller = new FacebookPluginConfigurationController($owner, 'facebook');
        $output = $controller->go();
        $this->assertPattern('/var required_values_set = false/', $output); // is not configured
    }

    public function testConfigOptionsIsAdminWithSSL() {
        self::buildInstanceData();
        // build some options data
        $_SERVER['HTTPS'] = true;
        $options_array = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com', true);
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new FacebookPluginConfigurationController($owner, 'facebook');
        $output = $controller->go();

        $this->debug($output);

        $expected_pattern = '/copy and paste this:<br>
    <small>
      <code style="font-family:Courier;" id="clippy_2988">https:\/\//';
        $this->assertPattern($expected_pattern, $output);
    }

    public function testConfiguredPluginWithOneFacebookUserWithSeveralLikedAndManagedPagesWithAuthError() {
        self::buildInstanceData();
        // build some options data
        $options_array = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com', true);
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new FacebookPluginConfigurationController($owner, 'facebook');
        $output = $controller->go();
        $this->debug($output);

        //The mock API accessor reads the page likes JSON from the testdata/606837591_likes file
        $v_mgr = $controller->getViewManager();
        $liked_pages = $v_mgr->getTemplateDataItem('user_pages');
        $this->assertIsA($liked_pages, 'Array');
        $this->assertEqual($liked_pages[606837591][0]->name, 'jenny o.');
        $this->assertNull($v_mgr->getTemplateDataItem('owner_instance_pages'));
        $this->assertIsA($v_mgr->getTemplateDataItem('instances'), 'Array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('instances')), 1);
        $this->assertPattern("/Pages Gina Trapani Likes/", $output);
        $this->assertPattern("/The Wire/", $output);
        $this->assertPattern("/Glee/", $output);
        $this->assertPattern("/Brooklyn, New York/", $output);

        //The mock API accessor reads the page accounts JSON from the testdata/606837591_accounts file
        $managed_pages = $v_mgr->getTemplateDataItem('user_admin_pages');
        $this->assertIsA($managed_pages, 'Array');
        $this->assertEqual($managed_pages[606837591][0]->name, 'Sample Cause');
        $this->assertPattern("/Pages Gina Trapani Manages/", $output);
        $this->assertPattern("/Sample Cause/", $output);

        //with auth error
        $this->assertPattern('/facebook-auth-error"/', $output);

    }

    public function testConfiguredPluginWithOneFacebookUserNoLikedPagesNoAuthError() {
        self::buildInstanceData();
        // build some options data
        $options_array = $this->buildPluginOptions();
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
        $this->assertIsA($v_mgr->getTemplateDataItem('instances'), 'Array');
        $this->assertEqual(sizeof($v_mgr->getTemplateDataItem('instances')), 1);

        //no auth error
        $this->debug($output);
        $this->assertNoPattern('/facebook-auth-error/', $output);
    }

    public function testConfiguredPluginWithOneFacebookUserOneLikedPageOneManagedPage() {
        self::buildInstanceData();
        // build some options data
        $options_array = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com', true);
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new FacebookPluginConfigurationController($owner, 'facebook');
        $output = $controller->go();

        //The mock API accessor reads the page likes JSON from the testdata/668406218_likes file
        $v_mgr = $controller->getViewManager();
        $liked_pages = $v_mgr->getTemplateDataItem('user_pages');
        $this->assertIsA($liked_pages, 'Array');
        $this->assertEqual(sizeof($liked_pages), 1);
        $admin_pages = $v_mgr->getTemplateDataItem('user_admin_pages');
        $this->assertIsA($liked_pages, 'Array');
        $this->assertEqual(sizeof($liked_pages), 1);
        $this->debug($output);
        $this->assertPattern("/Pages Gina Trapani Manage/", $output);
    }

    public function testConfiguredPluginWithOneFacebookUserNoLikedPagesNoManagedPages() {
        self::buildInstanceData();
        // build some options data
        $options_array = $this->buildPluginOptions();
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
        $admin_pages = $v_mgr->getTemplateDataItem('user_admin_pages');
        $this->assertIsA($liked_pages, 'Array');
        $this->assertEqual(sizeof($liked_pages), 0);
        $this->assertNoPattern("/Pages Gina Trapani Manages/", $output);
    }

    private function buildPluginOptions() {
        $namespace = OptionDAO::PLUGIN_OPTIONS . '-2';
        $builders = array();
        $builders[] = FixtureBuilder::build('options',
        array('namespace' => $namespace, 'option_name' => 'facebook_api_secret', 'option_value' => "scrt") );
        $builders[] = FixtureBuilder::build('options',
        array('namespace' => $namespace, 'option_name' => 'facebook_app_id', 'option_value' => "77") );
        return $builders;
    }

    public function testAddPage() {
        self::buildInstanceData();

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

        $options_array = $this->buildPluginOptions();
        $this->simulateLogin('me2@example.com', true);
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new FacebookPluginConfigurationController($owner, 'facebook');
        $output = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('instances'), 'array', 'Owner instances set');
        $this->assertTrue($v_mgr->getTemplateDataItem('fbconnect_link') != '', 'Authorization link set');

        $msgs = $v_mgr->getTemplateDataItem('success_msgs');
        $this->assertEqual($msgs['page_add'], 'Success! Your Facebook page has been added.');
        $this->debug(Utils::varDumpToString($msgs));
        $this->assertEqual($v_mgr->getTemplateDataItem('error_msg'), null, $v_mgr->getTemplateDataItem('error_msg'));
        $instance = $instance_dao->getByUserIdOnNetwork('162504567094163', 'facebook page');
        $this->assertNotNull($instance);
        $this->assertEqual($instance->id, 3);
        $owner_instance = $owner_instance_dao->get( $owner->id, 3);
        $this->assertNotNull($owner_instance);

        //page exists
        $controller = new FacebookPluginConfigurationController($owner, 'facebook');
        $output = $controller->go();
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('success_msg'), null);

        $msgs = $v_mgr->getTemplateDataItem('info_msgs');
        $this->assertEqual($msgs['page_add'], 'This Facebook Page is already in ThinkUp.');
        $this->debug(Utils::varDumpToString($msgs));
    }

    public function testConnectAccountSuccessful()  {
        $owner_instance_dao = new OwnerInstanceMySQLDAO();
        $instance_dao = new InstanceMySQLDAO();
        $owner_dao = new OwnerMySQLDAO();

        $config = Config::getInstance();
        $config->setValue('site_root_path', '/');

        $_SERVER['SERVER_NAME'] = "srvr";
        SessionCache::put('facebook_auth_csrf', '123');
        $_GET['p'] = 'facebook';
        $_GET['code'] = '456';
        $_GET['state'] = '123';

        $options_array = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com', true);

        $instance = $instance_dao->getByUserIdOnNetwork('606837591', 'facebook');
        $this->assertNull($instance); //Instance doesn't exist

        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new FacebookPluginConfigurationController($owner, 'facebook');
        $output = $controller->go();

        $v_mgr = $controller->getViewManager();

        $msgs = $v_mgr->getTemplateDataItem('success_msgs');
        $this->assertEqual($msgs['user_add'], "Success! Your Facebook account has been added to ThinkUp.");
        $this->debug(Utils::varDumpToString($msgs));

        $instance = $instance_dao->getByUserIdOnNetwork('606837591', 'facebook');
        $this->assertNotNull($instance); //Instance created

        $owner_instance = $owner_instance_dao->get($owner->id, $instance->id);
        $this->assertNotNull($owner_instance); //Owner Instance created
        //OAuth token set
        $this->assertEqual($owner_instance->oauth_access_token, 'newfauxaccesstoken11234567890');
    }

    public function testConnectAccountSuccessfulNoServerName()  {
        $owner_instance_dao = new OwnerInstanceMySQLDAO();
        $instance_dao = new InstanceMySQLDAO();
        $owner_dao = new OwnerMySQLDAO();

        $config = Config::getInstance();
        $config->setValue('site_root_path', '/');

        // $_SERVER['SERVER_NAME'] may not be set, depending on web server configuration
        unset($_SERVER['SERVER_NAME']);
        $_SERVER['HTTP_HOST'] = 'srvr';
        $_SERVER['HTTPS'] = true;
        SessionCache::put('facebook_auth_csrf', '123');
        $_GET['p'] = 'facebook';
        $_GET['code'] = '789';
        $_GET['state'] = '123';

        $options_array = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com', true);

        $instance = $instance_dao->getByUserIdOnNetwork('606837591', 'facebook');
        $this->assertNull($instance); //Instance doesn't exist

        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new FacebookPluginConfigurationController($owner, 'facebook');
        $output = $controller->go();

        $v_mgr = $controller->getViewManager();

        $msgs = $v_mgr->getTemplateDataItem('success_msgs');
        $this->assertEqual($msgs['user_add'], "Success! Your Facebook account has been added to ThinkUp.");
        $this->debug(Utils::varDumpToString($msgs));

        $instance = $instance_dao->getByUserIdOnNetwork('606837591', 'facebook');
        $this->assertNotNull($instance); //Instance created

        $owner_instance = $owner_instance_dao->get($owner->id, $instance->id);
        $this->assertNotNull($owner_instance); //Owner Instance created
        //OAuth token set
        $this->assertEqual($owner_instance->oauth_access_token, 'newfauxaccesstoken11234567890');
    }

    public function testConnectAccountHTTPSSuccessful()  {
        $owner_instance_dao = new OwnerInstanceMySQLDAO();
        $instance_dao = new InstanceMySQLDAO();
        $owner_dao = new OwnerMySQLDAO();

        $config = Config::getInstance();
        $config->setValue('site_root_path', '/');

        $_SERVER['SERVER_NAME'] = "srvr";
        $_SERVER['HTTPS'] = 'on';
        SessionCache::put('facebook_auth_csrf', '123');
        $_GET['p'] = 'facebook';
        $_GET['code'] = '789';
        $_GET['state'] = '123';

        $options_array = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com', true);

        $instance = $instance_dao->getByUserIdOnNetwork('606837591', 'facebook');
        $this->assertNull($instance); //Instance doesn't exist

        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new FacebookPluginConfigurationController($owner, 'facebook');
        $output = $controller->go();

        $v_mgr = $controller->getViewManager();

        $msgs = $v_mgr->getTemplateDataItem('success_msgs');
        $this->assertEqual($msgs['user_add'], "Success! Your Facebook account has been added to ThinkUp.");
        $this->debug(Utils::varDumpToString($msgs));

        $instance = $instance_dao->getByUserIdOnNetwork('606837591', 'facebook');
        $this->assertNotNull($instance); //Instance created

        $owner_instance = $owner_instance_dao->get($owner->id, $instance->id);
        $this->assertNotNull($owner_instance); //Owner Instance created
        //OAuth token set
        $this->assertEqual($owner_instance->oauth_access_token, 'newfauxaccesstoken11234567890');
    }

    public function testConnectAccountInvalidCSRFToken()  {
        $owner_instance_dao = new OwnerInstanceMySQLDAO();
        $instance_dao = new InstanceMySQLDAO();
        $owner_dao = new OwnerMySQLDAO();

        $config = Config::getInstance();
        $config->setValue('site_root_path', '/');

        $_SERVER['SERVER_NAME'] = "srvr";
        SessionCache::put('facebook_auth_csrf', '123');
        $_GET['p'] = 'facebook';
        $_GET['code'] = '456';
        $_GET['state'] = 'NOT123';

        $options_array = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com', true);

        $instance = $instance_dao->getByUserIdOnNetwork('606837591', 'facebook');
        $this->assertNull($instance); //Instance doesn't exist

        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new FacebookPluginConfigurationController($owner, 'facebook');
        $output = $controller->go();

        $v_mgr = $controller->getViewManager();

        $msgs = $v_mgr->getTemplateDataItem('error_msgs');
        $this->assertEqual($msgs['authorization'],
        "Could not authenticate Facebook account due to invalid CSRF token.");
        $this->debug(Utils::varDumpToString($msgs));
    }

    public function testConnectAccountThatAlreadyExists()  {
        self::buildInstanceData();

        $owner_instance_dao = new OwnerInstanceMySQLDAO();
        $instance_dao = new InstanceMySQLDAO();
        $owner_dao = new OwnerMySQLDAO();

        $config = Config::getInstance();
        $config->setValue('site_root_path', '/');

        $_SERVER['SERVER_NAME'] = "srvr";
        SessionCache::put('facebook_auth_csrf', '123');
        $_GET['p'] = 'facebook';
        $_GET['code'] = '456';
        $_GET['state'] = '123';

        $options_array = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com', true);
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());

        $instance = $instance_dao->getByUserIdOnNetwork('606837591', 'facebook');
        $this->assertNotNull($instance);

        //assert there is an auth error
        $owner_instance = $owner_instance_dao->get($owner->id, $instance->id);
        $this->assertEqual($owner_instance->auth_error, 'Token has expired.');

        $controller = new FacebookPluginConfigurationController($owner, 'facebook');
        $output = $controller->go();

        $v_mgr = $controller->getViewManager();
        $msgs = $v_mgr->getTemplateDataItem('success_msgs');
        $this->assertEqual($msgs['user_add'], "Success! You've reconnected your Facebook account. To connect ".
        "a different account, log  out of Facebook in a different browser tab and try again.");
        $this->debug(Utils::varDumpToString($msgs));

        $instance = $instance_dao->getByUserIdOnNetwork('606837591', 'facebook');
        $this->assertNotNull($instance);

        $owner_instance = $owner_instance_dao->get($owner->id, $instance->id);
        $this->assertNotNull($owner_instance);
        $this->assertEqual($owner_instance->oauth_access_token, 'newfauxaccesstoken11234567890');

        //assert the auth error got reset to an empty string on successful reconnection
        $this->assertEqual($owner_instance->auth_error, '');
    }

    public function testForDeleteCSRFToken() {
        self::buildInstanceData();

        $owner_instance_dao = new OwnerInstanceMySQLDAO();
        $instance_dao = new InstanceMySQLDAO();
        $owner_dao = new OwnerMySQLDAO();

        $options_array = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com', true, true);
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new FacebookPluginConfigurationController($owner, 'facebook');

        // add mock page data to view
        $owner_instance_pages = array(
            '123456' =>
        array('id' => '123456',
              'network_username' => 'test_username',
              'network' => 'facebook', ));
        $view = $controller->getViewManager();
        $view->assign('owner_instance_pages', $owner_instance_pages);

        $output = $controller->go();
        // looks for account delete token
        $this->assertPattern('/name="csrf_token" value="'. self::CSRF_TOKEN .
        '" \/><!\-\- delete account csrf token \-\->/', $output);

        // looks for page delete token
        $this->assertPattern('/name="csrf_token" value="'. self::CSRF_TOKEN .
        '" \/><!\-\- delete page csrf token \-\->/', $output);
    }

    public function testOwnerMemberLevelWithAccountConnected() {
        // build options data
        $options_array = $this->buildPluginOptions();
        //Add a connected Facebook account
        $builders[] = FixtureBuilder::build('instances', array('id'=>2, 'network_user_id'=>14,
        'network_username'=>'zuck', 'is_public'=>1, 'network'=>'facebook'));
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>2));

        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        //Set membership_level to Member
        $owner->membership_level = "Member";

        $controller = new FacebookPluginConfigurationController($owner, 'facebook');
        $output = $controller->go();
        $this->debug($output);

        // Assert that the Add User button isn't there
        $this->assertNoPattern('/Add a Facebook Account/', $output);
        // Assert that the message about upgradiing is there
        $this->assertPattern('/To connect another Facebook account to ThinkUp, upgrade your membership/', $output);
    }

    public function testOwnerProLevelWith9AccountsConnected() {
        self::buildInstanceData();
        // build options data
        $options_array = $this->buildPluginOptions();
        //Add 9 connected Facebok accounts
        $i = 9;
        while ($i > 0) {
            $builders[] = FixtureBuilder::build('instances', array('id'=>(10+$i), 'network_user_id'=>14,
            'network_username'=>'zuck', 'is_public'=>1, 'network'=>'facebook'));
            $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>2, 'instance_id'=>(10+$i)));
            $i--;
        }

        $this->simulateLogin('me2@example.com', true);
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        //Set membership_level to Pro
        $owner->membership_level = "Pro";
        $controller = new FacebookPluginConfigurationController($owner, 'facebook');
        $output = $controller->go();

        $this->debug($output);

        // Assert that the Add User button isn't there
        $this->assertNoPattern('/Add a Facebook Account/', $output);
        // Assert that the message about the membership cap is there
        $this->assertPattern('/you&#39;ve connected 10 of 10 accounts to ThinkUp./', $output);
    }
}
