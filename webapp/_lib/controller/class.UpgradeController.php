<?php
/**
 * Upgrade Controller
 *
 * Runs DB migrations
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