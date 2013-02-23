<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.InsightMySQLDAO.php
 *
 * Copyright (c) 2012-2013 Gina Trapani
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
 * Insight Data Access Object MySQL Implementation
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class InsightMySQLDAO  extends PDODAO implements InsightDAO {
    public function getInsight($slug, $instance_id, $date) {
        $q = "SELECT date, instance_id, slug, prefix, text, related_data, filename, emphasis ";
        $q .= "FROM #prefix#insights WHERE ";
        $q .= "slug=:slug AND date=:date AND instance_id=:instance_id";
        $vars = array(
            ':slug'=>$slug,
            ':date'=>$date,
            ':instance_id'=>$instance_id
        );
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);
        return $this->getDataRowAsObject($ps, 'Insight');
    }

    public function getInsightByUsername($network_username, $network, $slug, $date) {
        $q = "SELECT i.*, i.id as insight_key, su.*, u.avatar FROM #prefix#insights i ";
        $q .= "INNER JOIN #prefix#instances su ON i.instance_id = su.id ";
        $q .= "LEFT JOIN #prefix#users u ON (su.network_user_id = u.user_id AND su.network = u.network) ";
        $q .= "WHERE su.is_active = 1 AND ";
        $q .= "i.slug=:slug AND i.date=:date AND su.network_username=:network_username AND su.network = :network ";
        $vars = array(
            ':slug'=>$slug,
            ':date'=>$date,
            ':network_username'=>$network_username,
            ':network'=>$network
        );
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);
        $row = $this->getDataRowAsArray($ps);
        if (isset($row)) {
            $insight = new Insight($row);
            $insight->related_data = unserialize($insight->related_data);
            //assume insight came at same time of day as now for relative day notation
            $insight->date = $insight->date. " ".date('H').":".date('i');
            $insight->instance = new Instance($row);
            $insight->instance->avatar = $row['avatar'];
            return $insight;
        } else {
            return null;
        }
    }

    public function doesInsightExist($slug, $instance_id) {
        $q = "SELECT date, instance_id, slug, prefix, text, related_data, emphasis FROM #prefix#insights WHERE ";
        $q .= "slug=:slug AND instance_id=:instance_id";
        $vars = array(
            ':slug'=>$slug,
            ':instance_id'=>$instance_id
        );
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);
        $result = $this->getDataRowsAsArrays($ps);
        return (sizeof($result) > 0);
    }

    public function getPreCachedInsightData($slug, $instance_id, $date) {
        $insight = self::getInsight($slug, $instance_id, $date);
        if (isset($insight->related_data) && $insight->related_data != '') {
            return unserialize($insight->related_data);
        } else {
            return null;
        }
    }

    public function insertInsight($slug, $instance_id, $date, $prefix, $text, $filename,
    $emphasis=Insight::EMPHASIS_LOW, $related_data=null) {
        $insight = self::getInsight($slug, $instance_id, $date);
        if ($insight == null) {
            $q = "INSERT INTO #prefix#insights SET slug=:slug, date=:date, instance_id=:instance_id, ";
            $q .= "prefix=:prefix, text=:text, filename=:filename, emphasis=:emphasis, related_data=:related_data";
            $vars = array(
            ':slug'=>$slug,
            ':date'=>$date,
            ':instance_id'=>$instance_id,
            ':prefix'=>$prefix,
            ':text'=>$text,
            ':filename'=>$filename,
            ':emphasis'=>$emphasis,
            ':related_data'=>$related_data
            );
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $ps = $this->execute($q, $vars);
            $result = $this->getUpdateCount($ps);
            return ($result > 0);
        } else {
            return self::updateInsight($slug, $instance_id, $date, $prefix, $text, $emphasis, $related_data);
        }
    }

    public function getInsights($instance_id, $page_count=10, $page_number=1) {
        $start_on_record = ($page_number - 1) * $page_count;
        $q = "SELECT * FROM #prefix#insights WHERE instance_id=:instance_id ";
        $q .= "ORDER BY date DESC, emphasis DESC, id DESC  LIMIT :start_on_record, :limit;";
        $vars = array(
            ':instance_id'=>$instance_id,
            ":start_on_record"=>(int)$start_on_record,
            ":limit"=>(int)$page_count
        );
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);
        $insights = $this->getDataRowsAsObjects($ps, "Insight");
        foreach ($insights as $insight) {
            $insight->related_data = unserialize($insight->related_data);
            //assume insight came at same time of day as now for relative day notation
            $insight->date = $insight->date. " ".date('H').":".date('i');
        }
        return $insights;
    }

    public function deleteInsight($slug, $instance_id, $date) {
        $q = "DELETE FROM #prefix#insights WHERE ";
        $q .= "slug=:slug AND date=:date AND instance_id=:instance_id";
        $vars = array(
            ':slug'=>$slug,
            ':date'=>$date,
            ':instance_id'=>$instance_id
        );
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);
        $result = $this->getUpdateCount($ps);
        return ($result > 0);
    }

    public function updateInsight($slug, $instance_id, $date, $prefix, $text, $emphasis=Insight::EMPHASIS_LOW,
    $related_data=null) {
        $q = "UPDATE #prefix#insights SET prefix=:prefix, text=:text, related_data=:related_data, emphasis=:emphasis ";
        $q .= "WHERE slug=:slug AND date=:date AND instance_id=:instance_id";
        $vars = array(
            ':slug'=>$slug,
            ':date'=>$date,
            ':instance_id'=>$instance_id,
            ':prefix'=>$prefix,
            ':text'=>$text,
            ':related_data'=>$related_data,
            ':emphasis'=>$emphasis
        );
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);
        $result = $this->getUpdateCount($ps);
        return ($result > 0);
    }

    public function deleteInsightsBySlug($slug, $instance_id) {
        $q = "DELETE FROM #prefix#insights WHERE ";
        $q .= "slug=:slug AND instance_id=:instance_id";
        $vars = array(
            ':slug'=>$slug,
            ':instance_id'=>$instance_id
        );
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);

        $ps = $this->execute($q, $vars);
        $result = $this->getUpdateCount($ps);
        return ($result > 0);
    }

    public function getPublicInsights($page_count=10, $page_number=1) {
        return self::getInsightsForInstances($page_count, $page_number, $public_only = true);
    }

    public function getAllInstanceInsights($page_count=10, $page_number=1) {
        return self::getInsightsForInstances($page_count, $page_number, $public_only = false);
    }

    public function getAllOwnerInstanceInsights($owner_id, $page_count=20, $page_number=1) {
        $start_on_record = ($page_number - 1) * $page_count;
        $q = "SELECT i.*, i.id as insight_key, su.*, u.avatar FROM #prefix#insights i ";
        $q .= "INNER JOIN #prefix#instances su ON i.instance_id = su.id ";
        $q .= "INNER JOIN #prefix#owner_instances oi ON su.id = oi.instance_id ";
        $q .= "LEFT JOIN #prefix#users u ON (su.network_user_id = u.user_id AND su.network = u.network) ";
        $q .= "WHERE su.is_active = 1 AND oi.owner_id = :owner_id ";
        $q .= "AND i.text != '' ORDER BY date DESC, emphasis DESC, i.id DESC LIMIT :start_on_record, :limit;";
        $vars = array(
            ":start_on_record"=>(int)$start_on_record,
            ":limit"=>(int)$page_count,
            ":owner_id"=>(int)$owner_id
        );
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);
        $rows = $this->getDataRowsAsArrays($ps);
        $insights = array();
        foreach ($rows as $row) {
            $insight = new Insight($row);
            $insight->instance = new Instance($row);
            $insight->instance->avatar = $row['avatar'];
            $insights[] = $insight;
        }
        foreach ($insights as $insight) {
            $insight->related_data = unserialize($insight->related_data);
            //assume insight came at same time of day as now for relative day notation
            $insight->date = $insight->date. " ".date('H').":".date('i');
        }
        return $insights;
    }

    private function getInsightsForInstances($page_count=10, $page_number=1, $public_only = true) {
        $start_on_record = ($page_number - 1) * $page_count;
        $q = "SELECT i.*, i.id as insight_key, su.*, u.avatar FROM #prefix#insights i ";
        $q .= "INNER JOIN #prefix#instances su ON i.instance_id = su.id ";
        $q .= "LEFT JOIN #prefix#users u ON (su.network_user_id = u.user_id AND su.network = u.network) ";
        $q .= "WHERE su.is_active = 1 ";
        if ($public_only) {
            $q .= "AND su.is_public = 1 ";
        }
        $q .= " AND i.text != '' ORDER BY date DESC, emphasis DESC, i.id DESC LIMIT :start_on_record, :limit;";
        $vars = array(
            ":start_on_record"=>(int)$start_on_record,
            ":limit"=>(int)$page_count
        );
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);
        $rows = $this->getDataRowsAsArrays($ps);
        $insights = array();
        foreach ($rows as $row) {
            $insight = new Insight($row);
            $insight->instance = new Instance($row);
            $insight->instance->avatar = $row['avatar'];
            $insights[] = $insight;
        }
        foreach ($insights as $insight) {
            $insight->related_data = unserialize($insight->related_data);
            //assume insight came at same time of day as now for relative day notation
            $insight->date = $insight->date. " ".date('H').":".date('i');
        }
        return $insights;
    }
}