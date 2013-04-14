<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.HashtagMySQLDAO.php
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
 * @copyright 2011-2013
 * @author Amy Unruh
 */
class HashtagMySQLDAO extends PDODAO implements HashtagDAO {

    public function getHashtag($hashtag, $network) {
        $q = "SELECT * FROM #prefix#hashtags WHERE hashtag = :hashtag AND network = :network";
        $vars = array(
            ':hashtag' => $hashtag,
            ':network' => $network
        );
        $ps = $this->execute($q, $vars);
        $hashtag = $this->getDataRowAsObject($ps, 'Hashtag');
        return $hashtag;
    }

    public function getHashtagByID($hashtag_id) {
        $q = "SELECT
                id, hashtag, network, count_cache
            FROM
                #prefix#hashtags
            WHERE  id = :hashtag_id";

        $vars = array(':hashtag_id' => $hashtag_id);
        $stmt = $this->execute($q, $vars);
        $hashtag = $this->getDataRowAsObject($stmt, 'Hashtag');
        return $hashtag;
    }

    public function deleteHashtagByID($hashtag_id) {
        $q  = "DELETE FROM #prefix#hashtags WHERE id=:hashtag_id;";
        $vars = array(':hashtag_id'=>$hashtag_id);
        $ps = $this->execute($q, $vars);
        return $this->getDeleteCount($ps);
    }

    public function insertHashtag($hashtag, $network) {
        $q  = "INSERT #prefix#hashtags ";
        $q .= "(hashtag, network, count_cache) ";
        $q .= "VALUES ( :hashtag, :network, :count) ";
        $vars  = array(':hashtag'=>$hashtag,':network'=>$network,':count'=> 0);
        $ps = $this->execute($q, $vars);
        $hashtag_id = $this->getInsertId($ps);
        if (!$hashtag_id) {
            return false;
        } else {
            return $hashtag_id;
        }
    }
}
