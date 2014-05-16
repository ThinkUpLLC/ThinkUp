<?php
/**
 *
 * ThinkUp/tests/WebTestOfChangePassword.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkup.com).
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';

class WebTestOfChangePassword extends ThinkUpWebTestCase {

    public function setUp() {
        parent::setUp();
        $this->builders = self::buildData();
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    public function testChangePasswordSuccess() {
        $cfg = Config::getInstance();
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');

        $this->click("Log In");
        $this->assertTitle($cfg->getValue('app_title_prefix') . "ThinkUp");
        $this->assertText('me@example.com');

        $this->click("Settings");
        $this->assertText('Account');
        $this->setField('oldpass', 'secretpassword');
        $this->setField('pass1', 'secretpassword1');
        $this->setField('pass2', 'secretpassword1');
        $this->click('Change');
        $this->assertPattern('/Your password has been updated\./');

        $this->click("Log Out");
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword1');

        $this->click("Log In");
        $this->assertTitle($cfg->getValue('app_title_prefix') . "ThinkUp");
        $this->assertText('me@example.com');
    }

    public function testChangePasswordWrongExistingPassword() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');

        $this->click("Log In");
        $this->assertTitle(Config::getInstance()->getValue('app_title_prefix'). "ThinkUp");
        $this->assertText('me@example.com');

        $this->click("Settings");
        $this->assertText('Account');
        $this->setField('oldpass', 'secretpassworddd');
        $this->setField('pass1', 'secretpassword1');
        $this->setField('pass2', 'secretpassword1');
        $this->click('Change');
        $this->assertText('Password is incorrect.');
    }

    public function testChangePasswordEmptyExistingPassword() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');

        $this->click("Log In");
        $this->assertTitle(Config::getInstance()->getValue('app_title_prefix'). "ThinkUp");
        $this->assertText('me@example.com');

        $this->click("Settings");
        $this->assertText('Account');
        $this->setField('pass1', 'secretpassword1');
        $this->setField('pass2', 'secretpassword1');
        $this->click('Change');
        $this->assertText('Password is incorrect.');
    }

    public function testChangePasswordNewPasswordsDontMatch() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');

        $this->click("Log In");
        $this->assertTitle(Config::getInstance()->getValue('app_title_prefix'). "ThinkUp");
        $this->assertText('me@example.com');

        $this->click("Settings");
        $this->assertText('Account');
        $this->setField('oldpass', 'secretpassword');
        $this->setField('pass1', 'secretpassword1');
        $this->setField('pass2', 'secretpassword2');
        $this->click('Change');
        $this->assertText('New passwords did not match. Your password has not been changed.');
    }

    public function testChangePasswordNewPasswordsNotLongEnough() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');

        $this->click("Log In");
        $this->assertTitle(Config::getInstance()->getValue('app_title_prefix'). "ThinkUp");
        $this->assertText('me@example.com');

        $this->click("Settings");
        $this->assertText('Account');
        $this->setField('oldpass', 'secretpassword');
        $this->setField('pass1', 'dd');
        $this->setField('pass2', 'dd');
        $this->click('Change');
        $this->assertText('Your new password must be at least 8 characters and contain both numbers and letters. '.
        'Your password has not been changed.');
    }
}