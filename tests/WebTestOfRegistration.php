<?php
/**
 *
 * ThinkUp/tests/WebTestOfRegistration.php
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

class WebTestOfRegistration extends ThinkUpWebTestCase {

    public function setUp() {
        parent::setUp();
        $this->builders = self::buildData();
    }

    public function tearDown() {
        $this->builders = null;
        $test_email = FileDataManager::getDataPath( Mailer::EMAIL);
        if (file_exists($test_email)) {
            unlink($test_email);
        }

        parent::tearDown();
    }

    public function testRegistrationClosedByDefault() {
        $this->get($this->url.'/session/register.php');
        $this->assertText('Sorry!');
        $this->assertText('Registration is closed for ');
    }

    public function testSuccessfulRegistration() {
        //Open registration
        $builders[] = FixtureBuilder::build('options', array('namespace'=>'application_options',
        'option_name'=>'is_registration_open', 'option_value'=>'true'));

        $this->get($this->url.'/session/register.php');
        $this->assertNoText('Sorry, registration is closed on this installation of');

        $this->setFieldById('full_name', 'Test User');
        $this->setFieldById('email', 'TestUser@example.com');
        $this->setFieldById('pass1', 'p4sswd123');
        $this->setFieldById('pass2', 'p4sswd123');
        $this->setFieldById('user_code', '123456');
        $this->clickSubmitById('login-save');

        $this->assertNoText('Sorry, registration is closed on this installation of');
        //$this->showSource();
        $this->assertPattern("/Success! Check your email for an activation link./");
    }

    public function testInvalidInputsRegistration() {
        //Open registration
        $builders[] = FixtureBuilder::build('options', array('namespace'=>'application_options',
        'option_name'=>'is_registration_open', 'option_value'=>'true'));

        $this->get($this->url.'/session/register.php');
        $this->assertNoText('Sorry, registration is closed on this ThinkUp installation.');

        $this->setFieldById('full_name', 'Test User');
        $this->setFieldById('email', 'TestUsernotavalidemailexample.com');
        $this->setFieldById('pass1', 'p4asdfwd');
        $this->setFieldById('pass2', 'p4sasdfasdswd');
        $this->setFieldById('user_code', 'badinput');
        $this->clickSubmitById('login-save');

        $this->assertNoText('Success! Check your email for an activation link.');
        $this->assertNoText('Sorry, registration is closed on this ThinkUp installation.');
        //$this->showSource();
        $this->assertPattern("/Hmm, that code did not match the image\. Please try again?/");
        $this->assertPattern("/Passwords do not match\./");
        $this->assertPattern("/Sorry, that email address looks wrong\./");
    }

    public function testInvalidInvitationCode() {
        $this->get($this->url.'/session/register.php?code=invalidcode');
        $this->assertPattern("/Sorry, registration is closed on /");
    }

    public function testValidInvitationCode() {
        //Open registration
        $builders[] = FixtureBuilder::build('options', array('namespace'=>'application_options',
        'option_name'=>'is_registration_open', 'option_value'=>'true'));
        $invite_dao = new InviteMySQLDAO();
        $invite_dao->addInviteCode('aabbddcc');
        $this->get($this->url.'/session/register.php?code=aabbddcc');
        $this->assertNoPattern("/Sorry, registration is closed on /");

        $this->setFieldById('full_name', 'Test User');
        $this->setFieldById('email', 'TestUser@example.com');
        $this->setFieldById('pass1', 'p4sswd123');
        $this->setFieldById('pass2', 'p4sswd123');
        $this->setFieldById('user_code', '123456');
        $this->clickSubmitById('login-save');

        $this->assertNoPattern("/Sorry, registration is closed on /");
               // $this->showSource();
        $this->assertPattern("/Success! Check your email for an activation link\./");
    }
}