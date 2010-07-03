<?php
require_once dirname(__FILE__).'/config.tests.inc.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once $SOURCE_ROOT_PATH.'extlib/simpletest/web_tester.php';
ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.$INCLUDE_PATH);

require_once $SOURCE_ROOT_PATH.'tests/classes/class.ThinkTankWebTestCase.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.User.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.FollowMySQLDAO.php';
require_once $SOURCE_ROOT_PATH.'webapp/model/class.Session.php';


class TestOfSignIn extends ThinkTankWebTestCase {

    public function setUp() {
        parent::setUp();

        //Add owner
        $session = new Session();
        $cryptpass = $session->pwdcrypt("secretpassword");
        $q = "INSERT INTO tt_owners (id, user_email, user_pwd, user_activated) VALUES (1, 'me@example.com', '".
        $cryptpass."', 1)";
        $this->db->exec($q);

        //Add instance
        $q = "INSERT INTO tt_instances (id, network_user_id, network_username, is_public) VALUES (1, 1234,
        'thinktankapp', 1)";
        $this->db->exec($q);

        //Add instance_owner
        $q = "INSERT INTO tt_owner_instances (owner_id, instance_id) VALUES (1, 1)";
        $this->db->exec($q);
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testSignInSuccessAndPrivateDashboard() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');
        $this->click("Log In");

        $this->assertTitle('Private Dashboard | ThinkTank');
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

        $this->assertTitle('Private Dashboard | ThinkTank');
        $this->assertText('Logged in as: me@example.com');
    }
}