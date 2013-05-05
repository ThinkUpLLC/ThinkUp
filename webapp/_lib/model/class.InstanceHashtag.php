<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.InstanceHashtag.php
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
 * InstanceHashtag class
 *
 * This class represents an instance hashtag
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Eduard Cucurella
 * @author Eduard Cucurella <eduard[dot]cucu[dot]cat[at]gmail[dot]com>
 *
 */
class InstanceHashtag {
    /**
     * @var int Internal unique ID.
     */
    var $id;
    /**
     * @var int Instance ID.
     */
    var $instance_id;
    /**
     * @var int Instance ID.
     */
    var $hashtag_id;
    /**
     * @var str Last network post ID.
     */
    var $last_post_id;
    /**
     * @var str Earliest network post ID.
     */
    var $earliest_post_id;
    public function __construct($row = false) {
        if ($row) {
            $this->id = $row['id'];
            $this->instance_id = $row['instance_id'];
            $this->hashtag_id = $row['hashtag_id'];
            $this->last_post_id = $row['last_post_id'];
            $this->earliest_post_id = $row['earliest_post_id'];
        }
    }
}