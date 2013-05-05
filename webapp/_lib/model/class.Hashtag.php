<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Hashtag.php
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
 * Hashtag class
 *
 * This class represents a hashtag.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Eduard Cucurella
 * @author Eduard Cucurella <eduard[dot]cucu[dot]cat[at]gmail[dot]com>
 *
 */
class Hashtag {
    /**
     * @var int Internal unique ID.
     */
    var $id;
    /**
     * @var str Hash tag, i.e., #thinkup.
     */
    var $hashtag;
    /**
     * @var str The network this hashtag appeared on in lower-case, e.g. twitter or facebook.
     */
    var $network;
    /**
     * @var int A count of times this hashtag was captured.
     */
    var $count_cache;
    public function __construct($row = false) {
        if ($row) {
            $this->id = $row['id'];
            $this->hashtag = $row['hashtag'];
            $this->network = $row['network'];
            $this->count_cache = $row['count_cache'];
        }
    }
}