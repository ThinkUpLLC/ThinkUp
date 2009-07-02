<?php

class OwnerInstance {
	var $owner_id;
	var $instance_id;
	
	
	function OwnerInstance($oid, $iid) {
		$this->owner_id = $oid;
		$this->instance_id = $iid;
	}

}

class OwnerInstanceDAO {
	
	function doesOwnerHaveAccess($owner_id, $username) {
		$sql_query = "
			SELECT 
				* 
			FROM 
				owner_instances oi
			INNER JOIN
				instances i
			ON 
				i.id = oi.instance_id
			WHERE 
				i.twitter_username = '".$username."' AND oi.owner_id = ".$owner_id. ";";
		$sql_result = mysql_query($sql_query)  or die('Error, selection query failed:' .$sql_query );
		if (mysql_num_rows  ( $sql_result  ) == 0 ) {
			return false;
		} else {
			return true;
		}
	}
	
	function get($owner_id, $instance_id) {
		$sql_query = "
			SELECT 
				* 
			FROM 
				owner_instances 
			WHERE 
				owner_id = ".$owner_id." AND instance_id = ".$instance_id. ";";
		$sql_result = mysql_query($sql_query)  or die('Error, selection query failed:' .$sql_query );
		if (mysql_num_rows  ( $sql_result  ) == 0 ) {
			$i = null;
		} else {
			$row = mysql_fetch_assoc($sql_result);
			$oid = $row["owner_id"];
			$iid = $row["instance_id"];
			$i = new OwnerInstance($oid, $iid );
		}
		return $i;
	}

	function insert($owner_id, $instance_id, $oauth_token, $oauth_token_secret) {
		$sql_query = "
			INSERT INTO 
				owner_instances (`owner_id`, `instance_id`, `oauth_access_token`, `oauth_access_token_secret`)
			 VALUES
				(".$owner_id.", ".$instance_id.", '".$oauth_token."', '". $oauth_token_secret."')";
		$sql_result = mysql_query($sql_query)  or die('Error, insert query failed:' .$sql_query );
	}


	function getOAuthTokens( $id ) {
		$q = "
			SELECT 
				oauth_access_token, oauth_access_token_secret 
			FROM 
				owner_instances 
			WHERE 
				instance_id = ".$id." ORDER BY id ASC LIMIT 1;";
		$sql_result = mysql_query($q)  or die('Error, selection query failed:' .$sql_query );
		$tokens = mysql_fetch_assoc($sql_result);
		return $tokens;
	}
	
}

?>