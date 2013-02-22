<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.PostErrorDAO.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
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
 * PostError Data Access Object
 *
 * Inserts post errors into the tu_post_error table.
 * Example post error text includes:
 * "No status found with that ID."
 * "Sorry, you are not authorized to see this status."
 * "This account is currently suspended."
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
interface PostErrorDAO {
    /**
     * Insert a post error
     * @param int $post_id ID of the post that got the error
     * @param int $error_code The HTTP error code (such as 404 not found or 403 not authorized)
     * @param string $error_text Description of the error
     * @param int $issued_to ID of the authorized user who got the error.
     */
    public function insertError($post_id, $network, $error_code, $error_text, $issued_to);
}
