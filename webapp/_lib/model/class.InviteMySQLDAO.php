<?php
/*
 *
 * ThinkUp/webapp/_lib/model/class.InviteMySQLDAO.php
 * 
 * Copyright (c) 2009-2011 Terrance Shepherd, Gina Trapani
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
 * Invite Data Access Object
 * The data access object for retrieving and saving owners in the ThinkUp database.
 * @license http://www.gnu.org/licenses/gpl.html
 * @ Copyright 2011 Terrance Shepherd
 * @author Terrance Shepherd
 * 
 * 
 */
class InviteMySQLDAO extends PDODAO implements InviteDAO {

    public function getInviteCode($invite_code) {
        // Query for the SQL database
        $q = "SELECT invite_code  FROM #prefix#invitations WHERE invite_code=:invite_code";
        $vars = array(
            ':invite_code'=>$invite_code
        );
        // Execute Query
        $ps = $this->execute($q, $vars);
        // Format the results so they can be used easiy and return
        return $this->getDataRowAsArray($ps);
    }

    public function addInviteCode($invite_code) {
        // Checks if the invite code exists
        if (!$this->doesInviteExist($invite_code)) {
            // Query the database for the invite code and add the invite code
            $q = "INSERT INTO #prefix#invitations SET invite_code=:invite_code, time_stamp=NOW() ";
            $vars = array(
            ':invite_code'=>$invite_code
            );
            // Execute the query
            $ps = $this->execute($q, $vars);
            // It worked return 1
            return 1;
        } else {
            // Code exists return 0
            return 0;
        }
    }

    public function doesInviteExist( $invite_code ) {
        // Query the database for the existing code
        $q = "SELECT invite_code FROM #prefix#invitations WHERE invite_code=:invite_code";
        $vars = array(
            ':invite_code'=>$invite_code
         );
        // Execute Query
        $ps = $this->execute($q, $vars);
        /*
         * Send information to the getDataIsReturned which will 
         * return 0 if there is no data or 1 if data is returned
         */
        return $this->getDataIsReturned($ps);
    }
    public function validateInviteCode( $invite_code) {
        // if the code is not in the database it does not exist
        if ( $this->doesInviteExist( $invite_code ) ) {
            // Query the database for the invite_code and return the time_stamp
            $q = " SELECT time_stamp FROM #prefix#invitations WHERE invite_code=:invite_code";
            $vars = array(
                ':invite_code'=>$invite_code
            );
            // Execute query
            $ps = $this->execute($q, $vars);
            // Format the return of the query for for useablity 
            $ps = $this->getDataRowAsArray( $ps ) ;
            // check to see if the time-stamp is less then 7 days old.
            $qdate = strtotime( $ps['time_stamp'] ) ;
            if ( (time() - $qdate ) <= 648600 ) {
                return 1 ;
            } else {
                return 0 ;
            }
        }

        return 0 ;

    }
    public function deleteInviteCode( $invite_code) {
        // Query for the database to delete the invite code
        $q  = "DELETE FROM #prefix#invitations ";
        $q .= "WHERE invite_code=:invite_code;";
        $vars = array(
            ':invite_code'=>$invite_code
        );
        // Execute Query
        $ps = $this->execute($q, $vars);
        // Check to see if one row was deleted
        return $this->getUpdateCount($ps);
    }
}
