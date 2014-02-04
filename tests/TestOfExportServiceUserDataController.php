<?php
/**
 *
 * ThinkUp/tests/TestOfExportServiceUserDataController.php
 *
 * Copyright (c) 2011-2013 Gina Trapani
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
 * @copyright 2011-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';
if (!class_exists('ExportDAO')) {
    require_once THINKUP_WEBAPP_PATH.'_lib/dao/interface.ExportDAO.php';
}
require_once THINKUP_WEBAPP_PATH.'_lib/dao/class.ExportMySQLDAO.php';

class TestOfExportServiceUserDataController extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
        new ExportMySQLDAO();
        $this->config = Config::getInstance();
        $this->pdo = ExportMySQLDAO::$PDO;
        $this->export_test = FileDataManager::getDataPath('thinkup_user_export_test.zip');

        $hashed_pass = ThinkUpTestLoginHelper::hashPasswordUsingDeprecatedMethod("secretpassword");

        $owner = array('id'=>1, 'email'=>'me@example.com', 'pwd'=>$hashed_pass, 'is_activated'=>1, 'is_admin'=>1,
        'pwd_salt'=>OwnerMySQLDAO::$default_salt);
        $this->builders[] = FixtureBuilder::build('owners', $owner);

        $instance = array('id'=>1, 'network_username'=>'test_user', 'network'=>'twitter');
        $this->builders[] = FixtureBuilder::build('instances', $instance);

        $owner_instance = array('owner_id'=>1, 'instance_id'=>1);
        $this->builders[] = FixtureBuilder::build('owner_instances', $owner_instance);

        $this->builders[] = FixtureBuilder::build('users', array('user_id'=>10, 'network'=>'twitter',
        'user_name'=>'test_user'));
    }

    public function tearDown() {
        if (file_exists($this->export_test)) {
            unlink($this->export_test);
        }
        self::deleteFile('posts.tmp');
        self::deleteFile('links.tmp');
        self::deleteFile('encoded_locations.tmp');
        self::deleteFile('favorites.tmp');
        self::deleteFile('follows.tmp');
        self::deleteFile('follower_count.tmp');
        self::deleteFile('users_from_posts.tmp');
        self::deleteFile('users_followees.tmp');
        self::deleteFile('users_followers.tmp');
        //set zip class requirement class name back
        BackupController::$zip_class_req = 'ZipArchive';

        $this->builders = null;

        parent::tearDown();
    }

    private function deleteFile($file) {
        $file = FileDataManager::getBackupPath($file);
        if (file_exists($file)) {
            unlink($file);
        }
    }

    public function testConstructor() {
        $controller = new ExportServiceUserDataController(true);
        $this->assertTrue(isset($controller));
    }

    public function testNotLoggedIn() {
        $controller = new ExportServiceUserDataController(true);
        $results = $controller->go();
        $this->assertPattern( '/session\/login.php\?redirect\=/', $controller->redirect_destination);
    }

    public function testNonAdminAccess() {
        $this->simulateLogin('me@example.com');
        $controller = new ExportServiceUserDataController(true);
        $this->expectException('Exception', 'You must be a ThinkUp admin to do this');
        $results = $controller->control();
    }

    public function testNoZipSupport() {
        BackupController::$zip_class_req = 'NoSuchZipArchiveClass';
        $this->simulateLogin('me@example.com', true);
        $controller = new ExportServiceUserDataController(true);
        $results = $controller->control();
        $this->assertPattern('/setup doesn\'t support/', $results);
    }

    public function testLoadExportView() {
        $this->simulateLogin('me@example.com', true);
        $controller = new ExportServiceUserDataController(true);
        $results = $controller->control();
        $this->assertPattern('/Export User Data/', $results);
    }

    public function testExport() {
        $this->simulateLogin('me@example.com', true);
        $controller = new ExportServiceUserDataController(true);
        $_POST['instance_id'] = 1;
        ob_start();
        $controller->go();
        $results = ob_get_contents();
        ob_end_clean();

        // write downloaded zip file to disk...
        $fh = fopen($this->export_test, 'wb');
        fwrite($fh, $results);
        fclose($fh);

        // verify contents of zip file...
        $za = new ZipArchive();
        $za->open($this->export_test);
        $zip_files = array();
        for ($i=0; $i<$za->numFiles;$i++) {
            $zfile = $za->statIndex($i);
            $zip_files[$zfile['name']] = $zfile['name'];
        }

        //verify we have create table file
        $this->assertTrue(isset($zip_files["/README.txt"]));
        $this->assertTrue(isset($zip_files["/posts.tmp"]));
        $this->assertTrue(isset($zip_files["/links.tmp"]));
        $this->assertTrue(isset($zip_files["/users_from_posts.tmp"]));
        $this->assertTrue(isset($zip_files["/follows.tmp"]));
        $this->assertTrue(isset($zip_files["/encoded_locations.tmp"]));
        $this->assertTrue(isset($zip_files["/favorites.tmp"]));
        $za->close();
    }

    public function testMySQLErrors() {
        $this->simulateLogin('me@example.com', true);
        // backup of DAO mapping
        $dao_mapping_backup = DAOFactory::$dao_mapping['ExportDAO'];

        DAOFactory::$dao_mapping['ExportDAO']['mysql'] = 'TestExportDAOFileFail';
        $controller = new ExportServiceUserDataController(true);
        $_POST['instance_id'] = 1;
        $results = $controller->go();
        $this->assertPattern("/MySQL user does not have the proper file permissions/", $results);

        DAOFactory::$dao_mapping['ExportDAO']['mysql'] = 'TestExportDAOGrantFail';
        $controller = new ExportServiceUserDataController(true);
        $_POST['instance_id'] = 1;
        $results = $controller->go();
        $this->assertPattern("/MySQL user does not have the proper permissions to/", $results);

        DAOFactory::$dao_mapping['ExportDAO']['mysql'] = $dao_mapping_backup;
    }
}


/**
 * a mock ExportDAO to test file error
 */
class TestExportDAOFileFail {

    public function dropExportedPostsTable($backup_file = null) {
        throw new Exception("Can't get stat of file /foo");
    }
}

/**
 * a mock ExportDAO to test grant error
 */
class TestExportDAOGrantFail {
    public function dropExportedPostsTable($backup_file = null) {
        throw new Exception("MySQL does not have GRANT FILE ON permissions to write to: /bla");
    }
}
