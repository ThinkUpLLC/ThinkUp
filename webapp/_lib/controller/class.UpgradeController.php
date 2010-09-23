<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.UpgradeController.php
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
 * AccountConfiguration Controller
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Mark Wilkie
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */

/**
 * Upgrade Controller
 *
 * Compares ThinkUp's database build script with the current db structure and runs migrations required to get them to
 * match. This controller is a work in progress and doesn't fully support all possible migrations.
 * 
 * If a migration is in progress, the controller creates the temporary file: 
 * _lib/view/compiled_view/upgrade-in-progress file. All users will get a message noting that the 
 * application is undergoing an upgrade and will not be usable.
 *
 * Currently this controller will:
 * * Add missing columns
 * * Add missing indexes
 *
 * It will NOT:
 * * Add missing tables
 * * Modify any data in the tables
 * * Drop any tables, columns or indexes
 *
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */

class UpgradeController extends ThinkUpAuthController {

    /**
     * Upgrade status file. Disables all controllers but UpgradeController
     * if this file exists.
     */
    const UPGRADE_IN_PROGRESS_FILE = '_lib/view/compiled_view/upgrade-in-progress';

    /**
     * Constructor
     * @param bool $session_started
     * @return UpgradeController
     */
    public function __construct($session_started=false) {
        parent::__construct($session_started);
    }


    public function authControl() {

        Utils::defineConstants();
        
        $build_sql_file = THINKUP_WEBAPP_PATH . 'install/sql/build-db_mysql.sql';
        $install_dao = DAOFactory::getDAO('InstallerDAO');
        $build_sql_string = file_get_contents($build_sql_file);

        if(! $build_sql_string) {
            throw new OpenFileException("Unable to open file: " + $build_sql_file);
        }
        $tables = $install_dao->getTables();

        // clean up primary key sql
        $build_sql_string = str_replace('PRIMARY KEY ', 'PRIMARY KEY  ', $build_sql_string);
        // pull out insert after dump data
        $build_sql_string = preg_replace('/-- Dump completed.*/s', '', $build_sql_string);
        $migrate_sql_array = $install_dao->diffDataStructure($build_sql_string, $tables);
        if(isset($_GET['process_sql'])) {
            sleep(1); // just so we can see what is happening...
            $process_sql  = stripslashes($_GET['process_sql']);
            $processed = false;
            foreach($migrate_sql_array['queries'] as $sql) {
                if($sql == $process_sql) {
                    $install_dao->runMigrationSQL($process_sql);
                    $processed = true;
                }
            }
            $this->setJsonData( array( 'processed' => $processed, 'sql' => $process_sql) );
        } else if (isset($_GET['migration_done'])) {
            unlink(THINKUP_WEBAPP_PATH . self::UPGRADE_IN_PROGRESS_FILE);
        } else {
            $this->setPageTitle('Upgrade...');
            $this->setViewTemplate('upgrade.index.tpl');
            $this->addToView('migrations',$migrate_sql_array['queries']);
            $this->addToView('migrations_json', json_encode($migrate_sql_array['queries']));
            // do we need to do a migration?
            if(count( $migrate_sql_array['queries'] ) > 0) {
                // disable all controller bu upgrade
                touch(THINKUP_WEBAPP_PATH . self::UPGRADE_IN_PROGRESS_FILE);
            }
        }

        return $this->generateView();

    }
}