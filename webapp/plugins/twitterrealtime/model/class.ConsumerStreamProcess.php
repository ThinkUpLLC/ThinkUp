<?php
/**
 *
 * ThinkUp/webapp/plugins/twitterrealtime/model/ConsumerStreamProcess.php
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
class ConsumerStreamProcess {
    /**
     * @var TwitterJSONStreamParser
     */
    protected $json_parser;
    /**
     * @var bool
     */
    protected $predis_supported; // whether this v. predis (reqs >=5.3) is supported by this version of php.
    /**
    * @var int
    */
    var $STIME = 1;

    public function __construct() {
        $this->json_parser = new TwitterJSONStreamParser();
    }

    //@TODO do we need this method instead of just calling new ConsumerStreamProcess() directly?
    public static function getInstance() {
        return new ConsumerStreamProcess();
    }

    /**
     * @return void
     */
    public function processStreamData() {
        $queue = StreamMessageQueueFactory::getQueue();
        while (true) {
            try {
                $this->process($queue);
            } catch(Exception $e) {
                error_log('Exception caught, sleeping...:' . $e->getMessage());
            }
        }
    }

    /**
     * our process function
     */
    public function process($queue) {
        $logger = Logger::getInstance('stream_log_location');
        $status = $queue->processStreamData();
        if (trim($status)) {
            $this->json_parser->parseJSON($status);
            $logger->logDebug("retrieved item is: [" . $status . "]", __METHOD__.','.__LINE__);
        } else {
            // $logger->logDebug(" -ran out of list items -- sleeping " . $this->STIME, __METHOD__.','.__LINE__);
            sleep($this->STIME);
        }
    }
}