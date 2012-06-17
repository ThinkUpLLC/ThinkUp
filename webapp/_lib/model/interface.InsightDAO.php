<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.InsightDAO.php
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
 * Insight Data Access Object
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
interface InsightDAO {
    /**
     * Insert insight into storage.
     * @param str $slug
     * @param int $instance_id
     * @param str $date
     * @param str $text
     * @param int $emphasis
     * @param str $related_data Defaults to null
     * @return bool
     */
    public function insertInsight($slug, $instance_id, $date, $text, $emphasis=Insight::EMPHASIS_LOW,
    $related_data=null);
    /**
     * Retrieve insight from storage.
     * @param str $slug
     * @param int $instance_id
     * @param str $date
     * @return Insight
     */
    public function getInsight($slug, $instance_id, $date);
    /**
     * Retrieve insight's related data from storage.
     * @param str $slug
     * @param int $instance_id
     * @param str $date
     * @return Insight
     */
    public function getPreCachedInsightData($slug, $instance_id, $date);
    /**
     * Remove insight from storage.
     * @param str $slug
     * @param int $instance_id
     * @param str $date
     * @return bool
     */
    public function deleteInsight($slug, $instance_id, $date);
    /**
     * Get a page of insights for an instance.
     * @param int $instance_id
     * @param int $page_count Number of insight baselines to return
     * @param int $page_number Page number
     * @return array Insights
     */
    public function getInsights($instance_id, $page_count=10, $page_number=1);
    /**
     * Update insight in storage.
     * @param str $slug
     * @param int $instance_id
     * @param int $date;
     * @param str $text
     * @param int $emphasis
     * @param str $related_data Defaults to null.
     * @return bool
     */
    public function updateInsight($slug, $instance_id, $date, $text, $emphasis=Insight::EMPHASIS_LOW,
    $related_data=null);
}