<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.HashtagDAO.php
 *
 * Copyright (c) 2011-2012 Amy Unruh
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
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
}
