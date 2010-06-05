<?php
require_once 'model/class.PDODAO.php';
require_once 'model/interface.PostErrorDAO.php';

/**
 * Post Error MySQL Data Access Object Implementation
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class PostErrorMySQLDAO extends PDODAO implements PostErrorDAO {
    public function insertError($post_id, $error_code, $error_text, $issued_to_user_id) {
        $q = "INSERT INTO #prefix#post_errors (post_id, error_code, error_text, error_issued_to_user_id) ";
        $q .= " VALUES (:id, :error_code, :error_text, :issued_to);";
        $vars = array(
            ':id'=>$post_id,
            ':error_code'=>$error_code,
            ':error_text'=>$error_text,
            ':issued_to'=>$issued_to_user_id
        );
        $ps = $this->execute($q, $vars);
        return $this->getInsertId($ps);
    }
}
