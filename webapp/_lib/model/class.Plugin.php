<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Plugin.php
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
 * Plugin
 *
 * A ThinkUp plugin
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
class Plugin {
    /*
     * @var int id
     */
    var $id;
    /*
     * @var str plugin name
     */
    var $name;
    /*
     * @var str folder name
     */
    var $folder_name;
    /*
     * @var istr description
     */
    var $description;
    /*
     * @var str author
     */
    var $author;
    /*
     * @var str homepage
     */
    var $homepage;
    /*
     * @var float version
     */
    var $version;
    /*
     * @var bool is active
     */
    var $is_active = false;
    /*
     * @var str plugin icon
     */
    var $icon;
    /**
     * Non-persistent array of plugin options which are required for the plugin to run.
     * @var array
     */
    var $required_settings;
    /**
     * Non-persistent hash of plugin options.
     * @var array
     */
    var $options_hash = null;

    public function __construct($val = null) {
        $this->required_settings = array();

        if (!$val) {
            return;
        }
        if (isset($val["id"])) {
            $this->id = $val["id"];
        }
        $this->name = $val["name"];
        $this->folder_name = $val["folder_name"];
        $this->description = $val['description'];
        $this->author = $val['author'];
        $this->homepage = $val['homepage'];
        $this->version = $val['version'];
        if (isset($val['icon'])) {
            $this->icon = $val['icon'];
        }
        if ($val['is_active'] == 1) {
            $this->is_active = true;
        } else {
            $this->is_active = false;
        }
    }

    /**
     * Add a setting name to the required settings array.
     * @param str $setting_name
     */
    public function addRequiredSetting($setting_name) {
        $this->required_settings[] = $setting_name;
    }

    /**
     * Return whether or not the plugin's required settings have been set in the options table
     * @return bool
     */
    public function isConfigured() {
        $this->options_hash = $this->getOptionsHash();
        foreach ($this->required_settings as $setting_name) {
            if (!isset($this->options_hash[$setting_name])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Retrieve this plugin's options from the data store.
     * @return array
     */
    public function getOptionsHash() {
        if (!isset($this->options_hash)) {
            $plugin_option_dao = DAOFactory::getDAO('PluginOptionDAO');
            if (isset($this->id)) {
                $this->options_hash  = $plugin_option_dao->getOptionsHashByPluginId($this->id);
            } else {
                $this->options_hash  = $plugin_option_dao->getOptionsHash($this->folder_name);
            }
        }
        return $this->options_hash;
    }
}
