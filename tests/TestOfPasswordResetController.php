<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test PasswordResetController class
 */
class TestOfPasswordResetController extends ThinkUpUnitTestCase {
    protected $owner;
    protected $token;

    public function setUp() {
        parent::setUp();
        $config = Config::getInstance();
        $config->setValue('debug', true);

        $session = new Session();
        $cryptpass = $session->pwdcrypt("oldpassword");
        $q = <<<SQL
INSERT INTO #prefix#owners SET
    id = 1,
    full_name = 'ThinkUp J. User',
    email = 'me@example.com',
    pwd = '$cryptpass',
    activation_code='8888',
    is_activated =1
SQL;
        $this->db->exec($q);

        $dao = DAOFactory::getDAO('OwnerDAO');
        $this->owner = $dao->getByEmail('me@example.com');
        $this->token = $this->owner->setPasswordRecoveryToken();
    }

    public function testOfControllerNoToken() {
        unset($_GET['token']);

        $controller = new PasswordResetController(true);
        $result = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('errormsg'), 'You have reached this page in error.');
    }

    public function testOfControllerExpiredToken() {
        $expired_time = strtotime('-2 days');
        $q = <<<SQL
UPDATE #prefix#owners
SET password_token = '{$this->token}_{$expired_time}'
WHERE id = 1;
SQL;
        $this->db->exec($q);

        $_GET['token'] = $this->token;
        $controller = new PasswordResetController(true);
        $result = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('errormsg'), 'Your token is expired.');
    }

    public function testOfControllerGoodToken() {
        $time = strtotime('-1 hour');
        $q = <<<SQL
UPDATE #prefix#owners
SET password_token = '{$this->token}_{$time}'
WHERE id = 1;
SQL;
        $this->db->exec($q);

        $_GET['token'] = $this->token;
        $controller = new PasswordResetController(true);
        $result = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertFalse($v_mgr->getTemplateDataItem('errormsg'));
        $this->assertFalse($v_mgr->getTemplateDataItem('successmsg'));
    }

    public function testOfControllerGoodTokenMismatchedPassword() {
        $time = strtotime('-1 hour');
        $q = <<<SQL
UPDATE #prefix#owners
SET password_token = '{$this->token}_{$time}'
WHERE id = 1;
SQL;
        $this->db->exec($q);

        $_POST['password'] = 'not';
        $_POST['password_confirm'] = 'the same';
        $_GET['token'] = $this->token;
        $controller = new PasswordResetController(true);
        $result = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('errormsg'), "Passwords didn't match.");
    }

    public function testOfControllerGoodTokenMatchedNewPassword() {
        $time = strtotime('-1 hour');
        $q = <<<SQL
UPDATE #prefix#owners
SET password_token = '{$this->token}_{$time}'
WHERE id = 1;
SQL;
        $this->db->exec($q);

        $_POST['password'] = 'the same';
        $_POST['password_confirm'] = 'the same';
        $_GET['token'] = $this->token;
        $controller = new PasswordResetController(true);
        $result = $controller->go();

        $dao = DAOFactory::getDAO('OwnerDAO');
        $session = new Session();

        $this->assertTrue($session->pwdCheck($_POST['password'], $dao->getPass('me@example.com')));
    }

}
