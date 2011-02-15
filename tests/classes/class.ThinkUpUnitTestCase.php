<?php
/**
 *
 * ThinkUp/tests/classes/class.ThinkUpUnitTestCase.php
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
 * ThinkUp Unit Test Case
 *
 * Adds database support to the basic unit test case, for tests that need ThinkUp's database structure.
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class ThinkUpUnitTestCase extends ThinkUpBasicUnitTestCase {

    const TEST_EMAIL = '/upgrade_test_email';

    var $db;
    var $conn;
    var $testdb_helper;

    /**
     * Create a clean copy of the ThinkUp database structure
     */
    public function setUp() {
        parent::setUp();
        require THINKUP_WEBAPP_PATH.'config.inc.php';

        global $TEST_DATABASE;

        //Override default CFG values
        $THINKUP_CFG['db_name'] = $TEST_DATABASE;
        $config = Config::getInstance();
        $config->setValue('db_name', $TEST_DATABASE);

        $this->db = new Database($THINKUP_CFG);
        $this->conn = $this->db->getConnection();

        $this->testdb_helper = new ThinkUpTestDatabaseHelper();
        $this->testdb_helper->drop($this->db);
        $this->testdb_helper->create($this->db);
    }

    /**
     * Drop the database and kill the connection
     */
    public function tearDown() {
        $this->testdb_helper->drop($this->db);
        $this->db->closeConnection($this->conn);
        parent::tearDown();
        // delete test email file if it exists
        $test_email = THINKUP_WEBAPP_PATH . '_lib/view/compiled_view' . self::TEST_EMAIL;
        if(file_exists($test_email)) {
            unlink($test_email);
        }
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
}

/**
 * Mock Mailer for test use
 */
class Mailer {
    public static function mail($to, $subject, $message) {
        $test_email = THINKUP_WEBAPP_PATH . '_lib/view/compiled_view' . ThinkUpUnitTestCase::TEST_EMAIL;
        $fp = fopen($test_email, 'w');
        fwrite($fp, "to: $to\n");
        fwrite($fp, "subject: $subject\n");
        fwrite($fp, "message: $message");
        fclose($fp);
        return $message;
    }

    public static function getLastMail() {
        return file_get_contents(THINKUP_WEBAPP_PATH . '_lib/view/compiled_view' . ThinkUpUnitTestCase::TEST_EMAIL);
    }
}