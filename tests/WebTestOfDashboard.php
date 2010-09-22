<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';

class WebTestOfDashboard extends ThinkUpWebTestCase {

    public function setUp() {
        parent::setUp();

        self::buildData();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testDashboardWithPosts() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');

        $this->click("Log In");
        //        $this->showSource();

        $this->assertTitle("thinkupapp's Dashboard | ThinkUp");
        $this->assertText('Logged in as: me@example.com');
        $this->assertText('thinkupapp');
    }

    public function testUserPage() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');

        $this->click("Log In");
        $this->assertTitle("thinkupapp's Dashboard | ThinkUp");

        $this->get($this->url.'/user/index.php?i=thinkupapp&u=ev&n=twitter');
        $this->assertTitle('User Details: ev | ThinkUp');
        $this->assertText('Logged in as: me@example.com');
        $this->assertText('ev');

        $this->get($this->url.'/user/index.php?i=thinkupapp&u=usernotinsystem');
        $this->assertText('User and network not specified.');
    }

    public function testConfiguration() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');

        $this->click("Log In");
        $this->assertTitle("thinkupapp's Dashboard | ThinkUp");

        $this->click("Configuration");
        $this->assertTitle('Configure Your Account | ThinkUp');
        $this->assertText('configure');
        $this->assertText('Expand URLs');

        $this->click("Twitter");
        $this->assertText('Configure the Twitter Plugin');
    }

    public function testExport() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');

        $this->click("Log In");
        $this->assertTitle("thinkupapp's Dashboard | ThinkUp");
        //        $this->showSource();
        $this->assertText('CSV');

        $this->click("CSV");
        $this->assertText('This is test post');
    }
}
