<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.GroupMembershipCountMySQLDAO.php
 *
 * Copyright (c) 2011-2013 SwellPath, Inc.
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
 * Group Membership Count MySQL Data Access Object Implementation
 * (based on class.FollowerCountMySQLDAO.php)
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 SwellPath, Inc.
 * @author Christian G. Warden <cwarden[at]xerus[dot]org>
 *
 */
class GroupMembershipCountMySQLDAO extends PDODAO implements GroupMembershipCountDAO {
    public function insert($network_user_id, $network, $count) {
        $q  = "INSERT INTO #prefix#group_member_count ";
        $q .= "(member_user_id, network, date, count) ";
        $q .= "VALUES ( :network_user_id, :network, NOW(), :count );";
        $vars = array(
            ':network_user_id'=>(string) $network_user_id,
            ':network'=>$network,
            ':count'=>$count
        );
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);
        return $this->getInsertCount($ps);
    }

    public function updateCount($network_user_id, $network) {
        $q  = "INSERT INTO #prefix#group_member_count ";
        $q .= "(member_user_id, network, date, count) ";
        $q .= "SELECT :network_user_id, :network, NOW(), COUNT(group_id) ";
        $q .= "FROM #prefix#group_members WHERE member_user_id = :network_user_id ";
        $q .= "AND network = :network AND is_active = 1";
        $vars = array(
            ':network_user_id'=>(string) $network_user_id,
            ':network'=>$network,
        );
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);
        return $this->getInsertCount($ps);
    }

    public function getHistory($network_user_id, $network, $units, $limit=10) {
        if ($units != "DAY" && $units != 'WEEK' && $units != 'MONTH') {
            $units = 'DAY';
        }
        if ($units == 'DAY') {
            $group_by = 'fc.date';
            $date_format = "DATE_FORMAT(date, '%m/%d/%Y')";
        } else if ($units == 'WEEK') {
            $group_by = 'YEAR(fc.date), WEEK(fc.date)';
            $date_format = "DATE_FORMAT(date, '%m/%e')";
        } else if ($units == 'MONTH') {
            $group_by = 'YEAR(fc.date), MONTH(fc.date)';
            $date_format = "DATE_FORMAT(date,'%m/01/%Y')";
        }
        $q = "SELECT member_user_id, network, count, date, full_date FROM ";
        $q .= "(SELECT member_user_id, network, count, ".$date_format." as date, date as full_date ";
        $q .= "FROM #prefix#group_member_count AS fc ";
        $q .= "WHERE fc.member_user_id = :network_user_id AND fc.network=:network ";
        $q .= "GROUP BY ".$group_by." ORDER BY full_date DESC LIMIT :limit ) as history_counts ";
        $q .= "ORDER BY history_counts.full_date ASC";
        $vars = array(
            ':network_user_id'=>(string) $network_user_id,
            ':network'=>$network,
            ':limit'=>(int)$limit
        );
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);
        $history_rows = $this->getDataRowsAsArrays($ps);

        $resultset = array();
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
          array('type' => 'number', 'label' => 'Lists'),
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
            if (sizeof($history_rows) == $limit) { //we have a complete data set
                //calculate the trend
                $first_group_count = reset($simplified_history);
                $last_group_count = end($simplified_history);
                $trend = ($last_group_count - $first_group_count)/sizeof($simplified_history);
                $trend = intval(round($trend));
                //complete data set
                $history = $simplified_history;
            } else { //there are dates with missing data
                //set up an array of all the dates to show in the chart
                $dates_to_display = array();
                $format = 'n/j';
                $date = date ( $format );
                $i = $limit;
                while ($i > 0 ) {
                    if ($units == "DAY") {
                        $format = 'm/d/Y';
                        $date_ago = date ($format, strtotime('-'.$i.' '.$units.$date));
                    } else if ($units == "WEEK") {
                        if ($i == $limit) {
                            $last_saturday = Utils::getLastSaturday();
                        }
                        $date_ago = date ($format, strtotime('-'.$i.' '.$units.$last_saturday));
                    } else {
                        $first_day_of_this_month = date('n/1');
                        $format = 'm/d/Y';
                        $date_ago = date ($format, strtotime('-'.$i.' '.$units.$first_day_of_this_month));
                    }
                    $dates_to_display[$date_ago] = "no data";
                    $i--;
                }
                //merge the data we do have with the dates we want
                $history = array_merge($dates_to_display, $simplified_history);
                //cut down oversized array
                if (sizeof($history) > $limit) {
                    $history = array_slice($history, (sizeof($history)-$limit));
                }
                if ($units=="DAY") {
                    ksort($history);
                }
            }

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
            $history = $simplified_history;

            $milestone = Utils::predictNextMilestoneDate(intval($history_rows[sizeof($history_rows)-1]['count']),
            $trend);
            if (isset($milestone)) {
                $milestone['units_of_time'] = $units;
            }
            //only set milestone if it's within 10 to avoid "954 weeks until you reach 1000 groups" messaging
            if ($milestone['will_take'] > 10) {
                $milestone = null;
            }
        } else  {
            $history = false;
            $trend = false;
            $milestone = false;
        }
        return array('history'=>$history, 'trend'=>$trend, 'milestone'=> $milestone, 'vis_data' => $vis_data);
    }
}
