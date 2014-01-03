<?php
/*
 Plugin Name: Foursquare
 Plugin URI: http://github.com/ginatrapani/thinkup/tree/master/webapp/plugins/foursquare/
 Description: Capture and display Foursquare checkins.
 Class: FoursquarePlugin
 Icon: foursquare
 Version: 1
 Author: Aaron Kalair
 */
/**
 *
 * ThinkUp/webapp/plugins/foursquare/controller/foursquare.php
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
 * Foursquare
 * Registers the plugin and crawler.
 *
 * Copyright (c) 2012-2013 Aaron Kaliar
 *
 * @author Aaron Kalair <aaronkalair[at]gmail[dot][com]>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Aaron Kalair
 */
$webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
$webapp_plugin_registrar->registerPlugin('foursquare', 'FoursquarePlugin');

$crawler_plugin_registrar = PluginRegistrarCrawler::getInstance();
$crawler_plugin_registrar->registerCrawlerPlugin('FoursquarePlugin');
