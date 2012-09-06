<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.FollowerCountDAO.php
 *
 * Copyright (c) 2009-2012 Gina Trapani
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
 * Follower Count Data Access Object
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2012 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
interface FollowerCountDAO  {

    /**
     * Insert a count
     * @param int $network_user_id
     * @param str $network
     * @param int $count
     * @return int Total inserted
     */
    public function insert($network_user_id, $network, $count);

    /**
     * Get follower count history for a user
     * @param int $network_user_id
     * @param str $network
     * @param str $group_by 'DAY', 'WEEK', 'MONTH'
     * @param int $limit Defaults to 10
     * @return array $history, $percentages
     */
    public function getHistory($network_user_id, $network, $group_by, $limit=10);
}