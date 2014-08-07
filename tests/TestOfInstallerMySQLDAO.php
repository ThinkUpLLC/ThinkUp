<?php
/**
 *
 * ThinkUp/tests/TestOfInstallerMySQLDAO.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Mark Wilkie
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
 * Test Of Installer MySQLDAO
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Guillaume Boudreau
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_WEBAPP_PATH.'_lib/extlib/simpletest/autorun.php';
require_once THINKUP_WEBAPP_PATH.'config.inc.php';

class TestOfInstallerMySQLDAO extends ThinkUpUnitTestCase {

    public function setUp() {
        parent::setUp();
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testCreateDatabase() {
        $config = Config::getInstance();
        $config_array = $config->getValuesArray();
        $dao = new InstallerMySQLDAO($config_array);

        //test a hairy database name with dashes to assert escaping is happening correctly
        $config_array['db_name'] = 'thinkup_db_creation-test_db-yes';

        //Check that the database exists before trying to create it.
        $before = $this->testdb_helper->databaseExists($config_array['db_name']);
        $this->assertFalse($before);

        //Create the database. True on success, false on fail.
        //Destroy the current correct config, replace with the test db name array in the createInstallDatabase function
        Config::destroyInstance();
        $create_db = $dao->createInstallDatabase($config_array);

        //Check the database exists after creation.
        Config::destroyInstance();
        $after = $this->testdb_helper->databaseExists($config_array['db_name']);
        $this->assertTrue($create_db);
        $this->assertTrue($after);

        //Delete the database
        $this->testdb_helper->deleteDatabase($config_array['db_name']);
        //Assert it no longer exists
        $after = $this->testdb_helper->databaseExists($config_array['db_name']);
        $this->assertFalse($after);
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
        $this->assertEqual(sizeof($result), 36);
        $this->assertEqual($result[0], $config_array["table_prefix"].'cookies');
        $this->assertEqual($result[1], $config_array["table_prefix"].'count_history');
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
        $install_queries = file_get_contents(THINKUP_ROOT_PATH.
        "webapp/install/sql/build-db_mysql-upcoming-release.sql");

        $this->debug(Utils::varDumpToString($install_queries));

        //clean SQL: diffDataStructure requires two spaces after PRIMARY KEY, and a space between key name and (field)
        $install_queries = str_replace('PRIMARY KEY (', 'PRIMARY KEY  (', $install_queries);

        // test on complete table set; this should return just the INSERT query into plugins table
        $config = Config::getInstance();
        $config_array = $config->getValuesArray();
        if ($config_array['table_prefix'] != 'tu_') {
            $install_queries = str_replace('tu_', $config_array['table_prefix'], $install_queries);
        }
        $dao = new InstallerMySQLDAO($config_array);
        $output = $dao->diffDataStructure($install_queries, $dao->getTables());

        $this->assertEqual(sizeof($output['for_update']), 0 );
        //var_dump($output);
        //$this->debug(Utils::varDumpToString($output));
        $expected = "/INSERT INTO ".$config_array["table_prefix"]."options/i";
        $this->assertPattern( $expected, $output['queries'][0] );

        $expected = "/INSERT INTO ".$config_array["table_prefix"]."plugins/i";
        $this->assertPattern( $expected, $output['queries'][1] );

        // test on missing tables
        InstallerMySQLDAO::$PDO->exec("DROP TABLE " . $config_array["table_prefix"] . "owners");
        $output = $dao->diffDataStructure($install_queries, $dao->getTables());
        $expected = "/Created table {$config_array["table_prefix"]}owners/i";
        $this->assertPattern($expected, $output['for_update'][$config_array["table_prefix"] . 'owners']);
        $expected = "/CREATE TABLE {$config_array["table_prefix"]}owners /i";
        $this->assertPattern($expected, $output['queries'][$config_array["table_prefix"] . 'owners']);

        // test on missing PRIMARY KEY
        InstallerMySQLDAO::$PDO->exec("ALTER TABLE " . $config_array["table_prefix"] .
        "follows DROP KEY network_follower_user");
        $tables = $dao->getTables();
        //var_dump($tables);
        $output = $dao->diffDataStructure($install_queries, $tables);
        $add_pk = "ALTER TABLE " . $config_array["table_prefix"] .
        "follows ADD UNIQUE KEY network_follower_user (network,follower_id,user_id)";
        $this->assertTrue(in_array($add_pk, $output['queries']));

        // test on missing index
        InstallerMySQLDAO::$PDO->exec("ALTER TABLE ".$config_array["table_prefix"]."follows DROP INDEX active");
        $output = $dao->diffDataStructure($install_queries, $dao->getTables(false));
        $add_idx = "ALTER TABLE ".$config_array["table_prefix"]."follows ADD KEY active (network,active,last_seen)";
        $this->assertTrue(in_array($add_idx, $output['queries']));

        // test on missing column
        InstallerMySQLDAO::$PDO->exec("ALTER TABLE ".$config_array["table_prefix"]."posts DROP place");
        $output = $dao->diffDataStructure($install_queries, $dao->getTables(false));
        $regex = "/ALTER TABLE ".$config_array["table_prefix"]."posts ADD COLUMN place varchar\(255\) DEFAULT NULL/i";
        $this->assertPattern($regex, $output['queries'][2]);
    }

    public function testNeedsSnowflakeUpgrade() {
        $dao = new InstallerMySQLDAO();
        $this->assertFalse($dao->needsSnowflakeUpgrade());
        $this->testdb_helper->runSQL('ALTER TABLE #prefix#posts CHANGE post_id post_id bigint(11) NOT NULL;');
        $this->assertTrue($dao->needsSnowflakeUpgrade());
    }

    public function testRunMigration() {
        $config = Config::getInstance();
        $config_array = $config->getValuesArray();
        $dao = new InstallerMySQLDAO();
        $this->testdb_helper->runSQL('CREATE TABLE `tu_test2` (`value` int(11) NOT NULL)');

        //errors
        try {
            $dao->runMigrationSQL('bad sql');
            $this->fail('should throw exception');
        } catch (Exception $e) {
            $this->assertTrue(true, 'should throw exception');
        }
        try {
            $dao->runMigrationSQL('insert into tu_test2 (valuee) VALUES ("a")');
            $this->fail('should throw exception');
        } catch (Exception $e) {
            $this->assertTrue(true, 'should throw exception');
        }

        // old migration
        $dao->runMigrationSQL('insert into tu_test2 (value) VALUES (1)');
        $stmt = InstallerMySQLDAO::$PDO->query('select * from tu_test2');
        $data = $stmt->fetchAll();
        $this->assertEqual(1, $data[0]['value']);

        //old migration multiple queries
        $dao->runMigrationSQL("delete from tu_test2;" .
        "insert into tu_test2 (value) VALUES (2);" .
        "insert into tu_test2 (value) VALUES (3)");

        $stmt = InstallerMySQLDAO::$PDO->query('select * from tu_test2');
        $data = $stmt->fetchAll();
        $this->assertEqual(2, $data[0]['value']);
        $this->assertEqual(3, $data[1]['value']);
    }

    public function testRunNewMigrationNoTable() {
        $config = Config::getInstance();
        $config_array = $config->getValuesArray();
        $dao = new InstallerMySQLDAO();
        $this->testdb_helper->runSQL('CREATE TABLE `tu_test2` (`value` int(11) NOT NULL)');
        $this->testdb_helper->runSQL('DROP TABLE IF EXISTS #prefix#completed_migrations');

        $dao->runMigrationSQL("insert into tu_test2 (value) VALUES (2);", true, $filename = 'a_file');

        // tu_completed_migrations table should now exists
        $stmt = InstallerMySQLDAO::$PDO->query("SHOW TABLES LIKE '" . $config_array['table_prefix'] .
        "completed_migrations'");
        $data = $stmt->fetchAll();
        $this->assertEqual(1, count($data));
        $this->assertEqual($data[0][0], $config_array['table_prefix'] . "completed_migrations");

        // migration should have run
        $stmt = InstallerMySQLDAO::$PDO->query("select * from tu_test2");
        $data = $stmt->fetchAll();
        $this->assertEqual(2, $data[0]['value']);

        // tu_completed_migrations table should contan a record for our latest migration
        $stmt = InstallerMySQLDAO::$PDO->query("select * from " . $config_array['table_prefix'] .
        "completed_migrations");
        $data = $stmt->fetchAll();
        $this->assertEqual($data[0]['migration'], 'a_file-0');
    }

    public function testRunNewMigrationsTwice() {
        $config = Config::getInstance();
        $config_array = $config->getValuesArray();
        $dao = new InstallerMySQLDAO();
        $this->testdb_helper->runSQL('CREATE TABLE `tu_test2` (`value` int(11) NOT NULL)');
        $this->testdb_helper->runSQL('DROP TABLE IF EXISTS #prefix#completed_migrations');

        $dao->runMigrationSQL("insert into tu_test2 (value) VALUES (2);", true, $filename = 'a_file');

        // first migration should have run
        $stmt = InstallerMySQLDAO::$PDO->query("select * from tu_test2");
        $data = $stmt->fetchAll();
        $this->assertEqual(count($data), 1);
        $this->assertEqual(2, $data[0]['value']);

        // tu_completed_migrations table should contain a record for our latest migration
        $stmt = InstallerMySQLDAO::$PDO->query("select * from " . $config_array['table_prefix'] .
        "completed_migrations");
        $data = $stmt->fetchAll();
        $this->assertEqual(count($data), 1);
        $this->assertEqual($data[0]['migration'], 'a_file-0');

        $dao->runMigrationSQL("insert into tu_test2 (value) VALUES (2);" .
        "insert into tu_test2 (value) VALUES (3);" .
        "insert into tu_test2 (value) VALUES (4);",
        true, $filename = 'a_file');

        // migration should have run
        $stmt = InstallerMySQLDAO::$PDO->query("select * from tu_test2");
        $data = $stmt->fetchAll();
        $this->assertEqual(count($data), 3);
        $this->assertEqual(2, $data[0]['value']);
        $this->assertEqual(3, $data[1]['value']);
        $this->assertEqual(4, $data[2]['value']);

        // tu_completed_migrations table should contain a record for our latest migration
        $stmt = InstallerMySQLDAO::$PDO->query("select * from " . $config_array['table_prefix'] .
        "completed_migrations");
        $data = $stmt->fetchAll();
        $this->assertEqual(count($data), 3);
        $this->assertEqual($data[0]['migration'], 'a_file-0');
        $this->assertEqual($data[1]['migration'], 'a_file-1');
        $this->assertEqual($data[2]['migration'], 'a_file-2');
    }

    public function testRunNewMigrationsSkipIfExists() {
        $config = Config::getInstance();
        $config_array = $config->getValuesArray();
        $dao = new InstallerMySQLDAO();
        $this->testdb_helper->runSQL('CREATE TABLE `tu_test2` (`value` int(11) NOT NULL)');
        $this->testdb_helper->runSQL('DROP TABLE IF EXISTS #prefix#completed_migrations');

        $dao->runMigrationSQL("insert into tu_test2 (value) VALUES (2);" .
        "insert into tu_test2 (value) VALUES (3);" .
        "DROP TABLE IF EXISTS tu_users_b16;" .
        "insert into tu_test2 (value) VALUES (4);" .
        "insert into tu_test2 (value) VALUES (5);",
        true, $filename = 'a_file');

        // migration should have run
        $stmt = InstallerMySQLDAO::$PDO->query("select * from tu_test2");
        $data = $stmt->fetchAll();
        $this->assertEqual(count($data), 4);
        $this->assertEqual(2, $data[0]['value']);
        $this->assertEqual(3, $data[1]['value']);
        $this->assertEqual(4, $data[2]['value']);
        $this->assertEqual(5, $data[3]['value']);

        // tu_completed_migrations table should contain a record for our latest migration
        $stmt = InstallerMySQLDAO::$PDO->query("select * from " . $config_array['table_prefix'] .
        "completed_migrations");
        $data = $stmt->fetchAll();
        $this->assertEqual(count($data), 4);
        $this->assertEqual($data[0]['migration'], 'a_file-0');
        $this->assertEqual($data[1]['migration'], 'a_file-1');
        $this->assertEqual($data[2]['migration'], 'a_file-2');
        $this->assertEqual($data[3]['migration'], 'a_file-3');
    }

    public function testRunNewMigrationStripsVersionForStorage() {
        $config = Config::getInstance();
        $config_array = $config->getValuesArray();
        $dao = new InstallerMySQLDAO();
        $this->testdb_helper->runSQL('CREATE TABLE `tu_test2` (`value` int(11) NOT NULL)');
        $this->testdb_helper->runSQL('DROP TABLE IF EXISTS #prefix#completed_migrations');

        $dao->runMigrationSQL("insert into tu_test2 (value) VALUES (2);" .
        "insert into tu_test2 (value) VALUES (3);" .
        "DROP TABLE IF EXISTS tu_users_b16;" .
        "insert into tu_test2 (value) VALUES (4);" .
        "insert into tu_test2 (value) VALUES (5);",
        true, $filename = 'a_file_v0.13.sql');

        // migration should have run
        $stmt = InstallerMySQLDAO::$PDO->query("select * from tu_test2");
        $data = $stmt->fetchAll();
        $this->assertEqual(count($data), 4);
        $this->assertEqual(2, $data[0]['value']);
        $this->assertEqual(3, $data[1]['value']);
        $this->assertEqual(4, $data[2]['value']);
        $this->assertEqual(5, $data[3]['value']);

        // tu_completed_migrations table should contain a record for our latest migration
        $stmt = InstallerMySQLDAO::$PDO->query("select * from " . $config_array['table_prefix'] .
        "completed_migrations");
        $data = $stmt->fetchAll();
        $this->assertEqual(count($data), 4);
        $this->assertEqual($data[0]['migration'], 'a_file-0');
        $this->assertEqual($data[1]['migration'], 'a_file-1');
        $this->assertEqual($data[2]['migration'], 'a_file-2');
        $this->assertEqual($data[3]['migration'], 'a_file-3');
    }
}
