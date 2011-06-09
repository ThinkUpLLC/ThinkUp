ForgotPasswordController
========================
Inherits from `ThinkUpController <./ThinkUpController.html>`_.

ThinkUp/webapp/_lib/controller/class.ForgotPasswordController.php

Copyright (c) 2009-2011 Gina Trapani

Forgot Password Controller
Handles requests for ThinkUp user password reset links.



Methods
-------

control
~~~~~~~



.. code-block:: php5

    <?php
        public function control() {
            if (isset($_POST['Submit']) && $_POST['Submit'] == 'Send Reset') {
                $this->disableCaching();
    
                $dao = DAOFactory::getDAO('OwnerDAO');
                $user = $dao->getByEmail($_POST['email']);
                if (isset($user)) {
                    $token = $user->setPasswordRecoveryToken();
    
                    $es = new SmartyThinkUp();
                    $es->caching=false;
    
                    $config = Config::getInstance();
                    $es->assign('apptitle', $config->getValue('app_title') );
                    $es->assign('recovery_url', "session/reset.php?token=$token");
                    $es->assign('server', isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost');
                    $es->assign('site_root_path', $config->getValue('site_root_path') );
                    $message = $es->fetch('_email.forgotpassword.tpl');
    
                    Mailer::mail($_POST['email'], $config->getValue('app_title') . " Password Recovery", $message);
    
                    $this->addSuccessMessage('Password recovery information has been sent to your email address.');
                } else {
                    $this->addErrorMessage('Error: account does not exist.');
                }
            }
            $this->view_mgr->addHelp('forgot', 'userguide/accounts/index');
            $this->setViewTemplate('session.forgot.tpl');
            return $this->generateView();
        }




