<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.MentionDAO.php
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
 * Mention Data Access Object Interface
 * Queries the tu_mentions and tu_mentions_posts tables.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @author Amy Unruh
 */
interface MentionDAO {
    /**
     * Insert multiple mentions in a given post on a network.
     * @param array $user_ids_and_names 'user_id'=>int, 'user_name'=>str
     * @param $post_id
     * @param $author_user_id
     * @param $network
     * @throws Exception
     */
    public function insertMentions(array $user_ids_and_names, $post_id, $author_user_id, $network);
    /**
     * Insert a mention, increment a mentioned user's mention count, and associate the mention with a post.
     * @param int $mention_user_id
     * @param str $mention_user_name
     * @param int $post_id
     * @param int $author_user_id
     * @param str $network
     * @throws Exception
     */
    public function insertMention($mention_user_id, $mention_user_name, $post_id, $author_user_id, $network);
    /**
     * Get the mention information by username and network.
     * @param str $user_name
     * @param str $network
     * @return array $mention row
     */
    public function getMentionInfoUserName($user_name, $network = 'twitter');
    /**
     * Get the mention information by user ID and network.
     * @param int $user_id
     * @param str $network
     * @return array $mention row
     */
    public function getMentionInfoUserID($user_id, $network = 'twitter');
    /**
     * Get mentions_posts array for a given post on a network.
     * @param int $pid Post ID
     * @param str $network
     * @return array mentions_posts rows
     */
    public function getMentionsForPost($pid, $network = 'twitter');
    /**
     * Get mentions_posts array by mention ID.
     * @param int $mid Mention ID
     * @return array mentions_posts rows
     */
    public function getMentionsForPostMID($mid);
}
