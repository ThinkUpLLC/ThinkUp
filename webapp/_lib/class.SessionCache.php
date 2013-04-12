<?php
/**
 *
 * ThinkUp/webapp/_lib/class.SessionCache.php
 *
 * Copyright (c) 2011-2013 Gina Trapani
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
 * SessionCache
 *
 * PHP $_SESSION accessor.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Hemant Kumar Singh <unizen01[at]gmail[dot]com>
 *
 */
class SessionCache {
    /**
     * Put a value in ThinkUp's $_SESSION key.
     * @param str $key
     * @param str $value
     */
    public static function put($key, $value) {
	
	$config = Config::getInstance();
	$_SESSION[$config->getValue('source_root_path')][$key] = $value;
	 if(isset($_SESSION[$config->getValue('source_root_path')]['session_key']))
	 {
        
        
		
		$session_key=$_SESSION[$config->getValue('source_root_path')]['session_key'];
		$session_dao = DAOFactory::getDAO('SessionDataDAO');
		$get_session_data=$session_dao->getSessionData($session_key);
		$get_session_data[$key]=$value;
		$session_dao->addSessionData($session_key,$get_session_data);
		}
    }

    /**
     * Get a value from ThinkUp's $_SESSION.
     * @param str $key
     * @return mixed Value
     */
    public static function get($key) {
        $config = Config::getInstance();
        if (self::isKeySet($key)) {     
		
		
		$session_key=$_SESSION[$config->getValue('source_root_path')]['session_key'];
		$session_dao = DAOFactory::getDAO('SessionDataDAO');
		$get_session_data=$session_dao->getSessionData($session_key);
		
		//return $_SESSION[$config->getValue('source_root_path')][$key];
		
		return $get_session_data[$key];
        } else {
            return null;
        }
    }

    /**
     * Check if a key in ThinkUp's $_SESSION has a value set.
     * @param str $key
     * @return bool
     */
    public static function isKeySet($key) {
	$config = Config::getInstance();
	if(isset($_SESSION[$config->getValue('source_root_path')]['session_key']))
       { 
		$session_key=$_SESSION[$config->getValue('source_root_path')]['session_key'];
		$session_dao = DAOFactory::getDAO('SessionDataDAO');
		$get_session_data=$session_dao->getSessionData($session_key);
		if(empty($get_session_data))
		return false;
		else
		return (array_key_exists($key,$get_session_data));
		}
		else 
		return false;
        //return isset($_SESSION[$config->getValue('source_root_path')][$key]);
    }

    /**
     * Unset key's value in ThinkUp's $_SESSION
     * @param str $key
     */
    public static function unsetKey($key) {	
		$config = Config::getInstance();
		$session_key=$_SESSION[$config->getValue('source_root_path')]['session_key'];
		$session_dao = DAOFactory::getDAO('SessionDataDAO');
		$get_session_data=$session_dao->getSessionData($session_key);
		unset($get_session_data[$key]);
		$session_dao->addSessionData($session_key,$get_session_data);
			
       unset($_SESSION[$config->getValue('source_root_path')][$key]);
    }
	
	public static function delete_session() {
		echo "delete session";
		$config = Config::getInstance();
		$session_key=$_SESSION[$config->getValue('source_root_path')]['session_key'];
		$session_dao = DAOFactory::getDAO('SessionDataDAO');
		$session_dao->clearSessionData($session_key);
		
    }
}

	