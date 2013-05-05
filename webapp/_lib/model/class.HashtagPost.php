<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.HashtagPost.php
 *
 * Copyright (c) 2012 Eduard Cucurella
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
 * OwnerInstance class
 *
 * This class represents a hashtag post
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012 Eduard Cucurella
 * @author Eduard Cucurella <eduard[dot]cucu[dot]cat[at]gmail[dot]com>
 *
 */
class HashtagPost {
    /**
     * @var str Post ID on a given network..
     */
    var $post_id;
    /**
     * @var int Internal hashtag ID..
     */
    var $hashtag_id;
    /**
     * @var str The network this post appeared on in lower-case, e.g. twitter or facebook.
     */
    var $network;
    public function __construct($row = false) {
        if ($row) {
            $this->post_id = $row['post_id'];
            $this->hashtag_id = $row['hashtag_id'];
            $this->network = $row['network'];
        }
    }
}