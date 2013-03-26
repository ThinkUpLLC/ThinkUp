<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.HashtagDAO.php
 *
 * Copyright (c) 2011-2013 Amy Unruh
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
 * Hashtag Data Access Object Interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @author Amy Unruh
 */
interface HashtagDAO {
    /**
     * Insert a new hashtag or update an existing hashtag's count.
     * @param str $hashtag
     * @param int $post_id
     * @param str $network
     * @throws Exception
     */
    public function insertHashtag($hashtag, $post_id, $network);
    /**
     * Insert or update multiple hashtags.
     * @param  array $hashtags
     * @param  int $post_id
     * @param  string $network
     * @throws Exception
     */
    public function insertHashtags(array $hashtags, $post_id, $network);
    /**
     * Get hashtag count by hashtag.
     * @param str $hashtag
     * @param str $network
     * @return array hashtags table array
     */
    public function getHashtagInfoForTag($hashtag, $network = 'twitter');
    /**
     * Get the hashtag(s) which appear in a given post.
     * @param int $post_id
     * @param str $network
     * @return array hashtag_posts table array
     */
    public function getHashtagsForPost($post_id, $network = 'twitter');
    /**
     * Get an array of post IDs where a hashtag appears by hashtag ID.
     * @param int $hashtag_id Hashtag ID
     * @return array hashtag_posts table array
     */
    public function getHashtagsForPostHID($hashtag_id);
	/**
     * Get hashtag by a hashtag id
     * @param int hashtag_id
     * @return hashtag object
     */
    public function getByHashtag($hashtag_id); 
    /**
     * Get hashtag by a hashtag name
     * @param str hashtag_name
     * @return hashtag object
     */
    public function getByHashtagName($hashtag_name);
    /**
     * Get hashtags by a username
     * @param str username
     * @return hashtag object
     */
    public function getByUsername($username);
    /**
     * Delete Hashtag given a hashtag_id
     * @param str $hashtag_id
     * @return  int Total number of affected rows
     */
    public function deleteHashtagByHashtagId($hashtag_id);
    /**
     * Delete Hashtags Posts given a hashtag_id
     * @param str $hashtag_id
     * @return  int Total number of affected rows
     */
    public function deleteHashtagsPostsByHashtagId($hashtag_id);
    /**
     * Insert a new hashtag by name
     * @param str $hashtag
     * @param str $network
     * @return int Hashtag id inserted
     */
    public function insertHashtagByHashtagName($hashtag, $network='twitter');
    /**
     * Check to see if relationship Hashtag Post is in database
     * @param int $hashtag_id
     * @param str $post_id
     * @param str $network
     * @return bool true if hashtag post is in the database
     */
    public function isHashtagPostInDB($hashtag_id, $post_id, $network);    

}
