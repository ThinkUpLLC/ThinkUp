<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';

class WebTestOfCrawlerRun extends ThinkUpWebTestCase {

    public function setUp() {
        parent::setUp();
        self::buildData();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testLoggedIn() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');

        $this->click("Log In");

        $this->assertTitle("thinkupapp's Dashboard | ThinkUp");
        $this->assertText('Logged in as: me@example.com');
        $this->assertText('thinkupapp');

        $this->assertText('Update now');

        //For the sake of time, set all instances to inactive so the crawler itself doesn't actually run
        $q = "UPDATE tu_instances SET is_active=0;";
        $this->db->exec($q);

        $this->click("Update now");
        //$this->showHeaders();
        $this->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function testNotLoggedIn() {
        $this->get($this->url.'/crawler/run.php');
        $this->assertText('You must log in to do this.');
    }

}
