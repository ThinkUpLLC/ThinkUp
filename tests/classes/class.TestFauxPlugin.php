<?php
/**
 *
 * ThinkUp/tests/classes/class.TestFauxPlugin.php
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
 * Test Faux Plugin for TestOfPluginRegistrar
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestFauxPlugin implements TestAppPlugin {
    public function activate() {
    }

    public function deactivate() {
    }

    /**
     * For testing purposes
     */
    public function performAppFunction() {
        //do something here
    }

    /**
     * For testing purposes
     */
    public function renderConfiguration($owner) {
        return "this is my configuration screen HTML";
    }

    /**
     * For testing purposes
     */
    public function renderInstanceConfiguration($owner, $instance_username, $instance_network) {
        return "this is my instance-specific configuration screen HTML";
    }

}

/**
 * Test Faux Plugin without the required method for TestOfPluginRegistrar
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestFauxPluginOne {
}