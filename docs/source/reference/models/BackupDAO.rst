BackupDAO
=========

ThinkUp/webapp/_lib/model/interface.BackupDAO.php

Copyright (c) 2009-2011 Mark Wilkie

Backup Data Access Object Interface



Methods
-------

export
~~~~~~
* **@param** $str Backup File (optional)
* **@return** $str Path to backup file


Export database to tmp dir

.. code-block:: php5

    <?php
        public function export($backup_file = null);


import
~~~~~~
* **@return** boolean tru if suceeds


Import database zip file
@ str Import zip file

.. code-block:: php5

    <?php
        public function import($zipfile);




