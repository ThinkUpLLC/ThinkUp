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
    /**
    * Insights stream global conditional and order by for logged-in and logged-out users
    * @var str
    */
    var $stream_conditionals_order;

    public function __construct() {
        parent::__construct();
        $this->stream_conditionals_order =  "AND i.filename != 'dashboard' ORDER BY date DESC, time_updated DESC, ".
            "emphasis DESC, filename, i.id DESC LIMIT :start_on_record, :limit;";
    }

    public function getInsight($slug, $instance_id, $date) {
        $q = "SELECT date, instance_id, slug, headline, text, related_data, filename, emphasis, header_image ";
        $q .= "FROM #prefix#insights WHERE ";
        $q .= "slug=:slug AND date=:date AND instance_id=:instance_id";
        $vars = array(
            ':slug'=>$slug,
            ':date'=>$date,
            ':instance_id'=>$instance_id
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
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
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $row = $this->getDataRowAsArray($ps);
        if (isset($row)) {
            $insight = new Insight($row);
            if ($row['related_data'] !== null) {
                $insight->related_data = Serializer::unserializeString($row['related_data']);
            }
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
        $q = "SELECT date, instance_id, slug, headline, text, related_data, emphasis FROM #prefix#insights WHERE ";
        $q .= "slug=:slug AND instance_id=:instance_id";
        $vars = array(
            ':slug'=>$slug,
            ':instance_id'=>$instance_id
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $result = $this->getDataRowsAsArrays($ps);
        return (sizeof($result) > 0);
    }

    public function getPreCachedInsightData($slug, $instance_id, $date) {
        $insight = self::getInsight($slug, $instance_id, $date);
        if (isset($insight->related_data) && $insight->related_data != '') {
            return Serializer::unserializeString($insight->related_data);
        } else {
            return null;
        }
    }

    public function insertInsightDeprecated($slug, $instance_id, $date, $headline, $text, $filename,
    $emphasis=Insight::EMPHASIS_LOW, $related_data=null) {
        $insight = self::getInsight($slug, $instance_id, $date);
        if ($insight == null) {
            $q = "INSERT INTO #prefix#insights SET slug=:slug, date=:date, instance_id=:instance_id, ";
            $q .= "headline=:headline, text=:text, filename=:filename, emphasis=:emphasis, ";
            $q .= "related_data=:related_data, time_generated='".date("Y-m-d H:i:s")."'";
            $vars = array(
            ':slug'=>$slug,
            ':date'=>$date,
            ':instance_id'=>$instance_id,
            ':headline'=>$headline,
            ':text'=>$text,
            ':filename'=>$filename,
            ':emphasis'=>$emphasis,
            ':related_data'=>$related_data
            );
            if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
            $ps = $this->execute($q, $vars);
            $result = $this->getUpdateCount($ps);
            return ($result > 0);
        } else {
            return self::updateInsightDeprecated($slug, $instance_id, $date, $headline, $text, $emphasis,
            $related_data);
        }
    }

    public function insertInsight(Insight $insight) {
        //If required fields are not set, throw an exception
        $insight_fields = get_object_vars($insight);
        foreach($insight_fields as $field=>$value) {
            if ($field != 'header_image' && $field != 'related_data' && $field != 'id'
                && $field != 'time_updated' && !isset($value)) {
                throw new InsightFieldNotSetException("Insight ".$field ." is not set.");
            }
        }
        if ($insight->related_data != null) {
            $related_data_for_insert = serialize($insight->related_data);
            if (strlen($related_data_for_insert) > 65535 ) {
                throw new InsightFieldExceedsMaxLengthException("Insight's related data exceeds max length.");
            }
        } else {
            $related_data_for_insert = null;
        }
        $existing_insight = self::getInsight($insight->slug, $insight->instance_id, $insight->date);
        if ($existing_insight == null) {
            $q = "INSERT INTO #prefix#insights SET slug=:slug, date=:date, instance_id=:instance_id, ";
            $q .= "headline=:headline, text=:text, header_image=:header_image, filename=:filename, ";
            $q .= "emphasis=:emphasis, related_data=:related_data, time_generated=:time_generated";
            $vars = array(
            ':slug'=>$insight->slug,
            ':date'=>$insight->date,
            ':instance_id'=>$insight->instance_id,
            ':headline'=>$insight->headline,
            ':text'=>$insight->text,
            ':header_image'=>$insight->header_image,
            ':filename'=>$insight->filename,
            ':emphasis'=>$insight->emphasis,
            ':related_data'=>$related_data_for_insert,
            ':time_generated'=>$insight->time_generated
            );
            if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
            $ps = $this->execute($q, $vars);
            $result = $this->getUpdateCount($ps);
            return ($result > 0);
        } else {
            return self::updateInsight($insight);
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
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $insights = $this->getDataRowsAsObjects($ps, "Insight");
        foreach ($insights as $insight) {
            if ($insight->related_data !== null) {
                $insight->related_data = Serializer::unserializeString($insight->related_data);
            }
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
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $result = $this->getUpdateCount($ps);
        return ($result > 0);
    }

    public function updateInsightDeprecated($slug, $instance_id, $date, $headline, $text,
    $emphasis=Insight::EMPHASIS_LOW, $related_data=null) {
        $q = "UPDATE #prefix#insights SET headline=:headline, text=:text, related_data=:related_data, ";
        $q .= "emphasis=:emphasis WHERE slug=:slug AND date=:date AND instance_id=:instance_id";
        $vars = array(
            ':slug'=>$slug,
            ':date'=>$date,
            ':instance_id'=>$instance_id,
            ':headline'=>$headline,
            ':text'=>$text,
            ':related_data'=>$related_data,
            ':emphasis'=>$emphasis
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $result = $this->getUpdateCount($ps);
        return ($result > 0);
    }

    private function updateInsight(Insight $insight) {
        $q = "UPDATE #prefix#insights SET headline=:headline, text=:text, related_data=:related_data, ";
        $q .= "emphasis=:emphasis, header_image=:header_image ";
        $q .= "WHERE slug=:slug AND date=:date AND instance_id=:instance_id";
        $vars = array(
            ':slug'=>$insight->slug,
            ':date'=>$insight->date,
            ':instance_id'=>$insight->instance_id,
            ':headline'=>$insight->headline,
            ':header_image'=>$insight->header_image,
            ':text'=>$insight->text,
            ':related_data'=>((isset($insight->related_data))?serialize($insight->related_data):null),
            ':emphasis'=>$insight->emphasis
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
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
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }

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

    public function getAllOwnerInstanceInsights($owner_id, $page_count=20, $page_number=1, $page_count_offset=1) {
        $start_on_record = ($page_number - 1) * ($page_count - $page_count_offset);
        $q = "SELECT i.*, i.id as insight_key, su.*, u.avatar FROM #prefix#insights i ";
        $q .= "INNER JOIN #prefix#instances su ON i.instance_id = su.id ";
        $q .= "INNER JOIN #prefix#owner_instances oi ON su.id = oi.instance_id ";
        $q .= "LEFT JOIN #prefix#users u ON (su.network_user_id = u.user_id AND su.network = u.network) ";
        $q .= "WHERE su.is_active = 1 AND oi.owner_id = :owner_id ";
        $q .= $this->stream_conditionals_order;
        $vars = array(
            ":start_on_record"=>(int)$start_on_record,
            ":limit"=>(int)$page_count,
            ":owner_id"=>(int)$owner_id
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $rows = $this->getDataRowsAsArrays($ps);
        $insights = array();
        foreach ($rows as $row) {
            $insight = new Insight($row);
            $insight->instance = new Instance($row);
            $insight->instance->avatar = $row['avatar'];
            if ($row['related_data'] !== null) {
                $insight->related_data = Serializer::unserializeString($row['related_data']);
            }
            //assume insight came at same time of day as now for relative day notation
            $insight->date = $row['date']. " ".date('H').":".date('i');
            $insights[] = $insight;
        }
        return $insights;
    }

    private function getInsightsForInstances($page_count=10, $page_number=1, $public_only = true,
    $page_count_offset =1) {
        $start_on_record = ($page_number - 1) * ($page_count - $page_count_offset);
        $q = "SELECT i.*, i.id as insight_key, su.*, u.avatar FROM #prefix#insights i ";
        $q .= "INNER JOIN #prefix#instances su ON i.instance_id = su.id ";
        $q .= "LEFT JOIN #prefix#users u ON (su.network_user_id = u.user_id AND su.network = u.network) ";
        $q .= "WHERE su.is_active = 1 ";
        if ($public_only) {
            $q .= "AND su.is_public = 1 ";
        }
        $q .= $this->stream_conditionals_order;
        $vars = array(
            ":start_on_record"=>(int)$start_on_record,
            ":limit"=>(int)$page_count
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $rows = $this->getDataRowsAsArrays($ps);
        $insights = array();
        foreach ($rows as $row) {
            $insight = new Insight($row);
            $insight->instance = new Instance($row);
            $insight->instance->avatar = $row['avatar'];
            if ($row['related_data'] !== null) {
                $insight->related_data = Serializer::unserializeString($row['related_data']);
            }
            //assume insight came at same time of day as now for relative day notation
            $insight->date = $row['date']. " ".date('H').":".date('i');
            $insights[] = $insight;
        }
        return $insights;
    }

    public function getAllOwnerInstanceInsightsSince($owner_id, $since) {
        $q = "SELECT i.*, i.id as insight_key, su.*, u.avatar FROM #prefix#insights i ";
        $q .= "INNER JOIN #prefix#instances su ON i.instance_id = su.id ";
        $q .= "INNER JOIN #prefix#owner_instances oi ON su.id = oi.instance_id ";
        $q .= "LEFT JOIN #prefix#users u ON (su.network_user_id = u.user_id AND su.network = u.network) ";
        $q .= "WHERE su.is_active = 1 AND oi.owner_id = :owner_id AND time_generated > :since ";
        $q .= "AND i.filename != 'dashboard' ORDER BY date DESC, emphasis DESC, i.id;";
        $vars = array(
            ":owner_id"=>(int)$owner_id,
            ':since'=>$since
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $rows = $this->getDataRowsAsArrays($ps);
        $insights = array();
        foreach ($rows as $row) {
            $insight = new Insight($row);
            $insight->instance = new Instance($row);
            $insight->instance->avatar = $row['avatar'];
            if ($row['related_data'] !== null) {
                $insight->related_data = Serializer::unserializeString($row['related_data']);
            }
            //assume insight came at same time of day as now for relative day notation
            $insight->date = $row['date']. " ".date('H').":".date('i');
            $insights[] = $insight;
        }
        return $insights;
    }
}