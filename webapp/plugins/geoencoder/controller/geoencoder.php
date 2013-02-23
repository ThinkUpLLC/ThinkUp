<?php
/*
 Plugin Name: GeoEncoder
 Plugin URI: http://github.com/ginatrapani/thinkup/tree/master/webapp/plugins/geoencoder/
 Description: Plot conversations on a Google Map.
 Class: GeoEncoderPlugin
 Icon: assets/img/geoencoder_icon.png
 Version: 0.01
 Author: Ekansh Preet Singh, Mark Wilkie
 */

/**
 *
 * ThinkUp/webapp/plugins/geoencoder/controller/geoencoder.php
 *
 * Copyright (c) 2009-2013 Ekansh Preet Singh, Mark Wilkie
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
 * @author Ekansh Preet Singh <ekanshpreet[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Ekansh Preet Singh, Mark Wilkie
 */

$webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
$webapp_plugin_registrar->registerPlugin('geoencoder', 'GeoEncoderPlugin');

$crawler_plugin_registrar = PluginRegistrarCrawler::getInstance();
$crawler_plugin_registrar->registerCrawlerPlugin('GeoEncoderPlugin');
