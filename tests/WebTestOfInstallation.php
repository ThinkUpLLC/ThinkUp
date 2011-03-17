<?php
/**
 *
 * ThinkUp/tests/WebTestOfInstallation.php
 *
 * Copyright (c) 2009-2011 Gina Trapani
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
 * @copyright 2009-2011 Gina Trapani
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';

class WebTestOfInstallation extends ThinkUpBasicWebTestCase {

    public function setUp() {
        parent::setUp();

        global $THINKUP_CFG;
        //Make sure test_installer directory exists
        if (!file_exists($THINKUP_CFG['source_root_path'].'webapp/test_installer/')) {
            exec('mkdir webapp/test_installer/');
        }

        //Clean up files from test installation
        exec('rm -rf webapp/test_installer/*');
        //Generate new user distribution based on current state of the tree
        exec('extras/scripts/generate-distribution');
        //Extract into test_installer directory and set necessary folder permissions
        exec('cp build/thinkup.zip webapp/test_installer/.;'.
        'cd webapp/test_installer/;'.
        'unzip thinkup.zip;chmod -R 777 thinkup');
    }

    public function tearDown() {
        global $THINKUP_CFG;
        //Clean up test installation files
        exec('rm -rf webapp/test_installer/*');

        //Delete test database created during installation process
        require THINKUP_WEBAPP_PATH.'config.inc.php';

        //Override default CFG values
        $THINKUP_CFG['db_name'] = $this->test_database_name;

        $this->testdb_helper = new ThinkUpTestDatabaseHelper();
        $this->testdb_helper->drop($this->test_database_name);

        parent::tearDown();
    }

    public function testSuccessfulInstallationAndAccountActivation() {
        require THINKUP_WEBAPP_PATH.'config.inc.php';

        //Config file doesn't exist
        $this->assertFalse(file_exists($THINKUP_CFG['source_root_path'].
        'webapp/test_installer/thinkup/config.inc.php'));

        //Start installation process
        $this->get($this->url.'/test_installer/thinkup/');
        $this->assertTitle("ThinkUp");
        $this->assertText('ThinkUp\'s configuration file does not exist! Try installing ThinkUp.');
        $this->clickLink("installing ThinkUp.");
        $this->assertText('Great! Your system has everything it needs to run ThinkUp. You may proceed to the next '.
        'step.');
        $this->clickLinkById('nextstep');

        $this->assertText('Create Your ThinkUp Account');
        $this->setField('full_name', 'ThinkUp J. User');
        $this->setField('site_email', 'user@example.com');
        $this->setField('password', 'secret');
        $this->setField('confirm_password', 'secret');
        $this->setField('timezone', 'America/Los_Angeles');

        $this->setField('db_host', $THINKUP_CFG['db_host']);
        $this->setField('db_name', $this->test_database_name);
        $this->setField('db_user', $THINKUP_CFG['db_user']);
        $this->setField('db_passwd', $THINKUP_CFG['db_password']);
        $this->setField('db_socket', $THINKUP_CFG['db_socket']);
        $this->clickSubmitByName('Submit');

        $this->assertText('ThinkUp has been installed successfully. Check your email account; an account activation '.
        'message has been sent.');

        //Config file has been written
        $this->assertTrue(file_exists($THINKUP_CFG['source_root_path'].
        'webapp/test_installer/thinkup/config.inc.php'));

        //Test bad activation code
        $this->get($this->url.'/test_installer/thinkup/session/activate.php?usr=user@example.com&code=dummycode');
        $this->assertText('Houston, we have a problem: Account activation failed.');

        //Get activation code for user from database
        date_default_timezone_set('America/Los_Angeles');
        $owner_dao = new OwnerMySQLDAO();
        $code = $owner_dao->getActivationCode('user@example.com');
        $activation_code = $code['activation_code'];

        //Visit activation page
        $this->get($this->url.'/test_installer/thinkup/session/activate.php?usr=user@example.com&code='.
        $activation_code);
        $this->assertNoText('Houston, we have a problem: Account activation failed.');
        $this->assertText('Success! Your account has been activated. Please log in.');

        //Log into ThinkUp
        $this->clickLink('Log in');

        $this->setField('email', 'user@example.com');
        $this->setField('pwd', 'secret');
        $this->click("Log In");
        $this->assertText('You have no accounts configured. Set up an account now');

        //Visit Settings page and assert content there
        $this->click("Settings");
        $this->assertTitle('Configure Your Account | ThinkUp');
        $this->assertText('As an administrator you can configure all installed plugins.');
    }
}