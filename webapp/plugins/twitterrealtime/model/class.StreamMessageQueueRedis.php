<?php
/**
 *
 * ThinkUp/webapp/plugins/twitterrealtime/model/class.StreamMessageQueueRedis.php
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
require_once THINKUP_WEBAPP_PATH . 'plugins/twitterrealtime/model/class.StreamMessageQueue.php';

class StreamMessageQueueRedis extends StreamMessageQueue {
    /**
     * @const our redis list name
     */
    const LIST_NAME = 'TU_STREAMING_LIST';
    /**
     * @const int number of times to retry queue
     */
    const RETRIES = 3;
    // const LIST_POP_TIMEOUT = 300;
    /**
    * @var $redis our Predis instance
    */
    var $redis;

    public function __construct() {
        // include redis libs
        parent::__construct();
        require_once THINKUP_WEBAPP_PATH . 'plugins/twitterrealtime/extlib/predis/lib/Predis.php';
        $this->redis = new Predis\Client();
    }

    /**
     * Enqueues data pulled in via the streainmg API firehose
     *
     * @param string $json_status
     */
    public function processStreamData() {
        try {
            $item = $this->redis->lpop(self::LIST_NAME);
        } catch (Exception $e) {
            throw new Exception("Unable to connect to Redis server on localhost. " . $e->getMessage());
        }
        return $item;
    }

    /**
     * Enqueues data pulled in via the streainmg API firehose
     *
     * @param string $status
     */
    public function enqueueStatus($status) {

        $attempts = 0;
        $logger = Logger::getInstance('stream_log_location');
        while ($attempts < self::RETRIES) {
            try {
                $this->redis->rpush(self::LIST_NAME, $status);
                if (trim($status)) {
                    $logger->logDebug("pushing into redis: $status", __METHOD__.','.__LINE__);
                }
                break;
            } catch (Exception $e) {
                $logger->logError("Error - retrying: " . $e->getMessage(), __METHOD__.','.__LINE__);
                $attempts++;
            }
        }
        if ($attempts >= self::RETRIES) {
            $logger->logError("Error: Unable to connect to local redis server after "
            . self::RETRIES . " retries, aborting.", __METHOD__.','.__LINE__);
        }
    }
}