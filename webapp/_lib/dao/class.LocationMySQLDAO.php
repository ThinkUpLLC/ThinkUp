<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.LocationMySQLDAO.php
 *
 * Copyright (c) 2009-2013 Ekansh Preet Singh, Mark Wilkie
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
 * Location Data Access Object
 * The data access object for retrieving and saving locations in the ThinkUp database
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Ekansh Preet Singh, Mark Wilkie
 * @author Ekansh Preet Singh <ekanshpreet[at]gmail[dot]com>
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 */

class LocationMySQLDAO extends PDODAO implements LocationDAO  {

    public function getLocation($location) {
        $q = "SELECT  l.* FROM #prefix#encoded_locations l ";
        $q .= " WHERE l.short_name=:name";
        $vars = array(
            ':name'=>$location,
        );
        $ps = $this->execute($q, $vars);
        $row = $this->getDataRowAsArray($ps);
        return $row;
    }

    public function addLocation($vals) {
        $q = "INSERT INTO #prefix#encoded_locations SET short_name = :short_name, full_name = :full_name, ";
        $q .= " latlng = :latlng";
        $vars = array(
            ':short_name'=>$vals['short_name'],
            ':full_name'=>$vals['full_name'],
            ':latlng'=>$vals['latlng']
        );
        $ps = $this->execute($q, $vars);
        $logstatus = "Location (".$vals['short_name'].") added to DB";
        $this->logger->logInfo($logstatus, __METHOD__.','.__LINE__);
        return $this->getUpdateCount($ps);
    }

    public function getAllLocations() {
        $q = "SELECT * FROM #prefix#encoded_locations";
        $ps = $this->execute($q);
        return $this->getDataRowsAsArrays($ps);
    }
}