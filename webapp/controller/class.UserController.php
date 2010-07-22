<?php
/**
 * User Controller
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class UserController extends ThinkTankAuthController {
    /**
     * Required query string parameters
     * @var array u = instance username, n = network
     */
    var $REQUIRED_PARAMS = array('u', 'n');

    /**
     *
     * @var boolean
     */
    var $is_missing_param = false;

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

    public function authControl() {
        if (!$this->is_missing_param) {
            $username = $_GET['u'];
            $network = $_GET['n'];
            $user_dao = DAOFactory::getDAO('UserDAO');

            if ( $user_dao->isUserInDBByName($username) ){
                $this->setPageTitle('User Details: '.$username);
                $user = $user_dao->getUserByName($username);

                $owner_dao = DAOFactory::getDAO('OwnerDAO');
                $owner = $owner_dao->getByEmail($this->getLoggedInUser());

                $instance_dao = DAOFactory::getDAO('InstanceDAO');
                $this->addToView('instances', $instance_dao->getByOwner($owner));

                $this->addToView('profile', $user);

                $post_dao = DAOFactory::getDAO('PostDAO');
                $this->addToView('user_statuses',  $post_dao->getAllPosts($user->user_id, $user->network, 20));
                $this->addToView('sources', $post_dao->getStatusSources($user->user_id, $user->network));
                if ( isset($_GET['i']) ) {
                    $i = $instance_dao->getByUsername($_GET['i'], 'twitter');
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
}
