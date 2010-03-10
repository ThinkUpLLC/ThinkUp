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
    
    function Plugin($val) {
        $this->id = $val["id"];
        $this->name = $val["name"];
        $this->folder_name = $val["folder_name"];
        $this->description = $val['description'];
        $this->author = $val['author'];
        $this->homepage = $val['homepage'];
        $this->version = $val['version'];
        if ($val['is_active'] == 1) {
            $this->is_active = true;
        }
    }
    
}

class PluginDAO extends MySQLDAO {

    public function getAllPlugins($condition = "") {
        $q = " SELECT * FROM #prefix#plugins p ";
		if ( $condition != "" ) {
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
}
?>
