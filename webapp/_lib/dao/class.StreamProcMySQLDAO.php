<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.StreamProcMySQLDAO.php
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
class StreamProcMySQLDAO extends PDODAO implements StreamProcDAO {

    public function insertProcessInfo($process_id, $email, $instance_id) {
        if (!isset($process_id) || !isset($email) || !isset($instance_id)) {
            return false;
        }
        $q = "INSERT INTO #prefix#stream_procs ";
        $q .= "(process_id, email, instance_id) VALUES (:process_id, :email, :instance_id)";
        $vars = array(
            ':process_id' => $process_id,
            ':email' => $email,
            ':instance_id' => $instance_id
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $res = $this->getUpdateCount($ps);
        if (!$res) {
            throw new StreamingException("Did not insert stream process.");
        }
        return $res;
    }

    public function getProcessInfo($process_id) {
        $q = "SELECT * FROM #prefix#stream_procs ";
        $q .= "WHERE process_id = :process_id";
        $vars = array(
            ':process_id'=>$process_id
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $row = $this->getDataRowAsArray($ps);
        if (!$row) {
            throw new StreamingException("Process ID $process_id could not be retrieved from the database.");
        }
        return $row;
    }

    public function getProcessInfoForOwner($email, $instance_id) {
        $q = "SELECT * from #prefix#stream_procs ";
        $q .= "WHERE email = :email AND instance_id = :instance_id";
        $vars = array(
            ':email' => $email,
            ':instance_id' => $instance_id
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $row = $this->getDataRowAsArray($ps);
        if (!$row) {
            return null;
        } else {
            return $row;
        }
    }

    public function getProcessInfoForInstance($instance_id) {
        $q = "SELECT * from #prefix#stream_procs ";
        $q .= "WHERE instance_id = :instance_id";
        $vars = array(
            ':instance_id' => $instance_id
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $row = $this->getDataRowAsArray($ps);
        if (!$row) {
            return null;
        } else {
            return $row;
        }
    }

    public function reportProcessActive($process_id) {
        $q = "UPDATE #prefix#stream_procs ";
        $q .= "SET last_report = now() WHERE process_id = :process_id";
        $vars = array(
            ':process_id' => $process_id
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $res = $this->getUpdateCount($ps);
        if (!$res) {
            throw new StreamingException("Record for process ID $process_id not found.");
        }
        return $res;
    }

    public function reportOwnerProcessActive($email, $instance_id) {
        $q = "UPDATE #prefix#stream_procs ";
        $q .= "SET last_report = now() WHERE email = :email AND instance_id = :instance_id ";
        $vars = array(
            ':email' => $email,
            ':instance_id' => $instance_id
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $res = $this->getUpdateCount($ps);
        if (!$res) {
            throw new StreamingException("Stream data process for $email, $instance_id not found.");
        }
        return $res;
    }

    private function getAllStreamProcessesNotIndexed() {
        $q = "SELECT * FROM #prefix#stream_procs ";
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q);
        $rows = $this->getDataRowsAsArrays($ps);
        return $rows;
    }

    public function getAllStreamProcessIDs() {
        $q = "SELECT process_id FROM #prefix#stream_procs ";
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q);
        $rows = $this->getDataRowsAsArrays($ps);
        return $rows;
    }

    public function getAllStreamProcesses() {
        $rows = $this->getAllStreamProcessesNotIndexed();
        $hash = array();
        foreach ($rows as $row) {
            $email = $row['email'];
            $inst_id = $row['instance_id'];
            $key = $email . "_" . $inst_id;
            $hash[$key] = $row;
        }
        return $hash;
    }

    public function deleteProcess($process_id) {
        $q = "DELETE FROM #prefix#stream_procs ";
        $q .= "WHERE process_id = :process_id";
        $vars = array(
            ':process_id' => $process_id
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $res = $this->getUpdateCount($ps);
        if (!$res) {
            throw new StreamingException("Could not delete pid $process_id");
        }
    }
}
