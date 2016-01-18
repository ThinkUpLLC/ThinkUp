<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.SearchController.php
 *
 * Copyright (c) 2013-2016 Gina Trapani
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
 * Search Controller
 * Display search results for all an owner's instances.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2013-2016 Gina Trapani
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class SearchController extends ThinkUpAuthController {
    /**
     * Number of search results to display on a page
     * @var int
     */
    const PAGE_RESULTS_COUNT = 20;
    /**
     * Query
     * @var str
     */
    var $query;

    public function authControl() {
        $this->setViewTemplate('search.tpl');
        $this->addToView('enable_bootstrap', true);
        $this->addToView('tpl_path', THINKUP_WEBAPP_PATH.'plugins/insightsgenerator/view/');

        $config = Config::getInstance();
        if ($config->getValue('image_proxy_enabled') == true) {
            $this->addToView('image_proxy_sig', $config->getValue('image_proxy_sig'));
        }

        if ($this->shouldRefreshCache() ) {
            $instance_dao = DAOFactory::getDAO('InstanceDAO');
            $owner_dao = DAOFactory::getDAO('OwnerDAO');
            $owner = $owner_dao->getByEmail($this->getLoggedInUser());
            if (isset($_GET['q'])) {
                //Get an owner's instances
                $instances = $instance_dao->getByOwner($owner);

                $this->query = htmlentities(trim($_GET['q']));
                $this->addToView('query', $this->query);

                if ($this->query == '') {
                    $this->addErrorMessage("Uh-oh. Your search term is missing. Please try again.");
                } else {
                    $instances_search_results = array();
                    //Foreach instance
                    foreach ($instances as $instance) {
                        if ($instance->network !== 'facebook') {
                            //Get follower search results
                            $results = self::searchFollowers($instance->network_user_id, $instance->network);
                            //Unique array key
                            $arr_key = count($results)."-"
                                .$instance->network_user_id.'-'.$instance->network;
                            $instances_search_results[$arr_key]['instance'] = $instance;
                            $instances_search_results[$arr_key]['search_results'] = $results;
                            $arr_key = null;
                            $results = null;
                        }
                    }
                    //Order sets of results by network with most results first
                    ksort($instances_search_results, SORT_STRING);
                    $instances_search_results = array_reverse($instances_search_results);
                    $this->addToView('instances_search_results', $instances_search_results);
                }
            } else {
                $this->addErrorMessage("Uh-oh. Your search terms are missing. Please try again.");
            }
            //Populate search dropdown with service users
            $this->addToView('instances', $instances);
        }
        return $this->generateView();
    }
    /**
     * Populate view with follower search results.
     * @param str $user_id
     * @param array Users
     */
    private function searchFollowers($user_id, $network) {
        $page_number = (isset($_GET['page']) && is_numeric($_GET['page']))?$_GET['page']:1;
        $keywords = explode(' ', $this->query);

        $follow_dao = DAOFactory::getDAO('FollowDAO');
        $users = $follow_dao->searchFollowers($keywords, $network, $user_id, $page_number,
            $page_count=(self::PAGE_RESULTS_COUNT));

        if (isset($users) && sizeof($users) > 0) {
            if (sizeof($posts) == (self::PAGE_RESULTS_COUNT+1)) {
                $this->addToView('next_page', $page_number+1);
                $this->addToView('last_page', $page_number-1);
                array_pop($users);
            }
            return $users;
        } else {
            return null;
        }
    }
}