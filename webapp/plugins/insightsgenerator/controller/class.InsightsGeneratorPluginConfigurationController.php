<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/controller/class.InsightsGeneratorPluginConfigurationController.php
 *
 * Copyright (c) 2012-2013 Gina Trapani
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
 * Insights Generator Plugin Configuration Controller
 *
 * Renders the Insights Generator plugin settings area.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

class InsightsGeneratorPluginConfigurationController extends PluginConfigurationController {

    public function __construct($owner) {
        parent::__construct($owner, 'insightsgenerator');
        $this->disableCaching();
        $this->owner = $owner;
    }

    public function authControl() {
        $config = Config::getInstance();
        Loader::definePathConstants();
        $this->setViewTemplate( THINKUP_WEBAPP_PATH.'plugins/insightsgenerator/view/account.index.tpl');
        $this->addToView('message', 'Pluggable plugin adds data into the insights stream.');
        $this->view_mgr->addHelp('insightsgenerator', 'contribute/developers/plugins/buildplugin');
        $installed_plugins = $this->getInstalledInsightPlugins();
        $this->addToView('installed_plugins', $installed_plugins);

        $mandrill_template = array('name' => 'mandrill_template', 'label' => 'Mandrill Template Name',
        'advanced' => true);
        $this->addPluginOption(self::FORM_TEXT_ELEMENT, $mandrill_template);

        return $this->generateView();
    }

    private function getInstalledInsightPlugins() {
        // Detect what plugins exist in the filesystem; parse their header comments for plugin metadata
        Loader::definePathConstants();
        $installed_plugins = array();
        foreach (glob(THINKUP_WEBAPP_PATH."plugins/insightsgenerator/insights/*.php") as $includefile) {
            $fhandle = fopen($includefile, "r");
            $contents = fread($fhandle, filesize($includefile));
            fclose($fhandle);
            $plugin_vals = $this->parseFileContents($contents);
            array_push($installed_plugins, $plugin_vals);
        }
        return $installed_plugins;
    }

    private function parseFileContents($contents) {
        $plugin_vals = array();
        $start = strpos($contents, '/*');
        $end = strpos($contents, '*/');
        if ($start > 0 && $end > $start) {
            $scriptData = substr($contents, $start + 2, $end - $start - 2);

            $scriptData = preg_split('/[\n\r]+/', $scriptData);
            foreach ($scriptData as $line) {
                $m = array();
                if (preg_match('/Plugin Name:(.*)/', $line, $m)) {
                    $plugin_vals['name'] = trim($m[1]);
                }
                if (preg_match('/Description:(.*)/', $line, $m)) {
                    $plugin_vals['description'] = trim($m[1]);
                }
                if (preg_match('/When:(.*)/', $line, $m)) {
                    $plugin_vals['when'] = trim($m[1]);
                }
            }
            return $plugin_vals;
        } else {
            return null;
        }
    }
}
