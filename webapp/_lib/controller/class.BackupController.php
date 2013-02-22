<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.BackupController.php
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
 * Export Controller
 * Exports posts from an instance user on ThinkUp.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Mark Wilkie
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
class BackupController extends ThinkUpAdminController {

    /**
     *
     * @var string Zip Class we check for a backup dependency
     */
    static public $zip_class_req = 'ZipArchive';

    public function __construct($session_started=false) {
        parent::__construct($session_started);
        $this->setViewTemplate('install.backup.tpl');
        $this->setPageTitle('Backup & Restore');
        $this->backup_file = FileDataManager::getDataPath('.htthinkup_db_backup.zip'); // not in the backup dir itself
    }

    public function adminControl() {
        $this->disableCaching();
        $this->view_mgr->addHelp('backup', 'install/backup');
        if (!self::checkForZipSupport()) {
            $this->addToView('no_zip_support', true);
        }
        // pass the count of the table with  the most records
        $table_stats_dao = DAOFactory::getDAO('TableStatsDAO');
        $table_counts = $table_stats_dao->getTableRowCounts();
        if ($table_counts[0]['count'] > UpgradeDatabaseController::$WARN_TABLE_ROW_COUNT) {
            $this->addToView('high_table_row_count',$table_counts[0]);
        }
        try {
            $backup_dao = DAOFactory::getDAO('BackupDAO');
            if (isset($_GET['backup'])) {
                self::mutexLock();
                try {
                    /* export/download backup file */
                    $backup_dao->export();
                    if ( ! headers_sent() ) { // this is so our test don't barf on us
                        header('Content-Type: application/zip');
                        header('Content-Disposition: attachment; filename="thinkup_db_backup.zip"');
                        header('Pragma: no-cache');
                        header('Expires: 0');
                    }
                    $fh = fopen($this->backup_file, "rb");
                    if ($fh) {
                        while (!feof($fh)) {
                            $data = fread($fh, 256);
                            echo $data;
                            flush();
                        }
                        fclose($fh);
                        unlink($this->backup_file);
                    } else {
                        throw new Exception("Unable to read backup zip file: " + $this->backup_file);
                    }
                } catch (MySQLGrantException $e) {
                    $this->addErrorMessage('It looks like the MySQL user does not have the proper permissions to grant'
                    . ' export Access. Please see the'
                    . ' <a href="http://thinkup.com/docs/troubleshoot/common/backupcannotwrite.html">'
                    . ' ThinkUp documentation</a> for more info on how to resolve this issue.');
                    self::mutexLock(true);
                    return $this->generateView();
                } catch (OpenFileException $e) {
                    $this->addErrorMessage('It looks like the MySQL user does not have the proper file permissions to'
                    . ' export data. Please see the'
                    . ' <a href="http://thinkup.com/docs/troubleshoot/common/backupcannotwrite.html">'
                    . ' ThinkUp documentation</a> for more info on how to resolve this issue.');
                    self::mutexLock(true);
                    return $this->generateView();
                }
                self::mutexLock(true);
            } else if (isset($_FILES['backup_file'])) {
                self::mutexLock();
                /* upload backup file */
                if ($_FILES['backup_file']['error']) {
                    if ($_FILES['backup_file']['error'] == UPLOAD_ERR_INI_SIZE) {
                        throw new Exception("Backup file upload failed. The file is too large." .
                        "You may need to increase the upload_max_filesize in php.ini.");
                    } else if ($_FILES['backup_file']['error'] == UPLOAD_ERR_NO_FILE) {
                        throw new Exception("No file uploaded. Please select a backup file to upload");
                    } else {
                        throw new Exception("Backup file upload failed.");
                    }
                } else {
                    $backup_dao->import($_FILES['backup_file']['tmp_name']);
                    $this->addSuccessMessage("Data Import Successfull!");
                    return $this->generateView();
                }
                self::mutexLock(true);
            } else {
                /* load default form */
                return $this->generateView();
            }
        } catch (Exception  $e) {
            $this->addErrorMessage($e->getMessage());
            return $this->generateView();
        }
    }

    /**
     * Checks to see if we have zip support
     * @returns boolean - true if we have zip support else false
     */
    public static function checkForZipSupport() {
        //check for zip support
        $zipsupport = false;
        if (class_exists(self::$zip_class_req)) {
            $zipsupport = true;
        }
        return $zipsupport;
    }

    /**
     * Set mutex lock
     * @param bool $release, if defined release mutex, else get it
     * @throws CrawlerLockedException if unable to get crawler mutex
     */
    public static function mutexLock($release = false) {
        $mutex_dao = DAOFactory::getDAO('MutexDAO');
        $global_mutex_name = PluginRegistrarCrawler::GLOBAL_MUTEX;
        if ($release) {
            $mutex_dao->releaseMutex($global_mutex_name);
        } else {
            // Everyone needs to check the global mutex
            $lock_successful = $mutex_dao->getMutex($global_mutex_name);
            if (!$lock_successful) {
                throw new CrawlerLockedException("A crawl is in progress, please wait until completed...");
            }
        }
    }
}