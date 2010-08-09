<?php
require_once 'model/class.PDODAO.php';
require_once 'model/interface.PluginDAO.php';
require_once 'model/class.Plugin.php';
require_once 'model/exceptions/class.BadArgumentException.php';


/**
 * Plugin Data Access Object
 * The data access object for retrieving and saving plugin data for thinkup
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 */
class PluginMySQLDAO extends PDODAO implements PluginDAO {

    public function getAllPlugins($is_active = false) {
        $q = " SELECT * FROM #prefix#plugins p";
        if ($is_active != "") {
            $q .= ' where p.is_active = 1';
        }
        $stmt = $this->execute($q);
        return $this->getDataRowsAsObjects($stmt, 'Plugin');
    }

    public function getActivePlugins() {
        return $this->getAllPlugins(true);
    }

    public function isPluginActive($id) {
        $q = 'SELECT is_active FROM #prefix#plugins p WHERE p.id = :id';
        $status = false;
        $stmt = $this->execute($q, array(':id' => $id));
        $plugin = $this->getDataRowAsObject($stmt, 'Plugin');
        if ($plugin && $plugin->is_active == 1) {
            $status = true;
        }
        return $status;
    }

    public function insertPlugin($plugin) {
        if(! is_object($plugin) || get_class($plugin) != 'Plugin'
        || ! isset($plugin->name) || ! isset($plugin->folder_name)
        || ! isset($plugin->is_active) ) {
            throw new BadArgumentException("insertPlugin() requires a valid plugin data object");
        }
        $q = 'INSERT INTO
                #prefix#plugins (name, folder_name, description, author, version, homepage, is_active)
            VALUES 
                (:name, :folder_name, :description, :author, :version, :homepage, :is_active)';
        $is_active = $plugin->is_active ? 1 : 0;
        $vars = array(
            ':name' => $plugin->name, 
            'folder_name' => $plugin->folder_name,
            ':description' => $plugin->description,
            ':author' => $plugin->author,
            ':version' => $plugin->version, 
            ':homepage' => $plugin->homepage, 
            ':is_active' => $is_active);
        $stmt = $this->execute($q, $vars);
        if ( $this->getInsertCount($stmt) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function updatePlugin($plugin) {
        if(! is_object($plugin) || get_class($plugin) != 'Plugin'
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
        $is_active = $plugin->is_active ? 1 : 0;
        $vars = array(
            ':name' => $plugin->name,
            'folder_name' => $plugin->folder_name,
            ':description' => $plugin->description,
            ':author' => $plugin->author,
            ':version' => $plugin->version,
            ':homepage' => $plugin->homepage,
            ':is_active' => $is_active,
            ':id' => $plugin->id);
        $stmt = $this->execute($q, $vars);
        if ( $this->getUpdateCount($stmt) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getPluginId($folder_name) {
        $q = "SELECT id FROM #prefix#plugins WHERE folder_name = :folder_name";
        $stmt = $this->execute($q, array(':folder_name' => $folder_name) );
        $row = $this->getDataRowAsArray($stmt);
        // get the id if there is one
        $id = $row && $row['id'] ? $row['id'] : null;
        return $id;
    }

    public function setActive($id, $active) {
        $q = "
            UPDATE 
                #prefix#plugins
             SET 
                is_active = :active
            WHERE
                id = :id";
        $stmt = $this->execute($q, array(':active' => $active, ':id' => $id));
        return $this->getUpdateCount($stmt);
    }

    public function getInstalledPlugins($plugin_path) {
        // Detect what plugins exist in the filesystem; parse their header comments for plugin metadata
        $installed_plugins = array();
        $plugin_files = Utils::getPlugins($plugin_path.'webapp/plugins');
        foreach ($plugin_files as $pf) {
            foreach (glob($plugin_path.'webapp/plugins/'.$pf."/controller/".$pf.".php") as $includefile) {
                $fhandle = fopen($includefile, "r");
                $contents = fread($fhandle, filesize($includefile));
                fclose($fhandle);
                $installed_plugin = $this->parseFileContents($contents, $pf);
                if (isset($installed_plugin)) {
                    // Insert or update plugin entries in the database
                    if (!isset($installed_plugin->id)) {
                        if ($this->insertPlugin($installed_plugin)) {
                            $installed_plugin->id = $this->getPluginId($installed_plugin->folder_name);
                        } else {
                            $this->updatePlugin($installed_plugin);
                        }
                    }
                    array_push($installed_plugins, $installed_plugin);
                }
            }
        }
        return $installed_plugins;
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

            }
            $plugin_vals["folder_name"] = $pf;
            $plugin_vals["id"] = $this->getPluginId($pf);
            if (isset($plugin_vals["id"])) {
                $plugin_vals["is_active"] = $this->isPluginActive($plugin_vals["id"]);
            } else {
                $plugin_vals["is_active"] = 0;
            }
            return new Plugin($plugin_vals);
        } else {
            return null;
        }
    }
}

