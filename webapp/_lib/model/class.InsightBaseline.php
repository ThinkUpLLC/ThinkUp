<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.InsightBaseline.php
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
 * InsightBaseline
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class InsightBaseline {
    /**
     * @var date Date of baseline statistic.
     */
    var $date;
    /**
     * @var int Instance ID.
     */
    var $instance_id;
    /**
     * @var str Unique identifier for a type of statistic.
     */
    var $slug;
    /**
     * @var int The numeric value of this stat/total/average.
     */
    var $value;
    public function __construct($row = false) {
        if ($row) {
            $this->date = $row['date'];
            $this->instance_id = $row['instance_id'];
            $this->slug = $row['slug'];
            $this->value = $row['value'];
        }
    }
}