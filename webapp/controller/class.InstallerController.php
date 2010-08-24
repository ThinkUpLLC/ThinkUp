<?php
/**
 * Installer Controller
 * Web-based application installer.
 *
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

    public function __construct($session_started=false) {
        //Don't call parent constructor because config.inc.php doesn't exist yet
        //Instead, set up the view manager with manual array configuration
        $cfg_array =  array(
            'site_root_path'=>THINKUP_BASE_URL,
            'source_root_path'=>THINKUP_ROOT_PATH, 
            'debug'=>false, 
            'app_title'=>"ThinkUp", 
            'cache_pages'=>false);
        $this->view_mgr = new SmartyThinkUp($cfg_array);
        $this->setPageTitle('Install ThinkUp');
        $this->disableCaching();
    }

    public function control() {
        $this->installer = Installer::getInstance();

        $this->checkForExistingInstallation();

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
        if ( file_exists( THINKUP_WEBAPP_PATH . 'config.inc.php' ) ) {
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
                'config.inc.php file and reload this page.<br /> Otherwise, start <a href="'.THINKUP_BASE_URL.
                '">using ThinkUp</a>.', Installer::ERROR_INSTALL_COMPLETE);
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
        $php_compat = 0;
        if ( $this->installer->checkVersion() ) {
            $php_compat = 1;
        }
        $this->addToView('php_compat', $php_compat);
        $requiredVersion = $this->installer->getRequiredVersion();
        $this->addToView('php_required_version', $requiredVersion['php']);

        // libs check
        $libs = $this->installer->checkDependency();
        $libs_compat = TRUE;
        foreach ($libs as $lib) {
            if (!$lib) {
                $libs_compat = false;
            }
        }
        $this->addToView('libs', $libs);

        // path permissions check
        $permissions = $this->installer->checkPermission();
        $this->addToView('permission', $permissions);
        $permissions_compat = TRUE;
        foreach ($permissions as $perm) {
            if (!$perm) {
                $permissions_compat = false;
            }
        }
        $this->addToView('permissions_compat', $permissions_compat);
        $writeable_directories = array(
            'logs' => THINKUP_ROOT_PATH . 'logs',
            'compiled_view' => $this->view_mgr->compile_dir,
            'cache' => $this->view_mgr->compile_dir . 'cache');
        $this->addToView('writeable_directories', $writeable_directories);

        // other vars set to view
        $requirements_met = ($php_compat && $libs_compat && $permissions_compat);
        $this->addToView('requirements_met', $requirements_met);
        $this->addToView('subtitle', 'Requirements Check');
    }

    /**
     * Step 2 - Set up database and site configuration
     */
    private function step2() {
        $this->setViewTemplate('install.step2.tpl');

        // make sure we have passed step 1
        if ( !$this->installer->checkStep1() ) {
            $this->step1();
            return;
        }

        $this->addToView('db_name', 'thinkup');
        $this->addToView('db_user', 'username');
        $this->addToView('db_passwd', 'password');
        $this->addToView('db_host', 'localhost');
        $this->addToView('db_prefix', 'tu_');
        $this->addToView('db_socket', '');
        $this->addToView('db_port', '');
        $this->addToView('site_email', 'you@example.com');
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
        if ( file_exists($config_file) ) {
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
            $db_config['GMT_offset']   = $THINKUP_CFG['GMT_offset'];
            $email                     = trim($_POST['site_email']);
        } else {
            // make sure we're not from error of couldn't write config.inc.php
            if ( !isset($_POST['db_user']) && !isset($_POST['db_passwd']) && !isset($_POST['db_name']) &&
            !isset($_POST['db_host']) ) {
                $this->addErrorMessage("Missing database credentials");
                $this->step2();
                return;
            }

            // trim each posted value
            $db_config['db_type']      = trim($_POST['db_type']);
            $db_config['db_name']      = trim($_POST['db_name']);
            $db_config['db_user']      = trim($_POST['db_user']);
            $db_config['db_password']  = trim($_POST['db_passwd']);
            $db_config['db_host']      = trim($_POST['db_host']);
            $db_config['db_socket']    = trim($_POST['db_socket']);
            $db_config['db_port']      = trim($_POST['db_port']);
            $db_config['table_prefix'] = trim($_POST['db_prefix']);
            $db_config['GMT_offset']   = 7;
            $email                     = trim($_POST['site_email']);

            if ( empty($db_config['table_prefix']) ) {
                $db_config['table_prefix'] = 'tu_';
            }
        }
        $db_config['db_type'] = 'mysql'; //default for now
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $full_name = $_POST['full_name'];
        $display_errors = false;

        // check email
        if ( !Utils::validateEmail($email) ) {
            $this->addErrorMessage("Please enter a valid email address.");
            $this->setViewTemplate('install.step2.tpl');
            $display_errors = true;
        } else if ( $password != $confirm_password || $password == '' ) { //check password
            if ($password != $confirm_password) {
                $this->addErrorMessage("Your passwords did not match.");
            } else {
                $this->addErrorMessage("Please choose a password.");
            }
            $this->setViewTemplate('install.step2.tpl');
            $display_errors = true;
        } elseif (!$this->installer->checkDb($db_config)) { //check db
            $this->addErrorMessage("Couldn't connect to your database; please re-enter your database credentials.");
            $this->setViewTemplate('install.step2.tpl');
            $display_errors = true;
        }

        if ( $display_errors ) {
            $this->addToView('db_name', $db_config['db_name']);
            $this->addToView('db_user', $db_config['db_user']);
            $this->addToView('db_passwd', $db_config['db_password']);
            $this->addToView('db_host', $db_config['db_host']);
            $this->addToView('db_prefix', $db_config['table_prefix']);
            $this->addToView('db_socket', $db_config['db_socket']);
            $this->addToView('db_port', $db_config['db_port']);
            $this->addToView('db_type', $db_config['db_type']);
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
            $this->addErrorMessage("ThinkUp couldn't write <code>config.inc.php</code> file. Either make the ".
            "<code>" . THINKUP_WEBAPP_PATH . "</code> folder writeable or create the <code>config.inc.php</code> file ".
            "there manually and paste the following text into it.");
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

        $owner_dao = DAOFactory::getDAO('OwnerDAO', $db_config);
        if ( !$owner_dao->doesAdminExist() && !$owner_dao->doesOwnerExist($email)) { // create admin if not exists
            $session = new Session();
            $activation_code = rand(1000, 9999);
            $crypt_pass = $session->pwdcrypt($password);
            //$owner_dao->insertActivatedAdmin($email, $crypt_pass, $full_name);
            $owner_dao->createAdmin($email, $crypt_pass, $activation_code, $full_name);

            // view for email
            $cfg_array =  array(
            'site_root_path'=>THINKUP_BASE_URL,
            'source_root_path'=>THINKUP_ROOT_PATH, 
            'debug'=>false, 
            'app_title'=>"ThinkUp", 
            'cache_pages'=>false);
            $email_view = new SmartyThinkUp($cfg_array);
            $email_view->caching=false;
            $email_view->assign('server', $_SERVER['HTTP_HOST'] . THINKUP_BASE_URL);
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
        $this->addToView('login_url', THINKUP_BASE_URL . 'session/login.php');
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
                    $this->addInfoMessage($info);
                }
                $this->addToView('action_form', $_SERVER['REQUEST_URI']);
            }
        }
    }
}
