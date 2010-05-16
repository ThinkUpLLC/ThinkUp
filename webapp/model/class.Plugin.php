<?php
class Plugin {
    var $id;
    var $name;
    var $folder_name;
    var $description;
    var $author;
    var $homepage;
    var $version;
    var $is_active = false;
    var $icon;

    function Plugin($val) {
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

class PluginDAO extends MySQLDAO {

    public function getAllPlugins($condition = "") {
        $q = " SELECT * FROM #prefix#plugins p ";
        if ($condition != "") {
            $q .= $condition;
        }
        //echo $q;
        $sql_result = $this->executeSQL($q);

        $plugins = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $plugins[] = new Plugin($row);
        }
        mysql_free_result($sql_result);
        return $plugins;
    }

    public function getActivePlugins() {
        return $this->getAllPlugins(" WHERE p.is_active = 1");
    }

    public function isPluginActive($id) {
        $q = "SELECT is_active FROM #prefix#plugins p WHERE p.id=$id ";
        $sql_result = $this->executeSQL($q);
        if (mysql_num_rows($sql_result) > 0) {
            $row = mysql_fetch_assoc($sql_result);
            if ($row["is_active"] == 1) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function insertPlugin($p) {
        $q = "INSERT INTO
                #prefix#plugins (name, folder_name, description, author, version, is_active)
                VALUES (
                    '".mysql_real_escape_string($p->name)."', 
                    '".mysql_real_escape_string($p->folder_name)."',
                    '".mysql_real_escape_string($p->description)."',
                    '".mysql_real_escape_string($p->author)."',
                    '".mysql_real_escape_string($p->version)."',
                    ".($p->is_active ? 1 : 0)."
                    )";
        $sql_result = $this->executeSQL($q);
        if (mysql_affected_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function updatePlugin($p) {
        $q = "UPDATE
                #prefix#plugins 
            SET
                name = '".mysql_real_escape_string($p->name)."',
                folder_name = '".mysql_real_escape_string($p->folder_name)."',
                description = '".mysql_real_escape_string($p->description)."',
                author = '".mysql_real_escape_string($p->author)."',
                version = '".mysql_real_escape_string($p->version)."', 
                is_active =".($p->is_active ? 1 : 0)."
            WHERE
                id = ".$p->id;
        $sql_result = $this->executeSQL($q);
        if (mysql_affected_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getPluginId($folder_name) {
        $q = " SELECT id FROM #prefix#plugins p WHERE p.folder_name='$folder_name'";
        $sql_result = $this->executeSQL($q);
        if (mysql_num_rows($sql_result) > 0) {
            $row = mysql_fetch_assoc($sql_result);
            return $row["id"][0];
        } else {
            return null;
        }
    }

    function setActive($pid, $p) {
        $q = "
            UPDATE 
                #prefix#plugins
             SET 
                is_active = ".$p."
            WHERE
                id = '".$pid."';";
        $sql_result = $this->executeSQL($q);
    }

    public function getInstalledPlugins($plugin_path) {
        // Detect what plugins exist in the filesystem; parse their header comments for plugin metadata
        $installed_plugins = array();
        $plugin_files = Utils::getPlugins($plugin_path.'webapp/plugins');
        foreach ($plugin_files as $pf) {
            foreach (glob($plugin_path.'webapp/plugins/'.$pf."/controller/*.php") as $includefile) {
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



?>
