<?php
/**
 *
 * ThinkUp/webapp/plugins/instagram/model/class.InstagramCrawler.php
 *
 * Copyright (c) 2013 Dimosthenis Nikoudis
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
 * @author Dimosthenis Nikoudis <dnna[at]dnna[dot]gr>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Dimosthenis Nikoudis
 */
$version = explode('.', PHP_VERSION); //dont run redis or instagram test for php less than 5.3
if ($version[0] >= 5 && $version[1] >= 3) { //only run Instagram tests if PHP 5.3
    require_once dirname(__FILE__) . '/PHP5.3/' . basename(__FILE__);
}