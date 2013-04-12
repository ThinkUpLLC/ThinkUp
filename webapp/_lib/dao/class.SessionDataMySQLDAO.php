<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.StreamDataMySQLDAO.php
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013 Hemant Kumar Singh
 * @author Hemant Kumar Singh <unizen01[at]gmail[dot]com>
 */
class SessionDataMySQLDAO extends PDODAO implements SessionDataDAO{

    public function addSessionData($session_key, $session_data) {
         $q = "SELECT session_data FROM #prefix#session_data WHERE ";
		$q .= "session_key = :session_key";
		$vars = array(
            ':session_key'=>$session_key            
        );
		
        $ps = $this->execute($q, $vars);
		$result=$this->getDataRowsAsArrays($ps);
			
		if(empty($result))
		{
		
		$q = "INSERT INTO #prefix#session_data SET  ";
        $q.="session_key = :session_key, session_data = :session_data";
        $vars = array(
            ':session_key'=>$session_key,
            ':session_data'=>serialize($session_data)            
        );
        $ps = $this->execute($q, $vars);		
		}
		else
		{	
		
        $q = "UPDATE #prefix#session_data SET  ";
        $q.="session_data = :session_data  WHERE session_key = :session_key ";
        $vars = array(
            ':session_key'=>$session_key,
            ':session_data'=>serialize($session_data)            
        );
        $ps = $this->execute($q, $vars);
		}
		
		
		
      return $this->getUpdateCount($ps);
    }

    public function getSessionData($session_key) {
		
        $q = "SELECT session_data FROM #prefix#session_data WHERE ";
		$q .= "session_key = :session_key";
		$vars = array(
            ':session_key'=>$session_key            
        );
		
        $ps = $this->execute($q, $vars);
		$result=$this->fetchAndClose($ps);	
		if(empty($result))
		{
		
		return NULL;		
		}
		else
		{		
		
        return unserialize($result['session_data']);
		}
    }
	
	
	public function clearSessionData($session_key) {
		
        $q = "DELETE FROM #prefix#session_data WHERE ";
		$q .= "session_key = :session_key";
		
		$vars = array(
            ':session_key'=>$session_key            
        );
		echo "deleted";
		echo $session_key;
        $ps = $this->execute($q, $vars);
		
		var_dump($ps);
		// $this->getDataRowsAsArrays($ps);
		
		return $this->getDeleteCount($ps);
    }
}
