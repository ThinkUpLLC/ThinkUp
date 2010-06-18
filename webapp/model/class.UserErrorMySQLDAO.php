<?php
require_once 'model/class.PDODAO.php';
require_once 'model/interface.UserErrorDAO.php';

/**
 * User Error MySQL DAO Implementation
 * 
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */

class UserErrorMySQLDAO extends PDODAO implements UserErrorDAO {

    public function insertError($id, $error_code, $error_text, $issued_to) {
        $q = "INSERT INTO #prefix#user_errors (user_id, error_code, error_text, error_issued_to_user_id) ";
        $q .= "VALUES (%s, %s, '%s', %s) ";
        $q = sprintf($q, mysql_real_escape_string($id), mysql_real_escape_string($error_code), mysql_real_escape_string($error_text), mysql_real_escape_string($issued_to));
        $sql_result = $this->executeSQL($q);
        if (mysql_affected_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }
}
