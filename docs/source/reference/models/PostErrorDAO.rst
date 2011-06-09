PostErrorDAO
============

ThinkUp/webapp/_lib/model/interface.PostErrorDAO.php

Copyright (c) 2009-2011 Gina Trapani

PostError Data Access Object

Inserts post errors into the tu_post_error table.
Example post error text includes:
"No status found with that ID."
"Sorry, you are not authorized to see this status."
"This account is currently suspended."



Methods
-------

insertError
~~~~~~~~~~~
* **@param** int $post_id ID of the post that got the error
* **@param** int $error_code The HTTP error code (such as 404 not found or 403 not authorized)
* **@param** string $error_text Description of the error
* **@param** int $issued_to ID of the authorized user who got the error.


Insert a post error

.. code-block:: php5

    <?php
        public function insertError($post_id, $network, $error_code, $error_text, $issued_to);




