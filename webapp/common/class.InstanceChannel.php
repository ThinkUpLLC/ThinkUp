<?php
class InstanceChannel {
    var $id;
    var $instance_id;
    var $channel_id;
    var $name;
    var $network_id;
    var $url;
    var $network;

    function InstanceChannel($val) {
        $this->id = $val["id"];
        $this->instance_id = $val["instance_id"];
        $this->channel_id = $val["channel_id"];
        if (isset($val["name"])) {
            $this->name = $val["name"];
        }
        if (isset($val["network_id"])) {
            $this->network_id = $val["network_id"];
        }
        if (isset($val["url"])) {
            $this->url = $val["url"];
        }
        if (isset($val["network"])) {
            $this->network = $val["network"];
        }
    }
}

class InstanceChannelDAO extends MySQLDAO {

    function insert($instance_id, $channel_id) {
        $q = "
			INSERT INTO
				#prefix#instance_channels (instance_id, channel_id)
				VALUES ('{$instance_id}', '{$channel_id}');";
        $this->executeSQL($q);
        if (mysql_affected_rows() > 0 and mysql_insert_id() > 0) {
            return mysql_insert_id();
        } else {
            return false;
        }
    }

    function get($instance_id, $channel_id) {
        $q = "SELECT ic.*
            FROM #prefix#instance_channels ic
            WHERE ic.instance_id='{$instance_id}' AND ic.channel_id='{$channel_id}';";

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



    function getByInstanceAndNetwork($instance_id, $network) {
        $q = "SELECT ic.id, ic.instance_id, ic.channel_id, c.name, c.network_id, c.url, c.network
			FROM #prefix#instance_channels ic
			INNER JOIN #prefix#channels c
			ON c.id = ic.channel_id
			WHERE ic.instance_id='{$instance_id}' AND c.network='{$network}';";
         
        $sql_result = $this->executeSQL($q);

        $instance_channels = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $ic = new InstanceChannel($row);
            $instance_channels[] = $ic;
        }
        mysql_free_result($sql_result);
        return $instance_channels;
    }

    function delete($instance_id, $channel_id) {
        $q = "DELETE FROM #prefix#instance_channels
            WHERE instance_id='{$instance_id}' AND channel_id='{$channel_id}';";

        $sql_result = $this->executeSQL($q);
        if (mysql_affected_rows() > 0 ) {
            return true;
        } else {
            return false;
        }
    }

}


?>
