<?php 
class Channel {
    var $id;
    var $keyword;
    var $network;
    
    function Channel($val) {
        $this->id = $val["id"];
        if (isset($val["keyword"])) {
            $this->keyword = $val["keyword"];
        }
        $this->network = $val["network"];
    }
}

class ChannelDAO extends MySQLDAO {

    function insert($keyword, $network) {
        $q = "
			INSERT INTO
				#prefix#channels (keyword, network)
				VALUES (
					'".mysql_real_escape_string($keyword)."', '".mysql_real_escape_string($network)."');";
					
        $foo = $this->executeSQL($q);
        if (mysql_affected_rows() > 0 and mysql_insert_id() > 0) {
            return mysql_insert_id();
        } else {
            return false;
        }
    }
    
    function get($channel_id) {
        $q = "SELECT c.*  
			FROM #prefix#channels c
			WHERE c.id={$channel_id};";
			
        $sql_result = $this->executeSQL($q);
        
        if (mysql_num_rows($sql_result) > 0) {
            $channel_row = mysql_fetch_assoc($sql_result);
            $c = new Channel($channel_row);
        } else {
            $c = null;
        }
        mysql_free_result($sql_result);
        return $c;
    }
    
    function getByKeyword($keyword, $network) {
        $q = "SELECT c.*  
			FROM #prefix#channels c
			WHERE c.keyword='{$keyword}' AND c.network='{$network}';";
			
        $sql_result = $this->executeSQL($q);
        
        if (mysql_num_rows($sql_result) > 0) {
            $channel_row = mysql_fetch_assoc($sql_result);
            $c = new Channel($channel_row);
        } else {
            $c = null;
        }
        mysql_free_result($sql_result);
        return $c;
    }
    
}

?>
