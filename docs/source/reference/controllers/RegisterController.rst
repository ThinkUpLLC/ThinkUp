RegisterController
==================
Inherits from `ThinkUpController <./ThinkUpController.html>`_.

ThinkUp/webapp/_lib/controller/class.RegisterController.php

Copyright (c) 2009-2011 Terrance Shepherd, Gina Trapani

Register Controller
Registers new ThinkUp users.


Properties
----------

REQUIRED_PARAMS
~~~~~~~~~~~~~~~

Required form submission values

is_missing_param
~~~~~~~~~~~~~~~~





Methods
-------

__construct
~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function __construct($session_started=false) {
            parent::__construct($session_started);
            $this->setViewTemplate('session.register.tpl');
            $this->setPageTitle('Register');
        }


control
~~~~~~~



.. code-block:: php5

    <?php
        public function control(){
            if ($this->isLoggedIn()) {
                $controller = new DashboardController(true);
                return $controller->go();
            } else {
                $this->disableCaching();
                $config = Config::getInstance();
                $invite_dao = DAOFactory::getDAO('InviteDAO') ;
                if ( isset( $_GET['code'] ) ) {
                    $invite_code = $_GET['code'] ;
                } else {
                    $invite_code = NULL ;
                }
                $is_invite_code_valid = $invite_dao->isInviteValid($invite_code) ;
    
                if ( !$config->getValue('is_registration_open') && !$is_invite_code_valid ){
                    $this->addToView('closed', true);
                    $this->addErrorMessage('<p>Sorry, registration is closed on this ThinkUp installation.</p>'.
                    '<p><a href="http://thinkupapp.com">Install ThinkUp on your own server.</a></p>');
                } else {
                    $owner_dao = DAOFactory::getDAO('OwnerDAO');
                    $this->addToView('closed', false);
                    $captcha = new Captcha();
                    if (isset($_POST['Submit']) && $_POST['Submit'] == 'Register' ) {
                        foreach ($this->REQUIRED_PARAMS as $param) {
                            if (!isset($_POST[$param]) || $_POST[$param] == '' ) {
                                $this->addErrorMessage('Please fill out all required fields.');
                                $this->is_missing_param = true;
                            }
                        }
                        if (!$this->is_missing_param) {
                            if (!Utils::validateEmail($_POST['email'])) {
                                $this->addErrorMessage("Incorrect email. Please enter valid email address.");
                            } elseif (strcmp($_POST['pass1'], $_POST['pass2']) || empty($_POST['pass1'])) {
                                $this->addErrorMessage("Passwords do not match.");
                            } elseif (!$captcha->check()) {
                                // Captcha not valid, captcha handles message...
                            } else {
                                if ($owner_dao->doesOwnerExist($_POST['email'])) {
                                    $this->addErrorMessage("User account already exists.");
                                } else {
                                    $es = new SmartyThinkUp();
                                    $es->caching=false;
                                    $session = new Session();
                                    $activ_code = rand(1000, 9999);
                                    $cryptpass = $session->pwdcrypt($_POST['pass2']);
                                    $server = $_SERVER['HTTP_HOST'];
                                    $owner_dao->create($_POST['email'], $cryptpass, $activ_code, $_POST['full_name']);
    
                                    $es->assign('server', $server );
                                    $es->assign('email', urlencode($_POST['email']) );
                                    $es->assign('activ_code', $activ_code );
                                    $message = $es->fetch('_email.registration.tpl');
    
                                    Mailer::mail($_POST['email'], "Activate Your ".$config->getValue('app_title')
                                    ." Account", $message);
    
                                    SessionCache::unsetKey('ckey');
                                    $this->addSuccessMessage("Success! Check your email for an activation link.");
                                    //delete invite code
                                    if ( $is_invite_code_valid ) {
                                        $invite_dao->deleteInviteCode($invite_code);
                                    }
                                }
                            }
                        }
                        if (isset($_POST["full_name"])) {
                            $this->addToView('name', $_POST["full_name"]);
                        }
                        if (isset($_POST["email"])) {
                            $this->addToView('mail', $_POST["email"]);
                        }
                    }
                    $challenge = $captcha->generate();
                    $this->addToView('captcha', $challenge);
                }
                $this->view_mgr->addHelp('register', 'userguide/accounts/index');
                return $this->generateView();
            }
        }




