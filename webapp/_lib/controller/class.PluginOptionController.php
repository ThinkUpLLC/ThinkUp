<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.PluginOptionController.php
 *
 * Copyright (c) 2009-2013 Mark Wilkie, Gina Trapani
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
 * Plugin Option Controller
 *
 * Controller to add and update plugin options
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Mark Wilkie <mwilkie[at]gmail[dot]com>
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */

class PluginOptionController extends ThinkUpAdminController {

    public function adminControl() {
        // set inital state
        $this->setContentType('application/json');
        $this->json = array('status' => 'failed');

        // validate CSRF token
        $this->validateCSRFToken();

        // verify we have a proper action and plugin id
        if (isset($_GET['action']) && $_GET['action'] == 'set_options') {
            if (isset($_GET['plugin_id'])
            && is_numeric( $_GET['plugin_id'] )
            && $this->isValidPluginId( $_GET['plugin_id'] ) ) {
                $this->setPluginOptions($_GET['plugin_id']);
            } else {
                // or fail
                $this->json['message'] = 'Bad plugin id defined for this request';
            }

        } else {
            // or fail
            $this->json['message'] = 'No action defined for this request';
        }
        $this->setJsonData($this->json);
        return $this->generateView();
    }

    /*
     * Sets plugin options in the data store.
     * @param int Plugin ID
     */
    public function setPluginOptions($plugin_id) {
        $plugin_dao = DAOFactory::getDAO('PluginDAO');
        $plugin_folder_name = $plugin_dao->getPluginFolder($plugin_id);
        $plugin_option_dao = DAOFactory::getDAO('PluginOptionDAO');
        $options = $plugin_option_dao->getOptions($plugin_folder_name);
        $updated_total = 0;
        $inserted = array();
        $deleted = 0;
        foreach ($_GET as $key => $value ) {
            //trim starting or ending whitespace
            $value = trim($value);
            if (preg_match('/^option_/', $key) ) {
                $name = preg_replace('/^option_/', '', $key);
                $id_name = "id_option_" . $name;
                if (isset($_GET[$id_name])) {
                    foreach ($options as $option) {
                        //error_log($option->option_name . ' '  . $name);
                        if ($option->option_name == $name) {
                            if ( $option->option_value != $value ) {
                                $id = preg_replace('/^id_option_/', '', $_GET[$id_name]);
                                if ($value == '') {
                                    $plugin_option_dao->deleteOption($id);
                                    $deleted++;
                                } else {
                                    if ($plugin_option_dao->updateOption($id, $name, $value) ) {
                                        $updated_total++;
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $insert_id = $plugin_option_dao->insertOption($plugin_id, $name, $value);
                    if (! $insert_id) {
                        $this->json_data['message'] = "Unable to add plugin option: $name";
                        return;
                    } else {
                        $inserted[$name] = $insert_id;
                        $updated_total++;
                    }
                }
            }
        }
        $this->json['results'] = array('updated' => $updated_total, 'inserted' => $inserted, 'deleted' => $deleted);
        $this->json['status'] = 'success';
    }

    /*
     * Checks if a plugin ID is valid
     * @param int A plugin ID
     * @return bool
     */
    public function isValidPluginId($plugin_id) {
        $plugin_option_dao = DAOFactory::getDAO('PluginDAO');
        return $plugin_option_dao->isValidPluginId($plugin_id);
    }
}