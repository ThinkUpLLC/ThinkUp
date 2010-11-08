<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.MutexDAO.php
 *
 * Copyright (c) 2009-2010 Guillaume Boudreau
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
 * Mutex Data Access Object interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Guillaume Boudreau
 * @author Guillaume Boudreau <gboudreau@pommepause.com>
 *
 */
interface MutexDAO {
    /**
     * Try to obtain a named mutex.
     * @param string $name
     * @return boolean True if the mutex was obtained, false if another thread was already holding this mutex.
     */
    public function getMutex($name);

    /**
     * Release a named mutex.
     * @param string $name
     */
    public function releaseMutex($name);

}