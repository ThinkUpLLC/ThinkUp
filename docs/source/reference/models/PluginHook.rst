PluginHook
==========

ThinkUp/webapp/_lib/model/class.PluginHook.php

Copyright (c) 2009-2011 Gina Trapani

Plugin Hook

Provides hooks to register plugin objects in ThinkUp.


Properties
----------

plugins
~~~~~~~

Array that associates plugin folder shortname with the plugin object name

object_method_callbacks
~~~~~~~~~~~~~~~~~~~~~~~

All the registered callbacks, an array of arrays where the index is the action name



Methods
-------

registerObjectMethod
~~~~~~~~~~~~~~~~~~~~
* **@param** str $trigger Trigger keyword
* **@param** str $o Object name
* **@param** str $m Method name


Register an object method call
Note: This will cause a PHP fatal error if the object name does not exist

.. code-block:: php5

    <?php
        protected function registerObjectMethod($trigger, $o, $m) {
            $obj = new $o;
            $this->object_method_callbacks[$trigger][] = array($o, $m);
        }


emitObjectMethod
~~~~~~~~~~~~~~~~
* **@param** str $trigger Trigger keyword
* **@param** array $params List of method parameters


Run all object methods registered as callbacks

.. code-block:: php5

    <?php
        protected function emitObjectMethod($trigger, $params = array()) {
            foreach ($this->object_method_callbacks[$trigger] as $callback) {
                if (method_exists($callback[0], $callback[1] )) {
                    $o = new $callback[0];
                    //call_user_func($callback, $params);
                    call_user_func(array($o, $callback[1]), $params);
                } else {
                    throw new Exception("The ".$callback[0]." object does not have a ".$callback[1]." method.");
                }
            }
        }


registerPlugin
~~~~~~~~~~~~~~
* **@param** str $shortname Short name for plugin, corresponds to plugin folder name (like "twitter")
* **@param** str $objectname Object name (like "TwitterPlugin")


Register an object plugin name.

.. code-block:: php5

    <?php
        public function registerPlugin($short_name, $object_name) {
            $this->plugins[$short_name] = $object_name;
        }


getPluginObject
~~~~~~~~~~~~~~~
* **@param** str $shortname Short name for the plugin, corresponds to the plugin folder name (like "twitter")
* **@return** str Object name


Retrieve an object plugin name

.. code-block:: php5

    <?php
        public function getPluginObject($shortname) {
            if (!isset($this->plugins[$shortname]) ) {
                throw new Exception("No plugin object defined for: " . $shortname);
            }
            return $this->plugins[$shortname];
        }




