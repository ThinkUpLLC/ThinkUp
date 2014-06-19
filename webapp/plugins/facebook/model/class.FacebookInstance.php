<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/model/class.FacebookInstance.php
 *
 * Copyright (c) 2011-2013 Gina Trapani
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
 * @author Chris Moyer <chris[at]inarow[dot]net>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014 Chris Moyer
 *
 * Facebook Instance
 *
 * Facebook plugin's instance metadata.
 */
class FacebookInstance extends Instance {
    /**
     * @var int Internal unique ID.
     */
    var $id;
    /**
     * @var str When the associated facebook profle was last updated
     */
    var $profile_updated;
    public function __construct($row = false) {
        parent::__construct($row);
        if ($row) {
            $this->id = $row['id'];
            $this->profile_updated = $row['profile_updated'];
        }
    }
}
