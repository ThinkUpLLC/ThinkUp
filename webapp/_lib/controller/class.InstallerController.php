<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.InstallerController.php
 *
 * Copyright (c) 2009-2013 Dwi Widiastuti, Gina Trapani
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
 * Installer Controller
 * Web-based application installer.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Dwi Widiastuti, Gina Trapani
 * @author Dwi Widiastuti <admin[at]diazuwi[dot]web[dot]id>
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 */
class InstallerController extends ThinkUpController {
    /**
     * Installer
     *
     * @var Installer
     */
    private $installer;
    /**
     * Pre-set requirements settings for testing only.
     * @var array
     */
    private $reqs;

    public function __construct($session_started=false, $reqs=null) {
        //Explicitly set TZ (before we have user's choice) to avoid date() warning about using system settings
        Utils::setDefaultTimezonePHPini();
        Loader::definePathConstants();
        //Don't call parent constructor because config.inc.php doesn't exist yet
        //Instead, set up the view manager with manual array configuration
        $cfg_array =  array(
            'site_root_path'=>Utils::getSiteRootPathFromFileSystem(),
            'source_root_path'=>THINKUP_ROOT_PATH,
            'datadir_path'=>THINKUP_WEBAPP_PATH.'data/',
            'debug'=>false,
            'app_title_prefix'=>'',
            'cache_pages'=>false);
        $this->view_mgr = new ViewManager($cfg_array);
        $this->setPageTitle('Install ThinkUp');
        $this->disableCaching();
        $this->reqs = $reqs;
    }

    public function control() {
        $this->installer = Installer::getInstance();

        if (@$_GET['step'] != 'repair') {
            $this->checkForExistingInstallation();
        }

        //route user to the right step
        if (!isset($_GET['step']) || $_GET['step'] == '1') {
            $this->step1();
        } elseif ($_GET['step'] == '2') {
            $this->step2();
        } elseif ($_GET['step'] == '3') {
            $this->step3();
        } elseif ($_GET['step'] == 'repair') {
            $repair_what = (isset($_GET['m']))?$_GET['m']:'';
            $this->repairInstallation($repair_what);
        } else {
            $this->step1();
        }
        return $this->generateView();
    }

    /**
     * Check for existing installation and throw appropriate exception if so.
     * @throws InstallerException
     */
    private function checkForExistingInstallation() {
        //if config file exists, check if ThinkUp is installed
        if ( file_exists( THINKUP_WEBAPP_PATH . 'config.inc.php' ) &&
        filesize( THINKUP_WEBAPP_PATH . 'config.inc.php' ) > 0 ) {
            require THINKUP_WEBAPP_PATH . 'config.inc.php';
            if ( $this->installer->isThinkUpInstalled($THINKUP_CFG) && $this->installer->checkPath($THINKUP_CFG) ) {
                // ThinkUp is installed, but check at least one admin owner exists, if not, let user know
                $owner_dao = DAOFactory::getDAO('OwnerDAO');
                $msg = '';
                if ( !$owner_dao->doesAdminExist() ) { // create admin if not exists
                    $msg = "However, there is no administrator set up for this installation.<br />Make sure at least ".
                    "one user in the owners table has its is_admin field set to 1.<br />";
                }
                throw new InstallerException(
                'ThinkUp is already installed!<br /> '.$msg.'<br />To reinstall ThinkUp from scratch, delete your '.
                'config.inc.php file and reload this page.<br /> Otherwise, start <a href="'.
                Utils::getSiteRootPathFromFileSystem(). '">using ThinkUp</a>.', Installer::ERROR_INSTALL_COMPLETE);
            }
            //if we're not in repair mode, check to see if some tables exist, and if so, let user know via Exception
            if (!isset($_GET["step"]) || $_GET['step'] != 'repair') {
                $this->installer->checkTable($THINKUP_CFG);
            }
        }
        // clear error messages after called isThinkUpInstalled successfully
        $this->installer->clearErrorMessages();
    }

    /**
     * Step 1 - Check system requirements
     */
    private function step1() {
        $this->setViewTemplate('install.step1.tpl');

        // php version check
        $php_compat = false;
        if ( $this->installer->checkVersion() ) {
            $php_compat = true;
        }
        $this->addToView('php_compat', $php_compat);
        $requiredVersion = $this->installer->getRequiredVersion();
        $this->addToView('php_required_version', $requiredVersion['php']);

        // libs check
        if (isset($this->reqs)) { //testing only
            $libs = $this->installer->checkDependency($this->reqs);
        } else {
            $libs = $this->installer->checkDependency();
        }
        $libs_compat = true;
        foreach ($libs as $lib) {
            if (!$lib) {
                $libs_compat = false;
            }
        }
        $this->addToView('libs', $libs);

        // path permissions check
        $permissions = $this->installer->checkPermission();
        $this->addToView('permission', $permissions);
        $permissions_compat = true;
        foreach ($permissions as $perm) {
            if (!$perm) {
                $permissions_compat = false;
            }
        }
        $this->addToView('permissions_compat', $permissions_compat);
        $this->addToView('writable_data_directory', FileDataManager::getDataPath());

        // session save path permissions check
        $session_permissions_compat = $this->installer->isSessionDirectoryWritable();
        $this->addToView('session_permissions_compat', $session_permissions_compat);
        $this->addToView('writable_session_save_directory', ini_get('session.save_path'));

        // other vars set to view
        $requirements_met = ($php_compat && $libs_compat && $permissions_compat && $session_permissions_compat);
        $this->addToView('requirements_met', $requirements_met);
        $this->addToView('subtitle', 'Check System Requirements');

        //If all requirements are met, go to step 2
        if ($requirements_met) {
            $this->addSuccessMessage("<strong>Great!</strong> Your system has everything it needs to run ThinkUp.",
            null, true);
            $this->step2();
        }
    }

    /**
     * Step 2 - Set up database and site configuration
     */
    private function step2() {
        $this->setViewTemplate('install.step2.tpl');
        $this->addHeaderJavaScript('assets/js/jstz-1.0.4.min.js');
        $this->addHeaderJavaScript('assets/js/jqBootstrapValidation.js');
        $this->addHeaderJavaScript('assets/js/validate-fields.js');

        // make sure we have passed step 1
        if ( !$this->installer->checkStep1() ) {
            $this->step1();
            return;
        }

        $current_tz = isset($_POST['timezone']) ? $_POST['timezone'] : '';

        $this->addToView('db_name', '');
        $this->addToView('db_user', '');
        $this->addToView('db_passwd', '');
        $this->addToView('db_host', '');
        $this->addToView('db_prefix', 'tu_');
        $this->addToView('db_socket', '');
        $this->addToView('db_port', '');
        $this->addToView('tz_list', $this->getTimeZoneList());
        $this->addToView('current_tz', $current_tz);
        $this->addToView('site_email', '');
    }

    /**
     * Step 3 - Populate database and finish
     */
    private function step3() {
        $this->setViewTemplate('install.step3.tpl');

        $config_file_exists = false;
        $config_file = THINKUP_WEBAPP_PATH . 'config.inc.php';

        // make sure we are here with posted data
        if ( empty($_POST) ) {
            $this->step1();
            return;
        }

        // check if we have made config.inc.php
        if ( file_exists($config_file) && filesize($config_file) > 0 ) {
            // this is could be from step 2 is not able writing
            // to webapp dir
            $config_file_exists = true;
            require $config_file;
            $db_config['db_type']      = $THINKUP_CFG['db_type'];
            $db_config['db_name']      = $THINKUP_CFG['db_name'];
            $db_config['db_user']      = $THINKUP_CFG['db_user'];
            $db_config['db_password']  = $THINKUP_CFG['db_password'];
            $db_config['db_host']      = $THINKUP_CFG['db_host'];
            $db_config['db_socket']    = $THINKUP_CFG['db_socket'];
            $db_config['db_port']      = $THINKUP_CFG['db_port'];
            $db_config['table_prefix'] = $THINKUP_CFG['table_prefix'];
            $db_config['timezone']     = $THINKUP_CFG['timezone'];
            $email                     = trim($_POST['site_email']);
        } else {
            // make sure we're not from error or couldn't write config.inc.php
            if ( !isset($_POST['db_user']) && !isset($_POST['db_passwd']) && !isset($_POST['db_name']) &&
            !isset($_POST['db_host']) ) {
                $this->addErrorMessage("Missing database credentials");
                $this->step2();
                return;
            }

            // trim each posted value
            $db_config['db_type']      = trim(@$_POST['db_type']);
            $db_config['db_name']      = trim($_POST['db_name']);
            $db_config['db_user']      = trim($_POST['db_user']);
            $db_config['db_password']  = trim($_POST['db_passwd']);
            $db_config['db_host']      = trim($_POST['db_host']);
            $db_config['db_socket']    = trim($_POST['db_socket']);
            $db_config['db_port']      = trim($_POST['db_port']);
            $db_config['table_prefix'] = trim($_POST['db_prefix']);
            $db_config['timezone']     = trim($_POST['timezone']);
            $email                     = trim($_POST['site_email']);
        }
        $db_config['db_type'] = 'mysql'; //default for now
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $full_name = $_POST['full_name'];
        $display_errors = false;

        // check email
        if ( !Utils::validateEmail($email) ) {
            $this->addErrorMessage("Please enter a valid email address.", "email");
            $display_errors = true;
        }

        if ( $password != $confirm_password || $password == ''
        || !preg_match("/(?=.{8,})(?=.*[a-zA-Z])(?=.*[0-9])/", $password) ) { //check password
            if ($password != $confirm_password) {
                $this->addErrorMessage("Your passwords did not match.", "password");
            } else if ( $password == '' ) {
                $this->addErrorMessage("Please choose a password.", "password");
            } else if ( !preg_match("/(?=.{8,})(?=.*[a-zA-Z])(?=.*[0-9])/", $password) ) {
                $this->addErrorMessage("Password must be at least 8 characters and contain both numbers and letters.",
                "password");
            }
            $display_errors = true;
        }

        if ($_POST['db_name'] == '') {
            $this->addErrorMessage("Please enter a database name.", "database_name");
            $display_errors = true;
        }

        if ( $_POST['db_host'] == '') {
            $this->addErrorMessage("Please enter a database host.", "database_host");
            $display_errors = true;
        }

        if ($_POST['timezone'] == '') {
            $this->addErrorMessage("Please select a time zone.", "timezone");
            $display_errors = true;
        }

        if (($error = $this->installer->checkDb($db_config)) !== true) { //check db
            if (($p = strpos($error->getMessage(), "Unknown MySQL server host")) !== false ||
            ($p = strpos($error->getMessage(), "Can't connect to MySQL server")) !== false ||
            ($p = strpos($error->getMessage(), "Can't connect to local MySQL server through socket")) !== false ||
            ($p = strpos($error->getMessage(), "Access denied for user")) !== false) {
                $db_error = substr($error->getMessage(), $p);
            } else {
                $db_error = $error->getMessage();
            }
            $disable_xss = true;
            $db_error = filter_var($db_error, FILTER_SANITIZE_SPECIAL_CHARS);
            $this->addErrorMessage("ThinkUp couldn't connect to your database. The error message is:<br /> ".
            " <strong>$db_error</strong><br />Please correct your database information and try again.",
            "database", $disable_xss);
            $display_errors = true;
        }

        if ( $display_errors ) {
            $this->setViewTemplate('install.step2.tpl');
            $this->addToView('db_name', $db_config['db_name']);
            $this->addToView('db_user', $db_config['db_user']);
            $this->addToView('db_passwd', $db_config['db_password']);
            $this->addToView('db_host', $db_config['db_host']);
            $this->addToView('db_prefix', $db_config['table_prefix']);
            $this->addToView('db_socket', $db_config['db_socket']);
            $this->addToView('db_port', $db_config['db_port']);
            $this->addToView('db_type', $db_config['db_type']);
            $this->addToView('current_tz', $_POST['timezone']);
            $this->addToView('tz_list', $this->getTimeZoneList());
            $this->addToView('site_email', $email);
            $this->addToView('full_name', $full_name);
            return;
        }

        $admin_user = array('email' => $email, 'password' => $password, 'confirm_password' => $confirm_password);
        // trying to create config file
        if (!$config_file_exists && !$this->installer->createConfigFile($db_config, $admin_user) ) {
            $config_file_contents_arr = $this->installer->generateConfigFile($db_config, $admin_user);
            $config_file_contents_str = '';
            foreach ($config_file_contents_arr as $line) {
                $config_file_contents_str .= htmlentities($line);
            }
            $whoami = @exec('whoami');
            $disable_xss = true;
            if (!empty($whoami)) {
                $whoami = filter_var($whoami, FILTER_SANITIZE_SPECIAL_CHARS);
                $this->addErrorMessage("ThinkUp couldn't write the <code>config.inc.php</code> file.<br /><br />".
                "Use root (or sudo) to create the file manually, and allow PHP to write to it, by executing the ".
                "following commands:<br /><code>sudo touch " . escapeshellcmd(THINKUP_WEBAPP_PATH . "config.inc.php") .
                "</code><br /><code>sudo chown $whoami " . escapeshellcmd(THINKUP_WEBAPP_PATH .
                "config.inc.php") ."</code><br /><br />If you don't have root access, create the <code>" .
                THINKUP_WEBAPP_PATH . "config.inc.php</code> file, show the contents of your config file below," .
                " and copy and paste the text into the <code>config.inc.php</code> file.",
                null, $disable_xss);
            } else {
                $this->addErrorMessage("ThinkUp couldn't write the <code>config.inc.php</code> file.<br /><br />".
                "You will need to create the <code>" .
                THINKUP_WEBAPP_PATH . "config.inc.php</code> file manually, and paste the following text into it.",
                null, $disable_xss);
            }
            $this->addToView('config_file_contents', $config_file_contents_str );
            $this->addToView('_POST', $_POST);

            $this->setViewTemplate('install.config.tpl');
            return;
        }
        unset($admin_user['confirm_password']);

        // check tables
        $this->installer->checkTable($db_config);

        // if empty, we're ready to populate the database with ThinkUp tables
        $this->installer->populateTables($db_config);

        //Set the application server name in app settings for access by command-line scripts
        Installer::storeServerName();

        $owner_dao = DAOFactory::getDAO('OwnerDAO', $db_config);
        if ( !$owner_dao->doesAdminExist() && !$owner_dao->doesOwnerExist($email)) { // create admin if not exists
            $activation_code = $owner_dao->createAdmin($email, $password, $full_name);
            // view for email
            $cfg_array =  array(
            'site_root_path'=>Utils::getSiteRootPathFromFileSystem(),
            'source_root_path'=>THINKUP_ROOT_PATH,
            'debug'=>false,
            'app_title_prefix'=>"",
            'cache_pages'=>false);
            $email_view = new ViewManager($cfg_array);
            $email_view->caching=false;
            $email_view->assign('application_url', Utils::getApplicationURL() );
            $email_view->assign('email', urlencode($email) );
            $email_view->assign('activ_code', $activation_code );
            $message = $email_view->fetch('_email.registration.tpl');

            Mailer::mail($email, "Activate Your New ThinkUp  Account", $message);
        } else {
            $email = 'Use your old email admin';
            $password = 'Use your old password admin';
        }
        unset($THINKUP_CFG);

        $this->addToView('errors', $this->installer->getErrorMessages() );
        $this->addToView('username', $email);
        $this->addToView('password', $password);
        $this->addToView('login_url', Utils::getSiteRootPathFromFileSystem() . 'session/login.php');
    }

    /**
     * Repair ThinkUp installation
     *
     * @param str $to_repair
     */
    private function repairInstallation($to_repair) {
        $this->setViewTemplate('install.repair.tpl');

        // check requirements on step #1
        $this->installer->repairerCheckStep1();

        // check file configuration
        $config_file = $this->installer->repairerCheckConfigFile();
        require $config_file;

        // check database
        $this->installer->checkDb($THINKUP_CFG);

        // check $THINKUP_CFG['repair'] is set to true
        // bypass this security check when running tests
        if ( defined('TESTS_RUNNING') && TESTS_RUNNING ) {
            $THINKUP_CFG['repair'] = true;
        }
        $this->installer->repairerIsDefined($THINKUP_CFG);

        // clear error messages before doing the repair
        $this->installer->clearErrorMessages();

        $info = '';
        // do repairing when form is posted and $_GET is not empty
        if ( isset($_POST['repair']) && !empty($_GET) ) {
            $this->addToView('posted', true);
            $succeed = false;
            $messages = array();

            // check database again
            $this->installer->checkDb($THINKUP_CFG);

            // check if we repairing db
            if ( $to_repair == "db" ) {
                $messages['db'] = $this->installer->repairTables($THINKUP_CFG);
                $this->addToView('messages_db', $messages['db']);
            }

            $error_messages = $this->installer->getErrorMessages();
            if ( !empty($error_messages) ) {
                // failed repairing
                $this->addToView('messages_error', $error_messages);
            } else {
                $succeed = true;
            }
            $this->addToView('succeed', $succeed);
        } else {
            if ( empty($_GET) ) {
                $this->addToView('show_form', 0);
            } else {
                $information_message = array();
                $this->addToView('show_form', 1);
                if ( $to_repair == "db" ) {
                    $information_message['db']  = 'Check your existing ThinkUp tables. If some tables are missing ';
                    $information_message['db'] .= 'or need repair, ThinkUp will attempt to create or repair them.';
                }
                if ( !empty($information_message) ) {
                    $info .= '<p><strong>Important!</strong> <br />';
                    $info .= 'The ThinkUp repair process will: </p><ul>';
                    foreach ($information_message as $msg) {
                        $info .= "<li>$msg</li>";
                    }
                    $info .= '</ul>';
                    $this->addInfoMessage($info, null, true);
                }
                $this->addToView('action_form', $_SERVER['REQUEST_URI']);
            }
        }
    }

    /**
     * Returns an array of time zone options formatted for display in a select field.
     *
     * @return array An associative array of options, ready for optgrouping.
     */
    protected function getTimeZoneList() {
        $tz_options = timezone_identifiers_list();
        $view_tzs = array();

        foreach ($tz_options as $option) {
            $option_data = explode('/', $option);

            // don't allow user to select UTC
            if ($option_data[0] == 'UTC') {
                continue;
            }

            // handle things like the many Indianas
            if (isset($option_data[2])) {
                $option_data[1] = $option_data[1] . ': ' . $option_data[2];
            }

            //avoid undefined offset error
            if (!isset($option_data[1])) {
                $option_data[1] = $option_data[0];
            }

            $view_tzs[$option_data[0]][] = array(
                'val' => $option,
                'display' => str_replace('_', ' ', $option_data[1])
            );
        }

        return $view_tzs;
    }
}
