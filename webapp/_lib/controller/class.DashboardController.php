<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.DashboardController.php
 *
 * Copyright (c) 2009-2010 Gina Trapani, Mark Wilkie, randi2kewl
 *
 * LICENSE:
 *
 * This file is part of ThinkUp.
 * 
 * ThinkUp is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * ThinkUp is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with ThinkUp.  If not, see <http://www.gnu.org/licenses/>.
 *
*/
/**
 * Dashboard controller
 *
 * The main controller which displays a given view for a give instance user.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani, Mark Wilkie, randi2kewl
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class DashboardController extends ThinkUpController {
    /**
     * Instance user
     * @var Instance
     */
    var $instance;

    /**
     * View name
     * @var str
     */
    var $view_name;

    public function control() {
        $this->setViewTemplate('dashboard.tpl');
        $this->setInstance();

        $this->view_name = (isset($_GET['v']))?$_GET['v']:'default';
        $webapp = Webapp::getInstance();
        if (isset($this->instance)) {
            $webapp->setActivePlugin($this->instance->network);
            $this->loadSidebarMenu();
            $this->loadView();
        } else {
            if (!Session::isLoggedIn()) {
                $this->addInfoMessage('There are no public accounts set up in this ThinkUp installation.');
            } else  {
                $config = Config::getInstance();
                $this->addInfoMessage('You have no accounts configured. <a href="'.$config->getValue('site_root_path').
                'account/?p=twitter">Set up an account now&rarr;</a>');
            }
        }
        return $this->generateView();
    }


    private function loadSidebarMenu() {
        $webapp = Webapp::getInstance();
        $tabs = $webapp->getChildTabsUnderPosts($this->instance);
        $tabs_array = array();
        foreach ($tabs as $tab) {
            $tabs_array[$tab->short_name] = $tab->name;
        }
        $this->addToView("sidebar_menu_posts", $tabs_array);

        $tabs = $webapp->getChildTabsUnderReplies($this->instance);
        $tabs_array = array();
        foreach ($tabs as $tab) {
            $tabs_array[$tab->short_name] = $tab->name;
        }
        $this->addToView("sidebar_menu_replies", $tabs_array);

        $tabs = $webapp->getChildTabsUnderFriends($this->instance);
        $tabs_array = array();
        foreach ($tabs as $tab) {
            $tabs_array[$tab->short_name] = $tab->name;
        }
        $this->addToView("sidebar_menu_friends", $tabs_array);

        $tabs = $webapp->getChildTabsUnderFollowers($this->instance);
        $tabs_array = array();
        foreach ($tabs as $tab) {
            $tabs_array[$tab->short_name] = $tab->name;
        }
        $this->addToView("sidebar_menu_followers", $tabs_array);

        $tabs = $webapp->getChildTabsUnderLinks($this->instance);
        $tabs_array = array();
        foreach ($tabs as $tab) {
            $tabs_array[$tab->short_name] = $tab->name;
        }
        $this->addToView("sidebar_menu_links", $tabs_array);
    }
    /**
     * Load the view with required variables
     */
    private function loadView() {
        $webapp = Webapp::getInstance();
        if ($this->view_name == 'default') {
            $this->loadDefaultDashboard();
        } else {
            $tab = $webapp->getTab($this->view_name, $this->instance);
            $this->addToView('data_template', $tab->view_template);
            $this->addToView('display', $tab->short_name);
            $this->addToView('header', $tab->name);
            $this->addToView('description', $tab->description);

            $this->setPageTitle($this->instance->network_username.' on '.ucfirst($this->instance->network));
            $page = (isset($_GET['page']) && is_numeric($_GET['page']))?$_GET['page']:1;
            foreach ($tab->datasets as $dataset) {
                if (array_search('#page_number#', $dataset->method_params) !== false) { //there's paging
                    $this->addToView('next_page', $page+1);
                    $this->addToView('last_page', $page-1);
                }
                $this->addToView($dataset->name, $dataset->retrieveDataset($page));
                if(Session::isLoggedIn() && $dataset->isSearchable()) {
                    $view_name = 'is_searchable';
                    $this->addToView($view_name, true);
                }
            }
        }
    }

    /**
     * Set the instance variable based on request and logged-in status
     * Add the list of avaiable instances to the view you can switch to in the dropdown based on logged-in status
     */
    private function setInstance() {
        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        if ($this->isLoggedIn()) {
            $owner_dao = DAOFactory::getDAO('OwnerDAO');
            $owner = $owner_dao->getByEmail($this->getLoggedInUser());
            if (isset($_GET["u"]) && isset($_GET['n'])) {
                $instance = $instance_dao->getByUsernameOnNetwork($_GET["u"], $_GET['n']);
                $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
                if ($owner_instance_dao->doesOwnerHaveAccess($owner, $instance)) {
                    $this->instance = $instance;
                } else {
                    $this->instance = null;
                    $this->addErrorMessage("Insufficient privileges");
                }
            } else {
                $this->instance = $instance_dao->getFreshestByOwnerId($owner->id);
            }
            $this->addToView('instances', $instance_dao->getByOwner($owner));
        } else {
            if (isset($_GET["u"]) && isset($_GET['n'])) {
                $instance = $instance_dao->getByUsernameOnNetwork($_GET["u"], $_GET['n']);
                if ($instance->is_public) {
                    $this->instance = $instance;
                } else {
                    $this->addErrorMessage("Insufficient privileges");
                }
            } else {
                $this->instance = $instance_dao->getInstanceFreshestPublicOne();
            }
            $this->addToView('instances', $instance_dao->getPublicInstances());
        }
        $this->addToView('instance', $this->instance);
        if (isset($this->instance)) {
            //user
            $user_dao = DAOFactory::getDAO('UserDAO');
            $user = $user_dao->getDetails($this->instance->network_user_id, $this->instance->network);
            $this->addToView('user_details', $user);
        }
    }

    /**
     * Load instance dashboard
     * @param str $username
     * @param str $network
     */
    private function loadDefaultDashboard() {
        if (isset($this->instance)) {
            $this->setPageTitle($this->instance->network_username . "'s Dashboard");

            $post_dao = DAOFactory::getDAO('PostDAO');
            //posts
            $recent_posts = $post_dao->getAllPosts($this->instance->network_user_id, $this->instance->network, 3, true);
            $this->addToView('recent_posts', $recent_posts);
            $most_replied_to_1wk = $post_dao->getMostRepliedToPostsInLastWeek($this->instance->network_username,
            $this->instance->network, 5);
            $this->addToView('most_replied_to_1wk', $most_replied_to_1wk);
            $most_retweeted_1wk = $post_dao->getMostRetweetedPostsInLastWeek($this->instance->network_username,
            $this->instance->network, 5);
            $this->addToView('most_retweeted_1wk', $most_retweeted_1wk);

            //follows
            $follow_dao = DAOFactory::getDAO('FollowDAO');
            $least_likely_followers = $follow_dao->getLeastLikelyFollowers($this->instance->network_user_id, 'twitter',
            16);
            $this->addToView('least_likely_followers', $least_likely_followers);

            //follower count history
            $follower_count_dao = DAOFactory::getDAO('FollowerCountDAO');
            $follower_count_history_by_day = $follower_count_dao->getHistory($this->instance->network_user_id,
            'twitter', 'DAY');
            $this->addToView('follower_count_history_by_day', $follower_count_history_by_day);
            $first_follower_count = $follower_count_history_by_day['history'][0]['count'];
            $last_follower_count = $follower_count_history_by_day['history']
            [sizeof($follower_count_history_by_day['history'])-1]['count'];
            $this->addToView('follower_count_by_day_trend',
            ($last_follower_count - $first_follower_count)/sizeof($follower_count_history_by_day['history']));
            $follower_count_history_by_week = $follower_count_dao->getHistory($this->instance->network_user_id,
            'twitter', 'WEEK');
            $this->addToView('follower_count_history_by_week', $follower_count_history_by_week);
            $first_follower_count = $follower_count_history_by_week['history'][0]['count'];
            $last_follower_count = $follower_count_history_by_week['history']
            [sizeof($follower_count_history_by_week['history'])-1]['count'];
            $this->addToView('follower_count_by_week_trend',
            ($last_follower_count - $first_follower_count)/sizeof($follower_count_history_by_week['history']));

            $post_dao = DAOFactory::getDAO('PostDAO');
            list($all_time_clients_usage, $latest_clients_usage) =
            $post_dao->getClientsUsedByUserOnNetwork($this->instance->network_user_id, $this->instance->network);

            // Only show the top 10 most used clients, since forever
            $all_time_clients_usage = array_merge(
            array_slice($all_time_clients_usage, 0, 10),
            array('Others'=>array_sum(array_slice($all_time_clients_usage, 10)))
            );
            $this->addToView('all_time_clients_usage', $all_time_clients_usage);

            // Only show the two most used clients for the last 25 posts
            $latest_clients_usage = array_slice($latest_clients_usage, 0, 2);
            $this->addToView('latest_clients_usage', $latest_clients_usage);
        } else {
            $this->addErrorMessage($username." on ".ucwords($this->instance->network).
            " isn't set up on this ThinkUp installation.");
        }
    }
}