<?php
/**
 * Link MySQL Data Access Object
 *
 * @author Christoffer Viken <christoffer[at]viken[dot]me>
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
require_once 'model/class.PDODAO.php';
require_once 'model/interface.LinkDAO.php';

class LinkMySQLDAO extends PDODAO implements LinkDAO {
    public function insert(
    $url,
    $expanded,
    $title,
    $post_id,
    $is_image = false
    ){
        $is_image = $this->convertBoolToDB($is_image);

        $q  = " INSERT INTO #prefix#links ";
        $q .= " (url, expanded_url, title, post_id, is_image) ";
        $q .= " VALUES ( :url, :expanded, :title, :postid, :isimage ) ";

        $vars = array(
            ':url'=>$url,
            ':expanded'=>$expanded,
            ':title'=>$title,
            ':postid'=>$post_id,
            ':isimage'=>(int)$is_image
        );
        $ps = $this->execute($q, $vars);

        return $this->getInsertId($ps);
    }

    public function saveExpandedURL(
    $url,
    $expanded,
    $title = '',
    $is_image = false
    ){
        $is_image = $this->convertBoolToDB($is_image);

        $q  = " UPDATE #prefix#links ";
        $q .= " SET expanded_url=:expanded, title=:title, is_image=:isimage ";
        $q .= " WHERE url=:url ";
        $vars = array(
            ':url'=>$url,
            ':expanded'=>$expanded,
            ':title'=>$title,
            ':isimage'=>$is_image
        );
        $ps = $this->execute($q, $vars);

        $ret = $this->getUpdateCount($ps);
        if ($ret > 0) {
            $this->logger->logStatus("Expanded URL $expanded for $url saved", get_class($this));
        } else {
            $this->logger->logStatus("Expanded URL NOT saved", get_class($this));
        }
        return $ret;
    }

    public function saveExpansionError($url, $error_text){
        $q  = " UPDATE #prefix#links ";
        $q .= " SET error=:error ";
        $q .= " WHERE url=:url ";
        $vars = array(
            ':url'=>$url,
            ':error'=>$error_text
        );
        $ps = $this->execute($q, $vars);

        $ret = $this->getUpdateCount($ps);
        if ($ret > 0) {
            $this->logger->logStatus("Error '$error_text' saved for link ID $url saved", get_class($this));
        } else {
            $this->logger->logStatus("Error '$error_text' for URL NOT saved", get_class($this));
        }
        return $ret;
    }

    public function update(
    $url,
    $expanded,
    $title,
    $post_id,
    $is_image = false
    ){
        $q  = " UPDATE #prefix#links ";
        $q .= " SET expanded_url=:expanded, title=:title, ";
        $q .= " post_id=:postid, is_image=:isimage ";
        $q .= " WHERE url=:url; ";
        $vars = array(
            ':url'=>$url,
            ':expanded'=>$expanded,
            ':title'=>$title,
            ':postid'=>$post_id,
            ':isimage'=>$is_image
        );
        $ps = $this->execute($q, $vars);

        return $this->getUpdateCount($ps);
    }

    public function getLinksByFriends($user_id) {
        $q  = "SELECT l.*, p.*, pub_date - interval 8 hour AS adj_pub_date ";
        $q .= " FROM #prefix#posts AS p ";
        $q .= " INNER JOIN #prefix#links AS l ";
        $q .= " ON p.post_id = l.post_id ";
        $q .= " WHERE p.author_user_id IN ( ";
        $q .= "   SELECT user_id FROM #prefix#follows AS f ";
        $q .= "   WHERE f.follower_id=:user AND f.active=1 ";
        $q .= " )";
        $q .= " ORDER BY l.post_id DESC ";
        $q .= " LIMIT 15 ";
        $vars = array(
            ':user'=>$user_id
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsObjects($ps, "Link");
    }

    public function getPhotosByFriends($user_id) {
        $q  = " SELECT l.*, p.*, pub_date - interval 8 hour as adj_pub_date ";
        $q .= " FROM #prefix#links AS l ";
        $q .= " INNER JOIN #prefix#posts p ";
        $q .= " ON p.post_id = l.post_id ";
        $q .= " WHERE is_image = 1 and p.author_user_id in ( ";
        $q .= "   SELECT user_id FROM #prefix#follows AS f ";
        $q .= "   WHERE f.follower_id=:user AND f.active=1 ";
        $q .= " ) ";
        $q .= " ORDER BY l.post_id DESC  ";
        $q .= " LIMIT 15 ";
        $vars = array(
            ':user'=>$user_id
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataRowsAsObjects($ps, "Link");
    }

    public function getLinksToExpand($limit = 1500) {
        $q  = " SELECT l1.url AS url ";
        $q .= " FROM (  ";
        $q .= "   SELECT l.url, l.post_id ";
        $q .= "   FROM #prefix#links AS l ";
        $q .= "   WHERE l.expanded_url = '' and l.error = '' ";
        $q .= "   ORDER BY post_id DESC LIMIT :limit ";
        $q .= " ) AS l1 ";
        $q .= " GROUP BY l1.url ";
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
        $q  = " SELECT l.url ";
        $q .= " FROM #prefix#links AS l ";
        $q .= " WHERE l.expanded_url = ''  ";
        $q .= " AND l.url LIKE :url AND l.error = '' ";
        $q .= " GROUP BY l.url";
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
        $q  = " SELECT l.* ";
        $q .= " FROM #prefix#links AS l ";
        $q .= " WHERE l.id=:id ";
        $vars = array(
            ':id'=>$id
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataRowAsObject($ps, "Link");
    }

    public function getLinkByUrl($url) {
        $q  = " SELECT l.* ";
        $q .= " FROM #prefix#links AS l ";
        $q .= " WHERE l.url=:url ";
        $vars = array(
            ':url'=>$url
        );
        $ps = $this->execute($q, $vars);

        return $this->getDataRowAsObject($ps, "Link");
    }

}