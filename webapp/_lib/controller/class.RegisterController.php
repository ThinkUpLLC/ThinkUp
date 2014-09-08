<?php
/**
 *
 * ThinkUp/webapp/_lib/controller/class.RegisterController.php
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
 * Register Controller
 * Registers new ThinkUp users.
 * This controller is not used when the installer registers the first user. Class.InstallerController handles that
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2009-2013 Terrance Shepherd, Gina Trapani
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
        $this->addHeaderJavaScript('assets/js/jqBootstrapValidation.js');
        $this->addHeaderJavaScript('assets/js/validate-fields.js');
        $this->setPageTitle('Register');
    }

    public function control(){
        $this->redirectToThinkUpLLCEndpoint();
        if ($this->isLoggedIn()) {
            $controller = new InsightStreamController(true);
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
            if ($invite_code != null && $is_invite_code_valid) {
                $this->addSuccessMessage("Welcome, VIP! You've been invited to register on ".
                $config->getValue('app_title_prefix')."ThinkUp.");
            }

            $has_been_registered = false;
            if ( !$is_registration_open && !$is_invite_code_valid ){
                $this->addToView('closed', true);
                $disable_xss = true;
                $this->addErrorMessage('Sorry, registration is closed on '.
                $config->getValue('app_title_prefix')."ThinkUp. ".
                'Try <a href="https://thinkup.com">ThinkUp.com</a>.', null,
                $disable_xss);
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
                            $this->addErrorMessage("Sorry, that email address looks wrong. Can you double-check it?", 'email');
                            $valid_input = false;
                        }

                        if (strcmp($_POST['pass1'], $_POST['pass2']) || empty($_POST['pass1'])) {
                            $this->addErrorMessage("Passwords do not match.", 'password');
                            $valid_input = false;
                        } else if (!preg_match("/(?=.{8,})(?=.*[a-zA-Z])(?=.*[0-9])/", $_POST['pass1'])) {
                            $this->addErrorMessage("Password must be at least 8 characters and contain both numbers ".
                            "and letters.", 'password');
                            $valid_input = false;
                        }

                        if (!$captcha->doesTextMatchImage()) {
                            $this->addErrorMessage("Hmm, that code did not match the image. Please try again?",
                            'captcha');
                            $valid_input = false;
                        }

                        if ($valid_input) {
                            if ($owner_dao->doesOwnerExist($_POST['email'])) {
                                $this->addErrorMessage("User account already exists.", 'email');
                            } else {
                                // Insert the details into the database
                                $activation_code =  $owner_dao->create($_POST['email'], $_POST['pass2'],
                                $_POST['full_name']);

                                if ($activation_code != false) {
                                    $es = new ViewManager();
                                    $es->caching=false;
                                    $es->assign('application_url', Utils::getApplicationURL(false) );
                                    $es->assign('email', urlencode($_POST['email']) );
                                    $es->assign('activ_code', $activation_code );
                                    $message = $es->fetch('_email.registration.tpl');

                                    Mailer::mail($_POST['email'], "Activate Your Account on ".
                                    $config->getValue('app_title_prefix')."ThinkUp", $message);

                                    SessionCache::unsetKey('ckey');
                                    $this->addSuccessMessage("Success! Check your email for an activation link.");
                                    //delete invite code
                                    if ( $is_invite_code_valid ) {
                                        $invite_dao->deleteInviteCode($invite_code);
                                    }
                                    $has_been_registered = true;
                                } else {
                                    $this->addErrorMessage("Unable to register a new user. Please try again.");
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
                    $this->addToView('has_been_registered', $has_been_registered);
                }
                $challenge = $captcha->generate();
                $this->addToView('captcha', $challenge);
            }
            $this->view_mgr->addHelp('register', 'userguide/accounts/index');
            return $this->generateView();
        }
    }
}
