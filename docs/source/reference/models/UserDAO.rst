UserDAO
=======

ThinkUp/webapp/_lib/model/interface.UserDAO.php

Copyright (c) 2009-2011 Gina Trapani

User Data Access Object interface



Methods
-------

isUserInDB
~~~~~~~~~~
* **@param** int $user_id
* **@param** str $network
* **@return** bool True if yes, false if not


Check if a user is in the database given a user ID

.. code-block:: php5

    <?php
        public function isUserInDB($user_id, $network);


isUserInDBByName
~~~~~~~~~~~~~~~~
* **@param** str $username
* **@param** str $network
* **@return** bool True if yes, false if not


Check if a user is in the database given a username

.. code-block:: php5

    <?php
        public function isUserInDBByName($username, $network);


updateUser
~~~~~~~~~~
* **@param** User $user
* **@return** int Total number of affected rows


Update existing or insert new user

.. code-block:: php5

    <?php
        public function updateUser($user);


getDetails
~~~~~~~~~~
* **@param** int $user_id
* **@param** str $network
* **@return** User User


Get user given an ID

.. code-block:: php5

    <?php
        public function getDetails($user_id, $network);


updateUsers
~~~~~~~~~~~
* **@param** array $users_to_update Array of User objects
* **@return** int Total users affected


Update an array of users

.. code-block:: php5

    <?php
        public function updateUsers($users_to_update);


getUserByName
~~~~~~~~~~~~~
* **@param** str $user_name
* **@param** str $network
* **@return** User User object


Get user given a username

.. code-block:: php5

    <?php
        public function getUserByName($user_name, $network);




