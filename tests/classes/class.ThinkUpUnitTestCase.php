<?php
/**
 *
 * ThinkUp/tests/classes/class.ThinkUpUnitTestCase.php
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
 * ThinkUp Unit Test Case
 *
 * Adds database support to the basic unit test case, for tests that need ThinkUp's database structure.
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class ThinkUpUnitTestCase extends ThinkUpBasicUnitTestCase {
    /**
     * @var ThinkUpTestDatabaseHelper
     */
    var $testdb_helper;
    /**
     * @var str
     */
    var $test_database_name;
    /**
     * @var str
     */
    var $table_prefix;
    /**
     * Create a clean copy of the ThinkUp database structure
     */
    public function setUp() {
        parent::setUp();
        require THINKUP_WEBAPP_PATH.'config.inc.php';
        require THINKUP_ROOT_PATH .'tests/config.tests.inc.php';
        $this->test_database_name = $TEST_DATABASE;

        $config = Config::getInstance();

        if (!self::ramDiskTestMode() ) {
            //Override default CFG values
            $THINKUP_CFG['db_name'] = $this->test_database_name;
            $config->setValue('db_name', $this->test_database_name);
        } else {
            $this->test_database_name = $THINKUP_CFG['db_name'];
        }
        $config->setValue('mandrill_api_key', '');

        $this->testdb_helper = new ThinkUpTestDatabaseHelper();
        $this->testdb_helper->drop($this->test_database_name);
        $this->table_prefix = $config->getValue('table_prefix');
        $this->testdb_helper->create($THINKUP_CFG['source_root_path'].
        "/webapp/install/sql/build-db_mysql-upcoming-release.sql");
        //If Travis is inexplicably dying, comment this in to see what test is
        /*
        if (getenv('TRAVIS') == 'true') {
            $trace = debug_backtrace();
            $caller = array_shift($trace);
            print $caller['file'].'
    ';
        }
        */
    }
    /**
     * Drop the database and kill the connection
     */
    public function tearDown() {
        if (isset(ThinkUpTestDatabaseHelper::$PDO)) {
            ThinkUpTestDatabaseHelper::$PDO->exec('SET SESSION sql_mode = "";');
            $this->testdb_helper->drop($this->test_database_name);
        }
        parent::tearDown();
    }
    /**
     * Returns an xml/xhtml document element by id
     * @param $doc an xml/xhtml document pobject
     * @param $id element id
     * @return Element
     */
    public function getElementById($doc, $id) {
        $xpath = new DOMXPath($doc);
        return $xpath->query("//*[@id='$id']")->item(0);
    }
    /**
     * Check if we in RAM disk test mode
     * @return bool
     */
    public static function ramDiskTestMode() {
        if (getenv("RD_MODE")=="1") {
            return true;
        }
        return false;
    }
    /**
     * Check if the MySQL server can set its timezone
     * @return bool
     */
    protected function isTimeZoneSupported() {
        $testdao = DAOFactory::getDAO('TestDAO');
        try {
            TestMySQLDAO::$PDO->exec("SET time_zone = 'UTC'");
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
