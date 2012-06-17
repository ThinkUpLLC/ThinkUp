<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.InsightMySQLDAO.php
 *
 * Copyright (c) 2012 Gina Trapani
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
 * Insight Data Access Object MySQL Implementation
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class InsightMySQLDAO  extends PDODAO implements InsightDAO {
    public function getInsight($slug, $instance_id, $date) {
        $q = "SELECT date, instance_id, slug, text, related_data, emphasis FROM #prefix#insights WHERE ";
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

    public function getPreCachedInsightData($slug, $instance_id, $date) {
        $insight = self::getInsight($slug, $instance_id, $date);
        if ($insight->related_data != '') {
            return unserialize($insight->related_data);
        } else {
            return null;
        }
    }

    public function insertInsight($slug, $instance_id, $date, $text, $emphasis=Insight::EMPHASIS_LOW,
    $related_data=null) {
        $insight = self::getInsight($slug, $instance_id, $date);
        if ($insight == null) {
            $q = "INSERT INTO #prefix#insights SET slug=:slug, date=:date, instance_id=:instance_id, ";
            $q .= "text=:text, emphasis=:emphasis, related_data=:related_data";
            $vars = array(
            ':slug'=>$slug,
            ':date'=>$date,
            ':instance_id'=>$instance_id,
            ':text'=>$text,
            ':emphasis'=>$emphasis,
            ':related_data'=>$related_data
            );
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);

            $ps = $this->execute($q, $vars);
            $result = $this->getUpdateCount($ps);
            return ($result > 0);
        } else {
            return self::updateInsight($slug, $instance_id, $date, $text, $emphasis, $related_data);
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
            if ($insight->related_data instanceof Post) {
                $insight->related_data_type = "post";
            } elseif (is_array($insight->related_data)) {
                if ($insight->related_data[0] instanceof User) {
                    $insight->related_data_type = "users";
                } elseif ($insight->related_data[0] instanceof Post) {
                    $insight->related_data_type = "posts";
                } elseif (isset($insight->related_data['history'])) {
                    $insight->related_data_type = "follower_count_history";
                }
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
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);

        $ps = $this->execute($q, $vars);
        $result = $this->getUpdateCount($ps);
        return ($result > 0);
    }

    public function updateInsight($slug, $instance_id, $date, $text, $emphasis=Insight::EMPHASIS_LOW,
    $related_data=null) {
        $q = "UPDATE #prefix#insights SET text=:text, related_data=:related_data, emphasis=:emphasis ";
        $q .= "WHERE slug=:slug AND date=:date AND instance_id=:instance_id";
        $vars = array(
            ':slug'=>$slug,
            ':date'=>$date,
            ':instance_id'=>$instance_id,
            ':text'=>$text,
            ':related_data'=>$related_data,
            ':emphasis'=>$emphasis
        );
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);

        $ps = $this->execute($q, $vars);
        $result = $this->getUpdateCount($ps);
        return ($result > 0);
    }
}