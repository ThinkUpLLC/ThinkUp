Session
=======

ThinkUp/webapp/_lib/model/class.Session.php

Copyright (c) 2009-2011 Christoffer Viken, Gina Trapani

Session

The object that manages logged-in ThinkUp users' sessions via the web and API calls.


Properties
----------

salt
~~~~



api_salt
~~~~~~~~

Salt used to create API secret tokens.



Methods
-------

isLoggedIn
~~~~~~~~~~
* **@return** bool Is user logged into ThinkUp




.. code-block:: php5

    <?php
        public static function isLoggedIn() {
            if (!SessionCache::isKeySet('user')) {
                return false;
            } else {
                return true;
            }
        }


isAdmin
~~~~~~~
* **@return** bool Is user logged into ThinkUp an admin




.. code-block:: php5

    <?php
        public static function isAdmin() {
            if (SessionCache::isKeySet('user_is_admin')) {
                return SessionCache::get('user_is_admin');
            } else {
                return false;
            }
        }


getLoggedInUser
~~~~~~~~~~~~~~~
* **@return** str Currently logged-in ThinkUp username (email address)




.. code-block:: php5

    <?php
        public static function getLoggedInUser() {
            if (self::isLoggedIn()) {
                return SessionCache::get('user');
            } else {
                return null;
            }
        }


md5pwd
~~~~~~
* **@param** str $pwd Password
* **@return** str MD5-hashed password




.. code-block:: php5

    <?php
        private function md5pwd($pwd) {
            return md5($pwd);
        }


sha1pwd
~~~~~~~
* **@param** str $pwd Password
* **@return** str SHA1-hashed password




.. code-block:: php5

    <?php
        private function sha1pwd($pwd) {
            return sha1($pwd);
        }


saltedsha1
~~~~~~~~~~
* **@param** str $pwd
* **@return** str Salted SHA1 password




.. code-block:: php5

    <?php
        private function saltedsha1($pwd) {
            return sha1(sha1($pwd.$this->salt).$this->salt);
        }


pwdCrypt
~~~~~~~~
* **@param** str $pwd password
* **@return** str Encrypted password


Encrypt password

.. code-block:: php5

    <?php
        public function pwdCrypt($pwd) {
            return $this->saltedsha1($pwd);
        }


pwdCheck
~~~~~~~~
* **@param** str $pwd Password
* **@param** str $result Result
* **@return** bool Whether or submitted password matches check


Check password

.. code-block:: php5

    <?php
        public function pwdCheck($pwd, $result) {
            if ($this->saltedsha1($pwd) == $result || $this->sha1pwd($pwd) == $result || $this->md5pwd($pwd) == $result) {
                return true;
            } else {
                return false;
            }
        }


completeLogin
~~~~~~~~~~~~~
* **@param** Owner $owner


Complete login action

.. code-block:: php5

    <?php
        public static function completeLogin($owner) {
            SessionCache::put('user', $owner->email);
            SessionCache::put('user_is_admin', $owner->is_admin);
        }


logout
~~~~~~

Log out

.. code-block:: php5

    <?php
        public static function logout() {
            SessionCache::unsetKey('user');
            SessionCache::unsetKey('user_is_admin');
        }


isAPICallAuthorized
~~~~~~~~~~~~~~~~~~~
* **@return** boolean Are the provided username and API secret parameters valid?


Checks the username and API secret from the request, and returns true if they match, and are both valid.

.. code-block:: php5

    <?php
        public static function isAPICallAuthorized($username, $api_secret) {
            $owner_dao = DAOFactory::getDAO('OwnerDAO');
            $pwd_from_db = $owner_dao->getPass($username);
            if ($pwd_from_db !== false && $api_secret == self::getAPISecretFromPassword($pwd_from_db)) {
                return true;
            }
            return false;
        }


getAPISecretFromPassword
~~~~~~~~~~~~~~~~~~~~~~~~
* **@param** str $pwd_from_db (hash)
* **@return** str Secret API token


Returns a secret API token that should be used when doing API calls.

.. code-block:: php5

    <?php
        public static function getAPISecretFromPassword($pwd_from_db) {
            return sha1(sha1($pwd_from_db.self::$api_salt).self::$api_salt);
        }




