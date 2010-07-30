<?php
/**
 * ThinkUp Database Helper
 *
 * Constructs and destructs the ThinkUp data structure for testing purposes.
 *
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
        $create_db_script = file_get_contents($THINKUP_CFG['source_root_path']."sql/build-db_mysql.sql");
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
}
