Option
======

ThinkUp/webapp/_lib/model/class.Option.php

Copyright (c) 2009-2011 Mark Wilkie

Option

A ThinkUp Option Class


Properties
----------

option_id
~~~~~~~~~



namespace
~~~~~~~~~



option_name
~~~~~~~~~~~



option_value
~~~~~~~~~~~~





Methods
-------

__construct
~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function __construct($args = null) {
            if(! is_null($args)) {
                if(isset($args['option_id'])) {
                    $this->option_id = $args['option_id'];
                }
                if(isset($args['namespace'])) {
                    $this->namespace = $args['namespace'];
                }
                if(isset($args['option_name'])) {
                    $this->option_name = $args['option_name'];
                }
                if(isset($args['option_value'])) {
                    $this->option_value = $args['option_value'];
                }
            }
        }




