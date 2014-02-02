<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.InstanceDAO.php
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
 * Instance Data Access Object Interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
interface InstanceDAO {
    /**
     * Gets the instance by ID
     * @param in $instance_id
     * @return Instance
     */
    public function get($instance_id);

    /**
     * Get all active instances, by last run oldest first limited to a network
     * @param str $network name of network to limit to
     * @return array with Instance
     */
    public function getAllActiveInstancesStalestFirstByNetwork( $network = "twitter" );

    /**
     * Get active instances, without a known last auth error, for a given owner and network. Will return all instances
     * for a network if owner is an admin.
     * @param Owner $owner
     * @param str $network
     * @return array Instance
     */
    public function getActiveInstancesStalestFirstForOwnerByNetworkNoAuthError( Owner $owner, $network );
    /**
     * Get all active instances, by last run oldest first
     * @return array with Instance
     */
    public function getAllInstancesStalestFirst();

    /**
     * Gets the instance that ran last.
     * @return Instance Freshest instance
     */
    public function getInstanceFreshestOne();

    /**
     * Gets the public instance that got updated last
     * @return Instance Freshest public instance
     */
    public function getInstanceFreshestPublicOne();

    /**
     * Gets the instance that ran the longest time ago
     * @return Instance Stalest Instance
     */
    public function getInstanceStalestOne();

    /**
     * Insert instance
     * @param int $network_user_id
     * @param string $network_username
     * @param string $network - "twitter", "facebook"
     * @param int $viewer_id
     * @return int inserted Instance ID
     */
    public function insert($network_user_id, $network_username, $network = "twitter", $viewer_id = false);

    /**
     * Delete instance
     * @param string $network_username
     * @param string $network - "twitter", "facebook"
     * @return int affected rows
     */
    public function delete($network_username, $network);

    /**
     * Get freshest (most recently updated) instance by owner
     * @param int $owner_id
     * @return Instance
     */
    public function getFreshestByOwnerId($owner_id);

    /**
     * Get by username -- DEPRECATED
     * Use getByUsernameOnNetwork instead
     * This method assumes the network is Twitter
     * @param str $username
     * @param str $network defaults to 'twitter'
     * @return Instance
     */
    public function getByUsername($username, $network = "twitter");

    /**
     * Get by username and network
     * @param str $username
     * @param str $network
     * @return Instance
     */
    public function getByUsernameOnNetwork($username, $network);

    /**
     * Get by user ID and network
     * @param str $network_user_id
     * @param str $network
     * @return Instance
     */
    public function getByUserIdOnNetwork($network_user_id, $network);

    /**
     * Get all instances
     * @param str $order 'DESC' or 'ASC'
     * @param bool $only_active Only active instances
     * @param str $network
     * @return array Instances
     */
    public function getAllInstances($order = "DESC", $only_active = false, $network = "twitter");

    /**
     * Get instance by owner
     * @param Owner $owner
     * @param bool $force_not_admin Override owner's admin status
     * @param bool $only_active Only return active instances
     * @return array Instance objects
     */
    public function getByOwner(Owner $owner, $force_not_admin = false, $only_active=false);

    /**
     * Get instances by owner with authorization status messages.
     * @param Owner $owner
     * @return array Instance objects
     */
    public function getByOwnerWithStatus(Owner $owner);

    /**
     * Get public instances
     * @return array Instance objects
     */
    public function getPublicInstances();

    /**
     * Get instances by owner and network
     * @param Owner $owner
     * @param string $network
     * @param bool $disregard_admin_status
     * @param bool $active_only Return active instances only
     * @return array Instances for the owner (all if admin and !$disregard_admin_status)
     */
    public function getByOwnerAndNetwork($owner, $network, $disregard_admin_status = false, $active_only = false);

    /**
     * Set whether or not an instance is public, i.e., should be included on the public timeline
     * @param int $instance_id
     * @param bool $public
     * @return int number of updated rows (1 if change was successful, 0 if not)
     */
    public function setPublic($instance_id, $public);

    /**
     * Set whether or not an instance is active, i.e., should be crawled
     * @param int $instance_id
     * @param bool $active
     * @return int number of updated rows (1 if change was successful, 0 if not)
     */
    public function setActive($instance_id, $active);

    /**
     * Save instance
     * @param Instance $instance_object
     * @param int $user_xml_total_posts_by_owner
     * @param Logger $logger
     */
    public function save($instance_object, $user_xml_total_posts_by_owner, $logger = false);

    /**
     * Update instance last crawler run to NOW()
     * @param int $id
     */
    public function updateLastRun($id);

    /**
     * Check if a user on a network is configured
     * @param str $username
     * @param str $network
     * @return bool
     */
    public function isUserConfigured($username, $network);

    /**
     * Check if an instance is public.
     * @param str $username
     * @param str $network
     * @return bool
     */
    public function isInstancePublic($username, $network);

    /**
     * Get instance by user and viewer ID
     * @param int $network_user_id
     * @param int $viewer_id
     * @param str $network Defaults to 'facebook'
     */
    public function getByUserAndViewerId($network_user_id, $viewer_id, $network = "facebook");

    /**
     * Get instance by viewer ID on a network
     * @param int $viewer_id
     * @param str $network
     * @return Instance
     */
    public function getByViewerId($viewer_id, $network = "facebook");

    /**
     * Get the number of hours since the freshest instance was updated
     * @return int hours
     */
    public function getHoursSinceLastCrawlerRun();

    /**
     * Update network username
     * @param int instance ID
     * @param str new username
     * @return int Count of updated instances
     */
    public function updateUsername($id, $username);

    /**
     * Sets the post archive loaded value to true
     * @param str network_user_id
     * @param str network
     * @return int Count of updated instances
     */
    public function setPostArchiveLoaded($network_user_id, $network);
}
