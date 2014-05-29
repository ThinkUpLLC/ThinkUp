<?php
/**
 *
 * ThinkUp/tests/WebTestOfLogin.php
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
require_once THINKUP_WEBAPP_PATH.'config.inc.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';

class WebTestOfLogin extends ThinkUpWebTestCase {

    public function setUp() {
        parent::setUp();
        $this->builders = self::buildData();
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    public function testLoginByCookie() {
        $email = 'me@example.com';
        $cookie_dao = DAOFactory::getDao('CookieDAO');
        $cookie = $cookie_dao->generateForEmail($email);

        $this->get($this->url.'/index.php');
        $this->assertNoText($email);
        $this->getBrowser()->setCookie(Session::COOKIE_NAME, $cookie);

        $this->get($this->url.'/index.php');
        $this->assertText($email);
    }

    public function testLoginFailureAttemptThenSuccess() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me51@example.com');
        $this->setField('pwd', 'wrongemail');
        $this->click("Log In");

        $this->assertPattern("/Hmm\, that email seems wrong\?/");

        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'wrongpassword');
        $this->click("Log In");

        $this->assertPattern("/Hmm\, that password seems wrong\?/");
        $this->assertField('email', 'me@example.com');

        $this->setField('pwd', 'secretpassword');
        $this->click("Log In");

        $this->get($this->url.'/index.php');
        $this->assertTitle(Config::getInstance()->getValue('app_title_prefix') .
        "ThinkUp");
        $this->assertText('me@example.com');
    }

    public function testLoginLockout() {
        $i = 1;
        while ($i <= 12) {
            $this->get($this->url.'/session/login.php');
            $this->setField('email', 'me@example.com');
            $this->setField('pwd', 'wrongpassword');
            $this->click("Log In");
            //$this->showSource();

            if ($i < 10) {
                $this->assertPattern("/Hmm\, that password seems wrong\?/");
                $this->assertField('email', 'me@example.com');
            } else {
                $this->assertPattern("/Inactive account. Account deactivated due to too many failed logins./");
            }
            $i = $i + 1;
        }
    }

    public function testAutofocusOnUserField() {
        $this->get($this->url.'/session/login.php');
        $this->assertPattern('/autofocus="autofocus"/');
    }
}
