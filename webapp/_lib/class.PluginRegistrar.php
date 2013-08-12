<?php
/**
 *
 * ThinkUp/webapp/_lib/class.PluginRegistrar.php
 *
 * Copyright (c) 2009-2013 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkup.com).
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
 * @copyright 2009-2013 Gina Trapani
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
    protected $object_function_callbacks = array();
    /**
     * All registered callbacks that need to be run before insight generation
     */
    private $before_insight_generation = array();
    /**
     * All registered callbacks that need to be run after insight generation
     */
    private $after_insight_generation = array();
    /**
     * The insight generator plugin
     */
    private $insight_generator = array();

    /**
     * Register an object function call
     * Note: This will cause a PHP fatal error if the object name does not exist
     * @param str $trigger Trigger keyword
     * @param str $object_name Object name
     * @param str $function_name Function name
     * @param boolean $before_insights_generate=true true if this plugin should run before the insight generator plugin
     */
    protected function registerObjectFunction($trigger, $object_name, $function_name, $before_insights_generate=true) {
        // If the trigger type is crawl then we have to store them in the correct array for ordering later
        if ($trigger == 'crawl') {
            if ($object_name == 'InsightsGeneratorPlugin') {
                $this->insight_generator[$trigger][] = array($object_name, $function_name);
            } elseif ($before_insights_generate == true) {
                $this->before_insight_generation[$trigger][] = array($object_name, $function_name);
            } elseif ($before_insights_generate == false) {
                $this->after_insight_generation[$trigger][] = array($object_name, $function_name);
            }
        } else { // Any other type such as generateInsight don't need ordering
            $this->object_function_callbacks[$trigger][] = array($object_name, $function_name);
        }
    }
    /**
     * Run all object functions registered as callbacks.
     * @param str $trigger Trigger keyword
     * @param arr $params List of function parameters
     * @throws Exception When registered object doesn't have function
     */
    protected function emitObjectFunction($trigger, $params = array()) {
        // Order the call back arrays for crawler plugins relative to the insight generator
        self::orderPlugins($trigger);

        if (isset($this->object_function_callbacks[$trigger])) {
            foreach ($this->object_function_callbacks[$trigger] as $callback) {
                if (method_exists($callback[0], $callback[1] )) {
                    $obj = new $callback[0];
                    //call_user_func($callback, $params);
                    call_user_func_array(array($obj, $callback[1]), $params);
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
    /**
     * Orders the object_function_callback array relative to the insight generator plugin
     * @param  string $trigger the type of plugins you want to order currently only supports crawl
     */
    public function orderPlugins($trigger='crawl') {
        if ($trigger == 'crawl') {
            // Handle case where the insight generator plugin does not exist (only known case is during testing)
            if (sizeof($this->insight_generator['crawl']) != 1) {
                if (sizeof($this->before_insight_generation) > 0 && sizeof($this->after_insight_generation) > 0) {
                    $this->object_function_callbacks['crawl'] = array_merge($this->before_insight_generation['crawl'],
                    $this->after_insight_generation['crawl']);
                } elseif (sizeof($this->before_insight_generation) > 0) {
                    $this->object_function_callbacks['crawl'] = $this->before_insight_generation['crawl'];
                } elseif (sizeof($this->after_insight_generation) > 0) {
                    $this->object_function_callbacks['crawl'] = $this->after_insight_generation['crawl'];
                }
            } else {
                if (sizeof($this->before_insight_generation) > 0 && sizeof($this->after_insight_generation) > 0) {
                    $this->object_function_callbacks['crawl'] = array_merge($this->before_insight_generation['crawl'],
                    $this->insight_generator['crawl'], $this->after_insight_generation['crawl']);
                } elseif (sizeof($this->before_insight_generation) > 0) {
                    $this->object_function_callbacks['crawl'] = array_merge($this->before_insight_generation['crawl'],
                    $this->insight_generator['crawl']);
                } elseif (sizeof($this->after_insight_generation) > 0) {
                    $this->object_function_callbacks['crawl'] = array_merge($this->insight_generator['crawl'],
                    $this->after_insight_generation['crawl']);
                }
            }
        }
    }
}
