<?php
/**
 *
 * ThinkUp/tests/TestOfLogger.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfLogger extends ThinkUpBasicUnitTestCase {
    public function __construct() {
        $this->UnitTestCase('Logger class test');
    }

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testFileLogger() {
        $config = Config::getInstance();
        $logger_file = $config->getValue('log_location');
        $logger = Logger::getInstance();
        $this->assertIsA($logger, 'Logger');

        //no username
        $logger->logStatus('Singleton logger should write this to the log', get_class($this));
        $this->assertTrue(file_exists($logger_file), 'File created');
        $messages = file($config->getValue('log_location'));
        $this->assertPattern('/Singleton logger should write this to the log/', $messages[sizeof($messages) - 1]);

        //with username
        $logger->setUsername('angelinajolie');
        $logger->logStatus('Should write this to the log with a username', get_class($this));
        $messages = null;
        $messages = file($config->getValue('log_location'));
        $this->assertPattern('/angelinajolie/', $messages[sizeof($messages) - 1]);
        $this->assertPattern('/Should write this to the log with a username/', $messages[sizeof($messages) - 1]);
        $logger->close();
    }

    public function testTerminalLogger() {
        $config = Config::getInstance();
        $config->setValue('log_location', false);

        $logger = Logger::getInstance();
        //        $logger->logStatus('Singleton logger should echo this', get_class($this));
    }
}
