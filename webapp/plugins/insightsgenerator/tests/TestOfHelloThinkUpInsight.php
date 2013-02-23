<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/tests/TestOfHelloThinkUpInsight.php
 *
 * Copyright (c) 2012-2013 Gina Trapani
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
 * Test of Hello ThinkUp Insight
 *
 * Test for the example Hello ThinkUp insight class (and others).
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

require_once dirname(__FILE__) . '/../../../../tests/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/hellothinkupinsight.php';
require_once THINKUP_ROOT_PATH. 'webapp/plugins/insightsgenerator/insights/flashbacks.php';

class TestOfHelloThinkUpInsight extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testHelloThinkUpInsight() {
        $posts = array();
        $instance = new Instance();
        $hello_thinkup_insight_plugin = new HelloThinkUpInsight();
        $hello_thinkup_insight_plugin->generateInsight($instance, $posts, 3);
        $this->assertTrue(true);
    }

    public function testFlashbacksInsight() {
        $posts = array();
        $instance = new Instance();
        $flashback_insight_plugin = new FlashbackInsight();
        $flashback_insight_plugin->generateInsight($instance, $posts, 3);
        $this->assertTrue(true);
    }
}
