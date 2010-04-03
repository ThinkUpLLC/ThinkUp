<?php 
require_once (dirname(__FILE__).'/simpletest/autorun.php');


require_once (dirname(__FILE__).'/config.tests.inc.php');

require_once ("common/class.Mailer.php");

class TestOfMailer extends UnitTestCase {
    function TestOfMailer() {
        $this->UnitTestCase('Mailer class test');
    }
    
    function setUp() {
    
    }
    
    function tearDown() {
    
    }
    
    function testSendingMail() {
    
        $result = Mailer::mail("you@example.com", "Login Activation", "Thank you for registering an account.");

        $this->assertTrue($result);
    }
}

?>
