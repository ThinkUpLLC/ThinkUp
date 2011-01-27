<?php
/**
 *
 * ThinkUp/tests/classes/class.ThinkUpTestDatabaseHelper.php
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
 * ThinkUp Database Helper
 *
 * Constructs and destructs the ThinkUp data structure for testing purposes.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class ThinkUpTestDatabaseHelper {

    /**
     * Create ThinkUp tables
     *
     * @TODO: Use PDO instead of deprecated Database class.
     * @param Database $db
     */
    public function create($db) {
        global $THINKUP_CFG;

        error_reporting(22527); //Don't show E_DEPRECATED PHP messages, split() is deprecated

        //Create all the tables based on the build script
        $create_db_script = file_get_contents($THINKUP_CFG['source_root_path']."webapp/install/sql/build-db_mysql.sql");
        $create_statements = split(";", $create_db_script);
        foreach ($create_statements as $q) {
            if (trim($q) != '') {
                $db->exec($q.";");
            }
        }
    }

    /**
     * Drop ThinkUp tables
     *
     * @TODO: Use PDO instead of deprecated Database class.
     * @param Database $db
     */
    public function drop($db) {
        global $TEST_DATABASE;

        //Delete test data by dropping all existing tables
        $q = "SHOW TABLES FROM ".$TEST_DATABASE;
        $result = $db->exec($q);
        while ($row = mysql_fetch_assoc($result)) {
            $q = "DROP TABLE ".$row['Tables_in_'.$TEST_DATABASE];
            $db->exec($q);
        }
    }

    public function databaseExists($db_name) {
        $config = Config::getInstance();
        $server = $config->getValue('db_host');
        if ($config->getValue('db_port')) {
            $server .= ':'.$config->getValue('db_port');
        }
        $con = mysql_connect($server, $config->getValue('db_user'), $config->getValue('db_password'));
        $return = mysql_select_db($db_name, $con);
        //Set the db back to what it was.
        mysql_select_db($config->getValue('db_name'), $con);
        //Shouldn't close the connection, it's needed for other tests.
        //mysql_close($con);
        return $return;
    }

    public function deleteDatabase($db_name) {
        $config = Config::getInstance();
        $server = $config->getValue('db_host');
        if ($config->getValue('db_port')) {
            $server .= ':'.$config->getValue('db_port');
        }
        $con = mysql_connect($server, $config->getValue('db_user'), $config->getValue('db_password'));
        $db_name = mysql_real_escape_string($db_name);
        $return = mysql_query("DROP DATABASE `".$db_name."`");
        //Shouldn't close the connection, it's needed for other tests.
        //mysql_close($con);
        return $return;
    }

}
