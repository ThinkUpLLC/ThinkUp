<?php
/**
 * Instance Data Access Object Interface
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
interface InstanceDAO {
    /**
     * Get all active instances, by last run oldest first limited to a network
     * @param str $network name of network to limit to
     * @return array with Instance
     */
    public function getAllActiveInstancesStalestFirstByNetwork( $network = "twitter" );

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
     * @return array Instance objects
     */
    public function getByOwner($owner, $force_not_admin = false);

    /**
     * Get instances by owner and network
     * @param Owner $owner
     * @param string $network
     * @param boolean $disregard_admin_status
     * @return array Instances for the owner (all if admin and !$disregard_admin_status)
     */
    public function getByOwnerAndNetwork($owner, $network, $disregard_admin_status = false);

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
}
