<?php
class Owner {
    var $id;
    var $user_name;
    var $full_name;
    var $user_email;
    var $is_admin = false;
 
    function Owner($val) {
        $this->id = $val["id"];
        $this->user_name = $val["user_name"];
        $this->full_name = $val["full_name"];
        $this->user_email = $val['user_email'];
        if ($val['is_admin'] == 1) {
            $this->is_admin = true;
        }
    }
    
}
 
class OwnerDAO extends MySQLDAO {
    //Construct is located in parent

    function getByEmail($email) {
        $q = " SELECT * FROM ".$this->db->table_prefix."owners AS o ";
        $q .= " WHERE o.user_email = '".$email."';";
        $sql_result = $this->executeSQL($q);
        $row = mysql_fetch_assoc($sql_result);
        mysql_free_result($sql_result);
        return new Owner($row);
    }
    function getForLogin($email) {
        $q = " SELECT o.id AS id, o.user_email AS mail, o.user_name AS name, o.user_pwd AS pwd ";
        $q .= " FROM ".$this->db->table_prefix."owners AS o ";
        $q .= " WHERE o.user_email = '".$email."' AND user_activated='1'";
        $q .= " LIMIT 1;";
        $sql_result = $this->executeSQL($q);
        if (mysql_num_rows($sql_result) != 0) {
            $row = mysql_fetch_assoc($sql_result);
            mysql_free_result($sql_result);
            return new Owner($row);
        }
        else {
            return false;
        }
    }
}
 
?>
