<?php
/**
 *
 * ThinkUp/tests/init.tests.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 */
putenv("MODE=TESTS");
require_once 'config.tests.inc.php';

//set up 3 required constants
if ( !defined('THINKUP_ROOT_PATH') ) {
    define('THINKUP_ROOT_PATH', str_replace("\\",'/', dirname(dirname(__FILE__))) .'/');
}

if ( !defined('THINKUP_WEBAPP_PATH') ) {
    define('THINKUP_WEBAPP_PATH', THINKUP_ROOT_PATH . 'webapp/');
}

if ( !defined('TESTS_RUNNING') ) {
    define('TESTS_RUNNING', true);
}

//Register our lazy class loader
require_once THINKUP_WEBAPP_PATH.'_lib/class.Loader.php';

Loader::register(array(
THINKUP_ROOT_PATH . 'tests/',
THINKUP_ROOT_PATH . 'tests/classes/',
THINKUP_ROOT_PATH . 'tests/fixtures/',
THINKUP_WEBAPP_PATH . 'plugins/expandurls/tests/',
THINKUP_WEBAPP_PATH . 'plugins/embedthread/tests/',
THINKUP_WEBAPP_PATH . 'plugins/facebook/tests/',
THINKUP_WEBAPP_PATH . 'plugins/twitter/tests/',
THINKUP_WEBAPP_PATH . 'plugins/twitterrealtime/tests/',
THINKUP_WEBAPP_PATH . 'plugins/geoencoder/tests/',
THINKUP_WEBAPP_PATH . 'plugins/hellothinkup/tests/',
THINKUP_WEBAPP_PATH . 'plugins/googleplus/tests/',
THINKUP_WEBAPP_PATH . 'plugins/foursquare/tests/',
THINKUP_WEBAPP_PATH . 'plugins/insightsgenerator/tests/',
THINKUP_WEBAPP_PATH . 'plugins/youtube/tests/'
));
