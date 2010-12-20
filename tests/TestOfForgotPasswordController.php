<?php
/**
 *
 * ThinkUp/tests/TestOfForgotPasswordController.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Michael Louis Thaler
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
 * @copyright 2009-2010 Gina Trapani, Michael Louis Thaler
 */

require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfForgotPasswordController extends ThinkUpUnitTestCase {

    public function __construct() {
        $this->UnitTestCase('ForgotPasswordController class test');
    }

    public function setUp() {
        parent::setUp();

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
    }

    public function testOfControllerNoParams() {
        $controller = new ForgotPasswordController(true);
        $result = $controller->go();

        $this->assertPattern('/Reset Your Password/', $result);
    }

    public function testOfControllerWithBadEmailAddress() {
        $_POST['email'] = 'im a broken email address';
        $_POST['Submit'] = "Send Reset";

        $controller = new ForgotPasswordController(true);
        $result = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('errormsg'), 'Error: account does not exist.');
    }

    public function testOfControllerWithValidEmailAddress() {
        $_POST['email'] = 'me@example.com';
        $_POST['Submit'] = "Send Reset";

        $controller = new ForgotPasswordController(true);
        $result = $controller->go();

        $this->assertTrue(strpos($result, 'Password recovery information has been sent to your email address.') > 0);
    }
}
