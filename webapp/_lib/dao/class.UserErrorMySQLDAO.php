<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.UserErrorMySQLDAO.php
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
 * User Error MySQL DAO Implementation
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */

class UserErrorMySQLDAO extends PDODAO implements UserErrorDAO {

    public function insertError($id, $error_code, $error_text, $issued_to, $network) {
        $q = "INSERT INTO #prefix#user_errors (user_id, error_code, error_text, error_issued_to_user_id, network) ";
        $q .= "VALUES (:id, :error_code, :error_text, :issued_to, :network) ";
        $vars = array(
            ':id'=>$id, 
            ':error_code'=>$error_code,
            ':error_text'=>$error_text,
            ':issued_to'=>(string)$issued_to,
            ':network'=>$network
        );
        $ps = $this->execute($q, $vars);

        return $this->getInsertCount($ps);
    }
}
