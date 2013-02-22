<?php
/**
 *
 * ThinkUp/webapp/plugins/twitterrealtime/model/class.StreamMessageQueueFactory.php
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
 * StreamMessageQueueFactory Factory
 *
 * Returns a MessageQueue based on a plugin configuration setting, defaults to a MySQL Queue.
 *
 * Example of use:
 *
 * <code>
 *  StreamingMessageQueueFactory::getQueue();
 * </code>
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Mark Wilkie
 * @author mark Wilkie <mwilkie[at]gmail[dot]com>
 */
class StreamMessageQueueFactory {

    public static $queue = null;

    /*
     * Creates a MessageQueue instance and returns it
     *
     * @returns StreamMessageQueue a stream message queue onject instance
     */
    public static function getQueue() {
        // get stream plugin settings
        $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptionsHash('twitterrealtime', true);
        if (is_null(self::$queue)) {
            if (isset($options['use_redis']) && $options['use_redis']->option_value == "true") {
                // we need a redis queue
                self::$queue = new StreamMessageQueueRedis();
            } else {
                // we need a mysql queue
                self::$queue = new StreamMessageQueueMySQL();
            }
        }
        return self::$queue;
    }
}