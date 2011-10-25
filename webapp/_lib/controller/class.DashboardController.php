<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.DashboardController.php
 *
 * Copyright (c) 2009-2011 Gina Trapani, Mark Wilkie
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
 * Dashboard controller
 *
 * The main controller which displays a given view for a give instance user.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Gina Trapani, Mark Wilkie
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
        if ($this->shouldRefreshCache() ) {
            $this->setInstance();

            $this->view_name = (isset($_GET['v']))?$_GET['v']:'default';
            $webapp = Webapp::getInstance();
            if (isset($this->instance)) {
                $webapp->setActivePlugin($this->instance->network);
                $sidebar_menu = $webapp->getDashboardMenu($this->instance);
                $this->addToView('sidebar_menu', $sidebar_menu);
                $this->loadView();
            } else {
                if (!Session::isLoggedIn()) {
                    $this->addInfoMessage('There are no public accounts set up in this ThinkUp installation.'.
                    '<br /><br />To make a current account public, log in and click on "Settings." '.
                    'Click on one of the plugins that contain accounts (like Twitter or Facebook) and '.
                    'click "Set Public" next to the account that should appear to users who are not logged in.');
                } else  {
                    $this->addInfoMessage('Welcome to ThinkUp. Let\'s get started.');

                    $plugin_dao = DAOFactory::getDAO('PluginDAO');
                    $plugins = $plugin_dao->getInstalledPlugins();
                    $add_user_buttons = array();
                    foreach ($plugins as $plugin) {
                        if ($plugin->folder_name == 'twitter' || $plugin->folder_name == 'facebook'
                        || $plugin->folder_name == 'googleplus') {
                            if ($plugin->is_active && $plugin->isConfigured()) {
                                $add_user_buttons[] = $plugin->folder_name;
                            }
                        }
                    }
                    $this->addToView('add_user_buttons', $add_user_buttons);
                }
            }
        }
        return $this->generateView();
    }

    /**
     * Load the view with required variables
     */
    private function loadView() {
        $webapp = Webapp::getInstance();
        if ($this->view_name == 'default') {
            $this->loadDefaultDashboard();
        } else {
            $menu_item = $webapp->getDashboardMenuItem($this->view_name, $this->instance);
            $this->addToView('data_template', $menu_item->view_template);
            $this->addToView('display', $this->view_name);
            $this->addToView('header', $menu_item->name);
            $this->addToView('description', $menu_item->description);
            $this->addToView('parent', $menu_item->parent);

            $this->setPageTitle($this->instance->network_username.' on '.ucfirst($this->instance->network));
            $page = (isset($_GET['page']) && is_numeric($_GET['page']))?$_GET['page']:1;
            foreach ($menu_item->datasets as $dataset) {
                if (array_search('#page_number#', $dataset->method_params) !== false) { //there's paging
                    $this->addToView('next_page', $page+1);
                    $this->addToView('last_page', $page-1);
                }
                $this->addToView($dataset->name, $dataset->retrieveDataset($page));
                if (Session::isLoggedIn() && $dataset->isSearchable()) {
                    $view_name = 'is_searchable';
                    $this->addToView($view_name, true);
                }
                $this->view_mgr->addHelp($this->view_name, $dataset->getHelp());
            }
        }
    }

    /**
     * Set the instance variable based on request and logged-in status
     * Add the list of avaiable instances to the view you can switch to in the dropdown based on logged-in status
     */
    private function setInstance() {
        $instance_dao = DAOFactory::getDAO('InstanceDAO');
        $config = Config::getInstance();
        if ($this->isLoggedIn()) {
            $owner_dao = DAOFactory::getDAO('OwnerDAO');
            $owner = $owner_dao->getByEmail($this->getLoggedInUser());
            if (isset($_GET["u"]) && isset($_GET['n'])) {
                $instance = $instance_dao->getByUsernameOnNetwork($_GET["u"], $_GET['n']);
                $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
                if ($owner_instance_dao->doesOwnerHaveAccessToInstance($owner, $instance)) {
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
            }
            $this->addToView('instances', $instance_dao->getPublicInstances());
        }
        if (!isset($this->instance)) {
            // A specific instance wasn't passed in the URL (or isn't accessible), get a default one
            $instance_id_to_display = $config->getValue('default_instance');
            $instance_id_to_display = intval($instance_id_to_display);
            if ( $instance_id_to_display != 0) {
                $this->instance = $instance_dao->get($instance_id_to_display);
            }
            if (!isset($this->instance) || !$this->instance->is_public) {
                $this->instance = $instance_dao->getInstanceFreshestPublicOne();
            }
        }
        if (isset($this->instance)) {
            //user
            $user_dao = DAOFactory::getDAO('UserDAO');
            $user = $user_dao->getDetails($this->instance->network_user_id, $this->instance->network);
            $this->addToView('user_details', $user);

            SessionCache::put('selected_instance_network', $this->instance->network);
            SessionCache::put('selected_instance_username', $this->instance->network_username);

            //check Realtime last update and overwrite instance->last_update
            $stream_proc_dao = DAOFactory::getDAO('StreamProcDAO');
            $process = $stream_proc_dao->getProcessInfoForInstance($this->instance->id);
            if (isset($process)) {
                //$this->instance->crawler_last_run = $process['last_report'];
                $this->instance->crawler_last_run = 'realtime';
            }

            $this->addToView('instance', $this->instance);
        }
        $this->addToView('developer_log', $config->getValue('is_log_verbose'));
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
            $recent_posts = $post_dao->getAllPosts($this->instance->network_user_id, $this->instance->network, 20,
            true);
            $this->addToView('recent_posts', $recent_posts);
            $hot_posts = $post_dao->getHotPosts($this->instance->network_user_id, $this->instance->network, 20);
            $this->addToView('hot_posts', $hot_posts);
            $most_replied_to_1wk = $post_dao->getMostRepliedToPostsInLastWeek($this->instance->network_username,
            $this->instance->network, 5);
            $this->addToView('most_replied_to_1wk', $most_replied_to_1wk);
            $most_retweeted_1wk = $post_dao->getMostRetweetedPostsInLastWeek($this->instance->network_username,
            $this->instance->network, 5);
            $this->addToView('most_retweeted_1wk', $most_retweeted_1wk);
            //for now, only show most liked/faved posts on Facebook dashboard
            //once we cache fave counts for Twitter, we can remove this conditional
            if ($this->instance->network == "facebook" || $this->instance->network == "facebook page"
            || $this->instance->network == "google+") {
                $most_faved_1wk = $post_dao->getMostFavedPostsInLastWeek($this->instance->network_username,
                $this->instance->network, 5);
                $this->addToView('most_faved_1wk', $most_faved_1wk);
            }

            //follows
            $follow_dao = DAOFactory::getDAO('FollowDAO');
            $least_likely_followers = $follow_dao->getLeastLikelyFollowers($this->instance->network_user_id, 'twitter',
            12);
            $this->addToView('least_likely_followers', $least_likely_followers);

            //follower count history
            //by day
            $follower_count_dao = DAOFactory::getDAO('FollowerCountDAO');
            $follower_count_history_by_day = $follower_count_dao->getHistory($this->instance->network_user_id,
            $this->instance->network, 'DAY', 5);
            $this->addToView('follower_count_history_by_day', $follower_count_history_by_day);

            //by week
            $follower_count_history_by_week = $follower_count_dao->getHistory($this->instance->network_user_id,
            $this->instance->network, 'WEEK', 5);
            $this->addToView('follower_count_history_by_week', $follower_count_history_by_week);

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
