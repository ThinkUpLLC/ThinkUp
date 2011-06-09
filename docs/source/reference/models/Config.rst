Config
======

ThinkUp/webapp/_lib/model/class.Config.php

Copyright (c) 2009-2011 Mark Wilkie, Gina Trapani

Configuration singleton

Singleton acess object for ThinkUp configuration values set in config.inc.php.
Never reference $THINKUP_CFG directly; always do it through this object.

Example of use:

<code>
/ get the Config singleton
$config = Config::getInstance();
/ get a value from it
$config->getValue('log_location');
</code>


Properties
----------

instance
~~~~~~~~



config
~~~~~~





Methods
-------

__construct
~~~~~~~~~~~
* **@param** array $vals Optional values to override file config
* **@return** Config


Private Constructor

.. code-block:: php5

    <?php
        private function __construct($vals = null) {
            if ($vals != null ) {
                $this->config = $vals;
            } else {
                Utils::defineConstants();
                if (file_exists(THINKUP_WEBAPP_PATH . 'config.inc.php')) {
                    require THINKUP_WEBAPP_PATH . 'config.inc.php';
                    $this->config = $THINKUP_CFG;
                    //set version info...
                    require THINKUP_WEBAPP_PATH . 'install/version.php';
                    $this->config['THINKUP_VERSION']  = $THINKUP_VERSION;
                    $this->config['THINKUP_VERSION_REQUIRED'] =
                    array('php' => $THINKUP_VERSION_REQUIRED['php'], 'mysql' => $THINKUP_VERSION_REQUIRED['mysql']);
                } else {
                    throw new Exception('ThinkUp\'s configuration file does not exist! Try <a href="'.THINKUP_BASE_URL.
                    'install/">installing ThinkUp.</a>');
                }
            }
        }


getInstance
~~~~~~~~~~~
* **@param** array $vals Optional values to override file config
* **@return** Config


Get the singleton instance of Config

.. code-block:: php5

    <?php
        public static function getInstance($vals = null) {
            if (!isset(self::$instance)) {
                self::$instance = new Config($vals);
            }
            return self::$instance;
        }


getValue
~~~~~~~~
* **@param** string   $key   key of the configuration key/value pair
* **@return** mixed    value of the configuration key/value pair


Get the configuration value

.. code-block:: php5

    <?php
        public function getValue($key) {
            // is this config value stored in the db?
            $db_value_config = AppConfig::getConfigValue($key);
            $value = null;
            if($db_value_config) {
                $option_dao = DAOFactory::getDAO("OptionDAO");
                $db_value = $option_dao->getOptionValue(OptionDAO::APP_OPTIONS, $key, true);
                $value =  $db_value ? $db_value : $db_value_config['default'];
                // convert db text booleans if needed
                if($value == 'false') {
                    $value = false;
                } else if ($value == 'true') {
                    $value = true;
                }
            } else {
                // if not a db config value, get from config file
                $value = isset($this->config[$key]) ? $this->config[$key] : null;
            }
            return $value;
        }


setValue
~~~~~~~~
* **@param** string $key
* **@param** string $value
* **@return** string $value


Provided only for use when overriding config.inc.php values in tests

.. code-block:: php5

    <?php
        public function setValue($key, $value) {
            $value = $this->config[$key] = $value;
            return $value;
        }


destroyInstance
~~~~~~~~~~~~~~~

Provided only for tests that want to kill Config object in tearDown()

.. code-block:: php5

    <?php
        public static function destroyInstance() {
            if (isset(self::$instance)) {
                self::$instance = null;
            }
        }


getValuesArray
~~~~~~~~~~~~~~

Provided for tests which expect an array

.. code-block:: php5

    <?php
        public function getValuesArray() {
            return $this->config;
        }


getGMTOffset
~~~~~~~~~~~~
* **@param** int $time The time to base it on, as anything strtotime() takes; leave blank for current time.
* **@return** int The GMT offset in hours.


Returns the GMT offset in hours based on the application's defined timezone.

If $time is given, gives the offset for that time; otherwise uses the current time.

.. code-block:: php5

    <?php
        public function getGMTOffset($time = 0) {
            $time = $time ? $time : 'now';
            $tz = ($this->getValue('timezone')==null)?date('e'):$this->getValue('timezone');
            return timezone_offset_get( new DateTimeZone($tz), new DateTime($time) ) / 3600;
        }




