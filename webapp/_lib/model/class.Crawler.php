<?php
/**
 *
 * ThinkUp/webapp/_lib/model/class.Crawler.php
 *
 * Copyright (c) 2009-2012 Gina Trapani, Guillaume Boudreau
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
 * Crawler
 *
 * Singleton provides hooks for crawler plugins.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2012 Gina Trapani, Guillaume Boudreau
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class Crawler extends PluginHook {

    /**
     *
     * @var Crawler
     */
    const GLOBAL_MUTEX = 'crawler';

    /**
     *
     * @var Crawler
     */
    private static $instance;

    /**
     * Get the singleton instance of Crawler
     * @return Crawler
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new Crawler();
        }
        return self::$instance;
    }

    /**
     * Provided only for tests that want to kill object in tearDown()
     */
    public static function destroyInstance() {
        if (isset(self::$instance)) {
            self::$instance = null;
        }
    }

    /**
     * Gets called when crawler runs.
     *
     * About crawler exclusivity (mutex usage):
     * When launched by an admin, no other user, admin or not, will be able to launch a crawl until this one is done.
     * When launched by a non-admin, we first check that no admin run is under way, and if that's the case,
     * we launch a crawl for the current user only.
     * No user will be able to launch two crawls in parallel, but different non-admin users crawls can run in parallel.
     */
    public function crawl() {
        if (!Session::isLoggedIn() ) {
            throw new UnauthorizedUserException('You need a valid session to launch the crawler.');
        }
        $mutex_dao = DAOFactory::getDAO('MutexDAO');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail(Session::getLoggedInUser());
        if (empty($owner)) {
            throw new UnauthorizedUserException('You need a valid session to launch the crawler.');
        }

        // are we in an upgrading state
        if (UpgradeDatabaseController::isUpgrading(true, 'Crawler')) {
            throw new InstallerException("ThinkUp needs a database migration, so we are unable to run the crawler.");
        }

        $global_mutex_name = self::GLOBAL_MUTEX;

        // Everyone needs to check the global mutex
        $lock_successful = $mutex_dao->getMutex($global_mutex_name);

        if ($lock_successful) {
            // Global mutex was free, which means no admin crawls are under way
            if ($owner->is_admin) {
                // Nothing more needs to be done, since admins use the global mutex
                $mutex_name = $global_mutex_name;
            } else {
                // User is a non-admin; let's use a user mutex.
                $mutex_name = 'crawler-'.$owner->id;
                $lock_successful = $mutex_dao->getMutex($mutex_name);
                $mutex_dao->releaseMutex($global_mutex_name);
            }
        }

        if ($lock_successful) {
            $this->emitObjectMethod('crawl');
            $mutex_dao->releaseMutex($mutex_name);
        } else {
            throw new CrawlerLockedException("Error starting crawler; another crawl is already in progress.");
        }
    }

    /**
     * Register crawler plugin.
     * @param str $object_name Name of Crawler plugin object which instantiates the Crawler interface, like
     * "TwitterPlugin"
     */
    public function registerCrawlerPlugin($object_name) {
        $this->registerObjectMethod('crawl', $object_name, 'crawl');
    }
}
