<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.StreamDataMySQLDAO.php
 *
 * Copyright (c) 2011-2013 Amy Unruh
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Amy Unruh
 * @author Amy Unruh
 */
class StreamDataMySQLDAO extends PDODAO implements StreamDataDAO {
    public function insertStreamData($content, $network = 'twitter') {
        if (!$content) {
            return false;
        }
        $q  = "INSERT INTO #prefix#stream_data ";
        $q .= "(data, network) VALUES (:data, :network)";
        $vars = array(
            ':data'  =>$content,
            ':network'  =>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $res = $this->getUpdateCount($ps);
        if (!$res) {
            throw new StreamingException("Error: Could not insert stream data.");
        }
        return $res;
    }

    public function retrieveNextItem($network = 'twitter') {
        $content = null;
        $id = null;

        $q  = "SELECT id, data FROM #prefix#stream_data ";
        $q .= "WHERE network = :network ORDER BY id LIMIT 1";
        $vars = array(
            ':network'  =>$network
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $row = $this->getDataRowAsArray($ps);
        if ($row) {
            $content = $row['data'];
            $id = $row['id'];
            if (!$content) {
                throw new StreamingException("No content returned from stream data table.");
            }
            $this->deleteItem($id);
        }
        return array($id, $content);
    }

    private function deleteItem($id) {
        $q = "DELETE FROM #prefix#stream_data ";
        $q .= "WHERE id = :id";
        $vars = array(
            ':id' => $id
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $res = $this->getUpdateCount($ps);
        if (!$res) {
            throw new StreamingException("Could not delete item $id");
        }
    }

    public function resetID() {
        $q = "ALTER TABLE #prefix#stream_data auto_increment = 1";
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q);
    }
}
