<?php
/**
 *
 * ThinkUp/tests/TestOfMailer.php
 *
 * Copyright (c) 2011-2012 Gina Trapani
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
 * @copyright 2011-2012 Gina Trapani
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfMailer extends ThinkUpBasicUnitTestCase {

    public function setUp() {
        parent::setUp();
    }

    public function tearDown(){
        parent::tearDown();
        // delete test email file if it exists
        $test_email = FileDataManager::getDataPath(Mailer::EMAIL);
        if (file_exists($test_email)) {
            unlink($test_email);
        }
    }

    public function testFromName() {
        $config = Config::getInstance();
        $config->setValue("app_title", "My Crazy Custom ThinkUp Named Installation");
        $_SERVER['HTTP_HOST'] = "my_thinkup_hostname";
        Mailer::mail('you@example.com', 'Testing 123', 'Me worky, yo?');
        $email_body = Mailer::getLastMail();
        $this->debug($email_body);
        $this->assertPattern('/From: "My Crazy Custom ThinkUp Named Installation" <notifications@my_thinkup_hostname>/',
        $email_body, 'Headers set to custom application name');

        $config->setValue("app_title", "My Other TU App Install");
        $_SERVER['HTTP_HOST'] = "my_other_hostname";
        Mailer::mail('you@example.com', 'Testing 123', 'Me worky, yo?');
        $email_body = Mailer::getLastMail();
        $this->debug($email_body);
        $this->assertPattern('/From: "My Other TU App Install" <notifications@my_other_hostname>/',
        $email_body, 'Headers set to custom application name');
    }
}