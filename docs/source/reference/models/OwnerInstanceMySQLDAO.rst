OwnerInstanceMySQLDAO
=====================
Inherits from `PDODAO <./PDODAO.html>`_.

ThinkUp/webapp/_lib/model/class.OwnerInstanceMySQLDAO.php

Copyright (c) 2009-2011 Gina Trapani

OwnerInstance Data Access Object MySQL Implementationn



Methods
-------

doesOwnerHaveAccess
~~~~~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function doesOwnerHaveAccess($owner, $instance) {
            // verify $owner is a proper object and has an owner_id
            if(! is_a($owner, 'Owner') || ! isset($owner->id)) {
                $message = 'doesOwnerHaveAccess() requires a valid "Owner" object with "id" defined';
                throw new BadArgumentException($message);
            }
            if(! is_a($instance, 'Instance') || ! isset($instance->id)) {
                $message = 'doesOwnerHaveAccess() requires a valid "Instance" object with "id" defined';
                throw new BadArgumentException($message);
            }
            if ($owner->is_admin) {
                return true;
            } else {
                $q = '
                    SELECT 
                        * 
                    FROM 
                        #prefix#owner_instances oi
                    INNER JOIN
                        #prefix#instances i
                    ON 
                        i.id = oi.instance_id
                    WHERE 
                        i.id = :id AND oi.owner_id = :owner_id';
                $vars = array(':owner_id' => $owner->id, ':id' => $instance->id);
                $stmt = $this->execute($q, $vars);
                return $this->getDataIsReturned($stmt);
            }
        }


get
~~~



.. code-block:: php5

    <?php
        public function get($owner_id, $instance_id) {
            $q = "SELECT
                    id, owner_id, instance_id, oauth_access_token, oauth_access_token_secret
                FROM 
                    #prefix#owner_instances 
                WHERE 
                    owner_id = :owner_id AND instance_id = :instance_id";
    
            $vars = array(':owner_id' => $owner_id, ':instance_id' => $instance_id);
            $stmt = $this->execute($q, $vars);
            $owner_instance = $this->getDataRowAsObject($stmt, 'OwnerInstance');
            return $owner_instance;
        }


getByInstance
~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function getByInstance($instance_id) {
            $q = "SELECT
                    id, owner_id, instance_id, oauth_access_token, oauth_access_token_secret
                FROM 
                    #prefix#owner_instances 
                WHERE  instance_id = :instance_id";
    
            $vars = array(':instance_id' => $instance_id);
            $stmt = $this->execute($q, $vars);
            $owner_instances = $this->getDataRowsAsObjects($stmt, 'OwnerInstance');
            return $owner_instances;
        }


insert
~~~~~~



.. code-block:: php5

    <?php
        public function insert($owner_id, $instance_id, $oauth_token = '', $oauth_token_secret = '') {
            $q = "INSERT INTO #prefix#owner_instances
                    (owner_id, instance_id, oauth_access_token, oauth_access_token_secret)
                        VALUES (:owner_id,:instance_id,:oauth_access_token,:oauth_access_token_secret)";
    
            $vars = array(':owner_id' => $owner_id,
                          ':instance_id' => $instance_id,
                          ':oauth_access_token' => $oauth_token,
                          ':oauth_access_token_secret' => $oauth_token_secret
            );
            $stmt = $this->execute($q, $vars);
            if ( $this->getInsertCount($stmt) > 0) {
                return true;
            } else {
                return false;
            }
        }


delete
~~~~~~



.. code-block:: php5

    <?php
        public function delete($owner_id, $instance_id) {
            $q  = "DELETE FROM #prefix#owner_instances ";
            $q .= "WHERE owner_id=:owner_id AND instance_id=:instance_id;";
            $vars = array(
                ':owner_id'=>$owner_id,
                ':instance_id'=>$instance_id
            );
            $ps = $this->execute($q, $vars);
            return $this->getUpdateCount($ps);
        }


deleteByInstance
~~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function deleteByInstance($instance_id) {
            $q  = "DELETE FROM #prefix#owner_instances ";
            $q .= "WHERE instance_id=:instance_id;";
            $vars = array(
                ':instance_id'=>$instance_id
            );
            $ps = $this->execute($q, $vars);
            return $this->getUpdateCount($ps);
        }


updateTokens
~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function updateTokens($owner_id, $instance_id, $oauth_token, $oauth_token_secret) {
            $q = 'UPDATE
                    #prefix#owner_instances 
                SET 
                    oauth_access_token=:oauth_access_token, oauth_access_token_secret=:oauth_access_token_secret
                WHERE
                    owner_id = :owner_id AND instance_id = :instance_id';
            $vars = array(  ':owner_id' => $owner_id,
                            ':instance_id' => $instance_id,
                            ':oauth_access_token' => $oauth_token,
                            ':oauth_access_token_secret' => $oauth_token_secret
            );
            $stmt = $this->execute($q, $vars);
            $insert_count = $this->getInsertCount($stmt);
            return ($insert_count > 0) ? true : false;
        }


getOAuthTokens
~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function getOAuthTokens($id) {
            $q = "SELECT
                    oauth_access_token, oauth_access_token_secret 
                FROM 
                    #prefix#owner_instances 
                WHERE 
                    instance_id = :instance_id ORDER BY id ASC LIMIT 1";
            $stmt = $this->execute($q, array(':instance_id' => $id));
            $tokens = $this->getDataRowAsArray($stmt);
            return $tokens;
        }




