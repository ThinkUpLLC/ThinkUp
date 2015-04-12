<?php
/**
 *
 * ThinkUp/tests/WebTestOfForgottonPassword.php
 *
 * Copyright (c) 2015 James Bell
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
 * @author James Bell <james.bell[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2015 James Bell
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';

class WebTestOfForgottonPassword extends ThinkUpWebTestCase {

    public function setUp() {
        parent::setUp();
        $this->builders = self::buildData();
    }

    public function tearDown() {
        $this->builders = null;
        $test_email = FileDataManager::getDataPath(Mailer::EMAIL);

        if (file_exists($test_email)) {
            unlink($test_email);
        }

        parent::tearDown();
    }

    public function testForgotPasswordSuccess() {
        $config = Config::getInstance();
        $this->get($this->url.'/session/login.php');

        $this->click('Forgot your password?');
        $this->assertTitle($config->getValue('app_title_prefix') . 'ThinkUp');
        $this->assertText('Reset your password');

        $this->setField('email', 'me@example.com');
        $this->click('Send');
        $this->assertPattern('/Password recovery information has been sent to your email address./');
        $email_file = Mailer::getLastMail();
        preg_match('/reset it:\n(http.*)/m', $email_file, $urls);
        $this->get($urls[1]);

        $this->assertNoPattern('/You have reached this page in error/');
        $this->assertText('Reset your password');
        $this->setField('password', 'abc123');
        $this->setField('password_confirm', 'abc123');
        $this->click('Send');

        //Doesn't work b/c it's a JS message?
        //$this->assertPattern('/You have changed your password/');

        //No longer at the reset page
        $this->assertPattern('/Log in/');
    }

    public function testForgotPasswordNoAccount() {
        $config = Config::getInstance();
        $this->get($this->url.'/session/login.php');

        $this->click('Forgot your password?');
        $this->assertTitle($config->getValue('app_title_prefix') . 'ThinkUp');
        $this->assertText('Reset your password');

        $this->setField('email', 'arthurdent@example.com');
        $this->click('Send');
        $this->assertNoPattern('/Password recovery information has been sent to your email address./');
        $this->assertPattern('/Error: account does not exist./');
    }
}
