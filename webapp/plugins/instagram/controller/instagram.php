<?php
/*
 Plugin Name: Instagram
 Plugin URI: http://github.com/ginatrapani/thinkup/tree/master/webapp/plugins/instagram/
 Description: Capture and display Instagram posts.
 Class: InstagramPlugin
 Icon: instagram
 Version: 0.01
 Author: Dimosthenis Nikoudis
 */

/**
 *
 * ThinkUp/webapp/plugins/instagram/controller/instagram.php
 *
 * Copyright (c) 2013 Dimosthenis Nikoudis
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
 * @author Dimosthenis Nikoudis <dnna[at]dnna[dot]gr>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Dimosthenis Nikoudis
 */
if (!class_exists('Instagram\Instagram')) {
    Loader::addSpecialClass('SplClassLoader', 'plugins/instagram/extlib/SplClassLoader.php');
    $loader = new SplClassLoader('Instagram', 'plugins/instagram/extlib');
    $loader->register();
}

$webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
$webapp_plugin_registrar->registerPlugin('instagram', 'InstagramPlugin');

$crawler_plugin_registrar = PluginRegistrarCrawler::getInstance();
$crawler_plugin_registrar->registerCrawlerPlugin('InstagramPlugin');
