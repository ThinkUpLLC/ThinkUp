<?php
/**
 *
 * ThinkUp/tests/init.tests.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Dwi Widiastuti, Christoffer Viken
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
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 * @author Christoffer Viken <christoffer[at]viken[dot]me>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Dwi Widiastuti, Christoffer Viken
*/
require_once 'config.tests.inc.php';

//set up 3 required constants
if ( !defined('DS') ) {
    define('DS', DIRECTORY_SEPARATOR);
}
if ( !defined('THINKUP_ROOT_PATH') ) {
    define('THINKUP_ROOT_PATH', dirname(dirname(__FILE__)) . DS);
}

if ( !defined('THINKUP_WEBAPP_PATH') ) {
    define('THINKUP_WEBAPP_PATH', THINKUP_ROOT_PATH . 'webapp' . DS);
}

if ( !defined('TESTS_RUNNING') ) {
    define('TESTS_RUNNING', true);
}

//Register our lazy class loader
require_once THINKUP_ROOT_PATH.'webapp/_lib/model/class.Loader.php';

Loader::register(array(
THINKUP_ROOT_PATH . 'tests' . DS,
THINKUP_ROOT_PATH . 'tests' . DS . 'classes' . DS,
THINKUP_ROOT_PATH . 'tests' . DS . 'fixtures' .DS
));
