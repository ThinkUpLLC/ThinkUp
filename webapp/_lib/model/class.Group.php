<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Group.php
 *
 * Copyright (c) 2011 SwellPath, Inc.
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
 *
 * Group class
 * (based on User class)
 *
 * This class represents social network groups or lists like @ginatrapani/lifehackers on Twitter.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011 SwellPath, Inc.
 * @author Christian G. Warden <cwarden[at]xerus[dot]org>
 *
 */
class Group {
    /**
     *
     * @var int
     */
    var $id;
    /**
     *
     * @var str
     */
    var $group_id;
    /**
     *
     * @var str
     */
    var $group_name;
    /**
     *
     * @var str Default 'twitter'
     */
    var $network;

    /**
     * Constructor
     * @param array $val User key/value pairs
     * @param str $found_in Where user was found
     * @return User New user
     */
    public function __construct($val = false, $found_in = false) {
        if ($val){
            if (isset($val['id'])) {
                $this->id = $val['id'];
            }
            $this->group_id = $val['group_id'];
            $this->group_name = $val['group_name'];
            $this->found_in = $found_in;

            if (isset($val['network'])) {
                $this->network = $val['network'];
            }
        }
    }

}

