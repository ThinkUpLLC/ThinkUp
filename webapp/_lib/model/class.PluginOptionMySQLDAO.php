<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.PluginOptionMySQLDAO.php
 *
 * Copyright (c) 2009-2010 Mark Wilkie, Gina Trapani
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
 * Plugin Option Data Access Object
 *
 * The data access object for retrieving and saving plugin options.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Mark Wilkie, Gina Trapani
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class PluginOptionMySQLDAO extends PDODAO implements PluginOptionDAO {

    public static $cached_options = array();

    public function deleteOption($id) {
        $q = 'DELETE FROM #prefix#plugin_options WHERE id = :id';
        $stmt = $this->execute($q, array(':id' => $id));
        if ( $this->getUpdateCount($stmt) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function insertOption($plugin_folder, $name, $value) {
        $q = 'INSERT INTO #prefix#plugin_options
                (plugin_id, option_name, option_value)
            VALUES
                (:plugin_id, :option_name, :option_value)';
        $stmt = $this->execute($q,
        array(':plugin_id' => $plugin_folder, ':option_name' => $name, ':option_value' => $value) );
        return $this->getInsertId($stmt);
    }

    public function updateOption($id, $name, $value) {
        $q = 'UPDATE #prefix#plugin_options
            SET
                option_name = :option_name, 
                option_value = :option_value
            WHERE 
                id = :id';
        $stmt = $this->execute($q,
        array(':id' => $id, ':option_name' => $name, ':option_value' => $value) );
        if ( $this->getUpdateCount($stmt) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getOptions($plugin_folder = null, $cached = false) {
        $options = null;
        $cache_key = (! is_null($plugin_folder) ) ? ($plugin_folder . 'id'): 'all';
        if ($cached && isset(self::$cached_options[$cache_key])) {
            $options = self::$cached_options[$cache_key];
        }
        if (is_null($options)) {
            $q = 'SELECT po.id, po.plugin_id, po.option_name, po.option_value
                FROM 
                    #prefix#plugin_options po ';
            $q .= $plugin_folder ? 'INNER JOIN #prefix#plugins p ON p.id = po.plugin_id
                WHERE p.folder_name = :plugin_folder' : '';
            if ($plugin_folder) {
                $data = array(':plugin_folder' => $plugin_folder);
                $stmt = $this->execute($q, $data);
            } else {
                $stmt = $this->execute($q);
            }
            $options = $this->getDataRowsAsObjects($stmt, 'PluginOption');
            if (isset($options[0])) {
                if ($cached) {
                    self::$cached_options[$cache_key] = $options;
                }
            } else {
                $options = null;
            }
        }
        return $options;
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
}