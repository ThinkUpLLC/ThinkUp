UserErrorDAO
============

ThinkUp/webapp/_lib/model/interface.UserErrorDAO.php

Copyright (c) 2009-2011 Gina Trapani

UserError Data Access Object

Inserts user errors into the tu_user_error table.
Example user error text includes:
"Not found"
"Not authorized"
"User has been suspended."



Methods
-------

insertError
~~~~~~~~~~~
* **@param** int $id ID of the user that got the error
* **@param** int $error_code The HTTP error code (such as 404 not found or 403 not authorized)
* **@param** string $error_text Description of the error
* **@param** int $issued_to ID of the authorized user who got the error.
* **@param** str $network
* **@return** int Update row count


Insert a user error

.. code-block:: php5

    <?php
        public function insertError($id, $error_code, $error_text, $issued_to, $network);




