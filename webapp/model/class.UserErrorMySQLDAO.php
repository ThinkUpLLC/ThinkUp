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

    public function insertError($id, $error_code, $error_text, $issued_to, $network) {
        $q = "INSERT INTO #prefix#user_errors (user_id, error_code, error_text, error_issued_to_user_id, network) ";
        $q .= "VALUES (:id, :error_code, :error_text, :issued_to, :network) ";
        $vars = array(
            ':id'=>$id, 
            ':error_code'=>$error_code,
            ':error_text'=>$error_text,
            ':issued_to'=>$issued_to,
           ':network'=>$network
        );
        $ps = $this->execute($q, $vars);

        return $this->getInsertCount($ps);
    }
}
