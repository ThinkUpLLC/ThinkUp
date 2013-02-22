<?php
/**
 *
 * ThinkUp/tests/TestOfPlugin.php
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
 * Test of Plugin class
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfPlugin extends ThinkUpUnitTestCase {

    public function testIsConfigured() {
        $plugin = new Plugin(array('id'=>1, 'folder_name'=>'twitter', 'name'=>'Twitter Plugin', 'description'=>'',
        'version'=>'0.10', 'author'=>'Gina Trapani', 'homepage'=>'', 'is_active'=>1));

        //no options, no requirements
        $this->assertTrue($plugin->isConfigured());

        //option, no requirements
        $builders[] = FixtureBuilder::build('options', array('namespace'=>'plugin_options-1', 'option_name'=>'api_key',
        'option_value'=>'mykeyeeeee'));
        $plugin->options_hash = null;
        $this->assertTrue($plugin->isConfigured());

        //requirement AND option
        $plugin->addRequiredSetting('api_key');
        $plugin->options_hash = null;
        $this->assertTrue($plugin->isConfigured());

        //another requirements without a matching setting
        $plugin->addRequiredSetting('another_thinger');
        $plugin->options_hash = null;
        $this->assertFalse($plugin->isConfigured());

        //2 requirements, 2 set
        $builders[] = FixtureBuilder::build('options', array('namespace'=>'plugin_options-1',
        'option_name'=>'another_thinger', 'option_value'=>'mythingerrrrr'));
        $plugin->options_hash = null;
        $this->assertTrue($plugin->isConfigured());

        $builders[] = FixtureBuilder::build('options', array('namespace'=>'plugin_options-1',
        'option_name'=>'yet_another_thinger', 'option_value'=>'mythingerrrrr222'));
        $plugin->options_hash = null;
        $this->assertTrue($plugin->isConfigured());
    }
}