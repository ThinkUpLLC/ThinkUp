<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.AccountConfigurationController.php
 *
 * Copyright (c) 2009-2012 Terrance Shepherd, Gina Trapani
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
 * @copyright 2009-2012 Terrance Shepherd, Gina Trapani
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

        $webapp_plugin_registrar = PluginRegistrarWebapp::getInstance();
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
                $server = $_SERVER['HTTP_HOST'];
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
                         bgcolor="#D5F0FC"
                         wmode="opaque"/></object>
                <br /> Good for one new registration. Expires in 7 days.', 'invite', true);
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
                    if ( $owner_instance_dao->doesOwnerHaveAccessToInstance($owner, $instance) ) {
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
            $pobj = $webapp_plugin_registrar->getPluginObject($active_plugin);
            $p = new $pobj;
            $this->addToView('body', $p->renderConfiguration($owner));
            $profiler = Profiler::getInstance();
            $profiler->clearLog();
        } else {
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
