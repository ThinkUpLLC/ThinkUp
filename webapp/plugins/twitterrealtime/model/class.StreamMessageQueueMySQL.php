<?php
/**
 *
 * ThinkUp/webapp/plugins/twitterrealtime/model/class.StreamMessageQueueMySQL.php
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
 * StreamMessageQueueMySQL class
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright Mark Wilkie
 * @author Mark Wilkie
 */
require_once THINKUP_WEBAPP_PATH.'plugins/twitterrealtime/model/class.StreamMessageQueue.php';

class StreamMessageQueueMySQL extends StreamMessageQueue {
    /**
     * @var int last id
     */
    var $last_id = 0;
    /**
     * @const int
     */
    var $IDMAX = 100000; // point in the ID seq at which auto-increment is reset
    /**
    * Enqueues data pulled in via the streainmg API firehose
    *
    * @param string $json_status
    */
    public function processStreamData() {
        $sd = DAOFactory::getDAO('StreamDataDAO');
        list($id, $data) = $sd->retrieveNextItem();
        if ($id) {
            $this->last_id = $id;
        } else { // empty 'queue'
            if ($this->last_id > $this->IDMAX) {
                $sd->resetID();
                $this->last_id = 0;
            }
        }
        return $data;
    }
    /**
     * Enqueues data pulled in via the streainmg API firehose
     *
     * @param string $status
     */
    public function enqueueStatus($status) {
        // database insert...
        $logger = Logger::getInstance('stream_log_location');
        if (!trim($status)) {
            return;
        }
        $sd = DAOFactory::getDAO('StreamDataDAO');
        $logger->logDebug("inserting into mysql: $status", __METHOD__.','.__LINE__);
        $sd->insertStreamData($status);
    }
}