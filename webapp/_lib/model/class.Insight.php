<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Insight.php
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
 * Insight
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class Insight {
    /**
     * @var int Internal unique ID.
     */
    var $id;
    /**
     * @var int Instance ID.
     */
    var $instance_id;
    /**
     * @var str Identifier for a type of statistic.
     */
    var $slug;
    /**
     * @var str Text content of the alert.
     */
    var $text;
    /**
     * @var str Serialized related insight data, such as a list of users or a post.
     */
    var $related_data;
    /**
     * @var date Date of insight.
     */
    var $date;
    /**
     * @var int Level of emphasis for insight presentation.
     */
    var $emphasis;
    /**
     * Non-persistent value indicating type of related data, for use in UI.
     * @var str
     */
    var $related_data_type;
    /**
     * High emphasis level.
     * @var int
     */
    const EMPHASIS_HIGH = 2;
    /**
     * Medium emphasis level.
     * @var int
     */
    const EMPHASIS_MED = 1;
    /**
     * Low emphasis level.
     * @var int
     */
    const EMPHASIS_LOW = 0;
    public function __construct($row = false) {
        if ($row) {
            $this->id = $row['id'];
            $this->instance_id = $row['instance_id'];
            $this->slug = $row['slug'];
            $this->text = $row['text'];
            $this->related_data = $row['related_data'];
            $this->date = $row['date'];
            $this->emphasis = $row['emphasis'];
        }
    }
}
