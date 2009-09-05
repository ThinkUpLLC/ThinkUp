<?php 
class Database {
    var $db_host;
    var $db_name;
    var $db_user;
    var $db_password;

    
    function Database() {
        global $TWITALYTIC_CFG;
        $this->db_host = $TWITALYTIC_CFG['db_host'];
        $this->db_name = $TWITALYTIC_CFG['db_name'];
        $this->db_user = $TWITALYTIC_CFG['db_user'];
        $this->db_password = $TWITALYTIC_CFG['db_password'];
    }
    
    function getConnection() {
        $conn = mysql_connect($this->db_host, $this->db_user, $this->db_password) or die("DIE: ".mysql_error().$this->db_host.$this->db_user.$this->db_password);
        
        //echo "select this db: " .$this->db_name."<br />";
        
        mysql_select_db($this->db_name, $conn) or die(mysql_errno()." ".mysql_error());
        
        return $conn;
    }
    
    function closeConnection($conn) {
        mysql_close($conn);
    }
    
    public static function exec($q) {
        $r = mysql_query($q) or die("Query failed:<br /> $q <br /><br />Error details:<br />".mysql_error());
        return $r;
    }
}
?>
