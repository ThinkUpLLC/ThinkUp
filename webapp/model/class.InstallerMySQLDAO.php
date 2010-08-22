<?php
/**
 * Installer DAO MySQL Implementation
 * The MySQL data access object for the installer.
 *
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class InstallerMySQLDAO extends PDODAO implements InstallerDAO  {
    /**
     * Error message when unable to instantiate the PDO
     *
     * @var string
     */
    public $error_message;

    /**
     * Override the PDODAO constructor so we can use installerConnect to connect the database
     * without Config instantiation
     */
    public function __construct($config) {
        if(is_null(self::$PDO)) {
            $this->setPDO($config);
        }

        if (isset($config['table_prefix'])) {
            $this->prefix = $config['table_prefix'];
        }
        if (isset($config['GMT_offset'])) {
            $this->gmt_offset = $config['GMT_offset'];
        }
    }

    /**
     * Initalize connection and set PDO member object
     *
     * @param array $config Array of configuration
     */
    protected function setPDO($config){
        if (is_null(self::$PDO)) {
            //set default db type to mysql if not set
            $db_type = $config['db_type'];
            if(! $db_type) {
                $db_type = 'mysql';
            }
            $db_socket = $config['db_socket'];
            if ( !$db_socket) {
                $db_socket = '';
            } else {
                $db_socket=";unix_socket=".$db_socket;
            }
            $db_string = sprintf("%s:dbname=%s;host=%s%s",
            $db_type,
            $config['db_name'],
            $config['db_host'],
            $db_socket );
            try {
                self::$PDO = new PDO(
                $db_string,
                $config['db_user'],
                $config['db_password']);
                self::$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                if (strstr($e->getMessage(), 'SQLSTATE[')) {
                    preg_match('/SQLSTATE\[(\w+)\] \[(\w+)\] (.*)/', $e->getMessage(), $matches);
                    $this->error_message = $matches[3];
                }
            }
        }
    }

    /**
     * Set PDO to null
     */
    public function close() {
        return $this->disconnect();
    }

    /**
     * Get array of tables from current connected database
     *
     * @return array Table name strings
     */
    public function getTables() {
        $q = 'SHOW TABLES';
        $ps = $this->execute($q);
        $results = $this->getDataRowsAsArrays($ps);
        $tables = array();
        foreach ($results as $table) {
            $tables[] = $table['Tables_in_thinkup_tests'];
        }
        return $tables;
    }

    /**
     * Check table query
     *
     * @param str $table_name
     * @return array Row that consists of key Message_text.
     * If table exists and okay it must be array('Msg_text' => 'OK')
     */
    public function checkTable($table_name) {
        $q = "CHECK TABLE ".$table_name;
        $ps = $this->execute($q);
        $result = $this->getDataRowsAsArrays($ps);
        return $result[0];
    }

    /**
     * Repair table
     *
     * @param str $table_name Name of table to repair
     * @return array Row that consists of key Message_text.
     * If table exists and okay it must be array('Msg_text' => 'OK')
     */
    public function repairTable($table_name) {
        $q = "REPAIR TABLE ".$table_name;
        $ps = $this->execute($q);
        $result = $this->getDataRowsAsArrays($ps);
        return $result[0];
    }

    /**
     * Describe table
     *
     * @param str $table_name
     * @return array table descriptions that consist of following case-sensitive properties:
     *             - Field => name of field
     *             - Type => type of field
     *             - Null => is type allowed to be null
     *             - Default => Default value for field
     *             - Extra => such as auto_increment
     */
    public function describeTable($table_name) {
        $ps = $this->execute("DESCRIBE ".$table_name);
        return $this->getDataRowsAsArrays($ps);
    }

    /**
     * Get index from particular table
     *
     * @param str $table_name with prefix
     * @return array Tables indices of $table_name
     */
    public function showIndex($table_name) {
        $ps = $this->execute("SHOW INDEX FROM ".$table_name);
        return $this->getDataRowsAsArrays($ps);
    }

    /**
     * Modified wp's dbDelta function
     * Examines / groups queries
     * More info: http://codex.wordpress.org/Creating_Tables_with_Plugins#Creating_or_Updating_the_Table
     *
     * @param str $queries
     * @param array $tables
     * @return array
     */
    public function examineQueries($complete_query_string = '', $tables = array()) {
        $queries = explode(';', $complete_query_string);
        if ( $queries[count($queries)-1] == '' ) {
            array_pop($queries);
        }

        $creation_queries = array(); // Creation Queries
        $insert_update_queries = array(); // Insertion / Update Queries
        $for_update = array();

        // Create a tablename index for an array ($creation_queries) of queries
        foreach($queries as $query) {
            if (preg_match("|CREATE TABLE ([^ ]*)|", $query, $matches)) {
                $creation_queries[trim( strtolower($matches[1]), '`' )] = $query;
                $for_update[$matches[1]] = 'Created table '.$matches[1];
            } else if (preg_match("|CREATE DATABASE ([^ ]*)|", $query, $matches)) {
                array_unshift($creation_queries, $query);
            } else if (preg_match("|INSERT INTO ([^ ]*)|", $query, $matches)) {
                $insert_update_queries[] = $query;
            } else if (preg_match("|UPDATE ([^ ]*)|", $query, $matches)) {
                $insert_update_queries[] = $query;
            } else {
                // Unrecognized query type
                //echo 'Unrecognized query type'.$query;
            }
        }

        // Check to see which tables and fields exist
        if ( !empty($tables) ) {
            $cfields = array();
            $indices = array();

            // For every table in the database
            foreach ($tables as $table) {
                // If a table query exists for the database table...
                if ( array_key_exists(strtolower($table), $creation_queries) ) {
                    // Clear the field and index arrays
                    unset($cfields);
                    unset($indices);
                    // Get all of the field names in the query from between the parens
                    preg_match("|\((.*)\)|ms", $creation_queries[strtolower($table)], $match2);
                    $qryline = trim($match2[1]);

                    // Separate field lines into an array
                    $flds = explode("\n", $qryline);

                    // For every field line specified in the query
                    foreach ($flds as $fld) {
                        // Extract the field name
                        preg_match("|^([^ ]*)|", trim($fld), $fvals);
                        $field_name = trim( $fvals[1], '`' );

                        // Verify the found field name
                        $valid_field = true;
                        switch (strtolower($field_name)) {
                            case '':
                            case 'primary':
                            case 'index':
                            case 'fulltext':
                            case 'unique':
                            case 'key':
                                $valid_field = false;
                                $indices[] = trim(trim($fld), ", \n");
                                break;
                        }
                        $fld = trim($fld);

                        // If it's a valid field, add it to the field array
                        if ($valid_field) {
                            $cfields[strtolower($field_name)] = trim($fld, ", \n");
                        }
                    }

                    // Fetch the table column structure from the database
                    $table_fields = $this->describeTable($table);

                    // For every field in the table
                    foreach ($table_fields as $table_field) {
                        // If the table field exists in the field array...
                        if (array_key_exists(
                        strtolower($table_field['Field'])
                        , $cfields)) {
                            // Get the field type from the query
                            preg_match("|".$table_field['Field']." ([^ ]*( unsigned)?)|i",
                            $cfields[strtolower($table_field['Field'])], $matches);
                            $fieldtype = $matches[1];

                            // Is actual field type different from the field type in query?
                            if ($table_field['Type'] != $fieldtype) {
                                // Add a query to change the column type
                                $creation_queries[] = "ALTER TABLE {$table} CHANGE COLUMN {$table_field['Field']} " .
                                $cfields[strtolower($table_field['Field'])];
                                $for_update[$table.'.'.$table_field['Field']] = "Changed type of ".
                                "{$table}.{$table_field['Field']} " ."from {$table_field['Type']} to {$fieldtype}";
                            }

                            // Get the default value from the array
                            //echo "{$cfields[strtolower($table_field['Field'])]}<br>";
                            if (preg_match("| DEFAULT '(.*)'|i", $cfields[strtolower($table_field['Field'])],
                            $matches)) {
                                $default_value = $matches[1];
                                if ($table_field['Default'] != $default_value) {
                                    // Add a query to change the column's default value
                                    $creation_queries[] = "ALTER TABLE {$table} ALTER COLUMN {$table_field['Field']} ".
                                                  "SET DEFAULT '{$default_value}'";
                                    $for_update[$table.'.'.$table_field['Field']] = "Changed default value of " .
                                    "{$table}.{$table_field['Field']} from " ."{$table_field['Default']} to " .
                                    $default_value;
                                }
                            }

                            // Remove the field from the array (so it's not added)
                            unset($cfields[strtolower($table_field['Field'])]);

                        } else {
                            // This field exists in the table, but not in the creation queries?
                        }
                    }

                    // For every remaining field specified for the table
                    foreach ($cfields as $field_name => $fielddef) {
                        // Push a query line into $creation_queries that adds the field to that table
                        $creation_queries[] = "ALTER TABLE {$table} ADD COLUMN $fielddef";
                        $for_update[$table.'.'.$field_name] = 'Added column '.$table.'.'.$field_name;
                    }

                    // Index stuff goes here
                    // Fetch the table index structure from the database
                    $table_indices = $this->showIndex($table);
                    if ( !empty($table_indices) ) {
                        // Clear the index array
                        unset($index_ary);

                        // For every index in the table
                        foreach ($table_indices as $table_index) {
                            // Add the index to the index data array
                            $keyname = $table_index['Key_name'];
                            $index_ary[$keyname]['columns'][] = array(
                              'fieldname' => $table_index['Column_name'], 'subpart' => $table_index['Sub_part']
                            );
                            $index_ary[$keyname]['unique'] = ($table_index['Non_unique'] == 0) ? true : false;
                            $index_ary[$keyname]['fulltext'] = ($table_index['Index_type'] == 'FULLTEXT')?true:false;
                        }

                        // For each actual index in the index array
                        foreach ($index_ary as $index_name => $index_data) {
                            // Build a create string to compare to the query
                            $index_string = '';
                            if ($index_name == 'PRIMARY') {
                                $index_string .= 'PRIMARY ';
                            } else if ($index_data['unique']) {
                                $index_string .= 'UNIQUE ';
                            } else if ($index_data['fulltext']) {
                                $index_string .= 'FULLTEXT ';
                            }
                            $index_string .= 'KEY ';
                            if ($index_name != 'PRIMARY') {
                                $index_string .= $index_name;
                            }
                            $index_columns = '';
                            // For each column in the index
                            foreach ($index_data['columns'] as $column_data) {
                                if ($index_columns != '') $index_columns .= ',';
                                // Add the field to the column list string
                                $index_columns .= $column_data['fieldname'];
                                if ($column_data['subpart'] != '') {
                                    $index_columns .= '('.$column_data['subpart'].')';
                                }
                            }

                            // Add the column list to the index create string
                            $index_string .= ' ('.$index_columns.')';
                            if( !(($aindex = array_search($index_string, $indices)) === false) ) {
                                unset($indices[$aindex]);
                            }
                        }
                    }

                    // For every remaining index specified for the table
                    if ( isset($indices) && !empty($indices) ) {
                        foreach ( (array) $indices as $index ) {
                            // Push a query line into $creation_queries that adds the index to that table
                            $creation_queries[] = "ALTER TABLE {$table} ADD $index";
                            $for_update[$table.'.'.$field_name] = 'Added index '.$table.' '.$index;
                        }
                    }

                    // Remove the original table creation query from processing
                    unset($creation_queries[strtolower($table)]);
                    unset($for_update[strtolower($table)]);
                } else {
                    // This table exists in the database, but not in the creation queries?
                }
            }
        }

        $all_queries = array_merge($creation_queries, $insert_update_queries);
        return array('queries' => $all_queries, 'for_update' => $for_update);
    }
}