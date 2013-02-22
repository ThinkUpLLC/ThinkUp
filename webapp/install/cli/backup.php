<?php
/**
 *
 * ThinkUp/webapp/install/cli/backup.php
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
 * Caommnd line interface for backing up thinkup data
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Mark Wilkie
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 */
chdir(dirname(__FILE__) . '/../..');
require_once 'init.php';
//Avoid "Error: DateTime::__construct(): It is not safe to rely on the system's timezone settings" error
require_once 'config.inc.php';
date_default_timezone_set($THINKUP_CFG['timezone']);

// don't run via the web...
if (isset($_SERVER['SERVER_NAME'])) {
    die("This script should only be run via the command line.");
}

// we need zip support
if (!BackupController::checkForZipSupport()) {
    print "\nError: ThinkUp backups require Zip support\n\n";
    exit(1);
}

try {
    array_shift($argv);
    if (!empty($argv[0]) && preg_match('/^(\-h|\-\-help)$/i', $argv[0])) {
        usage();
    }

    if (count($argv) != 2 || ! preg_match('/^(\-\-export|\-\-import)$/', $argv[0]) ) {
        usage();
    } else {
        $filename = $argv[1];
        if ( ! preg_match('/\.zip$/', $filename) ) {
            error_log("\nError: data file should end in .zip");
            usage();
        } else {

            // set global mutex
            BackupController::mutexLock();

            putenv('BACKUP_VERBOSE=true');
            $backup_dao = DAOFactory::getDAO('BackupDAO');
            if ($argv[0] == '--export') {
                print "\nExporting data to: $filename\n\n";
                $backup_dao->export($filename);
                print "\nBackup completed...\n\n";
            } else {
                if (!file_exists($filename)) {
                    error_log("\nError: data import file '$filename' not found");
                    usage();
                }
                print "\nImporting data from: $filename\n\n";
                $backup_dao->import($filename);
                print "\nImport completed...\n\n";
            }

            // release global mutex
            BackupController::mutexLock(true);
        }
    }
} catch(Exception $e) {
    error_log("  Error: " . $e->getMessage() . "\n");
}

function usage() {
    print "\n Usage:\n\n";
    print "   php backup.php [--help] [--export|--import] filename.zip\n\n";
    print "    --export export_filename.zip - exports data to specified filename\n";
    print "    --import import_filename.zip - imports data from specified filename\n";
    print "    --help                       - usage help\n\n";
    exit;
}
