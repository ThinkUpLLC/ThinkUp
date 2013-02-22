<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.BackupMySQLDAO.php
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
 * Link MySQL Data Access Object
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Mark Wilkie
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 */
class BackupMySQLDAO extends PDODAO implements BackupDAO {

    public function import($zipfile) {
        if (file_exists($zipfile)) {
            $zip = new ZipArchive();
            if ($zip->open($zipfile) !==TRUE) {
                throw new Exception("Unable to open import file, corrupted zip file?: " . $zipfile);
            } else {
                // validate zip file
                $num_files = $zip->numFiles;
                if ($num_files < 1) {
                    throw new Exception("Unable to open import file, corrupted zip file?: " . $zipfile);
                }
                $num_files--;
                $last_file = $zip->statIndex($num_files);
                if ($last_file['name'] != 'create_tables.sql') {
                    throw new Exception("Unable to open import file, corrupted zip file?: " . $zipfile);
                }

                // extract zipfile
                $backup_dir = FileDataManager::getBackupPath();
                $zip->extractTo($backup_dir);
                $create_table = $backup_dir . '/create_tables.sql';
                $infiles = glob($backup_dir . '/*.txt');

                // rebuild db
                $sql = file_get_contents($create_table);
                if (getenv('BACKUP_VERBOSE')!==false) {
                    print "  Creating tables...\n\n";
                }
                $stmt = $this->execute($sql);
                $stmt->closeCursor();
                unlink($create_table);

                // imported table list
                $imported_tables = array();

                // import data
                foreach($infiles as $infile) {
                    $table = $infile;
                    $matches = array();
                    if (preg_match('#.*/(\w+).txt$#', $table, $matches)) {
                        $table = $matches[1];
                        $imported_tables[$table] = 1;
                        if (getenv('BACKUP_VERBOSE')!==false) {
                            print "  Restoring data for table: $table\n";
                        }
                        $q = "LOAD DATA INFILE '$infile' INTO TABLE $table";
                        $stmt = $this->execute($q);
                        if (!$stmt) {
                            throw new Exception("unable to load data file: " . $infile);
                        }
                        $stmt->closeCursor();
                        unlink($infile);
                    }
                }
                rmdir($backup_dir);

                //remove non-imported tables
                $stmt = $this->execute("SHOW TABLES");
                $db_tables = $this->getDataRowsAsArrays($stmt);
                foreach($db_tables as $table) {
                    foreach($table as $key => $value) {
                        $table_name = $value;
                        if (!isset( $imported_tables[ $table_name ] ) ) {
                            $stmt = $this->execute("DROP TABLE IF EXISTS $table_name");
                        }
                    }
                }

                return true;
            }
        } else {
            throw new Exception("Unable to open import file: " . $zipfile);
        }
    }

    public function export($backup_file = null) {
        // get table names...
        $q = "show tables";
        $q2 = "show create table ";
        $stmt = $this->execute($q);
        $data = $this->getDataRowsAsArrays($stmt);
        $create_tables = '';

        $zip_file = FileDataManager::getDataPath('.htthinkup_db_backup.zip');
        if ($backup_file) {
            $zip_file = $backup_file;
        }
        $zip = new ZipArchive();
        if (file_exists($zip_file)) {
            unlink($zip_file);
        }
        // make sure w can create this zip file, ZipArchive is a little funky and wont let us know its status
        // until we call close
        $zip_create_status = @touch($zip_file);
        if ($zip_create_status) {
            unlink($zip_file);
        }

        $backup_dir = FileDataManager::getBackupPath();
        if (!$zip_create_status || $zip->open($zip_file, ZIPARCHIVE::CREATE)!==TRUE) {
            throw new Exception("Unable to open backup file for exporting: $zip_file");
        }

        // write lock tables...
        $table_locks_list = '';
        foreach($data as $table) {
            foreach($table as $key => $value) {
                if ($table_locks_list != '') { $table_locks_list .= ', '; }
                $table_locks_list .= $value . ' WRITE';
            }
        }
        try {
            $stmt = $this->execute("LOCK TABLES " . $table_locks_list);
            $tmp_table_files = array();
            foreach($data as $table) {
                foreach($table as $key => $value) {
                    if (getenv('BACKUP_VERBOSE')!==false) {
                        print "  Backing up data for table: $value\n";
                    }
                    $stmt = $this->execute($q2 . $value);
                    $create_tables .= "-- Create $value table statement\n";
                    $create_tables .= "DROP TABLE IF EXISTS $value;\n";
                    $create_data = $this->getDataRowAsArray($stmt);
                    $create_tables .= $create_data["Create Table"] . ";";
                    $create_tables .= "\n\n";

                    // export table data
                    $table_file = FileDataManager::getBackupPath($value . '.txt');
                    if (file_exists($table_file)) {
                        unlink($table_file);
                    }
                    $q3 = "select * INTO OUTFILE '$table_file' from $value";
                    $stmt = $this->execute($q3);
                    $zip->addFile($table_file,"/$value" . '.txt');
                    array_push($tmp_table_files, $table_file);
                }
            }
        } catch (Exception $e) {
            $err = $e->getMessage();
            if (preg_match("/Can't create\/write to file/", $err) || preg_match("/Can\'t get stat of/", $err)) {
                // a file perm issue?
                throw new OpenFileException("It looks like the MySQL user does not have the proper file permissions "
                . "to back up data");
            } else {
                // assume its a GRANT FILE OR LOCK TABLES issue?
                throw new MySQLGrantException("It looks like the MySQL user does not have the proper grant permissions "
                . "to back up data");
            }
            error_log("export DB OUTFILE error: " . $e->getMessage());
        }
        // unlock tables...

        $stmt = $this->execute("unlock tables");
        if (getenv('BACKUP_VERBOSE')!==false) {
            print "\n  Backing up create table statments\n";
        }
        $zip->addFromString("create_tables.sql", $create_tables);
        $zip_close_status = $zip->close();
        // clean up tmp table files
        foreach($tmp_table_files as $tmp_file) {
            unlink($tmp_file);
        }
        if ($zip_close_status == false) {
            throw new Exception("Unable to create backup file for exporting. Bad file path?: $zip_file");
        }
        return $zip_file;
    }
}