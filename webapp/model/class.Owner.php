<?php
class Owner {
    var $id;
    var $user_name;
    var $full_name;
    var $user_email;
    var $is_admin = false;
    var $last_login;
    var $instances = null;

    function Owner($val) {
        $this->id = $val["id"];
        $this->user_name = $val["user_name"];
        $this->full_name = $val["full_name"];
        $this->user_email = $val['user_email'];
        $this->last_login = $val['last_login'];
        if ($val['is_admin'] == 1) {
            $this->is_admin = true;
        }
    }

    function setInstances($instances) {
        $this->instances = $instances;
    }

}

class OwnerDAO extends MySQLDAO {
    //Construct is located in parent

    public function getByEmail($email) {
        $q = " SELECT o.id AS id, o.user_name AS user_name, o.full_name AS full_name, o.user_email AS user_email, is_admin, last_login ";
        $q .= " FROM #prefix#owners AS o ";
        $q .= " WHERE o.user_email = '".$email."';";
        $sql_result = $this->executeSQL($q);
        $row = mysql_fetch_assoc($sql_result);
        mysql_free_result($sql_result);
        return new Owner($row);
    }
    public function getAllOwners() {
        $q = " SELECT o.id AS id, o.user_name AS user_name, o.full_name AS full_name, o.user_email AS user_email, is_admin, last_login";
        $q .= " FROM #prefix#owners AS o ";
        $q .= " ORDER BY last_login DESC;";
        $sql_result = $this->executeSQL($q);
        $owners = array();
        while ($row = mysql_fetch_assoc($sql_result)) {
            $owners[] = new Owner($row);
        }
        mysql_free_result($sql_result); # Free up memory
        return $owners;
    }

    public function doesOwnerExist($email) {
        $q = " SELECT user_email ";
        $q .= " FROM #prefix#owners ";
        $q .= " WHERE user_email='".$email."'";
        $sql_result = $this->executeSQL($q);
        if (mysql_num_rows($sql_result) != 0) {
            return true;
        } else {
            return false;
        }
    }
    public function getForLogin($email) {
        $q = " SELECT o.id AS id, o.user_email AS mail, o.user_name AS name, o.user_pwd AS pwd ";
        $q .= " FROM #prefix#owners AS o ";
        $q .= " WHERE o.user_email = '".$email."' AND user_activated='1'";
        $q .= " LIMIT 1;";
        $sql_result = $this->executeSQL($q);
        if (mysql_num_rows($sql_result) != 0) {
            $row = mysql_fetch_assoc($sql_result);
            mysql_free_result($sql_result);
            return $row;
        } else {
            return false;
        }
    }
    public function getPass($email) {
        $q = " SELECT o.user_pwd AS pwd ";
        $q .= " FROM #prefix#owners AS o ";
        $q .= " WHERE o.user_email = '".$email."' AND user_activated='1'";
        $q .= " LIMIT 1;";
        $sql_result = $this->executeSQL($q);
        if (mysql_num_rows($sql_result) != 0) {
            $row = mysql_fetch_assoc($sql_result);
            mysql_free_result($sql_result);
            return $row;
        } else {
            return false;
        }
    }
    public function getActivationCode($email) {
        $q = " SELECT o.activation_code AS activation_code ";
        $q .= " FROM #prefix#owners AS o ";
        $q .= " WHERE user_email='".$email."'";
        $sql_result = $this->executeSQL($q);
        if (mysql_num_rows($sql_result) != 0) {
            $row = mysql_fetch_assoc($sql_result);
            mysql_free_result($sql_result);
            return $row;
        }
    }
    public function updateActivate($email) {
        $q = " UPDATE #prefix#owners ";
        $q .= " SET user_activated=1 ";
        $q .= " WHERE user_email='".$email."'";
        $this->executeSQL($q);
    }
    public function updatePassword($email, $pwd) {
        $q = " UPDATE #prefix#owners ";
        $q .= " SET user_pwd='".$pwd."' ";
        $q .= " WHERE user_email='".$email."'";
        $this->executeSQL($q);
    }
    public function create($email, $pass, $country, $acode, $fullname) {
        $q = "INSERT INTO #prefix#owners ";
        $q .= " (`user_email`,`user_pwd`,`country`,`joined`,`activation_code`,`full_name`)";
        $q .= " VALUES ('".$email."','".$pass."','".$country."',now(),'".$acode."','".$fullname."')";
        $this->executeSQL($q);
    }

    public function updateLastLogin($email) {
        $q = " UPDATE #prefix#owners ";
        $q .= " SET last_login=now() ";
        $q .= " WHERE user_email='".$email."'";
        $this->executeSQL($q);
    }
}

?>
