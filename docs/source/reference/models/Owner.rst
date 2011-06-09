Owner
=====

ThinkUp/webapp/_lib/model/class.Owner.php

Copyright (c) 2009-2011 Gina Trapani

ThinkUp User, i.e., owner of social network user accounts


Properties
----------

id
~~



full_name
~~~~~~~~~



email
~~~~~



is_admin
~~~~~~~~



is_activated
~~~~~~~~~~~~



last_login
~~~~~~~~~~



instances
~~~~~~~~~



password_token
~~~~~~~~~~~~~~

Token to email to user for resetting password

failed_logins
~~~~~~~~~~~~~

Count of failed login attempts

account_status
~~~~~~~~~~~~~~

String describing acount status, like "Too many failed logins" or "Never activated"



Methods
-------

__construct
~~~~~~~~~~~
* **@param** array $val Key/value pairs to construct Owner
* **@return** Owner


Constructor

.. code-block:: php5

    <?php
        public function __construct($val=false) {
            if ($val) {
                $this->id = $val["id"];
                $this->full_name = $val["full_name"];
                $this->email = $val['email'];
                $this->last_login = $val['last_login'];
                $this->is_admin = PDODAO::convertDBToBool($val["is_admin"]);
                $this->is_activated = PDODAO::convertDBToBool($val["is_activated"]);
                $this->account_status = $val["account_status"];
                $this->failed_logins = $val["failed_logins"];
            }
        }


setInstances
~~~~~~~~~~~~
* **@param** array $instances


Setter

.. code-block:: php5

    <?php
        public function setInstances($instances) {
            $this->instances = $instances;
        }


setPasswordRecoveryToken
~~~~~~~~~~~~~~~~~~~~~~~~
* **@return** string A new password token for embedding in a link and emailing a user.


Generates a new password recovery token and returns it.

The internal format of the token is a Unix timestamp of when it was set (for checking if it's stale), an
underscore, and then the token itself.

.. code-block:: php5

    <?php
        public function setPasswordRecoveryToken() {
            $token = md5(uniqid(rand()));
            $dao = DAOFactory::getDAO('OwnerDAO');
            $dao->updatePasswordToken($this->email, $token . '_' . time());
            return $token;
        }


validateRecoveryToken
~~~~~~~~~~~~~~~~~~~~~
* **@param** string $token The token to validate against the database.
* **@return** bool Whether the token is valid or not.


Returns whether a given password recovery token is valid or not.

This requires that the token not be stale (older than a day), and that  token itself matches what's in the
database.

.. code-block:: php5

    <?php
        public function validateRecoveryToken($token) {
            $data = explode('_', $this->password_token);
            return ((time() - $data[1] <= 86400) && ($token == $data[0]));
        }




