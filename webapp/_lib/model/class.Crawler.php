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
     */
    public function crawl() {
        $this->emitObjectMethod('crawl');
    }

    /**
     * Register crawler plugin.
     * @param str $object_name Name of Crawler plugin object which instantiates the Crawler interface, like "TwitterPlugin"
     */
    public function registerCrawlerPlugin($object_name) {
        $this->registerObjectMethod('crawl', $object_name, 'crawl');
    }
}
