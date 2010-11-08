<?php
/*
 Plugin Name: Twitter
 Plugin URI: http://github.com/ginatrapani/thinkup/tree/master/webapp/plugins/twitter/
 Description: Crawler plugin fetches data from Twitter.com for the authorized user.
 Icon: assets/img/twitter_icon.png
 Version: 0.01
 Author: Gina Trapani
 */

/**
 *
 * ThinkUp/webapp/plugins/twitter/controller/twitter.php
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
 */
/**
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 */
$config = Config::getInstance();
//@TODO: For the testing sake, check if mock class has already been loaded
//@TODO: Figure out a better way to do this
if (!class_exists('TwitterOAuth')) {
    Utils::defineConstants();
    require_once THINKUP_WEBAPP_PATH.'_lib/extlib/twitteroauth/twitteroauth.php';
}

$webapp = Webapp::getInstance();
$webapp->registerPlugin('twitter', 'TwitterPlugin');

$crawler = Crawler::getInstance();
$crawler->registerCrawlerPlugin('TwitterPlugin');
