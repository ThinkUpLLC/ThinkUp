<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Webapp.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
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
 * Webapp
 *
 * Singleton provides hooks for webapp plugins.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class Webapp extends PluginHook {
    /**
     *
     * @var Webapp
     */
    private static $instance;

    /**
     * @var array MenuItems
     */
    private $menuItems = array();

    /**
     *
     * @var string Name of the active plugin, defaults to "twitter"
     */
    private $activePlugin = "twitter";

    /**
     * Get the singleton instance of Webapp
     * @return Webapp
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new Webapp();
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
        return $this->activePlugin;
    }

    /**
     * Sets active plugin
     * @param string $ap
     */
    public function setActivePlugin($ap) {
        $this->activePlugin = $ap;
    }

    public function getDashboardMenu($instance) {
        $pobj = $this->getPluginObject($this->activePlugin);
        $p = new $pobj;
        if (method_exists($p, 'getDashboardMenu')) {
            return call_user_func(array($p, 'getDashboardMenu'), $instance);
        } else {
            throw new Exception("The ".get_class($p)." object does not have a getDashboardMenu method.");
        }
    }
    /**
     * Get individual MenuItem
     * @param str $menu_item_short_name
     * @param Instance $instance
     * @return MenuItem for instance, null if none available for given short name
     */
    public function getMenuItem($menu_item_short_name, $instance) {
        $menus = $this->getDashboardMenu($instance);
        foreach ($menus as $menu) {
            foreach ($menu->items as $menu_item) {
                if ($menu_item->short_name == $menu_item_short_name) {
                    return $menu_item;
                }
            }
        }
        return null;
    }
}
