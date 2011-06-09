AppConfig
=========

ThinkUp/webapp/_lib/model/class.AppConfig.php

Copyright (c) 2009-2011 Mark Wilkie

Application config options defaults, and validation settings.
class.Config.php will use to determine what configs to pull from the database, and
class.AppConfigController will use config data for input validation


Properties
----------

config_data
~~~~~~~~~~~

Currently there's a bug with checkboxes which have a default value of true. When you uncheck the box,
and save the form, no value gets submitted for the checkbox, so the false value doesn't get saved.
As such, right now, checkbox default values must be false.
Therefore, for now, making this option 'is_api_disabled' instead of 'is_api_enabled.'
@TODO: Once that bug is fixed, change this to Enable JSON API with default value true.



Methods
-------

getConfigData
~~~~~~~~~~~~~
* **@return** array Application settings configuration and validation data array/hash


Getter for db config data array

.. code-block:: php5

    <?php
                public static function getConfigData() {
                    return self::$config_data;
                }


getConfigValue
~~~~~~~~~~~~~~
* **@param** str Key for apllication value
* **@return** array Application settings configuration and validation data array/hash


Getter for db config data value

.. code-block:: php5

    <?php
                public static function getConfigValue($key) {
                    $value = isset(self::$config_data[$key] ) ? self::$config_data[$key] : false;
                    return $value;
                }




