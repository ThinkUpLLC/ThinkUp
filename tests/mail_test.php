<?php 
require_once (dirname(__FILE__).'/simpletest/autorun.php');


require_once (dirname(__FILE__).'/config.tests.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);


class TestOfMail extends UnitTestCase {
    function TestOfMail() {
        $this->UnitTestCase('Mail function test');
    }
    
    function setUp() {
    
    }
    
    function tearDown() {
    
    }
    
    function testSendingMail() {
    
        //$result = mail("you@example.com", "Login Activation", "Thank you for registering an account.");

        //$this->assertTrue($result);
    }
}

?>
