<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.MenuItem.php
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
 * Menu Item
 * Sidebar menu item, contains datasets to render in the view.
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class MenuItem {
    /**
     *
     * @var str
     */
    var $name;
    /**
     *
     * @var str
     */
    var $description;
    /**
     *
     * @var array
     */
    var $datasets = array();
    /**
     *
     * @var str
     */
    var $view_template;
    /**
     * @var str Parent view slug, for maintaining state when on an inner view
     */
    var $parent = null;
    /**
     * Constructor
     * @param str $name
     * @param str $description
     * @param str $view_template
     * @return MenuItem
     */
    public function __construct($name, $description='', $view_template='inline.view.tpl', $parent=null) {
        $this->name = $name;
        $this->description = $description;
        $this->view_template = $view_template;
        $this->parent = $parent;
    }

    /**
     * Add dataset
     * @param MenuItemDataset $dataset
     */
    public function addDataset($dataset) {
        if (get_class($dataset) == 'Dataset') {
            array_push($this->datasets, $dataset);
        } else {
            //throw exception here?
        }
    }

    /**
     * Get datasets
     * @return array MenuItemDatasets
     */
    public function getDatasets() {
        return $this->datasets;
    }
}