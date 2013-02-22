<?php
/**
 *
 * ThinkUp/webapp/plugins/embedthread/controller/class.ThinkUpEmbedController.php
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
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 */
class ThinkUpEmbedController extends ThinkUpController {
    /**
     * Required query string parameters
     * @var array u = instance username, n = network
     */
    var $REQUIRED_PARAMS = array('p', 'n');
    /**
     *
     * @var boolean
     */
    var $is_missing_param = false;
    /**
     * Constructor
     * @param bool $session_started
     * @return ThinkUpEmbedController
     */
    public function __construct($session_started=false) {
        parent::__construct($session_started);
        foreach ($this->REQUIRED_PARAMS as $param) {
            if (!isset($_GET[$param]) || $_GET[$param] == '' ) {
                $this->addInfoMessage('No thread data to retrieve.');
                $this->is_missing_param = true;
            }
        }
    }
    /**
     * Generates the calling JavaScript to create embedded thread on calling page.
     * @return str JavaScript source
     */
    public function control() {
        Loader::definePathConstants();
        $this->setViewTemplate(THINKUP_WEBAPP_PATH.'_lib/view/api.embed.v1.embed.tpl');
        $this->setContentType('text/javascript');
        if (!$this->is_missing_param) {
            $this->addToView('post_id', $_GET['p']);
            $this->addToView('network', $_GET['n']);
        } else {
            $this->addErrorMessage('No ThinkUp thread specified.');
        }
        return $this->generateView();
    }
}