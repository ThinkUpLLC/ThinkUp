<?php
/**
 * CheckCrawler Controller
 * Outputs a message if crawler hasn't run in over 3 hours.
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class CheckCrawlerController extends ThinkUpController {
    var $threshold = 3.0;

    /**
     * Constructor
     *
     * @param boolean $session_started
     */
    public function __construct($session_started=false) {
        parent::__construct($session_started);
        $this->setViewTemplate('crawler.checkcrawler.tpl');
        $this->disableCaching();
    }

    public function control() {
        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $hours_since_last_crawl = $instance_dao->getHoursSinceLastCrawlerRun();
        if (isset($hours_since_last_crawl) && $hours_since_last_crawl > $this->threshold)  {
            $this->addToView('message', "Crawler hasn't run in ".round($hours_since_last_crawl)." hours");
        }
        return $this->generateView();
    }
}