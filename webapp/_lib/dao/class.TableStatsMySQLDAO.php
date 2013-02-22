<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.TableStatsMySQLDAO.php
 *
 * Copyright (c) 2009-2013 Mark Wilkie
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Mark Wilkie, Gina Trapani
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 */
class TableStatsMySQLDAO extends PDODAO implements TableStatsDAO {

    public function getTableRowCounts() {
        $q = "show tables";
        $stmt = $this->execute($q);
        $data = $this->getDataRowsAsArrays($stmt);
        $counts = array();
        foreach($data as $table) {
            foreach($table as $key => $value) {
                $q = "SELECT count(*) AS count FROM $value";
                $stmt = $this->execute($q);
                $data = $this->getDataRowsAsArrays($stmt);
                $counts[] = array('table' => $value, 'count' => $data[0]['count']);
            }
        }
        usort($counts, 'TableStatsMySQLDAO::tableCountSort');
        return $counts;
    }

    /**
     * Comparator to sort by count desc
     *
     * For PHP 5.2 compatibility, this method must be public so that we can call usort($plugins,
     * 'TableStatsMySQLDAO::tableCountSort')
     * private/self::tableCountSort doesn't work in PHP 5.2
     */
    public static function tableCountSort($a,$b) {
        return $a['count'] < $b['count'];
    }
}

