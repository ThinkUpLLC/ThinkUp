<?php
require_once 'model/class.PDODAO.php';
require_once 'model/interface.OwnerDAO.php';

/**
 * Owner Data Access Object
 * The data access object for retrieving and saving owners in the ThinkTank database
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class OwnerMySQLDAO extends PDODAO implements OwnerDAO {

    public function getByEmail($email) {
        $q = " SELECT o.id AS id, o.user_name AS user_name, o.full_name AS full_name, o.user_email AS user_email, ";
        $q .= "is_admin, last_login, user_activated as is_activated, user_pwd as pwd ";
        $q .= " FROM #prefix#owners AS o ";
        $q .= " WHERE o.user_email = :email;";
        $vars = array(
            ':email'=>$email
        );
        $ps = $this->execute($q, $vars);
        return $this->getDataRowAsObject($ps, 'Owner');
    }

    public function getAllOwners() {
        $q = " SELECT o.id AS id, o.user_name AS user_name, o.full_name AS full_name, o.user_email AS user_email, ";
        $q .= "is_admin, last_login ";
        $q .= "FROM #prefix#owners AS o ";
        $q .= "ORDER BY last_login DESC;";
        $ps = $this->execute($q);
        return $this->getDataRowsAsObjects($ps, 'Owner');
    }

    public function doesOwnerExist($email) {
        $q = " SELECT user_email ";
        $q .= " FROM #prefix#owners ";
        $q .= " WHERE user_email=:email";
        $vars = array(
            ':email'=>$email
        );
        $ps = $this->execute($q, $vars);
        return $this->getDataIsReturned($ps, $vars);
    }

    public function getPass($email) {
        $q = " SELECT o.user_pwd AS pwd ";
        $q .= " FROM #prefix#owners AS o ";
        $q .= " WHERE o.user_email = :email AND user_activated='1'";
        $q .= " LIMIT 1;";
        $vars = array(
            ':email'=>$email
        );
        $ps = $this->execute($q, $vars);
        $result = $this->getDataRowAsArray($ps);
        if (isset($result['pwd'])) {
            return $result['pwd'];
        } else {
            return false;
        }
    }

    public function getActivationCode($email) {
        $q = " SELECT o.activation_code AS activation_code ";
        $q .= " FROM #prefix#owners AS o ";
        $q .= " WHERE user_email=:email";
        $vars = array(
            ':email'=>$email
        );
        $ps = $this->execute($q, $vars);
        return $this->getDataRowAsArray($ps);
    }

    public function updateActivate($email) {
        $q = " UPDATE #prefix#owners ";
        $q .= " SET user_activated=1 ";
        $q .= " WHERE user_email=:email";
        $vars = array(
            ':email'=>$email
        );
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }

    public function updatePassword($email, $pwd) {
        $q = " UPDATE #prefix#owners ";
        $q .= " SET user_pwd=:pwd ";
        $q .= " WHERE user_email=:email";
        $vars = array(
            ':email'=>$email,
            ':pwd'=>$pwd
        );
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }

    public function create($email, $pass, $country, $acode, $full_name) {
        if (!$this->doesOwnerExist($email)) {
            $q = "INSERT INTO #prefix#owners SET ";
            $q .= "user_email=:email, user_pwd=:pass, country=:country, joined=NOW(),activation_code=:acode, ";
            $q .= "full_name=:full_name ";
            $vars = array(
                ':email'=>$email,
                ':pass'=>$pass,
                ':country'=>$country,
                ':acode'=>$acode,
                ':full_name'=>$full_name
            );
            $ps = $this->execute($q, $vars);
            return $this->getUpdateCount($ps);
        } else {
            return 0;
        }
    }

    public function updateLastLogin($email) {
        $q = " UPDATE #prefix#owners ";
        $q .= " SET last_login=now() ";
        $q .= " WHERE user_email=:email";
        $vars = array(
            ':email'=>$email
        );
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }
}
