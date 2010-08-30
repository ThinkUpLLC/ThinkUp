<?php
/**
 * Crawler plugin interface
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
interface CrawlerPlugin extends ThinkUpPlugin {
    /**
     * Crawl
     */
    public function crawl();
}

