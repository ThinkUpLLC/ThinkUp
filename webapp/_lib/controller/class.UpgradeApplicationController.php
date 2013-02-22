<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.UpgradeApplicationController.php
 *
 * Copyright (c) 2012-2013 Mark Wilkie
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
 * Web-based Application Update Controller
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Mark Wilkie
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
class UpgradeApplicationController extends ThinkUpAuthController {
    /**
     * Constructor
     * @param bool $session_started
     * @return UpgradeApplicationController
     */
    public function __construct($session_started=false) {
        parent::__construct($session_started);
        $this->setViewTemplate('install.upgrade-application.tpl');
    }

    public function authControl() {
        $show_try_again_button = false;
        if (isset($_GET['run_update'])) {
            try {
                $backup_info = $this->runUpdate(dirname(__FILE__));
                //var_dump($backup_info);
                $site_root_path = Config::getInstance()->getValue('site_root_path');
                $redir = $site_root_path . 'install/upgrade-application.php?ran_update=1';
                $this->redirect($redir);
            } catch(Exception $e) {
                $this->addErrorMessage($e->getMessage(), null, true);
            }
        } else if (isset($_GET['ran_update'])) {
            //Update the application server name in app settings for access by command-line scripts
            Installer::storeServerName();

            //Clear Smarty's compiled templates and cache to start fresh with newly upgraded app
            $this->view_mgr->clear_compiled_tpl();
            $this->view_mgr->clear_all_cache();
            $this->addToView('updated',true);
        } else {
            $verify_updatable = true;
            try {
                $update_info = $this->runUpdate(dirname(__FILE__), $verify_updatable);
                if (isset($update_info['latest_version'])) {
                    $this->addToView('latest_version',$update_info['latest_version']);
                }
                $this->addToView('updateable',true);
            } catch(Exception $e) {
                $this->addErrorMessage($e->getMessage(), null, true);
                $show_try_again_button = true;
            }
        }
        $this->addToView('show_try_again_button', $show_try_again_button);
        return $this->generateView();
    }

    /**
     * Upgrade the application code to the latest version.
     * @throws Exception
     * @param bool $verify_updatable Whether or not to verify if installation is updatable, defaults to false
     * @return array Backup file information
     */
    public function runUpdate($file_path, $verify_updatable = false) {
        $app_dir = preg_replace("/\/_lib\/controller/", '', $file_path);

        // do we have the disk space we need?
        $disk_util = new AppUpgraderDiskUtil($app_dir);
        $disk_space_megs = $disk_util->getAvailableDiskSpace();
        // do we have the perms to do what we need?
        $disk_util->validateUpdatePermissions($app_dir);

        // do we need to update?
        $update_client = new AppUpgraderClient();
        $update_info = $update_client->getLatestVersionInfo();
        require(dirname(__FILE__) . '/../../install/version.php');
        $version = Config::GetInstance()->getvalue('THINKUP_VERSION');
        if ( version_compare($update_info['version'], $version) <= 0) {
            throw new Exception("You are running the latest version of ThinkUp.");
        }

        if ($verify_updatable == true) {
            return array('latest_version' => $update_info['version']);
        }

        // download zip...
        $update_zip_data = $update_client->getLatestVersionZip($update_info['url']);
        $update_zip = $disk_util->writeZip($update_zip_data);
        $zip = new ZipArchive();
        $open_result = $zip->open($update_zip);
        if ($open_result !== true) {
            unlink($update_zip);
            throw new Exception("Unable to extract ".$update_zip.". ZipArchive::open failed with error code ".
            $open_result);
        }
        $num_files = $zip->numFiles;
        if ($num_files < 1) {
            unlink($update_zip);
            throw new Exception("Unable to extract ".$update_zip.". ZipArchive->numFiles is ".$num_files);
        }

        $backup_file_info = array();
        $backup_file_info = $disk_util->backupInstall();

        $disk_util->deleteOldInstall();
        $data_path = FileDataManager::getDataPath();
        if ($zip->extractTo($data_path) !== true) {
            throw new Exception("Unable to extract new files into $app_dir: " . $zip->getStatusString());
        } else {
            $new_version_dir = $data_path . 'thinkup';
            $disk_util->recurseCopy($new_version_dir,$app_dir);
            // delete install files
            $disk_util->deleteDir($new_version_dir);
            unlink($update_zip);
        }
        //replace config file
        copy($backup_file_info['config'], "$app_dir/config.inc.php");
        return $backup_file_info;
    }
}