<?php
/**
 *
 * ThinkUp/webapp/install/cli/upgrade.php
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
 * Command line interface for upgrading up thinkup/data
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Mark Wilkie
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 */
chdir('../..');
require_once 'init.php';

// don't run via the web...
if(isset($_SERVER['SERVER_NAME'])) {
    die("This script should only be run via the command line...");
}

// help?
array_shift($argv);
if($argv[0] && preg_match('/^(\-h|\-\-help)$/i', $argv[0])) {
    usage();
}

try {

    // do we need a migration?
    $db_version = UpgradeController::getCurrentDBVersion($cached = false);
    $config = Config::getInstance();
    $thinkup_db_version = $config->getValue('THINKUP_VERSION');
    $filename = false;
    if($db_version == $thinkup_db_version) {
        error_log("\nYour system is up to date...\n");
        exit;
    } else {
        print "\nThinkup needs to be upgraded to version $thinkup_db_version, proceed => [y|n] ";
        $handle = fopen ("php://stdin","r");
        $line = fgets($handle);
        if(trim($line) != 'y'){
            exit;
        }
        print "\nWould you like to backup your data first? => [y|n] ";
        $handle = fopen ("php://stdin","r");
        $line = fgets($handle);
        if(trim($line) == 'y'){
            print "\nEnter a .zip filename (/path/tp/backup.zip) => ";
            $handle = fopen ("php://stdin","r");
            $line = fgets($handle);
            $filename = trim($line);
        }
    }


    // set global mutex
    BackupController::mutexLock();

    // run backup first?
    if($argv[0] && preg_match('/^(\-h|\-\-help)$/i', $argv[0])) {
        usage();
    } else if($filename) {
        if( ! preg_match('/\.zip$/', $filename) ) {
            error_log("\nError: data file should end in .zip");
            usage();
        } else {
            putenv('BACKUP_VERBOSE=true');
            $backup_dao = DAOFactory::getDAO('BackupDAO');
            print "\nExporting data to: $filename\n\n";
            $backup_dao->export($filename);
            print "\nBackup completed...\n\n";
        }
    }

    // run updates...

    // get migrations we need to run...
    print "\nUpgrading Thinkup to version $thinkup_db_version...\n\n";
    putenv('CLI_BACKUP=true');
    $upgrade_ctl = new UpgradeController();
    $migrations = $upgrade_ctl->getMigrationList($db_version);
    $install_dao = DAOFactory::getDAO('InstallerDAO');
    foreach($migrations as $migration) {
        print("  Running migration " . $migration['version'] . "\n");
        $install_dao->runMigrationSQL($migration['sql']);
    }

    $option_dao = DAOFactory::getDAO('OptionDAO');
    $option = $option_dao->getOptionByName(OptionDAO::APP_OPTIONS, 'database_version');
    if($option) {
        $option_dao->updateOptionByName(OptionDAO::APP_OPTIONS, 'database_version',$thinkup_db_version);
    } else {
        $option_dao->insertOption(OptionDAO::APP_OPTIONS, 'database_version', $thinkup_db_version);
    }

    // release global mutex
    BackupController::mutexLock();

    print "\nUpgrade complete...\n\n";

} catch(Exception $e) {
    error_log("  Error: " . $e->getMessage() . "\n");
}

function usage() {
    print "\n Usage:\n\n";
    print "   php update.php [--help]\n\n";
    print "    --help - usage help\n\n";
    exit;
}
