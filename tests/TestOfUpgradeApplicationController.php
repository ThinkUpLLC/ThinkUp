<?php
/**
 *
 * ThinkUp/tests/TestOfUpgradeApplicationController.php
 *
 * Copyright (c) 2012-2013 Mark Wilkie
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
 * TestOfUpgradeApplicationController
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Mark Wilkie
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/web_tester.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfUpgradeApplicationController extends ThinkUpUnitTestCase {

    public function setUp(){
        parent::setUp();
        $test_app_dir = THINKUP_WEBAPP_PATH . 'test_installer';
        $this->test_web_dir = $test_app_dir . '/thinkup';

        //make sure test_installer files are gone to prevent permissions problems
        if (file_exists($test_app_dir)) {
            exec('rm -rf ' . $test_app_dir );
        }
        //Make sure test_installer directory exists
        if (!file_exists('$test_app_dir')) {
            exec('mkdir ' . $test_app_dir);
        }
        //Generate new user distribution based on current state of the tree
        exec('./extras/scripts/generate-distribution');
        //Extract into test_installer directory and set necessary folder permissions
        exec('cp build/thinkup.zip ' . $test_app_dir . ';' .
        'cd ' . $test_app_dir . ';' .
        'unzip thinkup.zip; chmod -R 777 thinkup;'.
        'cd thinkup;chmod -R 777 data; cp -f config.sample.inc.php config.inc.php;');
    }

    public function tearDown(){
        parent::tearDown();
        MockUpgradeApplicationController::$current_exception = false;
        //Clean up test installation files
        $test_app_dir = THINKUP_WEBAPP_PATH . 'test_installer';
        if (file_exists($test_app_dir)) {
            exec('rm -rf ' . $test_app_dir);
        }
        parent::tearDown();
    }

    /**
     * Test controller for non-logged in user
     * @TODO Possibly load the resulting markup as a DOM object and test various children in it;
     * this would enforce valid markup
     */
    public function testControlNotLoggedIn() {
        $config = Config::getInstance();
        $controller = new UpgradeApplicationController(true);
        $results = $controller->go();
        $this->assertPattern( '/session\/login.php\?redirect\=/', $controller->redirect_destination);
    }

    public function testException() {
        $this->simulateLogin('me@example.com', true);
        $controller = new UpgradeApplicationController(true);
    }

    public function testReadyToUpdate() {
        $this->simulateLogin('me@example.com', true);
        $controller = new MockUpgradeApplicationController(true);
        MockUpgradeApplicationController::$current_exception = 'Stuff Happened';
        $results = $controller->go();
        $this->assertPattern('/Stuff Happened/', $results);

        $controller = new MockUpgradeApplicationController(true);
        MockUpgradeApplicationController::$current_exception = false;
        $results = $controller->go();
        $v_mgr = $controller->getViewManager();
        $this->assertTrue($v_mgr->getTemplateDataItem('updateable'));
    }

    public function testRunUpdate() {
        $config = Config::getInstance();
        $this->simulateLogin('me@example.com', true);
        $_GET['run_update'] = true;
        $controller = new MockUpgradeApplicationController(true);
        MockUpgradeApplicationController::$current_exception = 'Stuff Happened';
        $results = $controller->go();
        $this->assertPattern('/Stuff Happened/', $results);

        $controller = new MockUpgradeApplicationController(true);
        MockUpgradeApplicationController::$current_exception = false;
        $results = $controller->go();
        $this->assertPattern('/\/install\/upgrade-application.php\?ran_update=1/', $controller->redirect_destination);
    }

    public function testRanUpdate() {
        $this->simulateLogin('me@example.com', true);
        $_SERVER['SERVER_NAME'] = 'http://example.com';
        $_GET['ran_update'] = true;
        $controller = new MockUpgradeApplicationController(true);
        $results = $controller->go();

        $option_dao = DAOFactory::getDAO('OptionDAO');
        $current_stored_server_name = $option_dao->getOptionByName(OptionDAO::APP_OPTIONS, 'server_name');
        $this->assertNotNull($current_stored_server_name);
        $this->assertEqual($current_stored_server_name->option_value, 'http://example.com');
        $this->assertEqual($current_stored_server_name->option_name, 'server_name');

        $this->assertPattern('/Success! You\'re running the latest version of ThinkUp./', $results);
    }

    public function testNotEnoughAvailableFileSpace() {
        // More disk space than is available
        AppUpgraderDiskUtil::$DISK_SPACE_NEEDED = disk_free_space(dirname(__FILE__))+(1024*1024*10);
        $upgrade_controller = new UpgradeApplicationController(true);
        try {
            $upgrade_controller->runUpdate($this->test_web_dir);
            $this->fail("Should throw an exception...");
        } catch(Exception $e) {
            $this->assertPattern('/There is not enough free disk space to perform an update/', $e->getMessage());
        }
        AppUpgraderDiskUtil::$DISK_SPACE_NEEDED = 104857600; // set back to 100 MB
    }

    public function testPermissions() {
        chmod( $this->test_web_dir . '/index.php', 0444 ); // make a file not writeable
        $this->debug($this->test_web_dir.'/index.php');
        $upgrade_controller = new UpgradeApplicationController(true);
        try {
            $upgrade_controller->runUpdate($this->test_web_dir);
            $this->fail("Should throw an exception...");
        } catch(Exception $e) {
            $this->assertPattern(
            '/ThinkUp can\'t upgrade itself because it doesn\'t have the right file permissions. To fix this problem/',
            $e->getMessage());
        }
        chmod( $this->test_web_dir.'/index.php', 0644 ); // make a file writeable again
    }

    public function testGetLatestInfo() {
        $upgrade_controller = new UpgradeApplicationController(true);
        $valid_url = AppUpgraderClient::$UPDATE_URL;

        error_reporting( E_ERROR | E_USER_ERROR ); // turn off warning messages

        // bad url, or bad response...
        AppUpgraderClient::$UPDATE_URL = '/badurl/nofile';
        try {
            $upgrade_controller->runUpdate($this->test_web_dir);
            $this->fail("Should throw an exception...");
        } catch(Exception $e) {
            $this->assertEqual('Unable to load latest version information from /badurl/nofile', $e->getMessage());
        }

        // bad json in response data
        AppUpgraderClient::$UPDATE_URL = THINKUP_ROOT_PATH . 'tests/data/update/bad_update_info.txt';
        try {
            $upgrade_controller->runUpdate($this->test_web_dir);
            $this->fail("Should throw an exception...");
        } catch(Exception $e) {
            $this->assertEqual('Invalid data received from update server: this is not valid json', $e->getMessage());
        }

        // we do not need to update
        AppUpgraderClient::$UPDATE_URL = THINKUP_ROOT_PATH . 'tests/data/update/old_update_info.txt';
        try {
            $upgrade_controller->runUpdate($this->test_web_dir);
            $this->fail("Should throw an exception...");
        } catch(Exception $e) {
            $this->assertEqual('You are running the latest version of ThinkUp.', $e->getMessage());
        }
        // reset
        AppUpgraderClient::$UPDATE_URL = $valid_url;
        error_reporting( E_STRICT ); // reset error reporting
    }

    public function testGetLatestUpdateFile() {
        $upgrade_controller = new UpgradeApplicationController(true);
        $valid_url = AppUpgraderClient::$UPDATE_URL;

        error_reporting( E_ERROR | E_USER_ERROR ); // turn off warning messages
        // we do need to update, but have an bad download url
        AppUpgraderClient::$UPDATE_URL = THINKUP_WEBAPP_PATH . '../tests/data/update/new_update_info_bad_url.txt';
        try {
            $upgrade_controller->runUpdate($this->test_web_dir);
            $this->fail("Should throw an exception...");
        } catch(Exception $e) {
            $this->assertEqual('Unable to download latest update file ./webapp/nothing.not', $e->getMessage());
        }

        // we do need to update, but have an bad zip file
        AppUpgraderClient::$UPDATE_URL = THINKUP_WEBAPP_PATH . '../tests/data/update/new_update_info_bad_zip2.txt';

        file_put_contents(AppUpgraderClient::$UPDATE_URL,
        '{"version":"100.1", "url":"'.THINKUP_WEBAPP_PATH.'_lib/controller/class.UpgradeApplicationController.php"}');
        try {
            $upgrade_controller->runUpdate($this->test_web_dir);
            $this->fail("Should throw an exception...");
        } catch(Exception $e) {
            $this->assertPattern('/Unable to extract/', $e->getMessage());
        }
        unlink(AppUpgraderClient::$UPDATE_URL);

        // reset
        AppUpgraderClient::$UPDATE_URL = $valid_url;
        error_reporting( E_STRICT ); // reset error reporting
    }

    public function testUpdate() {
        $upgrade_controller = new UpgradeApplicationController(true);
        $valid_url = AppUpgraderClient::$UPDATE_URL;
        error_reporting( E_ERROR | E_USER_ERROR ); // turn off warning messages

        $config = Config::getInstance();
        $proper_version = $config->getValue('THINKUP_VERSION');
        $config->setValue('THINKUP_VERSION', 1.0 ); //set a low version num

        // delete index.pho
        $this->assertTrue(unlink($this->test_web_dir . '/index.php'));
        // create a file in out data dir
        touch($this->test_web_dir . '/data/dont_delete_me');

        AppUpgraderClient::$UPDATE_URL = $this->test_web_dir . '/data/valid_json';
        file_put_contents(AppUpgraderClient::$UPDATE_URL,
        '{"version":"100.1", "url":"'.THINKUP_WEBAPP_PATH.'test_installer/thinkup.zip"}');

        $update_info = $upgrade_controller->runUpdate( $this->test_web_dir );
        $this->assertPattern('/data\/\d+\-v1\-config\.inc\.backup\.php/', $update_info['config']);
        $this->assertPattern('/data\/\d+\-v1\-backup\.zip/', $update_info['backup']);

        $this->assertTrue(file_exists($this->test_web_dir . '/index.php'), "we should have our index file back");
        $data_path = FileDataManager::getDataPath();
        $this->assertFalse(is_dir($data_path . '/data/thinkup'), "our unzipped update deleted");
        $this->assertTrue(file_exists($this->test_web_dir . '/data/dont_delete_me'), "/data/* not deleted");
        unlink(AppUpgraderClient::$UPDATE_URL);

        // reset
        AppUpgraderClient::$UPDATE_URL = $valid_url;
        error_reporting( E_STRICT ); // reset error reporting
        $config->setValue('THINKUP_VERSION', $proper_version);
    }
}
/**
 * Mock Controller for testing exceptions
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class MockUpgradeApplicationController extends UpgradeApplicationController {
    /**
     * @var mixed Test exception
     */
    static $current_exception = false;
    public function runUpdate($file_path, $verify_updatable = false) {
        if (self::$current_exception == true) {
            throw new Exception(self::$current_exception);
        } else {
            return array('backup' => '/bla/backup-zipfile.zip', 'config' => '/bla/config.inc.backup.php');
        }
    }
}