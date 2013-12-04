<?php
/**
 *
 * ThinkUp/tests/TestOfOwner.php
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfOwner extends ThinkUpBasicUnitTestCase {

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $complete_owner_values = array('id'=>10, "full_name"=>"ThinkUp J. User", "email"=>'tu_user@example.com',
        'last_login'=>'1/1/2010', 'is_admin'=>0, 'is_activated'=>1, 'account_status'=>'', 'failed_logins'=>19,
        'email_notification_frequency' => 'both'
        );

        $owner = new Owner($complete_owner_values);
        $this->assertEqual($owner->id, 10);
        $this->assertEqual($owner->full_name, "ThinkUp J. User");
        $this->assertEqual($owner->email, 'tu_user@example.com');
        $this->assertEqual($owner->last_login, '1/1/2010');
        $this->assertEqual($owner->email_notification_frequency, 'both');
        $this->assertFalse($owner->is_admin);
        $this->assertTrue($owner->is_activated);
    }
}
