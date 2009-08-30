<?php

class Link {
	var $id;
	var $url;
	var $expanded_url;
	var $title;
	var $clicks;
	var $status_id;


	function Link($val) {
		$this->url = $val["url"];
		if (isset($val["expanded_url"]))
			$this->expanded_url = $val["expanded_url"];

		if (isset($val["title"]))
			$this->expanded_url = $val["title"];

		if (isset($val["clicks"]))
			$this->expanded_url = $val["clicks"];

		if (isset($val["status_id"]))
			$this->status_id = $val["status_id"];

	}
	
}

class LinkDAO {

	function insert($url, $status_id) {
		$q = "
			INSERT INTO
				links (url, status_id)
				VALUES (
					'".$url."',".$status_id.");";
		$foo = Database::exec($q);
		if (mysql_affected_rows() > 0)
			return true;
		else
			return false;
	}

	function insertExpanded($url, $expanded, $title, $status_id) {
		$expanded = mysql_real_escape_string($expanded);
		$title = mysql_real_escape_string($title);

		$q = "
			INSERT INTO
				links (url, expanded_url, title, status_id)
				VALUES (
					'{$url}', '{$expanded}', '{$title}', ".$status_id.");";

		$foo = Database::exec($q);
		if (mysql_affected_rows() > 0)
			return true;
		else
			return false;
	}


	function getLinksByFriends($user_id) {
		$q = "
			SELECT l.* 
			FROM links l
			INNER JOIN tweets t
			ON t.status_id = l.status_id
			WHERE t.author_user_id in (SELECT user_id FROM follows f WHERE f.follower_id = ".$user_id.")
			LIMIT 15";
			
		$sql_result = Database::exec($q);
		$links = array();
		while ($row = mysql_fetch_assoc($sql_result)) { $links[] = $row; }
		mysql_free_result($sql_result);	
		return $links;	
	}
}

?>