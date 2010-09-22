<?php
/**
 *
 * ThinkUp/tests/TestOfRegisterController.php
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
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterOAuthThinkUp.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/twitter/model/class.TwitterPlugin.php';

/**
 * Test of RegisterController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Dwi Widiastuti
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfRegisterController extends ThinkUpUnitTestCase {
    public function __construct() {
        $this->UnitTestCase('RegisterController class test');
    }

    public function setUp() {
        parent::setUp();
        $webapp = Webapp::getInstance();
        $webapp->registerPlugin('twitter', 'TwitterPlugin');
    }

    public function testConstructor() {
        $controller = new RegisterController(true);
        $this->assertTrue(isset($controller));

        $results = $controller->go();
        $this->assertTrue(strpos( $results, "Register | ThinkUp") > 0);
    }

    public function testAlreadyLoggedIn() {
        $session = new Session();
        $cryptpass = $session->pwdcrypt("secretpassword");
        $owner = array('id'=>1, 'email'=>'me@example.com', 'pwd'=>$cryptpass, 'is_activated'=>1);
        $builder1 = FixtureBuilder::build('owners', $owner);
        $instance = array('id'=>1, 'network_username'=>'thinkupapp');
        $builder2 = FixtureBuilder::build('instances', $instance);
        $owner_instance = array('owner_id'=>1, 'instance_id'=>1);
        $builder3 = FixtureBuilder::build('owner_instances', $owner_instance);
        $this->simulateLogin('me@example.com');

        $controller = new RegisterController(true);
        $results = $controller->go();
        $this->assertPattern("/thinkupapp's Dashboard | ThinkUp/", $results);
    }

    public function testAllMissingFields() {
        $_POST['Submit'] = 'Register';
        $controller = new RegisterController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Register');
        $this->assertEqual($v_mgr->getTemplateDataItem('errormsg'), 'Please fill out all required fields.');
    }

    public function testSomeMissingFields() {
        $_POST['Submit'] = 'Register';
        $_POST['full_name'] = "Angelina Jolie";
        $_POST['email'] = 'angie@example.com';
        $controller = new RegisterController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Register');
        $this->assertEqual($v_mgr->getTemplateDataItem('errormsg'), 'Please fill out all required fields.');
        $this->assertEqual($v_mgr->getTemplateDataItem('name'), 'Angelina Jolie');
        $this->assertEqual($v_mgr->getTemplateDataItem('mail'), 'angie@example.com');
    }

    public function testMismatchedPasswords() {
        $_POST['Submit'] = 'Register';
        $_POST['full_name'] = "Angelina Jolie";
        $_POST['email'] = 'angie@example.com';
        $_POST['user_code'] = '123456';
        $_POST['pass1'] = 'mypass';
        $_POST['pass2'] = 'mmypass';
        $controller = new RegisterController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Register');
        $this->assertEqual($v_mgr->getTemplateDataItem('errormsg'), 'Passwords do not match.');
        $this->assertEqual($v_mgr->getTemplateDataItem('name'), 'Angelina Jolie');
        $this->assertEqual($v_mgr->getTemplateDataItem('mail'), 'angie@example.com');
    }

    public function testSuccessfulRegistration() {
        $_SERVER['HTTP_HOST'] = "http://mytestthinkup/";
        $_POST['Submit'] = 'Register';
        $_POST['full_name'] = "Angelina Jolie";
        $_POST['email'] = 'angie@example.com';
        $_POST['user_code'] = '123456';
        $_POST['pass1'] = 'mypass';
        $_POST['pass2'] = 'mypass';
        $controller = new RegisterController(true);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('controller_title'), 'Register');
        $this->assertEqual($v_mgr->getTemplateDataItem('successmsg'),
        'Success! Check your email for an activation link.');
    }
}

/**
 * Mock Captcha for test use
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class Captcha {
    public function generate() {
        return '';
    }

    public function check() {
        return true;
    }
}

/**
 * Mock Mailer for test use
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class Mailer {
    public static function mail($to, $subject, $message) {
        return $message;
    }
}