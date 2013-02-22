<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.StreamerPlugin.php
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
 * Streamer Plugin interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Amy Unruh
 * @author Amy Unruh
 */
interface StreamerPlugin extends GenericPlugin {
    /**
     * Start up all streams managed by the plugin.
     * @abstract
     * @return void
     */
    public function stream();
    /**
     * Process the streaming data.
     * @abstract
     * @return void
     */
    public function streamProcess();
    /**
     * Shutdown all running streams.
     * @abstract
     * @return void
     */
    public function shutdownStreams();
}
