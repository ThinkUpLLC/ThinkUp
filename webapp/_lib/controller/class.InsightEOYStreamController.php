<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.InsightEOYStreamController.php
 *
 * Copyright (c) 2014-2016 Gina Trapani
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
 * Insights stream controller
 *
 * Displays a list of insights for authenticated service users.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2014-2016 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class InsightEOYStreamController extends InsightStreamController {
    /**
     * Template name
     * @var string
     */
    var $tpl_name = 'insights-eoy.tpl';
    /**
     * Show insights since.
     * @var str
     */
    var $since;

    public function __construct($session_started=false, $since=null) {
        parent::__construct($session_started);
        $this->since = $since;
        $_GET['since'] = $since;
    }
    /**
     * Load view with data to display page of insights.
     */
    protected function displayPageOfInsights() {
        $cfg = Config::getInstance();
        $thinkup_username = $cfg->getValue('install_folder');
        $this->addToView('thinkup_username', $thinkup_username);

        $tomorrows_teaser = $this->getTomorrowsTeaser();
        $this->addToView('tomorrows_teaser', $tomorrows_teaser);

        $this->addToView('is_year_end', true);
        $this->addToView('year_end_year', substr($this->since, 0, 4));
        $insight_dao = DAOFactory::getDAO('InsightDAO');

        $page = (isset($_GET['page']) && is_numeric($_GET['page']))?$_GET['page']:1;
        if (Session::isLoggedIn()) {
            if ($this->isAdmin()) {
                ///show all insights for all service users
                $insights = $insight_dao->getAllInstanceEOYInsights($page_count=(self::PAGE_INSIGHTS_COUNT+1),
                    $page, $this->since);
            } else {
                //show only service users owner owns
                $owner_dao = DAOFactory::getDAO('OwnerDAO');
                $owner = $owner_dao->getByEmail($this->getLoggedInUser());

                $insights = $insight_dao->getAllOwnerInstanceEOYInsights($owner->id,
                    $page_count=(self::PAGE_INSIGHTS_COUNT+1), $page, 1, $this->since);
            }
        } else {
            //show just public service users in stream
            $insights = $insight_dao->getPublicEOYInsights($page_count=(self::PAGE_INSIGHTS_COUNT+1), $page,
                $this->since);
        }
        if (isset($insights) && sizeof($insights) > 0) {
            if (sizeof($insights) == (self::PAGE_INSIGHTS_COUNT+1)) {
                $this->addToView('next_page', $page+1);
                array_pop($insights);
            }
            if ($page != 1) {
                $this->addToView('last_page', $page-1);
            }
            $this->addToView('insights', $insights);
        } else {
            if ($this->isLoggedIn()) {
                //if owner has no instances, show welcome message
                $instance_dao = DAOFactory::getDAO('InstanceDAO');
                if (!isset($owner)) {
                    $owner_dao = DAOFactory::getDAO('OwnerDAO');
                    $owner = $owner_dao->getByEmail($this->getLoggedInUser());
                }
                $owned_instances = $instance_dao->getByOwner($owner, $force_not_admin = false, $only_active=true);
                $config = Config::getInstance();
                $site_root_path = $config->getValue('site_root_path');
                $plugin_link = '<a href="'.$site_root_path.'account/?p=';
                if (sizeof($owned_instances) > 0) {
                    $this->addToView('message_header', "ThinkUp doesn't have any insights for you yet.");
                    if (!Utils::isThinkUpLLC()) {
                        $this->addToView('message_body', "Check back later, ".
                        "or <a href=\"".$site_root_path."crawler/updatenow.php\">update your ThinkUp data now</a>.");
                    }
                } else {
                    $this->addToView('message_header', "Welcome to ThinkUp. Let's get started.");

                    $thinkupllc_endpoint = $config->getValue('thinkupllc_endpoint');
                    if (isset($thinkupllc_endpoint)) {
                        $this->addToView('message_body', "Set up a ".$plugin_link."twitter\">Twitter</a> or ".
                        "".$plugin_link."facebook\">Facebook</a> account.");
                    } else {
                        $this->addToView('message_body', "Set up a ".$plugin_link."twitter\">Twitter</a>, ".
                        "".$plugin_link."facebook\">Facebook</a>, ".$plugin_link.
                        "googleplus\">Google+</a>, or ".$plugin_link."foursquare\">Foursquare</a> account.");
                    }
                }
            } else { //redirect to login
                return false;
            }
        }
        return true;
    }

    /**
     * Get Tomorrow's insight tout copy
     * @return str
     */
    private function getTomorrowsTeaser() {
        $teasers = array();
        $teaser['2014-11-28'] = 'Your most-loved';
        $teaser['2014-11-29'] = 'Your most-loved';
        $teaser['2014-11-30'] = 'Your most-loved';
        $teaser['2014-12-01'] = 'But what about you?';
        $teaser['2014-12-02'] = 'What was your chattiest day?';
        $teaser['2014-12-03'] = 'Photo of the year';
        $teaser['2014-12-04'] = 'What was <em>really</em> exciting?';
        $teaser['2014-12-05'] = 'Keep it clean?';
        $teaser['2014-12-06'] = 'Ladies and Gentlemen...';
        $teaser['2014-12-07'] = 'RT FTW';
        //$teaser['2014-12-08'] = 'Fan-tastic';
        $teaser['2014-12-08'] = "2014's BFFs";
        $teaser['2014-12-09'] = 'Enough to fill a book?';
        $teaser['2014-12-10'] = 'Getting a response';
        $teaser['2014-12-11'] = 'Who got link love?';
        $teaser['2014-12-12'] = 'Laughs of the year';
        $teaser['2014-12-14'] = 'Link of the year';
        $teaser['2014-12-15'] = 'Tweeter of the year';
        $teaser['2014-12-16'] = 'Going streaking?';
        $teaser['2014-12-17'] = 'Repeat after me';
        $teaser['2014-12-18'] = 'The final count';
        $teaser['2014-12-21'] = 'Words of the year';
        $teaser['2014-12-22'] = 'Best of the year';

        //2015
        $teaser['2015-11-30'] = 'Your most-loved';
        $teaser['2015-12-01'] = 'But what about you?';
        $teaser['2015-12-02'] = 'What was your chattiest day?';
        $teaser['2015-12-03'] = 'Photo of the year';
        $teaser['2015-12-04'] = 'Keep it clean?';
        $teaser['2015-12-06'] = 'What was <em>really</em> exciting?';
        $teaser['2015-12-07'] = 'RT FTW';
        $teaser['2015-12-08'] = "2015's BFFs";
        $teaser['2015-12-09'] = 'Enough to fill a book?';
        $teaser['2015-12-10'] = 'Getting a response';
        $teaser['2015-12-11'] = 'Laughs of the year';
        $teaser['2015-12-13'] = 'Who got link love?';
        $teaser['2015-12-14'] = 'Link of the year';
        $teaser['2015-12-15'] = 'Most likeable';
        $teaser['2015-12-16'] = 'Going streaking?';
        $teaser['2015-12-17'] = 'Repeat after me';
        $teaser['2015-12-20'] = 'The final count';
        $teaser['2015-12-21'] = 'Words of the year';
        $teaser['2015-12-22'] = 'Most popular, 2015';
        $teaser['2015-12-29'] = 'Best of the year';

        $today = date('Y-m-d');
        if (isset($teaser[$today])) {
            return $teaser[$today];
        } else {
            return null;
        }
    }
}
