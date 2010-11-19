<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.LinkMySQLDAO.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
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
 * Link MySQL Data Access Object
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Christoffer Viken <christoffer[at]viken[dot]me>
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class LinkMySQLDAO extends PDODAO implements LinkDAO {
    public function insert($url, $expanded, $title, $post_id, $network, $is_image = false ){
        $is_image = $this->convertBoolToDB($is_image);

        $q  = "INSERT IGNORE INTO #prefix#links ";
        $q .= "(url, expanded_url, title, post_id, network, is_image) ";
        $q .= "VALUES ( :url, :expanded, :title, :post_id, :network, :is_image ) ";

        $vars = array(
            ':url'=>$url,
            ':expanded'=>$expanded,
            ':title'=>$title,
            ':post_id'=>$post_id,
            ':network'=>$network,
            ':is_image'=>(int)$is_image
        );
        $ps = $this->execute($q, $vars);

        return $this->getInsertId($ps);
    }

    public function saveExpandedURL($url, $expanded, $title = '', $is_image = false  ){
        $is_image = $this->convertBoolToDB($is_image);

        $q  = "UPDATE #prefix#links ";
        $q .= "SET expanded_url=:expanded, title=:title, is_image=:isimage ";
        $q .= "WHERE url=:url ";
        $vars = array(
            ':url'=>$url,
            ':expanded'=>$expanded,
            ':title'=>$title,
            ':isimage'=>$is_image
        );
        $ps = $this->execute($q, $vars);

        $ret = $this->getUpdateCount($ps);
        if ($ret > 0) {
            $this->logger->logSuccess("Expanded URL $expanded for $url saved", __METHOD__.','.__LINE__);
        } else {
            $this->logger->logError("Expanded URL NOT saved", __METHOD__.','.__LINE__);
        }
        return $ret;
    }

    public function saveExpansionError($url, $error_text){
        $q  = "UPDATE #prefix#links ";
        $q .= "SET error=:error ";
        $q .= "WHERE url=:url ";
        $vars = array(
            ':url'=>$url,
            ':error'=>$error_text
        );
        $ps = $this->execute($q, $vars);

        $ret = $this->getUpdateCount($ps);
        if ($ret > 0) {
            $this->logger->logInfo("Error '$error_text' saved for link ID $url saved", __METHOD__.','.__LINE__);
        } else {
            $this->logger->logInfo("Error '$error_text' for URL NOT saved", __METHOD__.','.__LINE__);
        }
        return $ret;
    }

    public function update( $url, $expanded, $title, $post_id, $network, $is_image = false ){
        $q  = "UPDATE #prefix#links ";
        $q .= "SET expanded_url=:expanded, title=:title, ";
        $q .= "post_id=:post_id, is_image=:is_image, network=:network ";
        $q .= "WHERE url=:url; ";
        $vars = array(
            ':url'=>$url,
            ':expanded'=>$expanded,
            ':title'=>$title,
            ':post_id'=>$post_id,
            ':is_image'=>$is_image,
            ':network'=>$network
        );
        $ps = $this->execute($q, $vars);
        return $this->getUpdateCount($ps);
    }

    public function getLinksByFriends($user_id, $network) {
        $q  = "SELECT l.*, p.*, pub_date + interval #gmt_offset# hour AS adj_pub_date ";
        $q .= "FROM #prefix#posts AS p ";
        $q .= "INNER JOIN #prefix#links AS l ";
        $q .= "ON p.post_id = l.post_id AND p.network = l.network ";
        $q .= "WHERE l.network = :network AND  p.author_user_id IN ( ";
        $q .= "   SELECT user_id FROM #prefix#follows AS f ";
        $q .= "   WHERE f.follower_id=:user_id AND f.active=1 AND f.network=:network ";
        $q .= ")";
        $q .= "ORDER BY l.post_id DESC ";
        $q .= "LIMIT 15 ";
        $vars = array(
            ':user_id'=>$user_id,
            ':network'=>$network
        );
        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        $links = array();
        foreach ($all_rows as $row) {
            $links[] = $this->setLinkWithPost($row);
        }
        return $links;
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

    public function getPhotosByFriends($user_id, $network) {
        $q  = "SELECT l.*, p.*, pub_date + interval #gmt_offset# hour as adj_pub_date ";
        $q .= "FROM #prefix#links AS l ";
        $q .= "INNER JOIN #prefix#posts p ";
        $q .= "ON p.post_id = l.post_id AND p.network = l.network ";
        $q .= "WHERE is_image = 1 AND l.network=:network AND p.author_user_id in ( ";
        $q .= "   SELECT user_id FROM #prefix#follows AS f ";
        $q .= "   WHERE f.follower_id=:user_id AND f.active=1 AND f.network = :network) ";
        $q .= "ORDER BY l.post_id DESC  ";
        $q .= "LIMIT 15 ";
        $vars = array(
            ':user_id'=>$user_id,
            ':network'=>$network
        );
        $ps = $this->execute($q, $vars);
        $all_rows = $this->getDataRowsAsArrays($ps);
        $links = array();
        foreach ($all_rows as $row) {
            $links[] = $this->setLinkWithPost($row);
        }
        return $links;
    }

    public function getLinksToExpand($limit = 1500) {
        $q  = "SELECT l1.url AS url ";
        $q .= "FROM (  ";
        $q .= "   SELECT l.url, l.post_id ";
        $q .= "   FROM #prefix#links AS l ";
        $q .= "   WHERE l.expanded_url = '' and l.error = '' ";
        $q .= "   ORDER BY post_id DESC LIMIT :limit ";
        $q .= ") AS l1 ";
        $q .= "GROUP BY l1.url ";
        $vars = array(
            ':limit'=>$limit
        );
        $ps = $this->execute($q, $vars);

        $rows = $this->getDataRowsAsArrays($ps);
        $urls = array();
        foreach($rows as $row){
            $urls[] = $row['url'];
        }
        return $urls;
    }

    public function getLinksToExpandByURL($url) {
        $q  = "SELECT l.url ";
        $q .= "FROM #prefix#links AS l ";
        $q .= "WHERE l.expanded_url = ''  ";
        $q .= "AND l.url LIKE :url AND l.error = '' ";
        $q .= "GROUP BY l.url";
        $vars = array(
            ':url'=>$url."%"
            );
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
        $ps = $this->execute($q, $vars);

        return $this->getDataRowAsObject($ps, "Link");
    }
}
