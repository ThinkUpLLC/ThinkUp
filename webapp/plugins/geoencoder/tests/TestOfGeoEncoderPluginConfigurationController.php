<?php
/**
 *
 * ThinkUp/webapp/plugins/geoencoder/tests/TestOfGeoEncoderPluginConfigurationController.php
 *
 * Copyright (c) 2009-2013 Mark Wilkie, Ekansh Preet Singh
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
 * Test of TestOfGeoEncoderPluginConfigurationController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Mark Wilkie, Ekansh Preet Singh
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'plugins/geoencoder/tests/classes/mock.GeoEncoderCrawler.php';
require_once THINKUP_WEBAPP_PATH.'plugins/geoencoder/controller/class.GeoEncoderPluginConfigurationController.php';
require_once THINKUP_WEBAPP_PATH.'plugins/geoencoder/model/class.GeoEncoderPlugin.php';

class TestOfGeoEncoderPluginConfigurationController extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        $webapp_plugin_registrar->registerPlugin('geoencoder', 'GeoEncoderPlugin');

        $_SERVER['SERVER_NAME'] = 'dev.thinkup.com';
        $_SERVER['HTTP_HOST'] = 'dev.thinkup.com';
    }

    public function tearDown(){
        parent::tearDown();
    }

    /**
     * Test Constructor
     */
    public function testConstructor() {
        $controller = new GeoEncoderPluginConfigurationController(null, 'geoencoder');
        $this->assertTrue(isset($controller), 'constructor test');
    }

    /**
     * Test output
     */
    public function testOutput() {
        //not logged in, no owner set
        $controller = new GeoEncoderPluginConfigurationController(null, 'geoencoder');
        $output = $controller->go();
        $this->assertPattern( '/session\/login.php\?redirect\=/', $controller->redirect_destination);

        // logged in
        // build a user
        $builder = FixtureBuilder::build('owners', array('email' => 'me@example.com', 'user_activated' => 1) );

        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new GeoEncoderPluginConfigurationController($owner, 'geoencoder');
        $output = $controller->go();
        $v_mgr = $controller->getViewManager();
        $message = $v_mgr->getTemplateDataItem('message');
        $this->assertEqual($message,
        'This is the GeoEncoder plugin configuration page for me@example.com.', 'message set ' . $message);
    }

    public function testAddGmapsAPIKey() {
        $build_data = $this->buildController();
        $controller = $build_data[0];
        $owner  = $build_data[1];
        $plugin  = $build_data[2];
        $plugin_option  = $build_data[3];

        // just name, not an admin, so view only
        $output = $controller->go();
        $this->assertNotNull($controller->option_elements);
        $this->assertEqual(count($controller->option_elements), 2);
        $this->assertEqual(
        PluginConfigurationController::FORM_TEXT_ELEMENT, $controller->option_elements['gmaps_api_key']['type']);
        $this->assertTrue(!isset($controller->option_elements['gmaps_api_key']['default_value']));
        $this->assertEqual($controller->option_required_message['gmaps_api_key'],
        'Please enter your Google Maps API Key');

        $v_mgr = $controller->getViewManager();
        $options_markup = $v_mgr->getTemplateDataItem('options_markup');
        $this->assertNotNull($options_markup);

        //parse option_markup
        $doc = new DOMDocument();
        // parse our html
        $doc->loadHTML("<html><body>" . $options_markup . "</body></html>");

        // we have a text form element with proper data
        $input_field = $this->getElementById($doc, 'plugin_options_gmaps_api_key');
        $submit_p = $this->getElementById($doc, 'plugin_option_submit_p');
        $this->assertPattern('/^\s+$/', $submit_p->nodeValue); //should be empty, no submit

        //now as an admin...
        $is_admin = 1;
        $this->simulateLogin('admin@example.com', true);
        $controller = $build_data[0];
        $owner  = $build_data[1];
        $plugin  = $build_data[2];
        $plugin_option  = $build_data[3];

        $output = $controller->go();
        $v_mgr = $controller->getViewManager();
        $options_markup = $v_mgr->getTemplateDataItem('options_markup');
        // parse our html
        $doc->loadHTML("<html><body>" . $options_markup . "</body></html>");

        // we have a text form element with proper data
        $input_field = $this->getElementById($doc, 'plugin_options_gmaps_api_key');

        // submit elements should be disbaled
        $this->assertFalse($input_field->getAttribute('disabled'));
        $submit_p = $this->getElementById($doc, 'plugin_option_submit_p');
        $this->assertPattern('/type="submit".*Save Settings/', $doc->saveXML( $submit_p ) );
    }

    public function testSelectDistanceUnit() {
        $this->simulateLogin('me@example.com', true);
        $build_data = $this->buildController();
        $controller = $build_data[0];
        $owner  = $build_data[1];
        $plugin  = $build_data[2];
        $plugin_option  = $build_data[3];

        // radio options name, is admin, so form should be enabled
        $output = $controller->go();
        $this->assertNotNull($controller->option_elements);
        $this->assertEqual(
        PluginConfigurationController::FORM_RADIO_ELEMENT, $controller->option_elements['distance_unit']['type'] );
        $this->assertTrue(isset($controller->option_elements['distance_unit']['default_value']) );
        $this->assertFalse(isset($controller->option_required_message['distance_unit']));
        $v_mgr = $controller->getViewManager();
        $options_markup = $v_mgr->getTemplateDataItem('options_markup');
        $this->assertNotNull($options_markup);

        //parse option_markup
        $doc = new DOMDocument();
        // parse our html
        $doc->loadHTML("<html><body>" . $options_markup . "</body></html>");

        // we have a text form element with proper data
        $radio_div = $this->getElementById($doc, 'plugin_options_distance_unit');
        $radios = $radio_div->getElementsByTagName('input');
        $this->assertEqual(2, $radios->length);
        $this->assertEqual($radios->item(0)->getAttribute('value'), 'km');
        $this->assertEqual($radios->item(1)->getAttribute('value'), 'mi');
        $submit_p = $this->getElementById($doc, 'plugin_option_submit_p');
        $this->assertPattern('/type="submit".*Save Settings/', $doc->saveXML( $submit_p ) );
    }

    public function testGetPluginOptions() {
        $build_data = $this->buildController();
        $controller = $build_data[0];
        $options_hash = $controller->getPluginOptions();
        $this->assertEqual($options_hash['gmaps_api_key']->id, 2);
        $this->assertEqual($options_hash['gmaps_api_key']->option_name, 'gmaps_api_key');
        $this->assertEqual($options_hash['gmaps_api_key']->option_value, '1234');

        // get a single undefined option
        $this->assertFalse($controller->getPluginOption('not defined'));

        // get a single defined option
        $this->assertEqual($controller->getPluginOption('gmaps_api_key'), '1234');
    }

    /**
     * Test config not admin
     */
    public function testConfigOptionsNotAdmin() {
        $build_data = $this->buildController();
        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new GeoEncoderPluginConfigurationController($owner, 'geoencoder');
        $output = $controller->go();
        // we have a text form element with proper data
        $this->assertNoPattern('/Save Settings/', $output); // should have no submit option
        $this->assertNoPattern('/plugin_options_error_gmaps_api_key/', $output); // should have no api key
        $this->assertPattern('/var is_admin = false/', $output); // not a js admin
        $this->assertPattern('/var required_values_set = true/', $output); // is configured

        // not configured
        $prefix = Config::getInstance()->getValue('table_prefix');
        $namespace = $build_data[3]->columns['namespace'];
        OwnerMySQLDAO::$PDO->query("delete from " . $prefix . "options where namespace = '$namespace'");

        $controller = new GeoEncoderPluginConfigurationController($owner, 'geoencoder');
        $output = $controller->go();
        $this->assertPattern('/var required_values_set = false/', $output); // is not configured
    }

    /**
     * Test config isa admin
     */
    public function testConfigOptionsIsAdmin() {
        $build_data = $this->buildController();
        $this->simulateLogin('me@example.com', true);
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new GeoEncoderPluginConfigurationController($owner, 'geoencoder');
        $output = $controller->go();
        // we have a text form element with proper data
        $this->assertPattern('/Save Settings/', $output); // should have submit option
        $this->assertPattern('/plugin_options_error_gmaps_api_key/', $output); // should have api key option
        $this->assertPattern('/var is_admin = true/', $output); // is a js admin
        $this->assertPattern('/var required_values_set = true/', $output); // is configured

        //app not configured
        $prefix = Config::getInstance()->getValue('table_prefix');
        $namespace = $build_data[3]->columns['namespace'];
        OwnerMySQLDAO::$PDO->query("delete from " . $prefix . "options where namespace = '$namespace'");
        $controller = new GeoEncoderPluginConfigurationController($owner, 'geoencoder');
        $output = $controller->go();
        $this->assertPattern('/var required_values_set = false/', $output); // is not configured
    }

    private function buildController($build_owner=true) {
        $builder_owner = null;
        if ($build_owner) {
            $builder_owner = FixtureBuilder::build('owners', array('email' => 'me@example.com', 'user_activated' => 1));
        }
        $builder_plugin = FixtureBuilder::build('plugins', array('folder_name' => 'geoencoder', 'is_active' => 1) );
        $plugin_id = $builder_plugin->columns['last_insert_id'];

        $namespace = OptionDAO::PLUGIN_OPTIONS . '-' . $plugin_id;
        $builder_plugin_options = FixtureBuilder::build('options',
        array('namespace'=>$namespace, 'option_name' => 'gmaps_api_key', 'option_value' => "1234"));
        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new GeoEncoderPluginConfigurationController($owner, 'geoencoder');
        return array($controller, $builder_owner, $builder_plugin, $builder_plugin_options);
    }
}
