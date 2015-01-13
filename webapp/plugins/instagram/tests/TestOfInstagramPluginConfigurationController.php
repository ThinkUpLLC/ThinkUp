<?php
/**
 *
 * ThinkUp/webapp/plugins/instagram/tests/TestOfInstagramPluginConfigurationController.php
 *
 * Copyright (c) 2009-2013 Dimosthenis Nikoudis
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
 * Test of InstagramPluginConfigurationController
 *
 * @author Dimosthenis Nikoudis <dnna[at]dnna[dot]gr>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Dimosthenis Nikoudis
 *
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'plugins/instagram/model/class.InstagramPlugin.php';
require_once THINKUP_WEBAPP_PATH.'plugins/instagram/controller/class.InstagramPluginConfigurationController.php';
require_once THINKUP_WEBAPP_PATH.'plugins/instagram/tests/classes/mock.Proxy.php';

class TestOfInstagramPluginConfigurationController extends ThinkUpUnitTestCase {

    /**
     * Data fixture builders
     * @var array
     */
    var $builders;

    public function setUp() {
        parent::setUp();
        $this->builders = array();

        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        $webapp_plugin_registrar->registerPlugin('instagram', 'InstagramPlugin');

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
        $instance_builder = FixtureBuilder::build('instances', array('id'=>1, 'network_user_id'=>'494785218',
        'network_username'=>'Gina Trapani', 'network'=>'instagram', 'is_active'=>1));
        array_push($this->builders, $instance_builder);

        //Add owner instance_owner
        $owner_instance_builder = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>1,
        'oauth_access_token'=>'faux-access-token1', 'auth_error'=>'Token has expired.'));
        array_push($this->builders, $owner_instance_builder);

        //Add second instance
        $instance2_builder = FixtureBuilder::build('instances', array('id'=>2, 'network_user_id'=>'668406218',
        'network_username'=>'Penelope Caridad', 'network'=>'instagram', 'is_active'=>1));
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
        $controller = new InstagramPluginConfigurationController(null);
        $this->assertNotNull($controller, 'constructor test');
        $this->assertIsA($controller, 'InstagramPluginConfigurationController');
    }

    public function testConfigNotSet() {
        $plugin_options_dao = DAOFactory::getDAO("PluginOptionDAO");
        PluginOptionMySQLDAO::$cached_options = array();
        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new InstagramPluginConfigurationController($owner);
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
        $controller = new InstagramPluginConfigurationController(null);
        $output = $controller->go();
        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertPattern( '/session\/login.php\?redirect\=/', $controller->redirect_destination);

        //logged in
        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new InstagramPluginConfigurationController($owner);
        $output = $controller->go();
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('instances'), 'array', 'Owner instances set');
        $this->assertTrue($v_mgr->getTemplateDataItem('instaconnect_link') != '', 'Authorization link set');
    }

    public function testConfigOptionsNotAdmin() {
        self::buildInstanceData();
        // build some options data
        $options_array = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new InstagramPluginConfigurationController($owner, 'instagram');
        $output = $controller->go();
        // we have a text form element with proper data
        $this->assertNoPattern('/Pause crawling/', $output);
        $this->assertNoPattern('/Start crawling/', $output);
        $this->assertNoPattern('/Save Settings/', $output); // should have no submit option
        $this->assertNoPattern('/plugin_options_error_instagram_app_id/', $output); // should have no app id
        $this->assertNoPattern('/plugin_options_error_message_instagram_api_secret/', $output); // no secret
        $this->assertNoPattern('/plugin_options_max_crawl_time/', $output); // no advanced option
        $this->assertPattern('/var is_admin = false/', $output); // not a js admin
        $this->assertPattern('/var required_values_set = true/', $output); // is configured

        //app not configured
        // Get plugin ID
        $sql = "select id from " . $this->table_prefix . "plugins where folder_name = 'instagram'";
        $stmt = PluginMySQLDAO::$PDO->query($sql);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $namespace = OptionDAO::PLUGIN_OPTIONS . '-'.$data['id'];
        $prefix = Config::getInstance()->getValue('table_prefix');
        OwnerMySQLDAO::$PDO->query("delete from " . $prefix . "options where namespace = '$namespace'");
        $controller = new InstagramPluginConfigurationController($owner, 'instagram');
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
        $controller = new InstagramPluginConfigurationController($owner, 'instagram');
        $output = $controller->go();

        $this->debug($output);

        // we have a text form element with proper data
        $this->assertPattern('/Pause crawling/', $output);
        $this->assertPattern('/Save Settings/', $output); // should have submit option
        $this->assertPattern('/plugin_options_error_message_instagram_api_secret/', $output); // secret option
        $this->assertPattern('/plugin_options_max_crawl_time/', $output); // advanced option
        $this->assertPattern('/var is_admin = true/', $output); // is a js admin
        $this->assertPattern('/var required_values_set = true/', $output); // is configured

        //app not configured
        // Get plugin ID
        $sql = "select id from " . $this->table_prefix . "plugins where folder_name = 'instagram'";
        $stmt = PluginMySQLDAO::$PDO->query($sql);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $namespace = OptionDAO::PLUGIN_OPTIONS . '-'.$data['id'];
        $prefix = Config::getInstance()->getValue('table_prefix');
        OwnerMySQLDAO::$PDO->query("delete from " . $prefix . "options where namespace = '$namespace'");
        $controller = new InstagramPluginConfigurationController($owner, 'instagram');
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
        $controller = new InstagramPluginConfigurationController($owner, 'instagram');
        $output = $controller->go();

        $this->debug($output);

        $expected_pattern = '/Go to the Instagram Developers Clients page/';
        $this->assertPattern($expected_pattern, $output);
    }

    public function testConfiguredPluginWithAuthError() {
        self::buildInstanceData();
        // build some options data
        $options_array = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com', true);
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new InstagramPluginConfigurationController($owner, 'instagram');
        $output = $controller->go();
        $this->debug($output);

        //with auth error
        $this->assertPattern('/instagram-auth-error"/', $output);
    }

    public function testConfiguredPluginWitNoAuthError() {
        self::buildInstanceData();
        // build some options data
        $options_array = $this->buildPluginOptions();
        $this->simulateLogin('me2@example.com', true);
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new InstagramPluginConfigurationController($owner, 'instagram');
        $output = $controller->go();

        //no auth error
        $this->debug($output);
        $this->assertNoPattern('/instagram-auth-error/', $output);
    }

    private function buildPluginOptions() {
        $builders = array();
        // Create a plugin (required as Instagram isn't a default plugin)
        $builders[] = FixtureBuilder::build('plugins', array('name' => 'Instagram',
        'folder_name' => 'instagram', 'is_active' => 1) );
        // Get plugin ID
        $sql = "select id from " . $this->table_prefix . "plugins where folder_name = 'instagram'";
        $stmt = PluginMySQLDAO::$PDO->query($sql);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $namespace = OptionDAO::PLUGIN_OPTIONS . '-'.$data['id'];
        $builders[] = FixtureBuilder::build('options',
        array('namespace' => $namespace, 'option_name' => 'instagram_api_secret', 'option_value' => "scrt") );
        $builders[] = FixtureBuilder::build('options',
        array('namespace' => $namespace, 'option_name' => 'instagram_app_id', 'option_value' => "77") );
        return $builders;
    }

    public function testConnectAccountSuccessful()  {
        $owner_instance_dao = new OwnerInstanceMySQLDAO();
        $instance_dao = new InstanceMySQLDAO();
        $owner_dao = new OwnerMySQLDAO();

        $config = Config::getInstance();
        $config->setValue('site_root_path', '/');

        $_SERVER['SERVER_NAME'] = "srvr";
        SessionCache::put('instagram_auth_csrf', '123');
        $_GET['p'] = 'instagram';
        $_GET['code'] = '456';
        $_GET['state'] = '123';

        $options_array = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com', true);

        $instance = $instance_dao->getByUserIdOnNetwork('494785218', 'instagram');
        $this->assertNull($instance); //Instance doesn't exist

        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new InstagramPluginConfigurationController($owner, 'instagram');
        $output = $controller->go();

        $v_mgr = $controller->getViewManager();

        $msgs = $v_mgr->getTemplateDataItem('success_msgs');
        $this->assertEqual($msgs['user_add'], "Success! Your instagram account has been added to ThinkUp.");
        $this->debug(Utils::varDumpToString($msgs));

        $instance = $instance_dao->getByUserIdOnNetwork('494785218', 'instagram');
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
        SessionCache::put('instagram_auth_csrf', '123');
        $_GET['p'] = 'instagram';
        $_GET['code'] = '789';
        $_GET['state'] = '123';

        $options_array = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com', true);

        $instance = $instance_dao->getByUserIdOnNetwork('494785218', 'instagram');
        $this->assertNull($instance); //Instance doesn't exist

        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new InstagramPluginConfigurationController($owner, 'instagram');
        $output = $controller->go();

        $v_mgr = $controller->getViewManager();

        $msgs = $v_mgr->getTemplateDataItem('success_msgs');
        $this->assertEqual($msgs['user_add'], "Success! Your instagram account has been added to ThinkUp.");
        $this->debug(Utils::varDumpToString($msgs));

        $instance = $instance_dao->getByUserIdOnNetwork('494785218', 'instagram');
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
        SessionCache::put('instagram_auth_csrf', '123');
        $_GET['p'] = 'instagram';
        $_GET['code'] = '789';
        $_GET['state'] = '123';

        $options_array = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com', true);

        $instance = $instance_dao->getByUserIdOnNetwork('494785218', 'instagram');
        $this->assertNull($instance); //Instance doesn't exist

        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new InstagramPluginConfigurationController($owner, 'instagram');
        $output = $controller->go();

        $v_mgr = $controller->getViewManager();

        $msgs = $v_mgr->getTemplateDataItem('success_msgs');
        $this->assertEqual($msgs['user_add'], "Success! Your instagram account has been added to ThinkUp.");
        $this->debug(Utils::varDumpToString($msgs));

        $instance = $instance_dao->getByUserIdOnNetwork('494785218', 'instagram');
        $this->assertNotNull($instance); //Instance created

        $owner_instance = $owner_instance_dao->get($owner->id, $instance->id);
        $this->assertNotNull($owner_instance); //Owner Instance created
        //OAuth token set
        $this->assertEqual($owner_instance->oauth_access_token, 'newfauxaccesstoken11234567890');
    }

    public function testConnectAccountThatAlreadyExists()  {
        self::buildInstanceData();

        $owner_instance_dao = new OwnerInstanceMySQLDAO();
        $instance_dao = new InstanceMySQLDAO();
        $owner_dao = new OwnerMySQLDAO();

        $config = Config::getInstance();
        $config->setValue('site_root_path', '/');

        $_SERVER['SERVER_NAME'] = "srvr";
        SessionCache::put('instagram_auth_csrf', '123');
        $_GET['p'] = 'instagram';
        $_GET['code'] = '456';
        $_GET['state'] = '123';

        $options_array = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com', true);
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());

        $instance = $instance_dao->getByUserIdOnNetwork('494785218', 'instagram');
        $this->assertNotNull($instance);

        //assert there is an auth error
        $owner_instance = $owner_instance_dao->get($owner->id, $instance->id);
        $this->assertEqual($owner_instance->auth_error, 'Token has expired.');

        $controller = new InstagramPluginConfigurationController($owner, 'instagram');
        $output = $controller->go();
        $this->debug($output);

        $v_mgr = $controller->getViewManager();
        $msgs = $v_mgr->getTemplateDataItem('success_msgs');
        $this->assertEqual($msgs['user_add'], "Success! You've reconnected your Instagram account. To connect ".
        "a different account, log  out of Instagram in a different browser tab and try again.");
        $this->debug(Utils::varDumpToString($msgs));

        $instance = $instance_dao->getByUserIdOnNetwork('494785218', 'instagram');
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
        $controller = new InstagramPluginConfigurationController($owner, 'instagram');

        // add mock page data to view
        $owner_instance_pages = array(
            '123456' =>
        array('id' => '123456',
              'network_username' => 'test_username',
              'network' => 'instagram', ));
        $view = $controller->getViewManager();
        $view->assign('owner_instance_pages', $owner_instance_pages);

        $output = $controller->go();
        // looks for account delete token
        $this->assertPattern('/name="csrf_token" value="'. self::CSRF_TOKEN .
        '" \/>/', $output);
    }
}
