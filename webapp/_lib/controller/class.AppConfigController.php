<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.AppConfigController.php
 *
 * Copyright (c) 2009-2013 Mark Wilkie
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
 * App Config Controller
 * Saves/Updates application-wide options/settings.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Mark Wilkie
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 *
 */
class AppConfigController extends ThinkUpAdminController {

    public function __construct($session_started=false) {
        parent::__construct($session_started);
    }

    public function adminControl() {
        $this->disableCaching();
        $option_dao = DAOFactory::getDAO("OptionDAO");

        if (isset($_POST['save'])) {
            // verify CSRF token
            $this->validateCSRFToken();
            $required = array();
            $config_values = array();
            $parent_config_values = array();
            $app_config = AppConfig::getConfigData();
            $values = 0;

            foreach($app_config as $key => $value) {
                $app_config[$key]['title'] =
                isset($app_config[$key]['title']) ? $app_config[$key]['title'] : $key;

                if ((isset($_POST[$key])  && $_POST[$key] != '') || $app_config[$key]['required']
                && ( (! isset($app_config[$key]['value']) || $app_config[$key]['value'] == '')
                && ! isset($required[$key]) ) ) {
                    $config_values[$key] = $app_config[$key];
                    if (isset($_POST[$key])) {
                        $config_values[$key]['value'] = $_POST[$key];
                        $values++;
                    }
                    $config_values[$key]['value'] = isset($_POST[$key]) ? $_POST[$key] : '';
                    if ( isset($app_config[$key]['match'])
                    && ! preg_match($app_config[$key]['match'], $config_values[$key]['value']) ) {
                        $required[$key] = $app_config[$key]['title'] .
                        ' should ' . $app_config[$key]['match_message'];
                    }

                    if (isset($app_config[$key]['dependencies'])) {
                        foreach( $config_values[$key]['dependencies'] as $dep_key ) {
                            $config_values[$dep_key]['value'] = isset($_POST[$dep_key]) ? $_POST[$dep_key] : '';
                            $value = $config_values[$dep_key]['value'];
                            if ( isset($app_config[$dep_key]['match'])
                            && ! preg_match($app_config[$dep_key]['match'], $value) ) {
                                $required[$dep_key] = $app_config[$dep_key]['title'] .
                                ' is required if ' . $app_config[$key]['title'] .
                                ' is set ' . $app_config[$dep_key]['match_message'];
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
                $saved = 0;
                $deleted = 0;
                foreach($config_values as $key => $config_value) {
                    $config = $option_dao->getOptionByName(OptionDAO::APP_OPTIONS, $key);
                    if ($config_value['value'] != '') {
                        if ($config) {
                            $option_dao->updateOption($config->option_id, $config_value['value']);
                        } else {
                            $option_dao->insertOption(OptionDAO::APP_OPTIONS, $key, $config_value['value']);
                        }
                        $saved++;
                    }
                }
                foreach($app_config as $key => $value) {
                    // delete the record if it exists and is empty in the post request
                    if (!isset($config_values[$key]['value']) || $config_values[$key]['value'] == '') {
                        $config = $option_dao->getOptionByName(OptionDAO::APP_OPTIONS, $key);
                        if ($config) {
                            $option_dao->deleteOption($config->option_id);
                            $deleted++;
                        }
                    }
                }
                $this->setJsonData( array( 'status' => 'success', 'saved' => $saved, 'deleted' => $deleted));
                SessionCache::unsetKey('selected_instance_network');
                SessionCache::unsetKey('selected_instance_username');
            }
        } else {
            $config_values = $option_dao->getOptions(OptionDAO::APP_OPTIONS);
            $app_config = AppConfig::getConfigData();
            $filtered_config_values = array();
            foreach($app_config as $key => $value) {
                if (isset($config_values[$key])) {
                    $filtered_config_values[$key] = $config_values[$key];
                }
            }
            $this->setJsonData( array( 'values' => $filtered_config_values, 'app_config_settings' => $app_config ));
        }
        return $this->generateView();
    }
}
