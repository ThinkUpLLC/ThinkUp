<?php
/**
 *
 * ThinkUp/tests/TestOfInstallerMySQLDAO.php
 *
 * Copyright (c) 2009-2010 Dwi Widiastuti, Gina Trapani, Guillaume Boudreau
 *
 * LICENSE:
 *
 * This file is part of ThinkUp.
 * 
 * ThinkUp is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * ThinkUp is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with ThinkUp.  If not, see <http://www.gnu.org/licenses/>.
 *
*/
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

/**
 * Test Of Installer MySQLDAO
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Dwi Widiastuti, Gina Trapani, Guillaume Boudreau
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class TestOfInstallerMySQLDAO extends ThinkUpUnitTestCase {

    public function __construct() {
        $this->UnitTestCase('InstallerMySQLDAO class test');
    }

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testConstructor() {
        $config = Config::getInstance();
        $config_array = $config->getValuesArray();
        $dao = new InstallerMySQLDAO($config_array);
        $this->assertIsA($dao, 'InstallerMySQLDAO');
    }

    public function testGetTables() {
        $config = Config::getInstance();
        $config_array = $config->getValuesArray();
        $dao = new InstallerMySQLDAO($config_array);
        $result = $dao->getTables();
        $this->assertEqual(sizeof($result), 13);
        $this->assertEqual($result[0], $config_array["table_prefix"].'encoded_locations');
    }
    public function testCheckTable() {
        $config = Config::getInstance();
        $config_array = $config->getValuesArray();
        $dao = new InstallerMySQLDAO($config_array);
        $result = $dao->checkTable($config_array["table_prefix"].'owners');
        $this->assertTrue(array_key_exists('Msg_text', $result));
    }

    public function testRepairTable() {
        $config = Config::getInstance();
        $config_array = $config->getValuesArray();
        $dao = new InstallerMySQLDAO($config_array);
        $result = $dao->repairTable($config_array["table_prefix"].'owners');
        $this->assertTrue(array_key_exists('Msg_text', $result));
    }

    public function testDescribeTable() {
        $config = Config::getInstance();
        $config_array = $config->getValuesArray();
        $dao = new InstallerMySQLDAO($config_array);
        $result = $dao->describeTable($config_array["table_prefix"].'owners');
        foreach ($result as $field) {
            $this->assertTrue(isset($field['Field']));
            $this->assertTrue(isset($field['Type']));
            $this->assertTrue(isset($field['Null']));
            $this->assertTrue(isset($field['Key']));
            $this->assertTrue(isset($field['Extra']));
        }
    }

    public function testShowIndex() {
        $config = Config::getInstance();
        $config_array = $config->getValuesArray();
        $dao = new InstallerMySQLDAO($config_array);
        $result = $dao->showIndex($config_array["table_prefix"].'owners');
        $this->assertIdentical("id", $result[0]['Column_name']);
        $this->assertIdentical("PRIMARY", $result[0]['Key_name']);
    }

    public function testExamineQueries() {
        // test on fully installed tables
        $install_queries = file_get_contents(THINKUP_ROOT_PATH."webapp/install/sql/build-db_mysql.sql");

        //clean SQL: diffDataStructure requires two spaces after PRIMARY KEY, and a space between key name and (field)
        $install_queries = str_replace('PRIMARY KEY ', 'PRIMARY KEY  ', $install_queries);

        // test on complete table set; this should return just the INSERT query into plugins table
        $config = Config::getInstance();
        $config_array = $config->getValuesArray();
        $dao = new InstallerMySQLDAO($config_array);
        $output = $dao->diffDataStructure($install_queries, $dao->getTables());
        $this->assertEqual(sizeof($output['for_update']), 0 );
        //var_dump($output);
        $expected = "/INSERT INTO ".$config_array["table_prefix"]."plugins/i";
        $this->assertPattern( $expected, $output['queries'][0] );

        // test on missing tables
        InstallerMySQLDAO::$PDO->exec("DROP TABLE " . $config_array["table_prefix"] . "owners");
        $output = $dao->diffDataStructure($install_queries, $dao->getTables());
        $expected = "/Created table {$config_array["table_prefix"]}owners/i";
        $this->assertPattern($expected, $output['for_update'][$config_array["table_prefix"] . 'owners']);
        $expected = "/CREATE TABLE {$config_array["table_prefix"]}owners /i";
        $this->assertPattern($expected, $output['queries'][$config_array["table_prefix"] . 'owners']);

        // test on missing PRIMARY KEY
        InstallerMySQLDAO::$PDO->exec("ALTER TABLE " . $config_array["table_prefix"] . "follows DROP KEY user_id");
        $tables = $dao->getTables();
        //var_dump($tables);
        $output = $dao->diffDataStructure($install_queries, $tables);
        $add_pk = "ALTER TABLE " . $config_array["table_prefix"] .
        "follows ADD UNIQUE KEY user_id (user_id,follower_id,network)";
        $this->assertTrue(in_array($add_pk, $output['queries']));

        // test on missing index
        InstallerMySQLDAO::$PDO->exec("ALTER TABLE ".$config_array["table_prefix"]."follows DROP INDEX active");
        $output = $dao->diffDataStructure($install_queries, $dao->getTables(false));
        $add_idx = "ALTER TABLE ".$config_array["table_prefix"]."follows ADD KEY active (active)";
        $this->assertTrue(in_array($add_idx, $output['queries']));

        // test on missing column
        InstallerMySQLDAO::$PDO->exec("ALTER TABLE ".$config_array["table_prefix"]."posts DROP place");
        $output = $dao->diffDataStructure($install_queries, $dao->getTables(false));
        $add_idx = "ALTER TABLE ".$config_array["table_prefix"]."posts ADD COLUMN place varchar(255) DEFAULT NULL";
        $this->assertTrue(in_array($add_idx, $output['queries']));
    }
}