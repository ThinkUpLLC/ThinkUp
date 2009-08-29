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
}

?>