<?php 
class Database {
    var $db_host;
    var $db_name;
    var $db_user;
    var $db_password;
    var $logger = null;
    
    function Database($TWITALYTIC_CFG) {
        $this->db_host = $TWITALYTIC_CFG['db_host'];
        $this->db_name = $TWITALYTIC_CFG['db_name'];
        $this->db_user = $TWITALYTIC_CFG['db_user'];
        $this->db_password = $TWITALYTIC_CFG['db_password'];
		//TODO: Get optional table name prefix from CFG array and set it here
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
    
    public static function exec($q) {
    	//TODO: Process the table prefix--replace table name with prefix_tablename
		//TOOD: Process GMT offset in query
    	//TODO: On failure throw an exception here, catch and log inside DAO's with mysql error
        $r = mysql_query($q) or die("Query failed:<br /> $q <br /><br />Error details:<br />".mysql_error());
        return $r;
    }
    
	/*
    function logOrDie($s) {
        if ($this->logger != null)
            $this->logger->logStatus($s, get_class($this));
        else
            die($s);
    }
    */
}
?>
