<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Group.php
 *
 * Copyright (c) 2011-2013 SwellPath, Inc.
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
 * Group class
 * (based on User class)
 *
 * This class represents social network groups or lists like @ginatrapani/lifehackers on Twitter.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 SwellPath, Inc.
 * @author Christian G. Warden <cwarden[at]xerus[dot]org>
 *
 */
class Group {
    /**
     * @var int Internal unique ID.
     */
    var $id;
    /**
     * @var str Group/list ID on the source network.
     */
    var $group_id;
    /**
     * @var str Originating network in lower case, i.e., twitter or facebook.
     */
    var $network;
    /**
     * @var str Name of the group or list on the source network.
     */
    var $group_name;
    /**
     * @var bool Whether or not the group is active.
     */
    var $is_active;
    /**
     * @var str First time this group was seen on the originating network.
     */
    var $first_seen;
    /**
     * @var str Last time this group was seen on the originating network.
     */
    var $last_seen;
    /**
     * @var str Non-persistent storage for URL to group
     */
    var $url;
    /**
     * @var str Non-persistent keyword or phrase describing group
     */
    var $keyword;
    public function __construct($val = false) {
        if ($val) {
            if (isset($val['id'])) {
                $this->id = $val['id'];
            }
            $this->group_id = $val['group_id'];
            $this->network = $val['network'];
            $this->group_name = $val['group_name'];
            $this->is_active = PDODAO::convertDBToBool($val['is_active']);
            $this->first_seen = $val['first_seen'];
            $this->last_seen = $val['last_seen'];
        }
    }

    public function setMetadata() {
        if ($this->network == 'twitter') {
            $this->url = 'http://twitter.com/'.substr($this->group_name,1);
            $parts = preg_split("(\/)", $this->group_name);
            $this->keyword = $parts[1];
        }
    }
}