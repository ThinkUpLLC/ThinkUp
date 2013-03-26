<?php
/**
 *
 * ThinkUp/webapp/plugins/twitter/model/class.TwitterOAuthThinkUp.php
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
 */
/**
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 */
if (!class_exists('twitterOAuth')) {
    Loader::definePathConstants();
    require_once THINKUP_WEBAPP_PATH.'plugins/twitter/extlib/twitteroauth/twitteroauth.php';
}

class TwitterOAuthThinkUp extends TwitterOAuth {
    
    /**
     * Set proxy properties
     */
    function setProxy($_requires_proxy,$_proxy) {
        $setproxy = (bool)$_requires_proxy;
        if ($setproxy && (isset($_proxy)) && ($_proxy <> '')) {
            $this->requires_proxy = true;
            $this->proxy = $_proxy;
        } else {
            $this->requires_proxy = false;
        }
    }
}
