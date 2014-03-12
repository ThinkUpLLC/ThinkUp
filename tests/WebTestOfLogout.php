<?php
/**
 *
 * ThinkUp/tests/WebTestOfLogout.php
 *
 * Copyright (c) 2014 Chris Moyer
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
 * @author Chris Moyer
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';

class WebTestOfLogout extends ThinkUpWebTestCase {

    public function setUp() {
        parent::setUp();
        $this->builders = self::buildData();
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    public function testLogout() {
        $email = 'me@example.com';
        $cookie_dao = DAOFactory::getDao('CookieDAO');
        $deleted = $cookie_dao->deleteByEmail($email);
        $this->assertFalse($deleted);

        $this->get($this->url.'/session/login.php');
        $this->setField('email', $email);
        $this->setField('pwd', 'secretpassword');
        $this->click("Log In");
        $this->get($this->url.'/index.php');

        $this->assertTitle(Config::getInstance()->getValue('app_title_prefix') . "ThinkUp");
        $this->assertText($email);

        $cookie = $this->getBrowser()->getCurrentCookieValue(Session::COOKIE_NAME);

        $this->get($this->url.'/session/logout.php');
        $cookie = $this->getBrowser()->getCurrentCookieValue(Session::COOKIE_NAME);
        $this->assertEqual('deleted',$cookie);

        $this->get($this->url.'/index.php');
        $this->assertNoText($email);

        $deleted = $cookie_dao->deleteByEmail($email);
        $this->assertFalse($deleted);
    }
}
