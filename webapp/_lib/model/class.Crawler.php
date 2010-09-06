<?php
/**
 * Crawler
 *
 * Singleton provides hooks for crawler plugins.
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class Crawler extends PluginHook {
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
        if (!isset($_SESSION['user'])) {
            throw new UnauthorizedUserException('You need a valid session to launch the crawler.');
        }
        $mutex_dao = DAOFactory::getDAO('MutexDAO');
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail($_SESSION['user']);
        if (empty($owner)) {
            throw new UnauthorizedUserException('You need a valid session to launch the crawler.');
        }
        
        $global_mutex_name = 'crawler';
        
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
     * @param str $object_name Name of Crawler plugin object which instantiates the Crawler interface, like "TwitterPlugin"
     */
    public function registerCrawlerPlugin($object_name) {
        $this->registerObjectMethod('crawl', $object_name, 'crawl');
    }
}
