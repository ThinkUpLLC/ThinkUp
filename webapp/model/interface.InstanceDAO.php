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
     * 
     * @param str $network name of network to limit to
     * 
     * @return array with Instance
     */
    public function getAllActiveInstancesStalestFirstByNetwork(
        $network = "twitter"
    );
    
    /**
     * Get all active instances, by last run oldest first
     * 
     * @return array with Instance
     */
    public function getAllInstancesStalestFirst();
    
    /**
     * Gets the instance that ran last.
     * 
     * @return Instance Freshest instance
     */
    public function getInstanceFreshestOne();

    /**
     * Gets the instance that ran the longest time ago
     * 
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

    public function getFreshestByOwnerId($owner_id);

    public function getInstanceOneByLastRun($order);

    public function getByUsername($username);

    public function getByUsernameOnNetwork($username, $network);

    public function getByUserId($network_user_id);

    public function getAllInstances($order = "DESC", $only_active = false, $network = "twitter");

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

    public function save($instance_object, $user_xml_total_posts_by_owner, $logger = false, $api = false);

    public function updateLastRun($id);

    public function isUserConfigured($username);

    public function getByUserAndViewerId($network_user_id, $viewer_id);

    public function getByViewerId($viewer_id);
}