UserController
==============
Inherits from `ThinkUpAuthController <./ThinkUpAuthController.html>`_.

ThinkUp/webapp/_lib/controller/class.UserController.php

Copyright (c) 2009-2011 Gina Trapani

User Controller


Properties
----------

REQUIRED_PARAMS
~~~~~~~~~~~~~~~

Required query string parameters

is_missing_param
~~~~~~~~~~~~~~~~





Methods
-------

__construct
~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function __construct($session_started=false) {
            parent::__construct($session_started);
            $this->setViewTemplate('user.index.tpl');
            foreach ($this->REQUIRED_PARAMS as $param) {
                if (!isset($_GET[$param]) || $_GET[$param] == '' ) {
                    $this->addInfoMessage('User and network not specified.');
                    $this->is_missing_param = true;
                }
            }
        }


authControl
~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function authControl() {
            if (!$this->is_missing_param) {
                $username = $_GET['u'];
                $network = $_GET['n'];
                $user_dao = DAOFactory::getDAO('UserDAO');
                $page = (isset($_GET['page']) && is_numeric($_GET['page']))?$_GET['page']:1;
    
                if ( $user_dao->isUserInDBByName($username, $network) ){
                    $this->setPageTitle('User Details: '.$username);
                    $user = $user_dao->getUserByName($username, $network);
    
                    $owner_dao = DAOFactory::getDAO('OwnerDAO');
                    $owner = $owner_dao->getByEmail($this->getLoggedInUser());
    
                    $instance_dao = DAOFactory::getDAO('InstanceDAO');
                    $this->addToView('instances', $instance_dao->getByOwner($owner));
    
                    $this->addToView('profile', $user);
    
                    $post_dao = DAOFactory::getDAO('PostDAO');
                    $user_posts = $post_dao->getAllPosts($user->user_id, $user->network, 20, $page);
                    $this->addToView('user_statuses',  $user_posts );
                    if (sizeof($user_posts) == 20) {
                        $this->addToView('next_page', $page+1);
                    }
                    $this->addToView('last_page', $page-1);
    
                    $this->addToView('sources', $post_dao->getStatusSources($user->user_id, $user->network));
                    if (SessionCache::isKeySet('selected_instance_username') &&
                    SessionCache::isKeySet('selected_instance_network')) {
                        $i = $instance_dao->getByUsername(SessionCache::get('selected_instance_username'),
                        SessionCache::get('selected_instance_network'));
                        if (isset($i)) {
                            $this->addToView('instance', $i);
                            $exchanges =  $post_dao->getExchangesBetweenUsers($i->network_user_id, $i->network,
                            $user->user_id);
                            $this->addToView('exchanges', $exchanges);
                            $this->addToView('total_exchanges', count($exchanges));
    
                            $follow_dao = DAOFactory::getDAO('FollowDAO');
    
                            $mutual_friends = $follow_dao->getMutualFriends($user->user_id, $i->network_user_id,
                            $i->network);
                            $this->addToView('mutual_friends', $mutual_friends);
                            $this->addToView('total_mutual_friends', count($mutual_friends) );
                        }
                    }
                } else {
                    $this->addErrorMessage($username. ' is not in the system.');
                }
            }
            return $this->generateView();
        }




