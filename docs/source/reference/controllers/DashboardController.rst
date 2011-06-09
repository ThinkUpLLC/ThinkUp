DashboardController
===================
Inherits from `ThinkUpController <./ThinkUpController.html>`_.

ThinkUp/webapp/_lib/controller/class.DashboardController.php

Copyright (c) 2009-2011 Gina Trapani, Mark Wilkie

Dashboard controller

The main controller which displays a given view for a give instance user.


Properties
----------

instance
~~~~~~~~

Instance user

view_name
~~~~~~~~~

View name



Methods
-------

control
~~~~~~~



.. code-block:: php5

    <?php
        public function control() {
            $this->setViewTemplate('dashboard.tpl');
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
                    $this->addInfoMessage('There are no public accounts set up in this ThinkUp installation.<br /><br />'.
                    'To make a current account public, log in and click on "Settings." Click on one of the plugins '.
                    'that contain accounts (like Twitter or Facebook) and click "Set Public" next to the account that '.
                    ' should appear to users who are not logged in.');
                } else  {
                    $config = Config::getInstance();
                    $this->addInfoMessage('You have no services configured. <a href="'.$config->getValue('site_root_path').
                    'account/">Set up a service like Twitter or Facebook now&rarr;</a>');
                }
            }
            return $this->generateView();
        }


loadView
~~~~~~~~

Load the view with required variables

.. code-block:: php5

    <?php
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
    
                $this->setPageTitle($this->instance->network_username.' on '.ucfirst($this->instance->network));
                $page = (isset($_GET['page']) && is_numeric($_GET['page']))?$_GET['page']:1;
                foreach ($menu_item->datasets as $dataset) {
                    if (array_search('#page_number#', $dataset->method_params) !== false) { //there's paging
                        $this->addToView('next_page', $page+1);
                        $this->addToView('last_page', $page-1);
                    }
                    $this->addToView($dataset->name, $dataset->retrieveDataset($page));
                    if(Session::isLoggedIn() && $dataset->isSearchable()) {
                        $view_name = 'is_searchable';
                        $this->addToView($view_name, true);
                    }
                    $this->view_mgr->addHelp($this->view_name, $dataset->getHelp());
                }
            }
        }


setInstance
~~~~~~~~~~~

Set the instance variable based on request and logged-in status
Add the list of avaiable instances to the view you can switch to in the dropdown based on logged-in status

.. code-block:: php5

    <?php
        private function setInstance() {
            $instance_dao = DAOFactory::getDAO('InstanceDAO');
            $config = Config::getInstance();
            $instance_id_to_display = $config->getValue('default_instance');
            $instance_id_to_display = intval($instance_id_to_display);
            if ( $instance_id_to_display != 0) {
                $this->instance = $instance_dao->get($instance_id_to_display);
            }
            if (!isset($this->instance) || !$this->instance->is_public) {
                $this->instance = $instance_dao->getInstanceFreshestPublicOne();
            }
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
                }
                $this->addToView('instances', $instance_dao->getPublicInstances());
            }
            if (isset($this->instance)) {
                //user
                $user_dao = DAOFactory::getDAO('UserDAO');
                $user = $user_dao->getDetails($this->instance->network_user_id, $this->instance->network);
                $this->addToView('user_details', $user);
    
                SessionCache::put('selected_instance_network', $this->instance->network);
                SessionCache::put('selected_instance_username', $this->instance->network_username);
                $this->addToView('instance', $this->instance);
            }
        }


loadDefaultDashboard
~~~~~~~~~~~~~~~~~~~~
* **@param** str $username
* **@param** str $network


Load instance dashboard

.. code-block:: php5

    <?php
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
                12);
                $this->addToView('least_likely_followers', $least_likely_followers);
    
                //follower count history
                //by day
                $follower_count_dao = DAOFactory::getDAO('FollowerCountDAO');
                $follower_count_history_by_day = $follower_count_dao->getHistory($this->instance->network_user_id,
                'twitter', 'DAY', 5);
                //print_r($follower_count_history_by_day);
                $this->addToView('follower_count_history_by_day', $follower_count_history_by_day);
    
                //by week
                $follower_count_history_by_week = $follower_count_dao->getHistory($this->instance->network_user_id,
                'twitter', 'WEEK', 5);
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




