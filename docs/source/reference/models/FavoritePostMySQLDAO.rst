FavoritePostMySQLDAO
====================
Inherits from `PostMySQLDAO <./PostMySQLDAO.html>`_.

ThinkUp/webapp/_lib/model/class.FavoritePostMySQLDAO.php

Copyright (c) 2009-2011 Amy Unruh, Gina Trapani

FavoritePost Data Access Object

The data access object for retrieving and saving favorited posts in the ThinkUp database.
This class extends PostMySQLDAO, and adds access to a favorites 'join' table that is used to record information
about favorited posts.  The favorites table stores post id, user (author) id, favoriter id, and network.



Methods
-------

addFavorite
~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function addFavorite($favoriter_id, $vals) {
            if (!$favoriter_id) {
                throw new Exception("error: favoriter/owner id not set");
            }
            // first add the post (if need be-- this post may have already been inserted).
            $retval = $this->addPostAndEntities($vals, null);
            $q = " INSERT IGNORE INTO #prefix#favorites
             (post_id, author_user_id, fav_of_user_id, network)
            VALUES ( :pid, :uid, :fid, :network) ";
            $vars = array(
                ':pid' => $vals['post_id'],
                ':uid' => $vals['author_user_id'],
                ':fid' => $favoriter_id,
                ':network' => $vals['network']
            );
            $res = $this->execute($q, $vars);
            return $this->getUpdateCount($res);
        }


unFavorite
~~~~~~~~~~



.. code-block:: php5

    <?php
        public function unFavorite($tid, $uid, $network = 'twitter') {
            $q = " DELETE FROM #prefix#favorites where post_id = :tid AND fav_of_user_id = :uid AND network = :network";
            $vars = array(
                ':tid' => $tid,
                ':uid' => $uid,
                ':network' => $network,
            );
            $res = $this->execute($q, $vars);
            return $this->getUpdateCount($res);
        }


getAllFavoritePosts
~~~~~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function getAllFavoritePosts($owner_id, $network, $count, $page=1) {
            return $this->getAllFavoritePostsByUserID($owner_id, $network, $count, "pub_date", "DESC", null, $page);
        }


getAllFavoritePostsUpperBound
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function getAllFavoritePostsUpperBound($owner_id, $network, $count, $ub) {
            return $this->getAllFavoritePostsByUserID($owner_id, $network, $count, "pub_date", "DESC", $ub);
        }


getAllFavoritePostsByUsername
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function getAllFavoritePostsByUsername($username, $network, $count) {
            return $this->getAllFavoritePostsByUsernameOrderedBy($username, $network, $count, "pub_date");
        }


getAllFavoritePostsByUserID
~~~~~~~~~~~~~~~~~~~~~~~~~~~
* **@param** int $owner_id
* **@param** str $network
* **@param** int $count
* **@param** str $order_by field name
* **@param** str $direction either "DESC" or "ASC
* **@param** int $ubound
* **@param** int $page
* **@param** bool $iterator
* **@return** array Posts with link object set or PostIterator


Get all favorited posts by a given user id, with configurable order by field and direction.
Returns either an iterator or an array, as specified by $iterator. Supports pagination.

.. code-block:: php5

    <?php
        private function getAllFavoritePostsByUserID($owner_id, $network, $count, $order_by="pub_date", $direction="DESC",
        $ubound = null, $page=1, $iterator = false) {
            $direction = $direction=="DESC" ? "DESC": "ASC";
            $start_on_record = ($page - 1) * $count;
            if ( !in_array($order_by, $this->REQUIRED_FIELDS) && !in_array($order_by, $this->OPTIONAL_FIELDS  )) {
                $order_by="pub_date";
            }
            $q = "select l.*, p.*, pub_date - interval #gmt_offset# hour as adj_pub_date from (#prefix#posts p
            LEFT JOIN #prefix#favorites f on f.post_id = p.post_id) LEFT JOIN #prefix#links l on l.post_id = p.post_id 
            where f.fav_of_user_id = :owner_id AND p.network=:network ";
    
            if ($order_by == 'reply_count_cache') {
                $q .= "AND reply_count_cache > 0 ";
            }
            if ($order_by == 'retweet_count_cache') {
                $q .= "AND retweet_count_cache > 0 ";
            }
            if ($ubound > 0) {
                $q .= "AND p.post_id < :ubound ";
            }
            $q .= " ORDER BY ".$order_by." ".$direction." ";
            if ($count > 0) {
                $q .= "LIMIT :start_on_record, :limit";
            }
            $vars = array(
              ':owner_id'=>$owner_id,
              ':network'=>$network,
              ':limit'=>$count,
              ':start_on_record'=>(int)$start_on_record
            );
            if ($ubound > 0) {
                $vars[':ubound'] = $ubound;
            }
            $ps = $this->execute($q, $vars);
            if($iterator) {
                return (new PostIterator($ps));
            }
            $all_rows = $this->getDataRowsAsArrays($ps);
            $posts = array();
            foreach ($all_rows as $row) {
                $posts[] = $this->setPostWithLink($row);
            }
            return $posts;
        }


getAllFavoritePostsByUsernameOrderedBy
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
* **@param** str $author_username
* **@param** str $network
* **@param** int $count
* **@param** str $order_by
* **@param** int $in_last_x_days
* **@param** bool $iterator
* **@return** array Posts with link object set or PostIterator


Get favorited posts by the given username.
Returns either an iterator or an array, as specified by $iterator.

.. code-block:: php5

    <?php
        private function getAllFavoritePostsByUsernameOrderedBy($author_username, $network="twitter", $count=0,
        $order_by="pub_date", $in_last_x_days = 0, $iterator = false) {
            if ( !in_array($order_by, $this->REQUIRED_FIELDS) && !in_array($order_by, $this->OPTIONAL_FIELDS  )) {
                $order_by="pub_date";
            }
            $vars = array(
              ':author_username'=>$author_username,
              ':network'=>$network
            );
            $q = "select l.*, p.*, pub_date - interval #gmt_offset# hour as adj_pub_date from
            ((#prefix#posts p LEFT JOIN #prefix#favorites f on f.post_id = p.post_id) LEFT JOIN 
            #prefix#links l on l.post_id = p.post_id) LEFT JOIN #prefix#users u on u.user_id = f.fav_of_user_id 
            where u.user_name = :author_username AND p.network=:network ";
    
            if ($in_last_x_days > 0) {
                $q .= "AND pub_date >= DATE_SUB(CURDATE(), INTERVAL :in_last_x_days DAY) ";
                $vars[':in_last_x_days'] = (int)$in_last_x_days;
            }
            if ($order_by == 'reply_count_cache') {
                $q .= "AND reply_count_cache > 0 ";
            }
            if ($order_by == 'retweet_count_cache') {
                $q .= "AND retweet_count_cache > 0 ";
            }
            $q .= " ORDER BY ".$order_by." DESC ";
            if ($count) {
                $q .= " LIMIT :limit";
                $vars[':limit'] = (int)$count;
            }
            $ps = $this->execute($q, $vars);
            if($iterator) {
                return (new PostIterator($ps));
            }
            $all_rows = $this->getDataRowsAsArrays($ps);
            $posts = array();
            foreach ($all_rows as $row) {
                $posts[] = $this->setPostWithLink($row);
            }
            return $posts;
        }


getAllFavoritePostsByUsernameIterator
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function getAllFavoritePostsByUsernameIterator($username, $network, $count = 0) {
            return $this->getAllFavoritePostsByUsernameOrderedBy($username, $network, $count, null, null, true);
        }


getAllFavoritePostsIterator
~~~~~~~~~~~~~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function getAllFavoritePostsIterator($user_id, $network, $count) {
            return $this->getAllFavoritePostsByUserID($user_id, $network, $count, "pub_date", "DESC", null, 1, true);
        }




