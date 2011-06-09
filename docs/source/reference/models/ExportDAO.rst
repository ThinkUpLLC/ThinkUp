ExportDAO
=========

ThinkUp/webapp/_lib/model/interface.ExportDAO.php

Copyright (c) 2011 Gina Trapani

Export Data Access Object interface



Methods
-------

createExportedPostsTable
~~~~~~~~~~~~~~~~~~~~~~~~
* **@return** bool Whether or not table was successfully created


Create a temporary table which matches the existing posts table to export (select *) into.

.. code-block:: php5

    <?php
        public function createExportedPostsTable();


doesExportedPostsTableExist
~~~~~~~~~~~~~~~~~~~~~~~~~~~
* **@return** bool Whether or not it exists


Check if temporary export table exists

.. code-block:: php5

    <?php
        public function doesExportedPostsTableExist();


dropExportedPostsTable
~~~~~~~~~~~~~~~~~~~~~~
* **@return** Whether or not table was succesfully dropped


Drop temporary export table.

.. code-block:: php5

    <?php
        public function dropExportedPostsTable();


createExportedFollowsTable
~~~~~~~~~~~~~~~~~~~~~~~~~~
* **@return** bool Whether or not table was successfully created


Create a temporary table which matches the existing follows table to export (select *) into.

.. code-block:: php5

    <?php
        public function createExportedFollowsTable();


doesExportedFollowsTableExist
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
* **@return** bool Whether or not it exists


Check if temporary exported follows table exists

.. code-block:: php5

    <?php
        public function doesExportedFollowsTableExist();


dropExportedFollowsTable
~~~~~~~~~~~~~~~~~~~~~~~~
* **@return** Whether or not table was succesfully dropped


Drop temporary exported follows table.

.. code-block:: php5

    <?php
        public function dropExportedFollowsTable();


exportPostsByServiceUser
~~~~~~~~~~~~~~~~~~~~~~~~
* **@param** str $username
* **@param** str $service
* **@return** int Number of posts exported


Copy the posts authored by a given service user from the core posts table into the temporary export table.

.. code-block:: php5

    <?php
        public function exportPostsByServiceUser($username, $service);


exportRepliesRetweetsOfPosts
~~~~~~~~~~~~~~~~~~~~~~~~~~~~
* **@param** array $posts_to_process Array of Post objects
* **@return** int Number of posts exported


Copy posts from core table to export table which reply to or retweet given posts.

.. code-block:: php5

    <?php
        public function exportRepliesRetweetsOfPosts($posts_to_process);


exportMentionsOfServiceUser
~~~~~~~~~~~~~~~~~~~~~~~~~~~
* **@param** str $username
* **@param** str $service
* **@return** int Number of posts exported


Copy the posts which mention the service user from the core posts table to the temporary export table.

.. code-block:: php5

    <?php
        public function exportMentionsOfServiceUser($username, $service);


exportPostsServiceUserRepliedTo
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
* **@param** str $username
* **@param** str $service
* **@return** int Number of posts exported


Copy the posts the user has replied to from the core posts table to the export table.

.. code-block:: php5

    <?php
        public function exportPostsServiceUserRepliedTo($username, $service);


exportFavoritesOfServiceUser
~~~~~~~~~~~~~~~~~~~~~~~~~~~~
* **@param** str $username
* **@param** str $service
* **@param** str $favorites_file
* **@return** int Number of posts exported


Copy the posts which the service user favorited from the core posts table to the temporary export table;
also export the favorites table data to file.

.. code-block:: php5

    <?php
        public function exportFavoritesOfServiceUser($user_id, $service, $favorites_file);


exportPostsLinksUsersToFile
~~~~~~~~~~~~~~~~~~~~~~~~~~~
* **@param** str $posts_file
* **@param** str $links_file
* **@param** str $users_file


Select all the posts in the export table and their links into specified files.

.. code-block:: php5

    <?php
        public function exportPostsLinksUsersToFile($posts_file, $links_file, $users_file);


getExportFields
~~~~~~~~~~~~~~~
* **@param** str $table_name
* **@param** str $prefix Adds a prefix like l.links to links table
* **@return** str Comma-delimited list of fields (without the id field)


Return a list of table fields, not including the auto-increment id field.

.. code-block:: php5

    <?php
        public function getExportFields($table_name, $prefix='');


exportFollowerCountToFile
~~~~~~~~~~~~~~~~~~~~~~~~~
* **@param** $user_id
* **@param** $network
* **@param** $file


Export daily follower count for a given user to file.

.. code-block:: php5

    <?php
        public function exportFollowerCountToFile($user_id, $network, $file);


exportFollowsUsersToFile
~~~~~~~~~~~~~~~~~~~~~~~~
* **@param** $user_id
* **@param** $network
* **@param** $follows_file
* **@param** $users_file


Export followers, followees, and user data to file.

.. code-block:: php5

    <?php
        public function exportFollowsUsersToFile($user_id, $network, $follows_file, $users_followers_file,
        $users_followees_file);


exportGeoToFile
~~~~~~~~~~~~~~~
* **@param** str $file


Export the entire encoded_locations table to file.

.. code-block:: php5

    <?php
        public function exportGeoToFile($file);




