MutexDAO
========

ThinkUp/webapp/_lib/model/interface.MutexDAO.php

Copyright (c) 2009-2011 Guillaume Boudreau

Mutex Data Access Object interface



Methods
-------

getMutex
~~~~~~~~
* **@param** string $name
* **@return** boolean True if the mutex was obtained, false if another thread was already holding this mutex.


Try to obtain a named mutex.

.. code-block:: php5

    <?php
        public function getMutex($name);


releaseMutex
~~~~~~~~~~~~
* **@param** string $name


Release a named mutex.

.. code-block:: php5

    <?php
        public function releaseMutex($name);


isMutexFree
~~~~~~~~~~~
* **@param** str $name
* **@return** bool


Determine if a mutex is free

.. code-block:: php5

    <?php
        public function isMutexFree($name);


isMutexUsed
~~~~~~~~~~~
* **@param** str $name
* **@return** bool


Determine if a mutex is in use.

.. code-block:: php5

    <?php
        public function isMutexUsed($name);




