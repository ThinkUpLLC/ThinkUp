LinkDAO
=======

ThinkUp/webapp/_lib/model/interface.LinkDAO.php

Copyright (c) 2009-2011 Gina Trapani, Christoffer Viken

Link Data Access Object Interface



Methods
-------

insert
~~~~~~
* **@param** str $url
* **@param** str $expanded
* **@param** str $title
* **@param** int $post_id
* **@param** str $network
* **@param** bool $is_image
* **@return** int insert ID


Inserts a link into the database.

.. code-block:: php5

    <?php
        public function insert($url, $expanded, $title, $post_id, $network, $is_image = false );


saveExpandedURL
~~~~~~~~~~~~~~~
* **@param** str $url
* **@param** str $expanded
* **@param** str $title
* **@param** bool $is_image
* **@return** int Update count


Sets a expanded URL in storage.

.. code-block:: php5

    <?php
        public function saveExpandedURL($url, $expanded, $title = '', $is_image = false );


saveExpansionError
~~~~~~~~~~~~~~~~~~
* **@param** str $url
* **@param** str $error_text
* **@return** int insert ID


Stores a error message.

.. code-block:: php5

    <?php
        public function saveExpansionError($url, $error_text);


update
~~~~~~
* **@param** str $url
* **@param** str $expanded
* **@param** str $title
* **@param** int $post_id
* **@param** str $network
* **@param** bool $is_image
* **@return** int Update count


Updates a URL in storage.

.. code-block:: php5

    <?php
        public function update($url, $expanded, $title, $post_id, $network, $is_image = false );


getLinksByFriends
~~~~~~~~~~~~~~~~~
* **@param** int $user_id
* **@param** str $network
* **@param** int $count
* **@param** int $page
* **@return** array with Link objects


Get the links posted by a user's friends.

.. code-block:: php5

    <?php
        public function getLinksByFriends($user_id, $network, $count = 15, $page = 1);


getLinksByFavorites
~~~~~~~~~~~~~~~~~~~
* **@param** int $user_id
* **@param** str $network
* **@param** int $count
* **@param** int $page
* **@return** array with Link objects


Get the links in a user's favorites.

.. code-block:: php5

    <?php
        public function getLinksByFavorites($user_id, $network, $count = 15, $page = 1);


getPhotosByFriends
~~~~~~~~~~~~~~~~~~
* **@param** int $user_id
* **@param** str $network
* **@param** int $count
* **@param** int $page
* **@return** array numbered keys, with Link objects


Get the images posted by a user's friends.

.. code-block:: php5

    <?php
        public function getPhotosByFriends($user_id, $network, $count = 15, $page = 1);


getLinksToExpand
~~~~~~~~~~~~~~~~
* **@param** int $limit
* **@return** array with numbered keys, with strings


Gets a number of links that has not been expanded.
Non standard output - Scheduled for deprecation.

.. code-block:: php5

    <?php
        public function getLinksToExpand($limit = 1500);


getLinksToExpandByURL
~~~~~~~~~~~~~~~~~~~~~
* **@param** str $url
* **@return** array with numbered keys, with strings


Gets all links with short URL statring with a prefix.
Non standard output - Scheduled for deprecation.

.. code-block:: php5

    <?php
        public function getLinksToExpandByURL($prefix);


getLinkById
~~~~~~~~~~~
* **@param** int $id
* **@return** Link Object


Gets a link with a given ID

.. code-block:: php5

    <?php
        public function getLinkById($id);


getLinkByUrl
~~~~~~~~~~~~
* **@param** $url
* **@return** Link Object


Gets the link with spscified short URL

.. code-block:: php5

    <?php
        public function getLinkByUrl($url);




