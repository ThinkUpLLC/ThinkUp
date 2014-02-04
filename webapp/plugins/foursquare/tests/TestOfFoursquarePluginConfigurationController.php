<?php
/**
 *
 * ThinkUp/webapp/plugins/foursquare/tests/TestOfFoursquarePluginConfigurationController.php
 *
 * Copyright (c) 2012-2013 Aaron Kalair
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
 * @author Aaron Kalair <aaronkalair[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Aaron Kalair
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';
require_once THINKUP_ROOT_PATH.'tests/classes/class.ThinkUpBasicUnitTestCase.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/foursquare/controller/class.FoursquarePluginConfigurationController.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/foursquare/model/class.FoursquareCrawler.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/foursquare/model/class.FoursquarePlugin.php';
// Handle API queries locally
require_once THINKUP_ROOT_PATH.'webapp/plugins/foursquare/tests/classes/mock.FoursquareAPIAccessor.php';

class TestOfFoursquarePluginConfigurationController extends ThinkUpUnitTestCase {

    // Do some set up work, registering the plugin and settings variables like server name etc.
    public function setUp(){
        // Call the ThinkUpUnitTestCase constructor
        parent::setUp();
        // Get an instance
        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        // Register the foursquare plugin
        $webapp_plugin_registrar->registerPlugin('foursquare', 'FoursquarePlugin');
        // Set the server name variable as we don't actually have a server
        $_SERVER['SERVER_NAME'] = 'test';
    }

    public function tearDown() {
        // Clean up any database changes we made
        parent::tearDown();
    }

    // Build various structures like options, controller and the owner
    private function buildController() {
        // Create an owner
        $builder_owner = FixtureBuilder::build('owners', array('email' => 'me@example.com', 'user_activated' => 1) );
        // Set the name space to plugin_options-pluginid
        $sql = "select id from " . $this->table_prefix . "plugins where folder_name = 'foursquare'";
        $stmt = PluginMySQLDAO::$PDO->query($sql);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $namespace = OptionDAO::PLUGIN_OPTIONS . '-'.$data['id'];
        // Create the client id option
        $builder_plugin_options[] =
        FixtureBuilder::build('options',
        array('namespace' => $namespace, 'option_name' => 'foursquare_client_id',
        'option_value' => "ci") );
        // Create the client secret option
        $builder_plugin_options[] =
        FixtureBuilder::build('options',
        array('namespace' => $namespace, 'option_name' => 'foursquare_client_secret',
         'option_value' => "cs") );
        // Log the user in
        $this->simulateLogin('me@example.com');
        // Get an owner DAO
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        // Get the logged in users email
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        // Create a new foursquare config controller for the owner
        $controller = new FoursquarePluginConfigurationController($owner);
        // Return an array with the controller, owner, and options
        return array($controller, $builder_owner, $builder_plugin_options);
    }

    public function getElementById($doc, $id) {
        $xpath = new DOMXPath($doc);
        return $xpath->query("//*[@id='$id']")->item(0);
    }

    //  Insert the plugin options in the database
    private function buildPluginOptions() {
        $builders = array();
        // Set the plugin ID
        $sql = "select id from " . $this->table_prefix . "plugins where folder_name = 'foursquare'";
        $stmt = PluginMySQLDAO::$PDO->query($sql);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $namespace = OptionDAO::PLUGIN_OPTIONS . '-'.$data['id'];
        // Create the client id option
        $builders[] = FixtureBuilder::build('options', array('namespace' => $namespace,
        'option_name' => 'foursquare_client_id', 'option_value' => "ci") );
        // Create the client secret option
        $builders[] = FixtureBuilder::build('options', array('namespace' => $namespace,
        'option_name' => 'foursquare_client_secret', 'option_value' => "cs") );
        return $builders;
    }

    public function testConstructor() {
        // Create a new controller
        $controller = new FoursquarePluginConfigurationController(null);
        // Check the controller was created
        $this->assertNotNull($controller);
        // Check the controller is of type foursquare
        $this->assertIsA($controller, 'FoursquarePluginConfigurationController');
    }

    public function testOutput() {
        // Not logged in, no owner set
        $controller = new FoursquarePluginConfigurationController(null);
        // Run the plugin configuration controller
        $output = $controller->go();
        // Check the user sees a message telling them they need to login
        $this->assertPattern( '/session\/login.php\?redirect\=/', $controller->redirect_destination);

        // Logged in

        // Build a user
        $builder = FixtureBuilder::build('owners', array('email' => 'me@example.com', 'user_activated' => 1) );
        // Log the user in
        $this->simulateLogin('me@example.com');
        // Get a owner DAO
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        // Get the owners email
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        // Create a new foursquare controller for the owner
        $controller = new FoursquarePluginConfigurationController($owner);
        // Run the plugin configuration controller
        $output = $controller->go();
        $this->assertPattern('/Foursquare/', $output);
    }

    public function testConfigOptionsNotAdmin() {
        self::buildInstanceData();
        // build some options data
        $options_array = $this->buildPluginOptions();
        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new FoursquarePluginConfigurationController($owner);
        $output = $controller->go();
        // we have a text form element with proper data
        $this->assertNoPattern('/Pause crawling/', $output);
        $this->assertNoPattern('/Start crawling/', $output);
        $this->assertNoPattern('/Save Settings/', $output); // should have no submit option
        $this->assertNoPattern('/plugin_options_foursquare_client_id_label/', $output); // should have no app id
        $this->assertNoPattern('/plugin_options_foursquare_client_secret_label/', $output); // no secret
        $this->assertPattern('/var is_admin = false/', $output); // not a js admin
        $this->assertPattern('/var required_values_set = true/', $output); // is configured

        //app not configured
        $sql = "select id from " . $this->table_prefix . "plugins where folder_name = 'foursquare'";
        $stmt = PluginMySQLDAO::$PDO->query($sql);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $namespace = OptionDAO::PLUGIN_OPTIONS . '-'.$data['id'];
        $prefix = Config::getInstance()->getValue('table_prefix');
        OwnerMySQLDAO::$PDO->query("delete from " . $prefix . "options where namespace = '$namespace'");
        $controller = new FoursquarePluginConfigurationController($owner);
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
        $controller = new FoursquarePluginConfigurationController($owner);
        $output = $controller->go();

        // we have a text form element with proper data
        $this->assertPattern('/Pause crawling/', $output);
        $this->assertPattern('/Save Settings/', $output); // should have submit option
        $this->assertPattern('/plugin_options_foursquare_client_id/', $output); // should have no app id
        $this->assertPattern('/plugin_options_foursquare_client_secret/', $output); // no secret
        $this->assertPattern('/var is_admin = true/', $output); // is a js admin
        $this->assertPattern('/var required_values_set = true/', $output); // is configured

        //app not configured
        $sql = "select id from " . $this->table_prefix . "plugins where folder_name = 'foursquare'";
        $stmt = PluginMySQLDAO::$PDO->query($sql);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $prefix = Config::getInstance()->getValue('table_prefix');
        $namespace = OptionDAO::PLUGIN_OPTIONS . '-'.$data['id'];
        OwnerMySQLDAO::$PDO->query("delete from " . $prefix . "options where namespace = '$namespace'");
        $controller = new FoursquarePluginConfigurationController($owner);
        $output = $controller->go();
        $this->debug($output);
        $this->assertPattern('/var required_values_set = false/', $output); // is not configured
    }

    private function buildInstanceData() {
        $builders = array();
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User',
        'email'=>'me@example.com', 'is_activated'=>1));

        //Add instance
        $builders[] = FixtureBuilder::build('instances', array('id'=>1, 'network_user_id'=>'606837591',
        'network_username'=>'Gina Trapani', 'network'=>'foursquare', 'is_active'=>1));

        //Add owner instance_owner
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>1,
        'oauth_access_token'=>'faux-access-token1', 'auth_error'=>'Token has expired.'));

        //Add second instance
        $builders[] = FixtureBuilder::build('instances', array('id'=>2, 'network_user_id'=>'668406218',
        'network_username'=>'Penelope Caridad', 'network'=>'foursquare', 'is_active'=>1));

        //Add second owner instance_owner
        $builders[] = FixtureBuilder::build('owner_instances', array('owner_id'=>2, 'instance_id'=>2,
        'oauth_access_token'=>'faux-access-token2', 'auth_error'=>''));

        return $builders;
    }

    // Check the options were inserted correctly into the database
    public function testOptionList2HashByOptionName() {
        // Build a controller
        $build_data = $this->buildController();
        // Get the controller from the first element of the array the above command returned
        $controller = $build_data[0];
        // Get a hash of plugin options with option_name as the key
        $options_hash = $controller->optionList2HashByOptionName();

        // Check the client id is the 2nd option set
        $this->assertEqual($options_hash['foursquare_client_id']->id, 2);
        // Check the name of the client id option is foursquare_client_id
        $this->assertEqual($options_hash['foursquare_client_id']->option_name, 'foursquare_client_id');
        // Check the value is test_client_id
        $this->assertEqual($options_hash['foursquare_client_id']->option_value, 'ci');

        // Check the client secret is the 3rd option set
        $this->assertEqual($options_hash['foursquare_client_secret']->id, 3);
        // Check that the name of the client secret option is foursquare_client_secret
        $this->assertEqual($options_hash['foursquare_client_secret']->option_name, 'foursquare_client_secret');
        // Check the value of the client secret is test_client_secret
        $this->assertEqual($options_hash['foursquare_client_secret']->option_value, 'cs');
    }

    // Check all the correct options get added to the template for a non admin
    public function testAddTextOptionNotAdmin() {
        // Build some things we need
        $build_data = $this->buildController();
        // Get the controller from the array the above call returned
        $controller = $build_data[0];
        // Get the owner from the array the above call returned
        $owner  = $build_data[1];
        // Get the plugin options from the array the above call returned
        $plugin_option  = $build_data[2];

        // Just user, not an admin, so view only
        // Run the controller
        $output = $controller->go();
        // Check some option elements were set
        $this->assertNotNull( $controller->option_elements);
        // Check 2 option elements were set
        $this->assertEqual( count($controller->option_elements), 2);
        // Check the foursquare client ID option is set
        $this->assertNotNull( $controller->option_elements['foursquare_client_id']);
        //
        $this->assertEqual(
        PluginConfigurationController::FORM_TEXT_ELEMENT,
        $controller->option_elements['foursquare_client_id']['type'] );
        // Check the default value was set
        $this->assertTrue( isset($controller->option_elements['foursquare_client_id']['default_value']) );
        // Check 2 required messages are present on the entire page
        $this->assertEqual( count($controller->option_required_message), 2);
        // Check the client id has a message
        $this->assertTrue( isset($controller->option_required_message['foursquare_client_id']));
        // Check the client secret option is set
        $this->assertNotNull( $controller->option_elements['foursquare_client_secret']);
        //
        $this->assertEqual(
        PluginConfigurationController::FORM_TEXT_ELEMENT,
        $controller->option_elements['foursquare_client_secret']['type'] );
        // Check the client secret has a default value
        $this->assertTrue(isset($controller->option_elements['foursquare_client_secret']['default_value']) );
        // Check it has a required message
        $this->assertTrue(isset($controller->option_required_message['foursquare_client_secret']));

        // Get a view manager
        $v_mgr = $controller->getViewManager();
        // Get the markup for options
        $options_markup = $v_mgr->getTemplateDataItem('options_markup');
        // Check the options markup was actually set
        $this->assertNotNull($options_markup);

        // Parse option_markup
        $doc = new DOMDocument();
        // Parse our html
        $doc->loadHTML("<html><body>" . $options_markup . "</body></html>");

    }

    // Check all the correct options get added to the template for a admin
    public function testAddTextOptionIsAdmin() {
        // Set admin to be true
        $is_admin = 1;
        // Log the owner in
        $this->simulateLogin('me@example.com', true);
        // Build the data structures
        $build_data = $this->buildController();
        // Get the controller from the array
        $controller = $build_data[0];
        // Get the owner from the array
        $owner  = $build_data[1];
        // Get the plugin options from the array
        $plugin_option  = $build_data[2];

        // Just name, is admin, so form should be enabled
        $output = $controller->go();
        $this->assertNotNull( $controller->option_elements);
        $this->assertEqual( count($controller->option_elements), 2);
        $this->assertNotNull( $controller->option_elements['foursquare_client_id']);
        $this->assertEqual(
        PluginConfigurationController::FORM_TEXT_ELEMENT,
        $controller->option_elements['foursquare_client_id']['type'] );
        $this->assertTrue( isset($controller->option_elements['foursquare_client_id']['default_value']) );
        $this->assertEqual( count($controller->option_required_message), 2);
        $this->assertTrue( isset($controller->option_required_message['foursquare_client_id']));
        $v_mgr = $controller->getViewManager();
        $options_markup = $v_mgr->getTemplateDataItem('options_markup');
        $this->assertNotNull($options_markup);

        // Parse option_markup
        $doc = new DOMDocument();
        // Parse our html
        //$doc = $doc->loadHTML("<html><body>" . $options_markup . "</body></html>");
        $doc->loadHTML("<html><body>" . $options_markup . "</body></html>");
        // We have a text form element with proper data
        $input_field = self::getElementById($doc, 'plugin_options_foursquare_client_id');
        $this->assertEqual($input_field->getAttribute('value'), $plugin_option[0]->columns['option_value']);

        $input_field = self::getElementById($doc, 'plugin_options_foursquare_client_secret');
        $this->assertEqual($input_field->getAttribute('value'), $plugin_option[1]->columns['option_value']);

        // Submit and elements should be disabled
        $this->assertFalse($input_field->getAttribute('disabled'));
        $submit_p = self::getElementById($doc, 'plugin_option_submit_p');
        $this->assertPattern('/type="submit".*Save Settings/', $doc->saveXML( $submit_p ) );
    }

    // Test we can get the options correctly
    public function testGetPluginOptions() {
        // Build the data structures
        $build_data = $this->buildController();
        // Get the controller from the array
        $controller = $build_data[0];
        // Get a hash of the options
        $options_hash = $controller->getPluginOptions();
        // Check they are all set correctly
        $this->assertEqual($options_hash['foursquare_client_id']->id, 2);
        $this->assertEqual($options_hash['foursquare_client_id']->option_name, 'foursquare_client_id');
        $this->assertEqual($options_hash['foursquare_client_id']->option_value, 'ci');

        // Get a single undefined option
        $this->assertFalse($controller->getPluginOption('not defined'));

        // Get a single defined option
        $this->assertEqual($controller->getPluginOption('foursquare_client_id'), 'ci');
    }

    // Check the correct screen is presented to the users when the configuration is not complete
    public function testConfigNotSet() {
        // Create a owner
        $builder = FixtureBuilder::build('owners', array('email' => 'me@example.com', 'user_activated' => 1) );
        // Get a options DAO
        $plugin_options_dao = DAOFactory::getDAO("PluginOptionDAO");
        //
        PluginOptionMySQLDAO::$cached_options = array();
        // Log the owner in
        $this->simulateLogin('me@example.com');
        // Get a owner DAO
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        // Get the logged in owners email address
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        // Create a new foursquare config controller for the owner
        $controller = new FoursquarePluginConfigurationController($owner);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();

        // Config is not complete so they should see a error message
        $info = $v_mgr->getTemplateDataItem('info_msgs');
        $this->assertEqual($info['setup'], 'Please complete plugin setup to start using it');

        // Shouldn't see the authorize link
        $this->assertNoPattern("/Click on this button to authorize ThinkUp to access your foursquare account./",
        $results);
        $this->assertNoPattern("/Authorize ThinkUp on foursquare/", $results);
    }

    // Test the owner has the option to add a foursquare user when the plugin is configured
    public function testConfigSet() {
        // Build the plugin options to configure the plugin
        $builders = $this->buildPluginOptions();
        // Create an owner
        $builder = FixtureBuilder::build('owners', array('email' => 'me@example.com', 'user_activated' => 1) );
        // Get a plugin options DAO
        $plugin_options_dao = DAOFactory::getDAO("PluginOptionDAO");
        //
        PluginOptionMySQLDAO::$cached_options = array();
        // Log the owner in
        $this->simulateLogin('me@example.com');
        // Get a owner DAO
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        // Get the logged in owners email
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        // Create a config controller for them
        $controller = new FoursquarePluginConfigurationController($owner);
        $results = $controller->go();
        // Shouldn't see error message
        $this->assertNoPattern("/Please set your Foursquare client ID and secret./", $results);
        // Should see authorize link
        $this->assertPattern("/Add a Foursquare User/", $results);
    }

    // Test getting the OAuth tokens from foursquare
    public function testGetOAuthTokens() {
        // Set the plugin options
        $builders = $this->buildPluginOptions();
        // Get an instance
        $config = Config::getInstance();
        // Set the root path
        $config->setValue('site_root_path', '/');
        // Get a plugin options DAO
        $plugin_options_dao = DAOFactory::getDAO("PluginOptionDAO");
        //
        PluginOptionMySQLDAO::$cached_options = array();

        // Build an owner
        $builders[] = FixtureBuilder::build('owners', array('email' => 'me@example.com', 'user_activated' => 1) );
        $instance_builder = FixtureBuilder::build('instances', array('id'=>1, 'network_user_id'=>'18127856',
        'network_username'=>'me@me.com', 'network'=>'foursquare', 'is_active'=>1));

        //Add owner instance_owner
        $owner_instance_builder = FixtureBuilder::build('owner_instances', array('owner_id'=>1, 'instance_id'=>1,
        'oauth_access_token'=>'secret'));

        // Log them in
        $this->simulateLogin('me@example.com');
        // Get an owner DAO
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        // Get the logged in owners email
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        // Create a new config controller for the owner
        $controller = new FoursquarePluginConfigurationController($owner);

        // Set the code foursquare would return from a real request
        $_GET['code'] = '5dn';
        // Check we get the tokens and tell the user it was a sucess
        $results = $controller->go();
        $v_mgr = $controller->getViewManager();
        $msgs = $v_mgr->getTemplateDataItem('success_msgs');
        $this->assertEqual($msgs['user_add'], 'Success! Your foursquare account has been added to ThinkUp.');

        // Get an owner instance DAO
        $owner_instance_dao = new OwnerInstanceMySQLDAO();
        // Get a instance DAO
        $instance_dao = new InstanceMySQLDAO();

        // Check we created a foursquare instance
        $instance = $instance_dao->getByUserIdOnNetwork('18127856', 'foursquare');
        $this->assertNotNull($instance);

        // Check a owner instance for this user with the instance ID was created
        $owner_instance = $owner_instance_dao->get($owner->id, $instance->id);
        $this->assertNotNull($owner_instance);
        // OAuth tokens set
        $this->assertEqual($owner_instance->oauth_access_token, 'secret');
    }

    // Supply a bad return code from foursquare and check the user is told theres an error
    public function testGetOAuthTokensWithError() {
        // Build the plugin options
        $builders = $this->buildPluginOptions();
        // Get an instance
        $config = Config::getInstance();
        // Set the root path
        $config->setValue('site_root_path', '/');
        // Get a plugin option DAO
        $plugin_options_dao = DAOFactory::getDAO("PluginOptionDAO");
        //
        PluginOptionMySQLDAO::$cached_options = array();

        // Build a owner
        $builders[] = FixtureBuilder::build('owners', array('email' => 'me@example.com', 'user_activated' => 1) );
        // Log the owner in
        $this->simulateLogin('me@example.com');
        // Get a owner DAO
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        // Get the logged in owners email address
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        // Create a new controller for this user
        $controller = new FoursquarePluginConfigurationController($owner);

        // Set the return code from foursquare to anything not valid
        $_GET['code'] = 'error5dn';
        // Check the user is told theres a problem
        $results = $controller->go();
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('success_msg'), '');
        $msgs = $v_mgr->getTemplateDataItem('error_msgs');
        $this->assertEqual($msgs['authorization'], 'Oops! Something went wrong while obtaining OAuth tokens.'.
        ' foursquare says "foursquare_error_text." Please double-check your settings and try again.');
    }
}
