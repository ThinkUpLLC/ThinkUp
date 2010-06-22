<?php
/**
 * AccountConfiguration Controller
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class AccountConfigurationController extends ThinkTankAuthController {

    /**
     * Constructor
     * @param bool $session_started
     * @return AccountConfigurationController
     */
    public function __construct($session_started=false) {
        parent::__construct($session_started);
        $this->disableCaching();
        $this->setViewTemplate('account.index.tpl');
        $this->addToView('controller_title', 'Configure Your Account');
    }

    public function authControl() {
        $webapp = Webapp::getInstance();
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail($this->getLoggedInUser());
        $this->addToView('owner', $owner);

        /* Begin plugin-specific configuration handling */
        if (isset($_GET['p'])) {
            $this->addToViewCacheKey($_GET['p']);
            $active_plugin = $_GET['p'];
            $pobj = $webapp->getPluginObject($active_plugin);
            $p = new $pobj;
            $this->addToView('body', $p->renderConfiguration($owner));
            $profiler = Profiler::getInstance();
            $profiler->clearLog();
        } else {
            global $db; //@TODO remove this when PDO port is complete
            $pld = new PluginDAO($db);
            $config = Config::getInstance();
            $installed_plugins = $pld->getInstalledPlugins($config->getValue("source_root_path"));
            $this->addToView('installed_plugins', $installed_plugins);
        }
        /* End plugin-specific configuration handling */

        if (isset($_POST['changepass']) && $_POST['changepass'] == 'Change password' && isset($_POST['oldpass'])
        && isset($_POST['pass1']) && isset($_POST['pass2'])) {
            $originalpass = $owner_dao->getPass($this->getLoggedInUser());
            $origpass = $originalpass['pwd'];
            if (!$this->app_session->pwdCheck($_POST['oldpass'], $origpass)) {
                $this->addToView('errormsg', "Old password does not match or empty.");
            } elseif ($_POST['pass1'] != $_POST['pass2']) {
                $this->addToView('errormsg',"New passwords did not match. Your password has not been changed.");
            } elseif (strlen($_POST['pass1']) < 5) {
                $this->addToView('errormsg',"New password must be at least 5 characters. ".
                "Your password has not been changed." );
            } else {
                $cryptpass = $this->app_session->pwdcrypt($_POST['pass1']);
                $owner_dao->updatePassword($this->getLoggedInUser(), $cryptpass);
                $this->addToView('successmsg', "Your password has been updated.");
            }
        }

        if ($owner->is_admin) {
            $instance_dao = DAOFactory::getDAO('InstanceDAO');
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
