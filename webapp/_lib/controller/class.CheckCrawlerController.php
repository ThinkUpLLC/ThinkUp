<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.CheckCrawlerController.php
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
 * CheckCrawler Controller
 * Outputs a message if crawler hasn't run in over 3 hours.
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class CheckCrawlerController extends ThinkUpController {
    private $threshold;

    /**
     * Constructor
     *
     * @param boolean $session_started
     */
    public function __construct($session_started=false, $argc = null, $argv = null) {
        parent::__construct($session_started);
        $this->setViewTemplate('crawler.checkcrawler.tpl');
        $this->disableCaching();
        $this->profiler_enabled = false;

        $this->threshold = isset($argv[1]) ? floatval($argv[1]) : 3.0;
    }

    public function control() {
        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $hours_since_last_crawl = $instance_dao->getHoursSinceLastCrawlerRun();
        if (isset($hours_since_last_crawl) && $hours_since_last_crawl > $this->threshold)  {
            $this->addToView('message', Utils::getApplicationURL().": Crawler hasn't run in ".
            round($hours_since_last_crawl)." hours");
        }
        return $this->generateView();
    }
}