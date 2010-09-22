<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.InstallerMySQLDAO.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
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
/**
 * Installer DAO MySQL Implementation
 * The MySQL data access object for the installer.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class InstallerMySQLDAO extends PDODAO implements InstallerDAO  {

    public function getTables() {
        $q = 'SHOW TABLES';
        $ps = $this->execute($q);
        $tables = array();
        while ( $row = $ps->fetch(PDO::FETCH_NUM) ) {
            $tables[] = $row[0];
        }
        $ps->closeCursor();
        return $tables;
    }

    public function checkTable($table_name) {
        $q = "CHECK TABLE ".$table_name;
        $ps = $this->execute($q);
        $result = $this->getDataRowsAsArrays($ps);
        return $result[0];
    }

    public function repairTable($table_name) {
        $q = "REPAIR TABLE ".$table_name;
        $ps = $this->execute($q);
        $result = $this->getDataRowsAsArrays($ps);
        return $result[0];
    }

    public function describeTable($table_name) {
        $ps = $this->execute("DESCRIBE ".$table_name);
        return $this->getDataRowsAsArrays($ps);
    }

    public function showIndex($table_name) {
        $ps = $this->execute("SHOW INDEX FROM ".$table_name);
        return $this->getDataRowsAsArrays($ps);
    }

    public function diffDataStructure($desired_structure_sql_string = '', $existing_tables = array()) {
        $queries = explode(';', $desired_structure_sql_string);
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
        if ( !empty($existing_tables) ) {
            $cfields = array();
            $indices = array();

            // For every table in the database
            foreach ($existing_tables as $table) {
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