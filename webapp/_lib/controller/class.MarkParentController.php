<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.MarkParentController.php
 *
 * Copyright (c) 2009-2011 Gina Trapani
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
 * Mark Parent Controller
 *
 * Mark a post the parent of a reply.
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */

class MarkParentController extends ThinkUpAuthController {
    /**
     * Required query string parameters
     * @var array parend ID, orphan ID(s), parent/orphan post network, template, cache key
     */
    var $REQUIRED_PARAMS = array('pid', 'oid', 'n', 't', 'ck');

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

    public function authControl(){
        if (!$this->is_missing_param) {
            $template = $_GET["t"];
            $cache_key = $_GET["ck"];
            $pid = $_GET["pid"];
            $oid =  $_GET["oid"];
            $network = $_GET['n'];
            $config = Config::getInstance();

            $post_dao = DAOFactory::getDAO('PostDAO');
            foreach ($oid as $o) {
                if ( isset($_GET["fp"])) {
                    $result = $post_dao->assignParent($pid, $o, $network, $_GET["fp"]);
                } else {
                    $result = $post_dao->assignParent($pid, $o, $network);
                }
            }

            $s = new SmartyThinkUp();
            $s->clear_cache($template, $cache_key);
            if ($result > 0 ) {
                $this->addToView('result', 'Assignment successful.');
            } else {
                $this->addToView('result', 'No data was changed.');
            }
        }
        return $this->generateView();
    }
}