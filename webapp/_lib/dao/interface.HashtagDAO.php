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
     * Get hashtag by content and network.
     * @param str $hashtag
     * @param str $network
     * @return Hashtag
     */
    public function getHashtag($hashtag, $network);
    /**
     * Get hashtag by ID.
     * @param int $hashtag_id
     * @return Hashtag
     */
    public function getHashtagByID($hashtag_id);
    /**
     * Delete hashtag by ID.
     * @param int $hashtag_id
     * @return bool Whether or not deletion was successful
     */
    public function deleteHashtagByID($hashtag_id);
    /**
     * Insert hashtag.
     * @param str $hashtag
     * @param str $network
     * @return mixed New hashtag ID or false if insertion was unsuccessful
     */
    public function insertHashtag($hashtag, $network);
}
