<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.PluginOptionMySQLDAO.php
 *
 * Copyright (c) 2009-2013 Mark Wilkie, Gina Trapani
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
 * Plugin Option Data Access Object
 *
 * The data access object for retrieving and saving plugin options.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Mark Wilkie, Gina Trapani
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class PluginOptionMySQLDAO extends PDODAO implements PluginOptionDAO {

    public function __construct() {
        $this->option_dao = DAOFactory::getDAO('OptionDAO');
        $this->namespace = OptionDAO::PLUGIN_OPTIONS;

    }
    public static $cached_options = array();

    public function deleteOption($id) {
        $count = $this->option_dao->deleteOption($id);
        if ($count == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function insertOption($plugin_id, $name, $value) {
        $namespace = $this->namespace . '-' . $plugin_id;
        return $this->option_dao->insertOption($namespace, $name, $value);
    }

    public function updateOption($id, $name, $value) {
        $count = $this->option_dao->updateOption($id, $value, $name);
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getOptions($plugin_folder, $cached = false) {
        $plugin_dao = DAOFactory::getDAO('PluginDAO');
        $plugin_id = $plugin_dao->getPluginId($plugin_folder);
        if ($plugin_id) {
            return self::getOptionsByPluginId($plugin_id, $cached);
        } else {
            return null;
        }
    }

    public function getOptionsByPluginId($plugin_id, $cached = false) {
        $namespace = $this->namespace . '-' . $plugin_id;
        $options =  $this->option_dao->getOptions($namespace, $cached);
        $plugin_opts = array();
        if ($options) {
            foreach($options as $option) {
                $plugin_opt = new PluginOption();
                $plugin_opt->id = $option->option_id;
                $plugin_opt->plugin_id = $plugin_id;
                $plugin_opt->option_name = $option->option_name;
                $plugin_opt->option_value = $option->option_value;
                array_push($plugin_opts, $plugin_opt);
            }
        }
        return $plugin_opts;
    }

    public function getOptionsHash($plugin_folder, $cached = false) {
        $options = $this->getOptions($plugin_folder, $cached);
        $options_hash = array();
        if (count( $options) > 0 ) {
            foreach ($options as $option) {
                $options_hash[ $option->option_name ] = $option;
            }
        }
        return $options_hash;
    }

    public function getOptionsHashByPluginId($plugin_id, $cached = false) {
        $options = $this->getOptionsByPluginId($plugin_id, $cached);
        $options_hash = array();
        if (count( $options) > 0 ) {
            foreach ($options as $option) {
                $options_hash[ $option->option_name ] = $option;
            }
        }
        return $options_hash;
    }
}