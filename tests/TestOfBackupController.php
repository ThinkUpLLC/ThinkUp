<?php
/**
 *
 * ThinkUp/tests/TestOfBackupController.php
 *
 * Copyright (c) 2009-2010 Mark Wilkie
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
 * @author Mark Wilkie <mark[at]bitterpill[dot]org>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Mark Wilkie
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfBackupController extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
        new BackupMySQLDAO();
        $this->config = Config::getInstance();
        $this->pdo = BackupMySQLDAO::$PDO;
        $this->backup_file = THINKUP_WEBAPP_PATH . BackupDAO::CACHE_DIR . '/thinkup_db_backup.zip';
        $this->backup_test = THINKUP_WEBAPP_PATH . BackupDAO::CACHE_DIR . '/thinkup_db_backup_test.zip';
        $this->backup_dir = THINKUP_WEBAPP_PATH . BackupDAO::CACHE_DIR . '/backup';
    }

    public function tearDown() {
        parent::tearDown();
        if(file_exists($this->backup_file)) {
            unlink($this->backup_file);
        }
        if(file_exists($this->backup_test)) {
            unlink($this->backup_test);
        }
        if(file_exists($this->backup_dir)) {
            unlink($this->backup_dir);
        }
    }

    public function __construct() {
        $this->UnitTestCase('BackupController class test');
    }

    public function testConstructor() {
        $controller = new BackupController(true);
        $this->assertTrue(isset($controller));
    }

    public function testNotLoggedIn() {
        $controller = new BackupController(true);
        $results = $controller->go();
        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('You must <a href="'.$config->getValue('site_root_path').
        'session/login.php">log in</a> to do this.', $v_mgr->getTemplateDataItem('errormsg'));
    }

    public function testNonAdminAccess() {
        $this->simulateLogin('me@example.com');
        $controller = new BackupController(true);
        $this->expectException('Exception', 'You must be a ThinkUp admin to do this');
        $results = $controller->control();
    }

    public function testLoadBackupView() {
        $this->simulateLogin('me@example.com', true);
        $controller = new BackupController(true);
        $results = $controller->control();
        $this->assertPattern('/Back Up Your ThinkUp Data/', $results);
    }

    public function XtestBackupCrawlerHasMutex() {
        // mutex needs to be on another db handle, so can't use doa framework to test to test
        $mutex_name = $this->config->getValue('db_name') . '.' . 'crawler';
        $result = $this->db->exec("SELECT GET_LOCK('$mutex_name', 1)");
        var_dump(mysql_fetch_assoc($result));
        $this->simulateLogin('me@example.com', true);
        $controller = new BackupController(true);
        $_GET['backup'] = 'true';
        ob_start();
        $controller->go();
        $results = ob_get_contents();
        ob_end_clean();
        echo $results;
        $this->db->exec("SELECT RELEASE_LOCK('$mutex_name')");
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
        $this->assertTrue($zip_files["create_tables.sql"]);
        $za->close();
        $q = "show tables";
        $q2 = "show create table ";
        $stmt = $this->pdo->query($q);
        // verify we have all table files
        while($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            foreach($data as $key => $value) {
                $zfile = '/' . $value .'.txt';
                $this->assertTrue($zip_files[$zfile]);
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
        $zipfile = 'tests/data/backup/bad-zip-archive.zip';
        $_FILES["backup_file"]["tmp_name"] = $zipfile;
        $_FILES['backup_file']["error"] = 0;
        $results = $controller->go();
        $this->assertPattern("/Unable to open import file, corrupted zip file/is", $results);

        // upload bad archive file,, valid zip file, but not the right data
        $zipfile = 'tests/data/backup/bad-zip-archive2.zip';
        $_FILES["backup_file"]["tmp_name"] = $zipfile;
        $_FILES['backup_file']["error"] = 0;
        $results = $controller->go();
        $this->assertPattern("/Unable to open import file, corrupted zip file/is", $results);

    }

    public function testResore() {
        // create export
        $dao = new BackupMySQLDAO();
        $export_file = $dao->export();

        $this->pdo->query("drop table tu_plugins");


        $this->simulateLogin('me@example.com', true);
        $controller = new BackupController(true);

        $_FILES['backup_file']['name'] = "name";
        $_FILES['backup_file']['type'] = "application/zip";
        $_FILES['backup_file']["error"] = 0;
        $_FILES["backup_file"]["tmp_name"] = $export_file;
        $results = $controller->go();
        $this->assertPattern("/Data Import Successfull/is", $results);

        $stmt = $this->pdo->query("show create table tu_plugins");
        $data = $stmt->fetch();
        $stmt->closeCursor();
        $this->assertEqual($data['Table'], 'tu_plugins');

        $stmt = $this->pdo->query("select * from tu_plugins");

        $data = $stmt->fetch();
        $this->assertEqual($data['id'], 1);
        $this->assertEqual($data['name'], 'Twitter');
    }
}
