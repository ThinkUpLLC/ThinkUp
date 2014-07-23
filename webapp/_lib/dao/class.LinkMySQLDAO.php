<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.LinkMySQLDAO.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
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
 * Link MySQL Data Access Object
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Christoffer Viken <christoffer[at]viken[dot]me>
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class LinkMySQLDAO extends PDODAO implements LinkDAO {
    public function insert(Link $link){
        $existing_link = $this->getLinkByUrl( $link->url );
        if ($existing_link) {
            throw new DuplicateLinkException("The link ".$link->url." is already in storage");
        }
        $q  = "INSERT INTO #prefix#links ";
        $q .= "(url, expanded_url, title, description, image_src, caption, post_key) ";
        $q .= "VALUES ( :url, :expanded, :title, :description, :image_src, :caption, :post_key ) ";

        $vars = array(
            ':url'=>$link->url,
            ':expanded'=>$link->expanded_url,
            ':title'=>substr($link->title, 0, 255),
            ':description'=>substr($link->description, 0, 255),
            ':image_src'=>$link->image_src,
            ':caption'=>substr($link->caption, 0, 255),
            ':post_key'=>$link->post_key
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getInsertId($ps);
    }

    public function saveExpandedURL($url, $expanded, $title = '', $image_src = '', $description = '' ){
        $vars = array(
            ':url'=>$url,
            ':expanded'=>$expanded,
            ':title'=>substr($title, 0, 255),
            ':description'=>substr($description, 0, 255),
            ':image_src'=> ((strlen($image_src) < 256)?$image_src:'')
        );
        $q  = "UPDATE #prefix#links ";
        $q .= "SET expanded_url=:expanded, title=:title, description = :description , image_src=:image_src ";
        $q .= "WHERE url=:url ";
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        $ret = $this->getUpdateCount($ps);
        if ($ret > 0) {
            $this->logger->logSuccess("Expanded URL $expanded for $url saved".
            (($image_src=='')?'':" (thumbnail ".$image_src.")"), __METHOD__.','.__LINE__);
        } else {
            $this->logger->logError("Expanded URL NOT saved", __METHOD__.','.__LINE__);
        }
        return $ret;
    }

    public function saveExpansionError($url, $error_text){
        $q  = "UPDATE #prefix#links SET error=:error WHERE url=:url ";
        $vars = array(
            ':url'=>$url,
            ':error'=> substr($error_text, 0, 255) //Make sure error text isn't longer than field width
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        $ret = $this->getUpdateCount($ps);
        if ($ret > 0) {
            $this->logger->logInfo("Error ".$error_text." saved for $url", __METHOD__.','.__LINE__);
        } else {
            $this->logger->logInfo("Error ".$error_text." for ".$url." was NOT saved", __METHOD__.','.__LINE__);
        }
        return $ret;
    }

    public function getLinksByFriends($user_id, $network, $count = 15, $page = 1, $is_public = false) {
        $start_on_record = ($page - 1) * $count;

        if ($is_public) {
            $protected = 'AND p.is_protected = 0 ';
        } else {
            $protected = '';
        }

        $q  = "SELECT l.*, p.*, pub_date + interval #gmt_offset# hour AS adj_pub_date ";
        $q .= "FROM #prefix#posts AS p ";
        $q .= "INNER JOIN #prefix#links AS l ";
        $q .= "ON p.id = l.post_key ";
        $q .= "WHERE p.network = :network ";
        $q .= $protected;
        $q .=  "AND p.author_user_id IN ( ";
        $q .= "   SELECT user_id FROM #prefix#follows AS f ";
        $q .= "   WHERE f.follower_id=:user_id AND f.active=1 AND f.network=:network ";
        $q .= ")";
        $q .= "ORDER BY l.id DESC ";
        $q .= "LIMIT :start_on_record, :limit";
        $vars = array(
            ':user_id'=>$user_id,
            ':network'=>$network,
            ':limit'=>$count,
            ':start_on_record'=>(int)$start_on_record
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        $links = array();
        foreach ($all_rows as $row) {
            $links[] = $this->setLinkWithPost($row);
        }
        return $links;
    }

    public function countLinksPostedByUserSinceDaysAgo($user_id, $network, $days_ago=7) {
        $q = "SELECT COUNT(*) AS count FROM #prefix#links AS l ";
        $q .= "INNER JOIN #prefix#posts AS p ON p.id = l.post_key ";
        $q .= "WHERE p.author_user_id=:user_id AND p.network=:network ";
        $q .= "AND p.pub_date>=DATE_SUB(CURDATE(), INTERVAL :days_ago DAY) ";
        $vars = array(
            ':user_id'=>$user_id,
            ':network'=>$network,
            ':days_ago'=>$days_ago
        );

        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $result = $this->getDataRowAsArray($ps);

        return (int)$result['count'];
    }

    /**
     * Add post object to link
     * @param array $row
     * @return Link object with post member object set
     */
    private function setLinkWithPost($row) {
        $link = new Link($row);
        $post = new Post($row);
        $link->container_post = $post;
        return $link;
    }

    public function getLinksByFavorites($user_id, $network, $count = 15, $page = 1, $is_public = false) {
        $start_on_record = ($page - 1) * $count;

        if ($is_public) {
            $protected = 'AND p.is_protected = 0 ';
        } else {
            $protected = '';
        }

        $q  = "SELECT l.*, p.*, pub_date - interval 8 hour AS adj_pub_date ";
        $q .= "FROM #prefix#posts as p, #prefix#favorites as f, #prefix#links as l WHERE f.post_id = p.post_id ";
        $q .= $protected;
        $q .= "AND p.id = l.post_key ";
        $q .= "AND p.network = :network AND  f.fav_of_user_id = :user_id ";
        $q .= "ORDER BY l.id DESC ";
        $q .= "LIMIT :start_on_record, :limit";
        $vars = array(
            ':user_id'=>$user_id,
            ':network'=>$network,
            ':limit'=>$count,
            ':start_on_record'=>(int)$start_on_record
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDataRowsAsObjects($ps, "Link");
    }

    public function getPhotosByFriends($user_id, $network, $count = 15, $page = 1, $is_public = false) {
        $start_on_record = ($page - 1) * $count;

        if ($is_public) {
            $protected = 'AND p.is_protected = 0 ';
        } else {
            $protected = '';
        }

        $q  = "SELECT l.*, p.*, pub_date + interval #gmt_offset# hour as adj_pub_date ";
        $q .= "FROM #prefix#links AS l ";
        $q .= "INNER JOIN #prefix#posts p ";
        $q .= "ON p.id = l.post_key ";
        $q .= "WHERE image_src != '' AND p.network=:network ";
        $q .= $protected;
        $q .= "AND p.author_user_id in ( ";
        $q .= "   SELECT user_id FROM #prefix#follows AS f ";
        $q .= "   WHERE f.follower_id=:user_id AND f.active=1 AND f.network = :network) ";
        $q .= "ORDER BY l.id DESC  ";
        $q .= "LIMIT :start_on_record, :limit";
        $vars = array(
            ':user_id'=>$user_id,
            ':network'=>$network,
            ':limit'=>$count,
            ':start_on_record'=>(int)$start_on_record
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        $links = array();
        foreach ($all_rows as $row) {
            $links[] = $this->setLinkWithPost($row);
        }
        return $links;
    }

    public function getLinksToExpand($limit = 1500) {
        $q  = "SELECT * ";
        $q .= "FROM (  ";
        $q .= "   SELECT * ";
        $q .= "   FROM #prefix#links AS l ";
        $q .= "   WHERE l.expanded_url = '' and l.error = '' ";
        $q .= "   ORDER BY id DESC LIMIT :limit ";
        $q .= ") AS l1 ";
        $q .= "GROUP BY l1.url ";
        $vars = array(
            ':limit'=>$limit
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsObjects($ps, 'Link');
    }

    public function getLinksToExpandByURL($url, $limit = 0) {
        $q  = "SELECT l.url ";
        $q .= "FROM #prefix#links AS l ";
        $q .= "WHERE l.expanded_url = ''  ";
        $q .= "AND l.url LIKE :url AND l.error = '' ";
        $q .= "GROUP BY l.url ";
        $vars = array( ':url'=>$url."%" );
        if ($limit != 0) {
            $q .= "LIMIT :limit";
            $vars[':limit'] = $limit;
        }

        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        $rows = $this->getDataRowsAsArrays($ps);
        $urls = array();
        foreach($rows as $row){
            $urls[] = $row['url'];
        }
        return $urls;
    }

    public function getLinkById($id) {
        $q  = "SELECT l.* ";
        $q .= "FROM #prefix#links AS l ";
        $q .= "WHERE l.id=:id ";
        $vars = array(
            ':id'=>$id
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        return $this->getDataRowAsObject($ps, "Link");
    }

    public function getLinkByUrl($url) {
        $q  = "SELECT l.* ";
        $q .= "FROM #prefix#links AS l ";
        $q .= "WHERE l.url=:url ";
        $vars = array(
            ':url'=>$url
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);

        return $this->getDataRowAsObject($ps, "Link");
    }

    public function getLinksForPost($post_id, $network = 'twitter') {
        $q  = "SELECT l.* ";
        $q .= "FROM #prefix#links AS l ";
        $q .= "INNER JOIN #prefix#posts as p ON l.post_key = p.id ";
        $q .= "WHERE p.post_id=:post_id  and p.network = :network ";
        $vars = array(
            ':post_id'=>$post_id,
            ':network' => $network
        );
        $ps = $this->execute($q, $vars);
        return $this->getDataRowsAsObjects($ps, "Link");
    }

    public function updateTitle($id, $title) {
        $q  = "UPDATE #prefix#links SET title=:title WHERE id=:id;";
        $vars = array(
            ':title'=>substr($title, 0, 255),
            ':id'=>$id
        );
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }

        $ps = $this->execute($q, $vars);

        return $this->getUpdateCount($ps);
    }

    public function deleteLinksByHashtagId($hashtag_id) {
        $q  = "DELETE l.* FROM #prefix#links l INNER JOIN #prefix#posts t ON l.post_key =t.id ";
        $q .= "INNER JOIN #prefix#hashtags_posts hp ON t.post_id = hp.post_id ";
        $q .= "WHERE hp.hashtag_id=:hashtag_id;";
        $vars = array(':hashtag_id'=>$hashtag_id);
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        return $this->getDeleteCount($ps);
    }

    public function getLinksByUserSinceDaysAgo($user_id, $network, $limit= 0, $days_ago = 0) {

        $vars = array(
            ':user_id'=>$user_id,
            ':network'=>$network,
        );
        $q = "SELECT l.*, p.in_retweet_of_post_id FROM #prefix#links AS l ";
        $q .= "INNER JOIN #prefix#posts AS p ON p.id = l.post_key ";
        $q .= "WHERE p.author_user_id=:user_id AND p.network=:network ";
        $q .= "AND p.in_reply_to_user_id IS NULL ";

       if($days_ago != 0) {
            $q .= "AND p.pub_date>=DATE_SUB(CURDATE(), INTERVAL :days_ago DAY) ";
            $q .= "ORDER BY p.pub_date DESC ";
            $vars[':days_ago'] = $days_ago;
        }
        if($limit != 0){
            $q .= "ORDER BY p.pub_date DESC ";
            $q .= "LIMIT :limit ";
            $vars[':limit'] = $limit;
        }
        if ($this->profiler_enabled) { Profiler::setDAOMethod(__METHOD__); }
        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        return $all_rows;
   }

}
