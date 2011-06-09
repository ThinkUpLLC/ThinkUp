Profiler
========

ThinkUp/webapp/_lib/model/class.Profiler.php

Copyright (c) 2009-2011 Gina Trapani

Profiler


Properties
----------

instance
~~~~~~~~



logged_actions
~~~~~~~~~~~~~~



total_queries
~~~~~~~~~~~~~



dao_method
~~~~~~~~~~

Name of class and function about to call Profiler



Methods
-------

getInstance
~~~~~~~~~~~
* **@return** Profiler


Get singleton instance

.. code-block:: php5

    <?php
        public static function getInstance() {
            if (!isset(self::$instance)) {
                self::$instance = new Profiler();
            }
            return self::$instance;
        }


add
~~~
* **@param** float $time
* **@param** str $action


Add action

.. code-block:: php5

    <?php
        public function add($time, $action, $is_query=false, $num_rows=0 ) {
            if ($is_query) {
                $this->total_queries = $this->total_queries + 1;
            }
            $rounded_time = round($time, 3);
            $this->logged_actions[] =  array('time'=>number_format($rounded_time,3), 'action'=> trim($action),
            'num_rows'=>$num_rows, 'is_query'=>$is_query, 'dao_method'=>self::$dao_method);
            self::$dao_method = ''; //now that it's logged, set the dao_method to empty string
        }


setDAOMethod
~~~~~~~~~~~~
* **@param** $dao_method


Set DAO method member variable to display in log.

.. code-block:: php5

    <?php
        public static function setDAOMethod($dao_method) {
            self::$dao_method = $dao_method;
        }


getProfile
~~~~~~~~~~
* **@return** array


Get sorted profiled actions

.. code-block:: php5

    <?php
        public function getProfile() {
            sort($this->logged_actions);
            return array_reverse($this->logged_actions);
        }


isEnabled
~~~~~~~~~
* **@return** bool Whether the profiler is enabled


Check if Profiler is enabled; that is, if enabled in config file and running a web page.

.. code-block:: php5

    <?php
        public static function isEnabled() {
            if (isset($_SERVER['HTTP_HOST'])) {
                $config = Config::getInstance();
                return $config->getValue('enable_profiler');
            } else {
                return false;
            }
        }


clearLog
~~~~~~~~

Clear out all logged items, reset query count to 0

.. code-block:: php5

    <?php
        public function clearLog() {
            $keys = array_keys($this->logged_actions);
            foreach ($keys as $key) {
                unset($this->logged_actions[$key]);
            }
            $this->total_queries = 0;
        }




