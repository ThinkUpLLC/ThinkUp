<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.UpdateNowController.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
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
 * Update Now Controller
 *
 * Runs crawler from the web for the logged-in user and outputs logging into a text area.
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class UpdateNowController extends ThinkUpAuthAPIController {

    public function authControl() {
        Utils::defineConstants();
        // set content type, as not all installations will use this charset as default
        $this->setContentType('text/html; charset=UTF-8');
        $this->setPageTitle("ThinkUp Crawler");
        $this->setViewTemplate('crawler.updatenow.tpl');
        $whichphp = @exec('which php');
        $php_path =  (!empty($whichphp))?$whichphp:'php';
        $rss_url = THINKUP_BASE_URL.'rss.php?'.ThinkUpAuthAPIController::getAuthParameters($this->getLoggedInUser());
        $this->addInfoMessage('<b>Hint</b><br />You can automate ThinkUp crawls by subscribing to '.
            '<strong><a href="'.$rss_url.'" target="_blank">this RSS feed</a></strong> '.
            'in your favorite RSS reader.<br /><br /> Alternately, use the command below to set up a cron job that '.
            'runs hourly to update your posts. (Be sure to change yourpassword to your real password!)<br /><br />'.
            '<code style="font-family:Courier">cd '.THINKUP_WEBAPP_PATH.
            'crawler/;export THINKUP_PASSWORD=yourpassword; '.$php_path.' crawl.php '.$this->getLoggedInUser().
            '</code>');
        if (isset($_GET['log']) && $_GET['log'] == 'full') {
            $this->addToView('log', 'full');
        }
        return $this->generateView();
    }
}