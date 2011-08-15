<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.RegisterController.php
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
 * Register Controller
 * Registers new ThinkUp users.
 * This controller is not used when the installer registers the first user. Class.InstallerController handles that
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2011 Terrance Shepherd, Gina Trapani
 * @author Terrance Shepherd
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class RegisterController extends ThinkUpController {
    /**
     * Required form submission values
     * @var array
     */
    var $REQUIRED_PARAMS = array('email', 'pass1', 'pass2', 'full_name');
    /**
     *
     * @var boolean
     */
    var $is_missing_param = false;

    public function __construct($session_started=false) {
        parent::__construct($session_started);
        $this->setViewTemplate('session.register.tpl');
        $this->setPageTitle('Register');
    }

    public function control(){
        if ($this->isLoggedIn()) {
            $controller = new DashboardController(true);
            return $controller->go();
        } else {
            $config = Config::getInstance();
            $is_registration_open = $config->getValue('is_registration_open');

            $this->disableCaching();
            $invite_dao = DAOFactory::getDAO('InviteDAO') ;
            if ( isset( $_GET['code'] ) ) {
                $invite_code = $_GET['code'];
            } else {
                $invite_code = null;
            }
            $this->addToView('invite_code', $invite_code);
            $is_invite_code_valid = $invite_dao->isInviteValid($invite_code);

            if ( !$is_registration_open && !$is_invite_code_valid ){
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
                        $valid_input = true;
                        if (!Utils::validateEmail($_POST['email'])) {
                            $this->addErrorMessage("Incorrect email. Please enter valid email address.", 'email');
                            $valid_input = false;
                        }

                        if (strcmp($_POST['pass1'], $_POST['pass2']) || empty($_POST['pass1'])) {
                            $this->addErrorMessage("Passwords do not match.", 'password');
                            $valid_input = false;
                        } else if (strlen($_POST['pass1']) < 5) {
                            $this->addErrorMessage("Password must be at least 5 characters.", 'password');
                            $valid_input = false;
                        }

                        if (!$captcha->doesTextMatchImage()) {
                            $this->addErrorMessage("Entered text didn't match the image. Please try again.",
                            'captcha');
                            $valid_input = false;
                        }

                        if ($valid_input) {
                            if ($owner_dao->doesOwnerExist($_POST['email'])) {
                                $this->addErrorMessage("User account already exists.", 'email');
                            } else {
                                $es = new SmartyThinkUp();
                                $es->caching=false;
                                $act_code = rand(1000, 9999);
                                // Generate a salt for the user and combine it with the password
                                $salt = $owner_dao->generateSalt($_POST['email']);
                                $cryptpass = $owner_dao->generateUniqueSaltedPassword($_POST['pass2'], $salt);
                                $server = $_SERVER['HTTP_HOST'];
                                // Insert the details into the database
                                $owner_dao->create($_POST['email'], $cryptpass, $salt, $act_code, $_POST['full_name']);
                              
                                $es->assign('server', $server );
                                $es->assign('email', urlencode($_POST['email']) );
                                $es->assign('activ_code', $act_code );
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
}
