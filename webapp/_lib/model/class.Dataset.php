<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Dataset.php
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
 * Dataset
 * Parameters needed to retrieve a set of data to display in ThinkUp.
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class Dataset {
    /**
     * @var str
     */
    var $name;
    /**
     * @var str
     */
    var $dao_name;
    /**
     *
     * @var str
     */
    var $dao_method_name;
    /**
     *
     * @var array
     */
    var $method_params;
    /**
     *
     * @var str
     */
    var $iterator_method_name;
    /**
     *
     * @var array
     */
    var $iterator_method_params;
    /**
     *
     * @var array String of allowed DAO names
     */
    var $FETCHING_DAOS = array('FollowDAO', 'PostDAO', 'LinkDAO', 'CountHistoryDAO', 'FavoritePostDAO',
    'PluginOptionDAO', 'InsightDAO');

    /**
     *
     * @var str Help link slug which links to the corresponding documentation for this Dataset.
     */
    var $help_slug = null;

    /**
     * Constructor
     * @param str $name
     * @param str $dao_name
     * @param str $dao_method_name
     * @param array $method_params
     * @return Dataset
     */
    public function __construct($name, $dao_name, $dao_method_name, $method_params=array(),
    $iterator_method_name = null, $iterator_method_params = array()) {
        $this->name = $name;
        if (in_array($dao_name, $this->FETCHING_DAOS)) {
            $this->dao_name = $dao_name;
            $this->dao_method_name = $dao_method_name;
            $this->method_params = $method_params;
            if ( isset($iterator_method_name) ) {
                $this->iterator_method_name = $iterator_method_name;
                $this->iterator_method_params = $iterator_method_params;
            }
        } else {
            throw new Exception($dao_name . ' is not one of the allowed DAOs');
        }
    }

    /**
     * Retrieve dataset
     * Run the specified DAO method and return results
     * @param int $page_number Page number of the list
     * @return array DAO method results
     */
    public function retrieveDataset($page_number=1) {
        $dao = DAOFactory::getDAO($this->dao_name);
        if (method_exists($dao, $this->dao_method_name)) {
            $page_pos = array_search('#page_number#', $this->method_params);
            if ($page_pos !== false) {
                $this->method_params[$page_pos] = $page_number;
            }
            return call_user_func_array(array($dao, $this->dao_method_name), $this->method_params);
        } else {
            throw new Exception($this->dao_name . ' does not have a ' . $this->dao_method_name . ' method.');
        }
    }

    /**
     * Is this tab searchable
     * Returns true if there is an Iterator method defined for this tab
     * @return boolean
     */
    public function isSearchable() {
        return isset($this->iterator_method_name);
    }

    /**
     * Retrieve Iterator
     * Run the specified DAO Iterator method and return results
     * @return PostIterator
     */
    public function retrieveIterator() {
        $dao = DAOFactory::getDAO($this->dao_name);
        $iterator = null;
        if (!is_null($this->iterator_method_name) ) {
            if (method_exists($dao, $this->iterator_method_name)) {
                $iterator = call_user_func_array(array($dao, $this->iterator_method_name),
                $this->iterator_method_params);
            } else {
                throw new Exception($this->dao_name . ' does not have a ' . $this->dao_method_name . ' method.');
            }
        }
        return $iterator;
    }

    /**
     * Add a slug which points to the documentation that corresponds to this dataset.
     * @param str $slug
     */
    public function addHelp($slug) {
        $this->help_slug = $slug;
    }

    /**
     * Get the slug which points to documentation that corresponds to this dataset.
     * @return str slug
     */
    public function getHelp() {
        return $this->help_slug;
    }
}
