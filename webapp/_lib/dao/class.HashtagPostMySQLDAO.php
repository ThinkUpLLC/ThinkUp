<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.HashtagPostMySQLDAO.php
 *
 * Copyright (c) 2012 Eduard Cucurella
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
 * @copyright 2012 Eduard Cucurella
 * @author Eduard Cucurella <eduard[dot]cucu[dot]cat[at]gmail[dot]com>
 *
 */
class HashtagPostMySQLDAO extends PDODAO implements HashtagPostDAO {
    public function insertHashtagPosts(array $hashtags, $post_id, $network) {
        foreach ($hashtags as $hashtag) {
            $this->insertHashtagPost($hashtag, $post_id, $network);
        }
    }

    public function insertHashtagPost($hashtag, $post_id, $network) {
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
            //add hashtag post before incrementing its counter
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
            if ($res > 0) {
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
            else {
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
        }
    }

    public function getHashtagsForPost($post_id, $network) {
        $q = "SELECT * FROM #prefix#hashtags_posts WHERE post_id = :post_id AND network = :network ORDER BY hashtag_id";
        $vars = array(
            ':post_id' => $post_id,
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

    public function getHashtagPostsByHashtagID($hashtag_id) {
        $q = "SELECT * FROM #prefix#hashtags_posts WHERE hashtag_id = :hashtag_id ";
        $vars = array(
            ':hashtag_id' => $hashtag_id
        );
        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        if ($all_rows) {
            return $all_rows;
        } else {
            return null;
        }
    }

    public function deleteHashtagsPostsByHashtagID($hashtag_id) {
        $q  = "DELETE FROM #prefix#hashtags_posts WHERE hashtag_id=:hashtag_id;";
        $vars = array(':hashtag_id'=>$hashtag_id);
        $ps = $this->execute($q, $vars);
        return $this->getDeleteCount($ps);
    }

    public function isHashtagPostInStorage($hashtag_id, $post_id, $network) {
        $q = "SELECT post_id FROM  #prefix#hashtags_posts ";
        $q .= "WHERE hashtag_id = :hashtag_id AND post_id = :post_id AND network=:network;";
        $vars = array(
                ':hashtag_id'=>$hashtag_id,
                ':post_id'=>(string)$post_id,
                ':network'=>$network
        );
        $ps = $this->execute($q, $vars);
        return $this->getDataIsReturned($ps);
    }

    public function getTotalPostsByHashtagAndDate($hashtag_id, $for_date=null) {
        $vars = array(
            ':hashtag_id'=>$hashtag_id,
        );
        if (!isset($for_date)) {
            //$for_date = 'CURRENT_DATE()'; //accounts for timezone; use UTC instead
            $for_date = date('Y-m-d H:i:s');
        }
        $vars[':for_date'] = $for_date;
        $for_date = ':for_date';

        $q = "SELECT COUNT(p.id) AS total ";
        $q .= "FROM #prefix#posts p, #prefix#hashtags_posts hp, #prefix#hashtags h ";
        $q .= "WHERE  p.post_id= hp.post_id AND hp.hashtag_id = h.id AND p.network = hp.network AND h.id = :hashtag_id ";
        $q .= "AND YEAR(pub_date) = YEAR($for_date) AND (DAYOFMONTH(pub_date)=DAYOFMONTH($for_date)) ";
        $q .= "AND (MONTH(pub_date)=MONTH($for_date)); ";
        $ps = $this->execute($q, $vars);
        $result = $this->getDataRowAsArray($ps);
        return $result['total'];
    }
}
