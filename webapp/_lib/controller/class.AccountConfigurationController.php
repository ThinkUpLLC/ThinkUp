<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.AccountConfigurationController.php
 *
 * Copyright (c) 2009-2011 Gina Trapani
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
 * @copyright 2009-2011 Gina Trapani
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
    }

    public function authControl() {
        $webapp = Webapp::getInstance();
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail($this->getLoggedInUser());
        $this->addToView('owner', $owner);

        //proces password change
        if (isset($_POST['changepass']) && $_POST['changepass'] == 'Change password' && isset($_POST['oldpass'])
        && isset($_POST['pass1']) && isset($_POST['pass2'])) {
            $origpass = $owner_dao->getPass($this->getLoggedInUser());
            if (!$this->app_session->pwdCheck($_POST['oldpass'], $origpass)) {
                $this->addErrorMessage("Old password does not match or empty.");
            } elseif ($_POST['pass1'] != $_POST['pass2']) {
                $this->addErrorMessage("New passwords did not match. Your password has not been changed.");
            } elseif (strlen($_POST['pass1']) < 5) {
                $this->addErrorMessage("New password must be at least 5 characters. ".
                "Your password has not been changed." );
            } else {
                $cryptpass = $this->app_session->pwdcrypt($_POST['pass1']);
                $owner_dao->updatePassword($this->getLoggedInUser(), $cryptpass);
                $this->addSuccessMessage("Your password has been updated.");
            }
        }

        //process invite
	if (isset($_POST['invite']) && ( $_POST['invite'] == 'Invite' ) && isset($_POST['full_name']) && isset($_POST['email']) ) {
		if (!Utils::validateEmail($_POST['email'])) {
			$this->addErrorMessage("Incorrect email. Please enter valid email address.");
		} else { 
			if ($owner_dao->doesOwnerExist($_POST['email'])) {
				$this->addErrorMessage("User account already exists.");
			} else {
			        $config = Config::getInstance() ;
				$es = new SmartyThinkUp();
				$es->caching=false;
				$session = new Session();
				$activ_code = rand(1000, 9999);
				// Generate a temporary password
				$password =  substr(md5(uniqid(rand(), true)), 0, 10) ;
				$cryptpass = $session->pwdcrypt($password);
				$server = $_SERVER['HTTP_HOST'];
				$owner_dao->create($_POST['email'], $cryptpass, $activ_code, $_POST['full_name']) ;

				$es->assign('server', $server );
				$es->assign('email', urlencode($_POST['email']) );
				$es->assign('activ_code', $activ_code );
				$es->assign('password', $password ) ;
				$message = $es->fetch('_email.invite.tpl');

				// Check if mail button is clicked
				Mailer::mail($_POST['email'], "Activate Your ".$config->getValue('app_title')
				." Account", $message);

				unset($_SESSION['ckey']);
				$this->addSuccessMessage("Success! Invitation Sent.");
			}
		}
	}

        //process account deletion
        if (isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['instance_id']) &&
        is_numeric($_POST['instance_id'])) {
            $owner_instance_dao = DAOFactory::getDAO('OwnerInstanceDAO');
            $instance_dao = DAOFactory::getDAO('InstanceDAO');
            $instance = $instance_dao->get($_POST['instance_id']);
            if ( isset($instance) ) {
                if ($this->isAdmin()) {
                    //delete all owner_instances
                    $owner_instance_dao->deleteByInstance($instance->id);
                    //delete instance
                    $instance_dao->delete($instance->network_username, $instance->network);
                    $this->addSuccessMessage('Account deleted.');
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
                            $this->addSuccessMessage('Account deleted.');
                        }
                    } else {
                        $this->addErrorMessage('Insufficient privileges.');
                    }
                }
            } else {
                $this->addErrorMessage('Instance doesn\'t exist.');
            }
        }
        $this->view_mgr->clear_all_cache();

        /* Begin plugin-specific configuration handling */
        if (isset($_GET['p'])) {
            // add config js to header
            if($this->isAdmin()) {
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
        }

        return $this->generateView();
    }
}
