<?php
/**
 *
 * ThinkUp/tests/TestOfBackupController.php
 *
 * Copyright (c) 2009-2013 Mark Wilkie
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
 * @author Mark Wilkie <mark[at]bitterpill[dot]org>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Mark Wilkie
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';
if (!class_exists('BackupDAO')) {
    require_once THINKUP_WEBAPP_PATH.'_lib/dao/interface.BackupDAO.php';
}

class TestOfBackupController extends ThinkUpUnitTestCase {
    public function setUp() {
        parent::setUp();
        new BackupMySQLDAO();
        $this->config = Config::getInstance();
        $this->pdo = BackupMySQLDAO::$PDO;
        $this->backup_file = FileDataManager::getDataPath('.htthinkup_db_backup.zip');
        $this->backup_test = FileDataManager::getDataPath('thinkup_db_backup_test.zip');
        $this->backup_dir = FileDataManager::getBackupPath() . '/';
    }

    public function tearDown() {
        parent::tearDown();
        if (file_exists($this->backup_file)) {
            unlink($this->backup_file);
        }
        if (file_exists($this->backup_test)) {
            unlink($this->backup_test);
        }
        if (file_exists($this->backup_dir)) {
            rmdir($this->backup_dir);
        }

        //set zip class requirement class name back
        BackupController::$zip_class_req = 'ZipArchive';
    }

    public function testConstructor() {
        $controller = new BackupController(true);
        $this->assertTrue(isset($controller));
    }

    public function testNotLoggedIn() {
        $controller = new BackupController(true);
        $results = $controller->go();
        $this->assertPattern( '/session\/login.php\?redirect\=/', $controller->redirect_destination);
    }

    public function testNonAdminAccess() {
        $this->simulateLogin('me@example.com');
        $controller = new BackupController(true);
        $this->expectException('Exception', 'You must be a ThinkUp admin to do this');
        $results = $controller->control();
    }

    public function testNoZipSupport() {
        BackupController::$zip_class_req = 'NoSuchZipArchiveClass';
        $this->simulateLogin('me@example.com', true);
        $controller = new BackupController(true);
        $results = $controller->control();
        $this->assertPattern('/setup does not support/', $results);
    }

    public function testLoadBackupView() {
        $this->simulateLogin('me@example.com', true);
        $controller = new BackupController(true);
        $results = $controller->control();
        $this->assertPattern('/Back up ThinkUp\'s entire database/', $results);
    }

    public function testLoadBackupViewCLIWarn() {
        $this->simulateLogin('me@example.com', true);
        $controller = new BackupController(true);
        $results = $controller->control();
        $this->assertPattern('/Back up ThinkUp\'s entire database/', $results);
        $v_mgr = $controller->getViewManager();
        $this->assertNull($v_mgr->getTemplateDataItem('high_table_row_count') ) ;

        // table row counts are bad
        $old_count = UpgradeDatabaseController::$WARN_TABLE_ROW_COUNT;
        UpgradeDatabaseController::$WARN_TABLE_ROW_COUNT = 2;
        $results = $controller->control();
        $this->assertPattern('/we recommend that you use the/', $results);
        $table_counts = $v_mgr->getTemplateDataItem('high_table_row_count');
        $this->assertNotNull($table_counts);
        $this->assertNotNull(3, $table_counts['count']); // tu_plugins, defaults to three
        UpgradeDatabaseController::$WARN_TABLE_ROW_COUNT = $old_count;
    }

    public function XtestBackupCrawlerHasMutex() {
        // mutex needs to be on another db handle, so can't use doa framework to test
        $mutex_name = $this->config->getValue('db_name') . '.' . 'crawler';
        $result = $this->testdb_helper->runSQL("SELECT GET_LOCK('$mutex_name', 1)");
        var_dump(mysql_fetch_assoc($result));
        $this->simulateLogin('me@example.com', true);
        $controller = new BackupController(true);
        $_GET['backup'] = 'true';
        ob_start();
        $controller->go();
        $results = ob_get_contents();
        ob_end_clean();
        echo $results;
        $this->testdb_helper->runSQL("SELECT RELEASE_LOCK('$mutex_name')");
    }

    public function testBackup() {
        $this->simulateLogin('me@example.com', true);
        $controller = new BackupController(true);
        $_GET['backup'] = 'true';
        ob_start();
        $controller->go();
        $results = ob_get_contents();
        ob_end_clean();
        // write downloaded zip file to disk...
        $fh = fopen($this->backup_test, 'wb');
        fwrite($fh, $results);
        fclose($fh);

        // verify contents of zip file...
        $za = new ZipArchive();
        $za->open($this->backup_test);
        $zip_files = array();
        for ($i=0; $i<$za->numFiles;$i++) {
            $zfile = $za->statIndex($i);
            $zip_files[$zfile['name']] = $zfile['name'];
        }
        //verify we have create table file
        $this->assertTrue(isset($zip_files["create_tables.sql"]));
        $za->close();
        $q = "show tables";
        $q2 = "show create table ";
        $stmt = $this->pdo->query($q);
        // verify we have all table files

        while($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            foreach($data as $key => $value) {
                $zfile = '/' . $value .'.txt';
                $this->assertTrue(isset($zip_files[$zfile]));
            }
        }
    }

    public function testBadRestore() {
        $this->simulateLogin('me@example.com', true);
        $controller = new BackupController(true);

        // upload fails...to large
        $_FILES['backup_file']['name'] = "name";
        $_FILES['backup_file']['type'] = "application/zip";
        $_FILES['backup_file']["error"] = UPLOAD_ERR_INI_SIZE;
        $results = $controller->go();
        $this->assertPattern("/The file is too large/", $results);

        // upload fails...no file uploaded
        $_FILES['backup_file']['name'] = "name";
        $_FILES['backup_file']['type'] = "application/zip";
        $_FILES['backup_file']["error"] = UPLOAD_ERR_NO_FILE;
        $results = $controller->go();
        $this->assertPattern("/No file uploaded. Please select a backup file to upload/", $results);

        // upload fails...
        $_FILES['backup_file']['name'] = "name";
        $_FILES['backup_file']['type'] = "application/zip";
        $_FILES['backup_file']["error"] = UPLOAD_ERR_INI_SIZE;
        $results = $controller->go();
        $this->assertPattern("/The file is too large/", $results);

        $_FILES['backup_file']["error"] = UPLOAD_ERR_INI_SIZE;
        $results = $controller->go();
        $this->assertPattern("/Backup file upload failed./", $results);

        // upload bad archive file
        $zipfile = dirname(__FILE__) . '/data/backup/bad-zip-archive.zip';
        $_FILES["backup_file"]["tmp_name"] = $zipfile;
        $_FILES['backup_file']["error"] = 0;
        $results = $controller->go();
        $this->assertPattern("/Unable to open import file, corrupted zip file/is", $results);

        // upload bad archive file,, valid zip file, but not the right data
        $zipfile = dirname(__FILE__) . '/data/backup/bad-zip-archive2.zip';
        $_FILES["backup_file"]["tmp_name"] = $zipfile;
        $_FILES['backup_file']["error"] = 0;
        $results = $controller->go();
        $this->assertPattern("/Unable to open import file, corrupted zip file/is", $results);
    }

    public function testRestore() {
        // create export
        $dao = new BackupMySQLDAO();
        $export_file = $dao->export();
        $config = Config::getInstance();
        $table_prefix = $config->getValue('table_prefix');

        $this->pdo->query("drop table " . $table_prefix . "plugins");

        $this->simulateLogin('me@example.com', true);
        $controller = new BackupController(true);

        $_FILES['backup_file']['name'] = "name";
        $_FILES['backup_file']['type'] = "application/zip";
        $_FILES['backup_file']["error"] = 0;
        $_FILES["backup_file"]["tmp_name"] = $export_file;
        $results = $controller->go();
        $this->assertPattern("/Data Import Successfull/is", $results);

        $stmt = $this->pdo->query("show create table " . $table_prefix . "plugins");
        $data = $stmt->fetch();
        $stmt->closeCursor();
        $this->assertEqual($data['Table'], $table_prefix . 'plugins');

        $stmt = $this->pdo->query("select * from " . $table_prefix . "plugins");

        $data = $stmt->fetch();
        $this->assertEqual($data['id'], 1);
        $this->assertEqual($data['name'], 'Twitter');
    }

    public function testMySQLExportFails() {
        // backup DAO mapping
        $dao_mapping_backup = DAOFactory::$dao_mapping['BackupDAO'];

        $this->simulateLogin('me@example.com', true);
        $controller = new BackupController(true);
        $_GET['backup'] = 'true';

        // no grant perms
        DAOFactory::$dao_mapping['BackupDAO']['mysql'] = 'TestBackupDAOGrantFail';
        $results = $controller->go();
        $this->assertPattern("/It looks like the MySQL user does not have the proper permissions to/", $results);

        // no file perms
        $controller = new BackupController(true);
        DAOFactory::$dao_mapping['BackupDAO']['mysql'] = 'TestBackupDAOFileFail';
        $results = $controller->go();
        $this->assertPattern("/It looks like the MySQL user does not have the proper file permissions/", $results);

        // restore DAO mapping
        DAOFactory::$dao_mapping['BackupDAO']['mysql'] = $dao_mapping_backup;
    }
}

/**
 * a mock BackupDAO to test grant error
 */
class TestBackupDAOGrantFail implements BackupDAO {
    public function import($zipfile) {
        // does nothing
    }
    public function export($backup_file = null) {
        throw new MySQLGrantException("MySQL does not have GRANT FILE ON permissions to write to: /bla");
    }
}

/**
 * a mock BackupDAO to test file error
 */
class TestBackupDAOFileFail implements BackupDAO {
    public function import($zipfile) {
        // does nothing
    }
    public function export($backup_file = null) {
        throw new OpenFileException("MySQL does not have permissions to write to: /bla");
    }
}
