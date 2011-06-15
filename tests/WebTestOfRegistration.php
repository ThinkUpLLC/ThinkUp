<?php
/**
 *
 * ThinkUp/tests/WebTestOfRegistration.php
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

class WebTestOfRegistration extends ThinkUpWebTestCase {

    public function setUp() {
        parent::setUp();
        $this->builders = self::buildData();
    }

    public function tearDown() {
        $this->builders = null;
        $test_email = THINKUP_WEBAPP_PATH . '_lib/view/compiled_view' . Mailer::EMAIL;
        if (file_exists($test_email)) {
            unlink($test_email);
        }

        parent::tearDown();
    }

    public function testRegistrationClosedByDefault() {
        $this->get($this->url.'/session/register.php');
        $this->assertText('Sorry, registration is closed on this ThinkUp installation.');
    }

    public function testInvalidInvitationCode() {
        $this->get($this->url.'/session/register.php?code=invalidcode');
        $this->assertText('Sorry, registration is closed on this ThinkUp installation.');
    }

    public function testValidInvitationCode() {
        $invite_dao = new InviteMySQLDAO();
        $invite_dao->addInviteCode('aabbddcc');
        $this->get($this->url.'/session/register.php?code=aabbddcc');
        $this->assertNoText('Sorry, registration is closed on this ThinkUp installation.');

        $this->setFieldById('full_name', 'Test User');
        $this->setFieldById('email', 'TestUser@example.com');
        $this->setFieldById('pass1', 'p4sswd');
        $this->setFieldById('pass2', 'p4sswd');
        $this->setFieldById('captcha', '123456');
        $this->clickSubmitById('login-save');

        $this->assertNoText('Sorry, registration is closed on this ThinkUp installation.');
        //        $this->showSource();
        $this->assertText('Success! Check your email for an activation link.');
    }
}