<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.ExportServiceUserDataController.php
 *
 * Copyright (c) 2011-2013 Gina Trapani
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
 * Export Service User Data Controller
 *
 * Selects all the posts, replies, retweets, mentions, favorites, links, followers, followees, and users associated
 * with a specified service user into an outfile in the data directory, EXCEPT for row IDs so the data can be
 * imported with new auto_increment values into a fresh, empty database. Generates a zip archive of all the exported
 * files and outputs it.
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class ExportServiceUserDataController extends ThinkUpAdminController {
    /**
     * @var arr List of file names which should go into the zip archive in this format:
     * array('path'=>'/path/to/file.txt', 'name'=>'shortname.txt')
     */
    var $files_to_zip = array();
    /**
     *
     * @var int Number of posts to process per page
     */
    var $page_size = 500;
    /**
     * @var str Full path and zip file name.
     */
    var $zip_file_full_name;
    /**
     * @var str Just zip file name
     */
    var $zip_file_short_name;
    /**
     * @var str Full path and readme file name
     */
    var $readme_file;

    public function __construct($session_started=false) {
        parent::__construct($session_started);
        $this->setViewTemplate('install.exportserviceuser.tpl');
        $this->setPageTitle('Export User Data');
    }

    public function adminControl() {
        $this->disableCaching();
        if (!BackupController::checkForZipSupport()) {
            $this->addToView('no_zip_support', true);
        } else {
            $instance_dao = DAOFactory::getDAO('InstanceDAO');
            if (isset($_POST['instance_id'])) {
                $instance = $instance_dao->get($_POST['instance_id']);
                if ($instance != null) {
                    $this->zip_file_short_name = str_replace(' ', '_', $instance->network_username)."_".
                    str_replace(' ', '_', $instance->network).'_user_data.zip';
                    $this->zip_file_full_name =  FileDataManager::getDataPath($this->zip_file_short_name);
                    $this->readme_file = FileDataManager::getBackupPath('README.txt');
                    $this->files_to_zip[] = array('path'=>$this->readme_file, 'name'=>'README.txt');
                    self::appendToReadme(
'THINKUP EXPORTED USER DATA
===========================

This zip archive contains all the data related to a specific service user gathered by ThinkUp. This README file '.
                'describes how to import that data into an existing ThinkUp installation.

');
                    if (!self::exportData($instance->network_username, $instance->network)) {
                        return $this->generateView();
                    }
                    self::generateZipFile();
                } else {
                    $this->addErrorMessage('Invalid service user');
                }
            } else { //render dropdown and form to get POST['instance_id']
                $owner_dao = DAOFactory::getDAO('OwnerDAO');
                $owner = $owner_dao->getByEmail($this->getLoggedInUser());
                $this->addToView('instances', $instance_dao->getByOwner($owner));
                $this->addInfoMessage('Choose a user to export.');
            }
        }
        return $this->generateView();
    }

    protected function exportData($username, $service) {
        //Add intro to README.txt
        $import_instructions = "To import your data, run the following commands in MySQL after you make two tweaks:

1. In each command, where it says INTO TABLE tu_destination, replace tu_destination with the name of your destination ".
        "table.
2. Replace /your/path/to/data.tmp to your actual file path. Make sure the data.tmp files and their enclosing ".
        "folder have the appropriate permissions for mysql to read the file. (Otherwise you'll get a ".
        "'ERROR 13 (HY000): Can't get stat of' error.)

Commands to run:

";
        try {
            //begin export
            self::appendToReadme($import_instructions);

            //get user id (some export methods need it)
            $user_dao = DAOFactory::getDAO('UserDAO');
            $user = $user_dao->getUserByName($username, $service);
            $user_id = $user->user_id;

            self::exportPostsRepliesRetweetsFavoritesMentions($username, $user_id, $service);
            self::exportFollowsAndFollowers($user_id, $service);
            self::exportCountHistory($user_id, $service);
            return true;
        } catch(Exception $e) {
            $err = $e->getMessage();
            if (preg_match("/Can't create\/write to file/", $err) || preg_match("/Can\'t get stat of/", $err)) {
                // a file open perm issue?
                $this->addToView('mysql_file_perms', true);
            } else {
                $this->addToView('grant_perms', true);
            }
            return false;
        }
    }

    protected function generateZipFile() {
        $zip = new ZipArchive();
        if (file_exists($this->zip_file_full_name)) {
            unlink($this->zip_file_full_name);
        }
        $zip_create_status = @touch($this->zip_file_full_name);
        if ($zip_create_status) {
            unlink($this->zip_file_full_name);
        }
        if ( !$zip_create_status || $zip->open($this->zip_file_full_name, ZIPARCHIVE::CREATE)!==TRUE) {
            throw new Exception("Unable to open backup file for exporting: $this->zip_file_full_name");
        }
        foreach ($this->files_to_zip as $file) {
            $zip->addFile($file['path'],"/".$file['name']);
        }

        $zip_close_status = $zip->close();

        // clean up tmp table files
        foreach ($this->files_to_zip as $file) {
            unlink($file['path']);
        }
        if ($zip_close_status == false) {
            throw new Exception("Unable to create export file. Bad file path?: ".
            $this->zip_file_full_name);
        }

        if ( !headers_sent() ) { // this is so our test don't barf on us
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="'.$this->zip_file_short_name.'"');
            header('Pragma: no-cache');
            header('Expires: 0');
        }
        $fh = fopen($this->zip_file_full_name, "rb");
        if ($fh) {
            while (!feof($fh)) {
                $data = fread($fh, 256);
                echo $data;
                flush();
            }
            fclose($fh);
            unlink($this->zip_file_full_name);
        }
    }

    protected function exportPostsRepliesRetweetsFavoritesMentions($username, $user_id, $service) {
        $post_dao = DAOFactory::getDAO('PostDAO');
        $export_dao = DAOFactory::getDAO('ExportDAO');

        //start with empty export table
        $export_dao->dropExportedPostsTable();

        //user posts
        $export_dao->exportPostsByServiceUser($username, $service);

        //replies and retweets
        $cur_page = 1;
        $total_posts_to_process = $post_dao->getTotalPostsByUser($username, $service);
        $total_pages_to_process = ceil($total_posts_to_process/$this->page_size);
        " pages) authored by ". $username." on ".$service."<br>";
        $total_posts_exported = 0;
        while ($cur_page < $total_pages_to_process ) {
            $posts_to_process = $post_dao->getAllPosts($user_id, $service, $count=$this->page_size, $page=$cur_page);
            $page_posts_exported = $export_dao->exportRepliesRetweetsOfPosts($posts_to_process);
            $cur_page = $cur_page + 1;
            $total_posts_exported = $total_posts_exported + $page_posts_exported;
        }
        //mentions
        $total_mentions_exported = $export_dao->exportMentionsOfServiceUser($username, $service);

        //posts the author has replied to
        $total_replied_to_posts_exported = $export_dao->exportPostsServiceUserRepliedTo($username, $service);

        //export favorites
        $favorites_table_file = FileDataManager::getBackupPath('favorites.tmp');
        $total_favorite_posts_exported = $export_dao->exportFavoritesOfServiceUser($user_id, $service,
        $favorites_table_file);
        $this->files_to_zip[] = array('path'=>$favorites_table_file, 'name'=>'favorites.tmp');

        $import_instructions = "LOAD DATA INFILE '/your/path/to/favorites.tmp' IGNORE INTO TABLE tu_favorites;

";

        //export posts, links, users
        $posts_table_file = FileDataManager::getBackupPath('posts.tmp');
        $links_table_file = FileDataManager::getBackupPath('links.tmp');
        $users_table_file = FileDataManager::getBackupPath('users_from_posts.tmp');
        $export_dao->exportPostsLinksUsersToFile($posts_table_file, $links_table_file, $users_table_file);

        $this->files_to_zip[] = array('path'=>$posts_table_file, 'name'=>'posts.tmp');
        $this->files_to_zip[] = array('path'=>$links_table_file, 'name'=>'links.tmp');
        $this->files_to_zip[] = array('path'=>$users_table_file, 'name'=>'users_from_posts.tmp');

        //export geodata
        $geo_table_file = FileDataManager::getBackupPath('encoded_locations.tmp');
        $export_dao->exportGeoToFile($geo_table_file);
        $this->files_to_zip[] = array('path'=>$geo_table_file, 'name'=>'encoded_locations.tmp');
        $import_instructions .= "LOAD DATA INFILE '/your/path/to/encoded_locations.tmp' IGNORE INTO TABLE ".
        "tu_encoded_locations;

";

        //clean up
        $export_dao->dropExportedPostsTable();

        $import_instructions .= "LOAD DATA INFILE '/your/path/to/posts.tmp' IGNORE INTO TABLE tu_posts (".
        $export_dao->getExportFields('posts') .");

";
        $import_instructions .= "LOAD DATA INFILE '/your/path/to/links.tmp' IGNORE INTO TABLE tu_links (".
        $export_dao->getExportFields('links') .");

";
        $import_instructions .= "LOAD DATA INFILE '/your/path/to/users_from_posts.tmp' IGNORE INTO TABLE tu_users (".
        $export_dao->getExportFields('users') .");

";
        self::appendToReadme($import_instructions);
    }

    protected function appendToReadme($text) {
        $handle = fopen($this->readme_file, "a");
        fwrite($handle, $text);
        fclose($handle);
    }

    protected function exportFollowsAndFollowers($user_id, $network) {
        $follows_table_file = FileDataManager::getBackupPath('follows.tmp');
        $users_followers_table_file = FileDataManager::getBackupPath('users_followers.tmp');
        $users_followees_table_file = FileDataManager::getBackupPath('users_followees.tmp');

        $export_dao = DAOFactory::getDAO('ExportDAO');
        $export_dao->exportFollowsUsersToFile($user_id, $network, $follows_table_file, $users_followers_table_file,
        $users_followees_table_file);

        $this->files_to_zip[] = array('path'=>$follows_table_file, 'name'=>'follows.tmp');
        $this->files_to_zip[] = array('path'=>$users_followers_table_file, 'name'=>'users_followers.tmp');
        $this->files_to_zip[] = array('path'=>$users_followees_table_file, 'name'=>'users_followees.tmp');

        $import_instructions = "LOAD DATA INFILE '/your/path/to/follows.tmp' IGNORE INTO TABLE tu_follows (".
        $export_dao->getExportFields('follows') .");

";
        $import_instructions .= "LOAD DATA INFILE '/your/path/to/users_followers.tmp' IGNORE INTO TABLE tu_users (".
        $export_dao->getExportFields('users') .");

";
        $import_instructions .= "LOAD DATA INFILE '/your/path/to/users_followees.tmp' IGNORE INTO TABLE tu_users (".
        $export_dao->getExportFields('users') .");

";
        self::appendToReadme($import_instructions);
    }

    protected function exportCountHistory($user_id, $network) {
        //just the max of each day's count
        $count_history_table_file = FileDataManager::getBackupPath('count_history.tmp');

        $export_dao = DAOFactory::getDAO('ExportDAO');
        $export_dao->exportCountHistoryToFile($user_id, $network, $count_history_table_file);

        $this->files_to_zip[] = array('path'=>$count_history_table_file, 'name'=>'count_history.tmp');

        $import_instructions = "LOAD DATA INFILE '/your/path/to/count_history.tmp' ";
        $import_instructions .= "IGNORE INTO TABLE tu_count_history;

";
        self::appendToReadme($import_instructions);
    }
}
