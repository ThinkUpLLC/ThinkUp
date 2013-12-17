<?php
/**
 *
 * ThinkUp/tests/WebTestOfCSRFToken.php
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
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';

class WebTestOfCSRFToken extends ThinkUpWebTestCase {
    public function setUp() {
        parent::setUp();
        $this->builders = self::buildData();
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    public function testNoJSToken() {

        //Log in as admin
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');
        $this->click("Log In");

        // main views should not have a any token defined
        $this->assertNoPattern("/var csrf_token = '" . self::TEST_CSRF_TOKEN . ";/");
        $this->assertNoPattern('/<input type="hidden" name="csrf_token" value="'. self::TEST_CSRF_TOKEN .'" \/>/');
    }

    public function testJSToken() {
        //Log in as admin
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');
        $this->click("Log In");

        //Open setting page
        $this->click("Settings");
        $this->assertTitle("Configure Your Account | " .  Config::getInstance()->getValue('app_title_prefix') .
        "ThinkUp");
        $this->assertText('me@example.com');

        // look for global js token
        $this->assertPattern("/var csrf_token = '" . self::TEST_CSRF_TOKEN . "';/");

        // look for password form token
        $this->assertPattern('/<input name="oldpass" type="password" id="oldpass" class="password form-control" required>'.
        '<input type="hidden" name="csrf_token" value="'. self::TEST_CSRF_TOKEN.'" \/>/');

        // look for invite token
        $this->assertPattern('/<input type="hidden" name="csrf_token" value="'. self::TEST_CSRF_TOKEN .'" \/>' .
        '<input type="submit" id="login-save" name="invite"/');

        // look for js ajax data tokens
        $this->assertPattern('/&p=1&csrf_token=" \+ window.csrf_token; \/\/ toggle public on/');
        $this->assertPattern('/&p=0&csrf_token=" \+ window.csrf_token; \/\/ toggle public off/');
        $this->assertPattern('/&p=1&csrf_token=" \+ window.csrf_token; \/\/ toggle active on/');
        $this->assertPattern('/&p=0&csrf_token=" \+ window.csrf_token; \/\/ toggle active off/');
        $this->assertPattern('/&a=1&csrf_token=" \+ window.csrf_token; \/\/ toggle owner active on/');
        $this->assertPattern('/&a=0&csrf_token=" \+ window.csrf_token; \/\/ toggle owner active off/');
    }
}