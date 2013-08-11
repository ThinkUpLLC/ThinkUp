<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.ExportDAO.php
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
 * Export Data Access Object interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
interface ExportDAO {
    /**
     * Create a temporary table which matches the existing posts table to export (select *) into.
     * @return bool Whether or not table was successfully created
     */
    public function createExportedPostsTable();
    /**
     * Check if temporary export table exists
     * @return bool Whether or not it exists
     */
    public function doesExportedPostsTableExist();
    /**
     * Drop temporary export table.
     * @return Whether or not table was succesfully dropped
     */
    public function dropExportedPostsTable();
    /**
     * Create a temporary table which matches the existing follows table to export (select *) into.
     * @return bool Whether or not table was successfully created
     */
    public function createExportedFollowsTable();
    /**
     * Check if temporary exported follows table exists
     * @return bool Whether or not it exists
     */
    public function doesExportedFollowsTableExist();
    /**
     * Drop temporary exported follows table.
     * @return Whether or not table was succesfully dropped
     */
    public function dropExportedFollowsTable();
    /**
     * Copy the posts authored by a given service user from the core posts table into the temporary export table.
     * @param str $username
     * @param str $service
     * @return int Number of posts exported
     */
    public function exportPostsByServiceUser($username, $service);
    /**
     * Copy posts from core table to export table which reply to or retweet given posts.
     * @param array $posts_to_process Array of Post objects
     * @return int Number of posts exported
     */
    public function exportRepliesRetweetsOfPosts($posts_to_process);
    /**
     * Copy the posts which mention the service user from the core posts table to the temporary export table.
     * @param str $username
     * @param str $service
     * @return int Number of posts exported
     */
    public function exportMentionsOfServiceUser($username, $service);
    /**
     * Copy the posts the user has replied to from the core posts table to the export table.
     * @param str $username
     * @param str $service
     * @return int Number of posts exported
     */
    public function exportPostsServiceUserRepliedTo($username, $service);
    /**
     * Copy the posts which the service user favorited from the core posts table to the temporary export table;
     * also export the favorites table data to file.
     * @param str $username
     * @param str $service
     * @param str $favorites_file
     * @return int Number of posts exported
     */
    public function exportFavoritesOfServiceUser($user_id, $service, $favorites_file);
    /**
     * Select all the posts in the export table and their links into specified files.
     * @param str $posts_file
     * @param str $links_file
     * @param str $users_file
     */
    public function exportPostsLinksUsersToFile($posts_file, $links_file, $users_file);
    /**
     * Return a list of table fields, not including the auto-increment id field.
     * @param str $table_name
     * @param str $prefix Adds a prefix like l.links to links table
     * @return str Comma-delimited list of fields (without the id field)
     */
    public function getExportFields($table_name, $prefix='');
    /**
     * Export daily follower count for a given user to file.
     * @param $user_id
     * @param $network
     * @param $file
     */
    public function exportCountHistoryToFile($user_id, $network, $file);
    /**
     * Export followers, followees, and user data to file.
     * @param $user_id
     * @param $network
     * @param $follows_file
     * @param $users_file
     */
    public function exportFollowsUsersToFile($user_id, $network, $follows_file, $users_followers_file,
    $users_followees_file);

    /**
     * Export the entire encoded_locations table to file.
     * @param str $file
     */
    public function exportGeoToFile($file);
}
