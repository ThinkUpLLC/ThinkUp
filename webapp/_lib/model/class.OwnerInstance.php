<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.OwnerInstance.php
 *
 * Copyright (c) 2009-2011 Gina Trapani
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
 * OwnerInstance class
 *
 * This class represents an owner instance
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
class OwnerInstance {
    /*
     * @var int owner id
     */
    var $owner_id;
    /*
     * @var int instance id
     */
    var $instance_id;

    /**
     * Constructor
     * @param int owner id - optional
     * @param int instance id - optional
     */
    public function __construct($oid = null, $iid = null) {
        if ($oid) { $this->owner_id = $oid; }
        if ($iid) { $this->instance_id = $iid; }
    }
}

