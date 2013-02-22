<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.BackupDAO.php
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
 * Backup Data Access Object Interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Mark Wilkie
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 */

interface BackupDAO {

    /**
     * Export database to tmp dir
     * @param $str Backup File (optional)
     * @return $str Path to backup file
     */
    public function export($backup_file = null);

    /**
     * Import database zip file
     * @ str Import zip file
     * @return boolean tru if suceeds
     */
    public function import($zipfile);
}