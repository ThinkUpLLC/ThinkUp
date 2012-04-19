<?php
/**
 *
 * ThinkUp/webapp/plugins/googleplus/tests/TestOfGooglePlusPluginConfigurationController.php
 *
 * Copyright (c) 2011-2012 Gina Trapani
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
 * Test of TestOfGooglePlusPluginConfigurationController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2012 Gina Trapani
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
        $webapp = Webapp::getInstance();
        $webapp->registerPlugin('googleplus', 'GooglePlusPlugin');
        $_SERVER['SERVER_NAME'] = 'dev.thinkup.com';
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
        $config = Config::getInstance();
        $this->assertEqual('You must <a href="'.$config->getValue('site_root_path').
        'session/login.php">log in</a> to do this.', $v_mgr->getTemplateDataItem('error_msg'));

        // logged in
        // build a user
        $builder = FixtureBuilder::build('owners', array('email' => 'me@example.com', 'user_activated' => 1) );

        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new GooglePlusPluginConfigurationController($owner, 'googleplus');
        $output = $controller->go();
        $v_mgr = $controller->getViewManager();
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

        // var_dump("<html><body>" . $options_markup . "</body></html>");

        // submit and elemnts should be disbaled
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
        $plugin_id = 3;
        $namespace = OptionDAO::PLUGIN_OPTIONS . '-' .$plugin_id;
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
        $namespace = OptionDAO::PLUGIN_OPTIONS . '-3';
        $builders = array();
        $builders[] = FixtureBuilder::build('plugins',
        array('name' => 'Google+', 'folder_name' => 'googleplus', 'description' => "Google+ plugin") );
        $builders[] = FixtureBuilder::build('options',
        array('namespace' => $namespace, 'option_name' => 'google_plus_client_id', 'option_value' => "id") );
        $builders[] = FixtureBuilder::build('options',
        array('namespace' => $namespace, 'option_name' => 'google_plus_client_secret', 'option_value' => "s3cr3t") );
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

        $_GET['code'] = 'test-google-provided-code';

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

        $_GET['code'] = 'test-google-provided-code-should-return-error';

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

        $_GET['code'] = 'test-google-provided-code-user-profile-403-error';

        $results = $controller->go();
        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('success_msg'), '');
        $msgs = $v_mgr->getTemplateDataItem('error_msgs');
        $this->assertEqual($msgs['authorization'], 'Oops! Looks like Google+ API access isn\'t turned on. '.
        '<a href="http://code.google.com/apis/console#access">In the Google APIs console</a>, in Services, flip the '.
        'Google+ API Status switch to \'On\' and try again.');
        $this->debug(Utils::varDumpToString($msgs));
    }
}