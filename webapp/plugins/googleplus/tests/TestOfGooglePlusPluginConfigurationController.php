<?php
/**
 *
 * ThinkUp/webapp/plugins/googleplus/tests/TestOfGooglePlusPluginConfigurationController.php
 *
 * Copyright (c) 2011-2013 Gina Trapani
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
 * Test of TestOfGooglePlusPluginConfigurationController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';
require_once THINKUP_ROOT_PATH.'tests/classes/class.ThinkUpBasicUnitTestCase.php';
require_once THINKUP_WEBAPP_PATH.'plugins/googleplus/controller/class.GooglePlusPluginConfigurationController.php';
require_once THINKUP_WEBAPP_PATH.'plugins/googleplus/tests/classes/mock.GooglePlusAPIAccessor.php';
require_once THINKUP_WEBAPP_PATH.'plugins/googleplus/model/class.GooglePlusCrawler.php';
require_once THINKUP_WEBAPP_PATH.'plugins/googleplus/model/class.GooglePlusPlugin.php';

class TestOfGooglePlusPluginConfigurationController extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        $webapp_plugin_registrar->registerPlugin('googleplus', 'GooglePlusPlugin');
        $_SERVER['SERVER_NAME'] = 'test';
    }

    public function tearDown(){
        parent::tearDown();
    }

    public function testConstructor() {
        $controller = new GooglePlusPluginConfigurationController(null, 'googleplus');
        $this->assertNotNull($controller);
        $this->assertIsA($controller, 'GooglePlusPluginConfigurationController');
    }

    public function testOutput() {
        //not logged in, no owner set
        $controller = new GooglePlusPluginConfigurationController(null, 'googleplus');
        $output = $controller->go();
        $v_mgr = $controller->getViewManager();
        $this->assertPattern( '/session\/login.php\?redirect\=/', $controller->redirect_destination);

        // logged in
        // build a user
        $builder = FixtureBuilder::build('owners', array('email' => 'me@example.com', 'user_activated' => 1) );

        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new GooglePlusPluginConfigurationController($owner, 'googleplus');
        $output = $controller->go();
        $this->assertPattern('/Google/', $output);
    }

    public function testOptionList2HashByOptionName() {
        $build_data = $this->buildController();
        $controller = $build_data[0];
        $options_hash = $controller->optionList2HashByOptionName();
        $this->assertEqual($options_hash['google_plus_client_id']->id, 2);
        $this->assertEqual($options_hash['google_plus_client_id']->option_name, 'google_plus_client_id');
        $this->assertEqual($options_hash['google_plus_client_id']->option_value, 'test_client_id');

        $this->assertEqual($options_hash['google_plus_client_secret']->id, 3);
        $this->assertEqual($options_hash['google_plus_client_secret']->option_name, 'google_plus_client_secret');
        $this->assertEqual($options_hash['google_plus_client_secret']->option_value, 'test_client_secret');
    }

    public function testAddTextOptionNotAdmin() {
        $build_data = $this->buildController();
        $controller = $build_data[0];
        $owner  = $build_data[1];
        $plugin_option  = $build_data[2];

        // just user, not an admin, so view only
        $output = $controller->go();
        $this->assertNotNull( $controller->option_elements);
        $this->assertEqual( count($controller->option_elements), 2);
        $this->assertNotNull( $controller->option_elements['google_plus_client_id']);
        $this->assertEqual(
        PluginConfigurationController::FORM_TEXT_ELEMENT,
        $controller->option_elements['google_plus_client_id']['type'] );
        $this->assertTrue( isset($controller->option_elements['google_plus_client_id']['default_value']) );
        $this->assertEqual( count($controller->option_required_message), 2);
        $this->assertTrue( isset($controller->option_required_message['google_plus_client_id']));

        $this->assertNotNull( $controller->option_elements['google_plus_client_secret']);
        $this->assertEqual(
        PluginConfigurationController::FORM_TEXT_ELEMENT,
        $controller->option_elements['google_plus_client_secret']['type'] );
        $this->assertTrue(isset($controller->option_elements['google_plus_client_secret']['default_value']) );
        $this->assertTrue(isset($controller->option_required_message['google_plus_client_secret']));

        $v_mgr = $controller->getViewManager();
        $options_markup = $v_mgr->getTemplateDataItem('options_markup');
        $this->assertNotNull($options_markup);

        //parse option_markup
        $doc = new DOMDocument();
        // parse our html
        $doc->loadHTML("<html><body>" . $options_markup . "</body></html>");
    }

    public function testAddTextOptionIsAdmin() {
        $is_admin = 1;
        $this->simulateLogin('me@example.com', true);
        $build_data = $this->buildController();
        $controller = $build_data[0];
        $owner  = $build_data[1];
        $plugin_option  = $build_data[2];

        // just name, is admin, so form should be enabled
        $output = $controller->go();
        $this->debug($output);
        $this->assertNotNull( $controller->option_elements);
        $this->assertEqual( count($controller->option_elements), 2);
        $this->assertNotNull( $controller->option_elements['google_plus_client_id']);
        $this->assertEqual(
        PluginConfigurationController::FORM_TEXT_ELEMENT,
        $controller->option_elements['google_plus_client_id']['type'] );
        $this->assertTrue( isset($controller->option_elements['google_plus_client_id']['default_value']) );
        $this->assertEqual( count($controller->option_required_message), 2);
        $this->assertTrue( isset($controller->option_required_message['google_plus_client_id']));
        $v_mgr = $controller->getViewManager();
        $options_markup = $v_mgr->getTemplateDataItem('options_markup');
        $this->assertNotNull($options_markup);

        //parse option_markup
        $doc = new DOMDocument();
        // parse our html
        $doc->loadHTML("<html><body>" . $options_markup . "</body></html>");

        // we have a text form element with proper data
        $input_field = $this->getElementById($doc, 'plugin_options_google_plus_client_id');
        $this->assertEqual($input_field->getAttribute('value'), $plugin_option[0]->columns['option_value']);

        $input_field = $this->getElementById($doc, 'plugin_options_google_plus_client_secret');
        $this->assertEqual($input_field->getAttribute('value'), $plugin_option[1]->columns['option_value']);

        // submit and elemnts should be disabled
        $this->assertFalse($input_field->getAttribute('disabled'));
        $submit_p = $this->getElementById($doc, 'plugin_option_submit_p');
        $this->assertPattern('/type="submit".*Save Settings/', $doc->saveXML( $submit_p ) );
    }

    public function testGetPluginOptions() {
        $build_data = $this->buildController();
        $controller = $build_data[0];
        $options_hash = $controller->getPluginOptions();
        $this->assertEqual($options_hash['google_plus_client_id']->id, 2);
        $this->assertEqual($options_hash['google_plus_client_id']->option_name, 'google_plus_client_id');
        $this->assertEqual($options_hash['google_plus_client_id']->option_value, 'test_client_id');

        // get a single undefined option
        $this->assertFalse($controller->getPluginOption('not defined'));

        // get a single defined option
        $this->assertEqual($controller->getPluginOption('google_plus_client_id'), 'test_client_id');
    }

    private function buildController() {
        $builder_owner = FixtureBuilder::build('owners', array('email' => 'me@example.com', 'user_activated' => 1) );
        // Get plugin ID
        $sql = "select id from " . $this->table_prefix . "plugins where folder_name = 'googleplus'";
        $stmt = PluginMySQLDAO::$PDO->query($sql);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $namespace = OptionDAO::PLUGIN_OPTIONS . '-'.$data['id'];
        $builder_plugin_options[] =
        FixtureBuilder::build('options',
        array('namespace' => $namespace, 'option_name' => 'google_plus_client_id',
        'option_value' => "test_client_id") );
        $builder_plugin_options[] =
        FixtureBuilder::build('options',
        array('namespace' => $namespace, 'option_name' => 'google_plus_client_secret',
         'option_value' => "test_client_secret") );
        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new GooglePlusPluginConfigurationController($owner, 'googleplus');
        return array($controller, $builder_owner, $builder_plugin_options);
    }

    function getElementById($doc, $id) {
        $xpath = new DOMXPath($doc);
        return $xpath->query("//*[@id='$id']")->item(0);
    }

    public function testConfigNotSet() {
        $builder = FixtureBuilder::build('owners', array('email' => 'me@example.com', 'user_activated' => 1) );
        $plugin_options_dao = DAOFactory::getDAO("PluginOptionDAO");
        PluginOptionMySQLDAO::$cached_options = array();
        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new GooglePlusPluginConfigurationController($owner);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();

        //Should see error message
        $info = $v_mgr->getTemplateDataItem('info_msgs');
        $this->assertEqual($info['setup'], 'Please complete plugin setup to start using it.');
        $this->debug(Utils::varDumpToString($info));

        //Shouldn't see authorize link
        $this->assertNoPattern("/Click on this button to authorize ThinkUp to access your Google\+ account./",
        $results);
        $this->assertNoPattern("/Authorize ThinkUp on Google\+/", $results);
    }

    public function testConfigSet() {
        $builders = $this->buildPluginOptions();
        $builder = FixtureBuilder::build('owners', array('email' => 'me@example.com', 'user_activated' => 1) );

        $plugin_options_dao = DAOFactory::getDAO("PluginOptionDAO");
        PluginOptionMySQLDAO::$cached_options = array();
        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new GooglePlusPluginConfigurationController($owner);
        $results = $controller->go();

        //Shouldn't see error message
        $this->assertNoPattern("/Please set your Google\+ client ID and secret./", $results);
        //Should see authorize link
        $this->assertPattern("/Add a Google\+ User/", $results);
    }

    private function buildPluginOptions() {
        // Get plugin ID
        $sql = "select id from " . $this->table_prefix . "plugins where folder_name = 'googleplus'";
        $stmt = PluginMySQLDAO::$PDO->query($sql);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $namespace = OptionDAO::PLUGIN_OPTIONS . '-'.$data['id'];
        $builders = array();
        $builders[] = FixtureBuilder::build('options',
        array('namespace' => $namespace, 'option_name' => 'google_plus_client_id', 'option_value' => "id") );
        $builders[] = FixtureBuilder::build('options',
        array('namespace' => $namespace, 'option_name' => 'google_plus_client_secret', 'option_value' => "s3c") );
        return $builders;
    }

    public function testGetOAuthTokens() {
        $builders = $this->buildPluginOptions();

        $config = Config::getInstance();
        $config->setValue('site_root_path', '/');

        $plugin_options_dao = DAOFactory::getDAO("PluginOptionDAO");
        PluginOptionMySQLDAO::$cached_options = array();

        $builders[] = FixtureBuilder::build('owners', array('email' => 'me@example.com', 'user_activated' => 1) );

        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new GooglePlusPluginConfigurationController($owner);

        $_GET['code'] = 'tgpc';

        $results = $controller->go();
        $v_mgr = $controller->getViewManager();
        $msgs = $v_mgr->getTemplateDataItem('success_msgs');
        $this->assertEqual($msgs['user_add'], 'Success! Your Google+ account has been added to ThinkUp.');
        $this->debug(Utils::varDumpToString($msgs));

        $owner_instance_dao = new OwnerInstanceMySQLDAO();
        $instance_dao = new InstanceMySQLDAO();

        $instance = $instance_dao->getByUserIdOnNetwork('113612142759476883204', 'google+');
        $this->assertNotNull($instance); //Instance created

        $owner_instance = $owner_instance_dao->get($owner->id, $instance->id);
        $this->assertNotNull($owner_instance); //Owner Instance created
        //OAuth tokens set
        $this->assertEqual($owner_instance->oauth_access_token, 'faux-access-token');
        $this->assertEqual($owner_instance->oauth_access_token_secret, 'faux-refresh-token');
    }

    public function testGetOAuthTokensWithError() {
        $builders = $this->buildPluginOptions();

        $config = Config::getInstance();
        $config->setValue('site_root_path', '/');

        $plugin_options_dao = DAOFactory::getDAO("PluginOptionDAO");
        PluginOptionMySQLDAO::$cached_options = array();

        $builders[] = FixtureBuilder::build('owners', array('email' => 'me@example.com', 'user_activated' => 1) );

        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new GooglePlusPluginConfigurationController($owner);

        $_GET['code'] = 'tgpc-error';

        $results = $controller->go();
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('success_msg'), '');
        $msgs = $v_mgr->getTemplateDataItem('error_msgs');
        $this->assertEqual($msgs['authorization'], 'Oops! Something went wrong while obtaining OAuth tokens.'.
        '<br>Google says "google_error_text." Please double-check your settings and try again.');
        $this->debug(Utils::varDumpToString($msgs));
    }

    public function testGetUserProfileWith403Error() {
        $builders = $this->buildPluginOptions();

        $config = Config::getInstance();
        $config->setValue('site_root_path', '/');

        $plugin_options_dao = DAOFactory::getDAO("PluginOptionDAO");
        PluginOptionMySQLDAO::$cached_options = array();

        $builders[] = FixtureBuilder::build('owners', array('email' => 'me@example.com', 'user_activated' => 1) );

        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new GooglePlusPluginConfigurationController($owner);

        $_GET['code'] = 'tgpc-403';

        $results = $controller->go();
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('success_msg'), '');
        $msgs = $v_mgr->getTemplateDataItem('error_msgs');
        $this->assertEqual($msgs['authorization'], 'Oops! Looks like Google+ API access isn\'t turned on. '.
        '<a href="http://code.google.com/apis/console#access">In the Google APIs console</a>, in Services, flip the '.
        'Google+ API Status switch to \'On\' and try again.');
        $this->debug(Utils::varDumpToString($msgs));
    }

    public function testConfigOptionsNotAdmin() {
        self::buildInstanceData();
        // build some options data
        $options_array = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new GooglePlusPluginConfigurationController($owner, 'facebook');
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
        $sql = "select id from " . $this->table_prefix . "plugins where folder_name = 'googleplus'";
        $stmt = PluginMySQLDAO::$PDO->query($sql);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $prefix = Config::getInstance()->getValue('table_prefix');
        $namespace = OptionDAO::PLUGIN_OPTIONS . '-'.$data['id'];
        OwnerMySQLDAO::$PDO->query("delete from " . $prefix . "options where namespace = '$namespace'");
        $controller = new GooglePlusPluginConfigurationController($owner);
        $output = $controller->go();
        $this->assertPattern('/var required_values_set = false/', $output); // is not configured
    }

    public function testConfigOptionsIsAdmin() {
        $builders = self::buildInstanceData();
        // build some options data
        $options_array = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com', true);
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new GooglePlusPluginConfigurationController($owner);
        $output = $controller->go();

        $this->debug($output);

        // we have a text form element with proper data
        $this->assertPattern('/Pause crawling/', $output);
        $this->assertPattern('/Save Settings/', $output); // should have submit option
        $this->assertPattern('/plugin_options_error_google_plus_client_secret/', $output); // secret option
        $this->assertPattern('/plugin_options_error_message_google_plus_client_id/', $output); // advanced option
        $this->assertPattern('/var is_admin = true/', $output); // is a js admin
        $this->assertPattern('/var required_values_set = true/', $output); // is configured

        //app not configured
        $sql = "select id from " . $this->table_prefix . "plugins where folder_name = 'googleplus'";
        $stmt = PluginMySQLDAO::$PDO->query($sql);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $prefix = Config::getInstance()->getValue('table_prefix');
        $namespace = OptionDAO::PLUGIN_OPTIONS . '-'.$data['id'];
        OwnerMySQLDAO::$PDO->query("delete from " . $prefix . "options where namespace = '$namespace'");
        $controller = new GooglePlusPluginConfigurationController($owner);
        $output = $controller->go();
        $this->assertPattern('/var required_values_set = false/', $output); // is not configured
    }

    private function buildInstanceData() {
        $builders = array();
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User',
        'email'=>'me@example.com', 'is_activated'=>1));

        //Add instance
        $builders[] = FixtureBuilder::build('instances', array('id'=>1, 'network_user_id'=>'606837591',
        'network_username'=>'Gina Trapani', 'network'=>'google+', 'is_active'=>1));

        //Add owner instance_owner
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>1,
        'oauth_access_token'=>'faux-access-token1', 'auth_error'=>'Token has expired.'));

        //Add second instance
        $builders[] = FixtureBuilder::build('instances', array('id'=>2, 'network_user_id'=>'668406218',
        'network_username'=>'Penelope Caridad', 'network'=>'google+', 'is_active'=>1));

        //Add second owner instance_owner
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>2, 'instance_id'=>2,
        'oauth_access_token'=>'faux-access-token2', 'auth_error'=>''));

        return $builders;
    }
}