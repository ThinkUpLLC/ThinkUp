<?php
/**
 * Crawler plugin interface
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
interface CrawlerPlugin extends ThinkTankPlugin {
    /**
     * Crawl
     */
    public function crawl();
}

