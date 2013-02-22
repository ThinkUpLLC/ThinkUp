<?php
/*
 *
 * ThinkUp/webapp/_lib/model/class.InviteMySQLDAO.php
 *
 * Copyright (c) 2011-2013 Terrance Shepherd, Gina Trapani
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
        $q = "SELECT invite_code FROM #prefix#invites WHERE invite_code=:invite_code";
        $vars = array(
            ':invite_code'=>$invite_code
        );
        $ps = $this->execute($q, $vars);
        return $this->getDataRowAsArray($ps);
    }

    public function addInviteCode($invite_code) {
        if (!$this->doesInviteExist($invite_code)) {
            $q = "INSERT INTO #prefix#invites SET invite_code=:invite_code, created_time=NOW() ";
            $vars = array(
                ':invite_code'=>$invite_code
            );
            $ps = $this->execute($q, $vars);
            return $this->getUpdateCount($ps);
        } else {
            return 0;
        }
    }

    public function doesInviteExist($invite_code) {
        $q = "SELECT invite_code FROM #prefix#invites WHERE invite_code=:invite_code";
        $vars = array(
            ':invite_code'=>$invite_code
        );
        $ps = $this->execute($q, $vars);
        return $this->getDataIsReturned($ps);
    }

    public function isInviteValid( $invite_code) {
        if ($this->doesInviteExist($invite_code)) {
            $q = " SELECT created_time FROM #prefix#invites WHERE invite_code=:invite_code";
            $vars = array(
                ':invite_code'=>$invite_code
            );
            $ps = $this->execute($q, $vars);
            $result = $this->getDataRowAsArray( $ps ) ;
            // check to see if the time-stamp is less then 7 days old.
            $qdate = strtotime( $result['created_time'] );
            if ( (time() - $qdate ) <= 648600 ) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    public function deleteInviteCode($invite_code) {
        $q  = "DELETE FROM #prefix#invites WHERE invite_code=:invite_code;";
        $vars = array(
            ':invite_code'=>$invite_code
        );
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }
}
