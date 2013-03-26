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

    public function insertHashtags(array $hashtags, $post_id, $network) {
        foreach ($hashtags as $hashtag) {
            $this->insertHashtag($hashtag, $post_id, $network);
        }
    }

    public function insertHashtag($hashtag, $post_id, $network) {
        $this->logger->logDebug("processing hashtag: $hashtag", __METHOD__.','.__LINE__);
        $hashtag_id = null;
        // see if record for hashtag already exists.  If so, increment its count.
        $q  = "SELECT id from #prefix#hashtags ";
        $q .= "WHERE hashtag = :hashtag AND network = :network ";
        $vars = array(
            ':hashtag'  =>$hashtag,
            ':network'  =>$network
        );
        $ps = $this->execute($q, $vars);
        $row = $this->getDataRowAsArray($ps);
        if ($row) {
            $hashtag_id = $row['id'];
            $q  = "UPDATE #prefix#hashtags ";
            $q .= "SET count_cache = count_cache + 1 where id = :id";
            $vars  = array(
                ':id'  =>$hashtag_id
            );
            $ps = $this->execute($q, $vars);
            $res = $this->getUpdateCount($ps);
            if (!$res) {
                throw new Exception("Error: Could not update hashtag.");
            }
        } else {
            // do the insert
            $q  = "INSERT IGNORE INTO #prefix#hashtags ";
            $q .= "(hashtag, network, count_cache) ";
            $q .= "VALUES ( :hashtag, :network, :count) ";

            $vars  = array(
                ':hashtag'  =>$hashtag,
                ':network'  =>$network,
                ':count' => 1
            );
            $ps = $this->execute($q, $vars);
            $hashtag_id = $this->getInsertId($ps);
            if (!$hashtag_id) {
                throw new Exception("Error: Could not insert hashtag.");
            }
        }
        // now create the join table entry
        $q  = "INSERT IGNORE INTO #prefix#hashtags_posts ";
        $q .= "(post_id, hashtag_id, network) ";
        $q .= "VALUES ( :post_id, :hashtag_id, :network) ";

        $vars = array(
             ':hashtag_id' => $hashtag_id,
             ':post_id' =>(string)$post_id,
             ':network' => $network
        );
        $ps  = $this->execute($q, $vars);
        $res = $this->getUpdateCount($ps);
    }

    public function getHashtagInfoForTag($hashtag, $network = 'twitter') {
        $q = "SELECT * FROM #prefix#hashtags WHERE hashtag = :hashtag AND network = :network";
        $vars = array(
            ':hashtag' => $hashtag,
            ':network' => $network
        );
        $ps = $this->execute($q, $vars);
        $row = $this->getDataRowAsArray($ps);
        if ($row) {
            return $row;
        } else {
            return null;
        }
    }

    public function getHashtagsForPost($pid, $network = 'twitter') {
        $q = "SELECT * FROM #prefix#hashtags_posts WHERE post_id = :post_id AND network = :network ORDER BY hashtag_id";
        $vars = array(
            ':post_id' => $pid,
            ':network' => $network
        );
        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        if ($all_rows) {
            return $all_rows;
        } else {
            return null;
        }
    }

    public function getHashtagsForPostHID($hid) {
        $q = "SELECT * FROM #prefix#hashtags_posts WHERE hashtag_id = :hashtag_id ORDER BY post_id";
        $vars = array(
            ':hashtag_id' => $hid
        );
        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        if ($all_rows) {
            return $all_rows;
        } else {
            return null;
        }
    }
	    
	public function getByHashtag($hashtag_id) {
        $q = "SELECT
                id, hashtag, network, count_cache
            FROM
                #prefix#hashtags
            WHERE  id = :hashtag_id";
        
        $vars = array(':hashtag_id' => $hashtag_id);
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $stmt = $this->execute($q, $vars);
        $hashtag = $this->getDataRowAsObject($stmt, 'Hashtag');
        return $hashtag;
    }
    
    public function getByHashtagName($hashtag_name) {
        $q = "SELECT
                id, hashtag, network, count_cache
            FROM
                #prefix#hashtags
            WHERE  hashtag = :hashtag_name";
    
        $vars = array(':hashtag_name' => $hashtag_name);
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $stmt = $this->execute($q, $vars);
        $hashtag = $this->getDataRowAsObject($stmt, 'Hashtag');
        return $hashtag;
    }

    public function getByUsername($username) {
        $q = "SELECT t.id, t.hashtag, t.network, t.count_cache " . 
             "FROM #prefix#hashtags t " .
             "INNER JOIN #prefix#instances_hashtags ih ON t.id=ih.hashtag_id " .
             "INNER JOIN #prefix#instances i ON ih.instance_id = i.id " .
             "WHERE i.network_username = :username";
        $vars = array(':username' => $username);
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $stmt = $this->execute($q, $vars);
        $hashtag = $this->getDataRowsAsObjects($stmt, 'Hashtag');
        return $hashtag;
    }
    
    public function deleteHashtagByHashtagId($hashtag_id) {
        $q  = "DELETE FROM #prefix#hashtags WHERE id=:hashtag_id;";
        $vars = array(':hashtag_id'=>$hashtag_id);
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);
        return $this->getDeleteCount($ps);
    }
    
    public function deleteHashtagsPostsByHashtagId($hashtag_id) {
        $q  = "DELETE FROM #prefix#hashtags_posts WHERE hashtag_id=:hashtag_id;";
        $vars = array(':hashtag_id'=>$hashtag_id);
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);
        return $this->getDeleteCount($ps);
    }
    
    public function insertHashtagByHashtagName($hashtag, $network='twitter') {
        $q  = "INSERT #prefix#hashtags ";
        $q .= "(hashtag, network, count_cache) ";
        $q .= "VALUES ( :hashtag, :network, :count) ";
        $vars  = array(':hashtag'=>$hashtag,':network'=>$network,':count'=> 0);
        $ps = $this->execute($q, $vars);
        $hashtag_id = $this->getInsertId($ps);
        if (!$hashtag_id) {
            return 0;           
        }
        else {
            return $hashtag_id;
        }
    }
    
    public function isHashtagPostInDB($hashtag_id, $post_id, $network) {
        $q = "SELECT post_id FROM  #prefix#hashtags_posts ";
        $q .= "WHERE hashtag_id = :hashtag_id AND post_id = :post_id AND network=:network;";
        $vars = array(
                ':hashtag_id'=>$hashtag_id,               
                ':post_id'=>(string)$post_id,
                ':network'=>$network
        );
        if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
        $ps = $this->execute($q, $vars);
        return $this->getDataIsReturned($ps);
    }

}
