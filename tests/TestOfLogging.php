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
        global $THINKTANK_CFG;
        // delete our log file if it exists
        if (file_exists($THINKTANK_CFG['log_location'])) {
            unlink($THINKTANK_CFG['log_location']);
        }
    }
    
    function tearDown() {
        global $THINKTANK_CFG;
        // delete our log file if it exists
        if (file_exists($THINKTANK_CFG['log_location'])) {
            unlink($THINKTANK_CFG['log_location']);
        }
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
    
    function testNewLoggerSingleton() {
        global $THINKTANK_CFG;
        $logger = Logger::getInstance();
        $logger->logStatus('Singleton logger should write this to the log', get_class($this));
        $this->assertTrue(file_exists($THINKTANK_CFG['log_location']), 'File created');
        $messages = file($THINKTANK_CFG['log_location']);
        $this->assertWantedPattern('/Singleton logger should write this to the log/', $messages[sizeof($messages) - 1]);
        $logger->setUsername('single-ton');
        $logger->logStatus('Should write this to the log with a username', get_class($this));
        $this->assertWantedPattern('/single-ton | TestOfLogging:Singleton logger should write this to the log/', $messages[sizeof($messages) - 1]);
        $logger->close();
    }
}
?>
