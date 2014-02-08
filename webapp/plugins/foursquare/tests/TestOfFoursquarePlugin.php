<?php
/**
 *
 * ThinkUp/webapp/plugins/foursquare/tests/TestOfFoursquarePlugin.php
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
 * Test of Foursquare Plugin
 *
 * Tests the foursquare plugin class
 *
 * @author Aaron Kalair <aaronkalair[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Aaron Kalair
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';
require_once THINKUP_ROOT_PATH.'tests/classes/class.ThinkUpBasicUnitTestCase.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/foursquare/model/class.FoursquarePlugin.php';

class TestOfFoursquarePlugin extends ThinkUpUnitTestCase {

    public function setUp(){
        // Call ThinkUpUnitTestCase's constructor
        parent::setUp();
        // Get ourselves an instance
        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        // Register the foursquare plugin and set its folder
        $webapp_plugin_registrar->registerPlugin('foursquare', 'FoursquarePlugin');
        // Make the plugin active
        $webapp_plugin_registrar->setActivePlugin('foursquare');
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        // Create a new foursquare plugin
        $plugin = new FoursquarePlugin();
        // Check it was created
        $this->assertNotNull($plugin);
        // Check its a foursquare plugin
        $this->assertIsA($plugin, 'FoursquarePlugin');
        // Check 2 settings are required (client id and secret)
        $this->assertEqual(count($plugin->required_settings), 2);
        // Check we know the plugin still needs configuring
        $this->assertFalse($plugin->isConfigured());
    }
}
