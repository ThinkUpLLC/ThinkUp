<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.InstanceHashtagDAO.php
 *
 * Copyright (c) 2013 Eduard Cucurella
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
 * InstanceHashtag Data Access Object interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Eduard Cucurella
 * @author Eduard Cucurella <eduard[dot]cucu[dot]cat[at]gmail[dot]com>
 *
 */
interface InstanceHashtagDAO {
    /**
     * Get hashtag IDs by an instance ID.
     * @param int $instance_id
     * @return array InstancesHashtag objects
     */
    public function getByInstance($instance_id);
    /**
     * Get unique hashtags saved by multiple instances.
     * @param arr Instance $instances
     * @return arr hashtag and instance fields
     */
    public function getHashtagsByInstances($instances);
    /**
     * Insert an instance hashtag into the data store.
     * @param int $instance_id
     * @param int $hashtag_id
     * @return bool Whether or not insertion was successful
     */
    public function insert($instance_id, $hashtag_id);
    /**
     * Delete an instance hashtag from the data store.
     * @param int $instance_id
     * @param int $hashtag_id
     * @return bool Whether or not deletion was successful
     */
    public function delete($instance_id, $hashtag_id);
    /**
     * Delete all hashtags by instance ID.
     * @param int $instance_id
     * @return bool Whether or not deletion was successful
     */
    public function deleteByInstance($instance_id);
    /**
     * Update last_post_id by instance ID and hashtag ID.
     * @param int $instance_id
     * @param int $hashtag_id
     * @param str $last_post_id
     * @return bool Whether or not update was successful
     */
    public function updateLastPostID($instance_id, $hashtag_id, $last_post_id);
    /**
     * Update earliest_post_id by instance ID and hashtag ID.
     * @param int $instance_id
     * @param int $hashtag_id
     * @param str $earliest_post_id
     * @return bool Whether or not update was successful
     */
    public function updateEarliestPostID($instance_id, $hashtag_id, $earliest_post_id);
    /**
     * Delete instance hashtags by hashtag ID.
     * @param str $hashtag_id
     * @return int Total instance hashtags deleted
     */
    public function deleteInstanceHashtagsByHashtagID($hashtag_id);
    /**
     * Get hashtags by a username and network.
     * @param str $username
     * @param str $network
     * @return array Hashtag objects
     */
    public function getByUsername($username, $network);
    /**
     * Determine if an instance has saved a hashtag given a hashtag ID.
     * @param str $hashtag_id
     * @return bool Whether or not an instance has saved a hashtag.
     */
    public function isHashtagSaved($hashtag_id);
}
