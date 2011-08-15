<?php
/**
 *
 * ThinkUp/tests/TestOfForgotPasswordController.php
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

class TestOfForgotPasswordController extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
        $this->builder = self::buildData();
    }

    protected function buildData() {
        $owner_dao = new OwnerMySQLDAO();
        $cryptpass = $owner_dao->pwdcrypt("oldpassword");
        $builder = FixtureBuilder::build('owners', array('id'=>1, 'full_name'=>'ThinkUp J. User',
        'email'=>'me@example.com', 'pwd'=>$cryptpass, 'activation_code'=>8888, 'is_activated'=>1));
        return $builder;
    }

    public function tearDown() {
        $this->builder = null;
        parent::tearDown();
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
        $this->assertEqual($v_mgr->getTemplateDataItem('error_msg'), 'Error: account does not exist.');
    }

    public function testOfControllerWithValidEmailAddress() {
        $config = Config::getInstance();
        $site_root_path = $config->getValue('site_root_path');
        $_POST['email'] = 'me@example.com';
        $_POST['Submit'] = "Send Reset";
        $_SERVER['HTTP_HOST'] = "mytestthinkup";
        $controller = new ForgotPasswordController(true);
        $result = $controller->go();

        $this->assertTrue(strpos($result, 'Password recovery information has been sent to your email address.') > 0);

        $actual_forgot_email = Mailer::getLastMail();
        $this->debug($actual_forgot_email);
        $expected_forgot_email_pattern = '/to: me@example.com
subject: ThinkUp Password Recovery
message: Hi there!

Looks like you forgot your ThinkUp password. Go to this URL to reset it:
http:\/\/mytestthinkup'.str_replace('/', '\/', $site_root_path).'session\/reset.php/';
        $this->assertPattern($expected_forgot_email_pattern, $actual_forgot_email);
    }

    public function testOfControllerWithValidEmailAddressAndSSL() {
        $config = Config::getInstance();
        $site_root_path = $config->getValue('site_root_path');
        $_POST['email'] = 'me@example.com';
        $_POST['Submit'] = "Send Reset";
        $_SERVER['HTTP_HOST'] = "mytestthinkup";
        $_SERVER['HTTPS'] = true;
        $controller = new ForgotPasswordController(true);
        $result = $controller->go();

        $this->assertTrue(strpos($result, 'Password recovery information has been sent to your email address.') > 0);

        $actual_forgot_email = Mailer::getLastMail();
        $this->debug($actual_forgot_email);
        $expected_forgot_email_pattern = '/to: me@example.com
subject: ThinkUp Password Recovery
message: Hi there!

Looks like you forgot your ThinkUp password. Go to this URL to reset it:
https:\/\/mytestthinkup'.str_replace('/', '\/', $site_root_path).'session\/reset.php/';
        $this->assertPattern($expected_forgot_email_pattern, $actual_forgot_email);
    }
}
