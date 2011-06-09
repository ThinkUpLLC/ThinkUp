OwnerDAO
========

ThinkUp/webapp/_lib/model/interface.OwnerDAO.php

Copyright (c) 2009-2011 Gina Trapani

Owner Data Access Object interface



Methods
-------

getByEmail
~~~~~~~~~~
* **@param** str $email
* **@return** Owner


Gets owner by email address

.. code-block:: php5

    <?php
        public function getByEmail($email);


getAllOwners
~~~~~~~~~~~~
* **@return** array Of Owner objects


Gets all ThinkUp owners

.. code-block:: php5

    <?php
        public function getAllOwners();


doesOwnerExist
~~~~~~~~~~~~~~
* **@param** str $email
* **@return** bool


Checks whether or not owner is in storage

.. code-block:: php5

    <?php
        public function doesOwnerExist($email);


getPass
~~~~~~~
* **@param** str $email
* **@return** str|bool Password string or false if none


Get password for activated owner by email

.. code-block:: php5

    <?php
        public function getPass($email);


getActivationCode
~~~~~~~~~~~~~~~~~
* **@param** str $email
* **@return** str Activation code


Get activation code for an owner

.. code-block:: php5

    <?php
        public function getActivationCode($email);


activateOwner
~~~~~~~~~~~~~
* **@param** str $email
* **@return** int Affected rows


Activate an owner

.. code-block:: php5

    <?php
        public function activateOwner($email);


deactivateOwner
~~~~~~~~~~~~~~~
* **@param** str $email
* **@return** int Affected rows


Dectivate an owner

.. code-block:: php5

    <?php
        public function deactivateOwner($email);


updatePassword
~~~~~~~~~~~~~~
* **@param** str $email
* **@param** str $pwd
* **@return** int Affected rows


Set owner password

.. code-block:: php5

    <?php
        public function updatePassword($email, $pwd);


create
~~~~~~
* **@param** str $email
* **@param** str $pass
* **@param** str $acode
* **@param** str $full_name
* **@return** int Affected rows


Insert owner

.. code-block:: php5

    <?php
        public function create($email, $pass, $acode, $full_name);


updateLastLogin
~~~~~~~~~~~~~~~
* **@param** str $email Owner's email
* **@return** int Affected rows


Update last_login field for given owner

.. code-block:: php5

    <?php
        public function updateLastLogin($email);


updatePasswordToken
~~~~~~~~~~~~~~~~~~~
* **@param** str $email The email address of the owner to set it for
* **@param** str $token The MD5 token and timestamp, separated by an underscore
* **@return** int Affected rows


Update an owner's token for recovering their password

.. code-block:: php5

    <?php
        public function updatePasswordToken($email, $token);


getByPasswordToken
~~~~~~~~~~~~~~~~~~
* **@param** str $token The token to load, minus the timestamp
* **@return** int The full Owner object


Load an owner by their recovery token

.. code-block:: php5

    <?php
        public function getByPasswordToken($token);


doesAdminExist
~~~~~~~~~~~~~~
* **@return** bool Whether or not admin user exists in the store.


Check if admin owner exists

.. code-block:: php5

    <?php
        public function doesAdminExist();


createAdmin
~~~~~~~~~~~
* **@param** str $email
* **@param** str $pwd
* **@param** str $activation_code
* **@param** str $full_name
* **@return** int Update count


Insert an activated admin owner

.. code-block:: php5

    <?php
        public function createAdmin($email, $pwd, $activation_code, $full_name);


promoteToAdmin
~~~~~~~~~~~~~~
* **@param** str $email Owner email address.
* **@return** int Update count


Promote an owner to admin status.

.. code-block:: php5

    <?php
        public function promoteToAdmin($email);


getAdmins
~~~~~~~~~
* **@return** array An array of Owners


Gets a list of the admin users

.. code-block:: php5

    <?php
        public function getAdmins();


incrementFailedLogins
~~~~~~~~~~~~~~~~~~~~~
* **@param** str $email


Increment the number of failed logins for a given owner.

.. code-block:: php5

    <?php
        public function incrementFailedLogins($email);


resetFailedLogins
~~~~~~~~~~~~~~~~~
* **@param** str $email
* **@return** bool True on success


Reset the number of failed login attempts to 0 (called on a successful login).

.. code-block:: php5

    <?php
        public function resetFailedLogins($email);


setAccountStatus
~~~~~~~~~~~~~~~~
* **@param** str $email
* **@param** str $status
* **@return** bool True on success


Set the contents of the acount status field for an owner.

.. code-block:: php5

    <?php
        public function setAccountStatus($email, $status);


clearAccountStatus
~~~~~~~~~~~~~~~~~~
* **@param** str $email
* **@return** bool True on success


Sets the account status to an empty string.

.. code-block:: php5

    <?php
        public function clearAccountStatus($email);


setOwnerActive
~~~~~~~~~~~~~~
* **@param** str $owner_id
* **@param** int $is_activated Active = 1, Inactive=0.
* **@return** int number of updated rows.


Activates an owner account.

.. code-block:: php5

    <?php
        public function setOwnerActive($id, $is_activated);




