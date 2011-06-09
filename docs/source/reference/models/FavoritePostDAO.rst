FavoritePostDAO
===============

ThinkUp/webapp/_lib/model/interface.FavoritePostDAO.php

Copyright (c) 2009-2011 Gina Trapani, Amy Unruh

FavoritePost Data Access Object interface



Methods
-------

addFavorite
~~~~~~~~~~~
* **@param** int $favoriter_id
* **@param** array $vals
* **@return** int


Inserts the given post record (if it does not already exist), then creates a row in the favorites 'join' table
to store information about the 'favorited' relationship. $vals holds the parsed post information.

.. code-block:: php5

    <?php
        public function addFavorite($favoriter_id, $vals);


unFavorite
~~~~~~~~~~
* **@param** int $tid
* **@param** int $uid
* **@param** str $network
* **@return** int


'Unfavorites' a post with respect to a given user, by removing the relevant entry from
the favorites table.

.. code-block:: php5

    <?php
        public function unFavorite($tid, $uid, $network="twitter");


getAllFavoritePosts
~~~~~~~~~~~~~~~~~~~
* **@param** int $owner_id
* **@param** str $network
* **@param** int $count
* **@param** int $page
* **@return** array Posts with link object set


Wrapper function for getAllFavoritePostsByUserID. Supports pagination.

.. code-block:: php5

    <?php
        public function getAllFavoritePosts($owner_id, $network, $count, $page=1);


getAllFavoritePostsUpperBound
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
* **@param** int $owner_id
* **@param** str $network
* **@param** int $count
* **@param** int $ub
* **@return** array Posts with link object set


Wrapper function for getAllFavoritePostsByUserID. Takes an 'upper bound' argument ($ub)-- if set,
only posts with id < $ub are retrieved.

.. code-block:: php5

    <?php
        public function getAllFavoritePostsUpperBound($owner_id, $network, $count, $ub);


getAllFavoritePostsByUsername
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
* **@param** str $username
* **@param** str $network
* **@param** int $count
* **@return** array Posts with link object set


wrapper function for getAllFavoritePostsByUsernameOrderedBy

.. code-block:: php5

    <?php
        public function getAllFavoritePostsByUsername($username, $network, $count);


getAllFavoritePostsByUsernameIterator
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
* **@param** str $username
* **@param** str $network
* **@param** int $count
* **@return** PostIterator


iterator wrapper for getAllFavoritePostsByUsernameOrderedBy

.. code-block:: php5

    <?php
        public function getAllFavoritePostsByUsernameIterator($username, $network, $count=0);


getAllFavoritePostsIterator
~~~~~~~~~~~~~~~~~~~~~~~~~~~
* **@param** int $user_id
* **@param** str $network
* **@param** int $count
* **@return** PostIterator


iterator wrapper for getAllFavoritePostsByUserID

.. code-block:: php5

    <?php
        public function getAllFavoritePostsIterator($user_id, $network, $count);




