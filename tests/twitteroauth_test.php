<?php 
require_once (dirname(__FILE__).'/simpletest/autorun.php');


require_once (dirname(__FILE__).'/config.tests.inc.php');
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$TEST_CLASS_PATH);
require_once ("mock.TwitterOAuth.php");


class TestOfTwitterOAuth extends UnitTestCase {
    function TestOfTwitterOAuth() {
        $this->UnitTestCase('Mock Twitter OAuth test');
    }
    
    function testMakingAPICall() {
        $to = new TwitterOAuth('', '', '', '');
        $result = $to->oAuthRequest('https://twitter.com/users/show/anildash.xml', array(), 'GET');
        $this->assertWantedPattern('/Anil Dash/', $result);
        
    }
}
?>
