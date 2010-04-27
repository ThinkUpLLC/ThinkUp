<?php 
class InstanceChannel {
    var $id;
    var $instance_id;
    var $channel_id;
    
    function InstanceChannel($val) {
        $this->id = $val["id"];
        $this->instance_id = $val["instance_id"];
        $this->channel_id = $val["channel_id"];
    }
}

class InstanceChannelDAO extends MySQLDAO {

    function insert($instance_id, $channel_id) {
        $q = "
			INSERT INTO
				#prefix#instance_channels (instance_id, channel_id)
				VALUES ('{$instance_id}', '{$channel_id}');";
        $this->executeSQL($q);
        if (mysql_affected_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    function getByInstance($instance_id) {
        $q = "SELECT ic.*  
			FROM #prefix#instance_channels ic
			WHERE ic.instance_id='{$instance_id}';";
			
        $sql_result = $this->executeSQL($q);
        
        if (mysql_num_rows($sql_result) > 0) {
            $row = mysql_fetch_assoc($sql_result);
            $ic = new InstanceChannel($row);
        } else {
            $ic = null;
        }
        mysql_free_result($sql_result);
        return $ic;
    }
    
}


?>
