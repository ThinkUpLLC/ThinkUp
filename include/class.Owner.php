<?php

class Owner {
	var $id;
	var $user_name;
	var $full_name;
	var $user_email;


	function Owner($val) {
		$this-> id = $val["id"];
		$this-> user_name = $val["user_name"];
		$this-> full_name = $val["full_name"];
		$this-> user_email = $val['user_email'];
	}
	
}

class OwnerDAO {
	function getByEmail($email) {
		$q		= "
			SELECT 
				* 
			FROM
				owners o 
			WHERE 
				o.user_email = '". $email. "';";
		$sql_result = mysql_query($q)  or die("Error, selection query failed: $q");
		$row = mysql_fetch_assoc($sql_result);
		mysql_free_result($sql_result);	
		return new Owner($row);		
		
	}
	
}


?>