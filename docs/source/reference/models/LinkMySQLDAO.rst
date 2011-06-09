LinkMySQLDAO
============
Inherits from `PDODAO <./PDODAO.html>`_.

ThinkUp/webapp/_lib/model/class.LinkMySQLDAO.php

Copyright (c) 2009-2011 Gina Trapani

Link MySQL Data Access Object



Methods
-------

insert
~~~~~~



.. code-block:: php5

    <?php
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
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $ps = $this->execute($q, $vars);
    
            return $this->getInsertId($ps);
        }


saveExpandedURL
~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
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
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $ps = $this->execute($q, $vars);
    
            $ret = $this->getUpdateCount($ps);
            if ($ret > 0) {
                $this->logger->logSuccess("Expanded URL $expanded for $url saved", __METHOD__.','.__LINE__);
            } else {
                $this->logger->logError("Expanded URL NOT saved", __METHOD__.','.__LINE__);
            }
            return $ret;
        }


saveExpansionError
~~~~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function saveExpansionError($url, $error_text){
            $q  = "UPDATE #prefix#links ";
            $q .= "SET error=:error ";
            $q .= "WHERE url=:url ";
            $vars = array(
                ':url'=>$url,
                ':error'=>$error_text
            );
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $ps = $this->execute($q, $vars);
    
            $ret = $this->getUpdateCount($ps);
            if ($ret > 0) {
                $this->logger->logInfo("Error '$error_text' saved for link ID $url saved", __METHOD__.','.__LINE__);
            } else {
                $this->logger->logInfo("Error '$error_text' for URL NOT saved", __METHOD__.','.__LINE__);
            }
            return $ret;
        }


update
~~~~~~



.. code-block:: php5

    <?php
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
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $ps = $this->execute($q, $vars);
            return $this->getUpdateCount($ps);
        }


getLinksByFriends
~~~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function getLinksByFriends($user_id, $network, $count = 15, $page = 1) {
            $start_on_record = ($page - 1) * $count;
    
            $q  = "SELECT l.*, p.*, pub_date + interval #gmt_offset# hour AS adj_pub_date ";
            $q .= "FROM #prefix#posts AS p ";
            $q .= "INNER JOIN #prefix#links AS l ";
            $q .= "ON p.post_id = l.post_id AND p.network = l.network ";
            $q .= "WHERE l.network = :network AND  p.author_user_id IN ( ";
            $q .= "   SELECT user_id FROM #prefix#follows AS f ";
            $q .= "   WHERE f.follower_id=:user_id AND f.active=1 AND f.network=:network ";
            $q .= ")";
            $q .= "ORDER BY l.post_id DESC ";
            $q .= "LIMIT :start_on_record, :limit";
            $vars = array(
                ':user_id'=>$user_id,
                ':network'=>$network,
                ':limit'=>$count,
                ':start_on_record'=>(int)$start_on_record
            );
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $ps = $this->execute($q, $vars);
            $all_rows = $this->getDataRowsAsArrays($ps);
            $links = array();
            foreach ($all_rows as $row) {
                $links[] = $this->setLinkWithPost($row);
            }
            return $links;
        }


setLinkWithPost
~~~~~~~~~~~~~~~
* **@param** array $row
* **@return** Link object with post member object set


Add post object to link

.. code-block:: php5

    <?php
        private function setLinkWithPost($row) {
            $link = new Link($row);
            $post = new Post($row);
            $link->container_post = $post;
            return $link;
        }


getLinksByFavorites
~~~~~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function getLinksByFavorites($user_id, $network, $count = 15, $page = 1) {
            $start_on_record = ($page - 1) * $count;
    
            $q  = "SELECT l.*, p.*, pub_date - interval 8 hour AS adj_pub_date ";
            $q .= "FROM #prefix#posts as p, #prefix#favorites as f, #prefix#links as l WHERE f.post_id = p.post_id ";
            $q .= "AND p.post_id = l.post_id AND p.network = l.network ";
            $q .= "AND l.network = :network AND  f.fav_of_user_id = :user_id ";
            $q .= "ORDER BY l.post_id DESC ";
            $q .= "LIMIT :start_on_record, :limit";
            $vars = array(
                ':user_id'=>$user_id,
                ':network'=>$network,
                ':limit'=>$count,
                ':start_on_record'=>(int)$start_on_record
            );
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $ps = $this->execute($q, $vars);
            return $this->getDataRowsAsObjects($ps, "Link");
        }


getPhotosByFriends
~~~~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function getPhotosByFriends($user_id, $network, $count = 15, $page = 1) {
            $start_on_record = ($page - 1) * $count;
    
            $q  = "SELECT l.*, p.*, pub_date + interval #gmt_offset# hour as adj_pub_date ";
            $q .= "FROM #prefix#links AS l ";
            $q .= "INNER JOIN #prefix#posts p ";
            $q .= "ON p.post_id = l.post_id AND p.network = l.network ";
            $q .= "WHERE is_image = 1 AND l.network=:network AND p.author_user_id in ( ";
            $q .= "   SELECT user_id FROM #prefix#follows AS f ";
            $q .= "   WHERE f.follower_id=:user_id AND f.active=1 AND f.network = :network) ";
            $q .= "ORDER BY l.post_id DESC  ";
            $q .= "LIMIT :start_on_record, :limit";
            $vars = array(
                ':user_id'=>$user_id,
                ':network'=>$network,
                ':limit'=>$count,
                ':start_on_record'=>(int)$start_on_record
            );
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $ps = $this->execute($q, $vars);
            $all_rows = $this->getDataRowsAsArrays($ps);
            $links = array();
            foreach ($all_rows as $row) {
                $links[] = $this->setLinkWithPost($row);
            }
            return $links;
        }


getLinksToExpand
~~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
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
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $ps = $this->execute($q, $vars);
    
            $rows = $this->getDataRowsAsArrays($ps);
            $urls = array();
            foreach($rows as $row){
                $urls[] = $row['url'];
            }
            return $urls;
        }


getLinksToExpandByURL
~~~~~~~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function getLinksToExpandByURL($url) {
            $q  = "SELECT l.url ";
            $q .= "FROM #prefix#links AS l ";
            $q .= "WHERE l.expanded_url = ''  ";
            $q .= "AND l.url LIKE :url AND l.error = '' ";
            $q .= "GROUP BY l.url";
            $vars = array(
                ':url'=>$url."%"
                );
                if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
                $ps = $this->execute($q, $vars);
    
                $rows = $this->getDataRowsAsArrays($ps);
                $urls = array();
                foreach($rows as $row){
                    $urls[] = $row['url'];
                }
                return $urls;
        }


getLinkById
~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function getLinkById($id) {
            $q  = "SELECT l.* ";
            $q .= "FROM #prefix#links AS l ";
            $q .= "WHERE l.id=:id ";
            $vars = array(
                ':id'=>$id
            );
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $ps = $this->execute($q, $vars);
    
            return $this->getDataRowAsObject($ps, "Link");
        }


getLinkByUrl
~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function getLinkByUrl($url) {
            $q  = "SELECT l.* ";
            $q .= "FROM #prefix#links AS l ";
            $q .= "WHERE l.url=:url ";
            $vars = array(
                ':url'=>$url
            );
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $ps = $this->execute($q, $vars);
    
            return $this->getDataRowAsObject($ps, "Link");
        }




