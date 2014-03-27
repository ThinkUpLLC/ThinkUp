<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.CountHistoryMySQLDAO.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkup.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 *
 * Count History MySQL Data Access Object Implementation
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class CountHistoryMySQLDAO extends PDODAO implements CountHistoryDAO {

    public function insert($network_user_id, $network, $count, $post_id, $type, $date=null) {
        $q  = "INSERT INTO #prefix#count_history ";
        $q .= "(network_user_id, network, date, count, post_id, type) ";
        $q .= "VALUES ( :network_user_id, :network, :date, :count, :post_id, :type )";
        $vars = array(
            ':network_user_id'=>(string) $network_user_id,
            ':network'=>$network,
            ':count'=>$count,
            ':post_id'=>$post_id,
            ':type'=>$type
        );
        if (isset($date)) {
            $vars[':date'] = $date;
        } else {
            $vars[':date'] = date('Y-m-d');
        }
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getInsertCount($ps);
    }

    public function getHistory($network_user_id, $network, $units, $limit=10, $before_date=null, $type='followers',
                               $trend_minimum = null) {
        if ($before_date == date('Y-m-d')) {
            $before_date = null;
        }
        if ($units != "DAY" && $units != 'WEEK' && $units != 'MONTH') {
            $units = 'DAY';
        }
        if ($units == 'DAY') {
            $group_by = 'fc.date';
            $date_format = "DATE_FORMAT(date, '%m/%d/%Y')";
        } else if ($units == 'WEEK') {
            $group_by = 'YEARWEEK(fc.date)';
            $date_format = "DATE_FORMAT(date, '%m/%e')";
        } else if ($units == 'MONTH') {
            $group_by = 'YEAR(fc.date), MONTH(fc.date)';
            $date_format = "DATE_FORMAT(date,'%m/01/%Y')";
        }
        if ($trend_minimum === null) {
            $trend_minimum = $limit;
        }
        $vars = array(
            ':network_user_id'=>(string) $network_user_id,
            ':network'=>$network,
            ':type'=>$type,
            ':limit'=>(int)$limit
        );
        $q = "SELECT network_user_id, network, count, date, full_date FROM ";
        $q .= "(SELECT network_user_id, network, count, ".$date_format." as date, date as full_date ";
        $q .= "FROM #prefix#count_history AS fc ";
        $q .= "WHERE fc.network_user_id = :network_user_id AND fc.network=:network AND type=:type ";
        if ($before_date != null) {
            $q .= "AND date <= :before_date ";
            $vars[':before_date'] = $before_date;
        }
        $q .= "GROUP BY ".$group_by." ORDER BY full_date DESC LIMIT :limit ) as history_counts ";
        $q .= "ORDER BY history_counts.full_date ASC";
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }

        $ps = $this->execute($q, $vars);
        $history_rows = $this->getDataRowsAsArrays($ps);

        $resultset = array();
        switch ($network) {
            case 'facebook':
                $follower_description = 'Friends';
                break;
            case 'facebook page':
                $follower_description = 'Fans';
                break;
            case 'twitter':
            default:
                $follower_description = 'Followers';
                break;
        }
        if ($type == 'group_memberships') {
            $follower_description = 'Lists';
        }
        foreach ($history_rows as $row) {
            $timestamp = strtotime($row['full_date']);
            $resultset[] = array('c' => array(
            array('v' => sprintf('new Date(%d,%d,%d)', date('Y', $timestamp), date('n', $timestamp) - 1,
            date('j', $timestamp)), 'f' => $row['date']),
            array('v' => intval($row['count']))
            ));
        }
        $metadata = array(
        array('type' => 'date', 'label' => 'Date'),
        array('type' => 'number', 'label' => $follower_description),
        );
        $vis_data = json_encode(array('rows' => $resultset, 'cols' => $metadata));
        // Google Chart docs say that a string of the form "Date(Y,m,d)" should
        // work, but chrome throws an error if we don't use an actual Date
        // object.
        $vis_data = preg_replace('/"(new Date[^"]+)"/', '$1', $vis_data);

        if (sizeof($history_rows) > 1 ) {
            //break down rows into a simpler date=>count assoc array
            $simplified_history = array();
            foreach ($history_rows as $history_row) {
                $simplified_history[$history_row["date"]] = $history_row["count"];
            }

            $trend = false;
            if (sizeof($history_rows) >= $trend_minimum) { //we have a complete data set
                //calculate the trend
                $first_follower_count = reset($simplified_history);
                $last_follower_count = end($simplified_history);
                $trend = ($last_follower_count - $first_follower_count)/sizeof($simplified_history);
                $trend = intval(round($trend));
                //complete data set
                $history = $simplified_history;
            }

            if ($type == 'group_memberships') {
                //calculate the point percentages
                $percentages = array();

                $max_count = intval($history_rows[0]['count']);
                $min_count = intval($history_rows[0]['count']);
                foreach ($history_rows as $row) {
                    $min_count = ($row['count'] < $min_count)?intval($row['count']):$min_count;
                    $max_count = ($row['count'] > $max_count)?intval($row['count']):$max_count;
                }
                $difference = $max_count - $min_count;
                foreach ($history as $data_point) {
                    if ($data_point == 'no data') {
                        $percentages[] = 0;
                    } else {
                        $amount_above_min = $data_point - $min_count;
                        $percentages[] = round(Utils::getPercentage($amount_above_min, $difference));
                    }
                }
            }
            $history = $simplified_history;

            $milestone = Utils::predictNextMilestoneDate(intval($history_rows[sizeof($history_rows)-1]['count']),
            $trend);
            //If $before_date set, add difference between then and now to how long it will take
            if (isset($before_date) ) {
                if ($units=='DAY') {
                    $current_day_of_year = date('z');
                    $before_date_day_of_year = date('z', strtotime($before_date));
                    if (date('Y') == date('Y', strtotime($before_date))) {
                        $difference = $current_day_of_year - $before_date_day_of_year;
                        if ($milestone['will_take'] > $difference) {
                            $milestone['will_take'] = $milestone['will_take'] + $difference;
                        }
                    }
                } elseif ($units=='WEEK') {
                    $current_week_of_year = date('W');
                    $before_date_week_of_year = date('W', strtotime($before_date));
                    if (date('Y') == date('Y', strtotime($before_date))) {
                        $difference = $current_week_of_year - $before_date_week_of_year;
                        if ($milestone['will_take'] > $difference) {
                            $milestone['will_take'] = $milestone['will_take'] + $difference;
                        }
                    }
                } elseif ($units=='MONTH') {
                    $current_month_of_year = date('n');
                    $before_date_month_of_year = date('n', strtotime($before_date));
                    if (date('Y') == date('Y', strtotime($before_date))) {
                        $difference = $current_month_of_year - $before_date_month_of_year;
                        if ($milestone['will_take'] > $difference) {
                            $milestone['will_take'] = $milestone['will_take'] + $difference;
                        }
                    }
                }
            }

            if (isset($milestone)) {
                $milestone['units_of_time'] = $units;
            }
            //only set milestone if it's within 20 to avoid "954 weeks until you reach 1000 followers" messaging
            if ($milestone['will_take'] > 20) {
                $milestone = null;
            }
        } else  {
            $history = false;
            $trend = false;
            $milestone = false;
        }
        return array('history'=>$history, 'trend'=>$trend, 'milestone'=> $milestone, 'vis_data' => $vis_data);
    }

    public function getCountsByPostID($post_id) {
        $q = "SELECT network_user_id, post_id, network, type, date, count FROM #prefix#count_history WHERE ";
        $q .= "post_id=:post_id";
        $vars[':post_id'] = $post_id;
        $ps = $this->execute($q, $vars);
        $rows = $this->getDataRowsAsArrays($ps);
        return $rows;
    }

    public function getCountsByPostIDAndType($post_id, $type) {
        $q = "SELECT network_user_id, post_id, network, type, date, count FROM #prefix#count_history WHERE ";
        $q .= "post_id=:post_id AND type=:type";
        $vars[':post_id'] = $post_id;
        $vars[':type'] = $type;
        $ps = $this->execute($q, $vars);
        $rows = $this->getDataRowsAsArrays($ps);
        return $rows;
    }

    public function sumCountsOverTimePeriod($post_id, $type, $start_date, $end_date) {
        $q = "SELECT SUM(count) as count FROM #prefix#count_history WHERE post_id=:post_id AND type=:type AND date ";
        $q .= "BETWEEN :start_date AND :end_date";
        $vars[':post_id'] = $post_id;
        $vars[':type'] = $type;
        $vars[':start_date'] = $start_date;
        $vars[':end_date'] = $end_date;
        $ps = $this->execute($q, $vars);
        $rows = $this->getDataCountResult($ps);
        return $rows;
    }

    public function getLatestCountByPostIDAndType($post_id, $type) {
        $q = "SELECT network_user_id, post_id, network, type, date, count FROM #prefix#count_history WHERE ";
        $q .= "post_id=:post_id AND type=:type ORDER BY date DESC";
        $vars[':post_id'] = $post_id;
        $vars[':type'] = $type;
        $ps = $this->execute($q, $vars);
        $rows = $this->getDataRowsAsArrays($ps);
        return $rows[0];
    }

    public function updateGroupMembershipCount($network_user_id, $network) {
        $q  = "INSERT INTO #prefix#count_history ";
        $q .= "(network_user_id, network, type, date, count) ";
        $q .= "SELECT :network_user_id, :network, 'group_memberships', NOW(), COUNT(group_id) ";
        $q .= "FROM #prefix#group_members WHERE member_user_id = :network_user_id ";
        $q .= "AND network = :network AND is_active = 1";
        $vars = array(
            ':network_user_id'=>(string) $network_user_id,
            ':network'=>$network,
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getInsertCount($ps);
    }

    public function getLatestCountByNetworkUserIDAndType($network_user_id, $network, $type) {
        $q = "SELECT network_user_id, post_id, network, type, date, count FROM #prefix#count_history WHERE ";
        $q .= "network_user_id=:network_user_id AND network=:network AND type=:type ORDER BY date DESC, count DESC";
        $vars[':network_user_id'] = $network_user_id;
        $vars[':network'] = $network;
        $vars[':type'] = $type;
        $ps = $this->execute($q, $vars);
        $row = $this->getDataRowAsArray($ps);
        return $row;
    }
}
