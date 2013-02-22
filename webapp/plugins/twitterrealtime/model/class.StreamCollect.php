<?php
/**
 *
 * ThinkUp/webapp/plugins/twitterrealtime/model/class.StreamCollect.php
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
 *
 * Initiates pulling in Twitter UserStream data from the command line, for asynchronous processing,
 * given valid command line credentials.
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright Amy Unruh
 * @author Amy Unruh
 */

class StreamCollect {
    /**
     * @param  $owner
     * @return
     */
    public function consume($owner) {
        // This version will run just for (first) twitter instance of the given (admin) owner
        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $instances = $instance_dao->getByOwnerAndNetwork($owner, 'twitter', true);
        $logger = Logger::getInstance('stream_log_location');
        if (isset($instances[0])) {
            $instance = $instances[0];
            $oid = DAOFactory::getDAO('OwnerInstanceDAO');
            $tokens = $oid->getOAuthTokens($instance->id);
            if (isset($tokens['oauth_access_token']) && $tokens['oauth_access_token'] != ''
            && isset($tokens['oauth_access_token_secret']) && $tokens['oauth_access_token_secret'] != '') {
                $stream = ConsumerUserStream::getInstance($tokens['oauth_access_token'],
                $tokens['oauth_access_token_secret']);
                $stream->consume();
            } else {
                $logger->logError("Error: could not get oauth information for user.", __METHOD__.','.__LINE__);
                return;
            }
        } else {
            $logger->logError("Error: could not get twitter instance for user.", __METHOD__.','.__LINE__);
            return;
        }
    }
}
