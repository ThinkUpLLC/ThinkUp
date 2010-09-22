<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';

class WebTestOfSignIn extends ThinkUpWebTestCase {

    public function setUp() {
        parent::setUp();
        self::buildData();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testSignInSuccessAndPrivateDashboard() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');
        $this->click("Log In");

        $this->assertTitle("thinkupapp's Dashboard | ThinkUp");
        $this->assertText('Logged in as: me@example.com');
    }

    public function testSignInFailureAttemptThenSuccess() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me2@example.com');
        $this->setField('pwd', 'wrongemail');
        $this->click("Log In");

        $this->assertText('Incorrect email');

        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'wrongpassword');
        $this->click("Log In");

        $this->assertText('Incorrect password');
        $this->assertField('email', 'me@example.com');

        $this->setField('pwd', 'secretpassword');
        $this->click("Log In");

        $this->assertTitle("thinkupapp's Dashboard | ThinkUp");
        $this->assertText('Logged in as: me@example.com');
    }
}