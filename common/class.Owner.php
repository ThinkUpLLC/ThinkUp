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

    public function getByEmail($email) {
        $q = " SELECT o.id AS id, o.user_name AS user_name, o.full_name AS full_name, o.user_email AS user_email ";
        $q .= " FROM ".$this->db->table_prefix."owners AS o ";
        $q .= " WHERE o.user_email = '".$email."';";
        $sql_result = $this->executeSQL($q);
        $row = mysql_fetch_assoc($sql_result);
        mysql_free_result($sql_result);
        return new Owner($row);
    }
    public function getUserExist($email){
        $q = " SELECT user_email ";
        $q .= " FROM ".$this->db->table_prefix."owners ";
        $q .= " WHERE user_email='".$email."'";
        if (mysql_num_rows($sql_result) != 0) {
            return true;
        }
        else {
            return false;
        }
    }
    public function getForLogin($email) {
        $q = " SELECT o.id AS id, o.user_email AS mail, o.user_name AS name, o.user_pwd AS pwd ";
        $q .= " FROM ".$this->db->table_prefix."owners AS o ";
        $q .= " WHERE o.user_email = '".$email."' AND user_activated='1'";
        $q .= " LIMIT 1;";
        $sql_result = $this->executeSQL($q);
        if (mysql_num_rows($sql_result) != 0) {
            $row = mysql_fetch_assoc($sql_result);
            mysql_free_result($sql_result);
            return $row;
        }
        else {
            return false;
        }
    }
    public function getPass($email) {
        $q = " SELECT o.user_pwd AS pwd ";
        $q .= " FROM ".$this->db->table_prefix."owners AS o ";
        $q .= " WHERE o.user_email = '".$email."' AND user_activated='1'";
        $q .= " LIMIT 1;";
        $sql_result = $this->executeSQL($q);
        if (mysql_num_rows($sql_result) != 0) {
            $row = mysql_fetch_assoc($sql_result);
            mysql_free_result($sql_result);
            return $row;
        }
        else {
            return false;
        }
    }
    public function getActivationCode($email) {
        $q = " SELECT o.activation_code AS activation_code ";
        $q .= " FROM ".$this->db->table_prefix."owners AS o ";
        $q .= " WHERE user_email='".$email."'";
        $sql_result = $this->executeSQL($q);
        if (mysql_num_rows($sql_result) != 0) {
            $row = mysql_fetch_assoc($sql_result);
            mysql_free_result($sql_result);
            return $row;
        }
    }
    public function updateActivate($email) {
        $q = " UPDATE ".$this->db->table_prefix."owners ";
        $q .= " SET user_activated=1 "; 
        $q .= " WHERE user_email='".$email."'";
        $this->executeSQL($q);
    }
    public function updatePaassword($email, $pwd) {
        $q = " UPDATE ".$this->db->table_prefix."owners ";
        $q .= " SET user_pwd='".$pwd."' ";
        $q .= " WHERE user_email='".$email."'";
        $this->executeSQL($q);
    }
    public function create($email, $pass, $country, $acode, $fullname) {
        $q = "INSERT INTO ".$this->db->table_prefix."owners ";
        $q .= " (`user_email`,`user_pwd`,`country`,`joined`,`activation_code`,`full_name`)";
        $q .= " VALUES ('".$email."','".$pass."','".$country."',now(),'".$acode."','".$fullname."')";
        $this->executeSQL($q);
    }
}
 
?>
