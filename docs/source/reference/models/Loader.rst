Loader
======

ThinkUp/webapp/_lib/model/class.Loader.php

Copyright (c) 2009-2011 Dwi Widiastuti, Gina Trapani

Project-wide Loader

Implements lazy loading of ThinkUp classes by registering _autoload method in this class.


Properties
----------

lookup_path
~~~~~~~~~~~

Lookup path for classes and interfaces.

special_classes
~~~~~~~~~~~~~~~

Some classes have a special filename that doesn't follow the convention.
The value will be assigned inside setLookupPath method.



Methods
-------

register
~~~~~~~~
* **@param** array $additional_paths Array of strings; additional lookup path for classes
* **@return** bool true


Register current script to use lazy loading.

.. code-block:: php5

    <?php
        public static function register($paths = null) {
            self::setLookupPath($paths);
            return spl_autoload_register(array(__CLASS__, 'load' ));
        }


unregister
~~~~~~~~~~

Unregister the loader script.

.. code-block:: php5

    <?php
        public static function unregister() {
            self::$lookup_path = null;
            self::$special_classes = null;
            return spl_autoload_unregister( array(__CLASS__, 'load') );
        }


setLookupPath
~~~~~~~~~~~~~
* **@param** array $additional_paths Array of strings, additional lookup path for classes


Set lookup paths

.. code-block:: php5

    <?php
        private static function setLookupPath($additional_paths = null ) {
            // check two required named constants
            if ( !defined('THINKUP_ROOT_PATH') ) {
                define('THINKUP_ROOT_PATH', str_replace("\\",'/', dirname(dirname(dirname(dirname(__FILE__))))) .'/');
            }
    
            if ( !defined('THINKUP_WEBAPP_PATH') ) {
                define('THINKUP_WEBAPP_PATH', str_replace("\\",'/', dirname(dirname(dirname(__FILE__)))) .'/');
            }
    
            // set default lookup path for classes
            self::$lookup_path = array(
            THINKUP_WEBAPP_PATH . '_lib/model/',
            THINKUP_WEBAPP_PATH . '_lib/controller/',
            THINKUP_WEBAPP_PATH . '_lib/model/exceptions/'
            );
    
            // set default lookup path for special classes
            self::$special_classes = array(
            'Smarty' => THINKUP_WEBAPP_PATH . '_lib/extlib/Smarty-2.6.26/libs/Smarty.class.php'
            );
    
            if ( isset($additional_paths) && is_array($additional_paths)  ) {
                foreach ($additional_paths as $path) {
                    self::$lookup_path[] = $path;
                }
            }
        }


addPath
~~~~~~~
* **@param** str $path


Add another lookup path

.. code-block:: php5

    <?php
        public static function addPath($path) {
            if (!isset(self::$lookup_path)) {
                self::register();
            }
            self::$lookup_path[] = $path;
        }


getLookupPath
~~~~~~~~~~~~~
* **@return** array of lookup paths


Get lookup path

.. code-block:: php5

    <?php
        public static function getLookupPath() {
            return self::$lookup_path;
        }


getSpecialClasses
~~~~~~~~~~~~~~~~~
* **@return** array of special classes path files
* **@access** public


Get special classes files

.. code-block:: php5

    <?php
        public static function getSpecialClasses() {
            return self::$special_classes;
        }


load
~~~~
* **@param** str $class Class name
* **@return** bool true


The method registered to run on _autoload. When a class gets instantiated this method will be called to look up
the class file if the class is not present. The second instantiation of the same class wouldn't call this method.

.. code-block:: php5

    <?php
        public static function load($class) {
            // if class already in scope
            if ( class_exists($class, false) ) {
                return;
            }
    
            // if $class is a standard ThinkUp object or interface
            foreach ( self::$lookup_path as $path ) {
                $file_name = $path . 'class.' . $class . '.php';
                if ( file_exists( $file_name )) {
                    require_once $file_name;
                    return;
                }
                $file_name = $path . 'interface.' . $class . '.php';
                if ( file_exists( $file_name )) {
                    require_once $file_name;
                    return;
                }
                $file_name = $path . $class . '.php';
                if ( file_exists( $file_name )) {
                    require_once $file_name;
                    return;
                }
            }
    
            // if $class is special class filename
            if ( array_key_exists($class, self::$special_classes) ) {
                require_once self::$special_classes[$class];
                return;
            }
        }




