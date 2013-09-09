<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.CountHistoryDAO.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
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
 * Count History Data Access Object
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
interface CountHistoryDAO  {
    /**
     * Insert a count.
     * @param int $network_user_id
     * @param str $network
     * @param int $count
     * @param str $post_id id of the post this count is for, can be null
     * @param str $type what metric you are storing
     * @param str $date the date the metric occured, defaults to today if no date provided
     * @return int Total inserted
     */
    public function insert($network_user_id, $network, $count, $post_id, $type, $date=null);
    /**
     * Get count history for a user (by default follower count history).
     * @param int $network_user_id
     * @param str $network
     * @param str $group_by 'DAY', 'WEEK', 'MONTH'
     * @param int $limit Defaults to 10
     * @param str $start_date Defaults to null (today)
     * @return array $history, $percentages
     */
    public function getHistory($network_user_id, $network, $group_by, $limit=10, $before_date=null, $type='followers');
    /**
     * Get all the counts for a post by its ID.
     * @param  str $post_id The ID of the post you want counts for
     * @return array Of counts for the post with the specified ID
     */
    public function getCountsByPostID($post_id);
    /**
     * Get all the counts for a post of a certain type e.g. likes, dislikes, views etc.
     * @param  str $post_id The ID of the post to get counts for
     * @param  str $type The type of count you are interested in e.g. likes dislikes, views etc
     * @return array of Counts for the specified post and of the specified type
     */
    public function getCountsByPostIDAndType($post_id, $type);
    /**
     * Sums the counts for a specified post and of a specified type over a specified time period.
     * @param  str $post_id    ID of the post which the count is for
     * @param  str $type       Which count type you want to sum e.g. likes, views etc.
     * @param  str $start_date Oldest counts to include
     * @param  str $end_date   Earliest counts to include
     * @return int Sum of counts requested
     */
    public function sumCountsOverTimePeriod($post_id, $type, $start_date, $end_date);
    /**
     * Get the newest count for the specified type.
     * @param  str $post_id The ID of the post to get counts for
     * @param  str $type The type of count you are interested in e.g. likes dislikes, views etc
     * @return array of Counts for the specified post and of the specified type
     */
    public function getLatestCountByPostIDAndType($post_id, $type);
    /**
     * Update the count of active group memberships based on the group memberships in storage.
     * @param int $network_user_id
     * @param str $network
     * @return int Total inserted
     */
    public function updateGroupMembershipCount($network_user_id, $network);
    /**
     * Get the newest and highest count for the specified type and network user id
     * @param  str $network_user_id The ID of the user to get counts for
     * @param  str $network The network this count is for
     * @param  str $type The type of count you are interested in e.g. likes dislikes, views etc
     * @return array containing a single Count for the specified type and network user id
     */
    public function getLatestCountByNetworkUserIDAndType($network_user_id, $network, $type);

}
