<?php
/**
 *
 * ThinkUp/tests/TestOfProfiler.php
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
 * Test of Profiler object
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfProfiler extends ThinkUpBasicUnitTestCase {
    /**
     * Test Profiler singleton instantiation
     */
    public function testProfilerSingleton() {
        $profiler = Profiler::getInstance();
        $this->assertTrue(isset($profiler), 'constructor');
        $this->assertIsA($profiler, 'Profiler', 'object type');
    }

    public function testIsEnabledServerSet() {
        $config = Config::getInstance();
        $config->setValue('enable_profiler', true);
        $_SERVER['HTTP_HOST'] = 'myserver';
        $this->assertTrue(Profiler::isEnabled());
    }

    public function testIsEnabledServerNotSet() {
        $config = Config::getInstance();
        $config->setValue('enable_profiler', true);
        $this->assertTrue(!Profiler::isEnabled());
    }

    public function testAdd() {
        $profiler = Profiler::getInstance();
        $profiler->add(0.02503434, 'My 1st action');
        $profiler->add(0.02303434, 'My 2nd action');
        $profiler->add(0.12003434, 'My 3rd action');
        $profiler->add(0.62003434, 'My 4th action', true, 10);
        $profiler->add(0.40003434, 'My 5th action', true);
        $actions = $profiler->getProfile();
        $this->assertEqual($actions[0]['time'], '0.620');
        $this->assertEqual($actions[0]['action'], 'My 4th action');
        $this->assertEqual($actions[0]['num_rows'], 10);
        $this->assertEqual($profiler->total_queries, 2);
    }
}
