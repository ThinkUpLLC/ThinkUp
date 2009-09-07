<?php 
class Database {
    var $db_host;
    var $db_name;
    var $db_user;
    var $db_password;
    var $logger = null;
    var $table_prefix;
    
    function Database($TWITALYTIC_CFG) {
        $this->db_host = $TWITALYTIC_CFG['db_host'];
        $this->db_name = $TWITALYTIC_CFG['db_name'];
        $this->db_user = $TWITALYTIC_CFG['db_user'];
        $this->db_password = $TWITALYTIC_CFG['db_password'];
        if (isset($TWITALYTIC_CFG['table_prefix']))
            $this->table_prefix = $TWITALYTIC_CFG['table_prefix'];
            
        //TODO: Get GMT server offset here
    }
    
    function getConnection() {
        $fail = false;
        $conn = mysql_connect($this->db_host, $this->db_user, $this->db_password) or $fail = true;
        if ($fail)
            throw new Exception("ERROR: ".mysql_error().$this->db_host.$this->db_user.$this->db_password);
        mysql_select_db($this->db_name, $conn) or $fail = true;
        if ($fail)
            throw new Exception("ERROR: ".mysql_errno()." ".mysql_error());
        return $conn;
    }
    
    function closeConnection($conn) {
        mysql_close($conn);
    }
    
    function exec($q) {
    	$fail = false;
        $q = str_replace('%prefix%', $this->table_prefix, $q);
        //echo $q;
        //TOOD: Process GMT offset in query
        
        //TODO: On failure throw an exception here, catch and log inside DAO's with mysql error
        $r = mysql_query($q) or $fail = true;
		if ($fail)
            throw new Exception("ERROR: 
			Query failed: ".$q. " 
			 ".mysql_error());		
        return $r;
    }
    
}
?>
