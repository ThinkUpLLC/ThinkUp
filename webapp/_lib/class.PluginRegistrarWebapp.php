<?php
/**
 *
 * ThinkUp/PluginRegistrarWebapp/_lib/class.PluginRegistrarWebapp.php
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
 * PluginRegistrarWebapp
 *
 * Singleton provides hooks for PluginRegistrarWebapp plugins.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class PluginRegistrarWebapp extends PluginRegistrar {
    /**
     *
     * @var PluginRegistrarWebapp
     */
    private static $instance;
    /**
     *
     * @var string Name of the active plugin, defaults to "twitter"
     */
    private $active_plugin = "twitter";
    /**
     *
     * @var array Plugin objects
     */
    private $active_plugins = null;
    /**
     * Get the singleton instance of PluginRegistrarWebapp
     * @return PluginRegistrarWebapp
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new PluginRegistrarWebapp();
        }
        return self::$instance;
    }
    /**
     * Provided only for tests that want to kill object in tearDown()
     */
    public static function destroyInstance() {
        if (isset(self::$instance)) {
            self::$instance = null;
        }
    }
    /**
     * Returns active plugin
     * @return str Name of active plugin (like "twitter" or "facebook")
     */
    public function getActivePlugin() {
        return $this->active_plugin;
    }
    /**
     * Sets active plugin
     * @param string $ap
     */
    public function setActivePlugin($ap) {
        $this->active_plugin = $ap;
    }
}
