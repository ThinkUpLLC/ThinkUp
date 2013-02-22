<?php
/**
 *
 * ThinkUp/webapp/plugins/twitterrealtime/model/class.StreamMessageQueue.php
 *
 * Copyright (c) 2011-2013 Mark Wilkie
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
 * Abstract StreamMessageQueue class
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright Mark Wilkie
 * @author Mark Wilkie
 */
abstract class StreamMessageQueue {
    /**
     * @var StreamProcDAO doa to store last reported queue time
     */
    protected $stream_proc_dao;
    /**
     * @var int last reported queue time
     */
    protected $last_report = 0;

    public function __construct() {
        $this->stream_proc_dao = DAOFactory::getDAO('StreamProcDAO');
        $this->last_report = 0; // last_report for this run default set to 0
    }

    /**
     * Enqueue data pulled in via the streaming API/Phirehose
     *
     * @param string $status
     */
    public abstract function enqueueStatus($status);

    /**
     * Process queued stream data
     * @return string $json_status
     */
    public abstract function processStreamData();

    /**
     *
     */
    public function setLastReport($email, $instance_id) {
        $ts = time();
        if ($ts >= $this->last_report + 60) {
            // if at least 1 min has passed, update the 'heartbeat' timestamp in the database.
            // This information is used to determine whether the stream is alive.
            $this->stream_proc_dao->reportOwnerProcessActive($email, $instance_id);
            $this->last_report = $ts; // update time of last status received
        }
    }
}