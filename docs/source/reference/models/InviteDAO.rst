InviteDAO
=========

ThinkUp/webapp/_lib/model/interface.InviteDAO.php

Copyright (c) 2011 Terrance Shepherd, Gina Trapani

Invite Data Access Object interface



Methods
-------

getInviteCode
~~~~~~~~~~~~~
* **@param** str $invitation_code
* **@return** Array of invite values


Gets invite code from tu_invite

.. code-block:: php5

    <?php
        public function getInviteCode($invite_code);


addInviteCode
~~~~~~~~~~~~~
* **@param** str $invite_code
* **@return** Updated row count


Adds the invitation code into database

.. code-block:: php5

    <?php
        public function addInviteCode($invite_code);


doesInviteExist
~~~~~~~~~~~~~~~
* **@param** str $invite_code
* **@return** bool


Checks if an invite exists

.. code-block:: php5

    <?php
        public function doesInviteExist($invite_code);


isInviteValid
~~~~~~~~~~~~~
* **@param** str $invite_code
* **@return** bool


Checks if the invite exists and has not expired (is less than 7 days old).

.. code-block:: php5

    <?php
        public function isInviteValid($invite_code);


deleteInviteCode
~~~~~~~~~~~~~~~~
* **@paran** str $invite_code
* **@return** int Updated row count


Deletes an invitation code after it has been used

.. code-block:: php5

    <?php
        public function deleteInviteCode($invite_code);




