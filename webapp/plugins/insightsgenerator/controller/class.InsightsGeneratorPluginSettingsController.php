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

class InsightsGeneratorPluginSettingsController extends ThinkUpAuthController {

	public function __construct($session_started=false) {
        parent::__construct($session_started);
    }

    public function authControl() {
        $config = Config::getInstance();
        Loader::definePathConstants();
       
		$this->disableCaching();
        $installed_plugins = $this->getInstalledInsightPlugins();
        $this->addToView('installed_plugins', $installed_plugins);
	
		$pluginoption_dao = DAOFactory::getDAO("PluginOptionDAO");
		$insightsgenerator_config = array();
		/*Create a checkbox config option for each insights type*/
		foreach($installed_plugins as $installed_plugin) {
			$visibility_field= array(
				'type' => 'checkbox',
				'title' => 'Hide '.$installed_plugin['name'],
				'required' => false,
				'default' => 'false',
				'match' => '/^(true|false)$/',
				'match_message' => 'Must be true or false'
				);
			$insightsgenerator_config['hide_'.$installed_plugin['filename']] = $visibility_field;
		}
		if (isset($_POST['save'])) {
            // verify CSRF token
            $this->validateCSRFToken();
            $required = array();
            $config_values = array();
            $values = 0;

            foreach($insightsgenerator_config as $key => $value) {
                $insightsgenerator_config[$key]['title'] =
                isset($insightsgenerator_config[$key]['title']) ? $insightsgenerator_config[$key]['title'] : $key;
                if ((isset($_POST[$key])  && $_POST[$key] != '') || $insightsgenerator_config[$key]['required']
                && ( (! isset($insightsgenerator_config[$key]['value']) || $insightsgenerator_config[$key]['value'] == '')
                && ! isset($required[$key]) ) ) {
                    $config_values[$key] = $insightsgenerator_config[$key];
                    if (isset($_POST[$key])) {
                        $config_values[$key]['value'] = $_POST[$key];
                        $values++;
                    }
                    $config_values[$key]['value'] = isset($_POST[$key]) ? $_POST[$key] : '';
                    if ( isset($insightsgenerator_config[$key]['match'])
                    && ! preg_match($insightsgenerator_config[$key]['match'], $config_values[$key]['value']) ) {
                        $required[$key] = $insightsgenerator_config[$key]['title'] .
                        ' should ' . $insightsgenerator_config[$key]['match_message'];
                    }

                    if (isset($insightsgenerator_config[$key]['dependencies'])) {
                        foreach( $config_values[$key]['dependencies'] as $dep_key ) {
                            $config_values[$dep_key]['value'] = isset($_POST[$dep_key]) ? $_POST[$dep_key] : '';
                            $value = $config_values[$dep_key]['value'];
                            if ( isset($insightsgenerator_config[$dep_key]['match'])
                            && ! preg_match($insightsgenerator_config[$dep_key]['match'], $value) ) {
                                $required[$dep_key] = $insightsgenerator_config[$dep_key]['title'] .
                                ' is required if ' . $insightsgenerator_config[$key]['title'] .
                                ' is set ' . $insightsgenerator_config[$dep_key]['match_message'];
                            }
                        }
                    }
                }
                // strip magic quotes if enabled...
                if (get_magic_quotes_gpc() && isset( $config_values[$key]['value'] )) {
                    $config_values[$key]['value'] = stripslashes($config_values[$key]['value']);
                }
            }

            if (count($required) > 0) {
                $this->setJsonData( array( 'status' => 'failed', 'required' => $required));
            } else {
                // save our data
				$this->view_mgr->clear_cache('.htinsights.tpl');
                $saved = 0;
                $deleted = 0;
				$plugin_dao = DAOFactory::getDAO('PluginDAO');
		        $plugin_id = $plugin_dao->getPluginId('insightsgenerator');
                foreach($config_values as $key => $config_value) {
					
                    $config = $pluginoption_dao->getOptionByName('insightsgenerator', $key);
                    if ($config_value['value'] != '') {
                        if ($config) {
                            $pluginoption_dao->updateOption($config->option_id, $key, $config_value['value']);
                        } else {
                            $pluginoption_dao->insertOption($plugin_id, $key, $config_value['value']);
                        }
                        $saved++;
                    }
                }
                foreach($insightsgenerator_config as $key => $value) {
                    // delete the record if it exists and is empty in the post request
                    if (!isset($config_values[$key]['value']) || $config_values[$key]['value'] == '') {
                        $config = $pluginoption_dao->getOptionByName('insightsgenerator', $key);
                        if ($config) {
                            $pluginoption_dao->deleteOption($config->option_id);
                            $deleted++;
                        }
                    }
                }
                $this->setJsonData( array( 'status' => 'success', 'saved' => $saved, 'deleted' => $deleted));
                SessionCache::unsetKey('selected_instance_network');
                SessionCache::unsetKey('selected_instance_username');
            }
        } else {
            $config_values = $pluginoption_dao->getOptionsHash("insightsgenerator");
            $filtered_config_values = array();
            foreach($insightsgenerator_config as $key => $value) {
                if (isset($config_values[$key])) {
                    $filtered_config_values[$key] = $config_values[$key];
                }
            }
            $this->setJsonData( array( 'values' => $filtered_config_values, 'insightsgenerator_config_settings' => $insightsgenerator_config ));
        }
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
			if($plugin_vals) {
				$filename = substr($includefile,strripos($includefile,'/')+1);
				$plugin_vals['filename'] = substr($filename,0,strpos($filename,'.'));
			}
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
            }
            return $plugin_vals;
        } else {
            return null;
        }
    }
}