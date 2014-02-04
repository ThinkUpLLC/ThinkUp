<?php
/**
 *
 * ThinkUp/webapp/plugins/hellothinkup/tests/TestOfHelloThinkUpPluginConfigurationController.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Mark Wilkie
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
 * Test of TestOfHelloThinkUpPluginConfigurationController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Mark Wilkie
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';
require_once THINKUP_ROOT_PATH.'tests/classes/class.ThinkUpBasicUnitTestCase.php';

require_once THINKUP_ROOT_PATH.
'webapp/plugins/hellothinkup/controller/class.HelloThinkUpPluginConfigurationController.php';

class TestOfHelloThinkUpPluginConfigurationController extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        $webapp_plugin_registrar->registerPlugin('hellothinkup', 'HelloThinkUpPlugin');
    }

    public function tearDown(){
        parent::tearDown();
    }

    public function testConstructor() {
        $controller = new HelloThinkUpPluginConfigurationController(null, 'hellothinkup');
        $this->assertTrue(isset($controller), 'constructor test');
    }

    public function testOutput() {
        //not logged in, no owner set
        $controller = new HelloThinkUpPluginConfigurationController(null, 'hellothinkup');
        $output = $controller->go();
        $this->assertPattern( '/session\/login.php\?redirect\=/', $controller->redirect_destination);

        // logged in
        // build a user
        $builder = FixtureBuilder::build('owners', array('email' => 'me@example.com', 'user_activated' => 1) );

        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new HelloThinkUpPluginConfigurationController($owner, 'hellothinkup');
        $output = $controller->go();
        $v_mgr = $controller->getViewManager();
        $message = $v_mgr->getTemplateDataItem('message');
        $this->assertEqual($message,
        'Hello ThinkUp world! This is an example plugin configuration page for  me@example.com.');
    }

    public function testTextInputSize() {
        //not logged in, no owner set
        $controller = new HelloThinkUpPluginConfigurationController(null, 'hellothinkup');
        $builder = FixtureBuilder::build('owners', array('email' => 'me@example.com', 'user_activated' => 1) );

        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new HelloThinkUpPluginConfigurationController($owner, 'hellothinkup');
        $output = $controller->go();
        $this->assertEqual($controller->option_elements['testname']['size'], 40);
    }

    public function testOptionList2HashByOptionName() {
        $build_data = $this->buildController();
        $controller = $build_data[0];
        $options_hash = $controller->optionList2HashByOptionName();
        $this->assertEqual($options_hash['testname']->id, 2);
        $this->assertEqual($options_hash['testname']->option_name, 'testname');
        $this->assertEqual($options_hash['testname']->option_value, 'Hal');

    }

    public function testAddTextOptionNotAdmin() {
        $build_data = $this->buildController();
        $controller = $build_data[0];
        $owner  = $build_data[1];
        $plugin  = $build_data[2];
        $plugin_option  = $build_data[3];

        // just name, not an admin, so view onluy
        // $controller->addPluginOption(PluginConfigurationController::FORM_TEXT_ELEMENT, array('name' => 'testname'));
        $output = $controller->go();
        $this->assertNotNull( $controller->option_elements);
        $this->assertEqual( count($controller->option_elements), 7);
        $this->assertNotNull( $controller->option_elements['testname']);
        $this->assertEqual(
        PluginConfigurationController::FORM_TEXT_ELEMENT, $controller->option_elements['testname']['type'] );
        $this->assertTrue( isset($controller->option_elements['testname']['default_value']) );
        $this->assertEqual( count($controller->option_headers), 2);
        $this->assertTrue( isset($controller->option_headers['testname']));
        $this->assertEqual( count($controller->option_not_required), 3);
        $this->assertFalse( isset($controller->option_not_required['testname']));
        $this->assertEqual( count($controller->option_required_message), 2);
        $this->assertTrue( isset($controller->option_required_message['testname']));
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
        // var_dump($build_data[1]);
        $controller = $build_data[0];
        $owner  = $build_data[1];
        $plugin  = $build_data[2];
        $plugin_option  = $build_data[3];

        // just name, is admin, so form should be enabled
        $output = $controller->go();
        $this->assertNotNull( $controller->option_elements);
        $this->assertEqual( count($controller->option_elements), 7);
        $this->assertNotNull( $controller->option_elements['testname']);
        $this->assertEqual(
        PluginConfigurationController::FORM_TEXT_ELEMENT, $controller->option_elements['testname']['type'] );
        $this->assertTrue( isset($controller->option_elements['testname']['default_value']) );
        $this->assertEqual( count($controller->option_headers), 2);
        $this->assertTrue( isset($controller->option_headers['testname']));
        $this->assertEqual( count($controller->option_not_required), 3);
        $this->assertFalse( isset($controller->option_not_required['testname']));
        $this->assertEqual( count($controller->option_required_message), 2);
        $this->assertTrue( isset($controller->option_required_message['testname']));
        $v_mgr = $controller->getViewManager();
        $options_markup = $v_mgr->getTemplateDataItem('options_markup');
        $this->assertNotNull($options_markup);

        $this->assertEqual( isset($controller->option_elements['RegKey']['validation_regex']), '^\d+$' );

        //parse option_markup
        $doc = new DOMDocument();
        // parse our html
        $doc->loadHTML("<html><body>" . $options_markup . "</body></html>");

        // we have a text form element with proper data
        $input_field = $this->getElementById($doc, 'plugin_options_testname');
        $this->assertEqual($input_field->getAttribute('value'), $plugin_option->columns['option_value']);

        // var_dump("<html><body>" . $options_markup . "</body></html>");

        // submit and elemnts should be disbaled
        $this->assertFalse($input_field->getAttribute('disabled'));
        $submit_p = $this->getElementById($doc, 'plugin_option_submit_p');
        $this->assertPattern('/type="submit".*Save Settings/', $doc->saveXML( $submit_p ) );


    }

    public function testAddRadioOptions() {
        $this->simulateLogin('me@example.com', true);
        $build_data = $this->buildController();
        $controller = $build_data[0];
        $owner  = $build_data[1];
        $plugin  = $build_data[2];
        $plugin_option  = $build_data[3];

        // radio options name, is admin, so form should be enabled
        $output = $controller->go();
        $this->assertNotNull( $controller->option_elements);
        $this->assertEqual( count($controller->option_elements), 7);
        $this->assertNotNull( $controller->option_elements['testname']);
        $this->assertEqual(
        PluginConfigurationController::FORM_RADIO_ELEMENT, $controller->option_elements['testradio']['type'] );
        $this->assertTrue( isset($controller->option_elements['testradio']['default_value']) );
        $this->assertEqual( count($controller->option_headers), 2);
        $this->assertFalse( isset($controller->option_required_message['testradio']));
        $v_mgr = $controller->getViewManager();
        $options_markup = $v_mgr->getTemplateDataItem('options_markup');
        $this->assertNotNull($options_markup);

        //parse option_markup
        $doc = new DOMDocument();
        // parse our html
        $doc->loadHTML("<html><body>" . $options_markup . "</body></html>");

        // we have a text form element with proper data
        $radio_div = $this->getElementById($doc, 'plugin_options_testradio');
        $radios = $radio_div->getElementsByTagName('input');
        $this->assertEqual(3, $radios->length);
        $this->assertEqual( $radios->item(0)->getAttribute('value'), '1');
        $this->assertEqual( $radios->item(1)->getAttribute('value'), '2');
        $this->assertEqual( $radios->item(2)->getAttribute('value'), '3');
        $submit_p = $this->getElementById($doc, 'plugin_option_submit_p');
        $this->assertPattern('/type="submit".*Save Settings/', $doc->saveXML( $submit_p ) );

    }

    public function testAddSelectOptions() {
        $this->simulateLogin('me@example.com', true);
        $build_data = $this->buildController();
        $controller = $build_data[0];
        $owner  = $build_data[1];
        $plugin  = $build_data[2];
        $plugin_option  = $build_data[3];

        // radio options name, is admin, so form should be enabled
        $output = $controller->go();
        $this->assertNotNull( $controller->option_elements);
        $this->assertEqual( count($controller->option_elements), 7);
        $this->assertNotNull( $controller->option_elements['testbirthyear']);
        $this->assertEqual(
        PluginConfigurationController::FORM_SELECT_ELEMENT, $controller->option_elements['testbirthyear']['type'] );
        $this->assertTrue( isset($controller->option_elements['testbirthyear']['default_value']) );
        $this->assertFalse( isset($controller->option_required_message['testbirthyear']));
        $v_mgr = $controller->getViewManager();
        $options_markup = $v_mgr->getTemplateDataItem('options_markup');
        $this->assertNotNull($options_markup);

        //parse option_markup
        $doc = new DOMDocument();
        // parse our html
        $doc->loadHTML("<html><body>" . $options_markup . "</body></html>");

        // we have a text form element with proper data
        $select = $this->getElementById($doc, 'plugin_options_testbirthyear');
        $options = $select->getElementsByTagName('option');
        $this->assertEqual(111, $options->length);
        $this->assertEqual( $options->item(0)->getAttribute('value'), '1900');
        $this->assertEqual( $options->item(50)->getAttribute('value'), '1950');
        $this->assertEqual( $options->item(110)->getAttribute('value'), '2010');

        $submit_p = $this->getElementById($doc, 'plugin_option_submit_p');
        $this->assertPattern('/type="submit".*Save Settings/', $doc->saveXML( $submit_p ) );

    }

    public function testAddAdvancedOptions() {
        $this->simulateLogin('me@example.com', true);
        $build_data = $this->buildController();
        $controller = $build_data[0];
        $owner  = $build_data[1];
        $plugin  = $build_data[2];
        $plugin_option  = $build_data[3];

        // radio options name, is admin, so form should be enabled
        $output = $controller->go();
        $v_mgr = $controller->getViewManager();
        $options_markup = $v_mgr->getTemplateDataItem('options_markup');
        $this->assertNotNull($options_markup);

        //parse option_markup
        $doc = new DOMDocument();
        // parse our html
        $doc->loadHTML("<html><body>" . $options_markup . "</body></html>");

        $show_adv = $this->getElementById($doc, 'adv-flip-prompt');
        $this->assertPattern('/Show/', $doc->saveXML( $show_adv ) );

    }
    public function testGetPluginOptions() {
        $build_data = $this->buildController();
        $controller = $build_data[0];
        $options_hash = $controller->getPluginOptions();
        $this->assertEqual($options_hash['testname']->id, 2);
        $this->assertEqual($options_hash['testname']->option_name, 'testname');
        $this->assertEqual($options_hash['testname']->option_value, 'Hal');

        // get a single undefined option
        $this->assertFalse($controller->getPluginOption('not defined'));

        // get a single defined option
        $this->assertEqual($controller->getPluginOption('testname'), 'Hal');


    }

    private function buildController() {
        $builder_owner = FixtureBuilder::build('owners', array('email' => 'me@example.com', 'user_activated' => 1) );
        $builder_plugin = FixtureBuilder::build('plugins', array('folder_name' => 'hellothinkup', 'is_active' => 1) );
        $plugin_id = $builder_plugin->columns['last_insert_id'];
        $namespace = OptionDAO::PLUGIN_OPTIONS . '-' .$plugin_id;
        $builder_plugin_options =
        FixtureBuilder::build('options',
        array('namespace' => $namespace, 'option_name' => 'testname', 'option_value' => "Hal") );
        $this->simulateLogin('me@example.com');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        $controller = new HelloThinkUpPluginConfigurationController($owner, 'hellothinkup');
        return array($controller, $builder_owner, $builder_plugin, $builder_plugin_options);
    }

    function getElementById($doc, $id) {
        $xpath = new DOMXPath($doc);
        return $xpath->query("//*[@id='$id']")->item(0);
    }
}
