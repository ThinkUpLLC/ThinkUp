<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.PluginMySQLDAO.php
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
 * Plugin Data Access Object
 * The data access object for retrieving and saving plugin data for thinkup
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 */
class PluginMySQLDAO extends PDODAO implements PluginDAO {

    public function getAllPlugins($is_active = false) {
        $q = " SELECT * FROM #prefix#plugins p";
        if ($is_active != "") {
            $q .= ' where p.is_active = 1';
        }
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $stmt = $this->execute($q);
        return $this->getDataRowsAsObjects($stmt, 'Plugin');
    }

    public function getActivePlugins() {
        return $this->getAllPlugins(true);
    }

    public function isPluginActive($id) {
        $q = 'SELECT is_active FROM #prefix#plugins p WHERE p.id = :id';
        $status = false;
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $stmt = $this->execute($q, array(':id' => $id));
        $plugin = $this->getDataRowAsObject($stmt, 'Plugin');
        if ($plugin && $plugin->is_active == 1) {
            $status = true;
        }
        return $status;
    }

    public function insertPlugin($plugin) {
        if (!is_object($plugin) || !isset($plugin->name) || !isset($plugin->folder_name)
        || !isset($plugin->is_active) ) {
            throw new BadArgumentException("PluginDAO::insertPlugin requires a valid plugin data object");
        }
        $q = 'INSERT INTO
                #prefix#plugins (name, folder_name, description, author, version, homepage, is_active)
            VALUES
                (:name, :folder_name, :description, :author, :version, :homepage, :is_active)';
        $is_active = 1;
        $vars = array(
            ':name' => $plugin->name,
            'folder_name' => $plugin->folder_name,
            ':description' => $plugin->description,
            ':author' => $plugin->author,
            ':version' => $plugin->version,
            ':homepage' => $plugin->homepage,
            ':is_active' => $is_active);
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $stmt = $this->execute($q, $vars);
        if ( $this->getInsertCount($stmt) > 0) {
            return $this->getInsertId($stmt);
        } else {
            return false;
        }
    }

    public function updatePlugin($plugin) {
        if (!is_object($plugin) || get_class($plugin) != 'Plugin'
        || ! isset($plugin->name) || ! isset($plugin->folder_name)
        || ! isset($plugin->is_active) || ! isset($plugin->id) )
        {
            throw new BadArgumentException("updatePlugin() requires a valid plugin data object");
        }
        $q = 'UPDATE
                #prefix#plugins
            SET
                name = :name,
                folder_name = :folder_name,
                description = :description,
                author = :author,
                version = :version,
                homepage = :homepage,
                is_active = :is_active
            WHERE
                id = :id';
        $is_active = 1;
        $vars = array(
            ':name' => $plugin->name,
            'folder_name' => $plugin->folder_name,
            ':description' => $plugin->description,
            ':author' => $plugin->author,
            ':version' => $plugin->version,
            ':homepage' => $plugin->homepage,
            ':is_active' => $is_active,
            ':id' => $plugin->id);
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $stmt = $this->execute($q, $vars);
        if ( $this->getUpdateCount($stmt) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getPluginId($folder_name) {
        $q = "SELECT id FROM #prefix#plugins WHERE folder_name = :folder_name";
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $stmt = $this->execute($q, array(':folder_name' => $folder_name) );
        $row = $this->getDataRowAsArray($stmt);
        // get the id if there is one
        $id = $row && $row['id'] ? $row['id'] : null;
        return $id;
    }

    public function getPluginFolder($plugin_id) {
        $q = "SELECT folder_name FROM #prefix#plugins WHERE id = :plugin_id";
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $stmt = $this->execute($q, array(':plugin_id' => $plugin_id) );
        $row = $this->getDataRowAsArray($stmt);
        // get the id if there is one
        $folder_name = $row && $row['folder_name'] ? $row['folder_name'] : null;
        return $folder_name;
    }

    public function setActive($id, $active) {
        if (is_bool($active)) {
            $active = $this->convertBoolToDB($active);
        }
        $q = "
            UPDATE
                #prefix#plugins
             SET
                is_active = :active
            WHERE
                id = :id";
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $stmt = $this->execute($q, array(':active' => $active, ':id' => $id));
        return $this->getUpdateCount($stmt);
    }

    public function getInstalledPlugins() {
        // Detect what plugins exist in the filesystem; parse their header comments for plugin metadata
        Loader::definePathConstants();
        $active_plugins = $inactive_plugins = array();
        $plugin_files = Utils::getPlugins(THINKUP_WEBAPP_PATH.'plugins');
        foreach ($plugin_files as $pf) {
            foreach (glob(THINKUP_WEBAPP_PATH.'plugins/'.$pf."/controller/".$pf.".php") as $includefile) {
                $fhandle = fopen($includefile, "r");
                $contents = fread($fhandle, filesize($includefile));
                fclose($fhandle);
                $plugin_vals = $this->parseFileContents($contents, $pf);
                if (isset($plugin_vals['class'])) {
                    require_once THINKUP_WEBAPP_PATH.'plugins/'.$pf."/model/class.".$plugin_vals['class'].".php";
                    $installed_plugin = new $plugin_vals['class']($plugin_vals);
                } else {
                    $installed_plugin = new Plugin($plugin_vals);
                }
                if (isset($installed_plugin)) {
                    // Insert or update plugin entries in the database
                    if (!isset($installed_plugin->id)) {
                        $new_plugin_id = $this->insertPlugin($installed_plugin);
                        if ($new_plugin_id === false) {
                            $this->updatePlugin($installed_plugin);
                        } else {
                            $installed_plugin->id = $new_plugin_id;
                        }
                    }
                    // Store in list, active first
                    if ($installed_plugin->is_active) {
                        array_push($active_plugins, $installed_plugin);
                    } else {
                        array_push($inactive_plugins, $installed_plugin);
                    }
                }
            }
        }
        return array_merge($active_plugins, $inactive_plugins);
    }

    private function parseFileContents($contents, $pf) {
        $plugin_vals = array();
        $start = strpos($contents, '/*');
        $end = strpos($contents, '*/');
        if ($start > 0 && $end > $start) {
            $scriptData = substr($contents, $start + 2, $end - $start - 2);

            $scriptData = preg_split('/[\n\r]+/', $scriptData);
            foreach ($scriptData as $line) {
                $m = array();
                if (preg_match('/Plugin Name:(.*)/', $line, $m)) {
                    $plugin_vals['name'] = trim($m[1]);
                }
                if (preg_match('/Plugin URI:(.*)/', $line, $m)) {
                    $plugin_vals['homepage'] = trim($m[1]);
                }
                if (preg_match('/Description:(.*)/', $line, $m)) {
                    $plugin_vals['description'] = trim($m[1]);
                }
                if (preg_match('/Version:(.*)/', $line, $m)) {
                    $plugin_vals['version'] = trim($m[1]);
                }
                if (preg_match('/Author:(.*)/', $line, $m)) {
                    $plugin_vals['author'] = trim($m[1]);
                }
                if (preg_match('/Icon:(.*)/', $line, $m)) {
                    $plugin_vals['icon'] = trim($m[1]);
                }
                if (preg_match('/Class:(.*)/', $line, $m)) {
                    $plugin_vals['class'] = trim($m[1]);
                }

            }
            $plugin_vals["folder_name"] = $pf;
            $plugin_vals["id"] = $this->getPluginId($pf);
            if (isset($plugin_vals["id"])) {
                $plugin_vals["is_active"] = $this->isPluginActive($plugin_vals["id"]);
            } else {
                $plugin_vals["is_active"] = 0;
            }
            return $plugin_vals;
        } else {
            return null;
        }
    }

    public function isValidPluginId($plugin_id) {
        $q = 'SELECT id FROM  #prefix#plugins where id = :id';
        $data = array(':id' => $plugin_id);
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $stmt = $this->execute($q, $data);
        return $this->getDataIsReturned($stmt);
    }
}
