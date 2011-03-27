<?php
/**
 *
 * ThinkUp/tests/TestOfLogger.php
 *
 * Copyright (c) 2009-2011 Gina Trapani
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
 * @copyright 2009-2011 Gina Trapani
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
        $logger->setVerbosity(Logger::ALL_MSGS); // make sure we're at full verbosity
        $this->assertIsA($logger, 'Logger');

        //no username
        $logger->logInfo('Singleton logger should write this to the log', __METHOD__.','.__LINE__);
        $this->assertTrue(file_exists($logger_file), 'File created');
        $messages = file($config->getValue('log_location'));
        $this->assertPattern('/Singleton logger should write this to the log/', $messages[sizeof($messages) - 1]);

        //with username
        $logger->setUsername('angelinajolie');
        $logger->logInfo('Should write this to the log with a username', __METHOD__.','.__LINE__);
        $messages = null;
        $messages = file($config->getValue('log_location'));
        $this->assertPattern('/angelinajolie/', $messages[sizeof($messages) - 1]);
        $this->assertPattern('/Should write this to the log with a username/', $messages[sizeof($messages) - 1]);
        $logger->close();
    }

    public function testLimitedVerbosity() {
        $config = Config::getInstance();
        $logger_file = $config->getValue('log_location');
        $logger = Logger::getInstance();
        $this->assertIsA($logger, 'Logger');

        $logger = Logger::getInstance();
        $logger->setVerbosity(Logger::USER_MSGS);
        $logger->logInfo("Should not write this because it is not user level", __METHOD__.','.__LINE__);
        $logger->logUserInfo("Should write this because it is user level", __METHOD__.','.__LINE__);

        $messages = null;
        $messages = file($config->getValue('log_location'));
        $this->assertPattern('/Should write this because it is user level/', $messages[sizeof($messages) - 1]);
        $this->assertNoPattern('/Should not write this because it is not user level/',
        $messages[sizeof($messages) - 2]);
        $logger->close();
    }

    public function testFullVerbosity() {
        $config = Config::getInstance();
        $logger_file = $config->getValue('log_location');
        $logger = Logger::getInstance();
        $this->assertIsA($logger, 'Logger');

        $logger = Logger::getInstance();
        $logger->setVerbosity(Logger::ALL_MSGS);
        $logger->logInfo("Should write this because it is dev level", __METHOD__.','.__LINE__);
        $logger->logUserInfo("Should write this even though it is user level", __METHOD__.','.__LINE__);

        $messages = null;
        $messages = file($config->getValue('log_location'));
        $this->assertPattern('/Should write this because it is dev level/', $messages[sizeof($messages) - 2]);
        $this->assertPattern('/Should write this even though it is user level/', $messages[sizeof($messages) - 1]);
        $logger->close();
    }

    public function testMessageTypes() {
        $config = Config::getInstance();
        // set debugging to true
        $config->setValue('debug', true);
        $logger_file = $config->getValue('log_location');
        $logger = Logger::getInstance();
        $this->assertIsA($logger, 'Logger');

        $logger = Logger::getInstance();
        $logger->setVerbosity(Logger::ALL_MSGS);
        $logger->logInfo("This is an info message", __METHOD__.','.__LINE__);
        $logger->logError("This is an error message", __METHOD__.','.__LINE__);
        $logger->logSuccess("This is a success message", __METHOD__.','.__LINE__);
        $logger->logDebug("This is a debug message", __METHOD__.','.__LINE__);

        $messages = null;
        $messages = file($config->getValue('log_location'));
        $this->assertPattern('/This is an info message/', $messages[sizeof($messages) - 4]);
        $this->assertPattern('/This is an error message/', $messages[sizeof($messages) - 3]);
        $this->assertPattern('/This is a success message/', $messages[sizeof($messages) - 2]);
        $this->assertPattern('/This is a debug message/', $messages[sizeof($messages) - 1]);

        $logger->setVerbosity(Logger::USER_MSGS);
        $logger->logUserInfo("This is a user info message", __METHOD__.','.__LINE__);
        $logger->logInfo("This is an info message", __METHOD__.','.__LINE__);
        $logger->logUserError("This is a user error message", __METHOD__.','.__LINE__);
        $logger->logError("This is an info message", __METHOD__.','.__LINE__);
        $logger->logUserSuccess("This is a user success message", __METHOD__.','.__LINE__);
        $logger->logSuccess("This is an info message", __METHOD__.','.__LINE__);
        $logger->logDebug("This is a debug message", __METHOD__.','.__LINE__);

        $messages = null;
        $messages = file($config->getValue('log_location'));
        $this->assertPattern('/INFO | TestOfLogger::testMessageTypes,123 | This is a user info message/',
        $messages[sizeof($messages) - 3]);
        $this->assertPattern('/ERRO | TestOfLogger::testMessageTypes,125 | This is a user error message/',
        $messages[sizeof($messages) - 2]);
        $this->assertPattern('/SUCC | TestOfLogger::testMessageTypes,127 | This is a user success message/',
        $messages[sizeof($messages) - 1]);
        $logger->close();
    }

    public function testAllMsgsNoDebug() {
        $config = Config::getInstance();
        // set debugging to false
        $config->setValue('debug', false);
        $logger_file = $config->getValue('log_location');
        $logger = Logger::getInstance();
        $this->assertIsA($logger, 'Logger');

        $logger = Logger::getInstance();
        $logger->setVerbosity(Logger::ALL_MSGS);
        $logger->logInfo("This is an info message", __METHOD__.','.__LINE__);
        $logger->logError("This is an error message", __METHOD__.','.__LINE__);
        $logger->logSuccess("This is a success message", __METHOD__.','.__LINE__);
        // with debug set to false, this should not log anything
        $logger->logDebug("This is a debug message", __METHOD__.','.__LINE__);

        $messages = null;
        $messages = file($config->getValue('log_location'));
        $this->assertPattern('/This is an info message/', $messages[sizeof($messages) - 3]);
        $this->assertPattern('/This is an error message/', $messages[sizeof($messages) - 2]);
        $this->assertPattern('/This is a success message/', $messages[sizeof($messages) - 1]);
    }

    public function testHTMLOutput() {
        $config = Config::getInstance();
        $logger_file = $config->getValue('log_location');
        $logger = Logger::getInstance();
        $this->assertIsA($logger, 'Logger');

        $logger->setVerbosity(Logger::ALL_MSGS);
        $logger->enableHTMLOutput();
        $logger->logInfo("This is an info message", __METHOD__.','.__LINE__);
        $logger->logError("This is an error message", __METHOD__.','.__LINE__);
        $logger->logSuccess("This is a success message", __METHOD__.','.__LINE__);

        $messages = null;
        $messages = file($config->getValue('log_location'));
        $this->assertPattern('/This is an info message/', $messages[sizeof($messages) - 3]);
        $this->assertPattern('/This is an error message/', $messages[sizeof($messages) - 2]);
        $this->assertPattern('/This is a success message/', $messages[sizeof($messages) - 1]);

        $logger->setVerbosity(Logger::USER_MSGS);
        $logger->logUserInfo("This is a user info message", __METHOD__.','.__LINE__);
        $logger->logInfo("This is an info message", __METHOD__.','.__LINE__);
        $logger->logUserError("This is a user error message", __METHOD__.','.__LINE__);
        $logger->logError("This is an info message", __METHOD__.','.__LINE__);
        $logger->logUserSuccess("This is a user success message", __METHOD__.','.__LINE__);
        $logger->logSuccess("This is an info message", __METHOD__.','.__LINE__);
        $logger->logDebug("This is a debugging message", __METHOD__.','.__LINE__);

        $messages = null;
        $messages = file($config->getValue('log_location'));
        $this->assertPattern('/TestOfLogger: <span style="color:black">This is a user info message<\/span><br >/',
        $messages[sizeof($messages) - 3]);
        $this->assertPattern('/TestOfLogger: <span style="color:red">This is a user error message<\/span><br >/',
        $messages[sizeof($messages) - 2]);
        $this->assertPattern('/TestOfLogger: <span style="color:green">This is a user success message<\/span><br >/',
        $messages[sizeof($messages) - 1]);
        $logger->close();
    }

    public function testTerminalLogger() {
        $config = Config::getInstance();
        $config->setValue('log_location', false);

        $logger = Logger::getInstance();
        //        $logger->logInfo('Singleton logger should echo this', __METHOD__.','.__LINE__);
    }
}
