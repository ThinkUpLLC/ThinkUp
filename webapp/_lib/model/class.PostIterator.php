<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.PostIterator.php
 *
 * Copyright (c) 2009-2013 Mark Wilkie
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
 * Post Iterator.
 *
 * Used to iterate through the cursor of SQL results for Posts.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Mark Wilkie
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 */
class PostIterator implements Iterator {

    /*
     * @var object A PDO statment handle
     */
    private $stmt;

    /*
     * @var Post The current row, cursor value
     */
    private $row;

    /*
     * @var boolean defines if the current interation is valid
     */
    private $valid = false;

    /*
     * @var boolean defines if cursor has been closed
     */
    private $closed_cursor = false;


    /**
     * Contructor
     */
    public function __construct($stmt) {
        $this->stmt = $stmt;
    }

    /**
     * Empty method, for this case nothing
     */
    public function rewind() {
        // we can't rewind this stmt, so this won't do anything
    }

    /**
     * Returns the current row/Post
     * @return Post Current Post
     */
    public function current() {
        return $this->row;
    }

    /**
     * Returns the current Post key/id
     * @return int The current Post id
     */
    public function key() {
        return $this->row->id;
    }

    /*
     * Returns true if there is a row to fetch
     * @return bool There is another value/row
     */
    public function valid() {
        $this->valid = false;
        if (!is_null($this->stmt)) {
            $row = $this->stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $post = new Post($row);
                $this->row = $post;
                $this->valid = true;
            } else {
                // close our cursor...
                $this->closed_cursor = true;
                $this->stmt->closeCursor();
            }
        }
        return $this->valid;
    }

    /**
     * Empty method, for this case does nothing
     */
    public function next() {
        // we handle the row call invalid, so...
    }

    /*
     * Our destructor
     * closes PDO stmt handle/cursor if not closed already
     */
    public function __destruct() {
        // make sure our cursor is closed...
        if (!$this->closed_cursor && isset($this->stmt)) {
            $this->stmt->closeCursor();
        }
    }
}
