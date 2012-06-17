<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.DashboardController.php
 *
 * Copyright (c) 2009-2012 Gina Trapani, Mark Wilkie
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
 * @copyright 2009-2012 Gina Trapani, Mark Wilkie
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
                    $this->addInfoMessage('There are no public accounts set up in this ThinkUp installation.');
                } else  {
                    $this->addInfoMessage('Welcome to ThinkUp. Let\'s get started.');

                    $plugin_dao = DAOFactory::getDAO('PluginDAO');
                    $plugins = $plugin_dao->getInstalledPlugins();
                    $add_user_buttons = array();
                    foreach ($plugins as $plugin) {
                        if ($plugin->folder_name == 'twitter' || $plugin->folder_name == 'facebook'
                        || $plugin->folder_name == 'googleplus') {
                            if ($plugin->is_active) {
                                $add_user_buttons[] = $plugin->folder_name;
                            }
                        }
                    }
                    $add_user_buttons = array_reverse($add_user_buttons);
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
            if (isset($menu_item)) {
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
            } else {
                $this->loadDefaultDashboard();
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
                $instance = $instance_dao->getByUsernameOnNetwork(stripslashes($_GET["u"]), $_GET['n']);
                if (isset($instance)) {
                    $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
                    if ($owner_instance_dao->doesOwnerHaveAccessToInstance($owner, $instance)) {
                        $this->instance = $instance;
                    } else {
                        $this->instance = null;
                        $this->addErrorMessage("Insufficient privileges");
                    }
                } else {
                    $this->addErrorMessage(stripslashes($_GET["u"])." on ".ucfirst($_GET['n']) ." is not in ThinkUp.");
                }
            } else {
                $this->instance = $instance_dao->getFreshestByOwnerId($owner->id);
            }
            $this->addToView('instances', $instance_dao->getByOwner($owner));
        } else {
            if (isset($_GET["u"]) && isset($_GET['n'])) {
                $instance = $instance_dao->getByUsernameOnNetwork(stripslashes($_GET["u"]), $_GET['n']);
                if (isset($instance)) {
                    if ($instance->is_public) {
                        $this->instance = $instance;
                    } else {
                        $this->addErrorMessage("Insufficient privileges");
                    }
                } else {
                    $this->addErrorMessage(stripslashes($_GET["u"])." on ".ucfirst($_GET['n']) ." is not in ThinkUp.");
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
            if (Session::isLoggedIn() && !isset($user)) {
                $this->addInfoMessage("Oops! There's no information about ".$this->instance->network_username.
                " on ".ucfirst($this->instance->network)." to display.");
                $this->addToView('show_update_now_button', true);
            }

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
        } else {
            SessionCache::put('selected_instance_network', null);
            SessionCache::put('selected_instance_username', null);
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

            $insight_dao = DAOFactory::getDAO('InsightDAO');

            $hot_posts_data = $insight_dao->getPreCachedInsightData('PostMySQLDAO::getHotPosts', $this->instance->id,
            date('Y-m-d'));
            if (isset($hot_posts_data)) {
                $this->addToView('hot_posts_data', $hot_posts_data);
            }

            $click_stats_data = $insight_dao->getPreCachedInsightData( 'ShortLinkMySQLDAO::getRecentClickStats',
            $this->instance->id, date('Y-m-d'));
            if (isset($click_stats_data)) {
                $this->addToView('click_stats_data', $click_stats_data);
            }

            $post_dao = DAOFactory::getDAO('PostDAO');
            $most_replied_to_1wk = $insight_dao->getPreCachedInsightData(
            'PostMySQLDAO::getMostRepliedToPostsInLastWeek', $this->instance->id, date('Y-m-d'));
            $this->addToView('most_replied_to_1wk', $most_replied_to_1wk);

            $most_retweeted_1wk = $insight_dao->getPreCachedInsightData(
            'PostMySQLDAO::getMostRetweetedPostsInLastWeek', $this->instance->id, date('Y-m-d'));
            $this->addToView('most_retweeted_1wk', $most_retweeted_1wk);
            //for now, only show most liked/faved posts on Facebook dashboard
            //once we cache fave counts for Twitter, we can remove this conditional
            if ($this->instance->network == "facebook" || $this->instance->network == "facebook page"
            || $this->instance->network == "google+") {
                $most_faved_1wk = $post_dao->getMostFavedPostsInLastWeek($this->instance->network_username,
                $this->instance->network, 5);
                $this->addToView('most_faved_1wk', $most_faved_1wk);
            }

            //follows - these are pre-cached in insights
            $least_likely_followers = $insight_dao->getPreCachedInsightData(
            'FollowMySQLDAO::getLeastLikelyFollowersThisWeek', $this->instance->id, date('Y-m-d'));
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

            list($all_time_clients_usage, $latest_clients_usage) =
            $insight_dao->getPreCachedInsightData(
            'PostMySQLDAO::getClientsUsedByUserOnNetwork', $this->instance->id, date('Y-m-d'));
            $this->addToView('most_replied_to_1wk', $most_replied_to_1wk);

            // The sliceVisibilityThreshold option in the chart will prevent small slices from being created
            $all_time_clients_usage = InsightsGenerator::getClientUsageVisualizationData($all_time_clients_usage);
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
