<?php
/**
 * Facebook Auth Controller
 * Save the session key for authorized Facebook accounts.
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class FacebookAuthController extends ThinkUpAuthController {
    /**
     *
     * @var boolean
     */
    var $is_missing_param = false;

    public function __construct($session_started=false) {
        parent::__construct($session_started);
        $config = Config::getInstance();
        $this->setViewTemplate($config->getValue('source_root_path').'webapp/plugins/facebook/view/auth.tpl');
        $this->setPageTitle('Authorizing Your Facebook Account');
        if (!isset($_GET['sessionKey']) || $_GET['sessionKey'] == '' ) {
            $this->addInfoMessage('No session key specified.');
            $this->is_missing_param = true;
        }
    }

    public function authControl() {
        $fb_user = null;
        $msg = '';
        $config = Config::getInstance();
        $facebook = new Facebook($config->getValue('facebook_api_key'), $config->getValue('facebook_api_secret'));

        $fb_user = $facebook->api_client->users_getLoggedInUser();
        $msg .= "Facebook user is logged in and user ID set<br />";
        $fb_username = $facebook->api_client->users_getInfo($fb_user, 'name');
        $fb_username = $fb_username[0]['name'];

        if (isset($_GET['sessionKey']) && isset($fb_user) && $fb_user > 0) {
            $session_key = $_GET['sessionKey'];

            $owner_dao = DAOFactory::getDAO('OwnerDAO');
            $instance_dao = DAOFactory::getDAO('InstanceDAO');
            $oid = DAOFactory::getDAO('OwnerInstanceDAO');
            $user_dao = DAOFactory::getDAO('UserDAO');

            $owner = $owner_dao->getByEmail($this->getLoggedInUser());

            $i = $instance_dao->getByUserIdOnNetwork($fb_user, 'facebook');
            if (isset($i)) {
                $msg .= "Instance exists<br />";
                $oi = $oid->get($owner->id, $i->id);
                if ($oi == null) { //Instance already exists, owner instance doesn't
                    $oid->insert($owner->id, $i->id, $session_key); //Add owner instance with session key
                    $msg .= "Created owner instance.<br />";
                }
            } else { //Instance does not exist
                $msg .= "Instance does not exist<br />";

                $instance_dao->insert($fb_user, $fb_username, 'facebook');
                $msg .= "Created instance";

                $i = $instance_dao->getByUserIdOnNetwork($fb_user, 'facebook');
                $oid->insert($owner->id, $i->id, $session_key);
                $msg .= "Created owner instance.<br />";
            }

            if (!$user_dao->isUserInDB($fb_user, 'facebook')) {
                $r = array('user_id'=>$fb_user, 'user_name'=>$fb_username,'full_name'=>$fb_username, 'avatar'=>'',
        'location'=>'', 'description'=>'', 'url'=>'', 'is_protected'=>'',  'follower_count'=>0, 'friend_count'=>0, 
        'post_count'=>0, 'last_updated'=>'', 'last_post'=>'', 'joined'=>'', 'last_post_id'=>'', 'network'=>'facebook' );
                $u = new User($r, 'Owner info');
                $user_dao->updateUser($u);
            }
        } else {
            $msg .= "No session key or logged in Facebook user.";
        }

        $msg .= '<br /> <a href="'.$config->getValue('site_root_path').'account/">Back to your account</a>.';
        $this->addInfoMessage($msg);
        return $this->generateView();
    }
}