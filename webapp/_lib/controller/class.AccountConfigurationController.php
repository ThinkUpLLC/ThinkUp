<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.AccountConfigurationController.php
 *
 * Copyright (c) 2009-2013 Terrance Shepherd, Gina Trapani
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
 * AccountConfiguration Controller
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Terrance Shepherd, Gina Trapani
 * @author Terrance Shepehrd
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class AccountConfigurationController extends ThinkUpAuthController {

    /**
     * Constructor
     * @param bool $session_started
     * @return AccountConfigurationController
     */
    public function __construct($session_started=false) {
        parent::__construct($session_started);
        $this->disableCaching();
        $this->setViewTemplate('account.index.tpl');
        $this->setPageTitle('Configure Your Account');
        $this->enableCSRFToken();
    }

    public function authControl() {
        $this->disableCaching();

        //passsswd reset validation
        $this->addHeaderCSS('assets/css/validate_password.css');
        $this->addHeaderJavaScript('assets/js/jquery.validate.min.js');
        $this->addHeaderJavaScript('assets/js/jquery.validate.password.js');
        $this->addHeaderJavaScript('assets/js/validate_password.js');

        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $invite_dao = DAOFactory::getDAO('InviteDAO');
        $owner = $owner_dao->getByEmail($this->getLoggedInUser());
        $this->addToView('owner', $owner);
        $this->view_mgr->addHelp('api', 'userguide/api/posts/index');
        $this->view_mgr->addHelp('application_settings', 'userguide/settings/application');
        $this->view_mgr->addHelp('users', 'userguide/settings/allaccounts');
        $this->view_mgr->addHelp('backup', 'install/backup');
        $this->view_mgr->addHelp('account', 'userguide/settings/account');

        //process password change
        if (isset($_POST['changepass']) && $_POST['changepass'] == 'Change password' && isset($_POST['oldpass'])
        && isset($_POST['pass1']) && isset($_POST['pass2'])) {

            // Check their old password is correct
            if (!$owner_dao->isOwnerAuthorized($this->getLoggedInUser(), $_POST['oldpass']) )  {
                $this->addErrorMessage("Old password does not match or empty.", 'password');
            } elseif ($_POST['pass1'] != $_POST['pass2']) {
                $this->addErrorMessage("New passwords did not match. Your password has not been changed.", 'password');
            } elseif (!preg_match("/(?=.{8,})(?=.*[a-zA-Z])(?=.*[0-9])/", $_POST['pass1'])) {
                $this->addErrorMessage("Your new password must be at least 8 characters and contain both numbers ".
                "and letters. Your password has not been changed.", 'password' );
            } else {
                // verify CSRF token
                $this->validateCSRFToken();
                // Try to update the password
                if ($owner_dao->updatePassword($this->getLoggedInUser(), $_POST['pass1'] ) < 1 ) {
                    $this->addErrorMessage("Your password has NOT been updated.", 'password');
                } else {
                    $this->addSuccessMessage("Your password has been updated.", 'password');
                }
            }
        }

        //reset api_key
        if (isset($_POST['reset_api_key']) && $_POST['reset_api_key'] == 'Reset API Key') {
            $this->validateCSRFToken();
            $api_key = $owner_dao->resetAPIKey($owner->id);
            if (!$api_key) {
                throw new Exception("Unbale to update user's api_key, something bad must have happened");
            }
            $this->addSuccessMessage("Your API Key has been reset! Please update your ThinkUp RSS feed subscription.",
            'api_key');
            $owner->api_key = $api_key;
        }

        // process invite
        if (isset($_POST['invite']) && ( $_POST['invite'] == 'Create Invitation' ) ) {
            // verify CSRF token
            $this->validateCSRFToken();
            $invite_code =  substr(md5(uniqid(rand(), true)), 0, 10) ;
            $invite_added = $invite_dao->addInviteCode( $invite_code ) ;

            if ($invite_added == 1) { //invite generated and inserted
                $invite_link = Utils::getApplicationURL().'session/register.php?code='. $invite_code;
                $this->addSuccessMessage("Invitation created!<br />Copy this link and send it to someone you want to ".
                'invite to register on your ThinkUp installation.<br /><a href="'.$invite_link.'" id="clippy_12345">'.
                $invite_link.'</a>
                  <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"
                          width="100"
                          height="14"
                          class="clippy"
                          id="clippy" >
                  <param name="movie" value="'.Utils::getApplicationURL().'assets/flash/clippy.swf"/>
                  <param name="allowScriptAccess" value="always" />
                  <param name="quality" value="high" />
                  <param name="scale" value="noscale" />
                  <param NAME="FlashVars" value="id=clippy_12345&amp;copied=copied!&amp;copyto=copy to clipboard">
                  <param name="bgcolor" value="#D5F0FC">
                  <param name="wmode" value="opaque">
                  <embed src="'.Utils::getApplicationURL().'assets/flash/clippy.swf"
                         width="100"
                         height="14"
                         name="clippy"
                         quality="high"
                         allowScriptAccess="always"
                         type="application/x-shockwave-flash"
                         pluginspage="http://www.macromedia.com/go/getflashplayer"
                         FlashVars="id=clippy_12345&amp;copied=copied!&amp;copyto=copy to clipboard"
                         bgcolor="#dff0d8"
                         wmode="opaque"/></object>
                <br /> Good for one new registration. Expires in 7 days.', 'invite', true);
            } else {
                $this->addErrorMessage("There was an error creating a new invite. Please try again.", 'invite');
            }
        }

        //process service user deletion
        if (isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['instance_id']) &&
        is_numeric($_POST['instance_id']) && !isset($_POST['hashtag_id']) && !isset($_POST['new_hashtag_name'])) {
            $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
            $instance_dao = DAOFactory::getDAO('InstanceDAO');
            $instance_hashtag_dao = DAOFactory::getDAO('InstanceHashtagDAO');
            $hashtag_dao = DAOFactory::getDAO('HashtagDAO');
            $instance = $instance_dao->get($_POST['instance_id']);
            $message='';
            if ( isset($instance) ) {
                // verify CSRF token
                $this->validateCSRFToken();
                if ($this->isAdmin()) {                    
                    //Retrieve for this instance the hashtags to delete
                    $instances_hashtags = $instance_hashtag_dao->getByInstance($instance->id);
                    foreach ($instances_hashtags as $instance_hashtag) {
                        $hashtag_id = $instance_hashtag->hashtag_id;                  
                        $hpd= $hashtag_dao->deleteHashtagsPostsByHashtagId($hashtag_id);
                        $ihd = $instance_hashtag_dao->deleteInstancesHashtagsByHashtagId($hashtag_id);
                        $hd = $hashtag_dao->deleteHashtagByHashtagId($hashtag_id);
                        $message .= "Hashtag deleted = " . $hashtag_id . "(". $hpd . "," . $ihd .",". $hd .").";                   
                    }
                    //delete all owner_instances
                    $owner_instance_dao->deleteByInstance($instance->id);
                    //delete instance
                    $instance_dao->delete($instance->network_username, $instance->network);
                    $this->addSuccessMessage('Account deleted.' . $message, 'account');

                } else  {
                    if ( $owner_instance_dao->doesOwnerHaveAccessToInstance($owner, $instance) ) {
                        //delete owner instance
                        $total_deletions = $owner_instance_dao->delete($owner->id, $instance->id);
                        if ( $total_deletions > 0 ) {
                            //delete instance if no other owners have it
                            $remaining_owner_instances = $owner_instance_dao->getByInstance($instance->id);
                            if (sizeof($remaining_owner_instances) == 0 ) {
                                //Retrieve for this instance the hashtags to delete
                                $instances_hashtags = $instance_hashtag_dao->getByInstance($instance->id);
                                foreach ($instances_hashtags as $instance_hashtag) {
                                    $hashtag_id = $instance_hashtag->hashtag_id;                  
                                    $hpd= $hashtag_dao->deleteHashtagsPostsByHashtagId($hashtag_id);
                                    $ihd = $instance_hashtag_dao->deleteInstancesHashtagsByHashtagId($hashtag_id);
                                    $hd = $hashtag_dao->deleteHashtagByHashtagId($hashtag_id);
                                    $message .= "Hashtag deleted = " . $hashtag_id . "(". $hpd . "," . $ihd .",". 
                                        $hd .").";                   
                                }
                                $instance_dao->delete($instance->network_username, $instance->network);
                            }
                            $this->addSuccessMessage('Account deleted.'. $message, 'account');
                        }
                    } else {
                        $this->addErrorMessage('Insufficient privileges.', 'account');
                    }
                }
            } else {
                $this->addErrorMessage('Instance doesn\'t exist.', 'account');
            }
        }
        
        //process service user hashtag deletion
        if (isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['hashtag_id']) &&
                is_numeric($_POST['hashtag_id']) && isset($_POST['instance_id']) &&
                is_numeric($_POST['instance_id'])) {                     
                    
            $instance_hashtag_dao = DAOFactory::getDAO('InstanceHashtagDAO');
            $hashtag_dao = DAOFactory::getDAO('HashtagDAO');
            
            $hashtag_id = $_POST['hashtag_id'];
            $instance_id = $_POST['instance_id'];
            
            $instance_dao = DAOFactory::getDAO('InstanceDAO');
            $instance = $instance_dao->get($instance_id);
            if ( isset($instance) ) {
                $hashtags_posts_deleted = $hashtag_dao->deleteHashtagsPostsByHashtagId($hashtag_id);
                $instances_hashtags_deleted = $instance_hashtag_dao->deleteInstancesHashtagsByHashtagId($hashtag_id);
                $hashtags_deleted = $hashtag_dao->deleteHashtagByHashtagId($hashtag_id);                    
                $message = "Hashtags posts deleted = " . $hashtags_posts_deleted;
                $message .= ". Instances hashtags deleted = " . $instances_hashtags_deleted;
                $message .= ". Hashtag deleted = " . $hashtags_deleted;
                $this->addSuccessMessage($message,'account');
            } else {
                $this->addErrorMessage('Instance doesn\'t exist.','account');
            }
        }
        
        //process service user hashtag addition
        if (isset($_POST['action']) && $_POST['action'] == 'Save search' 
                && isset($_POST['new_hashtag_name']) && $_POST['new_hashtag_name']<>'' 
                && isset($_POST['instance_id']) && is_numeric($_POST['instance_id'])) {

            $instance_hashtag_dao = DAOFactory::getDAO('InstanceHashtagDAO');
            $hashtag_dao = DAOFactory::getDAO('HashtagDAO');
        
            $instance_id = $_POST['instance_id'];
            $new_hashtag_name=$_POST['new_hashtag_name'];                    

            $instance_dao = DAOFactory::getDAO('InstanceDAO');
            $instance = $instance_dao->get($instance_id);
            if ( isset($instance) ) {
                $hashtag = $hashtag_dao->getByHashtagName($new_hashtag_name);
                if (!isset($hashtag)) {
                    $hashtag_id = $hashtag_dao->insertHashtagByHashtagName($new_hashtag_name);
                    $row_inserted = $instance_hashtag_dao->insert($instance_id,$hashtag_id);
                    $message = "Hashtag " . $new_hashtag_name . " inserted with id " . $hashtag_id;
                    $message .= ". " . $row_inserted . " row inserted for relationship with instance "; 
                    $message .= $instance_id;
                    $this->addSuccessMessage($message,'account');
                }else {
                    $hashtag_id = $hashtag->id;
                    $message = "Hashtag " . $new_hashtag_name . " exist with id " . $hashtag_id;
                    $message .= " for other instance. Can't add it !! ";                        
                    $this->addErrorMessage($message,'account');
                }                   
            } else {
                $this->addErrorMessage('Instance doesn\'t exist.','account');
            }
        }        
        $this->view_mgr->clear_all_cache();

        /* Begin plugin-specific configuration handling */
        if (isset($_GET['p']) && !isset($_GET['u'])) {
            // add config js to header
            if ($this->isAdmin()) {
                $this->addHeaderJavaScript('assets/js/plugin_options.js');
            }
            $active_plugin = $_GET['p'];
            $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
            $pobj = $webapp_plugin_registrar->getPluginObject($active_plugin);
            $p = new $pobj;
            $this->addToView('body', $p->renderConfiguration($owner));
            $profiler = Profiler::getInstance();
            $profiler->clearLog();
        } elseif (isset($_GET['p']) && isset($_GET['u'])) {
            if ($this->isAdmin()) {
                $this->addHeaderJavaScript('assets/js/plugin_options.js');
            }
            $active_plugin = $_GET['p'];
            $active_user = $_GET['u'];
            $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
            $pobj = $webapp_plugin_registrar->getPluginObject($active_plugin);
            $p = new $pobj;
            $this->addToView('body', $p->renderHashtagConfiguration($owner,$active_user));
            $profiler = Profiler::getInstance();
            $profiler->clearLog();
        } 
        else {
            $plugin_dao = DAOFactory::getDAO('PluginDAO');
            $config = Config::getInstance();
            $installed_plugins = $plugin_dao->getInstalledPlugins();
            $this->addToView('installed_plugins', $installed_plugins);
        }
        /* End plugin-specific configuration handling */

        if ($owner->is_admin) {
            if (!isset($instance_dao)) {
                $instance_dao = DAOFactory::getDAO('InstanceDAO');
            }
            $owners = $owner_dao->getAllOwners();
            foreach ($owners as $o) {
                $instances = $instance_dao->getByOwner($o, true);
                $o->setInstances($instances);
            }
            $this->addToView('owners', $owners);
            $this->addToView('public_instances', $instance_dao->getPublicInstances());
        }

        $whichphp = @exec('which php');
        $php_path =  (!empty($whichphp))?$whichphp:'php';
        $email = $this->getLoggedInUser();

        //rss_crawl_url
        $rss_crawl_url = Utils::getApplicationURL(). sprintf('crawler/rss.php?un=%s&as=%s', urlencode($email),
        $owner->api_key);
        $this->addToView('rss_crawl_url', $rss_crawl_url);
        //cli_crawl_command
        $cli_crawl_command = 'cd '.THINKUP_WEBAPP_PATH.'crawler/;export THINKUP_PASSWORD=yourpassword; '.$php_path.
        ' crawl.php '.$email;
        $this->addToView('cli_crawl_command', $cli_crawl_command);
        //help link
        $this->view_mgr->addHelp('rss', 'userguide/datacapture');
        return $this->generateView();
    }
}
