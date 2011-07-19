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
    protected $owner_salt;
    protected $token_salt;

    public function setUp() {
        parent::setUp();
        $this->builder = self::buildData();
        $config = Config::getInstance();
        $config->setValue('debug', true);
    }

    protected function buildData() {
        $builders = array();

        $saltedpass = TestOfOwnerMySQLDAO::hashPasswordUsingCurrentMethod('oldpassword', 'testsalt');

        $cryptpass = TestOfOwnerMySQLDAO::hashPasswordUsingDeprecatedMethod("oldpassword");
        $builders[] = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User',
        'email'=>'me@example.com', 'pwd'=>$cryptpass, 'pwd_salt'=>TestOfOwnerMySQLDAO::$default_salt,
        'activation_code'=>'8888', 'is_activated'=>1));
        $builders[] = FixtureBuilder::build('owners', array('id'=>2, 'full_name'=>'Salted User',
        'email'=>'salt@example.com', 'pwd'=>$saltedpass, 'pwd_salt'=>TestOfOwnerMySQLDAO::$default_salt,
        'activation_code'=>'8888', 'is_activated'=>1));
        $dao = DAOFactory::getDAO('OwnerDAO');
        $this->owner = $dao->getByEmail('me@example.com');
        $this->token = $this->owner->setPasswordRecoveryToken();

        $this->owner_salt = $dao->getByEmail('salt@example.com');
        $this->token_salt = $this->owner_salt->setPasswordRecoveryToken();
        return $builders;
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
        $this->assertEqual($v_mgr->getTemplateDataItem('error_msg'), 'You have reached this page in error.');
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
        $this->assertEqual($v_mgr->getTemplateDataItem('error_msg'), 'Your token is expired.');
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
        $this->assertFalse($v_mgr->getTemplateDataItem('error_msg'));
        $this->assertFalse($v_mgr->getTemplateDataItem('success_msg'));
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
        $this->assertEqual($v_mgr->getTemplateDataItem('error_msg'), "Passwords didn't match.");
    }

    public function testOfControllerGoodTokenMatchedNewPasswordWithNoUniqueSalt() {
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
         
        $owner = $dao->getByEmail('me@example.com');
        $this->assertEqual($owner->account_status, '');

        // Check a new unique salt got generated
        $sql = "select pwd_salt from " . $this->table_prefix . "owners where email = 'me@example.com'";
        $stmt = OwnerMySQLDAO::$PDO->query($sql);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertNotEqual($data['pwd_salt'], TestOfOwnerMySQLDAO::$default_salt);
    }

    public function testOfControllerGoodTokenMatchedNewPasswordWithUniqueSalt() {
        $dao = DAOFactory::getDAO('OwnerDAO');
        $dao->setAccountStatus("salt@example.com", "Deactivated account");

        $time = strtotime('-1 hour');
        $q = <<<SQL
UPDATE #prefix#owners
SET password_token = '{$this->token_salt}_{$time}'
WHERE id = 2;
SQL;
        $this->testdb_helper->runSQL($q);

        $_POST['password'] = 'the same';
        $_POST['password_confirm'] = 'the same';
        $_GET['token'] = $this->token_salt;
        $controller = new PasswordResetController(true);
        $result = $controller->go();

        //assert account status is not deactivated
        $owner = $dao->getByEmail('salt@example.com');
        $this->assertEqual($owner->account_status, '');
    }
}
