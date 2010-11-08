<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.UpgradeToSnowflakeController.php
 *
 * Copyright (c) 2009-2010 Dwi Widiastuti, Gina Trapani
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
 * Upgrade to Snowflake Controller
 * This is a temporary controller that upgrades the database to support 64-bit Twitter post IDs. This controller
 * will be replaced by the permanent upgrade controller that will handle all database migrations in the future. It
 * will be deleted in a future beta.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class UpgradeToSnowflakeController extends ThinkUpAdminController {

    public function __construct($session_started=false) {
        parent::__construct($session_started);
        $this->setViewTemplate('install.upgrade.tpl');
    }

    public function adminControl(){
        $post_dao = new PostMySQLDAO();
        if (!isset($_POST['upgrade'])) {
            if ($post_dao->needsSnowflakeUpgrade()) {
                $this->addInfoMessage('In order to work correctly, your ThinkUp database needs an upgrade to suppport '.
                '<a href="http://engineering.twitter.com/2010/06/announcing-snowflake.html">Twitter\'s new 64-bit '.
                'post IDs</a>.  On large ThinkUp databases, this update can take a very long time.');
                $this->addToView('needs_upgrade', true);
            } else {
                $this->addSuccessMessage('Your database is up to date.');
            }
        } else {
            if ($post_dao->needsSnowflakeUpgrade()) {
                $changed = $post_dao->performSnowflakeUpgrade();
                $this->addSuccessMessage('Database updated! '.$changed.' rows affected. You may continue using '.
                'ThinkUp as usual.');
            } else {
                $this->addSuccessMessage('Your database is up to date.');
            }
        }
        return $this->generateView();
    }
}