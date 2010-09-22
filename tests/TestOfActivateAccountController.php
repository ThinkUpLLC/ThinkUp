<?php
/**
 *
 * ThinkUp/tests/TestOfActivateAccountController.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Dwi Widiastuti
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
*/
require dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test of ActivateAccountController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Dwi Widiastuti
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfActivateAccountController extends ThinkUpUnitTestCase {

    public function __construct() {
        $this->UnitTestCase('ActivateAccountController class test');
    }

    public function testNoParams() {
        $controller = new ActivateAccountController(true);
        $results = $controller->go();
        $this->assertTrue(strpos( $results, "Invalid account activation credentials.") > 0 );
    }

    public function testInvalidActivation() {
        $owner = array('id'=>1, 'email'=>'me@example.com', 'activation_code'=>'1001', 'is_activated'=>0);
        $builder1 = FixtureBuilder::build('owners', $owner);
        $_GET['usr'] = 'me@example.com';
        $_GET['code'] = 'invalidcode';

        $controller = new ActivateAccountController(true);
        $results = $controller->go();
        $this->assertTrue(strpos( $results, "Houston, we have a problem: Account activation failed.") > 0 );

        $_GET['usr'] = 'idontexist@example.com';
        $_GET['code'] = 'invalidcode';

        $controller = new ActivateAccountController(true);
        $results = $controller->go();
        $this->assertTrue(strpos( $results, "Houston, we have a problem: Account activation failed.") > 0 );

        $_GET['usr'] = 'me@example.com';
        $_GET['code'] = '10011';

        $controller = new ActivateAccountController(true);
        $results = $controller->go();
        $this->assertTrue(strpos( $results, "Houston, we have a problem: Account activation failed.") > 0 );
    }

    public function testValidActivation() {
        $owner = array('id'=>1, 'email'=>'me@example.com', 'activation_code'=>'1001', 'is_activated'=>0);
        $builder1 = FixtureBuilder::build('owners', $owner);
        $_GET['usr'] = 'me@example.com';
        $_GET['code'] = '1001';

        $controller = new ActivateAccountController(true);
        $results = $controller->go();
        $this->assertTrue(strpos( $results, "Success! Your account has been activated. Please log in.") > 0, $results );
    }
}