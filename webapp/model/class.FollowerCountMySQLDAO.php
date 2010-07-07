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

    public function getHistory($network_user_id, $network, $group_by) {
        if ($group_by != "DAY" && $group_by != 'WEEK' && $group_by != 'MONTH') {
            $group_by = 'DAY';
        }
        $q  = "SELECT network_user_id, network, count, DATE_FORMAT(date, '%c/%e') as date FROM #prefix#follower_count AS fc ";
        $q .= "WHERE fc.network_user_id = :network_user_id AND fc.network=:network ";
        $q .= "GROUP BY ".$group_by."(fc.date) LIMIT 10";
        $vars = array(
            ':network_user_id'=>$network_user_id,
            ':network'=>$network
        );
        $ps = $this->execute($q, $vars);
        $history = $this->getDataRowsAsArrays($ps);
        $percentages = array();
        if (sizeof($history) > 0 ) {
            $max_count = $history[0]['count'];
            $min_count = $history[0]['count'];
            foreach ($history as $row) {
                $min_count = ($row['count'] < $min_count)?$row['count']:$min_count;
                $max_count = ($row['count'] > $max_count)?$row['count']:$max_count;
            }
            $difference = $max_count - $min_count;
            foreach ($history as $row) {
                $amount_above_min = $row['count'] - $min_count;
                $percentages[] = round(Utils::getPercentage($amount_above_min, $difference));
            }
        } else  {
            $bounds = array(0, 0);
        }
        return array('history'=>$history, 'percentages'=>$percentages);
    }
}