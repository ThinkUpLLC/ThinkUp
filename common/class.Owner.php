<?php

class Owner {
	var $id;
	var $user_name;
	var $full_name;
	var $user_email;
	var $is_admin = false;


	function Owner($val) {
		$this-> id = $val["id"];
		$this-> user_name = $val["user_name"];
		$this-> full_name = $val["full_name"];
		$this-> user_email = $val['user_email'];
		if ($val['is_admin'] == 1)
			$this-> is_admin = true;
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
		$sql_result = Database::exec($q);
		$row = mysql_fetch_assoc($sql_result);
		mysql_free_result($sql_result);	
		return new Owner($row);		
		
	}
	
}


?>