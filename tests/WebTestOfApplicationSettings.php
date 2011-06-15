<?php
/**
 *
 * ThinkUp/tests/WebTestOfApplicationSettings.php
 *
 * Copyright (c) 2011 Gina Trapani
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011 Gina Trapani
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';

class WebTestOfApplicationSettings extends ThinkUpWebTestCase {
    public function setUp() {
        parent::setUp();
        $this->builders = self::buildData();
    }

    public function tearDown() {
        $this->builders = null;
        parent::tearDown();
    }

    public function testOpenRegistration() {
        //Assert registration is closed by default
        $this->get($this->url.'/session/register.php');
        $this->assertText('Sorry, registration is closed on this ThinkUp installation.');

        //Log in as admin
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');
        $this->click("Log In");
        //        $this->showSource();

        //Open registration
        $this->click("Settings");
        $this->assertTitle("Configure Your Account | ThinkUp");
        $this->assertText('Logged in as admin: me@example.com');

        $this->clickLinkById('app-settings-tab');
        $this->setFieldById('is_registration_open', 'true');
        $this->submitFormById('app-settings-form');

        //Log out
        $this->click("Log out");

        //Assert registration is now open
        $this->get($this->url.'/session/register.php');
        $this->assertNoText('Sorry, registration is closed on this ThinkUp installation.');
    }
}
