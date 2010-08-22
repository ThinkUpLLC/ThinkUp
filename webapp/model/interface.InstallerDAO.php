<?php
/**
 * Installer Data Access Object interface
 *
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 *
 */
interface InstallerDAO {
    /**
     * Get array of tables
     *
     * @return array Table names
     */
    public function getTables();

    /**
     * Check table condition
     *
     * @param str $table_name with prefix
     * @return array Table condition
     */
    public function checkTable($table_name);

    /**
     * Repair table
     *
     * @param string $table_name with prefix
     */
    public function repairTable($table_name);

    /**
     * Describe table
     *
     * @param string $table_name with prefix
     */
    public function describeTable($table_name);

    /**
     * Get list of table indexes
     *
     * @param string $table_name with prefix
     */
    public function showIndex($table_name);

    /**
     * Examines / groups queries based on modified wp's dbDelta function. Examine string of queries from
     * specified array of tables
     *
     * @param str $queries
     * @param array $tables array of tables with prefix
     * @return array Queries and update message. The array must contains key of queries and for_update.
     *         return array('queries' => $all_queries, 'for_update' => $for_update);
     */
    public function examineQueries($queries = '', $tables = array());
}