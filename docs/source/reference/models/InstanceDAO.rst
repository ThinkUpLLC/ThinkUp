InstanceDAO
===========

ThinkUp/webapp/_lib/model/interface.InstanceDAO.php

Copyright (c) 2009-2011 Gina Trapani

Instance Data Access Object Interface



Methods
-------

get
~~~
* **@param** in $instance_id
* **@return** Instance


Gets the instance by ID

.. code-block:: php5

    <?php
        public function get($instance_id);


getAllActiveInstancesStalestFirstByNetwork
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
* **@param** str $network name of network to limit to
* **@return** array with Instance


Get all active instances, by last run oldest first limited to a network

.. code-block:: php5

    <?php
        public function getAllActiveInstancesStalestFirstByNetwork( $network = "twitter" );


getAllInstancesStalestFirst
~~~~~~~~~~~~~~~~~~~~~~~~~~~
* **@return** array with Instance


Get all active instances, by last run oldest first

.. code-block:: php5

    <?php
        public function getAllInstancesStalestFirst();


getInstanceFreshestOne
~~~~~~~~~~~~~~~~~~~~~~
* **@return** Instance Freshest instance


Gets the instance that ran last.

.. code-block:: php5

    <?php
        public function getInstanceFreshestOne();


getInstanceFreshestPublicOne
~~~~~~~~~~~~~~~~~~~~~~~~~~~~
* **@return** Instance Freshest public instance


Gets the public instance that got updated last

.. code-block:: php5

    <?php
        public function getInstanceFreshestPublicOne();


getInstanceStalestOne
~~~~~~~~~~~~~~~~~~~~~
* **@return** Instance Stalest Instance


Gets the instance that ran the longest time ago

.. code-block:: php5

    <?php
        public function getInstanceStalestOne();


insert
~~~~~~
* **@param** int $network_user_id
* **@param** string $network_username
* **@param** string $network - "twitter", "facebook"
* **@param** int $viewer_id
* **@return** int inserted Instance ID


Insert instance

.. code-block:: php5

    <?php
        public function insert($network_user_id, $network_username, $network = "twitter", $viewer_id = false);


delete
~~~~~~
* **@param** string $network_username
* **@param** string $network - "twitter", "facebook"
* **@return** int affected rows


Delete instance

.. code-block:: php5

    <?php
        public function delete($network_username, $network);


getFreshestByOwnerId
~~~~~~~~~~~~~~~~~~~~
* **@param** int $owner_id
* **@return** Instance


Get freshest (most recently updated) instance by owner

.. code-block:: php5

    <?php
        public function getFreshestByOwnerId($owner_id);


getByUsername
~~~~~~~~~~~~~
* **@param** str $username
* **@param** str $network defaults to 'twitter'
* **@return** Instance


Get by username -- DEPRECATED
Use getByUsernameOnNetwork instead
This method assumes the network is Twitter

.. code-block:: php5

    <?php
        public function getByUsername($username, $network = "twitter");


getByUsernameOnNetwork
~~~~~~~~~~~~~~~~~~~~~~
* **@param** str $username
* **@param** str $network
* **@return** Instance


Get by username and network

.. code-block:: php5

    <?php
        public function getByUsernameOnNetwork($username, $network);


getByUserIdOnNetwork
~~~~~~~~~~~~~~~~~~~~
* **@param** str $network_user_id
* **@param** str $network
* **@return** Instance


Get by user ID and network

.. code-block:: php5

    <?php
        public function getByUserIdOnNetwork($network_user_id, $network);


getAllInstances
~~~~~~~~~~~~~~~
* **@param** str $order 'DESC' or 'ASC'
* **@param** bool $only_active Only active instances
* **@param** str $network
* **@return** array Instances


Get all instances

.. code-block:: php5

    <?php
        public function getAllInstances($order = "DESC", $only_active = false, $network = "twitter");


getByOwner
~~~~~~~~~~
* **@param** Owner $owner
* **@param** bool $force_not_admin Override owner's admin status
* **@return** array Instance objects


Get instance by owner

.. code-block:: php5

    <?php
        public function getByOwner($owner, $force_not_admin = false);


getPublicInstances
~~~~~~~~~~~~~~~~~~
* **@return** array Instance objects


Get public instances

.. code-block:: php5

    <?php
        public function getPublicInstances();


getByOwnerAndNetwork
~~~~~~~~~~~~~~~~~~~~
* **@param** Owner $owner
* **@param** string $network
* **@param** boolean $disregard_admin_status
* **@return** array Instances for the owner (all if admin and !$disregard_admin_status)


Get instances by owner and network

.. code-block:: php5

    <?php
        public function getByOwnerAndNetwork($owner, $network, $disregard_admin_status = false);


setPublic
~~~~~~~~~
* **@param** int $instance_id
* **@param** bool $public
* **@return** int number of updated rows (1 if change was successful, 0 if not)


Set whether or not an instance is public, i.e., should be included on the public timeline

.. code-block:: php5

    <?php
        public function setPublic($instance_id, $public);


setActive
~~~~~~~~~
* **@param** int $instance_id
* **@param** bool $active
* **@return** int number of updated rows (1 if change was successful, 0 if not)


Set whether or not an instance is active, i.e., should be crawled

.. code-block:: php5

    <?php
        public function setActive($instance_id, $active);


save
~~~~
* **@param** Instance $instance_object
* **@param** int $user_xml_total_posts_by_owner
* **@param** Logger $logger


Save instance

.. code-block:: php5

    <?php
        public function save($instance_object, $user_xml_total_posts_by_owner, $logger = false);


updateLastRun
~~~~~~~~~~~~~
* **@param** int $id


Update instance last crawler run to NOW()

.. code-block:: php5

    <?php
        public function updateLastRun($id);


isUserConfigured
~~~~~~~~~~~~~~~~
* **@param** str $username
* **@param** str $network
* **@return** bool


Check if a user on a network is configured

.. code-block:: php5

    <?php
        public function isUserConfigured($username, $network);


getByUserAndViewerId
~~~~~~~~~~~~~~~~~~~~
* **@param** int $network_user_id
* **@param** int $viewer_id
* **@param** str $network Defaults to 'facebook'


Get instance by user and viewer ID

.. code-block:: php5

    <?php
        public function getByUserAndViewerId($network_user_id, $viewer_id, $network = "facebook");


getByViewerId
~~~~~~~~~~~~~
* **@param** int $viewer_id
* **@param** str $network
* **@return** Instance


Get instance by viewer ID on a network

.. code-block:: php5

    <?php
        public function getByViewerId($viewer_id, $network = "facebook");


getHoursSinceLastCrawlerRun
~~~~~~~~~~~~~~~~~~~~~~~~~~~
* **@return** int hours


Get the number of hours since the freshest instance was updated

.. code-block:: php5

    <?php
        public function getHoursSinceLastCrawlerRun();




