<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Plugin.php
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
 *
 *
 * Plugin
 *
 * A ThinkUp plugin
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
class Plugin {
    /*
     * @var int id
     */
    var $id;
    /*
     * @var str plugin name
     */
    var $name;
    /*
     * @var str folder name
     */
    var $folder_name;
    /*
     * @var istr description
     */
    var $description;
    /*
     * @var str author
     */
    var $author;
    /*
     * @var str homepage
     */
    var $homepage;
    /*
     * @var float version
     */
    var $version;
    /*
     * @var bool is active
     */
    var $is_active = false;
    /*
     * @var str plugin icon
     */
    var $icon;

    public function __construct($val = null) {
        if(! $val) {
            return;
        }
        if (isset($val["id"])) {
            $this->id = $val["id"];
        }
        $this->name = $val["name"];
        $this->folder_name = $val["folder_name"];
        $this->description = $val['description'];
        $this->author = $val['author'];
        $this->homepage = $val['homepage'];
        $this->version = $val['version'];
        if (isset($val['icon'])) {
            $this->icon = $val['icon'];
        }
        if ($val['is_active'] == 1) {
            $this->is_active = true;
        } else {
            $this->is_active = false;
        }
    }

}
