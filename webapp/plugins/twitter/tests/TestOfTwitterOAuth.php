<?php
if ( !isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS ) {
    require_once '../../../../tests/init.tests.php';
}
require_once THINKUP_ROOT_PATH.'extlib/simpletest/autorun.php';
if (!class_exists('twitterOAuth')) {
    require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/tests/classes/mock.TwitterOAuth.php';
}

class TestOfTwitterOAuth extends UnitTestCase {
    function TestOfTwitterOAuth() {
        $this->UnitTestCase('Mock Twitter OAuth test');
    }

    function testMakingAPICall() {
        $to = new TwitterOAuth('', '', '', '');
        $result = $to->oAuthRequest('https://twitter.com/users/show/anildash.xml', 'GET', array());
        $this->assertWantedPattern('/Anil Dash/', $result);

    }
}
?>
