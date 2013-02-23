<?php
/**
 *
 * ThinkUp/webapp/init.php
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
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 *
 */
if ( version_compare(PHP_VERSION, '5.2', '<') ) {
    exit("ERROR: ThinkUp requires PHP 5.2 or greater. The current version of PHP is ".PHP_VERSION.".");
}

//Define path globals
if (!file_exists('README.md')) { // source repo
    define('THINKUP_ROOT_PATH', str_replace("\\",'/', dirname(dirname(__FILE__))) .'/');
    define('THINKUP_WEBAPP_PATH', THINKUP_ROOT_PATH . 'webapp/');
} else { // distro package
    define('THINKUP_ROOT_PATH', str_replace("\\",'/', dirname(__FILE__)) .'/');
    define('THINKUP_WEBAPP_PATH', THINKUP_ROOT_PATH);
}

//Register our lazy class loader
require_once '_lib/class.Loader.php';

Loader::register();
