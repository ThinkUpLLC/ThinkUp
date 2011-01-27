<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.MySQLDAO.deprecated.php
 *
 * Copyright (c) 2009-2011 Gina Trapani
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
 *
 *
 * Do not use this class in any new code. Instead, use the PDODAO system in place. This deprecated class is still here
 * only for existing unit tests that use it. The production webapp no longer utilizes this class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class MySQLDAO {
    var $logger;
    var $db;

    public function __construct($d, $l=null) {
        $this->logger = $l;
        $this->db = $d;
    }

    function executeSQL($q) {
        $r = null;
        try {
            $r = $this->db->exec($q);
        } catch(Exception $e) {
            if ( isset($this->logger) && $this->logger != null ){
                $this->logger->logInfo($e->getMessage(), __METHOD__.','.__LINE__);
            } else {
                die(get_class($this) ." | " . $e->getMessage());
            }
        }
        return $r;
    }
}
