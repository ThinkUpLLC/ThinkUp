<?php
/**
 *
 * webapp/plugins/youtube/tests/TestOfYouTubePlugin.php
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
 * Test of YouTube Plugin
 *
 * Tests the YouTube plugin class
 *
 * Copyright (c) 2013 Aaron Kalair
 *
 * @author Aaron Kalair <aaronkalair[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Aaron Kalair
 */

require_once 'tests/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';
require_once THINKUP_ROOT_PATH.'tests/classes/class.ThinkUpBasicUnitTestCase.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/youtube/model/class.YouTubePlugin.php';

class TestOfYouTubePlugin extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
        $webapp_plugin_registrar->registerPlugin('youtube', 'YouTubePlugin');
        $webapp_plugin_registrar->setActivePlugin('youtube');
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $plugin = new YoutubePlugin();
        $this->assertNotNull($plugin);
        $this->assertIsA($plugin, 'YouTubePlugin');
        $this->assertEqual(count($plugin->required_settings), 2);
        $this->assertFalse($plugin->isConfigured());
    }

}
