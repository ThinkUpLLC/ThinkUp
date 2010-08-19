<?php
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

    public function getHistory($network_user_id, $network, $group_by, $limit=10) {
        if ($group_by != "DAY" && $group_by != 'WEEK' && $group_by != 'MONTH') {
            $group_by = 'DAY';
        }
        $q = "SELECT network_user_id, network, count, date, full_date FROM ";
        $q .= "(SELECT network_user_id, network, count, DATE_FORMAT(date, '%c/%e') as date, date as full_date ";
        $q .= "FROM #prefix#follower_count AS fc ";
        $q .= "WHERE fc.network_user_id = :network_user_id AND fc.network=:network ";
        $q .= "GROUP BY ".$group_by."(fc.date) ORDER BY full_date DESC LIMIT :limit ) as history_counts ";
        $q .= "ORDER BY history_counts.full_date ASC";
        $vars = array(
            ':network_user_id'=>$network_user_id,
            ':network'=>$network,
            ':limit'=>(int)$limit
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

            $y_axis = array();
            $num_y_axis_points = 4;
            $y_axis_interval_size = $difference/$num_y_axis_points;
            $i = 0;
            while ($i < $num_y_axis_points) {
                $y_axis[$i] = $min_count + ($y_axis_interval_size * $i);
                $i = $i+1;
            }
            $y_axis[$num_y_axis_points-1] = $max_count;
        } else  {
            $history = false;
            $y_axis = false;
        }
        return array('history'=>$history, 'percentages'=>$percentages, 'y_axis'=>$y_axis);
    }
}