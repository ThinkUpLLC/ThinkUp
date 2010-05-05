<?php
class Channel {
    var $id;
    var $name;
    var $channel_id;
    var $url;
    var $network;

    function Channel($val) {
        $this->id = $val["id"];
        $this->name = $val["name"];

        if (isset($val["network_id"])) {
            $this->network_id = $val["network_id"];
        }

        if (isset($val["url"])) {
            $this->url = $val["url"];
        }

        $this->network = $val["network"];
    }
}

class ChannelDAO extends MySQLDAO {

    function insert($name, $network, $network_id, $url) {
        $q = "
			INSERT INTO
				#prefix#channels (name, network, network_id, url)
				VALUES (
					'".mysql_real_escape_string($name)."', '".mysql_real_escape_string($network)."', ".$network_id. ", '". mysql_real_escape_string($url) . "');";
         
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

    function getByNetworkID($network_id, $network) {
        $q = "SELECT c.*
			FROM #prefix#channels c
			WHERE c.network_id='{$network_id}' AND c.network='{$network}';";
         
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

    function delete($network_id, $network) {
        $q = "DELETE FROM #prefix#channels
            WHERE network_id='{$network_id}' AND network='{$network}';";

        $sql_result = $this->executeSQL($q);
        if (mysql_affected_rows() > 0 ) {
            return true;
        } else {
            return false;
        }
    }

}

?>
