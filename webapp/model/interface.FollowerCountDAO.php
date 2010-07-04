<?php
/**
 * Follower Count Data Access Object
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
interface FollowerCountDAO  {

    /**
     * Insert a count
     * @param int $network_user_id
     * @param str $network
     * @param int $count
     * @return int Total inserted
     */
    public function insert($network_user_id, $network, $count);

    /**
     * Get follower count history for a user
     * @param int $network_user_id
     * @param str $network
     * @param str $since_date
     * @return array $history['date'],['count']
     */
    public function getHistory($network_user_id, $network, $since_date);
}