<?php
/**
 *
 * ThinkUp/webapp/plugins/twitterrealtime/model/ConsumerUserStream.php
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

require_once THINKUP_WEBAPP_PATH . 'plugins/twitterrealtime/extlib/phirehose/lib/UserstreamPhirehose.php';
require_once THINKUP_WEBAPP_PATH . 'plugins/twitterrealtime/model/class.StreamMessageQueueFactory.php';

class ConsumerUserStream extends UserstreamPhirehose {
    /**
     * @var string
     */
    protected $email;
    /**
     * @var int
     */
    protected $instance_id;

    public function __construct($username, $password) {
        // Call parent constructor
        return parent::__construct($username, $password);
    }

    public function setKey($email, $instance_id) {
        $this->email = $email;
        $this->instance_id = $instance_id;
    }

    /**
     * Enqueue each status
     *
     * @param string $status
     */
    public function enqueueStatus($status) {
        // get our queue, redis or mysql depending on plugin config value
        $queue = StreamMessageQueueFactory::getQueue();
        $queue->enqueueStatus($status);
        $queue->setLastReport($this->email, $this->instance_id);
    }

    /**
     * @static
     * @param  $oauth_access_token
     * @param  $oauth_access_token_secret
     * @return ConsumerUserStream|null
     */
    public static function getInstance($oauth_access_token, $oauth_access_token_secret) {
        $sc = null;
        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash('twitter', true); //get cached
        if (isset($options['oauth_consumer_key']) && isset($options['oauth_consumer_secret'])) {
            define('TWITTER_CONSUMER_KEY', $options['oauth_consumer_key']->option_value);
            define('TWITTER_CONSUMER_SECRET', $options['oauth_consumer_secret']->option_value);
            $sc = new ConsumerUserStream($oauth_access_token, $oauth_access_token_secret);
        } else {
            $logger = Logger::getInstance('stream_log_location');
            $logger->logError("Error: could not obtain Twitter app consumer key and secret from Twitter plugin "
            . "settings.", __METHOD__.','.__LINE__);
        }
        return $sc;
    }
}
