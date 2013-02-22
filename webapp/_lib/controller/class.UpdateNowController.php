<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.UpdateNowController.php
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
 * Update Now Controller
 *
 * Runs crawler from the web for the logged-in user and outputs logging into a text area.
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class UpdateNowController extends ThinkUpAuthAPIController {
    public function authControl() {
        $this->disableCaching(); // we don't want to cache the rss link with api key as it can get updated
        $this->setContentType('text/html; charset=UTF-8');
        $this->setPageTitle("ThinkUp Crawler");
        $this->setViewTemplate('crawler.updatenow.tpl');

        $this->addInfoMessage('<b>Hint</b>: You can set up ThinkUp to capture your data automatically. Visit '.
        'Settings &rarr; Account to find out how.', null, true );

        if (isset($_GET['log']) && $_GET['log'] == 'full') {
            $this->addToView('log', 'full');
        }
        return $this->generateView();
    }
}