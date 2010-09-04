<?php
/**
 * Register Controller
 * Registers new ThinkUp users.
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
            $this->disableCaching();
            $config = Config::getInstance();

            if (!$config->getValue('is_registration_open')) {
                $this->addToView('closed', true);
                $this->addErrorMessage('<p>Sorry, registration is closed on this ThinkUp installation.</p>'.
                '<p><a href="http://github.com/ginatrapani/thinkup/tree/master">Install ThinkUp on your own '.
                'server.</a></p>');
            } else {
                $owner_dao = DAOFactory::getDAO('OwnerDAO');
                $this->addToView('closed', false);
                $captcha = new Captcha();
                if (isset($_POST['Submit']) && $_POST['Submit'] == 'Register') {
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

                                unset($_SESSION['ckey']);
                                $this->addSuccessMessage("Success! Check your email for an activation link.");
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
            return $this->generateView();
        }
    }
}
