<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.ShortLink.php
 *
 * Copyright (c) 2012-2013 Gina Trapani
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
 * ShortLink
 *
 * Shortened URLs, potentially many per link object.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */

class ShortLink {
    /**
     * @var int Internal unique ID.
     */
    var $id;
    /**
     * @var int Expanded link ID in links table.
     */
    var $link_id;
    /**
     * @var str Shortened URL.
     */
    var $short_url;
    /**
     * @var int Total number of clicks as reported by shortening service.
     */
    var $click_count;
    /**
     * @var str Last time the click count was updated from the shortening service.
     */
    var $first_seen;
    public function __construct($row = false) {
        if ($row) {
            $this->id = $row['id'];
            $this->link_id = $row['link_id'];
            $this->short_url = $row['short_url'];
            $this->click_count = $row['click_count'];
            $this->first_seen = $row['first_seen'];
        }
    }
}