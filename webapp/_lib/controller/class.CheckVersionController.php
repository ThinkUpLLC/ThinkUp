<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.CheckVersionController.php
 *
 * Copyright (c) 2011-2012 Gina Trapani
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
 * Check Version Controller
 * Generates the JavaScript to display "New version available" message in the status bar.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2012 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class CheckVersionController extends ThinkUpAuthController {
    public function authControl() {
        $this->setContentType('text/javascript');
        $this->setViewTemplate('install.checkversion.tpl');
        $config = Config::getInstance();
        $this->addToView('is_opted_out_usage_stats', $config->getValue('is_opted_out_usage_stats'));
        return $this->generateView();
    }
}