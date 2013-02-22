<?php
/**
 *
 * ThinkUp/webapp/install/cli/upgrade.php
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
 * Command line interface for upgrading Thinkup/data
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

// help?
array_shift($argv);
if (isset($argv[0]) && preg_match('/^(\-h|\-\-help)$/i', $argv[0])) {
    usage();
}
$no_version = false;
if (isset($argv[0]) && preg_match('/^(\-\-with\-new\-sql)$/i', $argv[0])) {
    $no_version = true;
}
try {
    // do we need a migration?
    $db_version = UpgradeDatabaseController::getCurrentDBVersion($cached = false);
    $config = Config::getInstance();
    $thinkup_db_version = $config->getValue('THINKUP_VERSION');
    $filename = false;
    if ($db_version == $thinkup_db_version && ! $no_version) {
        error_log("\nYour ThinkUp database structure is up to date.\n");
        exit;
    } else {
        if (!$no_version) {
            print "\nThinkup needs to be upgraded to version $thinkup_db_version, proceed => [y|n] ";
            $handle = fopen ("php://stdin","r");
            $line = fgets($handle);
            if (trim($line) != 'y'){
                exit;
            }
        }
        print "\nWould you like to backup your data first? => [y|n] ";
        $handle = fopen ("php://stdin","r");
        $line = fgets($handle);
        if (trim($line) == 'y'){
            // we need zip support
            if (!BackupController::checkForZipSupport()) {
                print "\n    Error: ThinkUp backups require Zip support\n\n";
                exit(1);
            }
            print "\nEnter a .zip filename (/path/tp/backup.zip) => ";
            $handle = fopen ("php://stdin","r");
            $line = fgets($handle);
            $filename = trim($line);
        }
    }

    // set global mutex
    BackupController::mutexLock();

    // run backup first?
    if (isset($argv[0]) && preg_match('/^(\-h|\-\-help)$/i', $argv[0])) {
        usage();
    } else if ($filename) {
        if ( ! preg_match('/\.zip$/', $filename) ) {
            error_log("\nError: data file should end in .zip");
            usage();
        } else {
            putenv('BACKUP_VERBOSE=true');
            $backup_dao = DAOFactory::getDAO('BackupDAO');
            print "\nExporting data to: $filename\n\n";
            $backup_dao->export($filename);
            print "\nBackup completed.\n\n";
        }
    }
    // run updates...
    // get migrations we need to run...
    if (!$no_version) {
        print "\nUpgrading Thinkup to version $thinkup_db_version...\n\n";
    }

    $upgrade_start_time = microtime(true);
    putenv('CLI_BACKUP=true');
    $upgrade_ctl = new UpgradeDatabaseController();

    $migrations = $upgrade_ctl->getMigrationList($db_version, $no_version);
    $install_dao = DAOFactory::getDAO('InstallerDAO');
    if ($no_version && count($migrations) > 0) {
        $s = count($migrations) > 1 ? 's' : '';
        print "\nFound " . count($migrations) . " migration" . $s . " to process...\n";
    }
    foreach($migrations as $migration) {
        if ($no_version) {
            print("\n  Running migration with file " . $migration['filename'] . "\n");
        } else {
            print("\n  Running migration " . $migration['version'] . "\n");
        }
        $sql = preg_replace('/\-\-.*/','', $migration['sql']);
        $install_dao->runMigrationSQL($sql, $migration['new_migration'], $migration['filename']);
    }
    if (count($migrations) == 0) {
        print("\n  No migrations to run...\n\n");
        exit;
    }

    $option_dao = DAOFactory::getDAO('OptionDAO');
    $option = $option_dao->getOptionByName(OptionDAO::APP_OPTIONS, 'database_version');
    if ($option) {
        $option_dao->updateOptionByName(OptionDAO::APP_OPTIONS, 'database_version',$thinkup_db_version);
    } else {
        $option_dao->insertOption(OptionDAO::APP_OPTIONS, 'database_version', $thinkup_db_version);
    }

    // release global mutex
    BackupController::mutexLock();

    // delete upgrade token if it exists
    $upgrade_ctl->deleteTokenFile();

    $upgrade_end_time = microtime(true);
    $total_time = $upgrade_end_time - $upgrade_start_time;
    print "\nUpgrade complete. Total time elapsed: ".round($total_time, 2)." seconds\n\n";
} catch(Exception $e) {
    error_log("  Error: " . $e->getMessage() . "\n");
}

function usage() {
    print "\n Usage:\n\n";
    print "   php upgrade.php [--help][--with-new-sql]\n\n";
    print "    --with-new-sql - will upgrade with non-versioned in development sql files\n";
    print "                     example: '2011-05-17_new_feature.sql'\n";
    print "    --help         - usage help\n\n";
    exit;
}
