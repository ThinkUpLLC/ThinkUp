OwnerInstanceDAO
================

ThinkUp/webapp/_lib/model/interface.OwnerInstanceDAO.php

Copyright (c) 2009-2011 Gina Trapani, Mark Wilkie

OwnerInstance Data Access Object interface



Methods
-------

doesOwnerHaveAccess
~~~~~~~~~~~~~~~~~~~
* **@throws** BadArgumentException If we do not pass a valid owner object
* **@param** Owner
* **@param** Instance
* **@return** bool true if yes, false if not


Check if an Owner has access to an instance

.. code-block:: php5

    <?php
        public function doesOwnerHaveAccess($owner, $instance);


get
~~~
* **@param** int owner_id
* **@param** int instance_id
* **@return** OwnerInstance


Get an owner instance by owner_id and instance_id

.. code-block:: php5

    <?php
        public function get($owner_id, $instance_id);


getByInstance
~~~~~~~~~~~~~
* **@param** int instance_id
* **@return** array OwnerInstance objects


Get owner instances by an instance id

.. code-block:: php5

    <?php
        public function getByInstance($instance_id);


insert
~~~~~~
* **@param** int owner_id
* **@param** int instance_id
* **@param** str auth_token
* **@param** str oauth_token_secret
* **@return** boolean - if insert was successful


Inserts an owner instance record

.. code-block:: php5

    <?php
        public function insert($owner_id, $instance_id, $oauth_token = '', $oauth_token_secret = '');


delete
~~~~~~
* **@param** int owner_id
* **@param** int instance_id
* **@return** int Number of rows affected


Delete an owner instance record

.. code-block:: php5

    <?php
        public function delete($owner_id, $instance_id);


deleteByInstance
~~~~~~~~~~~~~~~~
* **@param** int instance_id
* **@return** int Number of rows affected


Delete all owner instances by instance ID.

.. code-block:: php5

    <?php
        public function deleteByInstance($instance_id);


updateTokens
~~~~~~~~~~~~
* **@param** int owner_id
* **@param** int instance_id
* **@param** str oauth_token
* **@param** str oauth_token_secret
* **@return** boolean


Updates tokens based on user and instance ids, return true|false  update status

.. code-block:: php5

    <?php
        public function updateTokens($owner_id, $instance_id, $oauth_token, $oauth_token_secret);


getOAuthTokens
~~~~~~~~~~~~~~
* **@param** int instance_id
* **@return** array $token_assoc_array


Gets auth tokens by instance_id

.. code-block:: php5

    <?php
        public function getOAuthTokens($id);




