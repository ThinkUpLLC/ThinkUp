<?php
/**
 *
 * ThinkUp/tests/TestOfWebapp.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
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
 * Test Webapp object
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

require_once THINKUP_WEBAPP_PATH.'plugins/hellothinkup/model/class.HelloThinkUpPlugin.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitter/model/class.TwitterPlugin.php';
require_once THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/model/class.TwitterRealtimePlugin.php';

class TestOfWebapp extends ThinkUpUnitTestCase {

    /**
     * Test Webapp singleton instantiation
     */
    public function testWebappSingleton() {
        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        //test default active plugin
        $this->assertEqual($webapp_plugin_registrar->getActivePlugin(), "twitter");
    }

    /**
     * Test activePlugin getter/setter
     */
    public function testWebappGetSetActivePlugin() {
        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        $this->assertEqual($webapp_plugin_registrar->getActivePlugin(), "twitter");
        $webapp_plugin_registrar->setActivePlugin('facebook');
        $this->assertEqual($webapp_plugin_registrar->getActivePlugin(), "facebook");

        //make sure another instance reports back the same values
        $webapp_plugin_registrar_two = PluginRegistrarWebapp::getInstance();
        $this->assertEqual($webapp_plugin_registrar_two->getActivePlugin(), "facebook");
    }
}
