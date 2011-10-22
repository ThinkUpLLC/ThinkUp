<?php
/**
 *
 * ThinkUp/webapp/_lib/model/inteface.DomainMetricsDAO.php
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
 * Domain Metrics Data Access Object interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011 SwellPath, Inc.
 * @author Christian G. Warden <cwarden[at]xerus[dot]org>
 *
 */
interface DomainMetricsDAO  {

    /**
     * Insert or update a count
     * @param int $instance_id
     * @param str $domain_id
     * @param str $date
     * @param int $count
     * @return int Total inserted/updated
     */
    public function upsert($instance_id, $date, $like_views, $likes);

    /**
     * Get external referral count history for a page
     * @param int $network_user_id
     * @param str $network
     * @param str $units 'DAY', 'WEEK', 'MONTH'
     * @param int $periods_limit Defaults to 10
     * @return array $history, $percentages
     */
    public function getWidgetHistory($network_user_id, $network, $units, $periods_limit=10);

    /**
     * Get timestamp of earliest referral count recorded in database
     * @param int $instance_id
     * @return int unix timestamp
     */
    public function getEarliest($instance_id);

    /**
     * Get timestamp of latest referral count recorded in database
     * @param int $instance_id
     * @return int unix timestamp
     */
    public function getLatest($instance_id);
}
