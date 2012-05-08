<?php
/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/model/class.InsightPluginParent.php
 *
 * Copyright (c) 2012 Gina Trapani
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
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012 Gina Trapani
 * @author Gina Trapani <ginatrapani [at] gmail [dot] com>
 */
class InsightPluginParent {
    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        $this->logger = Logger::getInstance();
        $this->logger->setUsername($instance->network_username);
        $this->insight_date = new DateTime();
        $this->insight_date = $this->insight_date->format('Y-m-d');
        $this->insight_dao = DAOFactory::getDAO('InsightDAO');
    }

    public function renderConfiguration($owner) {
    }

    public function activate() {
    }

    public function deactivate() {
    }
}