<?php
/**
 *
 * ThinkUp/tests/TestOfBackupMySQLDAO.php
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

class TestOfBackupMySQLDAO extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
        new BackupMySQLDAO();
        $this->pdo = BackupMySQLDAO::$PDO;
    }

    public function tearDown() {
        parent::tearDown();
        $zipfile = FileDataManager::getBackupPath('.htthinkup_db_backup.zip');
        $backup_dir = FileDataManager::getBackupPath();
        if (file_exists($zipfile)) {
            unlink($zipfile);
        }
        if (file_exists($backup_dir)) {
            $this->recursiveDelete($backup_dir);
        }
    }

    /**
     * Test constructor
     */
    public function testConstructor() {
        $dao = new BackupMySQLDAO();
        $this->assertTrue(isset($dao));
    }

    /**
     * test export data
     */
    public function testExportData() {
        $dao = new BackupMySQLDAO();
        $export_file = $dao->export();
        $this->assertTrue( file_exists($export_file) );
        $zip_stats = stat($export_file);
        $this->assertTrue($zip_stats['size'] > 0);
        $za = new ZipArchive();
        $za->open($export_file);
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

    /**
     * test import data, no such file
     */
    public function testImportDataNoFile() {
        $dao = new BackupMySQLDAO();
        $export_file = $dao->export();
        $this->expectException('Exception', 'Unable to open import file: jshshsgsgs-badfile.nofile');
        $dao->import('jshshsgsgs-badfile.nofile');
    }

    /**
     * test import data, bad zip file
     */
    public function testImportDataBadFile() {
        $dao = new BackupMySQLDAO();
        $zipfile = 'tests/data/backup/bad-zip-archive.zip';
        $this->expectException('Exception', 'Unable to open import file, corrupted zip file?: ' . $zipfile);
        $dao->import($zipfile);
    }

    /**
     * test import data, good zip file, but data missing
     */
    public function testImportDataBadFile2() {
        $dao = new BackupMySQLDAO();
        $zipfile = 'tests/data/backup/bad-zip-archive2.zip';
        $this->expectException('Exception', 'Unable to open import file, corrupted zip file?: ' . $zipfile);
        $dao->import($zipfile);
    }

    /**
     * test import data
     */
    public function testImportData() {
        $dao = new BackupMySQLDAO();

        $stmt = $this->pdo->query("show tables");
        $data = $stmt->fetchAll();
        $pre_import = count($data);

        $export_file = $dao->export();

        $this->pdo->query("drop table " . $this->table_prefix . "plugins");
        $this->assertTrue( $dao->import($export_file) );
        $stmt = $this->pdo->query("show create table " . $this->table_prefix . "plugins");
        $data = $stmt->fetch();
        $stmt->closeCursor();
        $this->assertEqual($data['Table'], $this->table_prefix . 'plugins');

        $stmt = $this->pdo->query("select * from " . $this->table_prefix . "plugins");

        $data = $stmt->fetch();
        $this->assertEqual($data['id'], 1);
        $this->assertEqual($data['name'], 'Twitter');

        $stmt = $this->pdo->query("show tables");
        $data = $stmt->fetchAll();
        $post_import = count($data);
        $this->assertEqual($pre_import, $post_import);
    }

    /**
     * test import data, drop new tables not in backup
     */
    public function testImportDataDropNewTables() {
        $dao = new BackupMySQLDAO();

        $stmt = $this->pdo->query("show tables");
        $data = $stmt->fetchAll();
        $pre_import = count($data);

        $export_file = $dao->export();
        $this->pdo->query("drop table " . $this->table_prefix . "plugins");
        $this->pdo->query("create table tu_dropme (`value` int(11) NOT NULL)");
        $this->assertTrue( $dao->import($export_file) );
        $stmt = $this->pdo->query("show create table " . $this->table_prefix . "plugins");
        $data = $stmt->fetch();
        $stmt->closeCursor();
        $this->assertEqual($data['Table'], $this->table_prefix . 'plugins');

        $stmt = $this->pdo->query("show tables like '%dropme'");
        $data = $stmt->fetch();
        $this->assertFalse($data); // table should be dropped

        $stmt = $this->pdo->query("show tables");
        $data = $stmt->fetchAll();
        $post_import = count($data);
        $this->assertEqual($pre_import, $post_import);
    }

    public function recursiveDelete($str){
        if (is_file($str)){
            if (!preg_match("MAKETHISDIRWRITABLE", $str)) {
                return @unlink($str);
            } else {
                return true;
            }
        }
        elseif (is_dir($str)){
            $scan = glob(rtrim($str,'/').'/*');
            foreach($scan as $index=>$path){
                $this->recursiveDelete($path);
            }
            return @rmdir($str);
        }
    }
}