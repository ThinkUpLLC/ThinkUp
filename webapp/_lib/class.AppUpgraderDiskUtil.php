<?php
/**
 *
 * ThinkUp/webapp/_lib/class.AppUpgraderDiskUtil.php
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Mark Wilkie
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 */
class AppUpgraderDiskUtil {
    /**
     * @var int Required amount of disk space to run web update, 100MB default
     */
    static $DISK_SPACE_NEEDED = 104857600;
    /**
     * @var array Config file regexes
     */
    var $CONFIG = array( array('/\/config\.inc\.php$/', 'config.inc.php') );
    /**
     * @var array Files to ignore during web update
     */
    var $IGNORE_FILES = array('/_lib\/view\/compiled_view/', '/\/data\//');
    /**
     * @var str ThinkUp's current version
     */
    var $app_version = '0';
    /**
     * @var str Application directory
     */
    private $app_dir;
    /**
     * @var str Application data directory
     */
    private $data_dir;
    /**
     * Constructor
     * @param $app_dir str Application directory
     * @returns AppUpgraderDiskUtil
     */
    public function __construct($app_dir) {
        $this->app_dir = $app_dir;
        $this->app_version = Config::getInstance()->getValue('THINKUP_VERSION');
        $this->data_path = FileDataManager::getDataPath();
    }
    /**
     * Get the amount of available disk space.
     * @throws Exception If there's not enough available disk space
     * @return int Number of available megabytes; -1 if not known
     */
    public function getAvailableDiskSpace() {
        $disk_free_space = disk_free_space($this->app_dir);
        if ($disk_free_space !== false && $disk_free_space != '') {
            $available = (int) round(($disk_free_space / 1024) / 1024 );
            $needed = (int) (self::$DISK_SPACE_NEEDED / 1024) / 1024;
            if ($available > $needed) {
                return $available;
            } else {
                throw new exception("There is not enough free disk space to perform an update. " .$available.
                "MB available, but ".$needed."MB required.");
            }
        } else {
            return -1;
        }
    }
    /**
     * Get the amount of disk space required by web updater in bytes.
     * @return int
     */
    public function getDiskSpaceNeeded() {
        return self::$DISK_SPACE_NEEDED;
    }
    /**
     * Delete current ThinkUp application files.
     * @throws Exception
     */
    public function deleteOldInstall() {
        $files = $this->findAllFiles($this->app_dir);
        foreach($files as $file) {
            $ignore = false;
            foreach($this->IGNORE_FILES as $ignore_regex) {
                if (preg_match($ignore_regex, $file)) {
                    $ignore = true;
                    break;
                }
            }
            if ($ignore) {
                continue;
            }
            if (is_dir($file)) {
                rmdir($file);
            } else {
                if (!unlink($file)) {
                    throw new Exception("Unable able to delete $file");
                };
            }
        }
    }
    /**
     * Set the amount of disk space needed in bytes.
     * @param $bytes
     */
    public function setDiskSpaceNeeded($bytes) {
        self::$DISK_SPACE_NEEDED = $bytes;
    }
    /**
     * Back up current installation files.
     * @throws Exception
     */
    public function backUpInstall() {
        $backupzip = new ZipArchive();
        $backup_zipfile = $this->getBackupFilename();
        if ($backupzip->open($backup_zipfile, ZIPARCHIVE::CREATE)!==true) {
            throw new Exception("Unable to open backup file to export: $backup_zipfile");
        }
        $files = $this->findAllFiles($this->app_dir);
        foreach($files as $file) {
            $ignore = false;
            foreach($this->IGNORE_FILES as $ignore_regex) {
                if (preg_match($ignore_regex, $file)) {
                    $ignore = true;
                    break;
                }
            }
            if ($ignore) {
                continue;
            }
            $config = false;
            $backup_config = $this->getBackupFilename(true);
            foreach($this->CONFIG as $config_regex) {
                if (preg_match($config_regex[0], $file)) {
                    copy($file, $backup_config);
                    $config = true;
                    break;
                }
            }
            if (!$config && $file != $this->data_path) {
                $backupzip->addFile($file);
            }
        }
        $zip_close_status = $backupzip->close();
        if ($zip_close_status == false) {
            throw new Exception("Unable to create zip archive to back up. $backup_zipfile");
        }
        return array('backup' => $backup_zipfile, 'config' => $backup_config);
    }
    /**
     * Find all files.
     * @param $file File or directory name
     * @throws Exception
     * @return array
     */
    public function findAllFiles($file) {
        if (!is_writable($file)) {
            throw new Exception(self::getInadequateFilePermissionsException());
        }
        $root = scandir($file);
        foreach($root as $value) {
            if ($value === '.' || $value === '..') {
                continue;
            }
            if (is_file("$file/$value")) {
                $result[]="$file/$value";
                continue;
            }
            $files = $this->findAllFiles("$file/$value");
            if (isset($files)) {
                foreach($files as $value) {
                    $result[]=$value;
                }
            }
        }
        return (isset($result) ? $result : array());
    }
    /**
     * Get backup filename.
     * @param bool $config Whether or not it's a config file, defaults to false.
     * @return str File name
     */
    private function getBackupFilename($config = false) {
        $update_dir = dirname(__FILE__);
        $date = time();
        $filename = $this->data_path . '' . $date . '-v' . $this->app_version . '-';
        if ($config) {
            $filename .= 'config.inc.backup.php';
        } else {
            $filename .= 'backup.zip';
        }
        return $filename;
    }
    /**
     * Write zip file contents to the application data directory.
     * @param str $data
     * @throws Exception if unable to write file
     * @return str $filename
     */
    public function writeZip($data) {
        $date = time();
        $filename = $this->data_path . 'latest_update.zip';
        $result = file_put_contents($filename, $data);
        if ($result === false) {
            throw new Exception("Unable to save ".$filename.". Result ".$result);
        } else if (is_int($result)) {
            if ($result < 1) {
                throw new Exception("Unable to save ".$filename.". Wrote ".$result.' bytes.');
            }
        }
        return $filename;
    }
    /**
     * Copy new install files.
     * @param str $src
     * @param str $dst
     */
    public function recurseCopy($src,$dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    $this->recurseCopy($src . '/' . $file,$dst . '/' . $file);
                } else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
    /**
     * Remove a temporary thinkup directory.
     * @param str $new_version_dir
     */
    public function deleteDir($file) {
        if (!preg_match('/\/thinkup(\/)?/', $file )) {
            throw new Exception("The deleteDir function is designed to remove a temporary thinkup directory: " . $file);
        }
        if (!file_exists($file)) {
            throw new Exception("Trying to delete a directory or file that does not exist: $file");
        }
        if (!is_dir($file)) {
            return unlink($file);
        }
        foreach (scandir($file) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            if (!$this->deleteDir($file . "/" . $item)) {
                return false;
            }
        }
        return rmdir($file);
    }

    /**
     * Checks if application file permissions allow web-based update.
     * @throws Exception
     * @param $app_path
     */
    public function validateUpdatePermissions($app_path) {
        $app_files = $this->findAllFiles($app_path);
        foreach($app_files as $file) {
            if (!is_writable($file)) {
                throw new Exception(self::getInadequateFilePermissionsException());
            }
        }
    }
    /**
     * Get detailed file permissions user error text.
     * @return str
     */
    private function getInadequateFilePermissionsException() {
        $whoami = @exec('whoami');
        if (empty($whoami)) {
            $whoami = 'nobody';
        }
        return "<b>Oops!</b> ThinkUp can't upgrade itself because it doesn't have the right file permissions. ".
        "To fix this problem, run<br /><br /><code>sudo chown -R $whoami ". THINKUP_WEBAPP_PATH."</code><br /><br />".
        "on your server, using root (or sudo). If you don't have root access, try the following: ".
        "<br /><br /> <code>chmod -R a+rw ".THINKUP_WEBAPP_PATH."</code><br /><br />";
    }
}
