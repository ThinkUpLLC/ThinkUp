<?php
/**
 *
 * ThinkUp/tests/TestOfOwnerInstance.php
 *
 * Copyright (c) 2014 Eduard Cucurella
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
 * @copyright 2014 Eduard Cucurella
 * @author Eduard Cucurella <eduard[dot]cucu[cot]cat[at]gmail[dot]com>
 * */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfOwnerInstance extends ThinkUpBasicUnitTestCase {

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {

        $complete_owner_instance_values = array(
            'id'=>1, 
            'owner_id'=>2,
            'instance_id'=>3,
            'oauth_access_token'=>'xxx',
            'oauth_access_token_secret'=>'zzz',
            'auth_error'=>'',
            'is_twitter_referenced_instance'=>0
        );

        $owner_instance = new OwnerInstance($complete_owner_instance_values);
        $this->assertEqual($owner_instance->id, 1);
        $this->assertEqual($owner_instance->owner_id, 2);
        $this->assertEqual($owner_instance->instance_id, 3);
        $this->assertEqual($owner_instance->oauth_access_token, 'xxx');
        $this->assertEqual($owner_instance->oauth_access_token_secret, 'zzz');
        $this->assertEqual($owner_instance->auth_error, '');
        $this->assertFalse($owner_instance->is_twitter_referenced_instance);

        $complete_owner_instance_values = array(
            'id'=>1, 
            'owner_id'=>2,
            'instance_id'=>3,
            'oauth_access_token'=>'xxx',
            'oauth_access_token_secret'=>'zzz',
            'auth_error'=>'',
            'is_twitter_referenced_instance'=>1
        );

        $owner_instance = new OwnerInstance($complete_owner_instance_values);
        $this->assertTrue($owner_instance->is_twitter_referenced_instance);

    }
}
