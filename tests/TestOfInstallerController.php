<?php
/**
 *
 * ThinkUp/tests/TestOfInstallerController.php
 *
 * Copyright (c) 2009-2013 Dwi Widiastuti, Gina Trapani
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
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Guillaume Boudreau
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfInstallerController extends ThinkUpUnitTestCase {
    /**
     * Start time
     * @var int
     */
    var $start;
    /**
     *
     * @var int
     */
    var $current_test_index = 0;
    public function setUp() {
        if (getenv("TEST_TIMING")=="1") {
            list($usec, $sec) = explode(" ", microtime());
            $this->start =  ((float)$usec + (float)$sec);
        }
        $this->current_test_index++;
        parent::setUp();
        if ( !defined('THINKUP_ROOT_PATH') ) {
            define('THINKUP_ROOT_PATH', str_replace("\\",'/', dirname(dirname(__FILE__))) .'/');
        }
        if ( !defined('THINKUP_WEBAPP_PATH') ) {
            define('THINKUP_WEBAPP_PATH', THINKUP_ROOT_PATH.'webapp/');
        }
    }

    public function tearDown() {
        parent::tearDown();
        if (getenv("TEST_TIMING")=="1") {
            list($usec, $sec) = explode(" ", microtime());
            $finish =  ((float)$usec + (float)$sec);
            $runtime = round($finish - $this->start);
            printf($runtime ." seconds\n");
        }
    }

    protected function time($name) {
        if (getenv("TEST_TIMING")=="1") {
            printf($name." ");
        }
    }
    public function testAlreadyInstalledNoAdmin() {
        self::time(__METHOD__);
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
        self::time(__METHOD__);
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

    public function testFreshInstallStep1ReqsNotMet() {
        //get whatever session save path is set to
        $session_save_path = ini_get('session.save_path');

        self::time(__METHOD__);
        //remove config file
        Config::destroyInstance();
        $this->removeConfigFile();
        //drop DB
        $this->testdb_helper->drop($this->test_database_name);

        $reqs = array('curl'=>false, 'gd'=>false, 'pdo'=>false, 'pdo_mysql'=>false, 'json'=>false, 'hash'=>false);

        $controller = new InstallerController(true, $reqs);
        $this->assertTrue(isset($controller));
        $result = $controller->go();

        $this->debug($result);
        //system requirements have not been met
        $this->assertNoPattern('/Your system has everything it needs to run ThinkUp./', $result);
        $this->assertPattern('/Your web server isn\'t set up to run ThinkUp./', $result);
        $this->assertPattern('/ThinkUp needs the /', $result);

        //make sure install did not auto-progress to step 2 b/c
        $this->assertNoPattern('/Create Your ThinkUp Account/', $result);
        $this->restoreConfigFile();

        //reset back to what it was
        ini_set('session.save_path', $session_save_path);
    }

    public function testFreshInstallStep1SessionReqNotMet() {
        self::time(__METHOD__);
        //remove config file
        Config::destroyInstance();
        $this->removeConfigFile();
        //drop DB
        $this->testdb_helper->drop($this->test_database_name);

        $reqs = array('curl'=>true, 'gd'=>true, 'pdo'=>true, 'pdo_mysql'=>true, 'json'=>true, 'hash'=>true);

        //set session save path to invalid path
        ini_set('session.save_path', '/someinvalidpath/wecantwriteto/');

        $controller = new InstallerController(true, $reqs);
        $this->assertTrue(isset($controller));
        $result = $controller->go();

        $this->debug($result);
        //system requirements have not been met
        $this->assertNoPattern('/Your system has everything it needs to run ThinkUp./', $result);
        $this->assertPattern('/Your web server isn\'t set up to run ThinkUp./', $result);
        $this->assertPattern('/The PHP <code>session.save_path<\/code> directory/', $result);

        //make sure install did not auto-progress to step 2 b/c
        $this->assertNoPattern('/Create Your ThinkUp Account/', $result);
        $this->restoreConfigFile();
    }

    public function testFreshInstallStep1AllReqsMet() {
        self::time(__METHOD__);

        //get whatever session save path is set to
        $session_save_path = ini_get('session.save_path');

        ini_set('session.save_path', FileDataManager::getDataPath());

        //remove config file
        Config::destroyInstance();
        $this->removeConfigFile();
        //drop DB
        $this->testdb_helper->drop($this->test_database_name);

        $controller = new InstallerController(true);
        $this->assertTrue(isset($controller));
        $result = $controller->go();
        $this->debug($result);
        $this->assertPattern('/Your system has everything it needs to run ThinkUp./', $result);
        //make sure we've auto-progressed to step 2 b/c all requirements have been met
        $this->assertPattern('/Create your ThinkUp/', $result);
        $this->restoreConfigFile();
        //reset back to what it was
        ini_set('session.save_path', $session_save_path);
    }

    public function testFreshInstallStep2() {
        self::time(__METHOD__);
        //drop DB
        $this->testdb_helper->drop($this->test_database_name);
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

    public function testFreshInstallStep2TimezoneNotSetInPHPiniFile() {
        self::time(__METHOD__);
        //drop DB
        $this->testdb_helper->drop($this->test_database_name);
        //remove config file
        Config::destroyInstance();
        $this->removeConfigFile();

        //set date.timezone in PHP.ini
        ini_set('date.timezone', '');

        //set param for step 2
        $_GET['step'] = '2';

        $controller = new InstallerController(true);
        $this->assertTrue(isset($controller));
        $result = $controller->go();

        $v_mgr = $controller->getViewManager();
        $this->assertEqual($v_mgr->getTemplateDataItem('current_tz'), '');

        $this->restoreConfigFile();
    }

    public function testFreshInstallStep3InvalidEmail() {
        self::time(__METHOD__);
        //drop DB
        $this->testdb_helper->drop($this->test_database_name);
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
        $_POST['password'] = "7yoyoo123";
        $_POST['confirm_password'] = "7yoyoo123";
        $_POST['full_name'] = "My Full Name";
        $_POST['timezone'] = "America/Los_Angeles";

        $controller = new InstallerController(true);
        $this->assertTrue(isset($controller));
        $result = $controller->go();
        $this->assertPattern('/Please enter a valid email address/', $result);
        $this->restoreConfigFile();
    }

    public function testFreshInstallStep3MisssingPasswords() {
        self::time(__METHOD__);
        //drop DB
        $this->testdb_helper->drop($this->test_database_name);
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
        $_POST['timezone'] = "America/Los_Angeles";

        $controller = new InstallerController(true);
        $this->assertTrue(isset($controller));
        $result = $controller->go();
        $this->assertPattern('/Please choose a password./', $result);
        $this->restoreConfigFile();
    }

    public function testFreshInstallStep3MismatchedPasswords() {
        self::time(__METHOD__);
        //drop DB
        $this->testdb_helper->drop($this->test_database_name);
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
        $_POST['timezone'] = "America/Los_Angeles";

        $controller = new InstallerController(true);
        $this->assertTrue(isset($controller));
        $result = $controller->go();
        $this->assertPattern('/Your passwords did not match./', $result);
        $this->restoreConfigFile();
    }

    public function testFreshInstallStep3PasswordsTooShort() {
        self::time(__METHOD__);
        //drop DB
        $this->testdb_helper->drop($this->test_database_name);
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
        $_POST['password'] = "test";
        $_POST['confirm_password'] = "test";
        $_POST['full_name'] = "My Full Name";
        $_POST['timezone'] = "America/Los_Angeles";

        $controller = new InstallerController(true);
        $this->assertTrue(isset($controller));
        $result = $controller->go();
        $this->assertPattern('/Password must be at least 8 characters and contain both numbers and letters./', $result);
        $this->restoreConfigFile();
    }

    public function testFreshInstallStep3PasswordsNotAlphanumeric() {
        self::time(__METHOD__);
        //drop DB
        $this->testdb_helper->drop($this->test_database_name);
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
        $_POST['password'] = "freshtestword";
        $_POST['confirm_password'] = "freshtestword";
        $_POST['full_name'] = "My Full Name";
        $_POST['timezone'] = "America/Los_Angeles";

        $controller = new InstallerController(true);
        $this->assertTrue(isset($controller));
        $result = $controller->go();
        $this->assertPattern('/Password must be at least 8 characters and contain both numbers and letters/', $result);
        $this->restoreConfigFile();
    }

    public function testFreshInstallStep3NoTimezoneSet() {
        self::time(__METHOD__);
        //drop DB
        $this->testdb_helper->drop($this->test_database_name);
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
        $_POST['password'] = "pass12345";
        $_POST['confirm_password'] = "pass12345";
        $_POST['full_name'] = "My Full Name";
        $_POST['timezone'] = "";

        $controller = new InstallerController(true);
        $this->assertTrue(isset($controller));
        $result = $controller->go();
        $this->debug($result);
        $this->assertPattern("/Please select a time zone./", $result);
        $this->restoreConfigFile();
    }

    public function testFreshInstallStep3InvalidDatabaseCredentials() {
        self::time(__METHOD__);
        //get valid connection information
        $config = Config::getInstance();
        $valid_db_host = $config->getValue('db_host');
        $valid_db_socket = $config->getValue('db_socket');

        //drop DB
        $this->testdb_helper->drop($this->test_database_name);
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
        $_POST['password'] = "987asdfadsf";
        $_POST['confirm_password'] = "987asdfadsf";
        $_POST['full_name'] = "My Full Name";
        $_POST['timezone'] = "America/Los_Angeles";

        $_SERVER['HTTP_HOST'] = "http://example.com";

        $controller = new InstallerController(true);
        $this->assertTrue(isset($controller));

        $result = $controller->go();
        $this->debug($result);
        $this->assertPattern('/ThinkUp couldn\'t connect to your database. The error message is:/', $result);
        $this->restoreConfigFile();
    }

    public function testFreshInstallStep3InvalidDatabaseName() {
        self::time(__METHOD__);
        //get valid connection information
        $config = Config::getInstance();
        $valid_db_host = $config->getValue('db_host');
        $valid_db_socket = $config->getValue('db_socket');
        $valid_db_user = $config->getValue('db_user');
        $valid_db_password = $config->getValue('db_password');
        //drop DB
        $this->testdb_helper->drop($this->test_database_name);

        //remove config file
        Config::destroyInstance();
        //unset PDO so it must be recreated
        InstallerMySQLDAO::$PDO = null;
        $this->removeConfigFile();

        //set param for step 2
        $_GET['step'] = '3';
        //set post values from form
        $_POST['site_email'] = "you@example.com";
        $_POST['db_user'] = $valid_db_user;
        $_POST['db_passwd'] = $valid_db_password;
        $_POST['db_name'] = "mythinkupdb `lol";
        $_POST['db_type'] = "mysql";
        $_POST['db_host'] = $valid_db_host;
        $_POST['db_socket'] = $valid_db_socket;
        $_POST['db_port'] = "";
        $_POST['db_prefix'] = "tu_";
        $_POST['password'] = "asdfadsf123";
        $_POST['confirm_password'] = "asdfadsf123";
        $_POST['full_name'] = "My Full Name";
        $_POST['timezone'] = "America/Los_Angeles";

        $_SERVER['HTTP_HOST'] = "http://example.com";

        $controller = new InstallerController(true);
        $this->assertTrue(isset($controller));
        $result = $controller->go();

        $this->assertPattern('/ThinkUp couldn\'t connect to your database. The error message is:/', $result);
        $this->assertPattern('/Unknown database &#39;mythinkupdb `lol&#39;/', $result);
        $this->restoreConfigFile();
    }

    /*
     * This test can take an unreasonablely long time to complete (over an hour) due to connection timeouts on some
     * server setups. Commenting out for faster runs on the build server.
     */
    public function testFreshInstallStep3InvalidDatabaseHost() {
        self::time(__METHOD__);
        //get valid connection information
        $config = Config::getInstance();
        $valid_db_socket = $config->getValue('db_socket');
        $valid_db_user = $config->getValue('db_user');
        $valid_db_password = $config->getvalue('db_password');
        $valid_db_port = $config->getValue('db_port');
        $invalid_db_host = $config->getValue('invalid_db_host');
        if (!isset($invalid_db_host)) {
            $invalid_db_host = "127.0.0.2";
        }

        //drop DB
        $this->testdb_helper->drop($this->test_database_name);
        //remove config file
        Config::destroyInstance();
        //unset PDO so it must be recreated
        InstallerMySQLDAO::$PDO = null;
        $this->removeConfigFile();

        ini_set("error_reporting", E_ERROR);

        //set param for step 2
        $_GET['step'] = '3';
        //set post values from form
        $_POST['site_email'] = "you@example.com";
        $_POST['db_user'] = $valid_db_user;
        $_POST['db_passwd'] = $valid_db_password;
        $_POST['db_name'] = "mythinkupdb";
        $_POST['db_type'] = "mysql";
        $_POST['db_host'] = $invalid_db_host;
        $_POST['db_socket'] = $valid_db_socket;
        $_POST['db_port'] = $valid_db_port;
        $_POST['db_prefix'] = "tu_";
        $_POST['password'] = "asdfads123f";
        $_POST['confirm_password'] = "asdfads123f";
        $_POST['full_name'] = "My Full Name";
        $_POST['timezone'] = "America/Los_Angeles";

        $_SERVER['HTTP_HOST'] = "http://example.com";

        $controller = new InstallerController(true);
        $this->assertTrue(isset($controller));
        $result = $controller->go();

        $this->assertPattern('/ThinkUp couldn\'t connect to your database./', $result);
        //Different systems get different errors
        //$this->assertPattern('/Unknown MySQL server host \'localcheese\'/', $result);
        //$this->assertPattern('/php_network_getaddresses: getaddrinfo failed', $result);
        $this->restoreConfigFile();
        ini_set("error_reporting", E_STRICT);
    }

    public function testFreshInstallStep3SuccessfulInstall() {
        self::time(__METHOD__);
        //get valid credentials
        $config = Config::getInstance();
        $valid_db_username = $config->getValue('db_user');
        $valid_db_pwd = $config->getValue('db_password');
        $valid_db_name = $config->getValue('db_name');
        $valid_db_host = $config->getValue('db_host');
        $valid_db_socket = $config->getValue('db_socket');

        //drop DB
        $this->testdb_helper->drop($this->test_database_name);

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
        $_POST['password'] = "asdfadsf123";
        $_POST['confirm_password'] = "asdfadsf123";
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
        $_POST['timezone'] = "America/Los_Angeles";

        $_SERVER['HTTP_HOST'] = "example.com";

        $controller = new InstallerController(true);
        $this->assertTrue(isset($controller));
        $result = $controller->go();
        $this->debug($result);
        $this->assertPattern('/ThinkUp has been installed successfully./', $result);

        $option_dao = DAOFactory::getDAO('OptionDAO');
        $current_stored_server_name = $option_dao->getOptionByName(OptionDAO::APP_OPTIONS, 'server_name');
        $this->assertNotNull($current_stored_server_name);
        $this->assertEqual($current_stored_server_name->option_value, 'example.com');
        $this->assertEqual($current_stored_server_name->option_name, 'server_name');

        $install_email = Mailer::getLastMail();
        $this->debug($install_email);
        $this->assertPattern("/http:\/\/example.com\/session\/activate.php\?usr=you\%40example.com\&code\=/",
        $install_email);

        $this->restoreConfigFile();
        //echo $result;
    }

    public function testRepairProcess() {
        self::time(__METHOD__);
        $config = Config::getInstance();

        //repair process gets kicked off when at least 1 whole TU table is missing from the DB
        // drop one table
        $dao = new InstallerMySQLDAO();
        $q = "DROP TABLE ".$config->getValue('table_prefix')."encoded_locations";
        PDODAO::$PDO->exec($q);

        $controller = new InstallerController(true);
        $result = $controller->go();
        $this->assertPattern("/Looks like at least some of ThinkUp\'s database tables already exist./", $result);

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
        $this->assertPattern("/Created table " . $config->getValue('table_prefix') . "encoded_locations/", $result);
    }
}
