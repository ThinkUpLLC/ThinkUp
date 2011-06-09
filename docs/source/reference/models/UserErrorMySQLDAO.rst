UserErrorMySQLDAO
=================
Inherits from `PDODAO <./PDODAO.html>`_.

ThinkUp/webapp/_lib/model/class.UserErrorMySQLDAO.php

Copyright (c) 2009-2011 Gina Trapani

User Error MySQL DAO Implementation



Methods
-------

insertError
~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function insertError($id, $error_code, $error_text, $issued_to, $network) {
            $q = "INSERT INTO #prefix#user_errors (user_id, error_code, error_text, error_issued_to_user_id, network) ";
            $q .= "VALUES (:id, :error_code, :error_text, :issued_to, :network) ";
            $vars = array(
                ':id'=>$id, 
                ':error_code'=>$error_code,
                ':error_text'=>$error_text,
                ':issued_to'=>$issued_to,
               ':network'=>$network
            );
            $ps = $this->execute($q, $vars);
    
            return $this->getInsertCount($ps);
        }




