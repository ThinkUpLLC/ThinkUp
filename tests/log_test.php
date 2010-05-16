<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'webapp/model/class.Logger.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.LoggerSlowSQL.php';
require_once $SOURCE_ROOT_PATH.'webapp/config.inc.php';


class TestOfLogging extends UnitTestCase {
    function TestOfLogging() {
        $this->UnitTestCase('Log class test');
    }

    function setUp() {
    }

    function tearDown() {
    }

    function testCreatingNewLogger() {
        global $THINKTANK_CFG;
        $logger = new Logger($THINKTANK_CFG['log_location']);
        $logger->logStatus('Should write this to the log', get_class($this));
        $this->assertTrue(file_exists($THINKTANK_CFG['log_location']), 'File created');

        $messages = file($THINKTANK_CFG['log_location']);
        $this->assertWantedPattern('/Should write this to the log/', $messages[sizeof($messages) - 1]);

        $logger->setUsername('ginatrapani');
        $logger->logStatus('Should write this to the log with a username', get_class($this));
        $this->assertWantedPattern('/ginatrapani | TestOfLogging:Should write this to the log/', $messages[sizeof($messages) - 1]);

        $logger->close();

    }
}
?>
