<?php
/**
 *
 * ThinkUp/tests/TestOfPasswordResetController.php
 *
 * Copyright (c) 2009-2011 Gina Trapani, Michael Louis Thaler
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Michael Louis Thaler <michael[dot]louis[dot]thaler[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Gina Trapani, Michael Louis Thaler
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfPasswordResetController extends ThinkUpUnitTestCase {
    protected $owner;
    protected $token;

    public function setUp() {
        parent::setUp();
        $this->builder = self::buildData();
        $config = Config::getInstance();
        $config->setValue('debug', true);
    }

    protected function buildData() {
        $session = new Session();
        $cryptpass = $session->pwdcrypt("oldpassword");
        $builder = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User',
        'email'=>'me@example.com', 'pwd'=>$cryptpass, 'activation_code'=>'8888', 'is_activated'=>1));
        $dao = DAOFactory::getDAO('OwnerDAO');
        $this->owner = $dao->getByEmail('me@example.com');
        $this->token = $this->owner->setPasswordRecoveryToken();

        return $builder;
    }

    public function tearDown() {
        $this->builder = null;
        parent::tearDown();
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
        $this->testdb_helper->runSQL($q);

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
        $this->testdb_helper->runSQL($q);

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
        $this->testdb_helper->runSQL($q);

        $_POST['password'] = 'not';
        $_POST['password_confirm'] = 'the same';
        $_GET['token'] = $this->token;
        $controller = new PasswordResetController(true);
        $result = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('errormsg'), "Passwords didn't match.");
    }

    public function testOfControllerGoodTokenMatchedNewPassword() {
        $dao = DAOFactory::getDAO('OwnerDAO');
        $dao->setAccountStatus("me@example.com", "Deactivated account");

        $time = strtotime('-1 hour');
        $q = <<<SQL
UPDATE #prefix#owners
SET password_token = '{$this->token}_{$time}'
WHERE id = 1;
SQL;
        $this->testdb_helper->runSQL($q);

        $_POST['password'] = 'the same';
        $_POST['password_confirm'] = 'the same';
        $_GET['token'] = $this->token;
        $controller = new PasswordResetController(true);
        $result = $controller->go();

        $session = new Session();

        $this->assertTrue($session->pwdCheck($_POST['password'], $dao->getPass('me@example.com')));
        $owner = $dao->getByEmail('me@example.com');
        $this->assertEqual($owner->account_status, '');
    }
}
