<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.ExportMySQLDAO.php
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
 * Export MySQL Data Access Object
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

class ExportMySQLDAO extends PDODAO implements ExportDAO {
    /**
     * Name of the temporary posts export table.
     * @var str
     */
    static $exported_posts_table_name = 'posts_tmp';
    /**
     * Name of the temporary follows export table.
     * @var str
     */
    static $exported_follows_table_name = 'follows_tmp';

    public function createExportedPostsTable() {
        $q = "CREATE TABLE #prefix#".self::$exported_posts_table_name." LIKE #prefix#posts;";
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $stmt = $this->execute($q);
        $q = "ALTER TABLE #prefix#".self::$exported_posts_table_name." DROP  id";
        $stmt = $this->execute($q);
        return $this->doesExportedPostsTableExist();
    }

    public function doesExportedPostsTableExist() {
        $q = "SHOW TABLES LIKE '#prefix#" . self::$exported_posts_table_name . "'";
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $stmt = $this->execute($q);
        $tables = $this->getDataRowAsArray($stmt);
        return !empty($tables);
    }

    public function dropExportedPostsTable() {
        if ( self::doesExportedPostsTableExist() ) {
            if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
            $q = "DROP TABLE #prefix#".self::$exported_posts_table_name;
            $stmt = $this->execute($q);
        }
        return !$this->doesExportedPostsTableExist();
    }

    public function createExportedFollowsTable() {
        $q = "CREATE TABLE #prefix#".self::$exported_follows_table_name." LIKE #prefix#follows;";
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $stmt = $this->execute($q);
        return $this->doesExportedFollowsTableExist();
    }

    public function doesExportedFollowsTableExist() {
        $q = "SHOW TABLES LIKE '#prefix#" . self::$exported_follows_table_name . "'";
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $stmt = $this->execute($q);
        $tables = $this->getDataRowAsArray($stmt);
        return !empty($tables);
    }

    public function dropExportedFollowsTable() {
        if ( self::doesExportedFollowsTableExist() ) {
            if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
            $q = "DROP TABLE #prefix#".self::$exported_follows_table_name;
            $stmt = $this->execute($q);
        }
        return !$this->doesExportedFollowsTableExist();
    }

    public function exportPostsByServiceUser($username, $service) {
        if ( !self::doesExportedPostsTableExist() ) {
            self::createExportedPostsTable();
        }
        //select all-but-id into posts_to_export from posts where service user is the author
        $q = "INSERT IGNORE INTO #prefix#".self::$exported_posts_table_name." SELECT ";
        $q .= self::getExportFields('posts') . " ";
        $q .= "FROM #prefix#posts WHERE ";
        $q .= "author_username = :author_username AND network = :network";
        $vars = array(
            ':author_username'=>$username,
            ':network'=>$service
        );

        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $stmt = $this->execute($q, $vars);
        return $this->getUpdateCount($stmt);
    }

    public function exportRepliesRetweetsOfPosts($posts_to_process) {
        if ( !self::doesExportedPostsTableExist() ) {
            self::createExportedPostsTable();
        }
        $total_posts_exported = 0;
        foreach ($posts_to_process as $post) {
            $q = "INSERT IGNORE INTO #prefix#".self::$exported_posts_table_name." SELECT ";
            $q .= self::getExportFields('posts'). " ";
            $q .= "FROM #prefix#posts WHERE ";
            $q .= "in_reply_to_post_id = :post_id AND network=:network;";
            $vars = array("post_id"=>$post->post_id, "network"=>$post->network);
            $stmt = $this->execute($q, $vars);
            $total_posts_exported = $total_posts_exported + $this->getUpdateCount($stmt);

            $q = "INSERT IGNORE INTO #prefix#".self::$exported_posts_table_name." SELECT ";
            $q .= self::getExportFields('posts'). " ";
            $q .= "FROM #prefix#posts WHERE ";
            $q .= "in_retweet_of_post_id = :post_id AND network=:network;";
            $vars = array("post_id"=>$post->post_id, "network"=>$post->network);
            $stmt = $this->execute($q, $vars);
            $total_posts_exported = $total_posts_exported + $this->getUpdateCount($stmt);
        }
        return $total_posts_exported;
    }

    public function exportMentionsOfServiceUser($username, $service) {
        if ( !self::doesExportedPostsTableExist() ) {
            self::createExportedPostsTable();
        }
        $author_username = '@'.$username;
        //select all-but-id into posts_to_export from posts where service username is mentioned
        $q = "INSERT IGNORE INTO #prefix#".self::$exported_posts_table_name." SELECT ";
        $q .= self::getExportFields('posts') . " ";
        $q .= "FROM #prefix#posts WHERE ";
        $q .= "network = :network AND ";
        if ( strlen($username) > PostMySQLDAO::FULLTEXT_CHAR_MINIMUM ) {
            $q .= "MATCH (`post_text`) AGAINST(:author_username IN BOOLEAN MODE) ";
        } else {
            $author_username = '%'.$author_username .'%';
            $q .= "post_text LIKE :author_username ";
        }
        $vars = array(
            ':author_username'=>$author_username,
            ':network'=>$service
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $stmt = $this->execute($q, $vars);
        return $this->getUpdateCount($stmt);
    }

    public function exportPostsServiceUserRepliedTo($username, $service) {
        if ( !self::doesExportedPostsTableExist() ) {
            self::createExportedPostsTable();
        }
        $page = 1;
        $page_size = 500;
        $total_posts_inserted = 0;
        $posts_to_insert = self::getRepliedToPostIDs($username, $service, $page, $page_size);
        while (count($posts_to_insert) > 0 ) {
            foreach ($posts_to_insert as $post) {
                $q = "INSERT IGNORE INTO #prefix#".self::$exported_posts_table_name." SELECT ";
                $q .= self::getExportFields('posts') . " ";
                $q .= "FROM #prefix#posts WHERE ";
                $q .= "network = :network AND post_id=:post_id;";
                $vars = array(
                ':post_id'=>$post['post_id'],
                ':network'=>$service
                );
                if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
                $stmt = $this->execute($q, $vars);
                $total_posts_inserted = $total_posts_inserted + $this->getUpdateCount($stmt);
            }
            $page = $page+1;
            $posts_to_insert = self::getRepliedToPostIDs($username, $service, $page, $page_size);
        }
        return $total_posts_inserted;
    }

    private function getRepliedToPostIDs($username, $network, $page, $page_size) {
        $page = $page - 1;
        $start_on = $page * $page_size;
        $q = "SELECT in_reply_to_post_id as post_id FROM #prefix#posts WHERE ";
        $q .= "author_username = :author_username AND network=:network AND in_reply_to_post_id IS NOT null ";
        $q .= "LIMIT ".$start_on.", ".$page_size;
        $vars = array(
            ':author_username'=>$username,
            ':network'=>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $stmt = $this->execute($q, $vars);
        return $this->getDataRowsAsArrays($stmt);
    }

    public function exportFavoritesOfServiceUser($user_id, $service, $favorites_file) {
        if ( !self::doesExportedPostsTableExist() ) {
            self::createExportedPostsTable();
        }
        $q = "SELECT * INTO OUTFILE '$favorites_file' FROM #prefix#favorites WHERE fav_of_user_id = :user_id ".
        "AND network = :network;";
        $vars = array(
            ':user_id'=>$user_id,
            ':network'=>$service
        );
        $stmt = $this->execute($q, $vars);

        $q = "SELECT post_id FROM #prefix#favorites WHERE fav_of_user_id = :user_id AND network = :network;";
        $stmt = $this->execute($q, $vars);
        $fav_ids = $this->getDataRowsAsArrays($stmt);
        $total_favs_exported = 0;
        foreach ($fav_ids as $post) {
            $q = "INSERT IGNORE INTO #prefix#".self::$exported_posts_table_name." SELECT ";
            $q .= self::getExportFields('posts'). " ";
            $q .= "FROM #prefix#posts WHERE ";
            $q .= "post_id = :post_id AND network = :network";
            $vars = array(
            ':post_id'=>$post['post_id'],
            ':network'=>$service
            );
            $stmt = $this->execute($q, $vars);
            $total_favs_exported = $total_favs_exported + $this->getUpdateCount($stmt);
        }
        return $total_favs_exported;
    }

    public function exportPostsLinksUsersToFile($posts_file, $links_file, $users_file) {
        if (file_exists($posts_file)) {
            unlink($posts_file);
        }
        if (file_exists($links_file)) {
            unlink($links_file);
        }
        if (file_exists($users_file)) {
            unlink($users_file);
        }
        if ( !self::doesExportedPostsTableExist() ) {
            self::createExportedPostsTable();
        }
        $q = "SELECT * INTO OUTFILE '$posts_file' FROM #prefix#".self::$exported_posts_table_name;
        $stmt = $this->execute($q);

        $q = "SELECT ".$this->getExportFields('links', 'l')." INTO OUTFILE '$links_file' FROM #prefix#links l ";
        $q .= "INNER JOIN #prefix#posts p ON l.post_key = p.id;";
        $stmt = $this->execute($q);

        $q = "SELECT DISTINCT ".$this->getExportFields('users', 'u')." INTO OUTFILE '$users_file' ";
        $q .= "FROM #prefix#users u INNER JOIN #prefix#".self::$exported_posts_table_name.
        " p ON p.author_user_id = u.user_id AND p.network = u.network;";
        $stmt = $this->execute($q);

    }

    public function exportFollowsUsersToFile($user_id, $network, $follows_file, $users_followers_file,
    $users_followees_file) {
        if (file_exists($follows_file)) {
            unlink($follows_file);
        }
        if (file_exists($users_followers_file)) {
            unlink($users_followers_file);
        }
        if (file_exists($users_followees_file)) {
            unlink($users_followees_file);
        }

        self::createExportedFollowsTable();
        //export follows to temp table
        $q = "INSERT IGNORE INTO #prefix#".self::$exported_follows_table_name." SELECT * FROM #prefix#follows ";
        $q .= "WHERE network=:network AND user_id = :user_id;";
        $vars = array(
            ':user_id'=>$user_id,
            ':network'=>$network
        );
        $stmt = $this->execute($q, $vars);

        //export followees to temp table
        $q = "INSERT IGNORE INTO #prefix#".self::$exported_follows_table_name." SELECT * FROM #prefix#follows ";
        $q .= "WHERE network=:network AND follower_id = :user_id;";
        $vars = array(
            ':user_id'=>$user_id,
            ':network'=>$network
        );
        $stmt = $this->execute($q, $vars);

        //export temp table to file
        $q = "SELECT * INTO OUTFILE '$follows_file' FROM #prefix#".self::$exported_follows_table_name." ";
        $stmt = $this->execute($q, $vars);

        //export users join on temp table followers
        $q = "SELECT DISTINCT ".$this->getExportFields('users', 'u'). " FROM #prefix#users u ";
        $q .= "INNER JOIN #prefix#".self::$exported_follows_table_name.
        " f ON f.network = u.network AND f.follower_id = u.user_id ";
        $q .= "INTO OUTFILE '$users_followees_file' ";
        $vars = array(
            ':user_id'=>$user_id,
            ':network'=>$network
        );
        $stmt = $this->execute($q, $vars);

        //export users join on temp table followers
        $q = "SELECT DISTINCT ".$this->getExportFields('users', 'u'). " FROM #prefix#users u ";
        $q .= "INNER JOIN #prefix#".self::$exported_follows_table_name.
        " f ON f.network = u.network AND f.user_id = u.user_id ";
        $q .= "INTO OUTFILE '$users_followers_file' ";
        $vars = array(
            ':user_id'=>$user_id,
            ':network'=>$network
        );
        $stmt = $this->execute($q, $vars);

        //drop temp table
        self::dropExportedFollowsTable();
    }

    public function exportCountHistoryToFile($user_id, $network, $file) {
        if (file_exists($file)) {
            unlink($file);
        }
        $q = "SELECT * INTO OUTFILE '$file' FROM #prefix#count_history WHERE ";
        $q .= "network=:network AND network_user_id=:user_id GROUP by date;";
        $vars = array(
            ':user_id'=>$user_id,
            ':network'=>$network
        );
        $stmt = $this->execute($q, $vars);
    }

    public function exportGeoToFile($file) {
        if (file_exists($file)) {
            unlink($file);
        }
        $q = "SELECT * INTO OUTFILE '$file' FROM #prefix#encoded_locations;";
        $stmt = $this->execute($q);
    }

    public function getExportFields($table_name, $prefix='') {
        $q = "DESCRIBE #prefix#".$table_name.";";
        $stmt = $this->execute($q);
        $fields = $this->getDataRowsAsArrays($stmt);
        $fields_string = '';
        foreach ($fields as $field) {
            if ($fields_string != '') {
                $fields_string .= ", ";
            }
            if ($field['Field'] != 'id') {
                $fields_string .= $prefix.(($prefix!='')?".":"").$field['Field'];
            }
        }
        return $fields_string;
    }
}
