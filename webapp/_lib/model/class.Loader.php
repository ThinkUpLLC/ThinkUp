<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Loader.php
 *
 * Copyright (c) 2009-2010 Dwi Widiastuti, Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 *
 * Project-wide Loader
 *
 * Implements lazy loading of ThinkUp classes by registering _autoload method in this class.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Dwi Widiastuti, Gina Trapani
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class Loader {

    /**
     * Lookup path for classes and interfaces.
     *
     * @var array
     */
    private static $lookup_path;

    /**
     * Some classes have a special filename that doesn't follow the convention.
     * The value will be assigned inside setLookupPath method.
     */
    private static $special_classes;

    /**
     * Register current script to use lazy loading.
     * @param array $additional_paths Array of strings; additional lookup path for classes
     * @return bool true
     */
    public static function register($paths = null) {
        self::setLookupPath($paths);
        return spl_autoload_register(array(__CLASS__, 'load' ));
    }

    /**
     * Unregister the loader script.
     */
    public static function unregister() {
        self::$lookup_path = null;
        self::$special_classes = null;
        return spl_autoload_unregister( array(__CLASS__, 'load') );
    }

    /**
     * Set lookup paths
     * @param array $additional_paths Array of strings, additional lookup path for classes
     */
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

    /**
     * Add another lookup path
     * @param str $path
     */
    public static function addPath($path) {
        if (!isset(self::$lookup_path)) {
            self::register();
        }
        self::$lookup_path[] = $path;
    }

    /**
     * Get lookup path
     * @return array of lookup paths
     */
    public function getLookupPath() {
        return self::$lookup_path;
    }

    /**
     * Get special classes files
     * @return array of special classes path files
     * @access public
     */
    public function getSpecialClasses() {
        return self::$special_classes;
    }

    /**
     * The method registered to run on _autoload. When a class gets instantiated this method will be called to look up
     * the class file if the class is not present. The second instantiation of the same class wouldn't call this method.
     *
     * @param str $class Class name
     * @return bool true
     */
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
            $file_name = $path . 'class.'. $class . '.deprecated.php';
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
}
