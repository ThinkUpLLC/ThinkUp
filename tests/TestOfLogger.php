<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkUpBasicUnitTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Logger.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.LoggerSlowSQL.php';
require_once $SOURCE_ROOT_PATH.'webapp/config.inc.php';


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
        global $THINKUP_CFG;

        $logger = Logger::getInstance();
        $logger->logStatus('Singleton logger should write this to the log', get_class($this));
        $this->assertTrue(file_exists($THINKUP_CFG['log_location']), 'File created');
        $messages = file($THINKUP_CFG['log_location']);
        $this->assertWantedPattern('/Singleton logger should write this to the log/', $messages[sizeof($messages) - 1]);
        $logger->setUsername('single-ton');
        $logger->logStatus('Should write this to the log with a username', get_class($this));
        $this->assertWantedPattern('/single-ton | TestOfLogger:Singleton logger should write this to the log/',
        $messages[sizeof($messages) - 1]);
        $logger->close();
    }
}
