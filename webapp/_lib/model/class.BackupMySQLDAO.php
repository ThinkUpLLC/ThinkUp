<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.BackupMySQLDAO.php
 *
 * Copyright (c) 2009-2010 Mark Wilkie
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
 * Link MySQL Data Access Object
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Mark Wilkie
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 */
class BackupMySQLDAO extends PDODAO implements BackupDAO {

    public function import($zipfile) {
        if(file_exists($zipfile)) {
            $zip = new ZipArchive();
            if ($zip->open($zipfile) !==TRUE) {
                throw new Exception("Unable to open import file, corrupted zip file?: " . $zipfile);
            } else {
                // validate zip file
                $num_files = $zip->numFiles;
                if($num_files < 1) {
                    throw new Exception("Unable to open import file, corrupted zip file?: " . $zipfile);
                }
                $num_files--;
                $last_file = $zip->statIndex($num_files);
                if($last_file['name'] != 'create_tables.sql') {
                    throw new Exception("Unable to open import file, corrupted zip file?: " . $zipfile);
                }

                // extract zipfile
                // create backip dir
                $bkdir = THINKUP_WEBAPP_PATH . self::CACHE_DIR . '/backup';
                if(! file_exists($bkdir)) {
                    mkdir($bkdir);
                }
                $zip->extractTo($bkdir);
                $create_table = $bkdir . '/create_tables.sql';
                $infiles = glob($bkdir . '/*.txt');

                // rebuild db
                $sql = file_get_contents($create_table);
                $stmt = $this->execute($sql);
                $stmt->closeCursor();
                unlink($create_table);

                // import data
                //var_dump($infiles);
                foreach($infiles as $infile) {
                    $table = $infile;
                    $matches = array();
                    if(preg_match('#.*/(\w+).txt$#', $table, $matches)) {
                        $table = $matches[1];
                        $q = "LOAD DATA INFILE '$infile' INTO TABLE $table";
                        $stmt = $this->execute($q);
                        if(! $stmt) {
                            throw new Exception("unbale to load data file: " . $infile);
                        }
                        $stmt->closeCursor();
                        unlink($infile);
                    }
                }
                rmdir($bkdir);
                return true;
            }
        } else {
            throw new Exception("Unable to open import file: " . $zipfile);
        }
    }

    public function export() {
        // get table names...
        $q = "show tables";
        $q2 = "show create table ";
        $stmt = $this->execute($q);
        $data = $this->getDataRowsAsArrays($stmt);
        $create_tables = '';
        $zip_file = THINKUP_WEBAPP_PATH . self::CACHE_DIR . '/thinkup_db_backup.zip';
        $zip = new ZipArchive();
        if(file_exists($zip_file)) {
            unlink($zip_file);
        }
        if ($zip->open($zip_file, ZIPARCHIVE::CREATE)!==TRUE) {
            throw new Exception("Unable to open backup for for exporting: $zip_file");
        }

        // lock tables for writes...
        $stmt = $this->execute("flush tables with read lock");

        $tmp_table_files = array();
        foreach($data as $table) {
            foreach($table as $key => $value) {

                $stmt = $this->execute($q2 . $value);
                $create_tables .= "-- Create $value table statement\n";
                $create_tables .= "DROP TABLE IF EXISTS $value;\n";
                $create_data = $this->getDataRowAsArray($stmt);
                $create_tables .= $create_data["Create Table"] . ";";
                $create_tables .= "\n\n";

                // export table data
                $table_file = THINKUP_WEBAPP_PATH . self::CACHE_DIR . '/' . $value . '.txt';
                if(file_exists($table_file)) {
                    unlink($table_file);
                }
                $q3 = "select * INTO OUTFILE '$table_file' from $value";
                $stmt = $this->execute($q3);
                $zip->addFile($table_file,"/$value" . '.txt');
                array_push($tmp_table_files, $table_file);
            }
        }

        // unlock tables...
        $stmt = $this->execute("unlock tables");

        $zip->addFromString("create_tables.sql", $create_tables);
        $zip->close();
        // clean up tmp table files
        foreach($tmp_table_files as $tmp_file) {
            unlink($tmp_file);
        }
        return $zip_file;
    }
}