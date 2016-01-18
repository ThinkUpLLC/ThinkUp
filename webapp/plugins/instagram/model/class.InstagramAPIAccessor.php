<?php
/**
 *
 * ThinkUp/webapp/plugins/instagram/model/class.InstagramAPIAccessor.php
 *
 * Copyright (c) 2013-2016 Dimosthenis Nikoudis
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
 * Instagram API Accessor
 *
 * @author Dimosthenis Nikoudis <dnna[at]dnna[dot]gr>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013-2016 Dimosthenis Nikoudis
 */
class InstagramAPIAccessor {
    /**
     * Currently-authenticated Instagram user
     * @var Instagram/User
     */
    var $current_user;
    /**
     * Instagram object
     * @var Instagram/Instagram
     */
    var $instagram;
    /**
     * @var Logger
     */
    var $logger;
    /**
     * Maximum number of API calls to make per crawl.
     * @var int
     */
    var $max_api_calls;
    /**
     * Total number of API calls made this crawl so far.
     * @var integer
     */
    var $total_api_calls = 0;
    /**
     * Constructor
     * @param str $access_token
     * @return InstagramAPIAccessor
     */
    public function __construct($access_token, $max_api_calls = 2500) {
        $this->max_api_calls = $max_api_calls;
        $this->instagram = new Instagram\Instagram($access_token);
        $this->current_user = $this->instagram->getCurrentUser();
        $this->logger = Logger::getInstance();
    }
    /**
     * Return the currently-authenticated Instagram user.
     * @return Instagram/User
     */
    public function getCurrentUser() {
        return $this->current_user;
    }
    /**
     * Make an API request.
     * @param str $type String representing the endpoint, 'user', 'followers', 'media', 'relationship'
     * @param arr $params API call params
     * @return Object
     */
    public function apiRequest($type, $params = array()) {
        if ($this->total_api_calls >= $this->max_api_calls) {
            $this->logger->logInfo("Throw APICallLimitExceededException", __METHOD__.','.__LINE__);
            throw new APICallLimitExceededException();
        } else {
            $this->total_api_calls++;
            $this->logger->logInfo("Made ".$this->total_api_calls." API calls", __METHOD__.','.__LINE__);
        }
        try {
            if ($type == 'user') {
                return $this->instagram->getUser($params['user_id']);
            } else if ($type == 'followers') {
                return $this->current_user->getFollowers($params);
            } else if ($type == 'friends') {
                return $this->current_user->getFollows($params);
            } else if ($type == 'media') {
                return $this->current_user->getMedia($params);
            } else if ($type == 'relationship') {
                return $this->current_user->getRelationship($params['user_id']);
            } else if ($type == 'likes') {
                return $this->current_user->getLikedMedia($params);
            }
        } catch (Instagram\Core\ApiException $e) {
            if ($e->getMessage() == 'you cannot view this resource') {
                $this->logger->logInfo("Throw APICallPermissionDeniedException: ".$e->getMessage(),
                    __METHOD__.','.__LINE__);
                throw new APICallPermissionDeniedException($e->getMessage());
            } elseif (strpos( $e->getMessage(), 'exceeded the maximum number of requests per hour') !== false) {
                $this->logger->logInfo("Throw APICallLimitExceededException: ".$e->getMessage(),
                    __METHOD__.','.__LINE__);
                throw new APICallLimitExceededException($e->getMessage());
            } else {
                $this->logger->logInfo("Throw APIErrorException: ".$e->getMessage(), __METHOD__.','.__LINE__);
                throw new APIErrorException($e->getMessage());
            }
        }
    }
}
