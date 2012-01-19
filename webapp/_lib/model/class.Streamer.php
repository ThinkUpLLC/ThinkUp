<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Streamer.php
 *
 * Copyright (c) 2011-2012 Amy Unruh
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
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
 * Streamer
 * Singleton provides hooks for streamer plugins.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2012 Amy Unruh
 * @author Amy Unruh
 */
class Streamer extends PluginHook {
    /**
     * Singleton instance of Streamer object.
     * @var Streamer
     */
    private static $instance;
    /**
     * Get the singleton instance of Streamer
     * @return Streamer
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new Streamer();
        }
        return self::$instance;
    }
    /**
     * Provided only for tests that want to kill object in tearDown()
     */
    public static function destroyInstance() {
        if (isset(self::$instance)) {
            self::$instance = null;
        }
    }
    /**
     * @throws UnauthorizedUserException
     * @return void
     */
    public function stream() {
        if (!Session::isLoggedIn() ) {
            throw new UnauthorizedUserException('You need a valid session to launch the streamer.');
        }
        // @TODO What lock mgmt is necessary?
        $this->emitObjectMethod('stream');
    }
    /**
     * @throws UnauthorizedUserException
     * @return void
     */
    public function streamProcess() {
        if (!Session::isLoggedIn() ) {
            throw new UnauthorizedUserException('You need a valid session to launch the streamer.');
        }
        // @TODO What lock mgmt is necessary?
        $this->emitObjectMethod('streamProcess');
    }
    /**
     * @throws UnauthorizedUserException
     * @return void
     */
    public function shutdownStreams() {
        if (!Session::isLoggedIn() ) {
            throw new UnauthorizedUserException('You need a valid session to launch the streamer.');
        }
        $this->emitObjectMethod('shutdownStreams');
    }
    /**
     * Register streamer plugin.
     * @param str $object_name Name of Streamer plugin object which instantiates the Streamer interface, like
     * "TwitterRealtimePlugin"
     */
    public function registerStreamerPlugin($object_name) {
        $this->registerObjectMethod('stream', $object_name, 'stream');
        $this->registerObjectMethod('streamProcess', $object_name, 'streamProcess');
        $this->registerObjectMethod('shutdownStreams', $object_name, 'shutdownStreams');
    }
}
