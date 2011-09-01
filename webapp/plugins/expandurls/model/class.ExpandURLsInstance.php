<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/model/class.ExpandedURLsInstance.php
 *
 * Copyright (c) 2011 Gina Trapani
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Randi Miller <techrandy[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011 Gina Trapani
 *
 * ExpandedURLs Instance
 *
 * ExpandedURLs plugin's instance metadata.
 */
class ExpandURLsInstance extends Instance {
    /**
     * @var int Internal unique ID.
     */
    var $id = 1;
    /**
     * @var int Last page of replies fetched for this instance.
     */
    var $cursor = 0;
    /**
     * @var int Last page of tweets fetched for this instance.
     */
    var $finished_checking = time();

    public function __construct($row = false) {
        parent::__construct($row);
        if ($row) {
            $this->id = $row['id'];
            $this->cursor = $row['cursor'];
            $this->finished_checking = $row['finished_checking'];
        }
    }
}