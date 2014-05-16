<?php
/**
 *
 * ThinkUp/tests/WebTestOfInstallation.php
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
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';

class WebTestOfInstallation extends ThinkUpBasicWebTestCase {

    public function setUp() {
        parent::setUp();

        global $THINKUP_CFG;
        //Make sure test_installer directory exists
        chdir(dirname(__FILE__) . '/../');
        if (!file_exists($THINKUP_CFG['source_root_path'].'/webapp/test_installer/')) {
            @exec('mkdir webapp/test_installer/');
            @exec('chmod -R 777 webapp/test_installer/');
        }

        //Generate new user distribution based on current state of the tree
        @exec('extras/scripts/generate-distribution');
    }

    public function setUpDefaultFolderInstallation() {
        //Extract into test_installer directory and set necessary folder permissions
        @exec('cp build/thinkup.zip webapp/test_installer/.;'.
        'cd webapp/test_installer/;unzip thinkup.zip;'.
        'mkdir thinkup/data/compiled_view/;chmod -R 777 thinkup;');
    }

    public function setUpCustomFolderInstallation() {
        //Extract into test_installer directory and set necessary folder permissions
        @exec('cp build/thinkup.zip webapp/test_installer/.;'.
        'cd webapp/test_installer/;unzip thinkup.zip;mv thinkup mythinkupfolder;'.
        'mkdir mythinkupfolder/data/compiled_view;chmod -R 777 mythinkupfolder;');
    }

    public function setUpNonWritableInstallation() {
        //Extract into test_installer directory and set necessary folder permissions
        @exec('cp build/thinkup.zip webapp/test_installer/.;'.
        'cd webapp/test_installer/;unzip thinkup.zip;'.
        'mkdir thinkup/data/compiled_view/;');
    }

    public function tearDown() {
        global $THINKUP_CFG;
        //Clean up test installation files
        @exec('rm -rf ' . THINKUP_WEBAPP_PATH.'test_installer/*' );
        @exec('rmdir ' . THINKUP_WEBAPP_PATH.'test_installer/' );
        //Delete test database created during installation process
        require THINKUP_WEBAPP_PATH.'config.inc.php';

        //Override default CFG values
        $THINKUP_CFG['db_name'] = $this->test_database_name;

        $this->testdb_helper = new ThinkUpTestDatabaseHelper();
        $this->testdb_helper->drop($this->test_database_name);

        parent::tearDown();
    }

    public function testSuccessfulInstallationAndAccountActivation() {
        self::setUpDefaultFolderInstallation();
        require THINKUP_WEBAPP_PATH.'config.inc.php';

        //Config file doesn't exist
        $this->assertFalse(file_exists($THINKUP_CFG['source_root_path'].
          '/webapp/test_installer/thinkup/config.inc.php'));

        //sleep(1000);
        //Start installation process
        $this->get($this->url.'/test_installer/thinkup/');
        $this->assertTitle("ThinkUp");
        $this->assertText('ThinkUp\'s configuration file does not exist! Try installing ThinkUp.');
        $this->clickLink("installing ThinkUp.");
        $this->assertText('Great! Your system has everything it needs to run ThinkUp.');
        $this->clickLinkById('nextstep');

        //sleep(1000);
        $this->assertText('Create your ThinkUp account');
        $this->setField('full_name', 'ThinkUp J. User');
        $this->setField('site_email', 'user@example.com');
        $this->setField('password', 'secret12345');
        $this->setField('confirm_password', 'secret12345');
        $this->setField('timezone', 'America/Los_Angeles');

        $this->setField('db_host', $THINKUP_CFG['db_host']);
        $this->setField('db_name', $this->test_database_name);
        $this->setField('db_user', $THINKUP_CFG['db_user']);
        $this->setField('db_passwd', $THINKUP_CFG['db_password']);
        $this->setField('db_socket', $THINKUP_CFG['db_socket']);
        $this->setField('db_prefix', $THINKUP_CFG['table_prefix']);
        $this->clickSubmitByName('Submit');

        $this->assertText('ThinkUp has been installed successfully. Check your email account; an account activation '.
        'message has been sent.');

        //Config file has been written
        $this->assertTrue(file_exists($THINKUP_CFG['source_root_path'].
          'webapp/test_installer/thinkup/config.inc.php'));

        //sleep(1000);
        //Test bad activation code
        $this->get($this->url.'/test_installer/thinkup/session/activate.php?usr=user@example.com&code=dummycode');
        $this->assertPattern("/Houston, we have a problem: Account activation failed\./");

        //Get activation code for user from database
        Utils::setDefaultTimezonePHPini();
        $owner_dao = new OwnerMySQLDAO();
        $code = $owner_dao->getActivationCode('user@example.com');
        $activation_code = $code['activation_code'];

        //Visit activation page
        $this->get($this->url.'/test_installer/thinkup/session/activate.php?usr=user@example.com&code='.
        $activation_code);
        $this->assertNoText('Houston, we have a problem: Account activation failed.');
        $this->assertPattern("/Your account has been activated\./");

        //Try to activate again
        $this->get($this->url.'/test_installer/thinkup/session/activate.php?usr=user@example.com&code='.
        $activation_code);
        $this->assertNoText('Houston, we have a problem: Account activation failed.');
        $this->assertNoText('Success! Your account has been activated. Please log in.');
        $this->assertPattern("/You have already activated your account\./");

        //Log into ThinkUp
        $this->clickLink('Log in');

        $this->setField('email', 'user@example.com');
        $this->setField('pwd', 'secret12345');
        $this->click("Log In");
        $this->assertText('Welcome to ThinkUp. Let\'s get started.');
        //$this->showSource();

        //Visit Settings page and assert content there
        $this->click("Settings");
        $this->assertTitle('Configure Your Account | ThinkUp');
        $this->assertText('Settings');
    }

    public function testSuccessfulInstallationInNonWritableFolder() {
        self::setUpNonWritableInstallation();
        require THINKUP_WEBAPP_PATH.'config.inc.php';

        //Config file doesn't exist
        $this->assertFalse(file_exists($THINKUP_CFG['source_root_path'].
        '/webapp/test_installer/thinkup/config.inc.php'));

        //sleep(1000);
        //Start installation process
        @exec('chmod -R 555 webapp/test_installer/thinkup/data;');
        $this->get($this->url.'/test_installer/thinkup/');
        $this->assertTitle("ThinkUp Permissions Error");

        //data_dir isn't writable
        $this->assertText('Oops! ThinkUp is unable to run because of incorrect folder permissions. '.
        'To fix this problem, run');
        @exec('chmod -R 777 webapp/test_installer/thinkup/data;');

        $this->get($this->url.'/test_installer/thinkup/');

        $this->assertText('ThinkUp\'s configuration file does not exist! Try installing ThinkUp.');
        $this->clickLink("installing ThinkUp.");
        $this->assertText('Great! Your system has everything it needs to run ThinkUp.');
        $this->clickLinkById('nextstep');

        //sleep(1000);
        $this->assertText('Create your ThinkUp account');
        $this->setField('full_name', 'ThinkUp J. User');
        $this->setField('site_email', 'user@example.com');
        $this->setField('password', 'secret12345');
        $this->setField('confirm_password', 'secret12345');
        $this->setField('timezone', 'America/Los_Angeles');

        $this->setField('db_host', $THINKUP_CFG['db_host']);
        $this->setField('db_name', $this->test_database_name);
        $this->setField('db_user', $THINKUP_CFG['db_user']);
        $this->setField('db_passwd', $THINKUP_CFG['db_password']);
        $this->setField('db_socket', $THINKUP_CFG['db_socket']);
        $this->setField('db_prefix', $THINKUP_CFG['table_prefix']);
        @exec('chmod 555 webapp/test_installer/thinkup;');
        $this->clickSubmitByName('Submit');

        $this->assertNoText('ThinkUp has been installed successfully. Check your email account; an account activation '.
        'message has been sent.');
        $this->assertText("Use root (or sudo) to create the file manually, and allow PHP to write to it, by executing the");

        //Config file has not been written
        $this->assertTrue(!file_exists($THINKUP_CFG['source_root_path'].
        '/webapp/test_installer/thinkup/config.inc.php'));

        @exec('chmod 777 webapp/test_installer/thinkup;');
        @exec('touch webapp/test_installer/thinkup/config.inc.php;'.
        'chmod 777 webapp/test_installer/thinkup/config.inc.php');

        $this->clickSubmitByName("Submit");

        //Config file has been written
        $this->assertTrue(file_exists($THINKUP_CFG['source_root_path'].
        '/webapp/test_installer/thinkup/config.inc.php'));

        //$this->showSource();
        $this->assertText('ThinkUp has been installed successfully. Check your email account; an account activation '.
        'message has been sent.');
    }

    public function testSuccessfulInstallationInCustomFolder() {
        self::setUpCustomFolderInstallation();
        require THINKUP_WEBAPP_PATH.'config.inc.php';

        //Config file doesn't exist
        $this->assertFalse(file_exists($THINKUP_CFG['source_root_path'].
          '/webapp/test_installer/mythinkupfolder/config.inc.php'));

        //sleep(1000);
        //Start installation process
        $this->get($this->url.'/test_installer/mythinkupfolder/');
        $this->assertTitle("ThinkUp");
        $this->assertText('ThinkUp\'s configuration file does not exist!');
        $this->clickLink("installing ThinkUp.");
        $this->assertText('Your system has everything it needs to run ThinkUp.');
        $this->clickLinkById('nextstep');

        //sleep(1000);
        $this->assertText('Create your ThinkUp account');
        $this->setField('full_name', 'ThinkUp J. User');
        $this->setField('site_email', 'user@example.com');
        $this->setField('password', 'secret12345');
        $this->setField('confirm_password', 'secret12345');
        $this->setField('timezone', 'America/Los_Angeles');

        $this->setField('db_host', $THINKUP_CFG['db_host']);
        $this->setField('db_name', $this->test_database_name);
        $this->setField('db_user', $THINKUP_CFG['db_user']);
        $this->setField('db_passwd', $THINKUP_CFG['db_password']);
        $this->setField('db_socket', $THINKUP_CFG['db_socket']);
        $this->setField('db_prefix', $THINKUP_CFG['table_prefix']);
        $this->clickSubmitByName('Submit');

        //$this->showSource();
        //sleep(1000);
        $this->assertText('ThinkUp has been installed successfully. Check your email account;');

        //Config file has been written
        $this->assertTrue(file_exists($THINKUP_CFG['source_root_path'].
        '/webapp/test_installer/mythinkupfolder/config.inc.php'));
    }

    public function testFieldLevelMessagesForInvalidInputs() {
        self::setUpDefaultFolderInstallation();
        require THINKUP_WEBAPP_PATH.'config.inc.php';

        //Config file doesn't exist
        $this->assertFalse(file_exists($THINKUP_CFG['source_root_path'].
        'webapp/test_installer/thinkup/config.inc.php'));

        //Start installation process
        $this->get($this->url.'/test_installer/thinkup/');
        $this->assertTitle("ThinkUp");
        $this->assertText('ThinkUp\'s configuration file does not exist! Try installing ThinkUp.');
        $this->clickLink("installing ThinkUp.");
        $this->assertText('Great! Your system has everything it needs to run ThinkUp.');
        $this->clickLinkById('nextstep');

        $this->assertText('Create your ThinkUp account');
        $this->setField('full_name', '');
        $this->setField('site_email', 'notavalidemailaddress');
        $this->setField('password', 'secdddddd');
        $this->setField('confirm_password', 'secret');
        $this->setField('timezone', '');

        $this->setField('db_host', '');
        $this->setField('db_name', '');
        $this->setField('db_user', '');
        $this->setField('db_passwd', '');
        $this->setField('db_socket', '');
        $this->clickSubmitByName('Submit');

        $this->assertNoText('ThinkUp has been installed successfully. Check your email account; an account activation '.
        'message has been sent.');
        $this->assertText('Your passwords did not match');
        $this->assertText('Please enter a database host.');
        $this->assertText('Please enter a database host.');
        $this->assertText("Please select a time zone.");
    }

    // Can't use this test b/c we can't change the PHP ini settings of host instance
    //    public function testTimezoneDropdownDefaultValue() {
    //        //if php.ini's date.timezone is set to America/New_York, so should the tz dropdown in the UI
    //        ini_set('date.timezone', 'America/New_York');
    //        $this->get($this->url.'/test_installer/thinkup/install/index.php?step=2');
    //        $this->assertFieldById('timezone', 'America/New_York');
    //
    //        //if php.ini's date.timezone isn't set at all the tz dropdown should say so
    //        ini_set('date.timezone', '');
    //        Utils::setDefaultTimezonePHPini();
    //        $this->get($this->url.'/test_installer/thinkup/install/index.php?step=2');
    //        $this->assertFieldById('timezone', '');
    //    }
}
