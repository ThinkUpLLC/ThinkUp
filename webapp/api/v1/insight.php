<?php
/**
 *
 * ThinkUp/webapp/api/v1/insight.php
 *
 * Copyright (c) 2013 Gina Trapani, Nilaksh Das
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
 * @author Nilaksh Das <nilakshdas@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Gina Trapani, Nilaksh Das
 */
chdir("../../");
require_once 'init.php';

$controller = new InsightAPIController();
echo $controller->go();
