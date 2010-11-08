<?php
/*
 Plugin Name: Expand URLs
 Plugin URI: http://github.com/ginatrapani/thinkup/tree/master/webapp/plugins/expandurls/
 Description: Expands shortened links.
 Icon: assets/img/plugin_icon.png
 Version: 0.01
 Author: Gina Trapani
 */

/**
 *
 * ThinkUp/webapp/plugins/expandurls/controller/expandurls.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 */

$webapp = Webapp::getInstance();
$webapp->registerPlugin('expandurls', 'ExpandURLsPlugin');

$crawler = Crawler::getInstance();
$crawler->registerCrawlerPlugin('ExpandURLsPlugin');
