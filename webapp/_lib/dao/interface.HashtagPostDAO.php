<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.HashtagPostDAO.php
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
 * Hashtag Post Data Access Object Interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @author Eduard Cucurella
 */
interface HashtagPostDAO {
    /**
     * Insert a new hashtag or update an existing hashtag's count.
     * @param str $hashtag
     * @param str $post_id
     * @param str $network
     * @return void
     * @throws Exception
     */
    public function insertHashtagPost($hashtag, $post_id, $network);
    /**
     * Insert or update multiple hashtags.
     * @param arr $hashtags
     * @param str $post_id
     * @param str $network
     * @throws Exception
     */
    public function insertHashtagPosts(array $hashtags, $post_id, $network);
    /**
     * Delete hashtags posts given a hashtag ID.
     * @param int $hashtag_id
     * @return int Total number of affected rows
     */
    public function deleteHashtagsPostsByHashtagID($hashtag_id);
    /**
     * Get the hashtag(s) which appear in a given post.
     * @param str $post_id
     * @param str $network
     * @return arr hashtag_posts table array
     */
    public function getHashtagsForPost($post_id, $network);
    /**
     * Get an array of post IDs where a hashtag appears by hashtag ID.
     * @param int $hashtag_id Hashtag ID
     * @return arr hashtag_posts table array
     */
    public function getHashtagPostsByHashtagID($hashtag_id);
    /**
     * Check to see if relationship Hashtag Post is in database.
     * @param int $hashtag_id
     * @param str $post_id
     * @param str $network
     * @return bool true if hashtag post is in the database
     */
    public function isHashtagPostInStorage($hashtag_id, $post_id, $network);
    /**
     * Get count of posts published on a given date which contain the saved search.
     * @param int $hashtag_id
     * @param str $for_date Defaults to null, if null then today
     * @return arr Post objects
     */
    public function getTotalPostsByHashtagAndDate($hashtag_id, $for_date=null);
}
