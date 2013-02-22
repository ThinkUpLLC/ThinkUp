<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.StreamProcDAO.php
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
 * Stream Process Data Access Object Interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @author Amy Unruh
 */
interface StreamProcDAO {
    /**
     * Insert stream process details.
     * @param int $process_id
     * @param str $email
     * @param int $instance_id
     * @return mixed false if nothing inserted, total rows inserted otherwise
     * @throws StreamingException
     */
    public function insertProcessInfo($process_id, $email, $instance_id);
    /**
     * Get stream process details by ID.
     * @param int $process_id
     * @return array stream_data table row array
     * @throws StreamingException
     */
    public function getProcessInfo($process_id);
    /**
     * Get the process details run by a given owner.
     * @param str $email
     * @param int $instance_id
     * @return array stream_data table row array
     */
    public function getProcessInfoForOwner($email, $instance_id);
    /**
     * Get the process details for a given instance.
     * @param int $instance_id
     * @return array stream_data table row array
     */
    public function getProcessInfoForInstance($instance_id);
    /**
     * Update the report time to now.
     * @param int $process_id
     * @return int Number of rows updated
     * @throws StreamingException
     */
    public function reportProcessActive($process_id);
    /**
     * Update the report time for an owner's process to now.
     * @param str $email
     * @param int $instance_id
     * @throws StreamingException
     * @return Number of rows updated
     */
    public function reportOwnerProcessActive($email, $instance_id);
    /**
     * Get all process IDs in the table.
     * @return array $row['process_id']
     */
    public function getAllStreamProcessIDs();
    /**
     * Get all stream processes as a hash indexed by instance_id and owner email.
     * @return array $process['1-you@example.com'][$row] for process ID 1 run by you@example.com
     */
    public function getAllStreamProcesses();
    /**
     * Delete a process from the stream_procs table.
     * @param $process_id
     * @throws StreamingException
     * @returns void
     */
    public function deleteProcess($process_id);
}
