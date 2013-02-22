<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.InsightBaselineDAO.php
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
 * Insight Baseline Data Access Object
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
interface InsightBaselineDAO {
    /**
     * Insert insight baseline into storage.
     * @param str $slug
     * @param int $instance_id
     * @param int $value
     * @param str $date If not specified defaults to today
     * @return bool
     */
    public function insertInsightBaseline($slug, $instance_id, $value, $date=null);
    /**
     * Retrieve insight baseline from storage.
     * @param str $slug
     * @param int $instance_id
     * @param str $date If not specified defaults to today
     * @return InsightBaseline
     */
    public function getInsightBaseline($slug, $instance_id, $date=null);
    /**
     * Get a page of insight baselines for an instance.
     * @param int $instance_id
     * @param int $page_count Number of insight baselines to return
     * @param int $page_number Page number
     * @return array InsightBaselines
     */
    public function getInsightBaselines($instance_id, $page_count=10, $page_number=1);
    /**
     * Update insight baseline in storage.
     * @param str $slug
     * @param int $instance_id
     * @param int $value
     * @param str $date If not specified defaults to today
     * @return bool
     */
    public function updateInsightBaseline($slug, $instance_id, $value, $date=null);
    /**
     * Check whether or not a insight baseline exists for an instance by slug.
     * @param $slug
     * @param $instance_id
     * @return bool
     */
    public function doesInsightBaselineExist($slug, $instance_id);
}