<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/auth.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Mark Wilkie, Christoffer Viken
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
 * @author Mark Wilkie <mark[at]bitterpill[dot]org>
 * @author Christoffer Viken <christoffer[at]viken[dot]me>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Mark Wilkie, Christoffer Viken
*/
chdir("..");
chdir("..");
require_once 'init.php';
require_once 'plugins/twitter/controller/class.TwitterAuthController.php';

$controller = new TwitterAuthController();
echo $controller->go();
