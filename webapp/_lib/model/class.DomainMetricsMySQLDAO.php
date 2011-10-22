<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.DomainMetricsMySQLDAO.php
 *
 * Copyright (c) 2011 SwellPath, Inc.
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
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
 * Domain Metrics MySQL Data Access Object implementation
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011 SwellPath, Inc.
 * @author Christian G. Warden <cwarden[at]xerus[dot]org>
 *
 */
class DomainMetricsMySQLDAO extends PDODAO implements DomainMetricsDAO {
    public function upsert($instance_id, $date, $like_views, $likes) {
        $q  = "REPLACE INTO #prefix#domain_metrics ";
        $q .= "(instance_id, date, widget_like_views, widget_likes) ";
        $q .= "VALUES ( :instance_id, :date, :widget_like_views, :widget_likes);";
        $vars = array(
            ':instance_id'=>$instance_id,
            ':date'=>$date,
            ':widget_like_views'=>$like_views,
            ':widget_likes'=>$likes,
        );
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);
        return $this->getInsertCount($ps);
    }

    public function getEarliest($instance_id) {
        $q = "SELECT UNIX_TIMESTAMP(MIN(date)) AS earliest FROM #prefix#domain_metrics ";
        $q .= "WHERE instance_id = :instance_id";
        $vars = array(
            ':instance_id'=> $instance_id
        );
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);
        $row = $this->fetchAndClose($ps);
        if (! $row) {
            return null;
        }
        return $row['earliest'];
    }

    public function getLatest($instance_id) {
        $q = "SELECT UNIX_TIMESTAMP(MAX(date)) AS latest FROM #prefix#domain_metrics ";
        $q .= "WHERE instance_id = :instance_id";
        $vars = array(
            ':instance_id'=> $instance_id
        );
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);
        $row = $this->fetchAndClose($ps);
        if (! $row) {
            return null;
        }
        return $row['latest'];
    }

    /**
     * Get history of number of times Like button has been seen and clicked on the domain.
     */
    public function getWidgetHistory($network_user_id, $network, $units, $periods_limit=10) {
        if ($units != "DAY" && $units != 'WEEK' && $units != 'MONTH') {
            $units = 'DAY';
        }
        $periods_limit = intval($periods_limit);
        if ($units == 'DAY') {
            $group_by = 'data.date';
        } else if ($units == 'WEEK') {
            $group_by = 'YEAR(data.date), WEEK(data.date)';
        } else if ($units == 'MONTH') {
            $group_by = 'YEAR(data.date), MONTH(data.date)';
        }
        $q = "
          SELECT
            data.date AS full_date,
            DATE_FORMAT(data.date, '%c/%e/%y') AS date,
            SUM(data.widget_like_views) AS widget_like_views,
            SUM(data.widget_likes) AS widget_likes
          FROM
            #prefix#domain_metrics AS data
          INNER JOIN
            #prefix#instances AS i
          ON
            (i.id = data.instance_id AND
            i.network_user_id = :network_user_id AND
            i.network = :network)
          WHERE
            data.date >= DATE_SUB(NOW(), INTERVAL $periods_limit $units)
          GROUP BY
            $group_by
          ORDER BY
            full_date ASC";
        $vars = array(
            ':network_user_id'=>(string) $network_user_id,
            ':network'=>$network,
        );
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);
        $history_rows = $this->getDataRowsAsArrays($ps);
        $resultset = array();
        $metadata = array(
          array('type' => 'date', 'label' => 'Date'),
          array('type' => 'number', 'label' => 'Like Button Views'),
          array('type' => 'number', 'label' => 'Likes'),
        );
        foreach ($history_rows as $row) {
            $timestamp = strtotime($row['full_date']);
            $resultset[] = array('c' => array(
                array('v' => sprintf('new Date(%d,%d,%d)', date('Y', $timestamp), date('n', $timestamp) - 1,
                date('j', $timestamp)), 'f' => $row['date']),
                array('v' => intval($row['widget_like_views'])),
                array('v' => intval($row['widget_likes']))
            ));
        }
        $vis_data = json_encode(array('rows' => $resultset, 'cols' => $metadata));
        // Google Chart docs say that a string of the form "Date(Y,m,d)" should
        // work, but chrome throws an error if we don't use an actual Date
        // object.
        $vis_data = preg_replace('/"(new Date[^"]+)"/', '$1', $vis_data);
        return $vis_data;
    }

}
