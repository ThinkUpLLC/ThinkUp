<?php
/**
 *
 * ThinkUp/webapp/_lib/model/interface.GenericPlugin.php
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
 * ThinkUp Plugin interface
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
interface GenericPlugin {
    /**
     * Render the configuration screen in the webapp.
     * @param Owner $owner
     * @return str HTML markup of configuration panel
     */
    public function renderConfiguration($owner);
    /**
     * Render an instance-specific configuration screen in the webapp.
     * @param Owner $owner
     * @param str $instance_username
     * @param str $instance_network
     * @return str HTML markup of configuration panel for a given instance
     */
    public function renderInstanceConfiguration($owner, $instance_username, $instance_network);
    /**
     * Activation callback, triggered when user deactivates plugin.
     */
    public function activate();
    /**
     * Deactivation callback, triggered when user deactivates plugin.
     */
    public function deactivate();
}