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

    public function testNewLoggerSingleton() {
        $config = Config::getInstance();
        $logger_file = $config->getValue('log_location');

        $logger = Logger::getInstance();
        $logger->logStatus('Singleton logger should write this to the log', get_class($this));
        $this->assertTrue(file_exists($logger_file), 'File created');
        $messages = file($config->getValue('log_location'));
        $this->assertWantedPattern('/Singleton logger should write this to the log/', $messages[sizeof($messages) - 1]);
        $logger->setUsername('single-ton');
        $logger->logStatus('Should write this to the log with a username', get_class($this));
        $this->assertWantedPattern('/single-ton | TestOfLogger:Singleton logger should write this to the log/',
        $messages[sizeof($messages) - 1]);
        $logger->close();
    }
}
