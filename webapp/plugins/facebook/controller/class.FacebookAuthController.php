<?php
/**
 *
 * ThinkUp/webapp/plugins/facebook/controller/class.FacebookAuthController.php
 *
 * Copyright (c) 2009-2010 Gina Trapani
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
 * Facebook Auth Controller
 * Save the session key for authorized Facebook accounts.
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2010 Gina Trapani
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
        Utils::defineConstants();
        $this->setViewTemplate(THINKUP_WEBAPP_PATH.'plugins/facebook/view/auth.tpl');
        $this->setPageTitle('Authorizing Your Facebook Account');
        if (!isset($_GET['sessionKey']) || $_GET['sessionKey'] == '' ) {
            $this->addErrorMessage('No session key specified.');
            $this->is_missing_param = true;
        }
    }

    public function authControl() {
        if (!$this->is_missing_param) {
            $fb_user = null;
            $msg = '';
            $plugin_option_dao = DAOFactory::GetDAO('PluginOptionDAO');
            $options = $plugin_option_dao->getOptionsHash('facebook', true); //get cached

            $facebook = new Facebook($options['facebook_api_key']->option_value,
            $options['facebook_api_secret']->option_value);

            $fb_user = $facebook->api_client->users_getLoggedInUser();
            $msg .= "Facebook user is logged in and user ID set<br />";
            $fb_username = $facebook->api_client->users_getInfo($fb_user, 'name');

            if (isset($_GET['sessionKey']) && isset($fb_user) && $fb_user > 0) {
                $fb_username = $fb_username[0]['name'];
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

            $config = Config::getInstance();
            $msg .= '<br /> <a href="'.$config->getValue('site_root_path').'account/">Back to your account</a>.';
            $this->addInfoMessage($msg);
        }
        return $this->generateView();
    }
}