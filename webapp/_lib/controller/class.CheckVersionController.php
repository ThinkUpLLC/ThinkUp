<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.CheckVersionController.php
 *
 * Copyright (c) 2011-2013 Gina Trapani
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
 * Check Version Controller
 * Generates the JavaScript to display "New version available" message in the status bar.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2011-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class CheckVersionController extends ThinkUpAuthController {
    public function authControl() {
        $this->setContentType('text/javascript');
        $this->setViewTemplate('install.checkversion.tpl');
        $config = Config::getInstance();

        $is_in_beta = $config->getValue('is_subscribed_to_beta');
        $is_in_beta = isset($is_in_beta)?$is_in_beta:false;
        if ($is_in_beta) {
            $upgrade_checker_url = 'http://thinkup.com/version.php?channel=beta&';
        } else {
            $upgrade_checker_url = 'http://thinkup.com/version.php?';
        }

        $opt_out = $config->getValue('is_opted_out_usage_stats');
        $opt_out = isset($opt_out)?$opt_out:false;
        if ( $opt_out) {
            $upgrade_checker_url .= 'usage=n&';
        }
        $upgrade_checker_url .= 'v='.$config->getValue('THINKUP_VERSION');
        $this->addToView('checker_url', $upgrade_checker_url);
        return $this->generateView();
    }
}