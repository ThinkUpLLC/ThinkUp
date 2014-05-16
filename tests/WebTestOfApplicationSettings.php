<?php
/**
 *
 * ThinkUp/tests/WebTestOfApplicationSettings.php
 *
 * Copyright (c) 2011-2013 Gina Trapani
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
 * @copyright 2011-2013 Gina Trapani
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';

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
        $this->assertText('Registration is closed for ');

        //Log in as admin
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');
        $this->click("Log In");

        //Open registration
        $this->click("Settings");
        $this->assertTitle("Configure Your Account | " . Config::getInstance()->getValue('app_title_prefix') .
        "ThinkUp");
        $this->assertText('me@example.com');

        // NOTE: this uses an ajax call, so we need to set up the post ourselves
        $response = $this->post($this->url . '/account/appconfig.php',
        array('csrf_token' => self::TEST_CSRF_TOKEN,'is_registration_open' => 'true',
        'save' => 'true'));
        $response_object = json_decode($response);
        $this->assertEqual($response_object->status, 'success');
        $this->assertEqual($response_object->saved, 1);

        //Log out
        $this->get($this->url.'/session/logout.php');

        //Assert registration is open
        $this->get($this->url.'/session/register.php');
        $this->assertText('Name');
        $this->assertNoText('Registration is closed for ');
    }

    public function testCSRFToken() {
        //Log in as admin
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');
        $this->click("Log In");

        //Open registration bad token
        $this->click("Settings");
        $this->assertTitle("Configure Your Account | " . Config::getInstance()->getValue('app_title_prefix') .
        "ThinkUp");
        $this->assertText('me@example.com');

        // we should have a global js token
        $this->assertPattern("/var csrf_token = '" . self::TEST_CSRF_TOKEN . "';/");

        // bad token
        $this->post($this->url . '/account/appconfig.php',
        array('csrf_token' => self::TEST_CSRF_TOKEN . '-bad','is_registration_open' => 'true',
        'save' => 'true'));
        $this->assertText('Invalid CSRF token passed: ' . self::TEST_CSRF_TOKEN . '-bad');

        // good token
        $response = $this->post($this->url . '/account/appconfig.php',
        array('csrf_token' => self::TEST_CSRF_TOKEN,'is_registration_open' => 'true',
        'save' => 'true'));
        $response_object = json_decode($response);
        $this->assertEqual($response_object->status, 'success');
        $this->assertEqual($response_object->saved, 1);
    }

    public function testAuthControlLoggedInChangeNotificationFrequency() {
        //Log in as admin
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');
        $this->click("Log In");

        $this->click("Settings");
        $this->assertText('Insights email');
        $this->click("Weekly");
        $this->click("Save");
    }

    public function testBackupAndExport() {
        //Log in as admin
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');
        $this->click("Log In");
        $this->click("Settings");
        $this->assertTitle("Configure Your Account | " . Config::getInstance()->getValue('app_title_prefix') .
        "ThinkUp");
        $this->assertText('me@example.com');

        //Test export link
        $this->click("Export a ThinkUp account");
        $this->assertText("Export a single user account's data");
        $this->assertPattern("/Choose a user to export\./");

        //Test backup link
        $this->click("Settings");
        $this->click("Back up ThinkUp");
        $this->assertText("Back up ThinkUp's entire database");
        $this->assertText("If you have any issues using this backup feature, you can use mysqldump to manually back ".
        "up your ThinkUp data if you have access to your server.");
    }
}
