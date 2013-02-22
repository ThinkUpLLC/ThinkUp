<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.MentionMySQLDAO.php
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
class MentionMySQLDAO extends PDODAO implements MentionDAO {

    public function insertMentions(array $user_ids_and_names, $post_id, $author_user_id, $network) {
        foreach ($user_ids_and_names as $mention) {
            $this->insertMention($mention['user_id'], $mention['user_name'], $post_id, $author_user_id, $network);
        }
    }

    public function insertMention($mention_user_id, $mention_user_name, $post_id, $author_user_id, $network) {
        $this->logger->logDebug("Processing mention: " . $mention_user_id . ", " . $mention_user_name . " on ".
        $network, __METHOD__.','. __LINE__);

        $mention_id = null;
        // see if record for mention already exists.  If so, increment its count if this is a new mention.
        $q  = "SELECT id FROM #prefix#mentions ";
        $q .= "WHERE user_id = :user_id AND network = :network";
        $vars = array(
            ':user_id'  =>$mention_user_id,
            ':network'  =>$network
        );
        $ps = $this->execute($q, $vars);
        $row = $this->getDataRowAsArray($ps);
        if ($row) {
            $mention_id = $row['id'];
        } else {
            // do the insert
            $q  = "INSERT IGNORE INTO #prefix#mentions ";
            $q .= "(user_id, user_name, network, count_cache) ";
            $q .= "VALUES ( :user_id, :user_name, :network, :count) ";

            $vars  = array(
                ':user_id'  =>(string)$mention_user_id,
                ':user_name'  =>$mention_user_name,
                ':network'  =>$network,
                ':count' => 0
            );
            $ps = $this->execute($q, $vars);
            $mention_id = $this->getInsertId($ps);
            if (!$mention_id) {
                throw new Exception("Error: Could not insert mention.");
            }
        }
        // now create the join table entry
        $q  = "INSERT IGNORE INTO #prefix#mentions_posts ";
        $q .= "(post_id, mention_id, author_user_id, network) ";
        $q .= "VALUES ( :post_id, :mention_id, :author_user_id, :network) ";
        $vars = array(
             ':mention_id'   =>$mention_id,
             ':post_id'      =>(string)$post_id,
             ':author_user_id' => (string)$author_user_id,
             ':network' => (string)$network
        );
        $ps  = $this->execute($q, $vars);
        $res = $this->getUpdateCount($ps);
        if (!$res) {
            $this->logger->logDebug("Could not update mentions_posts with $post_id, $mention_id",
            __METHOD__.','. __LINE__);
        } else {
            // update record with incremented cache count if insert into mention_posts was successful
            $q  = "UPDATE #prefix#mentions ";
            $q .= "SET count_cache = count_cache + 1 WHERE id = :id";
            $vars  = array(
                ':id'  =>$mention_id
            );
            $ps = $this->execute($q, $vars);
            $res = $this->getUpdateCount($ps);
            if (!$res) {
                throw new Exception("Error: Could not update mention.");
            }
        }
    }

    /**
     * The 'mentions' information in these tables is not used by the app yet-- these access methods
     * are testing-only for now.
     */
    public function getMentionInfoUserName($user_name, $network = 'twitter') {
        $q = "SELECT * FROM #prefix#mentions WHERE user_name = :user_name AND network = :network";
        $vars = array(
            ':user_name' => $user_name,
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

    public function getMentionInfoUserID($user_id, $network = 'twitter') {
        $q = "SELECT * FROM #prefix#mentions WHERE user_id = :user_id AND network = :network";
        $vars = array(
            ':user_id' =>(string) $user_id,
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

    public function getMentionsForPost($post_id, $network = 'twitter') {
        $q = "SELECT * FROM #prefix#mentions_posts WHERE post_id = :post_id AND network = :network ORDER BY mention_id";
        $vars = array(
            ':post_id' => (string)$post_id,
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

    public function getMentionsForPostMID($mention_id) {
        $q = "SELECT * FROM #prefix#mentions_posts WHERE mention_id = :mention_id ORDER BY post_id";
        $vars = array(
            ':mention_id' => $mention_id
        );
        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        if ($all_rows) {
            return $all_rows;
        } else {
            return null;
        }
    }
}
