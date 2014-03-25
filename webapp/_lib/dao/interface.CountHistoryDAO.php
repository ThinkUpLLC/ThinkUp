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
     * Fetch information about a user's history for a given count
     * The result will include trend data, a history array and visualization data, ex:
     * Array(
     *    [history] => Array(
     *            [02/10/2014] => 10
     *            [02/11/2014] => 30
     *            [02/12/2014] => 50
     *            [02/13/2014] => 70
     *            [02/14/2014] => 90
     *   )
     *   [trend] => 16
     *   [milestone] => Array(
     *            [next_milestone] => 100
     *            [will_take] => 1
     *            [units_of_time] => DAY
     *   )
     *   [vis_data] => {"rows":[{"c":[{"v": ... }
     * )
     *
     * @param int $network_user_id Network user id
     * @param str $network Network such as "twitter"
     * @param str $units Time units (DAY, WEEK, MONTH)
     * @param int $limit How many units to go back
     * @param str $before_data Fetch history before this date
     * @param str $type What count (followers, group_memberships)
     * @param int $trend_minimum How man entries we need to calculate a trend (defaults to $limit)
     * @return array Array of history data
     */
    public function getHistory($network_user_id, $network, $group_by, $limit=10, $before_date=null,
                               $type='followers', $trend_minimum=null);
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
