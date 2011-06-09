OwnerMySQLDAO
=============
Inherits from `PDODAO <./PDODAO.html>`_.

ThinkUp/webapp/_lib/model/class.OwnerMySQLDAO.php

Copyright (c) 2009-2011 Gina Trapani

Owner Data Access Object
The data access object for retrieving and saving owners in the ThinkUp database.



Methods
-------

getByEmail
~~~~~~~~~~



.. code-block:: php5

    <?php
        public function getByEmail($email) {
            $q = <<<SQL
    SELECT
        id,
        full_name,
        email,
        is_admin,
        last_login,
        is_activated,
        password_token,
        account_status,
        failed_logins
    FROM #prefix#owners AS o
    WHERE email = :email;
    SQL;
    
            $vars = array(
                ':email'=>$email
            );
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $ps = $this->execute($q, $vars);
            return $this->getDataRowAsObject($ps, 'Owner');
        }


getAllOwners
~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function getAllOwners() {
            $q = " SELECT id, full_name, email, is_admin, is_activated, last_login ";
            $q .= "FROM #prefix#owners ORDER BY last_login DESC;";
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $ps = $this->execute($q);
            return $this->getDataRowsAsObjects($ps, 'Owner');
        }


getAdmins
~~~~~~~~~



.. code-block:: php5

    <?php
        public function getAdmins() {
            $q = " SELECT id, full_name, email, is_admin, is_activated, last_login ";
            $q .= "FROM #prefix#owners WHERE is_admin = 1 AND is_activated = 1 ORDER BY id";
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $ps = $this->execute($q);
            $admins = $this->getDataRowsAsObjects($ps, 'Owner');
            if(count($admins) == 0) { $admins = null; }
            return $admins;
        }


doesOwnerExist
~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function doesOwnerExist($email) {
            $q = " SELECT email FROM #prefix#owners WHERE email=:email";
            $vars = array(
                ':email'=>$email
            );
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $ps = $this->execute($q, $vars);
            return $this->getDataIsReturned($ps);
        }


getPass
~~~~~~~



.. code-block:: php5

    <?php
        public function getPass($email) {
            $q = "SELECT pwd FROM #prefix#owners  WHERE email = :email AND is_activated='1' LIMIT 1;";
            $vars = array(
                ':email'=>$email
            );
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $ps = $this->execute($q, $vars);
            $result = $this->getDataRowAsArray($ps);
            if (isset($result['pwd'])) {
                return $result['pwd'];
            } else {
                return false;
            }
        }


getActivationCode
~~~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function getActivationCode($email) {
            $q = " SELECT activation_code  FROM #prefix#owners  WHERE email=:email";
            $vars = array(
                ':email'=>$email
            );
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $ps = $this->execute($q, $vars);
            return $this->getDataRowAsArray($ps);
        }


activateOwner
~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function activateOwner($email) {
            $this->updateActivation($email, true);
        }


deactivateOwner
~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function deactivateOwner($email) {
            $this->updateActivation($email, false);
        }


updateActivation
~~~~~~~~~~~~~~~~
* **@param** str $email
* **@param** bool $is_activated
* **@return** int Count of affected rows


Set the value of the is_activated field.

.. code-block:: php5

    <?php
        private function updateActivation($email, $is_activated) {
            $q = " UPDATE #prefix#owners SET is_activated=:is_activated WHERE email=:email";
            $vars = array(
                ':email'=>$email,
                ':is_activated'=>(($is_activated)?1:0)
            );
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $ps = $this->execute($q, $vars);
            return $this->getUpdateCount($ps);
        }


updatePassword
~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function updatePassword($email, $pwd) {
            $q = " UPDATE #prefix#owners SET pwd=:pwd WHERE email=:email";
            $vars = array(
                ':email'=>$email,
                ':pwd'=>$pwd
            );
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $ps = $this->execute($q, $vars);
            return $this->getUpdateCount($ps);
        }


create
~~~~~~



.. code-block:: php5

    <?php
        public function create($email, $pass, $acode, $full_name) {
            return $this->createOwner($email, $pass, $acode, $full_name, false);
        }


createAdmin
~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function createAdmin($email, $pwd, $activation_code, $full_name) {
            return $this->createOwner($email, $pwd, $activation_code, $full_name, true);
        }


createOwner
~~~~~~~~~~~



.. code-block:: php5

    <?php
        private function createOwner($email, $pass, $acode, $full_name, $is_admin) {
            if (!$this->doesOwnerExist($email)) {
                $q = "INSERT INTO #prefix#owners SET email=:email, pwd=:pass, joined=NOW(), activation_code=:acode, ";
                $q .= "full_name=:full_name";
                if ($is_admin) {
                    $q .= ", is_admin=1";
                }
                $vars = array(
                    ':email'=>$email,
                    ':pass'=>$pass,
                    ':acode'=>$acode,
                    ':full_name'=>$full_name
                );
                if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
                $ps = $this->execute($q, $vars);
                return $this->getUpdateCount($ps);
            } else {
                return 0;
            }
        }


updateLastLogin
~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function updateLastLogin($email) {
            $q = " UPDATE #prefix#owners SET last_login=now() WHERE email=:email";
            $vars = array(
                ':email'=>$email
            );
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $ps = $this->execute($q, $vars);
            return $this->getUpdateCount($ps);
        }


updatePasswordToken
~~~~~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function updatePasswordToken($email, $token) {
            $q = "UPDATE #prefix#owners
                  SET password_token=:token
                  WHERE email=:email";
            $vars = array(
                ":token" => $token, 
                ":email" => $email
            );
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $ps = $this->execute($q, $vars);
            return $this->getUpdateCount($ps);
        }


getByPasswordToken
~~~~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function getByPasswordToken($token) {
            $q = "SELECT * FROM #prefix#owners WHERE password_token LIKE :token";
            $vars = array(':token' => $token . '_%');
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $ps = $this->execute($q, $vars);
            return $this->getDataRowAsObject($ps, 'Owner');
        }


doesAdminExist
~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function doesAdminExist() {
            $q = "SELECT id FROM #prefix#owners WHERE is_admin = 1";
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $ps = $this->execute($q);
            return $this->getDataIsReturned($ps);
        }


promoteToAdmin
~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function promoteToAdmin($email) {
            $q = "UPDATE #prefix#owners
                  SET is_admin=1
                  WHERE email=:email";
            $vars = array(
                ":email" => $email
            );
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $ps = $this->execute($q, $vars);
            return $this->getUpdateCount($ps);
        }


incrementFailedLogins
~~~~~~~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function incrementFailedLogins($email) {
            $q = "UPDATE #prefix#owners
                  SET failed_logins=failed_logins+1
                  WHERE email=:email";
            $vars = array(
                ":email" => $email
            );
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $ps = $this->execute($q, $vars);
            return ( $this->getUpdateCount($ps) > 0 )? true : false;
        }


resetFailedLogins
~~~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function resetFailedLogins($email) {
            $q = "UPDATE #prefix#owners
                  SET failed_logins=0
                  WHERE email=:email";
            $vars = array(
                ":email" => $email
            );
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $ps = $this->execute($q, $vars);
            return ( $this->getUpdateCount($ps) > 0 )? true : false;
        }


setAccountStatus
~~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function setAccountStatus($email, $status) {
            $q = "UPDATE #prefix#owners
                  SET account_status=:account_status
                  WHERE email=:email";
            $vars = array(
                ":account_status" => $status,
                ":email" => $email
            );
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $ps = $this->execute($q, $vars);
            return ( $this->getUpdateCount($ps) > 0 )? true : false;
        }


clearAccountStatus
~~~~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function clearAccountStatus($email) {
            return  $this->setAccountStatus($email, '');
        }


setOwnerActive
~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function setOwnerActive($id, $is_activated) {
            $q = "UPDATE #prefix#owners
                 SET is_activated=:is_activated
                 WHERE id=:id";
            if ($this->profiler_enabled) Profiler::setDAOMethod(__METHOD__);
            $stmt = $this->execute($q, array(':is_activated' => $is_activated, ':id' => $id));
            return $this->getUpdateCount($stmt);
        }




