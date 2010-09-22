<?php
/**
 *
 * ThinkUp/webapp/plugins/flickrthumbnails/controller/flickrthumbnails.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp.
 * 
 * ThinkUp is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * ThinkUp is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with ThinkUp.  If not, see <http://www.gnu.org/licenses/>.
 *
*/
/**
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
*/
/*
 Plugin Name: Flickr Thumbnails
 Plugin URI: http://github.com/ginatrapani/thinkup/tree/master/webapp/plugins/flickr/
 Icon: assets/img/flickr_icon.png
 Description: Expands shortened Flickr photo links to thumbnail locations.
 Version: 0.01
 Author: Gina Trapani
 */

$webapp = Webapp::getInstance();
$webapp->registerPlugin('flickrthumbnails', 'FlickrThumbnailsPlugin');

$crawler = Crawler::getInstance();
$crawler->registerCrawlerPlugin('FlickrThumbnailsPlugin');
