<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'extlib/simpletest/autorun.php';
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
