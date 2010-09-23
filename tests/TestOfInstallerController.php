<?php
/**
 *
 * ThinkUp/tests/TestOfInstallerController.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Guillaume Boudreau
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
 * @author Guillaume Boudreau <gboudreau[at]pommepause[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Guillaume Boudreau
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfInstallerController extends ThinkUpUnitTestCase {

    public function __construct() {
        $this->UnitTestCase('InstallerController class test');
    }

    public function setUp() {
        parent::setUp();
        if ( !defined('THINKUP_ROOT_PATH') ) {
            define('THINKUP_ROOT_PATH', dirname(dirname(__FILE__)) .'/');
        }
        if ( !defined('THINKUP_WEBAPP_PATH') ) {
            define('THINKUP_WEBAPP_PATH', THINKUP_ROOT_PATH . 'webapp/');
        }
        if ( !defined('THINKUP_BASE_URL') ) {
            define('THINKUP_BASE_URL', '/test/script/path/');
        }
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testAlreadyInstalledNoAdmin() {
        //force a refresh of getTables
        Installer::$show_tables = null;

        //db built, config file and admin exist, so ThinkUp is Already installed
        $controller = new InstallerController(true);
        $this->assertTrue(isset($controller));
        $result = $controller->go();
        $this->assertPattern('/ThinkUp is already installed!/', $result);
        $this->assertPattern('/However, there is no administrator set up for this installation/', $result);
    }

    public function testAlreadyInstalledWithAdmin() {
        //create admin
        $owner_builder = FixtureBuilder::build('owners', array('email'=>'testalreadyinstalled@example.com',
        'is_admin'=>'1'));
        //force a refresh of getTables
        Installer::$show_tables = null;

        //db built, config file and admin exist, so ThinkUp is Already installed
        $controller = new InstallerController(true);
        $this->assertTrue(isset($controller));
        $result = $controller->go();
        $this->assertPattern('/ThinkUp is already installed!/', $result);
        $this->assertNoPattern('/However, there is no administrator set up for this installation/', $result);
    }

    public function testFreshInstallStep1() {
        //remove config file
        Config::destroyInstance();
        $this->removeConfigFile();
        //drop DB
        $this->testdb_helper->drop($this->db);

        $controller = new InstallerController(true);
        $this->assertTrue(isset($controller));
        $result = $controller->go();
        $this->assertPattern('/Your system has everything it needs to run ThinkUp./', $result);
        $this->restoreConfigFile();
    }

    public function testFreshInstallStep2() {
        //drop DB
        $this->testdb_helper->drop($this->db);
        //remove config file
        Config::destroyInstance();
        $this->removeConfigFile();
        //set param for step 2
        $_GET['step'] = '2';

        $controller = new InstallerController(true);
        $this->assertTrue(isset($controller));
        $result = $controller->go();
        $this->assertPattern('/Configure ThinkUp/', $result);
        $this->restoreConfigFile();
    }

    public function testFreshInstallStep3InvalidEmail() {
        //drop DB
        $this->testdb_helper->drop($this->db);
        //remove config file
        Config::destroyInstance();
        $this->removeConfigFile();
        //set param for step 2
        $_GET['step'] = '3';
        //set post values from form
        $_POST['site_email'] = "invalidemail address";
        $_POST['db_user'] = "username";
        $_POST['db_passwd'] = "pass";
        $_POST['db_name'] = "mythinkupdb";
        $_POST['db_type'] = "mysql";
        $_POST['db_host'] = "localhost";
        $_POST['db_socket'] = "/tmp/mysql.sock";
        $_POST['db_port'] = "";
        $_POST['db_prefix'] = "tu_";
        $_POST['password'] = "yoyo";
        $_POST['confirm_password'] = "yoyo";
        $_POST['full_name'] = "My Full Name";

        $controller = new InstallerController(true);
        $this->assertTrue(isset($controller));
        $result = $controller->go();
        $this->assertPattern('/Please enter a valid email address/', $result);
        $this->restoreConfigFile();
    }

    public function testFreshInstallStep3MisssingPasswords() {
        //drop DB
        $this->testdb_helper->drop($this->db);
        //remove config file
        Config::destroyInstance();
        $this->removeConfigFile();
        //set param for step 2
        $_GET['step'] = '3';
        //set post values from form
        $_POST['site_email'] = "you@example.com";
        $_POST['db_user'] = "username";
        $_POST['db_passwd'] = "pass";
        $_POST['db_name'] = "mythinkupdb";
        $_POST['db_type'] = "mysql";
        $_POST['db_host'] = "localhost";
        $_POST['db_socket'] = "/tmp/mysql.sock";
        $_POST['db_port'] = "";
        $_POST['db_prefix'] = "tu_";
        $_POST['password'] = "";
        $_POST['confirm_password'] = "";
        $_POST['full_name'] = "My Full Name";

        $controller = new InstallerController(true);
        $this->assertTrue(isset($controller));
        $result = $controller->go();
        $this->assertPattern('/Please choose a password./', $result);
        $this->restoreConfigFile();
    }

    public function testFreshInstallStep3MismatchedPasswords() {
        //drop DB
        $this->testdb_helper->drop($this->db);
        //remove config file
        Config::destroyInstance();
        $this->removeConfigFile();
        //set param for step 2
        $_GET['step'] = '3';
        //set post values from form
        $_POST['site_email'] = "you@example.com";
        $_POST['db_user'] = "username";
        $_POST['db_passwd'] = "pass";
        $_POST['db_name'] = "mythinkupdb";
        $_POST['db_type'] = "mysql";
        $_POST['db_host'] = "localhost";
        $_POST['db_socket'] = "/tmp/mysql.sock";
        $_POST['db_port'] = "";
        $_POST['db_prefix'] = "tu_";
        $_POST['password'] = "asdfadsf";
        $_POST['confirm_password'] = "asdfasdfasdfasdfasdf";
        $_POST['full_name'] = "My Full Name";

        $controller = new InstallerController(true);
        $this->assertTrue(isset($controller));
        $result = $controller->go();
        $this->assertPattern('/Your passwords did not match./', $result);
        $this->restoreConfigFile();
    }

    public function testFreshInstallStep3InvalidDatabaseCredentials() {
        //get valid connection information
        $config = Config::getInstance();
        $valid_db_host = $config->getValue('db_host');
        $valid_db_socket = $config->getValue('db_socket');

        //drop DB
        $this->testdb_helper->drop($this->db);
        //remove config file
        Config::destroyInstance();
        //unset PDO so it must be recreated
        InstallerMySQLDAO::$PDO = null;
        $this->removeConfigFile();

        //set param for step 2
        $_GET['step'] = '3';
        //set post values from form
        $_POST['site_email'] = "you@example.com";
        $_POST['db_user'] = "username";
        $_POST['db_passwd'] = "pass";
        $_POST['db_name'] = "mythinkupdb";
        $_POST['db_type'] = "mysql";
        $_POST['db_host'] = $valid_db_host;
        $_POST['db_socket'] = $valid_db_socket;
        $_POST['db_port'] = "";
        $_POST['db_prefix'] = "tu_";
        $_POST['password'] = "asdfadsf";
        $_POST['confirm_password'] = "asdfadsf";
        $_POST['full_name'] = "My Full Name";

        $_SERVER['HTTP_HOST'] = "http://example.com";

        $controller = new InstallerController(true);
        $this->assertTrue(isset($controller));
        $result = $controller->go();

        $this->assertPattern('/ThinkUp couldn\'t connect to your database. The error message is:/', $result);
        $this->assertPattern('/Access denied for user \'username\'/', $result);
        $this->restoreConfigFile();
    }

    public function testFreshInstallStep3SuccessfulInstall() {
        //get valid credentials
        $config = Config::getInstance();
        $valid_db_username = $config->getValue('db_user');
        $valid_db_pwd = $config->getValue('db_password');
        $valid_db_name = $config->getValue('db_name');
        $valid_db_host = $config->getValue('db_host');
        $valid_db_socket = $config->getValue('db_socket');

        //drop DB
        $this->testdb_helper->drop($this->db);

        //remove config file
        $config = null;
        Config::destroyInstance();
        //unset PDO so it must be recreated
        InstallerMySQLDAO::$PDO = null;
        //remove config file
        $this->removeConfigFile();
        //force a refresh of getTables
        Installer::$show_tables = null;

        //set param for step 3
        $_GET['step'] = '3';
        //set post values from form
        $_POST['site_email'] = "you@example.com";
        $_POST['password'] = "asdfadsf";
        $_POST['confirm_password'] = "asdfadsf";
        $_POST['db_user'] = $valid_db_username;
        $_POST['db_passwd'] = $valid_db_pwd;
        $_POST['db_name'] = $valid_db_name;
        //$_POST['db_name'] = 'thinkup_install';
        $_POST['db_type'] = "mysql";
        $_POST['db_host'] = $valid_db_host;
        $_POST['db_socket'] = $valid_db_socket;
        $_POST['db_port'] = "";
        $_POST['db_prefix'] = "tu_";
        $_POST['full_name'] = "My Full Name";

        $_SERVER['HTTP_HOST'] = "http://example.com";

        $controller = new InstallerController(true);
        $this->assertTrue(isset($controller));
        $result = $controller->go();

        $this->assertPattern('/ThinkUp has been installed successfully./', $result);
        $this->restoreConfigFile();
        //echo $result;
    }

    public function testRepairProcess() {
        $config = Config::getInstance();

        //repair process gets kicked off when at least 1 whole TU table is missing from the DB
        // drop one table
        $dao = new InstallerMySQLDAO();
        $q = "DROP TABLE ".$config->getValue('table_prefix')."encoded_locations";
        PDODAO::$PDO->exec($q);

        $controller = new InstallerController(true);
        $result = $controller->go();
        $this->assertPattern("/Looks like at least some of ThinkUp's database tables already exist./", $result);

        $_GET["step"] = "repair";
        $_GET["m"] = "db";
        $_SERVER['REQUEST_URI'] = '/';
        $config->setValue('repair', true);
        $controller = new InstallerController(true);
        $result = $controller->go();
        $this->assertPattern("/Check your existing ThinkUp tables. If some tables are missing or need repair, ".
        "ThinkUp will attempt to create or repair them./", $result);

        $_POST["repair"] = "yespls";
        $controller = new InstallerController(true);
        $result = $controller->go();
        $this->assertPattern("/Created table tu_encoded_locations/", $result);
    }
}
