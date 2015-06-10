<?php
/**
 *
 * ThinkUp/webapp/plugins/instagram/model/class.InstagramInstance.php
 *
 * Copyright (c) 2015 Gina Trapani
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2015 Gina Trapani
 *
 * Instagram Instance
 *
 * Instagram plugin's instance metadata.
 */
class InstagramInstance extends Instance {
    /**
     * @var int Internal unique ID.
     */
    var $id;
    /**
     * @var str Follower fetch cursor.
     */
    var $followed_by_next_cursor;
    /**
     * @var str Friend fetch cursor.
     */
    var $follows_next_cursor;
    /**
     * @var str Likes fetch cursor.
     */
    var $next_max_like_id;
    public function __construct($row = false) {
        parent::__construct($row);
        if ($row) {
            $this->id = $row['id'];
            $this->followed_by_next_cursor = $row['followed_by_next_cursor'];
            $this->follows_next_cursor = $row['follows_next_cursor'];
            $this->next_max_like_id = $row['next_max_like_id'];
        }
    }
}
