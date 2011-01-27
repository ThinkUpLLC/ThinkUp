<?php
/*
 Plugin Name: Facebook
 Plugin URI: http://github.com/ginatrapani/thinkup/tree/master/webapp/plugins/facebook/
 Description: Crawler plugin pulls data from Facebook for authorized users and pages.
 Icon: assets/img/facebook_icon.png
 Version: 0.01
 Author: Gina Trapani
 */
/**
 *
 * ThinkUp/webapp/plugins/facebook/controller/facebook.php
 *
 * Copyright (c) 2009-2011 Gina Trapani
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 */
Utils::defineConstants();
require_once THINKUP_WEBAPP_PATH.'plugins/facebook/extlib/facebook/facebook.php';


$webapp = Webapp::getInstance();
$webapp->registerPlugin('facebook', 'FacebookPlugin');
$webapp->registerPlugin('facebook page', 'FacebookPlugin');

$crawler = Crawler::getInstance();
$crawler->registerCrawlerPlugin('FacebookPlugin');
