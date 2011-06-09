Plugin
======

ThinkUp/webapp/_lib/model/class.Plugin.php

Copyright (c) 2009-2011 Gina Trapani

Plugin

A ThinkUp plugin


Properties
----------

id
~~



name
~~~~



folder_name
~~~~~~~~~~~



description
~~~~~~~~~~~



author
~~~~~~



homepage
~~~~~~~~



version
~~~~~~~



is_active
~~~~~~~~~



icon
~~~~





Methods
-------

__construct
~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function __construct($val = null) {
            if(! $val) {
                return;
            }
            if (isset($val["id"])) {
                $this->id = $val["id"];
            }
            $this->name = $val["name"];
            $this->folder_name = $val["folder_name"];
            $this->description = $val['description'];
            $this->author = $val['author'];
            $this->homepage = $val['homepage'];
            $this->version = $val['version'];
            if (isset($val['icon'])) {
                $this->icon = $val['icon'];
            }
            if ($val['is_active'] == 1) {
                $this->is_active = true;
            } else {
                $this->is_active = false;
            }
        }




