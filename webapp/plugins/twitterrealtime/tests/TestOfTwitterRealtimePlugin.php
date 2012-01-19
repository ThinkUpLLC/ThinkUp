<?php
/**
 *
 * ThinkUp/webapp/plugins/twitterrealtime/tests/TestOfTwitterRealtimePlugin.php
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
 *
 * Test of TestOfTwitterRealtimePlugin
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2012 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
require_once 'tests/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';
require_once THINKUP_ROOT_PATH.'tests/classes/class.ThinkUpBasicUnitTestCase.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/twitterrealtime/model/class.TwitterRealtimePlugin.php';


class TestOfTwitterRealtimePlugin extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
        $webapp = Webapp::getInstance();
        $webapp->registerPlugin('google+', 'TwitterRealtimePlugin');
        $webapp->setActivePlugin('google+');
    }

    public function tearDown(){
        parent::tearDown();
    }

    public function testConstructor() {
        $plugin = new TwitterRealtimePlugin();
        $this->assertNotNull($plugin);
        $this->assertIsA($plugin, 'TwitterRealtimePlugin');
        $this->assertEqual(count($plugin->required_settings), 0);
        $this->assertTrue($plugin->isConfigured());
    }
}