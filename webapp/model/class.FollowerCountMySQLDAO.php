<?php
require_once 'model/class.PDODAO.php';
require_once 'model/interface.FollowerCountDAO.php';

/**
 * Follower Count MySQL Data Access Object Implementation
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class FollowerCountMySQLDAO extends PDODAO implements FollowerCountDAO {
    public function insert($network_user_id, $network, $count){
        $q  = " INSERT INTO #prefix#follower_count ";
        $q .= " (network_user_id, network, date, count) ";
        $q .= " VALUES ( :network_user_id, :network, NOW(), :count );";
        $vars = array(
            ':network_user_id'=>$network_user_id, 
            ':network'=>$network,
            ':count'=>$count
        );
        $ps = $this->execute($q, $vars);
        return $this->getInsertCount($ps);
    }

    public function getHistory($network_user_id, $network, $since_date) {
        $q  = "SELECT * FROM #prefix#follower_count AS fc ";
        $q .= "WHERE fc.network_user_id = :network_user_id AND fc.network=:network ";
        $q .= "AND fc.date > :since_date";
        $vars = array(
            ':network_user_id'=>$network_user_id,
            ':network'=>$network,
            ':since_date'=>$since_date
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsArrays($ps);
    }
}