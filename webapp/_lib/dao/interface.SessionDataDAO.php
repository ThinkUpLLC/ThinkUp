<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.LocationDAO.php
 *
 * Copyright (c) 2009-2013 Ekansh Preet Singh, Mark Wilkie
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
 * Location Data Access Object Interface
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Hemant Kumar Singh
 * @author Hemant Kumar Singh <unizen01[at]gmail[dot]com>
 * 
 */

interface SessionDataDAO {
    /**
     * Adds session data(serialized) to DB
     * @param session key and session data in array form
     * @return int update count
     */
    public function addSessionData($session_key, $session_data);
  
    
    /**
     * Returns session data of user
     * @return session data as array
     */
   public function getSessionData($session_key);
   
   /**
     * Clears session data
     * 
     */
   
   
   public function clearSessionData($session_key);
      /**
     * Checks for duplicate key
     * 
     */
   
   
   public function isSessionKeyDuplicate($session_key);
}