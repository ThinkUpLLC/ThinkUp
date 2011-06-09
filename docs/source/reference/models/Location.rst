Location
========

ThinkUp/webapp/_lib/model/class.Location.php

Copyright (c) 2009-2011 Ekansh Preet Singh, Mark Wilkie

Location Object


Properties
----------

id
~~



short_name
~~~~~~~~~~



full_name
~~~~~~~~~



latlng
~~~~~~





Methods
-------

__construct
~~~~~~~~~~~
* **@param** array $val Array of key/value pairs


Constructor

.. code-block:: php5

    <?php
        public function __construct($val) {
            $this->id = $val["id"];
            $this->short_name = $val["short_name"];
            $this->full_name = $val["full_name"];
            $this->latlng = $val["latlng"];
        }




