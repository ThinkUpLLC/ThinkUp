<?php
/**
 * Owner Data Access Object
 * The data access object for retrieving and saving owners in the ThinkUp database.
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class OwnerMySQLDAO extends PDODAO implements OwnerDAO {

    public function getByEmail($email) {
        $q = <<<SQL
SELECT
    id,
    full_name,
    email,
    is_admin,
    last_login,
    is_activated,
    password_token
FROM #prefix#owners AS o
WHERE email = :email;
SQL;

        $vars = array(
            ':email'=>$email
        );
        $ps = $this->execute($q, $vars);
        return $this->getDataRowAsObject($ps, 'Owner');
    }

    public function getAllOwners() {
        $q = " SELECT id, full_name, email, is_admin, is_activated, last_login ";
        $q .= "FROM #prefix#owners ORDER BY last_login DESC;";
        $ps = $this->execute($q);
        return $this->getDataRowsAsObjects($ps, 'Owner');
    }

    public function doesOwnerExist($email) {
        $q = " SELECT email FROM #prefix#owners WHERE email=:email";
        $vars = array(
            ':email'=>$email
        );
        $ps = $this->execute($q, $vars);
        return $this->getDataIsReturned($ps, $vars);
    }

    public function getPass($email) {
        $q = "SELECT pwd FROM #prefix#owners  WHERE email = :email AND is_activated='1' LIMIT 1;";
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
        $q = " SELECT activation_code  FROM #prefix#owners  WHERE email=:email";
        $vars = array(
            ':email'=>$email
        );
        $ps = $this->execute($q, $vars);
        return $this->getDataRowAsArray($ps);
    }

    public function updateActivate($email) {
        $q = " UPDATE #prefix#owners SET is_activated=1 WHERE email=:email";
        $vars = array(
            ':email'=>$email
        );
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }

    public function updatePassword($email, $pwd) {
        $q = " UPDATE #prefix#owners SET pwd=:pwd WHERE email=:email";
        $vars = array(
            ':email'=>$email,
            ':pwd'=>$pwd
        );
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }

    public function create($email, $pass, $acode, $full_name) {
        if (!$this->doesOwnerExist($email)) {
            $q = "INSERT INTO #prefix#owners SET email=:email, pwd=:pass, joined=NOW(),activation_code=:acode, ";
            $q .= "full_name=:full_name ";
            $vars = array(
                ':email'=>$email,
                ':pass'=>$pass,
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
        $q = " UPDATE #prefix#owners SET last_login=now() WHERE email=:email";
        $vars = array(
            ':email'=>$email
        );
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }

    public function updatePasswordToken($email, $token) {
        $q = "UPDATE #prefix#owners
              SET password_token=:token
              WHERE email=:email";
        $vars = array(
            ":token" => $token, 
            ":email" => $email
        );
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }

    public function getByPasswordToken($token) {
        $q = "SELECT * FROM #prefix#owners WHERE password_token LIKE :token";
        $vars = array(':token' => $token . '_%');
        $ps = $this->execute($q, $vars);
        return $this->getDataRowAsObject($ps, 'Owner');
    }
}
