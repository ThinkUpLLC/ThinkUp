<?php
/**
 *
 * ThinkUp/webapp/plugins/hellothinkup/model/class.HelloThinkUpPlugin.php
 *
 * Copyright (c) 2009-2013 Gina Trapani, Guillaume Boudreau, Mark Wilkie
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
 */
/**
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @author Guillaume Boudreau <gboudreau[at]pommepause[dot]com>
 * @author Mark Wilkie <mark[at]bitterpill[dot]org>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani, Guillaume Boudreau, Mark Wilkie
 */
class HelloThinkUpPlugin extends Plugin implements CrawlerPlugin {

    public function __construct($vals=null) {
        parent::__construct($vals);
        $this->folder_name = 'hellothinkup';
        $this->addRequiredSetting('testname');
    }

    public function activate() {
    }

    public function deactivate() {
    }

    public function renderConfiguration($owner) {
        $controller = new HelloThinkUpPluginConfigurationController($owner, 'hellothinkup');
        return $controller->go();
    }

    public function crawl() {
        //echo "HelloThinkUp crawler plugin is running now.";
        /**
        * When crawling, make sure you only work on objects the current Owner has access to.
        *
        * Example:
        *
        *	$od = DAOFactory::getDAO('OwnerDAO');
        *	$oid = DAOFactory::getDAO('OwnerInstanceDAO');
        *
        * $current_owner = $od->getByEmail(Session::getLoggedInUser());
        *
        * $instances = [...]
        * foreach ($instances as $instance) {
        *	    if (!$oid->doesOwnerHaveAccessToInstance($current_owner, $instance)) {
        *	        // Owner doesn't have access to this instance; let's not crawl it.
        *	        continue;
        *	    }
        *	    [...]
        * }
        *
        */
    }

    public function renderInstanceConfiguration($owner, $instance_username, $instance_network) {
        return '';
    }
}