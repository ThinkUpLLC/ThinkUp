<?php
/**
 * ThinkUp/webapp/plugins/twitterrealtime/model/class.StreamCollect2.php
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
 * Stream Collect 2
 * Initiates pulling in Twitter UserStream data from the command line, for asynchronous processing,
 * given valid command line credentials.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright Amy Unruh
 * @author Amy Unruh
 */

class StreamCollect2  {

    var $argc;
    var $argv;

    public function __construct($argc, $argv) {
        $config = Config::getInstance();
        if ($config->getValue('timezone')) {
            date_default_timezone_set($config->getValue('timezone'));
        }

        $this->argc = $argc;
        $this->argv = $argv;
    }

    /**
     * @return string
     */
    public function consume() {
        $logger = Logger::getInstance('stream_log_location');
        $logger->logInfo("in StreamCollect2->consume()", __METHOD__.','.__LINE__);

        if ($this->argc != 4) {
            $logger->logError("error: wrong number of args for StreamCollect2", __METHOD__.','.__LINE__);
            return;
        }
        $instance_id = $this->argv[1];
        $user_email = $this->argv[2];
        $pw = $this->argv[3];

        $output = "";
        $pwd_match = false;

        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail($user_email);
        $passcheck = $owner_dao->getPass($user_email);
        if ($passcheck == $pw) {
            $pwd_match = true;
        } else {
            $logger->logError("ERROR: Incorrect username and password.", __METHOD__.','.__LINE__);
            return;
        }

        if ($pwd_match && $instance_id) {
            $logger->logInfo("working on stream for $user_email", __METHOD__.','.__LINE__);
            if (isset($instance_id)) {
                $logger->logInfo("setting up stream for instance $instance_id", __METHOD__.','.__LINE__);
                $oid = DAOFactory::getDAO('OwnerInstanceDAO');
                $tokens = $oid->getOAuthTokens($instance_id);
                if (isset($tokens['oauth_access_token']) && $tokens['oauth_access_token'] != ''
                && isset($tokens['oauth_access_token_secret']) && $tokens['oauth_access_token_secret'] != '') {
                    $stream = ConsumerUserStream::getInstance($tokens['oauth_access_token'],
                    $tokens['oauth_access_token_secret']);
                    if ($stream) {
                        $stream->setKey($user_email, $instance_id);
                        $stream->consume();
                    } else {
                        return "Error: could not create stream object for instance $instance_id\n";
                    }
                } else {
                    return "Error: could not get oauth information for user $user_email.\n";
                }
            } else {
                return "Could not find a twitter instance for user $user_email.\n";
            }
        }
    }
}
