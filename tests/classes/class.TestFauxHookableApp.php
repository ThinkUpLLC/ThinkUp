<?php
/**
 *
 * ThinkUp/tests/classes/class.TestFauxHookableApp.php
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
 * Faux TestPluginRegistrar class for TestOfPluginRegistrar test
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestFauxHookableApp extends PluginRegistrar {
    /**
     * For testing purposes
     */
    public function performAppFunction() {
        $this->emitObjectFunction('performAppFunction');
    }

    /**
     * For testing purposes
     * @param str $object_name Object name
     */
    public function registerPerformAppFunction($object_name) {
        $this->registerObjectFunction('performAppFunction', $object_name, 'performAppFunction');
    }
}
