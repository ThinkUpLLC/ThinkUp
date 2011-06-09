FollowDAO
=========

ThinkUp/webapp/_lib/model/interface.FollowDAO.php

Copyright (c) 2009-2011 Gina Trapani, Christoffer Viken

Follow Data Access Object Interface



Methods
-------

followExists
~~~~~~~~~~~~
* **@param** int $user_id
* **@param** int $follower_id
* **@param** str $network
* **@param** bool $is_active Whether or not relationship should be active only
* **@return** bool true if follow exists


Checks weather a given 'follow' exist in storage.

.. code-block:: php5

    <?php
        public function followExists($user_id, $follower_id, $network, $is_active=false);


update
~~~~~~
* **@param** int $user_id
* **@param** int $follower_id
* **@param** str $network
* **@param** string $debug_api_call
* **@return** int update count


Updates 'last seen' in storage.

.. code-block:: php5

    <?php
        public function update($user_id, $follower_id, $network, $debug_api_call = '');


deactivate
~~~~~~~~~~
* **@param** int $user_id
* **@param** int $follower_id
* **@param** str $network
* **@param** string $debug_api_call
* **@return** int update count


Deactivates a 'follow' in storage.

.. code-block:: php5

    <?php
        public function deactivate($user_id, $follower_id, $network, $debug_api_call = '');


insert
~~~~~~
* **@param** int $user_id
* **@param** int $follower_id
* **@param** str $network
* **@param** string $debug_api_call
* **@return** int insert count


Adds a 'follow' to storage

.. code-block:: php5

    <?php
        public function insert($user_id, $follower_id, $network, $debug_api_call = '');


countTotalFollowsWithErrors
~~~~~~~~~~~~~~~~~~~~~~~~~~~
* **@param** int $user_id
* **@param** str $network
* **@return** int with the number


Gets the number of follow(ers) with errors for a given user

.. code-block:: php5

    <?php
        public function countTotalFollowsWithErrors($user_id, $network);


countTotalFriendsWithErrors
~~~~~~~~~~~~~~~~~~~~~~~~~~~
* **@param** int $user_id
* **@param** str $network
* **@return** int with the number


Gets the number of friends with errors for a given user.

.. code-block:: php5

    <?php
        public function countTotalFriendsWithErrors($user_id, $network);


countTotalFollowsWithFullDetails
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
* **@param** int $user_id
* **@param** str $network
* **@return** int with the number


Gets the number of follows that have full datails.

.. code-block:: php5

    <?php
        public function countTotalFollowsWithFullDetails($user_id, $network);


countTotalFollowsProtected
~~~~~~~~~~~~~~~~~~~~~~~~~~
* **@param** int $user_id
* **@param** str $network
* **@return** int with the number


Gets the number of follows that are protected.
Includes inactive friendships in count.

.. code-block:: php5

    <?php
        public function countTotalFollowsProtected($user_id, $network);


countTotalFriends
~~~~~~~~~~~~~~~~~
* **@param** int $user_id
* **@param** str $network
* **@return** int with the number


Count the total number of friends in storage related to a user.
Originally counts all the friends, also the inactive ones,
this may be a subject to change.

.. code-block:: php5

    <?php
        public function countTotalFriends($user_id, $network);


countTotalFriendsProtected
~~~~~~~~~~~~~~~~~~~~~~~~~~
* **@param** int $user_id
* **@param** str $network
* **@return** int Total protected friends


Gets the number of friends that is protected.
Includes inactive friendships in count.

.. code-block:: php5

    <?php
        public function countTotalFriendsProtected($user_id, $network);


getUnloadedFollowerDetails
~~~~~~~~~~~~~~~~~~~~~~~~~~
* **@param** int $user_id
* **@param** str $network
* **@return** array Numbered keys, with arrays - named keys


Get a list of, friends without details in storage.

.. code-block:: php5

    <?php
        public function getUnloadedFollowerDetails($user_id, $network);


getStalestFriend
~~~~~~~~~~~~~~~~
* **@param** int $user_id
* **@param** str $network
* **@return** User object


Get the friend updated the longest time ago, if age is more than 1 day.

.. code-block:: php5

    <?php
        public function getStalestFriend($user_id, $network);


getOldestFollow
~~~~~~~~~~~~~~~
* **@param** str $network
* **@return** array Named keys


Gets the person in storage seen the longest time ago.

.. code-block:: php5

    <?php
        public function getOldestFollow($network);


getMostFollowedFollowers
~~~~~~~~~~~~~~~~~~~~~~~~
* **@param** int $user_id
* **@param** str $network
* **@param** int $count
* **@return** array Numbered keys, with arrays - named keys


Gets the followers with most followers.

.. code-block:: php5

    <?php
        public function getMostFollowedFollowers($user_id, $network, $count = 20);


getLeastLikelyFollowers
~~~~~~~~~~~~~~~~~~~~~~~
* **@param** int $user_id
* **@param** str $network
* **@param** int $count
* **@return** array - numbered keys, with arrays - named keys


Gets the followes with highest follower:friend count.

.. code-block:: php5

    <?php
        public function getLeastLikelyFollowers($user_id, $network, $count = 20);


getEarliestJoinerFollowers
~~~~~~~~~~~~~~~~~~~~~~~~~~
* **@param** int $user_id
* **@param** str $network
* **@param** int $count
* **@return** array - numbered keys, with arrays - named keys


Gets the followers with the earliest join date.

.. code-block:: php5

    <?php
        public function getEarliestJoinerFollowers($user_id, $network, $count = 20);


getMostActiveFollowees
~~~~~~~~~~~~~~~~~~~~~~
* **@param** int $user_id
* **@param** str $network
* **@param** int $count
* **@return** array - numbered keys, with arrays - named keys


Gets the friends with the highest tweet per day count.

.. code-block:: php5

    <?php
        public function getMostActiveFollowees($user_id, $network, $count = 20);


getFormerFollowees
~~~~~~~~~~~~~~~~~~
* **@param** int $user_id
* **@param** str $network
* **@param** int $count
* **@return** array - numbered keys, with arrays - named keys


Gets a list of inactive friends.

.. code-block:: php5

    <?php
        public function getFormerFollowees($user_id, $network, $count = 20);


getFormerFollowers
~~~~~~~~~~~~~~~~~~
* **@param** int $user_id
* **@param** str $network
* **@param** int $count
* **@return** array - numbered keys, with arrays - named keys


Gets a list of inactive followers.

.. code-block:: php5

    <?php
        public function getFormerFollowers($user_id, $network, $count = 20);


getLeastActiveFollowees
~~~~~~~~~~~~~~~~~~~~~~~
* **@param** int $user_id
* **@param** str $network
* **@param** int $count
* **@return** array - numbered keys, with arrays - named keys


Gets the followers with the lowest tweet-per-day ratio.

.. code-block:: php5

    <?php
        public function getLeastActiveFollowees($user_id, $network, $count = 20);


getMostFollowedFollowees
~~~~~~~~~~~~~~~~~~~~~~~~
* **@param** int $user_id
* **@param** str $network
* **@param** int $count
* **@return** array - numbered keys, with arrays - named keys


Gets the friends with the most followers

.. code-block:: php5

    <?php
        public function getMostFollowedFollowees($user_id, $network, $count = 20);


getMutualFriends
~~~~~~~~~~~~~~~~
* **@param** int $uid
* **@param** int $instance_uid
* **@param** str $network
* **@return** array - numbered keys, with arrays - named keys


Gets friends that the two inputed user IDs both follow.

.. code-block:: php5

    <?php
        public function getMutualFriends($uid, $instance_uid, $network);


getFriendsNotFollowingBack
~~~~~~~~~~~~~~~~~~~~~~~~~~
* **@param** int $uid
* **@param** str $network
* **@return** array - numbered keys, with arrays - named keys


Gets the friends that do not follow you back.

.. code-block:: php5

    <?php
        public function getFriendsNotFollowingBack($uid, $network);




