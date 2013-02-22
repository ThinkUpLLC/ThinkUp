<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.StreamDataDAO.php
 *
 * Copyright (c) 2011-2013 Amy Unruh
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
 * Stream Data Access Object Interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @author Amy Unruh
 */
interface StreamDataDAO {
    /**
     * Insert stream data.
     * @param str $content
     * @param str $network
     * @return mixed False if no insert, number of rows inserted otherwise
     * @throws Exception
     */
    public function insertStreamData($content, $network = 'twitter');
    /**
     * Get the next item in the stream data table and delete it from the table.
     * @param string $network
     * @return array ($id, $content)
     * @throws StreamingException
     */
    public function retrieveNextItem($network = 'twitter');
    /**
     * Reset the auto increment value to 1 in the stream data table.
     * @return void
     */
    public function resetID();
}
