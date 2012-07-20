<?php
/**
 *
 * ThinkUp/webapp/_lib/class.PluginRegistrar.php
 *
 * Copyright (c) 2009-2012 Gina Trapani
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
 * Plugin Registrar
 *
 * Provides hooks to register plugin objects in ThinkUp.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2012 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
abstract class PluginRegistrar {
    /**
     * Array that associates plugin folder shortname with the plugin object name
     * @var arr
     */
    private $plugins = array();
    /**
     * All the registered callbacks, an array of arrays where the index is the action name
     * @var arr $object_function_callbacks['trigger_name'][] = array('object_name', 'function_name');
     */
    private $object_function_callbacks = array();
    /**
     * Register an object function call
     * Note: This will cause a PHP fatal error if the object name does not exist
     * @param str $trigger Trigger keyword
     * @param str $object_name Object name
     * @param str $function_name Function name
     */
    protected function registerObjectFunction($trigger, $object_name, $function_name) {
        $this->object_function_callbacks[$trigger][] = array($object_name, $function_name);
    }
    /**
     * Run all object functions registered as callbacks.
     * @param str $trigger Trigger keyword
     * @param arr $params List of function parameters
     * @throws Exception When registered object doesn't have function
     */
    protected function emitObjectFunction($trigger, $params = array()) {
        if (isset($this->object_function_callbacks[$trigger])) {
            foreach ($this->object_function_callbacks[$trigger] as $callback) {
                if (method_exists($callback[0], $callback[1] )) {
                    $obj = new $callback[0];
                    //call_user_func($callback, $params);
                    call_user_func(array($obj, $callback[1]), $params);
                } else {
                    throw new Exception("The ".$callback[0]." object does not have a ".$callback[1]." function.");
                }
            }
        }
    }
    /**
     * Register an object plugin name.
     * @param str $shortname Short name for plugin, corresponds to plugin folder name (like "twitter")
     * @param str $objectname Object name (like "TwitterPlugin")
     */
    public function registerPlugin($short_name, $object_name) {
        $this->plugins[$short_name] = $object_name;
    }
    /**
     * Retrieve an object plugin name
     * @param str $shortname Short name for the plugin, corresponds to the plugin folder name (like "twitter")
     * @return str Object name
     */
    public function getPluginObject($shortname) {
        //Ugh googleplus/google+ hack
        $shortname = ($shortname=='googleplus')?'google+':$shortname;
        if (!isset($this->plugins[$shortname]) ) {
            throw new PluginNotFoundException($shortname);
        }
        return $this->plugins[$shortname];
    }
}