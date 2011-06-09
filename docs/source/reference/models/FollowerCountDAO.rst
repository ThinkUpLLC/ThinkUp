FollowerCountDAO
================

ThinkUp/webapp/_lib/model/interface.FollowerCountDAO.php

Copyright (c) 2009-2011 Gina Trapani

Follower Count Data Access Object



Methods
-------

insert
~~~~~~
* **@param** int $network_user_id
* **@param** str $network
* **@param** int $count
* **@return** int Total inserted


Insert a count

.. code-block:: php5

    <?php
        public function insert($network_user_id, $network, $count);


getHistory
~~~~~~~~~~
* **@param** int $network_user_id
* **@param** str $network
* **@param** str $group_by 'DAY', 'WEEK', 'MONTH'
* **@param** int $limit Defaults to 10
* **@return** array $history, $percentages


Get follower count history for a user

.. code-block:: php5

    <?php
        public function getHistory($network_user_id, $network, $group_by, $limit=10);




