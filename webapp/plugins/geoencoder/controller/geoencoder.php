<?php
/*
 Plugin Name: GeoEncoder
 Plugin URI: http://github.com/ginatrapani/thinkup/tree/master/webapp/plugins/geoencoder/
 Description: Fetches lattitude and longitude points for a post or user's location to plot on a Google Map.
 Icon: assets/img/geoencoder_icon.png
 Version: 0.01
 Author: Ekansh Preet Singh, Mark Wilkie
 */

/**
 *
 * ThinkUp/webapp/plugins/geoencoder/controller/geoencoder.php
 *
 * Copyright (c) 2009-2010 Ekansh Preet Singh, Mark Wilkie
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
 */
/**
 * @author Ekansh Preet Singh <ekanshpreet[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Ekansh Preet Singh, Mark Wilkie
 */

$webapp = Webapp::getInstance();
$webapp->registerPlugin('geoencoder', 'GeoEncoderPlugin');

$crawler = Crawler::getInstance();
$crawler->registerCrawlerPlugin('GeoEncoderPlugin');
