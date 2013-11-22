<?php
/**
 *
 * ThinkUp/tests/TestOfReporter.php
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

class TestOfReporter extends ThinkUpUnitTestCase {

    public function testReportVersion() {

        $config = Config::getInstance();
        $site_root_path = $config->getValue('site_root_path');

        //create new instance
        $instance = new Instance();
        $instance->network_username = 'danica mckellar';
        $instance->network = 'twitter';
        $_SERVER['HTTP_HOST'] = "mytestthinkup";

        //report version
        $result = Reporter::reportVersion($instance);
        $this->assertPattern("/http:\/\/thinkup.com\/version.php\?v\=/", $result[0]);
        $this->assertEqual($result[1], "http://mytestthinkup$site_root_path?u=danica+mckellar&n=twitter");
        $this->assertEqual($result[2], 200);
    }
}