<?php
/*
*
* ThinkUp/webapp/_lib/model/class.InviteMySQLDAO.php
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
* Invite Data Access Object
* The data access object for retrieving and saving owners in the ThinkUp database.
* @license http://www.gnu.org/licenses/gpl.html
* @author Terrance Shepherd
* 
* 
*/
class InviteMySQLDAO extends PDODAO implements InviteDAO {

    public function getInviteCode($invite_code) {
        $q = "SELECT invite_code  FROM #prefix#invitations WHERE invite_code=:invite_code";
        $vars = array(
            ':invite_code'=>$invite_code
        );
        $ps = $this->execute($q, $vars);
        return $this->getDataRowAsArray($ps);
    }

    public function addInviteCode($invite_code) {
        if (!$this->doesInviteExist($invite_code)) {
            $q = "INSERT INTO #prefix#invitations SET invite_code=:invite_code, time_stamp=NOW() ";
            $vars = array(
                ':invite_code'=>$invite_code
            );
            $ps = $this->execute($q, $vars);
            return 1;
        } else {
            return 0;
        }
    }

    public function doesInviteExist( $invite_code ) {
        $q = "SELECT invite_code FROM #prefix#invitations WHERE invite_code=:invite_code";
        $vars = array(
            ':invite_code'=>$invite_code
        );
        /*$vars = array(
            ':invite_code'=>$invite_code
        );*/
        $ps = $this->execute($q, $vars);
        return $this->getDataIsReturned($ps);
    }
    public function validateInviteCode( $invite_code) {
        if ( $this->doesInviteExist( $invite_code ) ) {
            $q = " SELECT time_stamp FROM #prefix#invitations WHERE invite_code=:invite_code";
            $vars = array(
                ':invite_code'=>$invite_code
            );
            $ps = $this->execute($q, $vars);
            $ps = $this->getDataRowAsArray( $ps ) ;
            $qdate = strtotime( $ps['time_stamp'] ) ;
            if ( (time() - $qdate ) <= 648600 ) {
                return 1 ;
            } else {
                return 0 ;
            }
        }
        
        return 0 ;

    }
}
