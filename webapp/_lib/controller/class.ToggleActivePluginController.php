<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.ToggleActivePluginController.php
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
 * Toggle Active Plugin Controller
 * Activate or deactivat a plugin.
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2012 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class ToggleActivePluginController extends ThinkUpAdminController {
    /**
     * Required query string parameters
     * @var array pid = plugin ID, a = 1 or 0, active or inactive
     */
    var $REQUIRED_PARAMS = array('pid', 'a');

    /**
     *
     * @var boolean
     */
    var $is_missing_param = false;

    public function __construct($session_started=false) {
        parent::__construct($session_started);
        $this->setViewTemplate('session.toggle.tpl');
        foreach ($this->REQUIRED_PARAMS as $param) {
            if (!isset($_GET[$param]) || $_GET[$param] == '' ) {
                $this->addInfoMessage('Missing required parameters.');
                $this->is_missing_param = true;
            }
        }
    }

    public function adminControl(){
        if (!$this->is_missing_param) {
            // verify CSRF token
            $this->validateCSRFToken();
            $is_active = ($_GET["a"] != 1)?false:true;
            $plugin_dao = DAOFactory::getDAO('PluginDAO');
            $result = $plugin_dao->setActive($_GET["pid"], $is_active);
            if ($result > 0 ) {
                $plugin_folder = $plugin_dao->getPluginFolder($_GET["pid"]);
                $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
                try {
                    $plugin_class_name = $webapp_plugin_registrar->getPluginObject($plugin_folder);
                    $p = new $plugin_class_name;
                    if ($is_active) {
                        $p->activate();
                    } else {
                        $p->deactivate();
                    }
                } catch (Exception $e) {
                    //plugin object isn't registered, do nothing
                    //echo $e->getMessage();
                }
            }
            $this->addToView('result', $result);
            $this->view_mgr->clear_all_cache();
        }
        return $this->generateView();
    }
}