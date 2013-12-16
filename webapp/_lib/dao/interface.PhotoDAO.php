<?php
/**
 *
 * ThinkUp/webapp/_lib/dao/interface.PhotoDAO.php
 *
 * Copyright (c) 2013 Nilaksh Das
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
 * Photo Data Access Object Interface
 *
 * @author Nilaksh Das <nilakshdas[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Nilaksh Das
 *
 */
interface PhotoDAO {
    /**
     * Insert photo given an array of values
     *
     * Values expected:
     * <code>
     *  $vals['post_id']
     *  $vals['user_name']
     *  $vals['full_name']
     *  $vals['avatar']
     *  $vals['user_id']
     *  $vals['post_text']
     *  $vals['pub_date']
     *  $vals['source']
     *  $vals['network']
     *  $vals['is_protected']
     *  $vals['is_reply_by_friend']
     *  $vals['photo_page']
     *  $vals['standard_resolution_url']
     * </code>
     * Note: All fields which represent boolean values--fields whose names start with is_--should be an
     * int equal to either 1 or 0.
     *
     * @param array $vals see above
     * @return int|bool New insert id or false if not inserted
     */
    public function addPhoto($vals);

    /**
     * Get photo by ID
     * @param str $post_id
     * @param str $network
     * @return Photo Photo with the given post_id, null if photo doesn't exist
     */
    public function getPhoto($post_id, $network);
}