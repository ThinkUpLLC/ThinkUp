<?php
/*
 Plugin Name: Twitter Realtime
 Plugin URI: http://github.com/ginatrapani/thinkup/tree/master/webapp/plugins/twitterrealtime/
 Description: Capture and display tweets in realtime.
 Icon: twitter
 Version: 0.01
 Class: TwitterRealtimePlugin
 Author: Amy Unruh, Mark Wilkie
 */

/**
 *
 * ThinkUp/webapp/plugins/twitterrealtime/controller/twitterrealtime.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Amy Unruh
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
 * @author Amy Unruh
 */
$config = Config::getInstance();
//For testing, check if mock class has already been loaded
if (!class_exists('TwitterOAuth')) {
    Loader::addSpecialClass('TwitterOAuth', 'plugins/twitter/extlib/twitteroauth/twitteroauth.php');
}

$webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
$webapp_plugin_registrar->registerPlugin('twitterrealtime', 'TwitterRealtimePlugin');

$streamer_plugin_registrar = PluginRegistrarStreamer::getInstance();
$streamer_plugin_registrar->registerStreamerPlugin('TwitterRealtimePlugin');
