<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.WebappTab.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp.
 * 
 * ThinkUp is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * ThinkUp is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with ThinkUp.  If not, see <http://www.gnu.org/licenses/>.
 *
*/
/**
 * Webapp Tab
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class WebappTab {
    /**
     *
     * @var str
     */
    var $short_name;
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
     * Constructor
     * @param str $short_name
     * @param str $name
     * @param str $description
     * @param str $view_template
     * @return WebappTab
     */
    public function __construct($short_name, $name, $description='', $view_template='inline.view.tpl') {
        $this->short_name = $short_name;
        $this->name = $name;
        $this->description = $description;
        $this->view_template = $view_template;
    }

    /**
     * Add dataset
     * @param WebappTabDataset $dataset
     */
    public function addDataset($dataset) {
        if (get_class($dataset) == 'WebappTabDataset') {
            array_push($this->datasets, $dataset);
        } else {
            //throw exception here?
        }
    }

    /**
     * Get datasets
     * @return array WebappTabDatasets
     */
    public function getDatasets() {
        return $this->datasets;
    }
}