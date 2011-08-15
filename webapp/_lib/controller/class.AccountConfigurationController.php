<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.AccountConfigurationController.php
 *
 * Copyright (c) 2009-2011 Terrance Shepherd, Gina Trapani
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
 * AccountConfiguration Controller
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Terrance Shepherd, Gina Trapani
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

        $webapp = Webapp::getInstance();
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $invite_dao = DAOFactory::getDAO('InviteDAO');
        $owner = $owner_dao->getByEmail($this->getLoggedInUser());
        $this->addToView('owner', $owner);
        $this->addToView('logo_link', '');
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
            } elseif (strlen($_POST['pass1']) < 5) {
                $this->addErrorMessage("New password must be at least 5 characters. ".
                "Your password has not been changed.", 'password' );
            } else {
                // verify CSRF token
                $this->validateCSRFToken();
                // Generate new unique salt and store it in the database
                $salt = $owner_dao->generateSalt($this->getLoggedInUser());
                $owner_dao->updateSalt($this->getLoggedInUser(), $salt);
                // Combine the password and salt
                $newpass = $owner_dao->generateUniqueSaltedPassword($_POST['pass1'], $salt);
                // Try to update the password
                if ($owner_dao->updatePassword($this->getLoggedInUser(), $newpass ) < 1 ) {
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
            if(! $api_key) {
                throw new Exception("Unbale to update user's api_key, something bad must have happened");
            }
            $this->addSuccessMessage("Your API Key has been reset to <strong>" . $api_key . '</strong>', 'api_key');
            $owner->api_key = $api_key;
        }

        // process invite
        if (isset($_POST['invite']) && ( $_POST['invite'] == 'Create Invitation' ) ) {
            // verify CSRF token
            $this->validateCSRFToken();
            $invite_code =  substr(md5(uniqid(rand(), true)), 0, 10) ;
            $invite_added = $invite_dao->addInviteCode( $invite_code ) ;

            if ($invite_added == 1) { //invite generated and inserted
                $server = $_SERVER['HTTP_HOST'];
                $invite_link = 'http'.(isset($_SERVER['HTTPS'])?'s':'').'://'.$server.THINKUP_BASE_URL.
                'session/register.php?code='. $invite_code;
                $this->addSuccessMessage("Invitation created!<br />Copy this link and send it to someone you want to ".
                'invite to register on your ThinkUp installation.<br /><a href="'.$invite_link.'">'.
                $invite_link.'</a><br /> Good for one new registration. Expires in 7 days.', 'invite');
            } else {
                $this->addErrorMessage("There was an error creating a new invite. Please try again.", 'invite');
            }
        }

        //process service user deletion
        if (isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['instance_id']) &&
        is_numeric($_POST['instance_id'])) {
            $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
            $instance_dao = DAOFactory::getDAO('InstanceDAO');
            $instance = $instance_dao->get($_POST['instance_id']);
            if ( isset($instance) ) {
                // verify CSRF token
                $this->validateCSRFToken();
                if ($this->isAdmin()) {
                    //delete all owner_instances
                    $owner_instance_dao->deleteByInstance($instance->id);
                    //delete instance
                    $instance_dao->delete($instance->network_username, $instance->network);
                    $this->addSuccessMessage('Account deleted.', 'account');
                } else  {
                    if ( $owner_instance_dao->doesOwnerHaveAccess($owner, $instance) ) {
                        //delete owner instance
                        $total_deletions = $owner_instance_dao->delete($owner->id, $instance->id);
                        if ( $total_deletions > 0 ) {
                            //delete instance if no other owners have it
                            $remaining_owner_instances = $owner_instance_dao->getByInstance($instance->id);
                            if (sizeof($remaining_owner_instances) == 0 ) {
                                $instance_dao->delete($instance->network_username, $instance->network);
                            }
                            $this->addSuccessMessage('Account deleted.', 'account');
                        }
                    } else {
                        $this->addErrorMessage('Insufficient privileges.', 'account');
                    }
                }
            } else {
                $this->addErrorMessage('Instance doesn\'t exist.', 'account');
            }
        }
        $this->view_mgr->clear_all_cache();

        /* Begin plugin-specific configuration handling */
        if (isset($_GET['p'])) {
            // add config js to header
            if ($this->isAdmin()) {
                $this->addHeaderJavaScript('assets/js/plugin_options.js');
            }
            $active_plugin = $_GET['p'];
            $pobj = $webapp->getPluginObject($active_plugin);
            $p = new $pobj;
            $this->addToView('body', $p->renderConfiguration($owner));
            $profiler = Profiler::getInstance();
            $profiler->clearLog();
        } else {
            $pld = DAOFactory::getDAO('PluginDAO');
            $config = Config::getInstance();
            $installed_plugins = $pld->getInstalledPlugins($config->getValue("source_root_path"));
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

        return $this->generateView();
    }
}
