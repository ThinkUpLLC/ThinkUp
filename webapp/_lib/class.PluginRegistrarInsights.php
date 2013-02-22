<?php
/**
 *
 * ThinkUp/webapp/_lib/class.PluginRegistrarInsights.php
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
 *
 * PluginRegistrarInsights
 * Singleton provides hooks for insight plugins.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012-2013 Gina Trapani
 * @author Gina Trapani
 */
class PluginRegistrarInsights extends PluginRegistrar {
    /**
     * Singleton instance of PluginRegistrarInsights object.
     * @var PluginRegistrarInsights
     */
    private static $instance;
    /**
     * Get the singleton instance of PluginRegistrarInsights
     * @return PluginRegistrarInsights
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new PluginRegistrarInsights();
        }
        return self::$instance;
    }
    /**
     * Provided only for tests that want to kill object in tearDown()
     * @return void
     */
    public static function destroyInstance() {
        if (isset(self::$instance)) {
            self::$instance = null;
        }
    }
    /**
     * Runs the generateInsight function on all registered plugins.
     * @param Instance $instance
     * @param arr last week of Post objects
     * @param int $number_days Number of days to backfill with insights
     * @throws UnauthorizedUserException
     * @return void
     */
    public function runRegisteredPluginsInsightGeneration(Instance $instance, $last_week_of_posts, $number_days) {
        if (!Session::isLoggedIn() ) {
            throw new UnauthorizedUserException('You need a valid session to generate insights.');
        }
        $this->emitObjectFunction('generateInsight', array($instance, $last_week_of_posts, $number_days));
    }
    /**
     * Register insight plugin.
     * @param str $object_name Name of insight plugin object
     * @return void
     */
    public function registerInsightPlugin($object_name) {
        $this->registerObjectFunction('generateInsight', $object_name, 'generateInsight');
    }
}
