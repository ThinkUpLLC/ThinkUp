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
     *
     * @var array MenuItem objects
     */
    private $post_detail_menus = null;
    /**
     *
     * @var array MenuItem objects
     */
    private $dashboard_menus = null;
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

    public function getDashboardMenu($instance) {
        if ($this->dashboard_menus === null) {
            $this->dashboard_menus = array();
            $plugin_class_name = $this->getPluginObject($this->active_plugin);
            $p = new $plugin_class_name;
            if ($p instanceof DashboardPlugin) {
                $this->dashboard_menus = $p->getDashboardMenuItems($instance);
            }
        }
        return $this->dashboard_menus;
    }

    public function getPostDetailMenu($post) {
        if ($this->post_detail_menus === null) {
            $this->post_detail_menus = array();
            //Get all active plugins
            $plugin_dao = DAOFactory::getDAO('PluginDAO');
            $this->active_plugins = $plugin_dao->getActivePlugins();
            //For each active plugin, check if getPostDetailMenu method exists
            foreach ($this->active_plugins as $plugin) {
                try {
                    $plugin_class_name = $this->getPluginObject($plugin->folder_name);
                } catch (PluginNotFoundException $e) {
                    //there's a plugin activated which doesn't exist in the source code, so deactivate it
                    $plugin_id = $plugin_dao->getPluginId($plugin->folder_name);
                    $plugin_dao->setActive($plugin_id, 0);
                }

                //if so, add to sidebar_menu
                $p = new $plugin_class_name;
                if ($p instanceof PostDetailPlugin) {
                    $menus = $p->getPostDetailMenuItems($post);
                    $this->post_detail_menus = array_merge($this->post_detail_menus, $menus);
                }
            }
        }
        return $this->post_detail_menus;
    }
    /**
     * Get individual Dashboard MenuItem
     * @param str $menu_item_short_name
     * @param Instance $instance
     * @return MenuItem for instance, null if none available for given short name
     */
    public function getDashboardMenuItem($menu_item_short_name, $instance) {
        if ($this->dashboard_menus === null) {
            $this->getDashboardMenu($instance);
        }
        if ( isset($this->dashboard_menus[$menu_item_short_name]) ) {
            return $this->dashboard_menus[$menu_item_short_name];
        } else {
            return null;
        }
    }
    /**
     * Get individual post detail MenuItem
     * @param str $menu_item_short_name
     * @param Post $post
     * @return MenuItem for instance, null if none available for given short name
     */
    public function getPostDetailMenuItem($menu_item_short_name, $post) {
        if ($this->post_detail_menus === null) {
            $this->getPostDetailMenu($post);
        }
        if ( isset($this->post_detail_menus[$menu_item_short_name]) ) {
            return $this->post_detail_menus[$menu_item_short_name];
        } else {
            return null;
        }
    }
}
