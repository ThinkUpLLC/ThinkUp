SessionCache
============

ThinkUp/webapp/_lib/model/class.SessionCache.php

Copyright (c) 2011 Gina Trapani

SessionCache

PHP $_SESSION accessor.



Methods
-------

put
~~~
* **@param** str $key
* **@param** str $value


Put a value in ThinkUp's $_SESSION key.

.. code-block:: php5

    <?php
        public static function put($key, $value) {
            $config = Config::getInstance();
            $_SESSION[$config->getValue('source_root_path')][$key] = $value;
        }


get
~~~
* **@param** str $key
* **@return** mixed Value


Get a value from ThinkUp's $_SESSION.

.. code-block:: php5

    <?php
        public static function get($key) {
            $config = Config::getInstance();
            if (self::isKeySet($key)) {
                return $_SESSION[$config->getValue('source_root_path')][$key];
            } else {
                return null;
            }
        }


isKeySet
~~~~~~~~
* **@param** str $key
* **@return** bool


Check if a key in ThinkUp's $_SESSION has a value set.

.. code-block:: php5

    <?php
        public static function isKeySet($key) {
            $config = Config::getInstance();
            return isset($_SESSION[$config->getValue('source_root_path')][$key]);
        }


unsetKey
~~~~~~~~
* **@param** str $key


Unset key's value in ThinkUp's $_SESSION

.. code-block:: php5

    <?php
        public static function unsetKey($key) {
            $config = Config::getInstance();
            unset($_SESSION[$config->getValue('source_root_path')][$key]);
        }




