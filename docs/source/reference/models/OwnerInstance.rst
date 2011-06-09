OwnerInstance
=============

ThinkUp/webapp/_lib/model/class.OwnerInstance.php

Copyright (c) 2009-2011 Gina Trapani

OwnerInstance class

This class represents an owner instance


Properties
----------

owner_id
~~~~~~~~



instance_id
~~~~~~~~~~~





Methods
-------

__construct
~~~~~~~~~~~
* **@param** int owner id - optional
* **@param** int instance id - optional


Constructor

.. code-block:: php5

    <?php
        public function __construct($oid = null, $iid = null) {
            if($oid) { $this->owner_id = $oid; }
            if($iid) { $this->instance_id = $iid; }
        }




