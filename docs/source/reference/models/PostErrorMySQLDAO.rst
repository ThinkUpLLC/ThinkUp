PostErrorMySQLDAO
=================
Inherits from `PDODAO <./PDODAO.html>`_.

ThinkUp/webapp/_lib/model/class.PostErrorMySQLDAO.php

Copyright (c) 2009-2011 Gina Trapani

Post Error MySQL Data Access Object Implementation



Methods
-------

insertError
~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function insertError($post_id, $network, $error_code, $error_text, $issued_to_user_id) {
            $q = "INSERT INTO #prefix#post_errors (post_id, network, error_code, error_text, error_issued_to_user_id) ";
            $q .= " VALUES (:id, :network, :error_code, :error_text, :issued_to);";
            $vars = array(
                ':id'=>$post_id,
                ':network'=>$network,
                ':error_code'=>$error_code,
                ':error_text'=>$error_text,
                ':issued_to'=>$issued_to_user_id
            );
            $ps = $this->execute($q, $vars);
            return $this->getInsertId($ps);
        }




