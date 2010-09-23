<?php
/**
 *
 * ThinkUp/webapp/plugins/geoencoder/tests/TestOfGeoEncoderPluginConfigurationController.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Ekansh Preet Singh
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
*/
if ( !isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS ) {
    require_once '../../../../tests/config.tests.inc.php';
}
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/geoencoder/controller/class.GeoEncoderPluginConfigurationController.php';

/**
 * Test of TestOfGeoEncoderPluginConfigurationController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Ekansh Preet Singh
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
class TestOfGeoEncoderPluginConfigurationController extends ThinkUpUnitTestCase {

    public function __construct() {
        $this->UnitTestCase('TestOfGeoEncoderPluginConfigurationController class test');
    }

    public function setUp(){
        parent::setUp();
        $webapp = Webapp::getInstance();
        $webapp->registerPlugin('geoencoder', 'GeoEncoderPlugin');
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
        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('You must <a href="'.$config->getValue('site_root_path').
        'session/login.php">log in</a> to do this.', $v_mgr->getTemplateDataItem('errormsg'));

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
        $this->assertEqual($controller->option_headers['gmaps_api_key'], 'GeoEncoder Plugin Options');
        $this->assertEqual($controller->option_required_message['gmaps_api_key'],
        'Please enter your Google Maps API Key');

        $v_mgr = $controller->getViewManager();
        $options_markup = $v_mgr->getTemplateDataItem('options_markup');
        $this->assertNotNull($options_markup);

        //parse option_markup
        $doc = new DOMDocument();
        // parse our html
        $doc = DOMDocument::loadHTML("<html><body>" . $options_markup . "</body></html>");

        // we have a text form element with proper data
        $input_field = $this->getElementById($doc, 'plugin_options_gmaps_api_key');
        $this->assertTrue($input_field->getAttribute('disabled'));
        $submit_p = $this->getElementById($doc, 'plugin_option_submit_p');
        $this->assertPattern('/Note: Editing disabled for non admin users/', $submit_p->nodeValue);

        $is_admin = 1;
        $this->simulateLogin('admin@example.com', true);
        $build_data = $this->buildController(false);
        $controller = $build_data[0];
        $owner  = $build_data[1];
        $plugin  = $build_data[2];
        $plugin_option  = $build_data[3];

        // just name, not an admin, so view only
        $output = $controller->go();

        $v_mgr = $controller->getViewManager();
        $options_markup = $v_mgr->getTemplateDataItem('options_markup');
        // parse our html
        $doc = DOMDocument::loadHTML("<html><body>" . $options_markup . "</body></html>");

        // we have a text form element with proper data
        $input_field = $this->getElementById($doc, 'plugin_options_gmaps_api_key');

        // submit and elemnts should be disbaled
        $this->assertFalse($input_field->getAttribute('disabled'));
        $submit_p = $this->getElementById($doc, 'plugin_option_submit_p');
        $this->assertPattern('/type="submit".*save options/', $doc->saveXML( $submit_p ) );
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
        $doc = DOMDocument::loadHTML("<html><body>" . $options_markup . "</body></html>");

        // we have a text form element with proper data
        $radio_div = $this->getElementById($doc, 'plugin_options_distance_unit');
        $radios = $radio_div->getElementsByTagName('input');
        $this->assertEqual(2, $radios->length);
        $this->assertEqual($radios->item(0)->getAttribute('value'), 'km');
        $this->assertEqual($radios->item(1)->getAttribute('value'), 'mi');
        $submit_p = $this->getElementById($doc, 'plugin_option_submit_p');
        $this->assertPattern('/type="submit".*save options/', $doc->saveXML( $submit_p ) );
    }

    public function testGetPluginOptions() {
        $build_data = $this->buildController();
        $controller = $build_data[0];
        $options_hash = $controller->getPluginOptions();
        $this->assertEqual($options_hash['gmaps_api_key']->id, 1);
        $this->assertEqual($options_hash['gmaps_api_key']->option_name, 'gmaps_api_key');
        $this->assertEqual($options_hash['gmaps_api_key']->option_value, '1234');

        // get a single undefined option
        $this->assertFalse($controller->getPluginOption('not defined'));

        // get a single defined option
        $this->assertEqual($controller->getPluginOption('gmaps_api_key'), '1234');
    }

    private function buildController($build_owner=true) {
        $builder_owner = null;
        if ($build_owner) {
            $builder_owner = FixtureBuilder::build('owners', array('email' => 'me@example.com', 'user_activated' => 1));
        }
        $builder_plugin = FixtureBuilder::build('plugins', array('folder_name' => 'geoencoder', 'is_active' => 1) );
        $plugin_id = $builder_plugin->columns['last_insert_id'];
        $builder_plugin_options = FixtureBuilder::build('plugin_options',
        array('plugin_id' => $plugin_id, 'option_name' => 'gmaps_api_key', 'option_value' => "1234"));
        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new GeoEncoderPluginConfigurationController($owner, 'geoencoder');
        return array($controller, $builder_owner, $builder_plugin, $builder_plugin_options);
    }

    protected function getElementById($doc, $id) {
        $xpath = new DOMXPath($doc);
        return $xpath->query("//*[@id='$id']")->item(0);
    }
}